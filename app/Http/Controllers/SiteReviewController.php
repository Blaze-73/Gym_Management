<?php

namespace App\Http\Controllers;

use App\Models\SiteReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiteReviewController extends Controller
{
    public function index()
    {
        $reviews = SiteReview::with('user:id,name,avatar')
            ->orderByDesc('created_at')
            ->limit(30)
            ->get()
            ->map(fn ($r) => $this->formatForDisplay($r));

        $stats = $this->stats();

        return response()->json([
            'reviews' => $reviews,
            'average_rating' => $stats['average_rating'],
            'review_count' => $stats['review_count'],
        ]);
    }

    public function me()
    {
        $user = Auth::user();
        $review = SiteReview::where('user_id', $user->id)->first();

        return response()->json([
            'review' => $review ? $this->formatForDisplay($review->load('user:id,name,avatar')) : null,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10|max:600',
        ]);

        $review = SiteReview::updateOrCreate(
            ['user_id' => $user->id],
            [
                'rating' => $validated['rating'],
                'comment' => $validated['comment'],
            ]
        );

        $review->load('user:id,name,avatar');

        return response()->json([
            'message' => 'Thank you! Your review is now on our homepage.',
            'review' => $this->formatForDisplay($review),
            'average_rating' => $this->stats()['average_rating'],
            'review_count' => $this->stats()['review_count'],
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $review = SiteReview::where('user_id', $user->id)->find($id);

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        $validated = $request->validate([
            'rating' => 'sometimes|required|integer|min:1|max:5',
            'comment' => 'sometimes|required|string|min:10|max:600',
        ]);

        $review->update($validated);
        $review->load('user:id,name,avatar');

        return response()->json([
            'message' => 'Review updated.',
            'review' => $this->formatForDisplay($review),
            'average_rating' => $this->stats()['average_rating'],
            'review_count' => $this->stats()['review_count'],
        ]);
    }

    protected function stats(): array
    {
        $count = SiteReview::count();
        $avg = $count > 0 ? round((float) SiteReview::avg('rating'), 1) : 0;

        return [
            'average_rating' => $avg,
            'review_count' => $count,
        ];
    }

    protected function formatForDisplay(SiteReview $review): array
    {
        $user = $review->user;
        $name = $user?->name ?? 'Member';
        $parts = explode(' ', trim($name));
        $displayName = count($parts) > 1
            ? $parts[0] . ' ' . strtoupper(substr($parts[1], 0, 1)) . '.'
            : $name;

        return [
            'id' => $review->id,
            'rating' => $review->rating,
            'text' => $review->comment,
            'name' => $displayName,
            'full_name' => $name,
            'role' => 'Verified member',
            'image' => $user?->avatar ?: null,
            'initial' => strtoupper(substr($name, 0, 1) ?: 'M'),
            'created_at' => $review->created_at?->toIso8601String(),
            'is_mine' => Auth::check() && Auth::id() === $review->user_id,
        ];
    }
}
