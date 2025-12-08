<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Gate Perpustakaan - UBT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #F3F4F6; }
    </style>
</head>
<body class="p-6">

    <div class="max-w-7xl mx-auto space-y-6">
        <div class="bg-white p-4 rounded-xl shadow-sm flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">@yield('title')</h1>
            
            <div class="flex items-center gap-4">

                <div class="flex border rounded-lg overflow-hidden">
                    <a href="{{ route('dashboard') }}" class="px-4 py-2 text-sm {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-600 font-medium' : 'bg-white text-gray-600' }}">Ringkas</a>
                    <a href="{{ route('visitors.active') }}" class="px-4 py-2 text-sm border-l {{ request()->routeIs('visitors.active') ? 'bg-blue-50 text-blue-600 font-medium' : 'bg-white text-gray-600' }}">Aktif</a>
                    <a href="{{ route('visitors.logs') }}" class="px-4 py-2 text-sm border-l {{ request()->routeIs('visitors.logs') ? 'bg-blue-50 text-blue-600 font-medium' : 'bg-white text-gray-600' }}">Log</a>
                    <a href="{{ route('members.index') }}" class="px-4 py-2 text-sm border-l {{ request()->routeIs('members.*') ? 'bg-blue-50 text-blue-600 font-medium' : 'bg-white text-gray-600' }}">Member</a>
                </div>
            </div>
        </div>

        @yield('content')

        <div class="text-center text-gray-400 text-sm mt-8">
            © 2025 Universitas Borneo Tarakan • Sistem Buku Tamu Perpustakaan (Smart Gate RFID)
        </div>
    </div>

    @yield('scripts')
</body>
</html>