<x-app-layout :title="$title.' — Children\'s Classes'">
    <div class="mx-auto max-w-5xl space-y-6">
        <header>
            <p class="text-sm font-semibold text-muted">Panduan Pengguna</p>
            <h1 class="mt-1 font-display text-3xl font-bold text-navy">{{ $title }}</h1>
            <p class="mt-1 text-sm text-muted">{{ $description }}</p>
        </header>
        <div class="grid gap-4 md:grid-cols-2">
            @foreach($sections as $section)
                <section class="rounded-3xl border border-line bg-white p-5 shadow-sm sm:p-7">
                    <h2 class="font-display text-xl font-bold text-navy">{{ $section['title'] }}</h2>
                    <ol class="mt-3 list-inside list-decimal space-y-2 text-sm leading-relaxed text-muted">
                        @foreach($section['steps'] as $step)<li>{{ $step }}</li>@endforeach
                    </ol>
                </section>
            @endforeach
        </div>
    </div>
</x-app-layout>
