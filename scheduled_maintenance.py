import schedule
import time
import json
import requests
import pandas as pd
import os
from datetime import datetime
import logging
from gemini_maintenance import run_predictive_maintenance

# Konfigurasi logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler("maintenance_scheduler.log"),
        logging.StreamHandler()
    ]
)
logger = logging.getLogger("maintenance_scheduler")

# Konfigurasi
API_URL = "http://localhost:5001"
DATABASE_PATH = "storage/app/data/equipment_list.json"

# Fungsi untuk memuat daftar peralatan
def load_equipment_list():
    try:
        if os.path.exists(DATABASE_PATH):
            with open(DATABASE_PATH, 'r') as f:
                return json.load(f)
        else:
            # Jika file tidak ada, gunakan data dummy
            dummy_data = [
                {"id": "PUMP-101", "name": "Water Pump 1", "location": "Building A"},
                {"id": "PUMP-102", "name": "Water Pump 2", "location": "Building B"},
                {"id": "COMP-101", "name": "Air Compressor 1", "location": "Building A"},
                {"id": "HVAC-101", "name": "HVAC System 1", "location": "Building C"}
            ]
            # Simpan data dummy untuk penggunaan selanjutnya
            with open(DATABASE_PATH, 'w') as f:
                json.dump(dummy_data, f, indent=2)
            return dummy_data
    except Exception as e:
        logger.error(f"Error loading equipment list: {str(e)}")
        return []

# Fungsi untuk mendapatkan data sensor dari peralatan
def get_sensor_data(equipment_id):
    # Dalam implementasi nyata, ini akan mengambil data dari sensor fisik
    # atau dari database time-series
    
    # Untuk contoh, kita gunakan data dummy dengan variasi kecil
    import random
    
    base_values = {
        "PUMP-101": {"vibration": 5.8, "temperature": 82, "pressure": 118, "humidity": 60},
        "PUMP-102": {"vibration": 4.2, "temperature": 75, "pressure": 110, "humidity": 55},
        "COMP-101": {"vibration": 6.5, "temperature": 95, "pressure": 150, "humidity": 40},
        "HVAC-101": {"vibration": 3.2, "temperature": 65, "pressure": 90, "humidity": 70}
    }
    
    # Jika equipment_id tidak ada dalam data dasar, gunakan nilai default
    base = base_values.get(equipment_id, {"vibration": 5.0, "temperature": 80, "pressure": 115, "humidity": 60})
    
    # Tambahkan variasi kecil untuk mensimulasikan perubahan kondisi
    data = {
        "equipment_id": equipment_id,
        "vibration": round(base["vibration"] + random.uniform(-0.5, 0.5), 2),
        "temperature": round(base["temperature"] + random.uniform(-3, 3), 1),
        "pressure": round(base["pressure"] + random.uniform(-5, 5), 1),
        "humidity": round(base["humidity"] + random.uniform(-5, 5), 1),
        "timestamp": datetime.now().isoformat()
    }
    
    return data

# Fungsi untuk mengirim data ke API untuk prediksi
def send_to_api(data):
    try:
        response = requests.post(f"{API_URL}/api/hybrid-predict", json=data)
        if response.status_code == 200:
            return response.json()
        else:
            logger.error(f"API error: {response.status_code} - {response.text}")
            return None
    except Exception as e:
        logger.error(f"Error sending data to API: {str(e)}")
        return None

