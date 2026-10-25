<x-guest-layout title="Classes — Children's Classes">
    <header class="sticky top-0 z-50 border-b border-[#EFE4CC]/80 bg-white/95 backdrop-blur-md">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-3 px-4 sm:h-20 sm:px-6">
            <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3">
                <span
                    class="flex size-10 shrink-0 items-center justify-center rounded-full bg-[#2E4BC0] font-display text-xl font-bold text-white">K</span>
                <span class="truncate font-display text-base font-bold text-[#1D2B33] sm:text-xl">Children's
                    Classes</span>
            </a>
            <nav class="hidden items-center gap-7 text-sm font-semibold text-[#65737D] md:flex"
                aria-label="Main navigation">
                <a href="{{ route('home') }}" class="hover:text-[#2E4BC0]">Home</a>
                <a href="{{ route('regions') }}" class="hover:text-[#2E4BC0]">Regions</a>
                <a href="{{ route('classes') }}" aria-current="page" class="text-[#2E4BC0]">Classes</a>
                <a href="{{ route('home') }}#activities" class="hover:text-[#2E4BC0]">Activities</a>
                <a href="{{ route('home') }}#about" class="hover:text-[#2E4BC0]">About</a>
            </nav>
            <a href="{{ route('login') }}"
                class="shrink-0 rounded-full bg-[#FBA93A] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#e89726]">Sign
                In</a>
        </div>
    </header>

    @php
        $activities = [
            [
                'key' => 'prayers',
                'name' => 'Prayers',
                'title' => 'Reciting and memorizing prayers',
                'subtitle' => 'Activity a · Words to speak with God',
                'icon' => 'prayers',
                'children' => 'Learn a short prayer by heart, a little more each class.',
                'teacher' => 'Says it slowly, explains hard words, then says it together.',
                'everyLesson' => 'One prayer carries across several lessons.',
            ],
            [
                'key' => 'songs',
                'name' => 'Songs',
                'title' => 'Singing and learning together',
                'subtitle' => 'Activity b · Music, words, and movement',
                'icon' => 'songs',
                'children' => 'Sing a song together and learn its words through repetition and movement.',
                'teacher' => 'Introduces the melody and words, then invites children to sing along.',
                'everyLesson' => 'Songs bring energy and a shared rhythm to the class.',
            ],
            [
                'key' => 'quotations',
                'name' => 'Quotations',
                'title' => 'Discovering meaningful quotations',
                'subtitle' => 'Activity c · Words to remember',
                'icon' => 'quotations',
                'children' => 'Hear a short quotation, talk about its meaning, and practice remembering it.',
                'teacher' => 'Reads the quotation clearly and helps explain unfamiliar words.',
                'everyLesson' => 'A quotation connects the lesson theme with everyday choices.',
            ],
            [
                'key' => 'stories',
                'name' => 'Stories',
                'title' => 'Listening to stories and reflecting',
                'subtitle' => 'Activity d · Learning through stories',
                'icon' => 'stories',
                'children' => 'Listen to a story and share what they notice about its characters and choices.',
                'teacher' => 'Tells the story and asks gentle questions that connect it to the theme.',
                'everyLesson' => 'Stories make the lesson theme easier to imagine and discuss.',
            ],
            [
                'key' => 'games',
                'name' => 'Games',
                'title' => 'Playing games as a team',
                'subtitle' => 'Activity e · Play, movement, and cooperation',
                'icon' => 'games',
                'children' => 'Join a playful group activity that practices the lesson in an active way.',
                'teacher' => 'Explains the rules, keeps play welcoming, and helps everyone participate.',
                'everyLesson' => 'Games create a chance to practice cooperation and kindness.',
            ],
            [
                'key' => 'coloring',
                'name' => 'Coloring',
                'title' => 'Creating and coloring together',
                'subtitle' => 'Activity f · Art and expression',
                'icon' => 'coloring',
                'children' => 'Color or create an activity page connected with the lesson theme.',
                'teacher' => 'Provides the activity, encourages creative choices, and helps as needed.',
                'everyLesson' => 'Creative work gives children another way to engage with the theme.',
            ],
            [
                'key' => 'closing-prayers',
                'name' => 'Closing prayers',
                'title' => 'Ending the class with gratitude',
                'subtitle' => 'Activity g · A thoughtful goodbye',
                'icon' => 'closing-prayers',
                'children' => 'Join a short closing prayer and say goodbye to the group.',
                'teacher' => 'Leads the closing prayer and thanks everyone for taking part.',
                'everyLesson' => 'A calm ending helps the class finish together.',
            ],
        ];
    @endphp

    <main>
        @if($errors->any())
            <div class="mx-auto max-w-7xl px-4 pt-4 sm:px-6">
                <x-ui-alert variant="danger" title="Permintaan belum dapat dikirim">
                    <ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </x-ui-alert>
            </div>
        @endif
        <section class="bg-[#FFF8EA] px-4 pb-8 pt-6 sm:px-6 sm:pb-10 sm:pt-8">
            <div class="mx-auto max-w-6xl">
                <div class="text-center">
                    <h1 class="font-display text-4xl font-bold text-[#1F2F8A] sm:text-5xl">Classes</h1>
                    <p class="mx-auto mt-2 max-w-2xl text-sm leading-relaxed text-[#1D2B33]">These activities naturally
                        tend to differ in the level of energy and degree of movement
                        they require; some will be very active and others more tranquil. </p>
                </div>

                <div class="mt-7 grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-7" role="group"
                    aria-label="Seven class activities">
                    @foreach ($activities as $activity)
                        <button type="button"
                            class="activity-tab flex min-h-28 flex-col items-center justify-center gap-2 rounded-2xl border-2 border-[#EFE4CC] bg-white px-2 py-3 text-center font-display text-xs font-bold text-[#1F2F8A] transition hover:border-[#FBA93A] focus-visible:outline-[#FBA93A] {{ $loop->first ? 'is-selected border-[#FBA93A] bg-[#8ED6EC]' : '' }}"
                            data-activity="{{ $activity['key'] }}" aria-controls="activity-details"
                            aria-pressed="{{ $loop->first ? 'true' : 'false' }}">
                            <span class="flex size-11 items-center justify-center" aria-hidden="true">
                                @switch($activity['icon'])
                                    @case('prayers')
                                        <svg viewBox="0 0 40 40" class="size-10">
                                            <rect x="7" y="4" width="25" height="32" rx="4" fill="#2E4BC0" />
                                            <path d="M12 4v32" stroke="#1F2F8A" stroke-width="4" />
                                            <path d="m21 12 2 5 5 .3-4 3.2 1.4 5-4.4-2.8-4.2 2.8 1.3-5-4-3.2 5-.3z"
                                                fill="#FFD86B" />
                                        </svg>
                                    @break

                                    @case('songs')
                                        <svg viewBox="0 0 40 40" class="size-10" fill="none" stroke="#2E4BC0"
                                            stroke-width="3">
                                            <path d="M23 7v22M23 10l12-3v18" />
                                            <circle cx="17" cy="30" r="5" fill="#FBA93A" stroke="none" />
                                            <circle cx="29" cy="27" r="5" fill="#FBA93A" stroke="none" />
                                        </svg>
                                    @break

                                    @case('quotations')
                                        <svg viewBox="0 0 40 40" class="size-10">
                                            <rect x="3" y="6" width="34" height="25" rx="7" fill="#FBA93A" />
                                            <path d="m12 30-4 6 11-6" fill="#FBA93A" />
                                            <path
                                                d="M13 14c-3 0-5 2-5 5s2 5 5 5 4-2 4-5c0-2-1-4-4-5m14 0c-3 0-5 2-5 5s2 5 5 5 4-2 4-5c0-2-1-4-4-5"
                                                fill="white" />
                                        </svg>
                                    @break

                                    @case('stories')
                                        <svg viewBox="0 0 40 40" class="size-10" fill="none" stroke="#1F2F8A"
                                            stroke-width="2.5">
                                            <path d="M4 9c7-3 12-1 16 2v24c-4-3-9-5-16-2zM36 9c-7-3-12-1-16 2v24c4-3 9-5 16-2z"
                                                fill="#E3EAFB" />
                                            <path d="M8 15h8m-8 5h8m8-5h8m-8 5h8" stroke="#8ED6EC" />
                                        </svg>
                                    @break

                                    @case('games')
                                        <svg viewBox="0 0 40 40" class="size-10">
                                            <rect x="4" y="4" width="32" height="32" rx="8" fill="#2E9E5B" />
                                            <g fill="white">
                                                <circle cx="13" cy="13" r="2.4" />
                                                <circle cx="27" cy="13" r="2.4" />
                                                <circle cx="20" cy="20" r="2.4" />
                                                <circle cx="13" cy="27" r="2.4" />
                                                <circle cx="27" cy="27" r="2.4" />
                                            </g>
                                        </svg>
                                    @break

                                    @case('coloring')
                                        <svg viewBox="0 0 40 40" class="size-10">
                                            <path d="m8 28 18-22 8 6-18 22-10 3z" fill="#F4788C" />
                                            <path d="m7 29 8 6-9 2z" fill="#FFD86B" />
                                            <path d="m24 9 8 6" stroke="white" stroke-width="2" />
                                        </svg>
                                    @break

                                    @default
                                        <svg viewBox="0 0 40 40" class="size-10" fill="none" stroke="#1F2F8A"
                                            stroke-width="2.5">
                                            <path d="M7 33h26M12 30h16v3H12z" fill="#8ED6EC" />
                                            <rect x="16" y="13" width="8" height="17" rx="2"
                                                fill="#FFF8EA" />
                                            <path d="M20 4c-5 5-4 8 0 9 4-1 5-4 0-9z" fill="#FBA93A" />
                                        </svg>
                                @endswitch
                            </span>
                            <span>{{ $activity['name'] }}</span>
                        </button>
                    @endforeach
                </div>

                <section id="activity-details"
                    class="mx-auto mt-6 grid max-w-3xl gap-4 rounded-3xl border-2 border-[#EFE4CC] bg-white p-5 sm:grid-cols-[minmax(150px,0.8fr)_2fr] sm:p-6"
                    aria-live="polite">
                    <div class="flex flex-col items-start">
                        <span id="activity-detail-icon"
                            class="flex size-16 items-center justify-center rounded-2xl bg-[#8ED6EC]"
                            aria-hidden="true"></span>
                        <h2 id="activity-detail-title"
                            class="mt-2 font-display text-lg font-bold leading-tight text-[#1F2F8A]">Reciting and
                            memorizing prayers</h2>
                        <p id="activity-detail-subtitle" class="mt-1 text-xs text-[#65737D]">Activity a · Words to
                            speak with God</p>
                    </div>
                    <div class="space-y-3 text-sm text-[#1D2B33]">
                        <div>
                            <p class="text-[10px] font-bold uppercase text-[#FBA93A]">What the children do</p>
                            <p id="activity-children">Learn a short prayer by heart, a little more each class.</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase text-[#FBA93A]">What the teacher does</p>
                            <p id="activity-teacher">Says it slowly, explains hard words, then says it together.</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase text-[#FBA93A]">In every lesson</p>
                            <p id="activity-every-lesson">One prayer carries across several lessons.</p>
                        </div>
                    </div>
                </section>
            </div>
        </section>

        <section
            class="relative min-h-[420px] overflow-hidden bg-[#BFE9ED] px-4 pb-8 pt-12 sm:min-h-[460px] sm:px-6 sm:pt-16">
            <div aria-hidden="true"
                class="absolute inset-x-0 bottom-0 h-[45%] bg-[#71D27B] [clip-path:polygon(0_36%,20%_10%,40%_38%,63%_22%,82%_40%,100%_14%,100%_100%,0_100%)]">
            </div>
            <div aria-hidden="true" class="absolute inset-x-0 bottom-0 h-[24%] bg-[#18B765]"></div>
            <div
                class="relative mx-auto flex min-h-[340px] max-w-6xl items-end justify-between gap-5 sm:min-h-[360px]">
                <img src="{{ asset('images/imganak.png') }}" alt="Children learning and playing together"
                    class="w-[min(58%,620px)] self-end object-contain object-bottom" loading="lazy">
                <article
                    class="mb-4 w-[min(48%,400px)] rounded-2xl bg-[#1F2F8A] p-5 text-white shadow-[0_10px_0_#174379] sm:mb-8 sm:p-7">
                    <h2 class="font-display text-xl font-bold text-[#FFD86B] sm:text-2xl">Once upon a Saturday
                        morning...</h2>
                    <p class="mt-3 text-xs leading-relaxed sm:text-sm">A circle of children sat down on a soft mat.
                        They whispered a prayer, sang a song about sharing, and tucked a new quotation into their
                        pockets. Then came a game that made everyone laugh, and a drawing waiting to be colored.</p>
                    <p class="mt-3 text-xs leading-relaxed sm:text-sm">When the last prayer was said, nobody wanted to
                        leave — so they promised to come back next week.</p>
                    <a href="#activities"
                        class="mt-4 inline-flex rounded-full bg-[#FBA93A] px-4 py-2 text-xs font-bold text-white hover:bg-[#e89726]">Meet
                        the seven activities</a>
                </article>
            </div>
        </section>

        <section class="bg-[#FBA93A] px-4 py-6 text-center text-white sm:px-6">
            <div class="mx-auto grid max-w-6xl grid-cols-3 gap-3">
                <div><strong class="block font-display text-3xl font-bold sm:text-4xl">24</strong><span
                        class="text-xs sm:text-sm">lessons in Grade 1</span></div>
                <div><strong class="block font-display text-3xl font-bold sm:text-4xl">7</strong><span
                        class="text-xs sm:text-sm">activities in every class</span></div>
                <div><strong class="block font-display text-3xl font-bold sm:text-4xl">1</strong><span
                        class="text-xs sm:text-sm">theme for each lesson</span></div>
            </div>
        </section>

        <section id="activities" class="bg-[#FFF8EA] px-4 py-12 text-center sm:px-6 sm:py-16">
            <h2 class="font-display text-2xl font-bold text-[#1F2F8A] sm:text-3xl">Ready to join a class?</h2>
            <p class="mt-2 text-sm text-[#65737D]">Find a community near you and see which classes are open.</p>
            <a href="{{ route('regions') }}"
                class="mt-5 inline-flex rounded-full bg-[#FBA93A] px-6 py-3 text-sm font-bold text-white hover:bg-[#e89726]">Find
                a class</a>
            @if($bookModule)
                <details class="mx-auto mt-6 max-w-2xl rounded-2xl border border-[#EFE4CC] bg-white p-5 text-left">
                    <summary class="cursor-pointer text-center font-display font-bold text-[#2E4BC0]">Ajukan izin membaca atau mengunduh Modul Buku 3</summary>
                    <p class="mt-3 text-sm text-[#65737D]">Admin harus menyetujui permintaan sebelum file dapat dibuka. Simpan tautan status setelah mengirim permintaan.</p>
                    <form method="POST" action="{{ route('learning-materials.requests.store', $bookModule) }}" class="mt-4 grid gap-3 sm:grid-cols-2">
                        @csrf
                        <label class="space-y-1 text-xs font-semibold text-[#1F2F8A]"><span>Nama</span><input name="requester_name" value="{{ old('requester_name') }}" required maxlength="255" autocomplete="name" class="w-full rounded-xl border border-[#EFE4CC] px-3 py-2 text-sm font-normal"></label>
                        <label class="space-y-1 text-xs font-semibold text-[#1F2F8A]"><span>Email</span><input type="email" name="requester_email" value="{{ old('requester_email') }}" required maxlength="255" autocomplete="email" class="w-full rounded-xl border border-[#EFE4CC] px-3 py-2 text-sm font-normal"></label>
                        <div class="flex flex-wrap gap-3 sm:col-span-2">
                            <button name="requested_action" value="read" class="rounded-full bg-[#2E4BC0] px-5 py-2.5 text-sm font-bold text-white">Minta izin membaca</button>
                            <button name="requested_action" value="download" class="rounded-full bg-[#FBA93A] px-5 py-2.5 text-sm font-bold text-white">Minta izin mengunduh</button>
                        </div>
                    </form>
                </details>
            @else
                <p class="mx-auto mt-5 max-w-xl rounded-xl bg-white p-4 text-sm text-[#65737D]">Modul sedang tidak tersedia. Silakan hubungi Admin.</p>
            @endif
            <details class="mx-auto mt-6 max-w-3xl rounded-2xl border border-[#EFE4CC] bg-white px-5 py-4 text-left">
                <summary class="cursor-pointer font-display text-sm font-bold text-[#1F2F8A]">Lihat daftar kelas aktif ({{ $classes->count() }})</summary>
                <ul class="mt-3 grid gap-2 text-sm text-[#1D2B33] sm:grid-cols-2">
                    @forelse($classes as $class)
                        <li class="rounded-xl bg-[#FFF8EA] px-3 py-2">
                            <span class="block font-bold">{{ $class->name }}</span>
                            <span class="text-xs text-[#65737D]">{{ $class->environment?->name ?? 'Community' }} · {{ $class->students_count }} participants</span>
                        </li>
                    @empty
                        <li class="text-xs text-[#65737D]">Belum ada kelas aktif yang terdaftar.</li>
                    @endforelse
                </ul>
            </details>
        </section>
    </main>

    <footer class="bg-[#8ED6EC] px-4 py-6 sm:px-6">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 text-sm text-[#1F2F8A]">
            <a href="{{ route('home') }}" class="font-display font-bold">K&nbsp; Children's Classes</a>
            <p>© {{ now()->year }} Children's Class Management System</p>
            <nav class="flex gap-4"><a href="{{ route('regions') }}">Regions</a><a
                    href="{{ route('classes') }}">Classes</a><a href="{{ route('home') }}#about">About</a></nav>
        </div>
    </footer>

    @pushOnce('scripts')
        <script>
            const classActivityData = @json($activities);
            const activityIcons = document.querySelectorAll('.activity-tab');
            const activityTitle = document.getElementById('activity-detail-title');
            const activitySubtitle = document.getElementById('activity-detail-subtitle');
            const activityIcon = document.getElementById('activity-detail-icon');
            const activityChildren = document.getElementById('activity-children');
            const activityTeacher = document.getElementById('activity-teacher');
            const activityEveryLesson = document.getElementById('activity-every-lesson');

            activityIcons.forEach((button) => {
                button.addEventListener('click', () => {
                    const activity = classActivityData.find((item) => item.key === button.dataset.activity);
                    if (!activity) return;
                    activityIcons.forEach((item) => {
                        item.classList.remove('is-selected', 'border-[#FBA93A]', 'bg-[#8ED6EC]');
                        item.classList.add('border-[#EFE4CC]', 'bg-white');
                        item.setAttribute('aria-pressed', 'false');
                    });
                    button.classList.add('is-selected', 'border-[#FBA93A]', 'bg-[#8ED6EC]');
                    button.classList.remove('border-[#EFE4CC]', 'bg-white');
                    button.setAttribute('aria-pressed', 'true');
                    activityTitle.textContent = activity.title;
                    activitySubtitle.textContent = activity.subtitle;
                    activityChildren.textContent = activity.children;
                    activityTeacher.textContent = activity.teacher;
                    activityEveryLesson.textContent = activity.everyLesson;
                    activityIcon.setAttribute('aria-label', `${activity.name} activity`);
                });
            });
            document.getElementById('activity-detail-icon')?.replaceChildren(document.querySelector(
                '.activity-tab.is-selected span[aria-hidden="true"]')?.cloneNode(true) ?? document.createTextNode(''));
        </script>
    @endPushOnce
</x-guest-layout>
