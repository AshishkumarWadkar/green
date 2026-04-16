<?php

namespace App\Http\Controllers\Api\Enquiry;

use App\Http\Controllers\Controller;
use App\Http\Resources\EnquiryManagement\EnquiryResource;
use App\Models\Enquiry;
use App\Services\EnquiryManagement\EnquiryManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EnquiryController extends Controller
{
    public function __construct(private readonly EnquiryManagementService $enquiryManagementService)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('view-enquiries');

        $perPage = max(1, min((int) $request->integer('per_page', 15), 100));
        $query = $this->enquiryManagementService->getEnquiryListingQuery($request->user(), $request->all());

        return EnquiryResource::collection($query->paginate($perPage));
    }

    public function show(Request $request, Enquiry $enquiry): JsonResponse
    {
        $this->authorize('view-enquiries');

        try {
            $this->enquiryManagementService->ensureUserCanAccessEnquiry($request->user(), $enquiry);
        } catch (\DomainException $e) {
            return response()->json([
                'message' => 'You do not have permission to view this enquiry.',
            ], 403);
        }

        $enquiry->load(['enquirySource', 'assignedUser', 'createdBy']);

        return response()->json([
            'data' => new EnquiryResource($enquiry),
        ]);
    }

    public function filterOptions(Request $request): JsonResponse
    {
        $this->authorize('view-enquiries');

        $validator = Validator::make($request->all(), [
            'type' => 'nullable|string|in:sources,assigned_users,lead_types,locations,pincodes,enquiry_types,finance_types,customer_professions,statuses',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $type = $request->query('type');
        $data = $this->enquiryManagementService->getFilterOptions($request->user(), $type);

        return response()->json([
            'data' => $data,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create-enquiries');

        try {
            $enquiry = $this->enquiryManagementService->createEnquiry($request->user(), $request->all());
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating enquiry: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Enquiry created successfully.',
            'data' => new EnquiryResource($enquiry),
        ], 201);
    }

    public function update(Request $request, Enquiry $enquiry): JsonResponse
    {
        $this->authorize('edit-enquiries');

        try {
            $enquiry = $this->enquiryManagementService->updateEnquiry($request->user(), $enquiry, $request->all());
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\DomainException $e) {
            return response()->json([
                'message' => 'You do not have permission to update this enquiry.',
            ], 403);
        }

        return response()->json([
            'message' => 'Enquiry updated successfully.',
            'data' => new EnquiryResource($enquiry),
        ]);
    }

    public function destroy(Request $request, Enquiry $enquiry): JsonResponse
    {
        $this->authorize('delete-enquiries');

        try {
            $this->enquiryManagementService->deleteEnquiry($request->user(), $enquiry);
        } catch (\DomainException $e) {
            return response()->json([
                'message' => 'You do not have permission to delete this enquiry.',
            ], 403);
        }

        return response()->json([
            'message' => 'Enquiry deleted successfully.',
        ]);
    }

    public function updateStatus(Request $request, Enquiry $enquiry): JsonResponse
    {
        $this->authorize('edit-enquiries');

        try {
            $enquiry = $this->enquiryManagementService->updateEnquiryStatus($request->user(), $enquiry, $request->all());
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Invalid status',
                'errors' => $e->errors(),
            ], 422);
        } catch (\DomainException $e) {
            return response()->json([
                'message' => 'You do not have permission to update this enquiry.',
            ], 403);
        }

        return response()->json([
            'message' => 'Status updated successfully',
            'data' => new EnquiryResource($enquiry),
        ]);
    }
}
