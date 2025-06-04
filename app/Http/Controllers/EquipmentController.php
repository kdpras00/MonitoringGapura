<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class EquipmentController extends Controller
{
    public function showByQr($code)
    {
        $equipment = Equipment::where('qr_code', $code)->firstOrFail();
        return view('equipment.show', compact('equipment'));
    }

    public function show(Equipment $equipment): RedirectResponse
    {
        return redirect()->route('filament.resources.equipments.view', $equipment->id);
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
}
