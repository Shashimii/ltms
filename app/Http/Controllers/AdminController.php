<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function DashboardIndex() {
        return Inertia::render('Admin/Dashboard', [

        ]);
    }

    public function UserIndex() {
        return Inertia::render('Admin/User', [
            
        ]);
    }
}
