<?php

namespace App\Http\Controllers;

use App\Models\Column;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;


class TaskController extends Controller
{

    public function store(Request $request, Project $project, Column $column)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'completed_at' => 'nullable|date',
            'priority_id' => 'nullable|exists:priorities,id'
        ]);
        $validated['project_id'] = $project->id;
        $validated['column_id'] = $column->id;
        $validated['user_id'] = auth()->id();

        $task = Task::create($validated);

        return back()->with('success', 'Task created successfully.');
    }

    public function storeFromList(Request $request, Project $project)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'completed_at' => 'nullable|date',
            'priority_id' => 'nullable|exists:priorities,id',
            'column_id' => 'required|exists:columns,id'
        ]);

        $validated['project_id'] = $project->id;
        $validated['user_id'] = auth()->id();

        $task = Task::create($validated);

        return back()->with('success', 'Tâche créée avec succès.');
    }


    public function delete(Task $task)
    {
        $task->assignedUsers()->detach();
        $task->delete();

        return back()->with('success', 'Task deleted successfully.');
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'completed_at' => 'nullable|date',
            'priority_id' => 'nullable|exists:priorities,id',
            'column_id' => 'required|exists:columns,id'
        ]);

        // Gérer les utilisateurs assignés de manière sécurisée
        if ($request->has('assigned_users') && is_array($request->assigned_users)) {
            $ids = array_filter($request->assigned_users, 'is_numeric');
        $task->assignedUsers()->sync($ids);
        } else {
            // Si aucun utilisateur n'est assigné, vider la relation
            $task->assignedUsers()->sync([]);
        }
        $assignedUsers = $request->input('assigned_users', []);
        $ids = array_filter($assignedUsers, 'is_numeric');
        $task->assignedUsers()->sync($ids);

        $task->update($validated);

        return back()->with('success', 'Task updated successfully.');
    }


    public function list(Project $project)
    {
        session(['project_' . $project->id . '_view' => 'list']);
        return view('projects.show', compact('project'));
    }


    public function reorder(Request $request)
    {
        $request->validate([
            'column_id' => 'required|exists:columns,id',
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|exists:tasks,id',
            'tasks.*.order' => 'required|numeric'
        ]);

        $column = Column::findOrFail($request->column_id);
        $isFinishedColumn = $column->finished_column;

        foreach ($request->tasks as $taskData) {
            $task = Task::find($taskData['id']);

            $task->order = $taskData['order'];
            $task->column_id = $request->column_id;

            if ($isFinishedColumn && is_null($task->completed_at)) {
                $task->completed_at = now();
            }

            $task->save();
        }


        return response()->json(['success' => true]);
    }

    public function calendar(Project $project)
    {
        session(['project_' . $project->id . '_view' => 'calendar']);
        return view('projects.show', compact('project'));
    }

}

