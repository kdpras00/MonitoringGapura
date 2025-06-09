<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Picqer\Barcode\BarcodeGeneratorHTML;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Illuminate\Support\Str;

class SparePart extends Model
{
    protected $fillable = [
        'name',
        'part_number',
        'barcode',
        'stock',
        'min_stock',
        'price',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'price' => 'decimal:2',
    ];
    
    protected static function boot()
    {
        parent::boot();
        
        // Auto-generate barcode when creating new spare part
        static::creating(function ($sparePart) {
            if (empty($sparePart->barcode)) {
                $sparePart->barcode = 'SP' . str_pad($sparePart->id ?? random_int(1000, 9999), 8, '0', STR_PAD_LEFT);
            }
        });
    }
    
    /**
     * Generate HTML barcode.
     *
     * @return string
     */
    public function getHtmlBarcode()
    {
        $generator = new BarcodeGeneratorHTML();
        return $generator->getBarcode($this->barcode ?? $this->part_number, $generator::TYPE_CODE_128, 2, 50);
    }
    
    /**
     * Generate PNG barcode.
     *
     * @return string
     */
    public function getPngBarcode()
    {
        $generator = new BarcodeGeneratorPNG();
        return 'data:image/png;base64,' . base64_encode(
            $generator->getBarcode($this->barcode ?? $this->part_number, $generator::TYPE_CODE_128, 2, 50)
        );
    }
    
    /**
     * Get URL for scanning barcode.
     *
     * @return string
     */
    public function getBarcodeUrl()
    {
        return route('spare-parts.scan', ['code' => $this->barcode ?? $this->part_number]);
    }
    
    /**
     * Get status label.
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'available' => 'Tersedia',
            'low_stock' => 'Stok Rendah',
            'out_of_stock' => 'Habis',
            default => $this->status,
        };
    }
    
    /**
     * Check if stock is low.
     */
    public function isLowStock()
    {
        return $this->stock <= $this->min_stock && $this->stock > 0;
    }
    
    /**
     * Check if out of stock.
     */
    public function isOutOfStock()
    {
        return $this->stock <= 0;
    }
    
    /**
     * Update status based on stock.
     */
    public function updateStatus()
    {
        if ($this->isOutOfStock()) {
            $this->status = 'out_of_stock';
        } elseif ($this->isLowStock()) {
            $this->status = 'low_stock';
        } else {
            $this->status = 'available';
        }
        
        return $this;
    }
}
