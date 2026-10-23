<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PublicActivityCard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class PublicActivityCardController extends Controller
{
    public function index(): View
    {
        $cards = PublicActivityCard::query()->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.activity-cards.index', compact('cards'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $imagePath = $this->storeImage($request);
        $data['image_path'] = $imagePath;
        unset($data['image']);
        $data['created_by'] = $request->user()->id;

        try {
            PublicActivityCard::create($data);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($imagePath);

            throw $exception;
        }

        return redirect()->route('admin.activity-cards')->with('success', 'Kartu kegiatan berhasil ditambahkan.');
    }

    public function update(Request $request, PublicActivityCard $card): RedirectResponse
    {
        $data = $this->validatedData($request, $card);
        $previousImage = $card->image_path;
        $imagePath = $this->storeImage($request);
        if ($imagePath !== null) {
            $data['image_path'] = $imagePath;
        }
        unset($data['image']);

        try {
            $card->update($data);
        } catch (Throwable $exception) {
            if ($imagePath !== null) {
                Storage::disk('public')->delete($imagePath);
            }

            throw $exception;
        }
        if ($imagePath !== null && $previousImage !== null && $previousImage !== $imagePath) {
            Storage::disk('public')->delete($previousImage);
        }

        return redirect()->route('admin.activity-cards')->with('success', 'Kartu kegiatan berhasil diperbarui.');
    }

    public function destroy(PublicActivityCard $card): RedirectResponse
    {
        $imagePath = $card->image_path;
        $card->delete();

        if ($imagePath !== null) {
            Storage::disk('public')->delete($imagePath);
        }

        return redirect()->route('admin.activity-cards')->with('success', 'Kartu kegiatan berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?PublicActivityCard $card = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'image' => [$card?->image_path === null ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:10000'],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    private function storeImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $path = $request->file('image')->store('activity-cards', 'public');

        if (! is_string($path) || ! Storage::disk('public')->exists($path)) {
            throw ValidationException::withMessages(['image' => 'Gambar gagal disimpan. Periksa penyimpanan lalu coba lagi.']);
        }

        return $path;
    }
}
