import json
import os
import random
from datetime import datetime, timedelta
import requests
import pandas as pd

# Konfigurasi
API_URL = "http://localhost:5001"
EQUIPMENT_FILE = "storage/app/data/airport_equipment.json"

# Daftar peralatan bandara
airport_equipment = [
    {"id": "CONV-A1", "name": "Conveyor Belt A1", "type": "conveyor", "location": "Terminal A"},
    {"id": "CONV-A2", "name": "Conveyor Belt A2", "type": "conveyor", "location": "Terminal A"},
    {"id": "XRAY-B1", "name": "X-Ray Scanner B1", "type": "scanner", "location": "Security Checkpoint B"},
    {"id": "BRDG-C1", "name": "Passenger Boarding Bridge C1", "type": "boarding_bridge", "location": "Gate C1"},
    {"id": "BRDG-C2", "name": "Passenger Boarding Bridge C2", "type": "boarding_bridge", "location": "Gate C2"},
    {"id": "BAGC-D1", "name": "Baggage Carousel D1", "type": "baggage_carousel", "location": "Arrival Hall D"},
    {"id": "ELEV-E1", "name": "Elevator E1", "type": "elevator", "location": "Terminal E"},
    {"id": "ESCA-E2", "name": "Escalator E2", "type": "escalator", "location": "Terminal E"},
    {"id": "HVAC-F1", "name": "HVAC System F1", "type": "hvac", "location": "Terminal F"},
    {"id": "GENR-G1", "name": "Generator G1", "type": "generator", "location": "Power Station G"}
]

# Simpan data peralatan ke file
def save_equipment_data():
    os.makedirs(os.path.dirname(EQUIPMENT_FILE), exist_ok=True)
    with open(EQUIPMENT_FILE, 'w') as f:
        json.dump(airport_equipment, f, indent=2)
    print(f"Data peralatan disimpan ke {EQUIPMENT_FILE}")

# Fungsi untuk menghasilkan data sensor berdasarkan jenis peralatan
def generate_sensor_data(equipment):
    equipment_type = equipment["type"]
    
    # Nilai dasar berdasarkan jenis peralatan
    base_values = {
        "conveyor": {"vibration": 4.8, "temperature": 65, "pressure": 100, "humidity": 55},
        "scanner": {"vibration": 2.5, "temperature": 72, "pressure": 105, "humidity": 50},
        "boarding_bridge": {"vibration": 5.2, "temperature": 68, "pressure": 102, "humidity": 60},
        "baggage_carousel": {"vibration": 5.5, "temperature": 70, "pressure": 105, "humidity": 58},
        "elevator": {"vibration": 4.2, "temperature": 75, "pressure": 110, "humidity": 52},
        "escalator": {"vibration": 4.5, "temperature": 73, "pressure": 108, "humidity": 54},
        "hvac": {"vibration": 3.8, "temperature": 85, "pressure": 130, "humidity": 45},
        "generator": {"vibration": 6.2, "temperature": 92, "pressure": 140, "humidity": 40}
    }
    
    # Jika jenis peralatan tidak ada dalam daftar, gunakan nilai default
    base = base_values.get(equipment_type, {"vibration": 5.0, "temperature": 75, "pressure": 110, "humidity": 55})
    
    # Tambahkan variasi kecil untuk mensimulasikan perubahan kondisi
    # Untuk Conveyor Belt A1, buat nilai yang lebih tinggi untuk memicu maintenance
    if equipment["id"] == "CONV-A1":
        data = {
            "equipment_id": equipment["id"],
            "vibration": round(7.2 + random.uniform(-0.2, 0.5), 2),  # Nilai vibrasi tinggi
            "temperature": round(95 + random.uniform(-1, 3), 1),     # Suhu tinggi
            "pressure": round(base["pressure"] + random.uniform(-5, 5), 1),
            "humidity": round(base["humidity"] + random.uniform(-3, 3), 1),
            "timestamp": datetime.now().isoformat()
        }
    else:
        data = {
            "equipment_id": equipment["id"],
            "vibration": round(base["vibration"] + random.uniform(-0.5, 0.5), 2),
            "temperature": round(base["temperature"] + random.uniform(-3, 3), 1),
            "pressure": round(base["pressure"] + random.uniform(-5, 5), 1),
            "humidity": round(base["humidity"] + random.uniform(-3, 3), 1),
            "timestamp": datetime.now().isoformat()
        }
    
    return data

