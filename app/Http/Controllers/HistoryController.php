<?php

namespace App\Http\Controllers;

use App\Http\Resources\HistoryResource;
use App\Models\History;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HistoryController extends Controller
{    public function index(Request $request)
    {   
        $query = History::search($request);
        $histories = HistoryResource::collection($query->with(['officer', 'task'])->latest()->paginate(10));

            return Inertia::render('Chief/History', [
                'histories' => $histories,
                'rangeFilter' => $request->filter ?? 'Today',
                'activityFilter' => $request->activity_filter ?? '',
                'search' => $request->search ?? '',
            ]);
    }
}
