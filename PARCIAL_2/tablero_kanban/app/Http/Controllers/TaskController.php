<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::query();

        if ($request->filled('q')) {
            $query->where('title', 'ilike', '%' . $request->q . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        return $query
            ->orderByRaw("CASE priority WHEN 'alta' THEN 1 WHEN 'media' THEN 2 ELSE 3 END")
            ->orderBy('id')
            ->get();
    }

    public function store(Request $request)
    {
        return Task::create($this->validated($request));
    }

    public function update(Request $request, Task $task)
    {
        $task->update($this->validated($request));
        return $task;
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return response()->noContent();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:por_hacer,en_curso,hecha',
            'priority' => 'sometimes|in:baja,media,alta',
            'due_date' => 'nullable|date',
        ]);
    }
}