# Fungsi untuk menjadwalkan maintenance jika diperlukan
def schedule_maintenance_if_needed(prediction_result):
    if prediction_result and prediction_result.get("maintenance_required", False):
        try:
            # Kirim permintaan untuk menjadwalkan maintenance
            schedule_data = {
                "equipment_id": prediction_result["sensor_data"]["equipment_id"],
                "maintenance_required": prediction_result["maintenance_required"],
                "urgency_level": prediction_result.get("urgency_level", "medium"),
                "estimated_maintenance_time_hours": prediction_result.get("estimated_maintenance_time_hours", 2),
                "parts_needed": prediction_result.get("parts_needed", []),
                "recommended_action": prediction_result.get("recommended_action", "Inspect equipment")
            }
            
            response = requests.post(f"{API_URL}/api/schedule-maintenance", json=schedule_data)
            
            if response.status_code == 200:
                schedule_result = response.json()
                logger.info(f"Maintenance scheduled for {schedule_result['equipment_id']} on {schedule_result['scheduled_date']}")
                return schedule_result
            else:
                logger.error(f"Error scheduling maintenance: {response.status_code} - {response.text}")
                return None
        except Exception as e:
            logger.error(f"Error scheduling maintenance: {str(e)}")
            return None
    else:
        logger.info(f"No maintenance required for {prediction_result['sensor_data']['equipment_id']}")
        return None

# Fungsi untuk menyimpan hasil prediksi ke log file
def save_prediction_to_log(prediction_result):
    if prediction_result:
        try:
            log_dir = "storage/app/logs"
            os.makedirs(log_dir, exist_ok=True)
            
            log_file = os.path.join(log_dir, "predictions.json")
            
            # Baca log yang sudah ada
            existing_logs = []
            if os.path.exists(log_file):
                with open(log_file, 'r') as f:
                    existing_logs = json.load(f)
            
            # Tambahkan prediksi baru
            existing_logs.append({
                "timestamp": datetime.now().isoformat(),
                "prediction": prediction_result
            })
            
            # Simpan kembali ke file
            with open(log_file, 'w') as f:
                json.dump(existing_logs, f, indent=2)
            
            logger.info(f"Prediction saved to log for {prediction_result['sensor_data']['equipment_id']}")
        except Exception as e:
            logger.error(f"Error saving prediction to log: {str(e)}")

# Fungsi utama untuk menjalankan proses predictive maintenance untuk semua peralatan
def run_scheduled_maintenance():
    logger.info("Starting scheduled maintenance check")
    
    # 1. Muat daftar peralatan
    equipment_list = load_equipment_list()
    logger.info(f"Loaded {len(equipment_list)} equipment items")
    
    for equipment in equipment_list:
        try:
            # 2. Dapatkan data sensor untuk peralatan
            sensor_data = get_sensor_data(equipment["id"])
            logger.info(f"Got sensor data for {equipment['id']}")
            
            # 3. Kirim data ke API untuk prediksi
            prediction_result = send_to_api(sensor_data)
            
            # 4. Jika API tidak tersedia, gunakan fungsi lokal
            if prediction_result is None:
                logger.warning(f"API not available, using local prediction for {equipment['id']}")
                prediction_result = run_predictive_maintenance()
            
            # 5. Jadwalkan maintenance jika diperlukan
            if prediction_result and prediction_result.get("maintenance_required", False):
                schedule_maintenance_if_needed(prediction_result)
            
            # 6. Simpan hasil prediksi ke log
            save_prediction_to_log(prediction_result)
            
        except Exception as e:
            logger.error(f"Error processing equipment {equipment['id']}: {str(e)}")
    
    logger.info("Scheduled maintenance check completed")

# Jadwalkan tugas untuk dijalankan setiap jam
def setup_schedule():
    schedule.every(1).hour.do(run_scheduled_maintenance)
    logger.info("Maintenance check scheduled to run hourly")

# Jalankan sekali saat startup
def run_on_startup():
    logger.info("Running initial maintenance check on startup")
    run_scheduled_maintenance()

# Main function
if __name__ == "__main__":
    logger.info("Starting maintenance scheduler")
    
    # Jalankan sekali saat startup
    run_on_startup()
    
    # Setup jadwal
    setup_schedule()
    
    # Loop untuk menjalankan jadwal
    while True:
        schedule.run_pending()
        time.sleep(60)  # Cek setiap menit 