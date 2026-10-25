<x-app-layout title="Teacher Accounts — Children's Classes">
    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Header matching 06-admin-akun-guru.png -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="font-display font-bold text-3xl text-[#1F2F8A]">Teacher Accounts</h1>
                <p class="text-xs text-[#65737D] mt-1 font-sans">Settings › Teacher Accounts</p>
            </div>
            <details class="rounded-full bg-[#FBA93A] px-6 py-2.5 text-sm font-bold text-white">
                <summary class="cursor-pointer list-none">Add Teacher Account</summary>
                <div class="fixed inset-0 z-50 overflow-y-auto bg-ink/50 p-4 sm:p-8">
                    <div class="mx-auto mt-8 max-w-3xl rounded-3xl bg-white p-6 text-ink shadow-xl sm:p-8">
                        <div class="mb-5 flex items-center justify-between gap-4">
                            <h2 class="font-display text-xl font-bold text-navy">Buat akun guru</h2>
                            <button type="button" onclick="this.closest('details').open = false" class="rounded-lg px-3 py-2 text-sm font-bold text-muted hover:bg-cream">Tutup</button>
                        </div>
                        @include('admin.teachers._form', ['classes' => $classes])
                    </div>
                </div>
            </details>
        </div>

        @if(session('success'))
            <x-ui-alert variant="ok">{{ session('success') }}</x-ui-alert>
        @endif

        @if($errors->any())
            <x-ui-alert variant="danger" title="Periksa kembali data akun">
                <ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </x-ui-alert>
        @endif

        @if(session('temporary_credentials'))
            <x-ui-alert variant="warning" title="Kredensial sementara — salin sekarang">
                <p>Username: <strong>{{ session('temporary_credentials.username') }}</strong></p>
                <p>Kata sandi sementara: <strong>{{ session('temporary_credentials.password') }}</strong></p>
                <p class="mt-1">Kredensial ini hanya ditampilkan sekali. Guru wajib mengganti kata sandi saat masuk pertama kali.</p>
            </x-ui-alert>
        @endif

        <!-- Warning Banner matching 06-admin-akun-guru.png -->
        <div class="bg-[#FFF2D4] border-l-4 border-[#FBA93A] rounded-2xl p-4 text-xs sm:text-sm text-[#1D2B33]/85 font-sans">
            New accounts must change their password on first sign-in. Accounts are deactivated, not deleted.
        </div>

        <!-- Search Input matching mockup -->
        <div class="max-w-md">
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#65737D]">
                    🔍
                </span>
                <input
                    type="text"
                    placeholder="Search name or username"
                    class="w-full pl-10 pr-4 py-2.5 rounded-full border border-[#EFE4CC] bg-white text-xs sm:text-sm text-[#1D2B33] placeholder-[#65737D]/60 focus:border-[#FBA93A] focus:outline-none focus:ring-2 focus:ring-[#FBA93A]/20 transition-all font-sans"
                >
            </div>
        </div>

        <!-- Table Card matching 06-admin-akun-guru.png -->
        <div class="bg-white rounded-[28px] border-2 border-[#EFE4CC] p-6 sm:p-8 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-xs font-display font-bold text-[#65737D] border-b border-[#EFE4CC] pb-3">
                            <th class="py-3 px-4">Teachers</th>
                            <th class="py-3 px-4">Account</th>
                            <th class="py-3 px-4">Classes</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm font-sans divide-y divide-[#EFE4CC]">
                        @php
                            $avatarTypes = ['bangs', 'short', 'afro', 'beret', 'bun', 'pigtails'];
                        @endphp
                        @forelse($teachers as $index => $teacher)
                            <tr class="hover:bg-[#FFF8EA]/40 transition-colors">
                                <!-- Teacher avatar + name -->
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-3">
                                        <x-ui-avatar :type="$teacher->user?->avatar_type ?? $avatarTypes[$index % count($avatarTypes)]" :image="$teacher->user?->avatar_path" size="sm" :alt="$teacher->full_name" />
                                        <div>
                                            <span class="font-display font-bold text-[#1D2B33] block">
                                                {{ $teacher->full_name }}
                                            </span>
                                            @if($teacher->nickname)
                                                <span class="text-xs text-[#65737D]">({{ $teacher->nickname }})</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Account Username -->
                                <td class="py-4 px-4 text-xs font-mono text-[#65737D]">
                                    {{ $teacher->user?->username ?? '-' }}
                                </td>

                                <!-- Assigned Classes -->
                                <td class="py-4 px-4 text-xs text-[#1D2B33]">
                                    @php
                                        $classNames = $teacher->classes->pluck('name')->join(', ');
                                    @endphp
                                    {{ $classNames ?: '—' }}
                                </td>

                                <!-- Status Badge -->
                                <td class="py-4 px-4">
                                    @if($teacher->status === 'active')
                                        <span class="px-3 py-1 rounded-full text-xs font-display font-bold bg-[#E1F4E8] text-[#2E9E5B]">
                                            Active
                                        </span>
                                    @else
                                        <span class="px-3 py-1 rounded-full text-xs font-display font-bold bg-[#FCE6E6] text-[#D64545]">
                                            Inactive
                                        </span>
                                    @endif
                                </td>

                                <!-- Actions Links -->
                                <td class="py-4 px-4 text-right text-xs font-display font-semibold">
                                    <div class="flex flex-wrap justify-end gap-3">
                                        @if($teacher->user)
                                            <details class="text-left">
                                                <summary class="cursor-pointer text-[#2E4BC0] hover:underline">Edit</summary>
                                                <div class="fixed inset-0 z-50 overflow-y-auto bg-ink/50 p-4 text-left sm:p-8">
                                                    <div class="mx-auto mt-8 max-w-3xl rounded-3xl bg-white p-6 text-ink shadow-xl sm:p-8">
                                                        <div class="mb-5 flex items-center justify-between gap-4">
                                                            <h2 class="font-display text-xl font-bold text-navy">Edit {{ $teacher->full_name }}</h2>
                                                            <button type="button" onclick="this.closest('details').open = false" class="rounded-lg px-3 py-2 text-sm font-bold text-muted hover:bg-cream">Tutup</button>
                                                        </div>
                                                        @include('admin.teachers._form', ['teacher' => $teacher, 'classes' => $classes])
                                                    </div>
                                                </div>
                                            </details>
                                            <form method="POST" action="{{ route('admin.teachers.reset-password', $teacher->id) }}">
                                                @csrf
                                                <button type="submit" class="text-[#2E4BC0] hover:underline">Reset password</button>
                                            </form>
                                            @if($teacher->status === 'active')
                                                <form method="POST" action="{{ route('admin.teachers.deactivate', $teacher->id) }}" onsubmit="return confirm('Deactivate this teacher account? Historical records will be preserved.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-danger hover:underline">Deactivate</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('admin.teachers.reactivate', $teacher->id) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="text-[#2E4BC0] hover:underline">Reactivate</button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('admin.teachers.destroy', $teacher->id) }}" onsubmit="return confirm('Delete this teacher account permanently? Accounts with assignments or history cannot be deleted.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-danger hover:underline">Delete permanently</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-sm text-[#65737D]">
                                    No teacher accounts found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $teachers->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
