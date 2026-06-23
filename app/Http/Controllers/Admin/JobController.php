<?php
/* ============================================================
   JobController.php
   ============================================================ */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobPost; // 🔥 Corrigé (Évite le conflit avec les tables systèmes)
use App\Models\User;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function index(Request $request)
    {
        // Chargement immédiat de la relation 'owner' définie dans JobPost
        $query = JobPost::with(['owner', 'specialization'])->latest();

        // Recherche (Adaptée à vos colonnes de localisation fines)
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%$s%")
                  ->orWhere('company_name', 'like', "%$s%")
                  ->orWhere('city', 'like', "%$s%")
                  ->orWhere('state', 'like', "%$s%")
                  ->orWhere('country', 'like', "%$s%");
            });
        }
        
        // Filtre de statut (open, closed, etc.)
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        // Filtre de type de paiement (ex: hourly, daily, fix_price)
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('pay_rate_type', $request->type);
        }
        
        // Filtre de mise en avant (is_featured)
        if ($request->filled('featured') && $request->featured === '1') {
            $query->where('is_featured', true);
        }

        $jobs       = $query->paginate(15)->withQueryString();
        $totalCount = JobPost::count();
        $userCount  = User::count();
        $jobCount   = JobPost::where('status', 'pending')->count();
        $apprenticeCount = \App\Models\Opportunity::count();

        return view('admin.jobs', compact('jobs', 'totalCount', 'userCount', 'jobCount', 'apprenticeCount'));
    }

    public function show(JobPost $job)
    {
        // Chargement des relations explicites définies dans votre modèle JobPost
        $job->load(['owner', 'specialization', 'skills']);
        
        $userCount = User::count();
        $jobCount  = JobPost::where('status', 'pending')->count();
        $apprenticeCount = \App\Models\Opportunity::count();
        
        return view('admin.jobs-show', compact('job', 'userCount', 'jobCount', 'apprenticeCount'));
    }

    public function destroy(JobPost $job)
    {
        $job->delete();
        return redirect()->route('admin.jobs')->with('status', 'Job post deleted.');
    }

    public function close(JobPost $job)
    {
        $job->update(['status' => 'closed']);
        return back()->with('status', 'Job post closed.');
    }

    public function reopen(JobPost $job)
    {
        $job->update(['status' => 'open']);
        return back()->with('status', 'Job post reopened.');
    }

    public function export(Request $request)
    {
        $headers  = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=job_posts_' . now()->format('Y-m-d') . '.csv'
        ];
        
        $jobs = JobPost::with(['owner', 'specialization'])->get();
        
        $callback = function () use ($jobs) {
            $f = fopen('php://output', 'w');
            
            // En-tête structuré selon les colonnes de votre table 'job_posts'
            fputcsv($f, ['ID', 'Job Code', 'Title', 'Company', 'Poster (Owner)', 'Specialization', 'Pay Amount', 'Currency', 'Pay Type', 'Location (City)', 'Duration', 'Start Date', 'Status', 'Featured']);
            
            foreach ($jobs as $j) {
                $ownerName = $j->owner ? $j->owner->name : 'N/A';
                $specializationName = $j->specialization ? $j->specialization->name : 'General';
                $durationString = $j->duration_value ? ($j->duration_value . ' ' . ($j->duration_unit ?? 'days')) : 'N/A';
                
                fputcsv($f, [
                    $j->id,
                    $j->job_code ?? 'N/A',
                    $j->title,
                    $j->company_name ?? 'N/A',
                    $ownerName,
                    $specializationName,
                    $j->pay_rate_amount ? number_format($j->pay_rate_amount, 2) : '0.00',
                    $j->pay_rate_currency ?? 'USD',
                    $j->pay_rate_type ?? 'Hourly',
                    $j->city ?? 'N/A',
                    $durationString,
                    $j->start_date ? $j->start_date->format('Y-m-d') : 'N/A',
                    $j->status ?? 'open',
                    $j->is_featured ? 'Yes' : 'No'
                ]);
            }
            fclose($f);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}