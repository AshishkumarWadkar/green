<?php

namespace App\Services\EnquiryManagement;

use App\Models\CustomerProfession;
use App\Models\Enquiry;
use App\Models\EnquiryFollowUp;
use App\Models\EnquirySource;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class EnquiryManagementService
{
    public function getUsersForEnquiryList(User $user): Collection
    {
        if ($user->hasRole('Sales')) {
            return User::where('id', $user->id)->get();
        }

        return User::whereHas('roles', fn ($q) => $q->where('name', 'Sales'))->get();
    }

    public function getAssignableSalesReps(User $user): Collection
    {
        if ($user->hasRole('Sales')) {
            return collect([$user]);
        }

        return User::whereHas('roles', fn ($q) => $q->where('name', 'Sales'))->get();
    }

    public function getEnquiryListingQuery(User $authUser, array $filters = []): Builder
    {
        $query = Enquiry::query()->with(['enquirySource', 'assignedUser', 'createdBy']);

        if (!empty($filters['date_from'])) {
            $query->where('enquiry_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->where('enquiry_date', '<=', $filters['date_to']);
        }
        if (!empty($filters['source_id'])) {
            $query->where('enquiry_source_id', $filters['source_id']);
        }
        if (!empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }
        if (!empty($filters['lead_type'])) {
            $query->where('lead_type', $filters['lead_type']);
        }
        if (!empty($filters['location'])) {
            $query->where('location', $filters['location']);
        }
        if (!empty($filters['pincode'])) {
            $query->where('pincode', $filters['pincode']);
        }
        if (!empty($filters['enquiry_type'])) {
            $query->where('enquiry_type', $filters['enquiry_type']);
        }
        if (!empty($filters['finance_type'])) {
            $query->where('finance_type', $filters['finance_type']);
        }
        if (!empty($filters['customer_profession'])) {
            $query->where('customer_profession', $filters['customer_profession']);
        }

        if (($filters['view'] ?? 'all') === 'cancelled') {
            $query->where('status', 'Cancelled');
        } elseif (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if ($authUser->hasRole('Sales')) {
            $query->where('assigned_to', $authUser->id);
        }

        return $query->orderByDesc('enquiry_date')->orderByDesc('id');
    }

    public function getFollowUpListingQuery(User $authUser, array $filters = []): Builder
    {
        $query = Enquiry::query()
            ->with(['enquirySource', 'assignedUser', 'createdBy'])
            ->where('status', 'Pending');

        if (($filters['scope'] ?? 'today') !== 'all') {
            $query->whereDate('next_follow_up_date', '<=', now()->toDateString());
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('next_follow_up_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('next_follow_up_date', '<=', $filters['date_to']);
        }
        if (!empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        if ($authUser->hasRole('Sales')) {
            $query->where('assigned_to', $authUser->id);
        }

        return $query->orderBy('next_follow_up_date')->orderByDesc('id');
    }

    public function getCompletedFollowUpListingQuery(User $authUser, array $filters = []): Builder
    {
        $query = EnquiryFollowUp::query()
            ->with(['enquiry.assignedUser', 'createdBy'])
            ->whereHas('enquiry')
            ->where('is_done', true);

        if (!empty($filters['date_from'])) {
            $query->whereDate('follow_up_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('follow_up_date', '<=', $filters['date_to']);
        }
        if (!empty($filters['assigned_to'])) {
            $query->whereHas('enquiry', function ($enquiryQuery) use ($filters) {
                $enquiryQuery->where('assigned_to', $filters['assigned_to']);
            });
        }

        if ($authUser->hasRole('Sales')) {
            $query->whereHas('enquiry', function ($enquiryQuery) use ($authUser) {
                $enquiryQuery->where('assigned_to', $authUser->id);
            });
        }

        return $query->orderByDesc('follow_up_date')->orderByDesc('created_at');
    }

    public function getFollowUpForEdit(User $authUser, int $followUpId): EnquiryFollowUp
    {
        $followUp = EnquiryFollowUp::with(['enquiry.assignedUser', 'createdBy'])->findOrFail($followUpId);
        $this->ensureUserCanAccessEnquiry($authUser, $followUp->enquiry);

        return $followUp;
    }

    public function getFilterOptions(User $authUser, ?string $type = null): array
    {
        $all = [
            'sources' => EnquirySource::where('is_active', true)->orderBy('sort_order')->get(['id', 'name']),
            'assigned_users' => $this->getUsersForEnquiryList($authUser),
            'lead_types' => collect(['Hot', 'Cold', 'Warm'])->map(fn ($value) => ['id' => $value, 'name' => $value])->values(),
            'locations' => Enquiry::query()->whereNotNull('location')->where('location', '!=', '')->distinct()->orderBy('location')->pluck('location')->values(),
            'pincodes' => Enquiry::query()->whereNotNull('pincode')->where('pincode', '!=', '')->distinct()->orderBy('pincode')->pluck('pincode')->values(),
            'enquiry_types' => Enquiry::query()->whereNotNull('enquiry_type')->where('enquiry_type', '!=', '')->distinct()->orderBy('enquiry_type')->pluck('enquiry_type')->values(),
            'finance_types' => Enquiry::query()->whereNotNull('finance_type')->where('finance_type', '!=', '')->distinct()->orderBy('finance_type')->pluck('finance_type')->values(),
            'customer_professions' => CustomerProfession::where('is_active', true)->orderBy('sort_order')->get(['id', 'name']),
            'statuses' => collect(['Pending', 'Accepted', 'Cancelled'])->map(fn ($value) => ['id' => $value, 'name' => $value])->values(),
        ];

        if ($type === null || $type === '') {
            return $all;
        }

        return [$type => $all[$type] ?? collect([])];
    }

    public function ensureUserCanAccessEnquiry(User $user, Enquiry $enquiry): void
    {
        if ($user->hasRole('Sales') && (int) $enquiry->assigned_to !== (int) $user->id) {
            throw new \DomainException('You do not have permission to access this enquiry.');
        }
    }

    /**
     * @throws ValidationException
     */
    public function createEnquiry(User $authUser, array $payload): Enquiry
    {
        $data = $this->validateEnquiryPayload($authUser, $payload);

        return DB::transaction(function () use ($authUser, $data) {
            $enquiry = Enquiry::create([
                'enquiry_date' => $data['enquiry_date'],
                'customer_name' => $data['customer_name'],
                'mobile_number' => $data['mobile_number'],
                'alternate_mobile' => $data['alternate_mobile'] ?? null,
                'email' => $data['email'] ?? null,
                'location' => $data['location'] ?? null,
                'pincode' => $data['pincode'] ?? null,
                'enquiry_source_id' => $data['enquiry_source_id'],
                'product_service' => $data['product_service'] ?? null,
                'enquiry_type' => $data['enquiry_type'] ?? null,
                'assigned_to' => $authUser->hasRole('Sales') ? $authUser->id : $data['assigned_to'],
                'initial_remark' => $data['initial_remark'],
                'next_follow_up_date' => $data['next_follow_up_date'] ?? null,
                'capacity_kw' => $data['capacity_kw'] ?? null,
                'finance_type' => $data['finance_type'] ?? null,
                'shadow_free_area_sqft' => $data['shadow_free_area_sqft'] ?? null,
                'customer_profession' => $data['customer_profession'] ?? null,
                'consumer_number' => $data['consumer_number'] ?? null,
                'lead_type' => $data['lead_type'],
                'status' => 'Pending',
                'created_by' => $authUser->id,
                'updated_by' => $authUser->id,
            ]);

            return $enquiry->load(['enquirySource', 'assignedUser', 'createdBy']);
        });
    }

    /**
     * @throws ValidationException
     */
    public function updateEnquiry(User $authUser, Enquiry $enquiry, array $payload): Enquiry
    {
        $this->ensureUserCanAccessEnquiry($authUser, $enquiry);
        $data = $this->validateEnquiryPayload($authUser, $payload);

        $updateData = [
            'enquiry_date' => $data['enquiry_date'],
            'customer_name' => $data['customer_name'],
            'mobile_number' => $data['mobile_number'],
            'alternate_mobile' => $data['alternate_mobile'] ?? null,
            'email' => $data['email'] ?? null,
            'location' => $data['location'] ?? null,
            'pincode' => $data['pincode'] ?? null,
            'enquiry_source_id' => $data['enquiry_source_id'],
            'product_service' => $data['product_service'] ?? null,
            'enquiry_type' => $data['enquiry_type'] ?? null,
            'initial_remark' => $data['initial_remark'],
            'next_follow_up_date' => $data['next_follow_up_date'] ?? null,
            'capacity_kw' => $data['capacity_kw'] ?? null,
            'finance_type' => $data['finance_type'] ?? null,
            'shadow_free_area_sqft' => $data['shadow_free_area_sqft'] ?? null,
            'customer_profession' => $data['customer_profession'] ?? null,
            'consumer_number' => $data['consumer_number'] ?? null,
            'lead_type' => $data['lead_type'],
            'status' => $data['status'],
            'updated_by' => $authUser->id,
        ];

        if (!$authUser->hasRole('Sales')) {
            $updateData['assigned_to'] = $data['assigned_to'];
        }

        $enquiry->update($updateData);

        return $enquiry->fresh(['enquirySource', 'assignedUser', 'createdBy']);
    }

    public function deleteEnquiry(User $authUser, Enquiry $enquiry): void
    {
        $this->ensureUserCanAccessEnquiry($authUser, $enquiry);
        $enquiry->delete();
    }

    /**
     * @throws ValidationException
     */
    public function updateEnquiryStatus(User $authUser, Enquiry $enquiry, array $payload): Enquiry
    {
        $this->ensureUserCanAccessEnquiry($authUser, $enquiry);

        $validator = Validator::make($payload, [
            'status' => 'required|in:Accepted,Cancelled,Pending',
            'follow_up_remark' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $data = $validator->validated();
        if ($data['status'] !== 'Pending') {
            $data['next_follow_up_date'] = null;
        }

        $enquiry->update([
            'status' => $data['status'],
            'follow_up_remark' => $data['follow_up_remark'],
            'next_follow_up_date' => $data['next_follow_up_date'] ?? null,
            'updated_by' => $authUser->id,
        ]);

        return $enquiry->fresh(['enquirySource', 'assignedUser', 'createdBy']);
    }

    /**
     * @throws ValidationException
     */
    public function completeFollowUp(User $authUser, Enquiry $enquiry, array $payload): Enquiry
    {
        $this->ensureUserCanAccessEnquiry($authUser, $enquiry);

        $validator = Validator::make($payload, [
            'status' => 'required|in:Pending,Accepted,Cancelled',
            'remark' => 'required|string|min:3',
            'next_follow_up_date' => 'nullable|date|after_or_equal:today',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $data = $validator->validated();

        if ($data['status'] === 'Pending' && empty($data['next_follow_up_date'])) {
            throw ValidationException::withMessages([
                'next_follow_up_date' => ['Next follow-up date is required when status is Pending.']
            ]);
        }

        if ($data['status'] !== 'Pending') {
            $data['next_follow_up_date'] = null;
        }

        return DB::transaction(function () use ($authUser, $enquiry, $data) {
            EnquiryFollowUp::create([
                'enquiry_id' => $enquiry->id,
                'follow_up_date' => now()->toDateString(),
                'previous_status' => $enquiry->status,
                'new_status' => $data['status'],
                'remark' => $data['remark'],
                'next_follow_up_date' => $data['next_follow_up_date'] ?? null,
                'is_done' => $data['status'] !== 'Pending',
                'created_by' => $authUser->id,
            ]);

            $enquiry->update([
                'status' => $data['status'],
                'follow_up_remark' => $data['remark'],
                'next_follow_up_date' => $data['next_follow_up_date'] ?? null,
                'updated_by' => $authUser->id,
            ]);

            return $enquiry->fresh(['enquirySource', 'assignedUser', 'createdBy']);
        });
    }

    /**
     * @throws ValidationException
     */
    public function updateFollowUp(User $authUser, EnquiryFollowUp $followUp, array $payload): EnquiryFollowUp
    {
        $enquiry = $followUp->enquiry()->firstOrFail();
        $this->ensureUserCanAccessEnquiry($authUser, $enquiry);

        $validator = Validator::make($payload, [
            'status' => 'required|in:Pending,Accepted,Cancelled',
            'remark' => 'required|string|min:3',
            'next_follow_up_date' => 'nullable|date|after_or_equal:today',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $data = $validator->validated();

        if ($data['status'] === 'Pending' && empty($data['next_follow_up_date'])) {
            throw ValidationException::withMessages([
                'next_follow_up_date' => ['Next follow-up date is required when status is Pending.']
            ]);
        }

        if ($data['status'] !== 'Pending') {
            $data['next_follow_up_date'] = null;
        }

        return DB::transaction(function () use ($followUp, $enquiry, $authUser, $data) {
            $followUp->update([
                'new_status' => $data['status'],
                'remark' => $data['remark'],
                'next_follow_up_date' => $data['next_follow_up_date'] ?? null,
                'is_done' => $data['status'] !== 'Pending',
            ]);

            $latestFollowUpId = $enquiry->followUps()->latest('created_at')->value('id');
            if ((int) $latestFollowUpId === (int) $followUp->id) {
                $enquiry->update([
                    'status' => $data['status'],
                    'follow_up_remark' => $data['remark'],
                    'next_follow_up_date' => $data['next_follow_up_date'] ?? null,
                    'updated_by' => $authUser->id,
                ]);
            }

            return $followUp->fresh(['enquiry.assignedUser', 'createdBy']);
        });
    }

    /**
     * @throws ValidationException
     */
    private function validateEnquiryPayload(User $authUser, array $payload): array
    {
        $normalized = $this->normalizeEnquiryPayload($payload);
        $validator = Validator::make($normalized, $this->enquiryValidationRules($authUser), $this->enquiryValidationMessages());

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    private function normalizeEnquiryPayload(array $payload): array
    {
        $payload['mobile_number'] = preg_replace('/\D+/', '', (string) ($payload['mobile_number'] ?? ''));
        $payload['alternate_mobile'] = !empty($payload['alternate_mobile'])
            ? preg_replace('/\D+/', '', (string) $payload['alternate_mobile'])
            : null;
        $payload['pincode'] = !empty($payload['pincode'])
            ? preg_replace('/\D+/', '', (string) $payload['pincode'])
            : null;
        $payload['location'] = !empty($payload['location'])
            ? ucfirst(strtolower(trim((string) $payload['location'])))
            : null;
        if (($payload['status'] ?? null) !== 'Pending') {
            $payload['next_follow_up_date'] = null;
        }

        return $payload;
    }

    private function enquiryValidationRules(User $authUser): array
    {
        $rules = [
            'enquiry_date' => 'required|date',
            'customer_name' => 'required|string|max:255',
            'mobile_number' => ['required', 'regex:/^[6-9][0-9]{9}$/'],
            'alternate_mobile' => ['nullable', 'regex:/^[6-9][0-9]{9}$/'],
            'email' => 'nullable|email|max:255',
            'location' => 'nullable|string|max:255',
            'pincode' => ['nullable', 'regex:/^[0-9]{6}$/'],
            'enquiry_source_id' => 'required|exists:enquiry_sources,id',
            'product_service' => 'nullable|string|max:255',
            'enquiry_type' => 'nullable|in:Residential,Industrial,Commercial',
            'initial_remark' => 'required|string|min:3',
            'next_follow_up_date' => 'nullable|required_if:status,Pending|date|after_or_equal:today|after_or_equal:enquiry_date',
            'capacity_kw' => 'nullable|numeric|min:0',
            'finance_type' => 'nullable|in:Credit,Cash,EMI,Other',
            'shadow_free_area_sqft' => 'nullable|numeric|min:0',
            'customer_profession' => 'nullable|string|max:255',
            'consumer_number' => 'nullable|string|max:50',
            'lead_type' => 'required|in:Hot,Cold,Warm',
            'status' => 'required|in:Pending,Accepted,Cancelled',
        ];

        if (!$authUser->hasRole('Sales')) {
            $rules['assigned_to'] = 'required|exists:users,id';
        }

        return $rules;
    }

    private function enquiryValidationMessages(): array
    {
        return [
            'mobile_number.regex' => 'Mobile number must be a valid 10-digit Indian number.',
            'alternate_mobile.regex' => 'Alternate mobile must be a valid 10-digit Indian number.',
            'pincode.regex' => 'Pincode must be a valid 6-digit number.',
            'next_follow_up_date.required_if' => 'Please select next follow-up date',
            'next_follow_up_date.after_or_equal' => 'Next follow-up date must be today or later, and not before enquiry date.',
        ];
    }
}
