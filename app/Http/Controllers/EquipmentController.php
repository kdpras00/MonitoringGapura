<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class EquipmentController extends Controller
{
    public function showByQr($code)
    {
        // Decode URL encoded characters if any
        $originalCode = $code;
        $code = urldecode($code);

        // Additional decoding for problematic characters
        $code = str_replace(['+', '%20'], ' ', $code);

        // Log the code being searched for debugging
        \Illuminate\Support\Facades\Log::info('QR Code lookup:', [
            'original_param' => $originalCode,
            'after_urldecode' => urldecode($originalCode),
            'final_code' => $code,
            'url' => request()->url(),
            'full_url' => request()->fullUrl()
        ]);

        try {
            // Coba cari dengan kode QR persis
            $equipment = Equipment::where('qr_code', $code)->first();

            // Jika tidak ditemukan, coba cari dengan pola kode yang sama (format BGC-YYYY-001)
            if (!$equipment && preg_match('/^([A-Z]{3})-\d{4}-(\d{3})$/', $code, $matches)) {
                $prefix = $matches[1];
                $suffix = $matches[2];

                // Cari peralatan dengan prefix dan suffix yang sama, tahun bisa berbeda
                $equipment = Equipment::where('qr_code', 'LIKE', "{$prefix}-%{$suffix}")
                    ->orWhere('qr_code', 'LIKE', "{$prefix}-%")
                    ->first();

                if ($equipment) {
                    \Illuminate\Support\Facades\Log::info("Found equipment with similar QR code pattern: {$equipment->qr_code}");
                }
            }

            if (!$equipment) {
                throw new \Exception("Equipment not found with QR code: {$code}");
            }

            return view('equipment.show', compact('equipment'));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Equipment not found with QR code: ' . $code, [
                'error' => $e->getMessage(),
                'available_qr_codes' => Equipment::pluck('qr_code')->toArray()
            ]);

            // For debugging - show all available QR codes
            return response()->json([
                'error' => 'Equipment not found',
                'searched_for' => $code,
                'original_param' => $originalCode,
                'available_qr_codes' => Equipment::pluck('qr_code')->toArray(),
                'url' => request()->url(),
                'full_url' => request()->fullUrl()
            ], 404);
        }
    }

    public function quickAccess($code)
    {
        // Decode URL encoded characters if any
        $originalCode = $code;
        $code = urldecode($code);

        // Additional decoding for problematic characters
        $code = str_replace(['+', '%20'], ' ', $code);

        // Log the code being searched for debugging
        \Illuminate\Support\Facades\Log::info('Quick access QR Code lookup:', [
            'original_param' => $originalCode,
            'after_urldecode' => urldecode($originalCode),
            'final_code' => $code,
            'url' => request()->url(),
            'full_url' => request()->fullUrl()
        ]);

        try {
            // Coba cari dengan kode QR persis
            $equipment = Equipment::where('qr_code', $code)->first();

            // Jika tidak ditemukan, coba cari dengan pola kode yang sama (format BGC-YYYY-001)
            if (!$equipment && preg_match('/^([A-Z]{3})-\d{4}-(\d{3})$/', $code, $matches)) {
                $prefix = $matches[1];
                $suffix = $matches[2];

                // Cari peralatan dengan prefix dan suffix yang sama, tahun bisa berbeda
                $equipment = Equipment::where('qr_code', 'LIKE', "{$prefix}-%{$suffix}")
                    ->orWhere('qr_code', 'LIKE', "{$prefix}-%")
                    ->first();

                if ($equipment) {
                    \Illuminate\Support\Facades\Log::info("Found equipment with similar QR code pattern: {$equipment->qr_code}");
                }
            }

            if (!$equipment) {
                throw new \Exception("Equipment not found with QR code: {$code}");
            }

            return view('equipment.public-view', compact('equipment'));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Equipment not found with QR code: ' . $code, [
                'error' => $e->getMessage(),
                'available_qr_codes' => Equipment::pluck('qr_code')->toArray()
            ]);

            // For debugging - show all available QR codes
            return response()->json([
                'error' => 'Equipment not found',
                'searched_for' => $code,
                'original_param' => $originalCode,
                'available_qr_codes' => Equipment::pluck('qr_code')->toArray(),
                'url' => request()->url(),
                'full_url' => request()->fullUrl()
            ], 404);
        }
    }

    public function printQrCode($id)
    {
        $equipment = Equipment::findOrFail($id);
        return view('equipment.print-qr', compact('equipment'));
    }

    public function show(Equipment $equipment): RedirectResponse
    {
        return redirect()->route('filament.resources.equipment.view', $equipment->id);
    }

    public function updateSOP(Request $request, Equipment $equipment)
    {
        $request->validate([
            'sop_document' => 'required|file|mimes:pdf|max:2048', // Maksimum 2MB
        ]);

        $equipment->update([
            'sop_url' => $request->file('sop_document')->store('sops'),
        ]);

        return redirect()->back()->with('success', 'SOP document updated successfully!');
    }

    public function showQrById($id)
    {
        $equipment = Equipment::findOrFail($id);
        return view('equipment.show', compact('equipment'));
    }

    public function showBySerial($serial)
    {
        // Log the request for debugging
        \Illuminate\Support\Facades\Log::info('Accessing equipment by serial: ' . $serial);

        // Try exact match first
        $equipment = Equipment::where('serial_number', $serial)->first();

        // If not found, try case-insensitive search
        if (!$equipment) {
            $equipment = Equipment::whereRaw('LOWER(serial_number) = ?', [strtolower($serial)])->first();
        }

        // If still not found, try partial match
        if (!$equipment) {
            $equipment = Equipment::where('serial_number', 'LIKE', "%{$serial}%")->first();
        }

        if (!$equipment) {
            // Debugging - show available serial numbers
            $allSerials = Equipment::pluck('serial_number')->toArray();
            return response()->json([
                'error' => 'Equipment not found',
                'searched_for' => $serial,
                'available_serial_numbers' => $allSerials,
                'url' => request()->url(),
                'method' => request()->method()
            ]);
        }

        return view('equipment.show', compact('equipment'));
    }
}
