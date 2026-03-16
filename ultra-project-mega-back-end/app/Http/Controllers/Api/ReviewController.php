<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Booking;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, $resourceId)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'booking_id' => 'required|exists:bookings,id',
        ]);

        $booking = Booking::where('id', $validated['booking_id'])
            ->where('user_id', $request->user()->id)
            ->where('resource_id', $resourceId)
            ->first();

        if (!$booking) {
            return response()->json([
                'message' => 'Бронирование не найдено',
            ], 404);
        }

        if ($booking->status !== 'completed') {
            return response()->json([
                'message' => 'Отзыв можно оставить только после завершённого бронирования',
            ], 422);
        }

        $existingReview = Review::where('booking_id', $booking->id)->first();
        if ($existingReview) {
            return response()->json([
                'message' => 'Отзыв для этого бронирования уже оставлен',
            ], 422);
        }

        $review = Review::create([
            'user_id' => $request->user()->id,
            'resource_id' => $resourceId,
            'booking_id' => $booking->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
        ]);

        return response()->json($review->load('user'), 201);
    }

    public function index($resourceId)
    {
        $reviews = Review::where('resource_id', $resourceId)
            ->with('user')
            ->latest()
            ->paginate(10);

        $averageRating = Review::where('resource_id', $resourceId)->avg('rating') ?? 0;

        return response()->json([
            'data' => $reviews,
            'average_rating' => round($averageRating, 2),
            'total_reviews' => $reviews->total(),
        ]);
    }
}