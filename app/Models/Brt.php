<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'brt_code',
        'reserved_amount',
        'status',
    ];

    /**
     * Relationship: A BRT belongs to a User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
