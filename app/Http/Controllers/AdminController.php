<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function DashboardIndex() {
        return Inertia::render('Admin/Dashboard', [

        ]);
    }

    public function UserIndex() {
        $users = UserResource::collection(User::paginate(10));
        return Inertia::render('Admin/User', [
            'users' => $users
        ]);
    }
}
