<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Berbagi notifikasi ke semua views menggunakan Middleware
     */

    /**
     * Menandai semua notifikasi sebagai sudah dibaca
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $user->unreadNotifications->markAsRead();
            return redirect()->back()->with('success', 'Semua notifikasi telah ditandai sebagai sudah dibaca.');
        }

        return redirect()->back()->with('error', 'Gagal menandai notifikasi.');
    }
}
