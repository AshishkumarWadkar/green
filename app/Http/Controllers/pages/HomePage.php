<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use Illuminate\Support\Facades\DB;

class HomePage extends Controller
{
    public function index()
    {
        $today = now()->toDateString();
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();
        $authUser = auth()->user();

        // Defaults
        $enquiryCountToday = 0;
        $enquiryCountMonth = 0;
        $sourceWise = collect();
        $statusWise = collect();
        $trendDates = [];
        $trendCounts = [];

        if ($authUser->can('view-enquiries')) {
            $enquiryQuery = Enquiry::query();
            if ($authUser->hasRole('Sales')) {
                $enquiryQuery->where('assigned_to', $authUser->id);
            }

            $enquiryCountToday = (clone $enquiryQuery)->whereDate('enquiry_date', $today)->count();
            $enquiryCountMonth = (clone $enquiryQuery)->whereBetween('enquiry_date', [$startOfMonth, $endOfMonth])->count();
            
            // Status-wise counts
            $pendingEnquiries = (clone $enquiryQuery)->where('status', 'Pending')->count();
            $acceptedEnquiries = (clone $enquiryQuery)->where('status', 'Accepted')->count();
            $cancelledEnquiries = (clone $enquiryQuery)->where('status', 'Cancelled')->count();
            $totalEnquiries = (clone $enquiryQuery)->count();

            // Lead Type counts
            $hotLeads = (clone $enquiryQuery)->where('lead_type', 'Hot')->count();
            $warmLeads = (clone $enquiryQuery)->where('lead_type', 'Warm')->count();
            $coldLeads = (clone $enquiryQuery)->where('lead_type', 'Cold')->count();

            // Conversion rate calculation
            $conversionRate = $totalEnquiries > 0 ? round(($acceptedEnquiries / $totalEnquiries) * 100, 1) : 0;

            $sourceWise = (clone $enquiryQuery)
                ->join('enquiry_sources', 'enquiries.enquiry_source_id', '=', 'enquiry_sources.id')
                ->select('enquiry_sources.name', DB::raw('count(*) as total'))
                ->groupBy('enquiry_sources.name')
                ->get();

            $statusWise = (clone $enquiryQuery)
                ->select('status as name', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->get();

            $leadTypeWise = (clone $enquiryQuery)
                ->select('lead_type as name', DB::raw('count(*) as total'))
                ->groupBy('lead_type')
                ->get();

            for ($i = 13; $i >= 0; $i--) {
                $date = now()->subDays($i)->toDateString();
                $trendDates[] = \Carbon\Carbon::parse($date)->format('d M');
                $trendCounts[] = (clone $enquiryQuery)->whereDate('enquiry_date', $date)->count();
            }

            // Monthly Status comparison trend (Last 6 months)
            $monthlyTrendLabels = [];
            $monthlyAccepted = [];
            $monthlyCancelled = [];
            $monthlyPending = [];
            
            for ($i = 5; $i >= 0; $i--) {
                $monthDate = now()->subMonths($i);
                $monthLabel = $monthDate->format('M Y');
                $start = $monthDate->startOfMonth()->toDateString();
                $end = $monthDate->endOfMonth()->toDateString();
                
                $monthlyTrendLabels[] = $monthLabel;
                $monthlyAccepted[] = (clone $enquiryQuery)->where('status', 'Accepted')->whereBetween('enquiry_date', [$start, $end])->count();
                $monthlyCancelled[] = (clone $enquiryQuery)->where('status', 'Cancelled')->whereBetween('enquiry_date', [$start, $end])->count();
                $monthlyPending[] = (clone $enquiryQuery)->where('status', 'Pending')->whereBetween('enquiry_date', [$start, $end])->count();
            }
        }

        return view('content.pages.pages-home', compact(
            'enquiryCountToday',
            'enquiryCountMonth',
            'pendingEnquiries',
            'acceptedEnquiries',
            'cancelledEnquiries',
            'totalEnquiries',
            'hotLeads',
            'warmLeads',
            'coldLeads',
            'conversionRate',
            'sourceWise',
            'statusWise',
            'leadTypeWise',
            'trendDates',
            'trendCounts',
            'monthlyTrendLabels',
            'monthlyAccepted',
            'monthlyCancelled',
            'monthlyPending'
        ));
    }
}
