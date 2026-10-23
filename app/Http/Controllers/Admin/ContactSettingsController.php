<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PublicContact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ContactSettingsController extends Controller
{
    private const AVATAR_TYPES = ['afro', 'bangs', 'beret', 'braids', 'bun', 'pigtails', 'short'];

    public function index(): View
    {
        $contacts = PublicContact::query()->orderBy('id')->get();

        return view('admin.contacts.index', compact('contacts'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'contacts' => ['required', 'array:sarir,najmi'],
            'contacts.sarir' => ['required', 'array:name,phone,avatar_type'],
            'contacts.sarir.name' => ['required', 'string', 'max:100'],
            'contacts.sarir.phone' => ['required', 'string', 'max:30', 'regex:/^\+?[0-9][0-9\s()-]{7,28}$/'],
            'contacts.sarir.avatar_type' => ['required', 'string', 'in:'.implode(',', self::AVATAR_TYPES)],
            'contacts.najmi' => ['required', 'array:name,phone,avatar_type'],
            'contacts.najmi.name' => ['required', 'string', 'max:100'],
            'contacts.najmi.phone' => ['required', 'string', 'max:30', 'regex:/^\+?[0-9][0-9\s()-]{7,28}$/'],
            'contacts.najmi.avatar_type' => ['required', 'string', 'in:'.implode(',', self::AVATAR_TYPES)],
        ]);

        DB::transaction(function () use ($data): void {
            foreach ($data['contacts'] as $slug => $attributes) {
                PublicContact::query()->where('slug', $slug)->firstOrFail()->update($attributes);
            }
        });

        return redirect()->route('admin.contacts')->with('success', 'Pilihan kontak publik berhasil diperbarui.');
    }
}
