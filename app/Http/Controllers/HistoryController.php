<?php

namespace App\Http\Controllers;

use App\Http\Resources\HistoryResource;
use App\Models\History;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HistoryController extends Controller
{    public function index(Request $request)
    {   
        $user = auth()->user();

        if ($user->role === User::ROLE_CHIEF) {
            $query = History::search($request);
            $histories = HistoryResource::collection($query->with(['officer', 'task'])->latest()->paginate(10));

            return Inertia::render('Chief/History', [
                'histories' => $histories,
                'rangeFilter' => $request->filter ?? 'today',
                'activityFilter' => $request->activity_filter ?? '',
                'search' => $request->search ?? '',
            ]); 
        }

        if ($user->role === User::ROLE_OFFICER) {
            $query = History::search($request);
            $histories = HistoryResource::collection($query->where('officer_id', auth()->id())->with(['officer', 'task'])->latest()->paginate(10));

            return Inertia::render('Officer/History', [
                'histories' => $histories,
                'rangeFilter' => $request->filter ?? 'today',
                'activityFilter' => $request->activity_filter ?? '',
                'search' => $request->search ?? '',
            ]); 
        }
    }
}
