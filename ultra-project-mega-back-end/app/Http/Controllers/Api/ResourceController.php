<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    public function index()
    {
        $resources = Resource::where('is_active', true)->paginate(10);
        return response()->json($resources);
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

    public function show(Resource $resource)
    {
        return response()->json($resource);
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
}