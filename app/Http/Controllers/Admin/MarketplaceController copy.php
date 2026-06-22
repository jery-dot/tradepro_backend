<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\User;
use App\Models\Job;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    public function index(Request $request)
    {
        $query = Listing::with('seller')->latest();

        $category = $request->get('category','all');
        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }
        if ($request->filled('condition') && $request->condition !== 'all') {
            $query->where('condition', $request->condition);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('title','like',"%$s%")
                  ->orWhere('seller_name','like',"%$s%");
            });
        }

        $listings   = $query->paginate(15)->withQueryString();
        $totalCount = Listing::count();

        $categoryCounts = [
            'all'        => Listing::count(),
            'tools'      => Listing::where('category','tools')->count(),
            'equipment'  => Listing::where('category','equipment')->count(),
            'materials'  => Listing::where('category','materials')->count(),
            'companies'  => Listing::where('category','companies')->count(),
        ];

        $userCount = User::count();
        $jobCount  = Job::where('status','open')->count();

        return view('admin.marketplace', compact('listings','totalCount','categoryCounts','userCount','jobCount'));
    }

    public function show(Listing $listing)
    {
        $listing->load('seller');
        $userCount = User::count();
        $jobCount  = Job::where('status','open')->count();
        return view('admin.marketplace-show', compact('listing','userCount','jobCount'));
    }

    public function destroy(Listing $listing)
    {
        $listing->delete();
        return redirect()->route('admin.marketplace')->with('status','Listing removed.');
    }

    public function deactivate(Listing $listing)
    {
        $listing->update(['is_active'=>false]);
        return back()->with('status','Listing deactivated.');
    }

    public function activate(Listing $listing)
    {
        $listing->update(['is_active'=>true]);
        return back()->with('status','Listing activated.');
    }

    public function flag(Listing $listing)
    {
        $listing->update(['is_flagged'=>true]);
        return back()->with('status','Listing flagged for review.');
    }

    public function export()
    {
        $headers  = ['Content-Type'=>'text/csv','Content-Disposition'=>'attachment; filename=marketplace.csv'];
        $listings = Listing::all();
        $callback = function () use ($listings) {
            $f = fopen('php://output','w');
            fputcsv($f,['ID','Title','Category','Seller','Price','Condition','Location','Listed','Status']);
            foreach ($listings as $l) {
                fputcsv($f,[$l->id,$l->title,$l->category,$l->seller_name,'$'.number_format($l->price,2),$l->condition,$l->location,$l->created_at->format('d M Y'),$l->is_active?'Active':'Inactive']);
            }
            fclose($f);
        };
        return response()->stream($callback, 200, $headers);
    }
}
