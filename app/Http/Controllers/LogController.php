<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Inertia\Inertia;

class LogController extends Controller
{
    public function index(Request $request)
    {   
        $user = auth()->user();
        
        if ($user->role === User::ROLE_CHIEF) {

            return Inertia::render('Chief/Log', [
            ]);
        }

        if ($user->role === User::ROLE_OFFICER) {

            return Inertia::render('Officer/Log', [
            ]);
        }

        abort(403);
    }
}
