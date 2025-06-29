<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Inspection;
use App\Models\User;

class UnifiedLoginController extends Controller
{
    /**
     * Menampilkan form login unified
     */
    public function showLoginForm()
    {
        // Jika sudah login, arahkan ke halaman yang sesuai
        if (Auth::check()) {
            return $this->redirectBasedOnRole();
        }

        return view('auth.unified.login');
    }

    /**
     * Handle proses login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Tentukan apakah login menggunakan email atau username
        $loginField = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $authData = [
            $loginField => $request->login,
            'password' => $request->password,
        ];

        // Tambahkan kondisi is_active
        $authData['is_active'] = true;

        // Coba login
        if (Auth::attempt($authData, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            // Arahkan user berdasarkan role
            return $this->redirectBasedOnRole();
        }

        return back()->withErrors([
            'login' => 'Email/username atau password salah, atau akun Anda tidak aktif.',
        ])->withInput($request->only('login'));
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('unified.login');
    }

    /**
     * Redirect berdasarkan role
     */
    protected function redirectBasedOnRole()
    {
        $user = Auth::user();

        // Cek apakah user memiliki tugas sebagai teknisi
        $hasInspectionAssignment = Inspection::where('technician_id', $user->id)->exists();

        // Cek role dan arahkan ke halaman yang sesuai
        if ($user->role === 'admin') {
            return redirect()->route('filament.admin.pages.dashboard');
        } elseif ($user->role === 'technician' || ($hasInspectionAssignment && $user->is_active)) {
            // Jika role teknisi atau punya tugas inspeksi, pastikan role-nya teknisi
            if ($hasInspectionAssignment && $user->role !== 'technician') {
                $user->update(['role' => 'technician']);
            }
            return redirect()->route('filament.technician.pages.dashboard');
        } elseif ($user->role === 'supervisor') {
            return redirect()->route('filament.admin.pages.dashboard');
        } else {
            return redirect()->route('dashboard');
        }
    }
}
