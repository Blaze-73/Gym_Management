<?php

namespace App\Services;

use App\Models\Membership;
use App\Models\Subscription;
use App\Models\UserCoach;
use App\Models\UserNotification;
use App\Models\UserWorkout;

class UserNotificationService
{
    public function notifySubscriptionActivated(int $userId, Subscription $subscription): void
    {
        $subscription->loadMissing('plan');
        $plan = $subscription->plan;
        if (!$plan) {
            return;
        }

        $entitlements = app(PlanEntitlementService::class);
        $planDef = $entitlements->resolvePlanDefinition($plan);
        $planName = $planDef['name'] ?? $plan->name ?? 'your plan';

        $this->notifyPlanActivated($userId, $planName, 'subscription_' . $subscription->id);
    }

    public function notifyPlanActivated(int $userId, string $planName, string $typeKey): void
    {
        UserNotification::firstOrCreate(
            ['user_id' => $userId, 'type' => $typeKey],
            [
                'title' => 'Subscribed to ' . $planName,
                'body' => "You're subscribed to {$planName}. Your membership is now active.",
                'read' => false,
            ]
        );
    }

    public function notifyMembershipApproved(int $userId, Membership $membership): void
    {
        $membership->loadMissing('plan');
        $planName = $membership->plan?->name ?? 'your plan';

        UserNotification::firstOrCreate(
            ['user_id' => $userId, 'type' => 'membership_approved_' . $membership->id],
            [
                'title' => 'Membership approved',
                'body' => "Your {$planName} membership was approved. Welcome to Alien Fitness!",
                'read' => false,
            ]
        );
    }

    public function notifyMembershipRejected(int $userId, Membership $membership): void
    {
        $membership->loadMissing('plan');
        $planName = $membership->plan?->name ?? 'the requested plan';

        UserNotification::create([
            'user_id' => $userId,
            'type' => 'membership_rejected_' . $membership->id . '_' . time(),
            'title' => 'Membership request declined',
            'body' => "Your request for {$planName} was not approved. Contact us or choose another plan.",
            'read' => false,
        ]);
    }

    public function notifySubscriptionTerminated(int $userId, string $planName): void
    {
        UserNotification::create([
            'user_id' => $userId,
            'type' => 'subscription_terminated_' . time(),
            'title' => 'Subscription ended',
            'body' => "Your {$planName} subscription has been terminated. Renew anytime from Plans.",
            'read' => false,
        ]);
    }

    public function notifySubscriptionExpiring(int $userId, Subscription $subscription, int $daysLeft): void
    {
        $subscription->loadMissing('plan');
        $planName = $subscription->plan?->name ?? 'Your plan';

        UserNotification::updateOrCreate(
            ['user_id' => $userId, 'type' => 'sub_expiring_' . $subscription->id],
            [
                'title' => 'Subscription ending soon',
                'body' => $daysLeft === 0
                    ? "Your {$planName} plan expires today. Renew to keep access."
                    : "Your {$planName} plan expires in {$daysLeft} day(s). Renew before access ends.",
                'read' => false,
            ]
        );
    }

    public function notifyOrderStatus(int $userId, $order, string $newStatus): void
    {
        $labels = [
            'pending' => 'received',
            'shipped' => 'shipped',
            'delivered' => 'delivered',
            'cancelled' => 'cancelled',
        ];
        $label = $labels[$newStatus] ?? $newStatus;

        UserNotification::create([
            'user_id' => $userId,
            'type' => 'order_status_' . $order->id . '_' . $newStatus,
            'title' => 'Order update',
            'body' => "Order #{$order->id} has been {$label}.",
            'read' => false,
        ]);
    }

    public function notifyOrderPlaced(int $userId, $order): void
    {
        UserNotification::create([
            'user_id' => $userId,
            'type' => 'order_placed_' . $order->id,
            'title' => 'Order placed',
            'body' => "Your order #{$order->id} was placed successfully. We'll notify you when it ships.",
            'read' => false,
        ]);
    }

    public function notifyCoachDeliverable(int $userId, $deliverable, $coach): void
    {
        $coachName = $coach->user?->name ?? $coach->name ?? 'Your coach';
        $isProgram = $deliverable->type === 'program';
        $title = $isProgram
            ? 'New program from ' . $coachName
            : 'New message from ' . $coachName;
        $body = $isProgram
            ? ($deliverable->title ?: 'Your coach sent you a new training program.')
            : \Illuminate\Support\Str::limit($deliverable->body, 120);

        UserNotification::create([
            'user_id' => $userId,
            'type' => 'coach_deliverable_' . $deliverable->id,
            'title' => $title,
            'body' => $body,
            'read' => false,
        ]);
    }

