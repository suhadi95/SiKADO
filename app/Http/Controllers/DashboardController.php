<?php

namespace App\Http\Controllers;

use App\Enums\ActivityFileType;
use App\Enums\ActivityStatus;
use App\Models\Activity;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filters($request);

        $baseQuery = Activity::query()->with(['category', 'files']);

        $total = (clone $baseQuery)->count();
        $needsEvidence = (clone $baseQuery)->needsEvidence()->count();
        $complete = (clone $baseQuery)->get()->filter(fn (Activity $a) => $a->status->isComplete())->count();

        $byCategory = Category::query()
            ->ordered()
            ->withCount('activities')
            ->get();

        $activities = Activity::query()
            ->with(['category', 'files'])
            ->needsEvidence()
            ->filter($filters)
            ->sortedByDate($filters['sort'] ?? 'date_desc')
            ->paginate(10)
            ->withQueryString();

        return view('dashboard', [
            'summary' => [
                'total' => $total,
                'needs_evidence' => $needsEvidence,
                'complete' => $complete,
                'by_category' => $byCategory,
            ],
            'activities' => $activities,
            'categories' => Category::query()->ordered()->get(),
            'statuses' => ActivityStatus::cases(),
            'filters' => $filters,
            'fileTypes' => ActivityFileType::cases(),
        ]);
    }

    protected function filters(Request $request): array
    {
        $sort = $request->input('sort');

        return [
            'name' => $request->string('name')->trim()->toString() ?: null,
            'category_id' => $request->input('category_id') ?: null,
            'month' => $request->input('month') ?: null,
            'year' => $request->input('year') ?: null,
            'status' => $request->input('status') ?: null,
            'sort' => $sort === 'date_asc' ? 'date_asc' : 'date_desc',
        ];
    }
}
