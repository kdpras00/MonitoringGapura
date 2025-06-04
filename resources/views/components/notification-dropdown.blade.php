<div class="relative">
    <button onclick="toggleNotifications()" class="p-2 text-gray-700 hover:text-gray-900 focus:outline-none">
        <i class="fas fa-bell"></i>
        @if ($notifications->count() > 0)
            <span class="absolute top-0 right-0 bg-red-500 text-white rounded-full text-xs px-1">
                {{ $notifications->count() }}
            </span>
        @endif
    </button>

    <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg z-50">
        <div class="px-4 py-2 border-b">
            <h3 class="text-lg font-semibold">Notifikasi</h3>
        </div>

        @forelse($notifications as $notification)
            <a href="{{ $notification->data['link'] }}" class="block px-4 py-2 hover:bg-gray-100">
                <div class="text-sm text-gray-700">
                    {{ $notification->data['message'] }}
                </div>
                <div class="text-xs text-gray-500">
                    {{ $notification->created_at->diffForHumans() }}
                </div>
            </a>
        @empty
            <div class="px-4 py-2 text-gray-500">Tidak ada notifikasi baru.</div>
        @endforelse

        @if ($notifications->count() > 0)
            <div class="px-4 py-2 border-t">
                <form action="{{ route('notifications.mark-all-as-read') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-sm text-blue-500 hover:text-blue-700">
                        Tandai semua sudah dibaca
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>

<script>
    function toggleNotifications() {
        document.getElementById('notificationDropdown').classList.toggle('hidden');
    }

    // Tutup dropdown saat klik di luar
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('notificationDropdown');
        const button = document.querySelector('[onclick="toggleNotifications()"]');
        if (!dropdown.contains(event.target) && !button.contains(event.target)) {
            dropdown.classList.add('hidden');
        }
    });
</script>
