<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inspection;
use App\Models\Maintenance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UpdateInspectionController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->middleware('web'); // Pastikan middleware web untuk CSRF protection
    }

    /**
     * Verifikasi inspeksi dengan metode aman.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function verify(Request $request, $id)
    {
        Log::info('Verify inspection API called', ['id' => $id, 'request' => $request->all()]);

        // Gunakan transaction untuk memastikan semua operasi database berhasil atau gagal bersama
        DB::beginTransaction();

        try {
            $inspection = Inspection::findOrFail($id);

            Log::info('Inspection found', [
                'id' => $inspection->id,
                'current_status' => $inspection->status
            ]);

            // Pastikan pengguna adalah supervisor
            if (auth()->user()->role !== 'supervisor') {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized - Only supervisors can verify inspections'
                ], 403);
            }

            // Validasi data masukan
            $validated = $request->validate([
                'verification_notes' => 'nullable|string',
            ]);

            // Gunakan metode aman untuk verifikasi
            $success = $inspection->safeVerify(
                $validated['verification_notes'] ?? null,
                auth()->id()
            );

            if (!$success) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to verify inspection - database update failed'
                ], 500);
            }

            // Reload inspection untuk mendapatkan data terbaru
            $inspection->refresh();

            // Verifikasi bahwa status benar-benar berubah
            if ($inspection->status !== 'verified') {
                Log::warning('Inspection status not updated correctly', [
                    'id' => $inspection->id,
                    'expected' => 'verified',
                    'actual' => $inspection->status
                ]);

                // Coba update langsung menggunakan DB facade
                DB::update(
                    "UPDATE inspections SET status = 'verified' WHERE id = ?",
                    [$inspection->id]
                );

                // Refresh lagi
                $inspection->refresh();
            }

            // Update status maintenance jika ada
            $maintenance = Maintenance::where('equipment_id', $inspection->equipment_id)
                ->where('technician_id', $inspection->technician_id)
                ->whereIn('status', ['in-progress', 'planned', 'pending'])
                ->first();

            if ($maintenance) {
                Log::info('Updating related maintenance', ['maintenance_id' => $maintenance->id]);
                $maintenance->status = 'completed';
                $maintenance->actual_date = now();
                $maintenance->save();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Inspection verified successfully',
                'data' => [
                    'inspection_id' => $inspection->id,
                    'current_status' => $inspection->status,
                    'verification_date' => $inspection->verification_date
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error verifying inspection', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error verifying inspection: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tolak inspeksi dengan metode aman.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function reject(Request $request, $id)
    {
        Log::info('Reject inspection API called', ['id' => $id, 'request' => $request->all()]);

        // Gunakan transaction untuk memastikan semua operasi database berhasil atau gagal bersama
        DB::beginTransaction();

        try {
            $inspection = Inspection::findOrFail($id);

            Log::info('Inspection found', [
                'id' => $inspection->id,
                'current_status' => $inspection->status
            ]);

            // Pastikan pengguna adalah supervisor
            if (auth()->user()->role !== 'supervisor') {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized - Only supervisors can reject inspections'
                ], 403);
            }

            // Validasi data masukan
            $validated = $request->validate([
                'verification_notes' => 'required|string',
            ]);

            // Gunakan metode aman untuk tolak
            $success = $inspection->safeReject(
                $validated['verification_notes'],
                auth()->id()
            );

            if (!$success) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to reject inspection - database update failed'
                ], 500);
            }

            // Reload inspection untuk mendapatkan data terbaru
            $inspection->refresh();

            // Verifikasi bahwa status benar-benar berubah
            if ($inspection->status !== 'rejected') {
                Log::warning('Inspection status not updated correctly', [
                    'id' => $inspection->id,
                    'expected' => 'rejected',
                    'actual' => $inspection->status
                ]);

                // Coba update langsung menggunakan DB facade
                DB::update(
                    "UPDATE inspections SET status = 'rejected' WHERE id = ?",
                    [$inspection->id]
                );

                // Refresh lagi
                $inspection->refresh();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Inspection rejected successfully',
                'data' => [
                    'inspection_id' => $inspection->id,
                    'current_status' => $inspection->status,
                    'verification_date' => $inspection->verification_date
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting inspection', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error rejecting inspection: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kembalikan inspeksi ke status pending dengan metode aman.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function returnToPending($id)
    {
        Log::info('Return inspection to pending API called', ['id' => $id]);

        // Gunakan transaction untuk memastikan semua operasi database berhasil atau gagal bersama
        DB::beginTransaction();

        try {
            $inspection = Inspection::findOrFail($id);

            Log::info('Inspection found', [
                'id' => $inspection->id,
                'current_status' => $inspection->status
            ]);

            // Pastikan pengguna adalah supervisor
            if (auth()->user()->role !== 'supervisor') {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized - Only supervisors can return inspections to pending'
                ], 403);
            }

            // Gunakan metode aman untuk kembalikan ke pending
            $success = $inspection->safeReturnToPending();

            if (!$success) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to return inspection to pending - database update failed'
                ], 500);
            }

            // Reload inspection untuk mendapatkan data terbaru
            $inspection->refresh();

            // Verifikasi bahwa status benar-benar berubah
            if ($inspection->status !== 'pending') {
                Log::warning('Inspection status not updated correctly', [
                    'id' => $inspection->id,
                    'expected' => 'pending',
                    'actual' => $inspection->status
                ]);

                // Coba update langsung menggunakan DB facade
                DB::update(
                    "UPDATE inspections SET status = 'pending' WHERE id = ?",
                    [$inspection->id]
                );

                // Refresh lagi
                $inspection->refresh();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Inspection returned to pending successfully',
                'data' => [
                    'inspection_id' => $inspection->id,
                    'current_status' => $inspection->status
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error returning inspection to pending', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error returning inspection to pending: ' . $e->getMessage()
            ], 500);
        }
    }
}
