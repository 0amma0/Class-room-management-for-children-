<?php

use App\Models\Story;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('a teacher can save and edit a story without exposing it to another teacher', function () {
    $guru = User::factory()->guru()->create();
    $otherGuru = User::factory()->guru()->create();

    $response = $this->actingAs($guru)->post(route('guru.stories.store'), [
        'title' => 'Cerita gelas air',
        'story_text' => 'Teks cerita awal.',
    ]);

    $story = Story::query()->firstOrFail();
    $response->assertRedirect(route('guru.stories', ['story_id' => $story->id]));
    $this->actingAs($guru)->put(route('guru.stories.update', $story), [
        'title' => 'Cerita gelas penuh',
        'story_text' => 'Teks cerita yang disunting.',
    ])->assertRedirect(route('guru.stories', ['story_id' => $story->id]));

    $this->assertDatabaseHas('stories', [
        'id' => $story->id,
        'user_id' => $guru->id,
        'body' => 'Teks cerita yang disunting.',
    ]);

    $this->actingAs($otherGuru)
        ->put(route('guru.stories.update', $story), [
            'title' => 'Akses terlarang',
            'story_text' => 'Cerita guru lain.',
        ])
        ->assertNotFound();
});

test('a teacher can generate a private local storyboard without an external provider', function () {
    Storage::fake('local');
    $guru = User::factory()->guru()->create();
    $otherGuru = User::factory()->guru()->create();
    $story = Story::query()->create([
        'user_id' => $guru->id,
        'title' => 'Cerita <script>teman</script>',
        'body' => 'Anak-anak pergi ke sungai. Mereka melihat pohon besar & indah. Mereka saling menolong.',
    ]);

    $this->actingAs($guru)
        ->post(route('guru.stories.generate', $story))
        ->assertRedirect(route('guru.stories', ['story_id' => $story->id]));

    $story->refresh();
    expect($story->illustration_path)->not->toBeNull();
    expect($story->illustration_model)->toBe('Storyboard lokal');
    Storage::disk('local')->assertExists($story->illustration_path);
    $svg = Storage::disk('local')->get($story->illustration_path);
    expect($svg)
        ->toContain('&lt;script&gt;teman&lt;/script&gt;')
        ->toContain('Anak-anak pergi ke sungai.')
        ->toContain('Mereka melihat pohon besar &amp; indah.')
        ->toContain('Mereka saling menolong.')
        ->toContain('data-setting="water" data-action="walking"')
        ->toContain('data-setting="garden" data-action="talking" data-props="tree"')
        ->toContain('data-action="helping"')
        ->toContain('class="storyboard-panel"');
    expect(substr_count($svg, 'class="storyboard-panel"'))->toBe(3);

    $this->get(route('guru.stories.image', $story))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/svg+xml')
        ->assertHeader('X-Content-Type-Options', 'nosniff');
    $this->get(route('guru.stories', ['story_id' => $story->id]))
        ->assertOk()
        ->assertSee('Buat storyboard');
    $this->actingAs($otherGuru)
        ->get(route('guru.stories.image', $story))
        ->assertNotFound();
});

test('a local storyboard combines excess story sentences into at most six panels', function () {
    Storage::fake('local');
    $guru = User::factory()->guru()->create();
    $story = Story::query()->create([
        'user_id' => $guru->id,
        'title' => 'Cerita panjang',
        'body' => 'Adegan pertama. Adegan kedua. Adegan ketiga. Adegan keempat. Adegan kelima. Adegan keenam. Adegan ketujuh.',
    ]);

    $this->actingAs($guru)
        ->post(route('guru.stories.generate', $story))
        ->assertRedirect(route('guru.stories', ['story_id' => $story->id]));

    $story->refresh();
    expect($story->body)->toBe('Adegan pertama. Adegan kedua. Adegan ketiga. Adegan keempat. Adegan kelima. Adegan keenam. Adegan ketujuh.');
    expect(substr_count(Storage::disk('local')->get($story->illustration_path), 'class="storyboard-panel"'))->toBe(6);
});

test('a scene illustration reflects its activity and avoids matching keywords inside other words', function () {
    Storage::fake('local');
    $guru = User::factory()->guru()->create();
    $story = Story::query()->create([
        'user_id' => $guru->id,
        'title' => 'Bermain di halaman',
        'body' => 'Anak-anak bermain bola. Mereka pergi pada akhir cerita.',
    ]);

    $this->actingAs($guru)
        ->post(route('guru.stories.generate', $story))
        ->assertRedirect(route('guru.stories', ['story_id' => $story->id]));

    $svg = Storage::disk('local')->get($story->refresh()->illustration_path);
    expect($svg)
        ->toContain('data-setting="outdoor" data-action="playing" data-props="ball"')
        ->toContain('data-setting="outdoor" data-action="walking" data-props=""')
        ->toContain('fill="#f17b58"');
});
