<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'due_date',
        'priority',
        'category',
        'status',
        'reminders',
        'recurrence',
        'assigned_to'
    ];

    protected $attributes = [
        'status' => 'pending'
    ];

    public function users () : BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
