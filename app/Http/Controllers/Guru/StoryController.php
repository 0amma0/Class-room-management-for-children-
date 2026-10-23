<?php

namespace App\Http\Controllers\Guru;

use App\Actions\Guru\GenerateStoryStoryboard;
use App\Exceptions\StoryIllustrationException;
use App\Http\Controllers\Controller;
use App\Models\Story;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class StoryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['story_id' => ['nullable', 'integer']]);
        $story = isset($filters['story_id'])
            ? $request->user()->stories()->findOrFail($filters['story_id'])
            : null;
        $stories = $request->user()->stories()->latest('updated_at')->paginate(10)->withQueryString();

        return view('guru.stories', [
            'story' => $story,
            'stories' => $stories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedStoryData($request);
        $story = $request->user()->stories()->create([
            'title' => $data['title'],
            'body' => $data['story_text'],
        ]);

        return redirect()->route('guru.stories', ['story_id' => $story->id])
            ->with('success', 'Cerita berhasil disimpan. Anda dapat membuat ilustrasi dari teks ini.');
    }

    public function update(Request $request, Story $story): RedirectResponse
    {
        $story = $request->user()->stories()->findOrFail($story->id);
        $data = $this->validatedStoryData($request);
        $storyTextChanged = $story->body !== $data['story_text'];
        $oldIllustrationPath = $storyTextChanged ? $story->illustration_path : null;
        $story->update([
            'title' => $data['title'],
            'body' => $data['story_text'],
            ...($storyTextChanged ? [
                'illustration_path' => null,
                'illustration_model' => null,
                'illustration_generated_at' => null,
            ] : []),
        ]);

        if ($oldIllustrationPath !== null) {
            Storage::disk('local')->delete($oldIllustrationPath);
        }

        return redirect()->route('guru.stories', ['story_id' => $story->id])
            ->with('success', $storyTextChanged ? 'Cerita diperbarui. Buat ulang ilustrasi agar sesuai dengan naskah terbaru.' : 'Cerita berhasil diperbarui.');
    }

    public function generate(Request $request, Story $story, GenerateStoryStoryboard $storyboard): RedirectResponse
    {
        $story = $request->user()->stories()->findOrFail($story->id);

        try {
            $path = $storyboard->generate($story->title, $story->body);
        } catch (StoryIllustrationException $exception) {
            throw ValidationException::withMessages(['story' => $exception->getMessage()]);
        }

        $oldIllustrationPath = $story->illustration_path;
        try {
            DB::transaction(function () use ($story, $path): void {
                $story->update([
                    'illustration_path' => $path,
                    'illustration_model' => 'Storyboard lokal',
                    'illustration_generated_at' => now(),
                ]);
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);

            throw $exception;
        }

        if ($oldIllustrationPath !== null) {
            Storage::disk('local')->delete($oldIllustrationPath);
        }

        return redirect()->route('guru.stories', ['story_id' => $story->id])
            ->with('success', 'Storyboard berhasil dibuat tanpa mengirim cerita ke layanan eksternal.');
    }

    public function image(Request $request, Story $story): StreamedResponse
    {
        $story = $request->user()->stories()->findOrFail($story->id);
        abort_unless($story->illustration_path !== null && Storage::disk('local')->exists($story->illustration_path), 404);

        return Storage::disk('local')->response($story->illustration_path, basename($story->illustration_path), [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function downloadImage(Request $request, Story $story): StreamedResponse
    {
        $story = $request->user()->stories()->findOrFail($story->id);
        abort_unless($story->illustration_path !== null && Storage::disk('local')->exists($story->illustration_path), 404);

        return Storage::disk('local')->download(
            $story->illustration_path,
            'ilustrasi-cerita-'.$story->id.'.'.pathinfo($story->illustration_path, PATHINFO_EXTENSION),
            ['X-Content-Type-Options' => 'nosniff'],
        );
    }

    /**
     * @return array{title: string, story_text: string}
     */
    private function validatedStoryData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'story_text' => ['required', 'string', 'max:20000'],
        ]);
    }
}
