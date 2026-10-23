<?php

use App\Models\PublicContact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('an administrator can update public contacts and visitors see the saved phone links', function () {
    $admin = User::factory()->admin()->create();
    $contact = PublicContact::query()->where('slug', 'sarir')->firstOrFail();

    $this->actingAs($admin)
        ->put(route('admin.contacts.update'), [
            'contacts' => [
                'sarir' => [
                    'name' => 'Sarir Baru',
                    'phone' => '+62 812-3456-7890',
                    'avatar_type' => 'afro',
                ],
                'najmi' => [
                    'name' => 'Najmi Rahmani',
                    'phone' => '+62 896-2365-1159',
                    'avatar_type' => 'bangs',
                ],
            ],
        ])
        ->assertRedirect(route('admin.contacts'));

    $this->assertDatabaseHas('public_contacts', [
        'id' => $contact->id,
        'name' => 'Sarir Baru',
        'phone' => '+62 812-3456-7890',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Sarir Baru')
        ->assertSee('href="tel:+6281234567890"', false);
});

test('a teacher cannot change public contact settings', function () {
    $guru = User::factory()->guru()->create();

    $this->actingAs($guru)
        ->put(route('admin.contacts.update'), [])
        ->assertForbidden();
});
