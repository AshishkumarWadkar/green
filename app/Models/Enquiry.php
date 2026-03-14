<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Enquiry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'enquiry_date',
        'customer_name',
        'mobile_number',
        'alternate_mobile',
        'email',
        'enquiry_source_id',
        'product_service',
        'assigned_to',
        'initial_remark',
        'lead_type',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'enquiry_date' => 'date',
    ];

    // Relationships
    public function enquirySource()
    {
        return $this->belongsTo(EnquirySource::class, 'enquiry_source_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeByLeadType($query, $leadType)
    {
        return $query->where('lead_type', $leadType);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeBySource($query, $sourceId)
    {
        return $query->where('enquiry_source_id', $sourceId);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('enquiry_date', [$startDate, $endDate]);
    }
}
