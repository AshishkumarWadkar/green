<?php

namespace App\Http\Resources\EnquiryManagement;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnquiryResource extends JsonResource
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
            'enquiry_date' => optional($this->enquiry_date)->toDateString(),
            'customer_name' => $this->customer_name,
            'mobile_number' => $this->mobile_number,
            'alternate_mobile' => $this->alternate_mobile,
            'email' => $this->email,
            'location' => $this->location,
            'pincode' => $this->pincode,
            'product_service' => $this->product_service,
            'enquiry_type' => $this->enquiry_type,
            'initial_remark' => $this->initial_remark,
            'next_follow_up_date' => optional($this->next_follow_up_date)->toDateString(),
            'follow_up_remark' => $this->follow_up_remark,
            'capacity_kw' => $this->capacity_kw,
            'finance_type' => $this->finance_type,
            'shadow_free_area_sqft' => $this->shadow_free_area_sqft,
            'customer_profession' => $this->customer_profession,
            'consumer_number' => $this->consumer_number,
            'lead_type' => $this->lead_type,
            'status' => $this->status,
            'enquiry_source' => $this->enquirySource ? [
                'id' => $this->enquirySource->id,
                'name' => $this->enquirySource->name,
            ] : null,
            'assigned_user' => $this->assignedUser ? [
                'id' => $this->assignedUser->id,
                'name' => $this->assignedUser->name,
            ] : null,
            'created_by' => $this->createdBy ? [
                'id' => $this->createdBy->id,
                'name' => $this->createdBy->name,
            ] : null,
            'follow_ups' => EnquiryFollowUpResource::collection($this->whenLoaded('followUps')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
