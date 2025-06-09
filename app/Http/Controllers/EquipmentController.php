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

            // Jika tidak ditemukan, coba dengan case insensitive
            if (!$equipment) {
                $equipment = Equipment::whereRaw('LOWER(qr_code) = ?', [strtolower($code)])->first();
            }

            // Jika masih tidak ditemukan, coba dengan LIKE
            if (!$equipment) {
                $equipment = Equipment::where('qr_code', 'LIKE', "%{$code}%")->first();
            }

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

            // Jika tidak ditemukan, coba dengan case insensitive
            if (!$equipment) {
                $equipment = Equipment::whereRaw('LOWER(qr_code) = ?', [strtolower($code)])->first();
            }

            // Jika masih tidak ditemukan, coba dengan LIKE
            if (!$equipment) {
                $equipment = Equipment::where('qr_code', 'LIKE', "%{$code}%")->first();
            }

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

    /**
     * Tampilkan halaman untuk mencetak barcode.
     */
    public function barcode(Equipment $equipment)
    {
        // Gunakan barcode jika ada, jika tidak gunakan QR code
        $scanCode = $equipment->barcode ?: $equipment->qr_code;
        return view('equipment.barcode', compact('equipment', 'scanCode'));
    }

    /**
     * Tampilkan halaman setelah scan barcode.
     */
    public function scan(Request $request)
    {
        try {
            // Ambil code dari parameter GET
            $code = $request->query('code');
            
            // Log untuk debugging
            \Illuminate\Support\Facades\Log::info('Scanning equipment dengan kode: ' . $code);
            \Illuminate\Support\Facades\Log::info('Request full URL: ' . $request->fullUrl());
            \Illuminate\Support\Facades\Log::info('Request all parameters: ' . json_encode($request->all()));
            
            if (empty($code)) {
                \Illuminate\Support\Facades\Log::error('Parameter code kosong');
                return redirect()->route('equipment.not-found', ['code' => 'empty']);
            }
            
            // Cari equipment berdasarkan barcode
            $equipment = Equipment::where('barcode', $code)->first();
            \Illuminate\Support\Facades\Log::info('Hasil pencarian dengan barcode: ' . ($equipment ? 'Ditemukan' : 'Tidak ditemukan'));
            
            if (!$equipment) {
                // Jika tidak ditemukan, coba cari berdasarkan qr_code
                $equipment = Equipment::where('qr_code', $code)->first();
                \Illuminate\Support\Facades\Log::info('Hasil pencarian dengan qr_code: ' . ($equipment ? 'Ditemukan' : 'Tidak ditemukan'));
                
                if (!$equipment) {
                    // Jika masih tidak ditemukan, coba cari berdasarkan serial_number
                    $equipment = Equipment::where('serial_number', $code)->first();
                    \Illuminate\Support\Facades\Log::info('Hasil pencarian dengan serial_number: ' . ($equipment ? 'Ditemukan' : 'Tidak ditemukan'));
                    
                    if (!$equipment) {
                        // Log all equipments for debugging
                        $allEquipments = Equipment::select('id', 'barcode', 'qr_code', 'serial_number')->get();
                        \Illuminate\Support\Facades\Log::error('Equipment tidak ditemukan dengan kode: ' . $code);
                        \Illuminate\Support\Facades\Log::error('Semua equipment yang tersedia: ' . json_encode($allEquipments));
                        
                        return redirect()->to('/equipment/not-found?code=' . urlencode($code));
                    }
                }
            }
            
            return view('equipment.scan-result', compact('equipment'));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error saat scanning: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error('Trace: ' . $e->getTraceAsString());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Tampilkan halaman ketika barcode tidak ditemukan.
     */
    public function notFound(Request $request)
    {
        try {
            $code = $request->query('code');
            \Illuminate\Support\Facades\Log::info('Not Found page accessed with code: ' . $code);
            return view('equipment.not-found', compact('code'));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error di halaman not found: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
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

    /**
     * Tampilkan detail equipment berdasarkan ID.
     */
    public function viewById($id)
    {
        try {
            \Illuminate\Support\Facades\Log::info('Mencari equipment dengan ID: ' . $id);
            $equipment = Equipment::findOrFail($id);
            
            \Illuminate\Support\Facades\Log::info('Equipment ditemukan: ' . $equipment->name);
            
            // Log semua atribut equipment untuk debugging
            \Illuminate\Support\Facades\Log::debug('Equipment attributes:', $equipment->toArray());
            
            // Memastikan checklist berupa array sebelum diteruskan ke view
            if (property_exists($equipment, 'checklist') && !empty($equipment->checklist)) {
                if (!is_array($equipment->checklist)) {
                    if (is_string($equipment->checklist)) {
                        try {
                            $decoded = json_decode($equipment->checklist, true);
                            if (is_array($decoded)) {
                                $equipment->checklist = $decoded;
                            } else {
                                $equipment->checklist = [$equipment->checklist];
                            }
                        } catch (\Exception $e) {
                            $equipment->checklist = [];
                            \Illuminate\Support\Facades\Log::error('Error decoding checklist: ' . $e->getMessage());
                        }
                    } else {
                        $equipment->checklist = [];
                    }
                }
                \Illuminate\Support\Facades\Log::info('Checklist type after processing: ' . gettype($equipment->checklist));
            } else {
                $equipment->checklist = [];
            }
            
            return view('equipment.scan-result', compact('equipment'));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error saat melihat equipment ID ' . $id . ': ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->to('/equipment/not-found?code=ID-' . $id);
        }
    }
}
