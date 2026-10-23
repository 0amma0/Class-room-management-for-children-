<x-app-layout title="Public Activity Cards — Children's Classes">
    <div class="mx-auto max-w-7xl space-y-6">
        <header><p class="text-sm font-semibold text-muted">Administration / Public content</p><h1 class="mt-1 font-display text-3xl font-bold text-navy">What we do cards</h1><p class="mt-1 text-sm text-muted">Manage the activity cards displayed on the public home page.</p></header>
        @if(session('success'))<x-ui-alert variant="ok">{{ session('success') }}</x-ui-alert>@endif
        @if($errors->any())<x-ui-alert variant="danger" title="Please check the card details"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></x-ui-alert>@endif
        <form method="POST" action="{{ route('admin.activity-cards.store') }}" enctype="multipart/form-data" class="grid gap-4 rounded-3xl border border-line bg-white p-6 shadow-sm sm:grid-cols-2">
            @csrf
            <div class="sm:col-span-2"><h2 class="font-display text-xl font-bold text-navy">Add card</h2></div>
            <label class="space-y-1 text-sm font-semibold"><span>Title</span><input name="title" value="{{ old('title') }}" required maxlength="255" class="w-full rounded-xl border border-line px-3 py-2"></label>
            <label class="space-y-1 text-sm font-semibold"><span>Sort order</span><input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" max="10000" required class="w-full rounded-xl border border-line px-3 py-2"></label>
            <label class="space-y-1 text-sm font-semibold sm:col-span-2"><span>Description</span><textarea name="description" rows="2" maxlength="5000" class="w-full rounded-xl border border-line px-3 py-2">{{ old('description') }}</textarea></label>
            <label class="space-y-1 text-sm font-semibold"><span>Image</span><input type="file" name="image" accept="image/jpeg,image/png,image/webp" required class="w-full rounded-xl border border-line px-3 py-2 font-normal"><span class="block text-xs font-normal text-muted">Choose a JPG, PNG, or WebP image up to 5 MB.</span></label>
            <label class="flex items-center gap-2 self-end text-sm font-semibold"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" checked class="size-4 rounded border-line text-orange">Display on public home</label>
            <div class="sm:col-span-2"><button class="rounded-full bg-orange px-6 py-3 text-sm font-bold text-white hover:bg-orange/90">Add card</button></div>
        </form>
        <section class="grid gap-5 lg:grid-cols-2">
            @forelse($cards as $card)
                <article class="space-y-4 rounded-3xl border border-line bg-white p-6 shadow-sm">
                    <div class="flex items-center gap-4">
                        @if($card->image_path)<img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($card->image_path) }}" alt="" class="size-20 rounded-2xl object-cover">@else<img src="{{ asset('images/Kartu Kegiatan.png') }}" alt="" class="size-20 rounded-2xl object-cover">@endif
                        <div><h2 class="font-display text-lg font-bold text-navy">{{ $card->title }}</h2><p class="text-sm text-muted">Order {{ $card->sort_order }} · {{ $card->is_active ? 'Visible' : 'Hidden' }}</p></div>
                    </div>
                    <form method="POST" action="{{ route('admin.activity-cards.update', $card) }}" enctype="multipart/form-data" class="grid gap-3 sm:grid-cols-2">
                        @csrf @method('PUT')
                        <label class="space-y-1 text-sm font-semibold"><span>Title</span><input name="title" value="{{ $card->title }}" required maxlength="255" class="w-full rounded-xl border border-line px-3 py-2"></label>
                        <label class="space-y-1 text-sm font-semibold"><span>Sort order</span><input type="number" name="sort_order" value="{{ $card->sort_order }}" min="0" max="10000" required class="w-full rounded-xl border border-line px-3 py-2"></label>
                        <label class="space-y-1 text-sm font-semibold sm:col-span-2"><span>Description</span><textarea name="description" rows="2" maxlength="5000" class="w-full rounded-xl border border-line px-3 py-2">{{ $card->description }}</textarea></label>
                        <label class="space-y-1 text-sm font-semibold"><span>Replace image</span><input type="file" name="image" accept="image/jpeg,image/png,image/webp" @required(!$card->image_path) class="w-full rounded-xl border border-line px-3 py-2 font-normal"><span class="block text-xs font-normal text-muted">Leave empty to keep the current image.</span></label>
                        <label class="flex items-center gap-2 self-end text-sm font-semibold"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked($card->is_active) class="size-4 rounded border-line text-orange">Visible on public home</label>
                        <div class="flex items-center gap-3 sm:col-span-2"><button class="rounded-full bg-orange px-5 py-2.5 text-sm font-bold text-white">Save changes</button></div>
                    </form>
                    <form method="POST" action="{{ route('admin.activity-cards.destroy', $card) }}" onsubmit="return confirm('Delete this card?')">@csrf @method('DELETE')<button class="rounded-full border border-danger/30 px-5 py-2.5 text-sm font-bold text-danger hover:bg-danger-soft">Delete card</button></form>
                </article>
            @empty
                <p class="rounded-2xl border border-line bg-white p-6 text-sm text-muted lg:col-span-2">No managed cards yet. The existing four public cards will remain visible until you add managed cards.</p>
            @endforelse
        </section>
    </div>
</x-app-layout>
