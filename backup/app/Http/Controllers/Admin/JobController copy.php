<?php
/* ============================================================
   JobController.php
   ============================================================ */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\User;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function index(Request $request)
    {
        $query = Job::with('poster')->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('title',        'like', "%$s%")
                  ->orWhere('company_name','like', "%$s%")
                  ->orWhere('location',    'like', "%$s%");
            });
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }
        if ($request->filled('available') && $request->available === '1') {
            $query->where('is_available', true);
        }

        $jobs       = $query->paginate(15)->withQueryString();
        $totalCount = Job::count();
        $userCount  = User::count();
        $jobCount   = Job::where('status','open')->count();

        return view('admin.jobs', compact('jobs','totalCount','userCount','jobCount'));
    }

    public function show(Job $job)
    {
        $job->load(['poster','applicants.user']);
        $userCount = User::count();
        $jobCount  = Job::where('status','open')->count();
        return view('admin.jobs-show', compact('job','userCount','jobCount'));
    }

    public function destroy(Job $job)
    {
        $job->delete();
        return redirect()->route('admin.jobs')->with('status','Job deleted.');
    }

    public function close(Job $job)
    {
        $job->update(['status'=>'closed']);
        return back()->with('status','Job closed.');
    }

    public function reopen(Job $job)
    {
        $job->update(['status'=>'open']);
        return back()->with('status','Job reopened.');
    }

    public function export(Request $request)
    {
        $headers  = ['Content-Type'=>'text/csv','Content-Disposition'=>'attachment; filename=jobs.csv'];
        $jobs     = Job::all();
        $callback = function () use ($jobs) {
            $f = fopen('php://output','w');
            fputcsv($f,['ID','Title','Company','Type','Pay','Location','Duration','Start','Status','Featured']);
            foreach ($jobs as $j) {
                fputcsv($f,[$j->id,$j->title,$j->company_name,$j->type,'$'.$j->pay_rate,$j->location,$j->duration.' '.$j->duration_unit,$j->start_date,$j->status,$j->is_featured?'Yes':'No']);
            }
            fclose($f);
        };
        return response()->stream($callback, 200, $headers);
    }
}
