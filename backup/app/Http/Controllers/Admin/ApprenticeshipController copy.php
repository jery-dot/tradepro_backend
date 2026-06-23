<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Apprenticeship;
use App\Models\ApprenticeshipApplication;
use App\Models\User;
use App\Models\Job;
use Illuminate\Http\Request;

class ApprenticeshipController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'opportunities');

        // Opportunities query
        $oppQuery = Apprenticeship::latest();
        if ($request->filled('search')) {
            $s = $request->search;
            $oppQuery->where(function ($q) use ($s) {
                $q->where('company_name','like',"%$s%")
                  ->orWhere('trade_type','like',"%$s%");
            });
        }
        if ($request->filled('trade') && $request->trade !== 'all') {
            $oppQuery->where('trade_type','like','%'.$request->trade.'%');
        }
        if ($request->filled('location') && $request->location !== 'all') {
            $oppQuery->where('location',$request->location);
        }

        // Applicants query
        $appQuery = ApprenticeshipApplication::with(['user','opportunity'])->latest();
        if ($request->filled('search') && $tab === 'applicants') {
            $s = $request->search;
            $appQuery->whereHas('user', fn($q) => $q->where('first_name','like',"%$s%")->orWhere('last_name','like',"%$s%"));
        }

        $opportunities     = $oppQuery->paginate(15)->withQueryString();
        $applicants        = $appQuery->paginate(15)->withQueryString();
        $opportunityCount  = Apprenticeship::count();
        $applicantCount    = ApprenticeshipApplication::count();
        $locations         = Apprenticeship::distinct()->pluck('location')->sort()->values();
        $userCount         = User::count();
        $jobCount          = Job::where('status','open')->count();
        $apprenticeCount   = Apprenticeship::where('status','open')->count();

        return view('admin.apprenticeships', compact(
            'opportunities','applicants','opportunityCount','applicantCount',
            'locations','userCount','jobCount','apprenticeCount'
        ));
    }

    public function show($id)
    {
        $opportunity = Apprenticeship::with(['applicants.user'])->findOrFail($id);
        $userCount   = User::count();
        $jobCount    = Job::where('status','open')->count();
        return view('admin.apprenticeships-show', compact('opportunity','userCount','jobCount'));
    }

    public function showApplicant($id)
    {
        $applicant = ApprenticeshipApplication::with(['user','opportunity'])->findOrFail($id);
        $userCount = User::count();
        $jobCount  = Job::where('status','open')->count();
        return view('admin.apprenticeship-applicant', compact('applicant','userCount','jobCount'));
    }

    public function destroy($id)
    {
        Apprenticeship::findOrFail($id)->delete();
        return redirect()->route('admin.apprenticeships')->with('status','Opportunity deleted.');
    }

    public function close($id)
    {
        Apprenticeship::findOrFail($id)->update(['status'=>'closed']);
        return back()->with('status','Opportunity closed.');
    }

    public function reopen($id)
    {
        Apprenticeship::findOrFail($id)->update(['status'=>'open']);
        return back()->with('status','Opportunity reopened.');
    }
}
