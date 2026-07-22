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
        $filters = [
            'name' => $request->string('name')->trim()->toString() ?: null,
            'category_id' => $request->input('category_id') ?: null,
            'month' => $request->input('month') ?: null,
            'year' => $request->input('year') ?: null,
            'status' => $request->input('status') ?: null,
        ];

        $activities = Activity::query()
            ->with(['category', 'files'])
            ->withCount('files')
            ->filter($filters)
            ->latest('activity_date')
            ->latest('id')
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
