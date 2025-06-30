<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Inspection;

trait StatusSafety
{
    /**
     * Method aman untuk memverifikasi inspeksi menggunakan query native.
     *
     * @param string|null $notes
     * @param int $userId
     * @return bool
     */
    public function safeVerify($notes, $userId)
    {
        try {
            Log::info('Attempting to verify inspection', [
                'inspection_id' => $this->id,
                'notes' => $notes,
                'user_id' => $userId
            ]);

            // Gunakan direct SQL statement yang pasti menggunakan quotes
            $sql = "UPDATE inspections SET
                      status = 'verified',
                      verification_notes = ?,
                      verification_date = NOW(),
                      verified_by = ?,
                      updated_at = NOW()
                    WHERE id = ?";

            Log::info('Executing SQL', ['sql' => $sql, 'params' => [$notes, $userId, $this->id]]);
            $result = DB::statement($sql, [$notes, $userId, $this->id]);

            Log::info('Verify inspection result', ['result' => $result, 'inspection_id' => $this->id]);

            if ($result) {
                // Force reset cache
                DB::connection()->flushQueryLog();
                
                // Force refresh model dari database
                $this->refresh();

                // Periksa status setelah refresh
                Log::info('Status after refresh', [
                    'inspection_id' => $this->id,
                    'status' => $this->status,
                    'status_type' => gettype($this->status),
                    'raw_query' => "SELECT status FROM inspections WHERE id = {$this->id}",
                    'debug_status' => $this->debugStatus()
                ]);

                // Jika status masih tidak berubah, coba sekali lagi dengan query native
                if ($this->status !== 'verified') {
                    Log::warning('Status still not updated properly, trying raw query', [
                        'inspection_id' => $this->id,
                        'expected' => 'verified',
                        'actual' => $this->status
                    ]);

                    // Coba update langsung dengan query native
                    try {
                        DB::statement("UPDATE inspections SET status = 'verified' WHERE id = ?", [$this->id]);
                        
                        // Reset cache lagi
                        DB::connection()->flushQueryLog();
                        
                        // Fresh reload dari database - paksa reload
                        $freshModel = Inspection::find($this->id);
                        if ($freshModel) {
                            $this->status = $freshModel->status;
                        }
                        
                        Log::info('Status after raw query', [
                            'inspection_id' => $this->id,
                            'status' => $this->status,
                            'debug_status' => $this->debugStatus()
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Error in raw query update', [
                            'inspection_id' => $this->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                return true;
            }
            return false;
        } catch (\Exception $e) {
            Log::error('Error in safeVerify', [
                'inspection_id' => $this->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Method aman untuk menolak inspeksi menggunakan query native.
     *
     * @param string $notes
     * @param int $userId
     * @return bool
     */
    public function safeReject($notes, $userId)
    {
        try {
            Log::info('Attempting to reject inspection', [
                'inspection_id' => $this->id,
                'notes' => $notes,
                'user_id' => $userId
            ]);

            // Gunakan direct SQL statement yang pasti menggunakan quotes
            $sql = "UPDATE inspections SET
                      status = 'rejected',
                      verification_notes = ?,
                      verification_date = NOW(),
                      verified_by = ?,
                      updated_at = NOW()
                    WHERE id = ?";

            Log::info('Executing SQL', ['sql' => $sql, 'params' => [$notes, $userId, $this->id]]);
            $result = DB::statement($sql, [$notes, $userId, $this->id]);

            Log::info('Reject inspection result', ['result' => $result, 'inspection_id' => $this->id]);

            if ($result) {
                // Force reset cache
                DB::connection()->flushQueryLog();
                
                // Force refresh model dari database
                $this->refresh();

                // Periksa status setelah refresh
                Log::info('Status after reject refresh', [
                    'inspection_id' => $this->id,
                    'status' => $this->status,
                    'status_type' => gettype($this->status),
                    'raw_query' => "SELECT status FROM inspections WHERE id = {$this->id}",
                    'debug_status' => $this->debugStatus()
                ]);

                // Jika status masih tidak berubah, coba sekali lagi dengan query native
                if ($this->status !== 'rejected') {
                    Log::warning('Status still not updated properly after reject, trying raw query', [
                        'inspection_id' => $this->id,
                        'expected' => 'rejected',
                        'actual' => $this->status
                    ]);

                    // Coba update langsung dengan query native
                    try {
                        DB::statement("UPDATE inspections SET status = 'rejected' WHERE id = ?", [$this->id]);
                        
                        // Reset cache lagi
                        DB::connection()->flushQueryLog();
                        
                        // Fresh reload dari database - paksa reload
                        $freshModel = Inspection::find($this->id);
                        if ($freshModel) {
                            $this->status = $freshModel->status;
                        }
                        
                        Log::info('Status after raw reject query', [
                            'inspection_id' => $this->id,
                            'status' => $this->status,
                            'debug_status' => $this->debugStatus()
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Error in raw reject query update', [
                            'inspection_id' => $this->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                return true;
            }
            return false;
        } catch (\Exception $e) {
            Log::error('Error in safeReject', [
                'inspection_id' => $this->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Method aman untuk mengembalikan ke pending menggunakan query native.
     *
     * @return bool
     */
    public function safeReturnToPending()
    {
        try {
            Log::info('Attempting to return inspection to pending', [
                'inspection_id' => $this->id
            ]);

            // Gunakan direct SQL statement yang pasti menggunakan quotes
            $sql = "UPDATE inspections SET
                      status = 'pending',
                      completion_date = NULL,
                      updated_at = NOW()
                    WHERE id = ?";

            Log::info('Executing SQL', ['sql' => $sql, 'params' => [$this->id]]);
            $result = DB::statement($sql, [$this->id]);

            Log::info('Return to pending result', ['result' => $result, 'inspection_id' => $this->id]);

            if ($result) {
                // Force refresh model dari database
                $this->refresh();

                // Periksa status setelah refresh
                Log::info('Status after return to pending', [
                    'inspection_id' => $this->id,
                    'status' => $this->status,
                    'debug_status' => $this->debugStatus()
                ]);

                return true;
            }
            return false;
        } catch (\Exception $e) {
            Log::error('Error in safeReturnToPending', [
                'inspection_id' => $this->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}
