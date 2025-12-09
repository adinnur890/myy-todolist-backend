<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;

class TodoController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->todos()->orderBy('created_at', 'desc')->get();
    }

    public function store(Request $request)
    {
        $user = $request->user();
        
        if (!$user->is_premium && $user->todos()->count() >= 3) {
            return response()->json(['message' => 'Limit reached. Upgrade to premium for unlimited todos'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $todo = $user->todos()->create($validated);
        return response()->json($todo, 201);
    }

    public function show(Todo $todo)
    {
        return $todo;
    }

    public function update(Request $request, Todo $todo)
    {
        if ($todo->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'completed' => 'sometimes|boolean',
        ]);

        $todo->update($validated);
        return $todo;
    }

    public function destroy(Request $request, Todo $todo)
    {
        if ($todo->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $todo->delete();
        return response()->json(null, 204);
    }
}
