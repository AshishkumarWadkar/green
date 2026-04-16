<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnquiryFollowUp extends Model
{
    use HasFactory;

    protected $fillable = [
        'enquiry_id',
        'follow_up_date',
        'previous_status',
        'new_status',
        'remark',
        'next_follow_up_date',
        'is_done',
        'created_by',
    ];

    protected $casts = [
        'follow_up_date' => 'date',
        'next_follow_up_date' => 'date',
        'is_done' => 'boolean',
    ];

    public function enquiry()
    {
        return $this->belongsTo(Enquiry::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
