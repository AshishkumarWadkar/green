<?php

namespace App\Http\Resources\EnquiryManagement;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnquiryFollowUpResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'enquiry_id' => $this->enquiry_id,
            'follow_up_date' => optional($this->follow_up_date)->toDateString(),
            'previous_status' => $this->previous_status,
            'new_status' => $this->new_status,
            'remark' => $this->remark,
            'next_follow_up_date' => optional($this->next_follow_up_date)->toDateString(),
            'is_done' => (bool) $this->is_done,
            'enquiry' => $this->enquiry ? [
                'id' => $this->enquiry->id,
                'customer_name' => $this->enquiry->customer_name,
                'mobile_number' => $this->enquiry->mobile_number,
                'status' => $this->enquiry->status,
                'next_follow_up_date' => optional($this->enquiry->next_follow_up_date)->toDateString(),
                'assigned_user' => $this->enquiry->assignedUser ? [
                    'id' => $this->enquiry->assignedUser->id,
                    'name' => $this->enquiry->assignedUser->name,
                ] : null,
            ] : null,
            'created_by' => $this->createdBy ? [
                'id' => $this->createdBy->id,
                'name' => $this->createdBy->name,
            ] : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
