<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->latest();

        // Type tab filter
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // Search
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('first_name', 'like', "%$s%")
                  ->orWhere('last_name',  'like', "%$s%")
                  ->orWhere('email',      'like', "%$s%")
                  ->orWhere('city',       'like', "%$s%");
            });
        }

        // Status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Insured filter
        if ($request->filled('insured') && $request->insured !== 'all') {
            $query->where('is_insured', $request->insured === 'yes');
        }

        // Per-type counts for tabs
        $counts = [
            'all'           => User::count(),
            'contractor'    => User::where('user_type','contractor')->count(),
            'subcontractor' => User::where('user_type','subcontractor')->count(),
            'labour'        => User::where('user_type','labour')->count(),
            'apprentice'    => User::where('user_type','apprentice')->count(),
        ];

        $users      = $query->paginate(15)->withQueryString();
        $totalCount = $counts['all'];

        // Sidebar counts
        $userCount = $totalCount;
        $jobCount  = \App\Models\Job::where('status','open')->count();

        return view('admin.users', compact('users','counts','totalCount','userCount','jobCount'));
    }

    public function show(User $user)
    {
        $user->load(['jobs' => fn($q) => $q->latest()->take(5)]);
        $userCount = User::count();
        $jobCount  = \App\Models\Job::where('status','open')->count();
        return view('admin.user-show', compact('user','userCount','jobCount'));
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users')
            ->with('status', $user->first_name . ' ' . $user->last_name . ' has been deleted.');
    }

    public function suspend(User $user)
    {
        $user->update(['status' => 'suspended']);
        return redirect()->back()
            ->with('status', $user->first_name . ' has been suspended.');
    }

    public function deactivate(User $user)
    {
        $user->update(['status' => 'inactive']);
        return redirect()->back()
            ->with('status', $user->first_name . ' has been set to inactive.');
    }

    public function reactivate(User $user)
    {
        $user->update(['status' => 'active']);
        return redirect()->back()
            ->with('status', $user->first_name . ' has been reactivated.');
    }

    public function export(Request $request)
    {
        // Use Laravel Excel or a CSV stream here
        // Example CSV response:
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename=users.csv'];
        $users   = User::all();
        $callback = function () use ($users) {
            $f = fopen('php://output', 'w');
            fputcsv($f, ['ID','Name','Email','Type','City','State','Trade','Experience','Insured','Background','Status','Joined']);
            foreach ($users as $u) {
                fputcsv($f, [
                    $u->id, $u->first_name.' '.$u->last_name, $u->email,
                    $u->type, $u->city, $u->state, $u->trade,
                    $u->years_experience, $u->is_insured ? 'Yes':'No',
                    $u->background_check, $u->status,
                    $u->created_at->format('d M Y'),
                ]);
            }
            fclose($f);
        };
        return response()->stream($callback, 200, $headers);
    }
}
