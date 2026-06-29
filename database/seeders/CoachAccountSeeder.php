<?php

namespace Database\Seeders;

use App\Models\Coach;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CoachAccountSeeder extends Seeder
{
    /** Default password for all seeded coach accounts. */
    public const DEFAULT_PASSWORD = 'CoachPass123!';

    public function run(): void
    {
        $accounts = [
            1 => [
                'name' => 'Marcus Thorne',
                'email' => 'marcus.thorne@aliengym.com',
            ],
            2 => [
                'name' => 'Sienna Vance',
                'email' => 'sienna.vance@aliengym.com',
            ],
            3 => [
                'name' => 'Kaelen Drax',
                'email' => 'kaelen.drax@aliengym.com',
            ],
        ];

        foreach ($accounts as $coachId => $data) {
            $coach = Coach::find($coachId);
            if (!$coach) {
                $this->command?->warn("Coach #{$coachId} not found — skipped.");
                continue;
            }

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make(self::DEFAULT_PASSWORD),
                    'role' => 'coach',
                    'phone' => null,
                ]
            );

            $coach->update([
                'user_id' => $user->id,
                'name' => $data['name'],
            ]);

            $this->command?->info("Linked {$data['name']} → {$data['email']}");
        }
    }
}
