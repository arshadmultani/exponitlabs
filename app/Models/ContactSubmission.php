<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactSubmission extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'organization',
        'message',
        'handled',
    ];

    protected function casts(): array
    {
        return [
            'handled' => 'boolean',
        ];
    }
}
