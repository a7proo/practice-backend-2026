<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $bookings = Booking::where('user_id', $request->user()->id)
            ->with('resource')
            ->latest()
            ->paginate(10);

        return response()->json($bookings);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'resource_id' => 'required|exists:resources,id',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
        ]);

        $hasOverlap = DB::table('bookings')
            ->where('resource_id', $validated['resource_id'])
            ->where('status', 'active')
            ->where(function ($query) use ($validated) {
                $query->where(function ($q) use ($validated) {
                    $q->where('start_time', '<', $validated['end_time'])
                      ->where('end_time', '>', $validated['start_time']);
                });
            })
            ->exists();

        if ($hasOverlap) {
            return response()->json([
                'message' => 'Это время уже забронировано',
            ], 422);
        }

        $resource = Resource::find($validated['resource_id']);
        if (!$resource || !$resource->is_active) {
            return response()->json([
                'message' => 'Ресурс недоступен',
            ], 422);
        }

        $booking = Booking::create([
            'user_id' => $request->user()->id,
            'resource_id' => $validated['resource_id'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'status' => 'active',
        ]);

        return response()->json($booking->load('resource'), 201);
    }

    public function show(Request $request, Booking $booking)
    {
        if ($request->user()->role !== 'admin' && $booking->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Доступ запрещён'], 403);
        }

        return response()->json($booking->load('resource', 'user'));
    }

    public function cancel(Request $request, Booking $booking)
    {
        if ($request->user()->role !== 'admin' && $booking->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Доступ запрещён'], 403);
        }

        if ($booking->status === 'cancelled') {
            return response()->json([
                'message' => 'Бронирование уже отменено',
            ], 422);
        }

        if ($booking->status === 'completed') {
            return response()->json([
                'message' => 'Нельзя отменить завершённое бронирование',
            ], 422);
        }

        $booking->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Бронирование отменено',
            'booking' => $booking->load('resource'),
        ]);
    }

    public function adminIndex()
    {
        $bookings = Booking::with(['resource', 'user'])
            ->latest()
            ->paginate(10);

        return response()->json($bookings);
    }
}