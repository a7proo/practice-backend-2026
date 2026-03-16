<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResourceController extends Controller
{
    public function index(Request $request)
    {
        $query = Resource::where('is_active', true);
        
        if ($request->has('capacity')) {
            $query->where('capacity', '>=', $request->capacity);
        }
        
        if ($request->has('features')) {
            $features = explode(',', $request->features);
            foreach ($features as $feature) {
                $query->whereJsonContains('features', trim($feature));
            }
        }
        
        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }
        
        $resources = $query->with('reviews')->paginate(10);
        
        $resources->getCollection()->transform(function ($resource) {
            $resource->average_rating = round($resource->reviews()->avg('rating') ?? 0, 2);
            $resource->reviews_count = $resource->reviews()->count();
            return $resource;
        });
        
        return response()->json($resources);
    }

    public function show(Resource $resource)
    {
        $resource->load('reviews.user');
        $resource->average_rating = round($resource->reviews()->avg('rating') ?? 0, 2);
        $resource->reviews_count = $resource->reviews()->count();
        
        return response()->json($resource);
    }

    public function store(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Доступ запрещён'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'features' => 'nullable|array',
            'features.*' => 'string',
        ]);

        $resource = Resource::create($validated);

        return response()->json($resource, 201);
    }

    public function update(Request $request, Resource $resource)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Доступ запрещён'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'sometimes|required|string|max:255',
            'capacity' => 'sometimes|required|integer|min:1',
            'features' => 'nullable|array',
            'features.*' => 'string',
        ]);

        $resource->update($validated);

        return response()->json($resource);
    }

    public function destroy(Request $request, Resource $resource)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Доступ запрещён'], 403);
        }

        $resource->delete();

        return response()->json(['message' => 'Ресурс удалён']);
    }

    public function available(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'capacity' => 'nullable|integer|min:1',
            'features' => 'nullable|string',
        ]);

        $startDateTime = $validated['date'] . ' ' . $validated['start_time'];
        $endDateTime = $validated['date'] . ' ' . $validated['end_time'];

        $query = Resource::where('is_active', true);

        if ($validated['capacity']) {
            $query->where('capacity', '>=', $validated['capacity']);
        }

        if ($validated['features']) {
            $features = explode(',', $validated['features']);
            foreach ($features as $feature) {
                $query->whereJsonContains('features', trim($feature));
            }
        }

        $resources = $query->get()->filter(function ($resource) use ($startDateTime, $endDateTime) {
            $hasOverlap = DB::table('bookings')
                ->where('resource_id', $resource->id)
                ->where('status', 'active')
                ->where('start_time', '<', $endDateTime)
                ->where('end_time', '>', $startDateTime)
                ->exists();

            return !$hasOverlap;
        });

        return response()->json([
            'data' => $resources,
            'search_params' => [
                'date' => $validated['date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
            ],
        ]);
    }

    public function schedule(Request $request, Resource $resource)
    {
        $validated = $request->validate([
            'date' => 'nullable|date',
            'view' => 'nullable|in:day,week',
        ]);

        $date = $validated['date'] ?? now()->toDateString();
        $view = $validated['view'] ?? 'day';

        if ($view === 'day') {
            $startDate = $date . ' 00:00:00';
            $endDate = $date . ' 23:59:59';
        } else {
            $startDate = now()->parse($date)->startOfWeek()->toDateTimeString();
            $endDate = now()->parse($date)->endOfWeek()->toDateTimeString();
        }

        $bookings = $resource->bookings()
            ->where('status', 'active')
            ->whereBetween('start_time', [$startDate, $endDate])
            ->with('user')
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'resource_id' => $resource->id,
            'resource_name' => $resource->name,
            'date' => $date,
            'view' => $view,
            'bookings' => $bookings,
        ]);
    }
}