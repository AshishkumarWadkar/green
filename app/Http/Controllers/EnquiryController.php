<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use App\Models\EnquirySource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class EnquiryController extends Controller
{
    protected function getUsersForEnquiryList()
    {
        $user = auth()->user();
        if ($user->hasRole('Sales')) {
            return User::where('id', $user->id)->get();
        }
        return User::whereHas('roles', fn($q) => $q->where('name', 'Sales'))->get();
    }

    protected function getAssignableSalesReps()
    {
        $user = auth()->user();
        if ($user->hasRole('Sales')) {
            return collect([$user]);
        }
        return User::whereHas('roles', fn($q) => $q->where('name', 'Sales'))->get();
    }

    protected function userCanAccessEnquiry(Enquiry $enquiry): bool
    {
        $user = auth()->user();
        if ($user->hasRole('Sales')) {
            return $enquiry->assigned_to === $user->id;
        }
        return true; 
    }

    public function index(Request $request)
    {
        $sources = EnquirySource::where('is_active', true)->orderBy('sort_order')->get();
        $users = $this->getUsersForEnquiryList();
        
        $leadTypes = [
            (object)['id' => 'Hot', 'name' => 'Hot'],
            (object)['id' => 'Cold', 'name' => 'Cold'],
            (object)['id' => 'Warm', 'name' => 'Warm'],
        ];

        $viewStatus = $request->get('view', 'all'); // 'all' or 'cancelled'

        return view('content.enquiries.index', compact('sources', 'leadTypes', 'users', 'viewStatus'));
    }

    public function getData(Request $request)
    {
        $query = Enquiry::with(['enquirySource', 'assignedUser', 'createdBy']);

        if ($request->filled('date_from')) {
            $query->where('enquiry_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('enquiry_date', '<=', $request->date_to);
        }
        if ($request->filled('source_id')) {
            $query->where('enquiry_source_id', $request->source_id);
        }
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }
        if ($request->filled('lead_type')) {
            $query->where('lead_type', $request->lead_type);
        }
        
        if ($request->get('view') === 'cancelled') {
            $query->where('status', 'Cancelled');
        } else {
            // For 'all' view, user might mean both or just accepted. 
            // Usually 'All' means everything but 'Cancelled' is often separate.
            // But let's allow 'all' to show both and use a separate filter if needed.
            // Actually user said "All Enquiries" and "Cancelled Enquiries" as separate menus.
            // So "All" should probably show everything EXCEPT cancelled, or EVERYTHING.
            // Let's go with All = EVERYTHING, and Cancelled = only cancelled.
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
        }

        $authUser = auth()->user();
        if ($authUser->hasRole('Sales')) {
            $query->where('assigned_to', $authUser->id);
        }

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

                $viewBtn = '<a href="' . route('enquiries.show', $row->id) . '" class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect"><i class="ti ti-eye"></i></a>';

                $editBtn = $canEdit
                    ? '<button class="btn btn-sm btn-icon edit-record btn-text-secondary rounded-pill waves-effect" data-id="' . $row->id . '"><i class="ti ti-edit"></i></button>'
                    : '';
                
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
        return view('content.enquiries.create', compact('sources', 'users'));
    }

    public function store(Request $request)
    {
        $rules = [
            'enquiry_date' => 'required|date',
            'customer_name' => 'required|string|max:255',
            'mobile_number' => 'required|string|max:20',
            'alternate_mobile' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'enquiry_source_id' => 'required|exists:enquiry_sources,id',
            'product_service' => 'nullable|string|max:255',
            'initial_remark' => 'nullable|string',
            'lead_type' => 'required|in:Hot,Cold,Warm',
            'status' => 'required|in:Pending,Accepted,Cancelled',
        ];

        $authUser = auth()->user();
        if (!$authUser->hasRole('Sales')) {
            $rules['assigned_to'] = 'required|exists:users,id';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $assignedTo = $authUser->hasRole('Sales') ? $authUser->id : $request->assigned_to;

            $enquiry = Enquiry::create([
                'enquiry_date' => $request->enquiry_date,
                'customer_name' => $request->customer_name,
                'mobile_number' => $request->mobile_number,
                'alternate_mobile' => $request->alternate_mobile,
                'email' => $request->email,
                'enquiry_source_id' => $request->enquiry_source_id,
                'product_service' => $request->product_service,
                'assigned_to' => $assignedTo,
                'initial_remark' => $request->initial_remark,
                'lead_type' => $request->lead_type,
                'status' => 'Pending', // Force Pending on creation per requirement
                'created_by' => $authUser->id,
                'updated_by' => $authUser->id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Enquiry created successfully.',
                'data' => [
                    'id' => $enquiry->id,
                    'redirect' => route('enquiries.index')
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating enquiry: ' . $e->getMessage()
            ], 500);
        }
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

        $rules = [
            'enquiry_date' => 'required|date',
            'customer_name' => 'required|string|max:255',
            'mobile_number' => 'required|string|max:20',
            'alternate_mobile' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'enquiry_source_id' => 'required|exists:enquiry_sources,id',
            'product_service' => 'nullable|string|max:255',
            'initial_remark' => 'nullable|string',
            'lead_type' => 'required|in:Hot,Cold,Warm',
            'status' => 'required|in:Pending,Accepted,Cancelled',
        ];

        $authUser = auth()->user();
        if (!$authUser->hasRole('Sales')) {
            $rules['assigned_to'] = 'required|exists:users,id';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = [
            'enquiry_date' => $request->enquiry_date,
            'customer_name' => $request->customer_name,
            'mobile_number' => $request->mobile_number,
            'alternate_mobile' => $request->alternate_mobile,
            'email' => $request->email,
            'enquiry_source_id' => $request->enquiry_source_id,
            'product_service' => $request->product_service,
            'initial_remark' => $request->initial_remark,
            'lead_type' => $request->lead_type,
            'status' => $request->status,
            'updated_by' => $authUser->id,
        ];

        if (!$authUser->hasRole('Sales')) {
            $data['assigned_to'] = $request->assigned_to;
        }

        $enquiry->update($data);

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

        $enquiry->delete();

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

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:Accepted,Cancelled,Pending'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status'
            ], 422);
        }

        $enquiry->update([
            'status' => $request->status,
            'updated_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully'
        ]);
    }
}
