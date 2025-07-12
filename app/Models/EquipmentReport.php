<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EquipmentReport extends Model
{
    use HasFactory;
    
    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';      // Menunggu persetujuan
    const STATUS_APPROVED = 'approved';    // Disetujui dan dikonversi ke jadwal maintenance
    const STATUS_REJECTED = 'rejected';    // Ditolak

    /**
     * Priority constants
     */
    const PRIORITY_LOW = 'hijau';         // Prioritas rendah
    const PRIORITY_MEDIUM = 'kuning';     // Prioritas sedang
    const PRIORITY_HIGH = 'merah';        // Prioritas tinggi

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'equipment_id',
        'reporter_id',
        'approver_id',
        'maintenance_id',
        'issue_description',
        'issue_image',
        'priority',
        'status',
        'reported_at',
        'approved_at',
        'rejection_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'reported_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'status_label',
        'priority_label',
    ];

    /**
     * Get the equipment associated with this report.
     */
    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    /**
     * Get the user who reported the issue.
     */
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /**
     * Get the user who approved or rejected the report.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * Get the maintenance record created from this report.
     */
    public function maintenance()
    {
        return $this->belongsTo(Maintenance::class);
    }

    /**
     * Get the status label attribute.
     *
     * @return string
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Menunggu Persetujuan',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_REJECTED => 'Ditolak',
            default => 'Unknown',
        };
    }

    /**
     * Get the priority label attribute.
     *
     * @return string
     */
    public function getPriorityLabelAttribute()
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'Rendah',
            self::PRIORITY_MEDIUM => 'Sedang',
            self::PRIORITY_HIGH => 'Tinggi',
            default => 'Unknown',
        };
    }

    /**
     * Approve this report and create a maintenance record.
     *
     * @param int $approverId ID of the user who approved
     * @param string|null $notes Optional notes for the maintenance
     * @param \Carbon\Carbon|null $scheduleDate Optional schedule date, defaults to now
     * @return \App\Models\Maintenance|null
     */
    public function approve($approverId, $notes = null, $scheduleDate = null)
    {
        if ($this->status !== self::STATUS_PENDING) {
            return null;
        }

        // Create a new maintenance record
        $maintenance = new Maintenance();
        $maintenance->equipment_id = $this->equipment_id;
        $maintenance->schedule_date = $scheduleDate ?? now();
        $maintenance->status = Maintenance::STATUS_PENDING; // Jadwal sudah disusun, menunggu penugasan teknisi
        $maintenance->priority = $this->priority;
        $maintenance->notes = $notes ?? "Dibuat otomatis dari laporan kerusakan: {$this->issue_description}";
        $maintenance->maintenance_type = 'corrective'; // Karena dari laporan kerusakan, jenis maintenancenya corrective
        $maintenance->cost = 0; // Default cost
        $maintenance->save();

        // Update this report
        $this->status = self::STATUS_APPROVED;
        $this->approver_id = $approverId;
        $this->approved_at = now();
        $this->maintenance_id = $maintenance->id;
        $this->save();

        return $maintenance;
    }

    /**
     * Reject this report.
     *
     * @param int $approverId ID of the user who rejected
     * @param string $reason Reason for rejection
     * @return bool
     */
    public function reject($approverId, $reason)
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        $this->status = self::STATUS_REJECTED;
        $this->approver_id = $approverId;
        $this->approved_at = now();
        $this->rejection_reason = $reason;
        return $this->save();
    }
} 