# Fungsi untuk mengirim data ke API dan mendapatkan prediksi
def get_prediction(sensor_data):
    try:
        print(f"Mengirim data ke API untuk {sensor_data['equipment_id']}...")
        
        # Untuk debugging, jika API tidak tersedia, gunakan data dummy
        # Uncomment baris di bawah untuk menggunakan API sebenarnya
        # response = requests.post(f"{API_URL}/api/hybrid-predict", json=sensor_data)
        # if response.status_code == 200:
        #     return response.json()
        
        # Data dummy untuk testing
        prediction = {
            "maintenance_required": sensor_data["equipment_id"] == "CONV-A1",
            "confidence": 0.85,
            "method": "dummy",
            "urgency_level": "high" if sensor_data["equipment_id"] == "CONV-A1" else "low",
            "recommended_action": "Periksa belt conveyor dan motor penggerak" if sensor_data["equipment_id"] == "CONV-A1" else "Tidak ada tindakan yang diperlukan",
            "potential_issues": ["Belt aus", "Motor overheating"] if sensor_data["equipment_id"] == "CONV-A1" else [],
            "estimated_maintenance_time_hours": 4 if sensor_data["equipment_id"] == "CONV-A1" else 1,
            "parts_needed": ["Belt conveyor", "Bearing"] if sensor_data["equipment_id"] == "CONV-A1" else [],
            "justification": "Vibrasi dan suhu tinggi mengindikasikan masalah pada belt conveyor" if sensor_data["equipment_id"] == "CONV-A1" else "Semua parameter dalam batas normal"
        }
        
        print(f"Prediksi untuk {sensor_data['equipment_id']}: {prediction['maintenance_required']}")
        return prediction
        
    except Exception as e:
        print(f"Error saat mengirim data ke API: {str(e)}")
        return None

# Fungsi untuk menjadwalkan maintenance jika diperlukan
def schedule_maintenance(prediction):
    if prediction and prediction.get("maintenance_required", False):
        try:
            print(f"Menjadwalkan maintenance untuk {prediction['sensor_data']['equipment_id']}...")
            
            # Data dummy untuk testing
            return {
                "equipment_id": prediction["sensor_data"]["equipment_id"],
                "maintenance_scheduled": True,
                "scheduled_date": datetime.now().strftime("%Y-%m-%d"),
                "urgency_level": prediction.get("urgency_level", "medium"),
                "estimated_time": prediction.get("estimated_maintenance_time_hours", 2),
                "parts_needed": prediction.get("parts_needed", []),
                "technician_assigned": "Auto-assigned",
                "status": "Scheduled"
            }
            
        except Exception as e:
            print(f"Error menjadwalkan maintenance: {str(e)}")
            return None
    return None

# Fungsi untuk menghasilkan data maintenance terakhir
def generate_last_maintenance_data():
    now = datetime.now()
    last_maintenance = {}
    
    for equipment in airport_equipment:
        # Acak tanggal maintenance terakhir antara 10-90 hari yang lalu
        days_ago = random.randint(10, 90)
        maintenance_date = (now - timedelta(days=days_ago)).strftime("%Y-%m-%d")
        
        last_maintenance[equipment["id"]] = {
            "date": maintenance_date,
            "technician": f"Tech-{random.randint(1, 10)}",
            "report": "Routine maintenance performed"
        }
    
    # Simpan ke file
    os.makedirs("storage/app/data", exist_ok=True)
    with open("storage/app/data/last_maintenance.json", 'w') as f:
        json.dump(last_maintenance, f, indent=2)
    
    print(f"Data maintenance terakhir disimpan ke storage/app/data/last_maintenance.json")
    return last_maintenance

