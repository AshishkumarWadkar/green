<?php

namespace App\Http\Controllers;

use App\Models\CustomerProfession;
use App\Models\Enquiry;
use App\Models\EnquirySource;
use App\Services\EnquiryManagement\EnquiryManagementService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class EnquiryController extends Controller
{
    public function __construct(private readonly EnquiryManagementService $enquiryManagementService)
    {
    }

    protected function getUsersForEnquiryList()
    {
        return $this->enquiryManagementService->getUsersForEnquiryList(auth()->user());
    }

    protected function getAssignableSalesReps()
    {
        return $this->enquiryManagementService->getAssignableSalesReps(auth()->user());
    }

    protected function userCanAccessEnquiry(Enquiry $enquiry): bool
    {
        try {
            $this->enquiryManagementService->ensureUserCanAccessEnquiry(auth()->user(), $enquiry);
            return true;
        } catch (\DomainException $e) {
            return false;
        }
    }

    public function index(Request $request)
    {
        $filterOptions = $this->enquiryManagementService->getFilterOptions(auth()->user());
        $sources = $filterOptions['sources'];
        $users = $filterOptions['assigned_users'];
        $professions = $filterOptions['customer_professions'];
        $locations = $filterOptions['locations'];
        $pincodes = $filterOptions['pincodes'];
        $leadTypes = collect($filterOptions['lead_types'])->map(fn ($item) => (object) $item)->all();

        $viewStatus = $request->get('view', 'all'); // 'all' or 'cancelled'

        return view('content.enquiries.index', compact('sources', 'leadTypes', 'users', 'professions', 'locations', 'pincodes', 'viewStatus'));
    }

    public function getData(Request $request)
    {
        $query = $this->enquiryManagementService->getEnquiryListingQuery(auth()->user(), $request->all());

        $start = $request->get('start', 0);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('fake_id', function ($row) use ($start) {
                static $index = 0;
                $index++;
                return $start + $index;
            })
            ->editColumn('enquiry_date', function ($row) {
                return $row->enquiry_date->format('d M Y');
            })
            ->editColumn('customer_name', function ($row) {
                return '<span class="fw-medium">' . $row->customer_name . '</span>';
            })
            ->editColumn('mobile_number', function ($row) {
                $html = '<span>' . $row->mobile_number . '</span>';
                if ($row->alternate_mobile) {
                    $html .= '<br><small class="text-muted">' . $row->alternate_mobile . '</small>';
                }
                return $html;
            })
            ->editColumn('enquiry_source_id', function ($row) {
                return $row->enquirySource ? $row->enquirySource->name : '-';
            })
            ->editColumn('assigned_to', function ($row) {
                return $row->assignedUser ? $row->assignedUser->name : '-';
            })
            ->addColumn('created_by', function ($row) {
                return $row->createdBy ? $row->createdBy->name : '-';
            })
            ->editColumn('lead_type', function ($row) {
                $badgeClass = 'bg-label-primary';
                if ($row->lead_type == 'Hot') $badgeClass = 'bg-label-danger';
                if ($row->lead_type == 'Warm') $badgeClass = 'bg-label-warning';
                if ($row->lead_type == 'Cold') $badgeClass = 'bg-label-info';
                
                return '<span class="badge ' . $badgeClass . '">' . $row->lead_type . '</span>';
            })
            ->editColumn('status', function ($row) {
                $badgeClass = 'bg-label-secondary';
                if ($row->status == 'Accepted') $badgeClass = 'bg-label-success';
                if ($row->status == 'Cancelled') $badgeClass = 'bg-label-danger';
                if ($row->status == 'Pending') $badgeClass = 'bg-label-warning';
                return '<span class="badge ' . $badgeClass . '">' . $row->status . '</span>';
            })
            ->addColumn('action', function ($row) {
                $user = auth()->user();
                $canAccess = $this->userCanAccessEnquiry($row);
                $canEdit = $canAccess && $user->can('edit-enquiries');
                $canDelete = $canAccess && $user->can('delete-enquiries');

                $viewBtn = '<button class="btn btn-sm btn-icon view-record btn-text-secondary rounded-pill waves-effect" data-id="' . $row->id . '" data-can-edit="' . ($canEdit ? 1 : 0) . '" title="View"><i class="ti ti-eye"></i></button>';

                $editBtn = '';
                
                $deleteBtn = $canDelete
                    ? '<button class="btn btn-sm btn-icon delete-record btn-text-secondary rounded-pill waves-effect" data-id="' . $row->id . '"><i class="ti ti-trash"></i></button>'
                    : '';

                $statusBtns = '';
                if ($row->status === 'Pending' && $canEdit) {
                    $statusBtns = '<button class="btn btn-sm btn-icon update-status btn-text-success rounded-pill waves-effect" data-id="' . $row->id . '" data-status="Accepted" title="Accept"><i class="ti ti-check"></i></button>';
                    $statusBtns .= '<button class="btn btn-sm btn-icon update-status btn-text-danger rounded-pill waves-effect" data-id="' . $row->id . '" data-status="Cancelled" title="Cancel"><i class="ti ti-x"></i></button>';
                }

                return '<div class="d-flex align-items-center gap-50">' . $viewBtn . $statusBtns . $editBtn . $deleteBtn . '</div>';
            })
            ->rawColumns(['customer_name', 'mobile_number', 'lead_type', 'status', 'action'])
            ->make(true);
    }

    public function create()
    {
        $sources = EnquirySource::where('is_active', true)->orderBy('sort_order')->get();
        $users = $this->getAssignableSalesReps();
        $professions = CustomerProfession::where('is_active', true)->orderBy('sort_order')->get();
        return view('content.enquiries.create', compact('sources', 'users', 'professions'));
    }

    public function store(Request $request)
    {
        try {
            $enquiry = $this->enquiryManagementService->createEnquiry(auth()->user(), $request->all());
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating enquiry: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Enquiry created successfully.',
            'data' => [
                'id' => $enquiry->id,
                'redirect' => route('enquiries.index')
            ]
        ]);
    }

    public function show($id)
    {
        $enquiry = Enquiry::with([
            'enquirySource',
            'assignedUser',
            'createdBy'
        ])->findOrFail($id);

        if (!$this->userCanAccessEnquiry($enquiry)) {
            abort(403, 'You do not have permission to view this enquiry.');
        }

        return view('content.enquiries.show', compact('enquiry'));
    }

    public function edit($id)
    {
        if (!auth()->user()->can('edit-enquiries')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit enquiries.'
            ], 403);
        }

        $enquiry = Enquiry::findOrFail($id);

        if (!$this->userCanAccessEnquiry($enquiry)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit this enquiry.'
            ], 403);
        }

        $sources = EnquirySource::where('is_active', true)->orderBy('sort_order')->get();
        $users = $this->getAssignableSalesReps();

        $enquiry->load(['enquirySource', 'assignedUser']);

        $enquiryData = $enquiry->toArray();
        $enquiryData['enquiry_date'] = $enquiry->enquiry_date->format('Y-m-d');
        $enquiryData['next_follow_up_date'] = $enquiry->next_follow_up_date ? $enquiry->next_follow_up_date->format('Y-m-d') : null;

        return response()->json([
            'success' => true,
            'data' => [
                'enquiry' => $enquiryData,
                'sources' => $sources,
                'users' => $users,
                'leadTypes' => [
                    (object)['id' => 'Hot', 'name' => 'Hot'],
                    (object)['id' => 'Cold', 'name' => 'Cold'],
                    (object)['id' => 'Warm', 'name' => 'Warm'],
                ],
                'statuses' => [
                    (object)['id' => 'Pending', 'name' => 'Pending'],
                    (object)['id' => 'Accepted', 'name' => 'Accepted'],
                    (object)['id' => 'Cancelled', 'name' => 'Cancelled'],
                ]
            ]
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('edit-enquiries')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update enquiries.'
            ], 403);
        }

        $enquiry = Enquiry::findOrFail($id);

        if (!$this->userCanAccessEnquiry($enquiry)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update this enquiry.'
            ], 403);
        }

        try {
            $this->enquiryManagementService->updateEnquiry(auth()->user(), $enquiry, $request->all());
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Enquiry updated successfully.'
        ]);
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('delete-enquiries')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete enquiries.'
            ], 403);
        }

        $enquiry = Enquiry::findOrFail($id);
        
        if (!$this->userCanAccessEnquiry($enquiry)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete this enquiry.'
            ], 403);
        }

        $this->enquiryManagementService->deleteEnquiry(auth()->user(), $enquiry);

        return response()->json([
            'success' => true,
            'message' => 'Enquiry deleted successfully.'
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        if (!auth()->user()->can('edit-enquiries')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update enquiries.'
            ], 403);
        }

        $enquiry = Enquiry::findOrFail($id);
        
        if (!$this->userCanAccessEnquiry($enquiry)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update this enquiry.'
            ], 403);
        }

        try {
            $this->enquiryManagementService->updateEnquiryStatus(auth()->user(), $enquiry, $request->all());
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status',
                'errors' => $e->errors(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully'
        ]);
    }
}
