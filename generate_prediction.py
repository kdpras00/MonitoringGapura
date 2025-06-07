import sys
import json
import os
import random
from datetime import datetime
import requests
from gemini_maintenance import (
    predict_maintenance,
    get_gemini_recommendation,
    make_maintenance_decision
)

def generate_sensor_data(equipment_id):
    """
    Menghasilkan data sensor untuk peralatan tertentu
    """
    # Nilai dasar berdasarkan jenis peralatan
    equipment_type_map = {
        "CONV": "conveyor",
        "XRAY": "scanner",
        "BRDG": "boarding_bridge",
        "BAGC": "baggage_carousel",
        "ELEV": "elevator",
        "ESCA": "escalator",
        "HVAC": "hvac",
        "GENR": "generator"
    }
    
    # Tentukan jenis peralatan dari ID
    equipment_prefix = equipment_id.split("-")[0]
    equipment_type = equipment_type_map.get(equipment_prefix, "unknown")
    
    # Nilai dasar berdasarkan jenis peralatan
    base_values = {
        "conveyor": {"vibration": 4.8, "temperature": 65, "pressure": 100, "humidity": 55},
        "scanner": {"vibration": 2.5, "temperature": 72, "pressure": 105, "humidity": 50},
        "boarding_bridge": {"vibration": 5.2, "temperature": 68, "pressure": 102, "humidity": 60},
        "baggage_carousel": {"vibration": 5.5, "temperature": 70, "pressure": 105, "humidity": 58},
        "elevator": {"vibration": 4.2, "temperature": 75, "pressure": 110, "humidity": 52},
        "escalator": {"vibration": 4.5, "temperature": 73, "pressure": 108, "humidity": 54},
        "hvac": {"vibration": 3.8, "temperature": 85, "pressure": 130, "humidity": 45},
        "generator": {"vibration": 6.2, "temperature": 92, "pressure": 140, "humidity": 40},
        "unknown": {"vibration": 5.0, "temperature": 75, "pressure": 110, "humidity": 55}
    }
    
    # Jika ID adalah CONV-A1, buat nilai yang lebih tinggi untuk memicu maintenance
    if equipment_id == "CONV-A1":
        data = {
            "equipment_id": equipment_id,
            "vibration": round(7.2 + random.uniform(-0.2, 0.5), 2),  # Nilai vibrasi tinggi
            "temperature": round(95 + random.uniform(-1, 3), 1),     # Suhu tinggi
            "pressure": round(base_values[equipment_type]["pressure"] + random.uniform(-5, 5), 1),
            "humidity": round(base_values[equipment_type]["humidity"] + random.uniform(-3, 3), 1),
            "timestamp": datetime.now().isoformat()
        }
    else:
        # Ambil nilai dasar
        base = base_values.get(equipment_type, base_values["unknown"])
        
        # Tambahkan variasi kecil
        data = {
            "equipment_id": equipment_id,
            "vibration": round(base["vibration"] + random.uniform(-0.5, 0.5), 2),
            "temperature": round(base["temperature"] + random.uniform(-3, 3), 1),
            "pressure": round(base["pressure"] + random.uniform(-5, 5), 1),
            "humidity": round(base["humidity"] + random.uniform(-3, 3), 1),
            "timestamp": datetime.now().isoformat()
        }
    
    return data

def get_equipment_by_id(equipment_id):
    """
    Mendapatkan data peralatan berdasarkan ID
    """
    # Cek apakah file frontend_data.json sudah ada
    frontend_data_path = "storage/app/data/frontend_data.json"
    equipment = None
    
    if os.path.exists(frontend_data_path):
        with open(frontend_data_path, 'r') as f:
            all_equipment = json.load(f)
            
            # Cari peralatan dengan ID yang sesuai
            for eq in all_equipment:
                if eq["id"] == equipment_id:
                    equipment = eq
                    break
    
    return equipment

