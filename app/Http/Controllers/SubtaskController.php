<?php

namespace App\Http\Controllers;

use App\Models\Subtask;
use App\Models\Todo;
use Illuminate\Http\Request;

class SubtaskController extends Controller
{
    public function index(Request $request)
    {
        $todoId = $request->query('todo_id');
        $todo = Todo::findOrFail($todoId);

        if ($todo->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $todo->subtasks;
    }

    public function store(Request $request)
    {
        $todoId = $request->query('todo_id');
        $todo = Todo::findOrFail($todoId);

        if ($todo->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $subtask = $todo->subtasks()->create($validated);
        return response()->json($subtask, 201);
    }

    public function update(Request $request, Subtask $subtask)
    {
        if ($subtask->todo->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);

        $subtask->update($validated);
        return $subtask;
    }

    public function destroy(Request $request, Subtask $subtask)
    {
        if ($subtask->todo->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $subtask->delete();
        return response()->json(null, 204);
    }

    public function changeStatus(Request $request)
    {
        $validated = $request->validate([
            'subtask_id' => 'required|exists:subtasks,id',
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $subtask = Subtask::findOrFail($validated['subtask_id']);

        if ($subtask->todo->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $subtask->update(['status' => $validated['status']]);
        return $subtask;
    }
}
