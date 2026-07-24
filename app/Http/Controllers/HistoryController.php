<?php

namespace App\Http\Controllers;

use App\Enums\ActivityFileType;
use App\Enums\ActivityStatus;
use App\Models\Activity;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function index(Request $request): View
    {
        $sort = $request->input('sort');

        $filters = [
            'name' => $request->string('name')->trim()->toString() ?: null,
            'category_id' => $request->input('category_id') ?: null,
            'month' => $request->input('month') ?: null,
            'year' => $request->input('year') ?: null,
            'status' => $request->input('status') ?: null,
            'sort' => $sort === 'date_asc' ? 'date_asc' : 'date_desc',
        ];

        $activities = Activity::query()
            ->with(['category', 'files'])
            ->withCount('files')
            ->filter($filters)
            ->sortedByDate($filters['sort'])
            ->paginate(10)
            ->withQueryString();

        return view('history', [
            'activities' => $activities,
            'categories' => Category::query()->ordered()->get(),
            'statuses' => ActivityStatus::cases(),
            'filters' => $filters,
            'fileTypes' => ActivityFileType::cases(),
        ]);
    }
}
