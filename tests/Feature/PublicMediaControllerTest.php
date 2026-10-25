<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('serves public images from application storage without a storage symlink', function () {
    Storage::fake('public', ['url' => '/public-media']);
    $path = Storage::disk('public')->putFileAs(
        'class-photos',
        UploadedFile::fake()->image('class.png'),
        'class.png',
    );

    $response = $this->get(Storage::disk('public')->url($path));
    $response->assertOk()
        ->assertHeader('Content-Type', 'image/png')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Cache-Control', 'max-age=86400, public');
});

it('does not serve files outside public image folders or files that are not images', function () {
    Storage::fake('public', ['url' => '/public-media']);
    Storage::disk('public')->put('private/secret.png', 'private');
    Storage::disk('public')->put('avatars/script.php', '<?php echo "not an image";');

    $this->get('/public-media/private/secret.png')->assertNotFound();
    $this->get('/public-media/avatars/script.php')->assertNotFound();
    $this->get('/public-media/avatars/../secret.png')->assertNotFound();
});
