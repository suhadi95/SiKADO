<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Requests\UpdateGoogleDriveSettingsRequest;
use App\Models\Category;
use App\Services\GoogleDriveService;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(
        protected SettingService $settings,
        protected GoogleDriveService $drive
    ) {}

    public function index(): View
    {
        if ($this->settings->isConfigured() && ! $this->settings->isConnected()) {
            $this->drive->resetClient();
            $this->drive->testConnection();
        }

        return view('settings.index', [
            'drive' => $this->settings->getDriveSettings(),
            'categories' => Category::query()->ordered()->get(),
        ]);
    }

    public function updateGoogleDrive(UpdateGoogleDriveSettingsRequest $request): RedirectResponse
    {
        $this->settings->setRootFolderId($request->validated('root_folder_id'));

        $this->drive->resetClient();
        $this->settings->markConnection('belum_diuji', 'ID folder utama disimpan. Menguji koneksi...');

        $result = $this->drive->testConnection();

        if ($result['success']) {
            return back()->with('success', 'ID folder utama disimpan. '.$result['message']);
        }

        return back()->with('error', 'ID folder utama disimpan, tetapi uji koneksi gagal: '.$result['message']);
    }

    public function testGoogleDrive(): RedirectResponse
    {
        $this->drive->resetClient();
        $result = $this->drive->testConnection();

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    public function storeCategory(StoreCategoryRequest $request): RedirectResponse
    {
        $maxOrder = (int) Category::query()->max('sort_order');

        Category::query()->create([
            'name' => $request->validated('name'),
            'color' => $request->validated('color'),
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $maxOrder + 1,
        ]);

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function updateCategory(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update([
            'name' => $request->validated('name'),
            'color' => $request->validated('color'),
            'is_active' => $request->boolean('is_active', $category->is_active),
        ]);

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function toggleCategory(Category $category): RedirectResponse
    {
        $category->update([
            'is_active' => ! $category->is_active,
        ]);

        $status = $category->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Kategori berhasil {$status}.");
    }

    public function destroyCategory(Category $category): RedirectResponse
    {
        if ($category->activities()->exists()) {
            return back()->with(
                'error',
                'Kategori tidak dapat dihapus karena masih digunakan oleh kegiatan.'
            );
        }

        $category->delete();

        return back()->with('success', 'Kategori berhasil dihapus.');
    }
}
