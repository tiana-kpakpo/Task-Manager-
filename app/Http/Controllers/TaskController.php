<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

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
            'recurrence' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users, id'

        ]);

        //  $task = Task::create($request->all());

        $user = $request->user();

        $task = $user->tasks()->create($validData);

        $task->users()->attach($request->user()->id);

        if (isset($validData['assigned_to'])) {
            $assignedUser = User::find($validData['assigned_to']);
            if ($assignedUser) {
                $task->users()->attach($assignedUser->id);
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

        // $response = [
        //     'id' => $task->id,
        //     'title' => $task->title,
        //     'description' => $task->description,
        //     'due_date' => $task->due_date,
        //     'category' => $task->category,
        //     'status' => $task->status,
        //     'priority' => $task->priority,
        //     'reminders' => $task->reminders,
        //     'recurrence' => $task->recrrence,
        //     'created_at' => $task->created_at,
        //     'updated_at' => $task->updated_at,
            
        // ];

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
        'assigned_to' => 'nullable|exists:users, id'

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
                'message' => 'Task with id {$id} not found'
            ], 404);
        }
      
    }

    /**
     * Remove the specified resource from storage.
     */

    //  public function destroy(string $id)
    //  {
    //      $task = Task::findOrFail($id);
     
    //      if (!$task) {
    //          return response()->json(['message' => 'Task not found or already deleted.'], 404);
    //      }
     
    //      $task->delete();
     
    //      return response()->json(['message' => 'Task deleted successfully.'], 200);
    //  }

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
            'message' => "Task with id {$id} not found"
        ], 404);
    }
}


    /**
     * filters the specified resource from storage.
     */
    public function search(Request $request)
{
    
    $query = $request->input('query');

    
    $tasks = Task::query();

    // search query
    if ($query) {
        $tasks->where(function ($q) use ($query) {
            $q->where('title', 'like', '%' . $query . '%')
              ->orWhere('description', 'like', '%' . $query . '%')
              ->orWhere('category', 'like', '%' . $query . '%')
              ->orWhere('status', 'like', '%' . $query . '%')
              ->orWhere('priority', 'like', '%' . $query . '%')
              ->orWhere('category', 'like', '%' . $query . '%');
          
        });
    }

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

    public function getTasksForUser($userId)
    {
        $user = User::findOrFail($userId);
        $tasks = $user->tasks()->get();  /* Retrieves all tasks for the particular user */

        return response()->json([
            'user' => $user,
            'tasks' => $tasks
        ]);
    }
    
}
