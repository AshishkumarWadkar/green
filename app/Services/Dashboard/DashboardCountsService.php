<?php

namespace App\Services\Dashboard;

use App\Models\Enquiry;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardCountsService
{
    /**
     * Dashboard statistics aligned with the web home dashboard (scoped by role).
     *
     * @return array<string, mixed>
     */
    public function build(User $authUser): array
    {
        $today = now()->toDateString();
        $startOfMonth = now()->copy()->startOfMonth()->toDateString();
        $endOfMonth = now()->copy()->endOfMonth()->toDateString();

        $defaults = [
            'enquiryCountToday' => 0,
            'enquiryCountMonth' => 0,
            'pendingEnquiries' => 0,
            'acceptedEnquiries' => 0,
            'cancelledEnquiries' => 0,
            'totalEnquiries' => 0,
            'hotLeads' => 0,
            'warmLeads' => 0,
            'coldLeads' => 0,
            'conversionRate' => 0,
            'sourceWise' => collect(),
            'statusWise' => collect(),
            'leadTypeWise' => collect(),
            'trendDates' => [],
            'trendCounts' => [],
            'monthlyTrendLabels' => [],
            'monthlyAccepted' => [],
            'monthlyCancelled' => [],
            'monthlyPending' => [],
        ];

        if (!$authUser->can('view-enquiries')) {
            return $defaults;
        }

        $enquiryQuery = Enquiry::query();
        if ($authUser->hasRole('Sales')) {
            $enquiryQuery->where('assigned_to', $authUser->id);
        }

        $enquiryCountToday = (clone $enquiryQuery)->whereDate('enquiry_date', $today)->count();
        $enquiryCountMonth = (clone $enquiryQuery)->whereBetween('enquiry_date', [$startOfMonth, $endOfMonth])->count();

        $pendingEnquiries = (clone $enquiryQuery)->where('status', 'Pending')->count();
        $acceptedEnquiries = (clone $enquiryQuery)->where('status', 'Accepted')->count();
        $cancelledEnquiries = (clone $enquiryQuery)->where('status', 'Cancelled')->count();
        $totalEnquiries = (clone $enquiryQuery)->count();

        $hotLeads = (clone $enquiryQuery)->where('lead_type', 'Hot')->count();
        $warmLeads = (clone $enquiryQuery)->where('lead_type', 'Warm')->count();
        $coldLeads = (clone $enquiryQuery)->where('lead_type', 'Cold')->count();

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

        $trendDates = [];
        $trendCounts = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $trendDates[] = \Carbon\Carbon::parse($date)->format('d M');
            $trendCounts[] = (clone $enquiryQuery)->whereDate('enquiry_date', $date)->count();
        }

        $monthlyTrendLabels = [];
        $monthlyAccepted = [];
        $monthlyCancelled = [];
        $monthlyPending = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthDate = now()->copy()->subMonths($i);
            $monthLabel = $monthDate->format('M Y');
            $start = $monthDate->copy()->startOfMonth()->toDateString();
            $end = $monthDate->copy()->endOfMonth()->toDateString();

            $monthlyTrendLabels[] = $monthLabel;
            $monthlyAccepted[] = (clone $enquiryQuery)->where('status', 'Accepted')->whereBetween('enquiry_date', [$start, $end])->count();
            $monthlyCancelled[] = (clone $enquiryQuery)->where('status', 'Cancelled')->whereBetween('enquiry_date', [$start, $end])->count();
            $monthlyPending[] = (clone $enquiryQuery)->where('status', 'Pending')->whereBetween('enquiry_date', [$start, $end])->count();
        }

        return [
            'enquiryCountToday' => $enquiryCountToday,
            'enquiryCountMonth' => $enquiryCountMonth,
            'pendingEnquiries' => $pendingEnquiries,
            'acceptedEnquiries' => $acceptedEnquiries,
            'cancelledEnquiries' => $cancelledEnquiries,
            'totalEnquiries' => $totalEnquiries,
            'hotLeads' => $hotLeads,
            'warmLeads' => $warmLeads,
            'coldLeads' => $coldLeads,
            'conversionRate' => $conversionRate,
            'sourceWise' => $sourceWise,
            'statusWise' => $statusWise,
            'leadTypeWise' => $leadTypeWise,
            'trendDates' => $trendDates,
            'trendCounts' => $trendCounts,
            'monthlyTrendLabels' => $monthlyTrendLabels,
            'monthlyAccepted' => $monthlyAccepted,
            'monthlyCancelled' => $monthlyCancelled,
            'monthlyPending' => $monthlyPending,
        ];
    }
}
