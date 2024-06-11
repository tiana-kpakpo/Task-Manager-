<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Task;
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

        ]);

        //  $task = Task::create($request->all());

         $task = Task::create($validData);

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

        ]);

        try {
            $task = Task::findOrFail($id);
            $task->update($request ->all());
            return$task;

        } catch (ModelNotFoundException $e) {
            return response()-> json([
                'message' => 'Task with id {$id} not found'
            ], 404);
        }
      
    }

    /**
     * Remove the specified resource from storage.
     */

     public function destroy(string $id)
     {
         $task = Task::find($id);
     
         if (!$task) {
             return response()->json(['message' => 'Task not found or already deleted.'], 404);
         }
     
         $task->delete();
     
         return response()->json(['message' => 'Task deleted successfully.'], 200);
     }

    /**
     * filters the specified resource from storage.
     */
    public function search(Request $request)
{
    
    $query = $request->input('query');

    
    $tasks = Task::query();

    // Apply search query to title, description, and any other relevant fields
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

    return response()->json([
        'tasks' => $filteredTasks,
        'message' => 'Tasks retrieved successfully.'
    ]);
}  
    
}