# Fungsi untuk menghasilkan data frontend
def generate_frontend_data():
    results = []
    
    try:
        # Generate data maintenance terakhir
        print("Menghasilkan data maintenance terakhir...")
        last_maintenance = generate_last_maintenance_data()
        
        for equipment in airport_equipment:
            print(f"Memproses peralatan {equipment['id']}...")
            
            # Generate sensor data
            sensor_data = generate_sensor_data(equipment)
            
            # Get prediction
            prediction = get_prediction(sensor_data)
            
            # Tambahkan data sensor ke prediksi
            if prediction:
                prediction["sensor_data"] = sensor_data
            
            # Schedule maintenance if needed
            maintenance_schedule = None
            if prediction and prediction.get("maintenance_required", True):
                maintenance_schedule = schedule_maintenance(prediction)
            
            # Calculate condition score (0-100)
            condition_score = 0
            if prediction:
                # Jika tidak perlu maintenance, skor tinggi
                if not prediction.get("maintenance_required", False):
                    condition_score = random.randint(85, 100)
                else:
                    # Berdasarkan urgency level
                    urgency = prediction.get("urgency_level", "medium")
                    if urgency == "low":
                        condition_score = random.randint(70, 84)
                    elif urgency == "medium":
                        condition_score = random.randint(50, 69)
                    elif urgency == "high":
                        condition_score = random.randint(30, 49)
                    else:  # critical
                        condition_score = random.randint(10, 29)
            
            # Get last maintenance date
            last_maint = last_maintenance.get(equipment["id"], {"date": "Unknown", "technician": "Unknown"})
            
            # Determine next maintenance date
            next_maintenance_date = "Tidak diperlukan"
            if maintenance_schedule:
                next_maintenance_date = maintenance_schedule.get("scheduled_date", "Segera")
            
            # Generate recommendation
            recommendation = "Tidak ada tindakan yang diperlukan"
            if prediction and prediction.get("maintenance_required", False):
                if "recommended_action" in prediction:
                    recommendation = prediction["recommended_action"]
                else:
                    urgency = prediction.get("urgency_level", "medium")
                    if urgency == "critical":
                        recommendation = "Segera lakukan pemeriksaan dan perbaikan"
                    elif urgency == "high":
                        recommendation = "Jadwalkan pemeriksaan dalam 24 jam"
                    elif urgency == "medium":
                        recommendation = "Jadwalkan pemeriksaan dalam minggu ini"
                    else:
                        recommendation = "Pantau kondisi dan jadwalkan pemeriksaan rutin"
            
            # Create result object
            result = {
                "id": equipment["id"],
                "name": equipment["name"],
                "location": equipment["location"],
                "last_maintenance": last_maint["date"],
                "next_maintenance": next_maintenance_date,
                "condition_score": condition_score,
                "recommendation": recommendation,
                "sensor_data": sensor_data,
                "prediction": prediction
            }
            
            results.append(result)
        
        # Simpan hasil ke file JSON untuk frontend
        os.makedirs("storage/app/data", exist_ok=True)
        frontend_data_path = "storage/app/data/frontend_data.json"
        with open(frontend_data_path, 'w') as f:
            json.dump(results, f, indent=2)
        
        print(f"Data frontend disimpan ke {frontend_data_path} dengan {len(results)} peralatan")
        
    except Exception as e:
        print(f"Error saat menghasilkan data frontend: {str(e)}")
    
    return results

# Fungsi utama
def main():
    print("Memulai integrasi data peralatan bandara...")
    
    # Simpan data peralatan
    save_equipment_data()
    
    # Generate data untuk frontend
    frontend_data = generate_frontend_data()
    
    print("Integrasi data selesai!")
    print(f"Total peralatan: {len(frontend_data)}")
    
    # Tampilkan ringkasan
    print("\nRingkasan kondisi peralatan:")
    for item in frontend_data:
        maintenance_status = "Perlu maintenance" if item.get("next_maintenance") != "Tidak diperlukan" else "OK"
        print(f"{item['id']} - {item['name']}: Skor {item['condition_score']} - {maintenance_status}")

if __name__ == "__main__":
    main() 