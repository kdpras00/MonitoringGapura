<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SparePart;

class SparePartController extends Controller
{
    /**
     * Tampilkan halaman untuk mencetak barcode.
     */
    public function barcode(SparePart $sparePart)
    {
        return view('spare-parts.barcode', compact('sparePart'));
    }

    /**
     * Tampilkan halaman setelah scan barcode.
     */
    public function scan(Request $request)
    {
        $code = $request->code;
        
        // Cari spare part berdasarkan barcode
        $sparePart = SparePart::where('barcode', $code)->first();
        
        if (!$sparePart) {
            // Jika tidak ditemukan, coba cari berdasarkan part_number
            $sparePart = SparePart::where('part_number', $code)->first();
            
            if (!$sparePart) {
                return redirect()->route('spare-parts.not-found', ['code' => $code]);
            }
        }
        
        return view('spare-parts.scan-result', compact('sparePart'));
    }
    
    /**
     * Tampilkan halaman ketika barcode tidak ditemukan.
     */
    public function notFound(Request $request)
    {
        $code = $request->code;
        return view('spare-parts.not-found', compact('code'));
    }
} 