def update_equipment_data(equipment_id, new_data):
    """
    Memperbarui data peralatan di frontend_data.json
    """
    frontend_data_path = "storage/app/data/frontend_data.json"
    
    if os.path.exists(frontend_data_path):
        with open(frontend_data_path, 'r') as f:
            all_equipment = json.load(f)
        
        # Cari dan perbarui peralatan
        for i, eq in enumerate(all_equipment):
            if eq["id"] == equipment_id:
                all_equipment[i] = new_data
                break
        
        # Simpan kembali ke file
        with open(frontend_data_path, 'w') as f:
            json.dump(all_equipment, f, indent=2)
        
        return True
    
    return False

def generate_prediction_for_equipment(equipment_id):
    """
    Menghasilkan prediksi untuk peralatan tertentu
    """
    # Dapatkan data peralatan
    equipment = get_equipment_by_id(equipment_id)
    
    if not equipment:
        print(f"Peralatan dengan ID {equipment_id} tidak ditemukan")
        return False
    
    # Hasilkan data sensor baru
    sensor_data = generate_sensor_data(equipment_id)
    
    # Lakukan prediksi dengan model ML
    ml_prediction = predict_maintenance(sensor_data)
    
    # Dapatkan rekomendasi dari Gemini
    gemini_recommendation = get_gemini_recommendation(sensor_data, ml_prediction)
    
    # Buat keputusan final
    final_decision = make_maintenance_decision(ml_prediction, gemini_recommendation)
    
    # Perbarui data peralatan
    equipment["sensor_data"] = sensor_data
    equipment["prediction"] = final_decision
    
    # Hitung skor kondisi
    if not final_decision.get("maintenance_required", False):
        condition_score = random.randint(85, 100)
    else:
        # Berdasarkan urgency level
        urgency = final_decision.get("urgency_level", "medium")
        if urgency == "low":
            condition_score = random.randint(70, 84)
        elif urgency == "medium":
            condition_score = random.randint(50, 69)
        elif urgency == "high":
            condition_score = random.randint(30, 49)
        else:  # critical
            condition_score = random.randint(10, 29)
    
    equipment["condition_score"] = condition_score
    
    # Perbarui rekomendasi
    if final_decision.get("maintenance_required", False):
        if "recommended_action" in final_decision:
            equipment["recommendation"] = final_decision["recommended_action"]
        else:
            urgency = final_decision.get("urgency_level", "medium")
            if urgency == "critical":
                equipment["recommendation"] = "Segera lakukan pemeriksaan dan perbaikan"
            elif urgency == "high":
                equipment["recommendation"] = "Jadwalkan pemeriksaan dalam 24 jam"
            elif urgency == "medium":
                equipment["recommendation"] = "Jadwalkan pemeriksaan dalam minggu ini"
            else:
                equipment["recommendation"] = "Pantau kondisi dan jadwalkan pemeriksaan rutin"
    else:
        equipment["recommendation"] = "Tidak ada tindakan yang diperlukan"
    
    # Perbarui jadwal maintenance jika diperlukan
    if final_decision.get("maintenance_required", False):
        # Tentukan tanggal maintenance berdasarkan urgency
        today = datetime.now()
        
        urgency = final_decision.get("urgency_level", "medium")
        if urgency == "critical":
            equipment["next_maintenance"] = today.strftime("%Y-%m-%d")  # Hari ini
        elif urgency == "high":
            from datetime import timedelta
            equipment["next_maintenance"] = (today + timedelta(days=1)).strftime("%Y-%m-%d")  # Besok
        elif urgency == "medium":
            from datetime import timedelta
            equipment["next_maintenance"] = (today + timedelta(days=3)).strftime("%Y-%m-%d")  # 3 hari lagi
        else:  # low
            from datetime import timedelta
            equipment["next_maintenance"] = (today + timedelta(days=7)).strftime("%Y-%m-%d")  # 7 hari lagi
    else:
        equipment["next_maintenance"] = "Tidak diperlukan"
    
    # Simpan perubahan
    update_equipment_data(equipment_id, equipment)
    
    print(f"Prediksi untuk {equipment_id} berhasil dihasilkan")
    return True

if __name__ == "__main__":
    # Periksa argumen command line
    if len(sys.argv) < 2:
        print("Gunakan: python generate_prediction.py <equipment_id>")
        sys.exit(1)
    
    equipment_id = sys.argv[1]
    generate_prediction_for_equipment(equipment_id) 