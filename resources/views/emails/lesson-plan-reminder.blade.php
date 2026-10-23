<h1>Pengingat kegiatan</h1>
<p>Halo {{ $lessonPlan->creator->name }},</p>
<p>Kegiatan <strong>{{ $lessonPlan->theme }}</strong> akan dilaksanakan pada {{ $lessonPlan->execution_date->format('d M Y') }}.</p>
@if($lessonPlan->class)
    <p>Kelas: {{ $lessonPlan->class->name }}</p>
@else
    <p>Jenis: Rencana kegiatan lainnya</p>
@endif
<p>{{ $lessonPlan->objectives }}</p>
<p><a href="{{ $calendarUrl }}">Tambahkan ke Google Calendar</a></p>
