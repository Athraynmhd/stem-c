<?php

namespace App\Models\Contact;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected function casts(): array
    {
        return [
            'consent' => 'boolean',
        ];
    }

    protected $fillable = [
        'name',
        'email',
        'subject',
        'intent',
        'message',
        'consent',
        'status',
    ];
}
