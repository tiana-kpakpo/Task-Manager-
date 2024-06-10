<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'recurrence'
    ];

    protected $attributes = [
        'status' => 'pending'
    ];
}