    public function notifyClientMessageToCoach(int $coachUserId, $deliverable, $client): void
    {
        $clientName = $client->name ?? 'A client';

        UserNotification::create([
            'user_id' => $coachUserId,
            'type' => 'client_message_' . $deliverable->id,
            'title' => 'Message from ' . $clientName,
            'body' => \Illuminate\Support\Str::limit($deliverable->body, 120),
            'read' => false,
        ]);
    }

    public function notifyCoachClassAssignment(int $coachUserId, $schedule): void
    {
        $start = $schedule->start_time
            ? \Carbon\Carbon::parse($schedule->start_time)->format('g:i A')
            : 'TBD';
        $end = $schedule->end_time
            ? \Carbon\Carbon::parse($schedule->end_time)->format('g:i A')
            : null;
        $timeRange = $end ? "{$start} – {$end}" : $start;
        $room = $schedule->room ? " · {$schedule->room}" : '';

        UserNotification::updateOrCreate(
            ['user_id' => $coachUserId, 'type' => 'coach_class_' . $schedule->id],
            [
                'title' => 'Class assignment',
                'body' => "You're assigned to {$schedule->class_name} on {$schedule->day_of_week} at {$timeRange}{$room}.",
                'read' => false,
            ]
        );
    }

    public function notifyCoachRequestSubmitted(int $userId, UserCoach $assignment): void
    {
        $assignment->loadMissing('coach.user');
        $coachName = $assignment->coach?->user?->name ?? $assignment->coach?->name ?? 'your coach';

        UserNotification::create([
            'user_id' => $userId,
            'type' => 'coach_request_submitted_' . $assignment->id,
            'title' => 'Coach request submitted',
            'body' => "Your request to join {$coachName} is pending admin approval.",
            'read' => false,
        ]);
    }

    public function notifyCoachRequestApproved(int $userId, $assignment): void
    {
        $assignment->loadMissing('coach.user');
        $coachName = $assignment->coach?->user?->name ?? $assignment->coach?->name ?? 'your coach';

        UserNotification::create([
            'user_id' => $userId,
            'type' => 'coach_request_approved_' . $assignment->id,
            'title' => 'Coach request approved',
            'body' => "Your request was approved. {$coachName} is now your active coach.",
            'read' => false,
        ]);
    }

    public function notifyCoachRequestRejected(int $userId, $assignment): void
    {
        $assignment->loadMissing('coach.user');
        $coachName = $assignment->coach?->user?->name ?? $assignment->coach?->name ?? 'the coach';

        UserNotification::create([
            'user_id' => $userId,
            'type' => 'coach_request_rejected_' . $assignment->id,
            'title' => 'Coach request declined',
            'body' => "Your request to join {$coachName} was not approved. You can request another coach.",
            'read' => false,
        ]);
    }

    public function notifyLeaveRequestSubmitted(int $userId, $assignment): void
    {
        $assignment->loadMissing('coach.user');
        $coachName = $assignment->coach?->user?->name ?? $assignment->coach?->name ?? 'your coach';

        UserNotification::create([
            'user_id' => $userId,
            'type' => 'leave_request_submitted_' . $assignment->id,
            'title' => 'Leave request submitted',
            'body' => "Your request to leave {$coachName} is pending admin approval.",
            'read' => false,
        ]);
    }

    public function notifyLeaveRequestApproved(int $userId, $assignment): void
    {
        $assignment->loadMissing('coach.user');
        $coachName = $assignment->coach?->user?->name ?? $assignment->coach?->name ?? 'your coach';

        UserNotification::create([
            'user_id' => $userId,
            'type' => 'leave_request_approved_' . $assignment->id,
            'title' => 'Leave request approved',
            'body' => "Your request to leave {$coachName} was approved. You can request a new coach anytime.",
            'read' => false,
        ]);
    }

    public function notifyLeaveRequestRejected(int $userId, $assignment): void
    {
        $assignment->loadMissing('coach.user');
        $coachName = $assignment->coach?->user?->name ?? $assignment->coach?->name ?? 'your coach';

        UserNotification::create([
            'user_id' => $userId,
            'type' => 'leave_request_rejected_' . $assignment->id,
            'title' => 'Leave request declined',
            'body' => "Your request to leave {$coachName} was not approved. You remain assigned to this coach.",
            'read' => false,
        ]);
    }

    public function notifyCoachAssignedByAdmin(int $userId, $assignment): void
    {
        $assignment->loadMissing('coach.user');
        $coachName = $assignment->coach?->user?->name ?? $assignment->coach?->name ?? 'your coach';

        UserNotification::create([
            'user_id' => $userId,
            'type' => 'coach_assigned_' . $assignment->id,
            'title' => 'Coach assigned',
            'body' => "You have been assigned to {$coachName}. Open My Coach to get started.",
            'read' => false,
        ]);
    }

