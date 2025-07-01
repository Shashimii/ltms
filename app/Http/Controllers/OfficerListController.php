<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OfficerListController extends Controller
{    
    public function index(Request $request) {
        $officersQuery = User::search($request);
        $officers = UserResource::collection($officersQuery
        ->where('role', 0)->orderBy('created_at', 'desc')->paginate(10));

        return Inertia::render('Chief/OfficerList/Index', [
            'search' => $request->search ?? '',
            'officers' => $officers,
        ]);
        
    }
}
