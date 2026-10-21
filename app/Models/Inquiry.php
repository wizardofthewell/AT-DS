<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    use Auditable;

    public const STATUSES = [
        'new' => 'New',
        'contacted' => 'Contacted',
        'booked' => 'Booked',
        'closed' => 'Closed',
    ];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'due_date',
        'service_interest',
        'message',
        'status',
        'notes',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
        ];
    }
}
