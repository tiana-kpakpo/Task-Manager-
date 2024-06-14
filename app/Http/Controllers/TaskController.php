<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       return Task::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $validData = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'due_date' => 'required|date',
        'category' => 'required|string',
        'reminders' => 'nullable|string',
        'priority' => 'required|integer',
        'recurrence' => 'nullable|string|in:daily,weekly,monthly,yearly',
        'user_id' => 'nullable|exists:users,id'
    ]);

    $user = $request->user();

    $task = $user->tasks()->create($validData);

    //particular user's task
    $task->users()->attach($user->id);

    //assigning a task to someone
    if ($request->has('user_id') && $validData['user_id'] != $user->id) {
        $assignedUser = User::find($validData['user_id']);
        if ($assignedUser) {
            $task->users()->attach($assignedUser->id);
            return response()->json([
                'message' => 'Task successfully assigned to '  .  $assignedUser->name,
                'task' => $task
            ], 201);
        }
    }

    return response()->json([
        'message' => 'Task created successfully',
        'task' => $task
    ], 201);
}


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $task = Task::find($id);

        if(!$task){
            return response()->json([
                'message' => 'Task not found'
            ], 404);
        }

        return response()->json([
            'task' => $task,
            'message' => 'Task details retrieved successfully'
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
       $request->validate([
        'title' => 'string|max:255',
        'description' => 'nullable|string',
        'due_date' => 'date',
        'category' => 'string',
        'reminders' => 'nullable|string',
        'priority' => 'integer',
        'recurrence' => 'nullable|string',
        'user_id' => 'nullable|exists:users,id'

        ]);

        try {
            $task = Task::findOrFail($id);

            if($request->user()->id !== $task->user_id){
                return response()->json([
                    'message' => 'Unauthorized: You do not have permission to update this task.',
                ], 403);

            }
            $task->update($request ->all());
            return response()->json([
                'message' => 'Task updated successfully',
                'task' => $task
            ]);

        } catch (ModelNotFoundException $e) {
            return response()-> json([
                'message' => 'Task with id' . $task->id . 'not found'
            ], 404);
        }
      
    }

    /**
     * Remove the specified resource from storage.
     */

    public function destroy(string $id)
{
    try {
        $task = Task::findOrFail($id);

        // Check if the authenticated user is the owner of the task
        if (auth()->user()->id !== $task->user_id) {
            return response()->json([
                'message' => 'Unauthorized: You do not have permission to delete this task.'
            ], 403);
        }

        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully'
        ]);

    } catch (ModelNotFoundException $e) {
        return response()->json([
            'message' => 'Task with id' . $task->id . 'not found'
        ], 404);
    }
}


    /**
     * filters the specified resource from storage.
     */
    public function search(Request $request)
    {
        $query = $request->input('query');
    
        // If no query provided, return error response
        if (!$query) {
            return response()->json([
                'message' => 'No search query provided.'
            ], 400);
        }
    
        $tasks = Task::query();

        $fields = ['title', 'description', 'category', 'status', 'priority'];
    
    
        // Loop through each field and add search condition
        $tasks->where(function ($q) use ($fields, $query) {
            foreach ($fields as $field) {
                $q->orWhere($field, 'like', '%' . $query . '%')->get();
            }
        });
    
        // Retrieve filtered tasks
        $filteredTasks = $tasks->get();
    
        if ($filteredTasks->isEmpty()) {
            return response()->json([
                'message' => 'No tasks found matching the search criteria.'
            ], 404);
        }
    
        return response()->json([
            'tasks' => $filteredTasks,
            'message' => 'Tasks retrieved successfully.'
        ]);
    }
    
     


    public function getTasksForUser(Request $request)
{
    $userId = $request->user()->id;
    $user = User::findOrFail($userId);
    $tasks = $user->tasks()->get();  /* Retrieves all tasks for the particular user */

    return response()->json([
        'user' => $user,
        'tasks' => $tasks
    ]);
}


    
}
