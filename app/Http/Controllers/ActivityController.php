<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreActivityRequest;
use App\Http\Requests\UpdateActivityRequest;
use App\Models\Activity;
use App\Models\Category;
use App\Services\ActivityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class ActivityController extends Controller
{
    public function __construct(
        protected ActivityService $activities
    ) {}

    public function create(): View
    {
        return view('activities.create', [
            'categories' => Category::query()->active()->ordered()->get(),
        ]);
    }

    public function store(StoreActivityRequest $request): RedirectResponse
    {
        try {
            $activity = $this->activities->create(
                $request->validated(),
                $request->file('files', [])
            );

            $message = 'Kegiatan berhasil ditambahkan.';

            if ($activity->drive_sync_failed) {
                $message .= ' Namun folder Google Drive gagal dibuat. Periksa pengaturan Google Drive.';
            }

            return redirect()
                ->route('dashboard')
                ->with('success', $message);
        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan kegiatan: '.$e->getMessage());
        }
    }

    public function edit(Activity $activity): View
    {
        $activity->load(['category', 'files']);

        return view('activities.edit', [
            'activity' => $activity,
            'categories' => Category::query()->active()->ordered()->get(),
        ]);
    }

    public function update(UpdateActivityRequest $request, Activity $activity): RedirectResponse
    {
        try {
            $updated = $this->activities->update($activity, $request->validated());

            $message = 'Kegiatan berhasil diperbarui.';

            if ($updated->drive_sync_failed) {
                $message .= ' Namun sinkronisasi folder Google Drive bermasalah.';
            }

            return redirect()
                ->route('dashboard')
                ->with('success', $message);
        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui kegiatan: '.$e->getMessage());
        }
    }

    public function destroy(Activity $activity): RedirectResponse
    {
        try {
            $this->activities->delete($activity);

            return back()->with('success', 'Kegiatan berhasil dihapus.');
        } catch (Throwable $e) {
            return back()->with('error', 'Gagal menghapus kegiatan: '.$e->getMessage());
        }
    }
}
