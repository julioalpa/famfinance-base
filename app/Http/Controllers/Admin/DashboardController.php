<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FamilyGroup;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'users'         => User::count(),
            'admins'        => User::where('is_admin', true)->count(),
            'family_groups' => FamilyGroup::count(),
            'users_today'   => User::whereDate('created_at', today())->count(),
        ];

        $latestUsers  = User::latest()->limit(5)->get();
        $latestGroups = FamilyGroup::with('owner')->latest()->limit(5)->get();

        return view('admin.dashboard', compact('stats', 'latestUsers', 'latestGroups'));
    }
}