    public function notifyCoachAssignmentEnded(int $userId, $assignment): void
    {
        $assignment->loadMissing('coach.user');
        $coachName = $assignment->coach?->user?->name ?? $assignment->coach?->name ?? 'your coach';

        UserNotification::create([
            'user_id' => $userId,
            'type' => 'coach_ended_' . $assignment->id . '_' . time(),
            'title' => 'Coach assignment ended',
            'body' => "Your assignment with {$coachName} has ended.",
            'read' => false,
        ]);
    }

    public function notifyNewClientAssignedToCoach(int $coachUserId, $client, $assignment): void
    {
        $clientName = $client->name ?? 'A member';

        UserNotification::create([
            'user_id' => $coachUserId,
            'type' => 'new_client_' . $assignment->id,
            'title' => 'New client assigned',
            'body' => "{$clientName} is now your client. Open Coach Hub to view their progress.",
            'read' => false,
        ]);
    }

    public function notifyMilestone(int $userId, string $milestoneId, string $label): void
    {
        UserNotification::firstOrCreate(
            ['user_id' => $userId, 'type' => 'milestone_' . $milestoneId],
            [
                'title' => 'Milestone unlocked',
                'body' => "Congratulations! You earned: {$label}.",
                'read' => false,
            ]
        );
    }

    public function checkWorkoutMilestones(int $userId): void
    {
        $completed = UserWorkout::where('user_id', $userId)->where('status', 'completed')->count();

        $milestones = [
            ['id' => 'first_workout', 'label' => 'First Workout', 'threshold' => 1],
            ['id' => 'ten_workouts', 'label' => '10 Workouts', 'threshold' => 10],
            ['id' => 'fifty_workouts', 'label' => '50 Workouts', 'threshold' => 50],
        ];

        foreach ($milestones as $m) {
            if ($completed >= $m['threshold']) {
                $this->notifyMilestone($userId, $m['id'], $m['label']);
            }
        }
    }

    /** @return array<int, array<string, mixed>> */
    public function listForUser(int $userId, int $limit = 40): array
    {
        return UserNotification::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (UserNotification $n) => [
                'id' => $n->id,
                'type' => $this->resolveNotificationType($n->type),
                'title' => $n->title,
                'body' => $n->body,
                'read' => (bool) $n->read,
                'time' => $n->created_at?->diffForHumans() ?? 'Just now',
                'link' => $this->notificationLink($n->type),
            ])
            ->values()
            ->all();
    }

    private function resolveNotificationType(string $type): string
    {
        if ($this->isPlanNotification($type)) {
            return 'subscription';
        }
        if (str_starts_with($type, 'order_')) {
            return 'order';
        }
        if (str_starts_with($type, 'coach_deliverable_')
            || str_starts_with($type, 'client_message_')
            || str_starts_with($type, 'new_client_')
            || str_starts_with($type, 'coach_request_')
            || str_starts_with($type, 'leave_request_')
            || str_starts_with($type, 'coach_assigned_')
            || str_starts_with($type, 'coach_ended_')) {
            return 'coach';
        }
        if (str_starts_with($type, 'coach_class_')) {
            return 'schedule';
        }
        if (str_starts_with($type, 'milestone_')) {
            return 'workout';
        }
        if (str_starts_with($type, 'sub_expiring_')) {
            return 'alert';
        }

        return 'info';
    }

    private function notificationLink(string $type): ?string
    {
        if ($this->isPlanNotification($type) || str_starts_with($type, 'sub_expiring_')) {
            return '/subscription';
        }
        if (str_starts_with($type, 'membership_rejected_')) {
            return '/plans';
        }
        if (str_starts_with($type, 'order_')) {
            return '/my-orders';
        }
        if (str_starts_with($type, 'coach_deliverable_')) {
            return '/my-coach';
        }
        if (str_starts_with($type, 'client_message_') || str_starts_with($type, 'new_client_')) {
            return '/coach-portal';
        }
        if (str_starts_with($type, 'coach_class_')) {
            return '/schedule';
        }
        if (str_starts_with($type, 'milestone_')) {
            return '/dashboard';
        }
        if (str_starts_with($type, 'coach_assigned_') || str_starts_with($type, 'coach_request_approved_')) {
            return '/my-coach';
        }
        if (str_starts_with($type, 'leave_request_') || str_starts_with($type, 'coach_request_') || str_starts_with($type, 'coach_ended_')) {
            return '/coaches';
        }

        return null;
    }

    private function isPlanNotification(string $type): bool
    {
        return str_starts_with($type, 'subscription_')
            || str_starts_with($type, 'membership_');
    }
}
