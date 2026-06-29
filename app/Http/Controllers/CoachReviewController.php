<?php

namespace App\Http\Controllers;

use App\Models\Coach;
use App\Models\CoachReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CoachReviewController extends Controller
{
    public function index($coachId)
    {
        $coach = Coach::find($coachId);
        if (!$coach) {
            return response()->json(['message' => 'Coach not found'], 404);
        }

        $reviews = CoachReview::with('user:id,name')
            ->where('coach_id', $coach->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn ($r) => $this->formatReview($r));

        return response()->json([
            'coach_id' => $coach->id,
            'average_rating' => $this->averageRating($coach->id),
            'review_count' => CoachReview::where('coach_id', $coach->id)->count(),
            'reviews' => $reviews,
        ]);
    }

    public function myReview($coachId)
    {
        $user = Auth::user();
        $review = CoachReview::where('user_id', $user->id)
            ->where('coach_id', $coachId)
            ->first();

        return response()->json([
            'review' => $review ? $this->formatReview($review) : null,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'coach_id' => 'required|exists:coaches,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        $review = CoachReview::updateOrCreate(
            [
                'user_id' => $user->id,
                'coach_id' => $validated['coach_id'],
            ],
            [
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
            ]
        );

        $this->syncCoachRating($validated['coach_id']);

        return response()->json([
            'message' => 'Review saved. Thank you for your feedback.',
            'review' => $this->formatReview($review->load('user:id,name')),
            'average_rating' => $this->averageRating($validated['coach_id']),
            'review_count' => CoachReview::where('coach_id', $validated['coach_id'])->count(),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $review = CoachReview::where('user_id', $user->id)->find($id);

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        $validated = $request->validate([
            'rating' => 'sometimes|required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        $review->update($validated);
        $this->syncCoachRating($review->coach_id);

        return response()->json([
            'message' => 'Review updated.',
            'review' => $this->formatReview($review->fresh()->load('user:id,name')),
            'average_rating' => $this->averageRating($review->coach_id),
            'review_count' => CoachReview::where('coach_id', $review->coach_id)->count(),
        ]);
    }

    protected function formatReview(CoachReview $review): array
    {
        return [
            'id' => $review->id,
            'coach_id' => $review->coach_id,
            'rating' => $review->rating,
            'comment' => $review->comment,
            'author_name' => $review->user?->name ?? 'Member',
            'created_at' => $review->created_at?->toIso8601String(),
            'is_mine' => Auth::check() && Auth::id() === $review->user_id,
        ];
    }

    protected function averageRating(int $coachId): float
    {
        $avg = CoachReview::where('coach_id', $coachId)->avg('rating');

        return round((float) ($avg ?? 5), 2);
    }

    protected function syncCoachRating(int $coachId): void
    {
        $coach = Coach::find($coachId);
        if (!$coach) {
            return;
        }

        $count = CoachReview::where('coach_id', $coachId)->count();
        $rating = $count > 0
            ? round((float) CoachReview::where('coach_id', $coachId)->avg('rating'), 2)
            : 5.00;

        $coach->update(['rating' => $rating]);
    }
}
