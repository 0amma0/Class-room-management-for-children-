<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Kelas Anak — Tumbuh Ceria Bersama' }}</title>

    <!-- Google Fonts: Fredoka (Headings/Buttons) & Nunito (Body) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#FFF8EA] text-[#1D2B33] font-sans antialiased min-h-screen flex flex-col selection:bg-[#FFD86B] selection:text-[#1D2B33]">
    {{ $slot }}
    <x-support-chat-widget />
    @stack('scripts')
</body>
</html>
