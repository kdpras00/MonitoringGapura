from flask import Flask, request, jsonify
import os
import pandas as pd
from datetime import datetime, timedelta
import json
from gemini_maintenance import predict_maintenance, get_gemini_recommendation, make_maintenance_decision

app = Flask(__name__)

@app.route('/health', methods=['GET'])
def health_check():
    return jsonify({"status": "ok", "timestamp": datetime.now().isoformat()})

@app.route('/api/predict', methods=['POST'])
def predict():
    """
    Endpoint untuk melakukan prediksi maintenance dengan model ML yang sudah ada
    """
    if request.method == 'POST':
        try:
            # Ambil data dari request
            data = request.get_json()
            
            # Validasi input
            required_fields = ["vibration", "temperature", "pressure", "humidity"]
            for field in required_fields:
                if field not in data:
                    return jsonify({"error": f"Field {field} tidak ditemukan dalam request"}), 400
            
            # Tambahkan equipment_id dan timestamp jika tidak ada
            if "equipment_id" not in data:
                data["equipment_id"] = "UNKNOWN"
            if "timestamp" not in data:
                data["timestamp"] = datetime.now().isoformat()
            
            # Lakukan prediksi dengan model ML
            ml_prediction = predict_maintenance(data)
            
            return jsonify(ml_prediction)
        
        except Exception as e:
            return jsonify({"error": str(e)}), 500

@app.route('/api/gemini-predict', methods=['POST'])
def gemini_predict():
    """
    Endpoint untuk mendapatkan rekomendasi maintenance dari Gemini API
    """
    if request.method == 'POST':
        try:
            # Ambil data dari request
            data = request.get_json()
            
            # Validasi input
            required_fields = ["vibration", "temperature", "pressure", "humidity"]
            for field in required_fields:
                if field not in data:
                    return jsonify({"error": f"Field {field} tidak ditemukan dalam request"}), 400
            
            # Tambahkan equipment_id dan timestamp jika tidak ada
            if "equipment_id" not in data:
                data["equipment_id"] = "UNKNOWN"
            if "timestamp" not in data:
                data["timestamp"] = datetime.now().isoformat()
            
            # Lakukan prediksi dengan model ML terlebih dahulu
            ml_prediction = predict_maintenance(data)
            
            # Dapatkan rekomendasi dari Gemini
            gemini_recommendation = get_gemini_recommendation(data, ml_prediction)
            
            return jsonify(gemini_recommendation)
        
        except Exception as e:
            return jsonify({"error": str(e)}), 500

@app.route('/api/hybrid-predict', methods=['POST'])
def hybrid_predict():
    """
    Endpoint untuk mendapatkan rekomendasi maintenance dengan metode hybrid
    (kombinasi model ML dan Gemini API)
    """
    if request.method == 'POST':
        try:
            # Ambil data dari request
            data = request.get_json()
            
            # Validasi input
            required_fields = ["vibration", "temperature", "pressure", "humidity"]
            for field in required_fields:
                if field not in data:
                    return jsonify({"error": f"Field {field} tidak ditemukan dalam request"}), 400
            
            # Tambahkan equipment_id dan timestamp jika tidak ada
            if "equipment_id" not in data:
                data["equipment_id"] = "UNKNOWN"
            if "timestamp" not in data:
                data["timestamp"] = datetime.now().isoformat()
            
            # 1. Lakukan prediksi dengan model ML
            ml_prediction = predict_maintenance(data)
            
            # 2. Dapatkan rekomendasi dari Gemini
            gemini_recommendation = get_gemini_recommendation(data, ml_prediction)
            
            # 3. Buat keputusan final
            final_decision = make_maintenance_decision(ml_prediction, gemini_recommendation)
            
            # 4. Tambahkan informasi sensor ke hasil
            final_decision["sensor_data"] = {
                "vibration": data["vibration"],
                "temperature": data["temperature"],
                "pressure": data["pressure"],
                "humidity": data["humidity"],
                "equipment_id": data["equipment_id"],
                "timestamp": data["timestamp"]
            }
            
            return jsonify(final_decision)
        
        except Exception as e:
            return jsonify({"error": str(e)}), 500

@app.route('/api/schedule-maintenance', methods=['POST'])
def schedule_maintenance():
    """
    Endpoint untuk menjadwalkan maintenance berdasarkan rekomendasi
    """
    if request.method == 'POST':
        try:
            # Ambil data dari request
            data = request.get_json()
            
            # Validasi input
            required_fields = ["equipment_id", "maintenance_required", "urgency_level"]
            for field in required_fields:
                if field not in data:
                    return jsonify({"error": f"Field {field} tidak ditemukan dalam request"}), 400
            
            # Di sini akan ada kode untuk menjadwalkan maintenance ke sistem ERP atau CMMS
            # Untuk contoh, kita hanya mengembalikan jadwal dummy
            
            # Tentukan tanggal maintenance berdasarkan urgency
            today = datetime.now()
            scheduled_date = None
            
            if data["urgency_level"] == "critical":
                scheduled_date = today.strftime("%Y-%m-%d")  # Hari ini
            elif data["urgency_level"] == "high":
                scheduled_date = (today + timedelta(days=1)).strftime("%Y-%m-%d")  # Besok
            elif data["urgency_level"] == "medium":
                scheduled_date = (today + timedelta(days=3)).strftime("%Y-%m-%d")  # 3 hari lagi
            else:  # low
                scheduled_date = (today + timedelta(days=7)).strftime("%Y-%m-%d")  # 7 hari lagi
            
            response = {
                "equipment_id": data["equipment_id"],
                "maintenance_scheduled": data["maintenance_required"],
                "scheduled_date": scheduled_date,
                "urgency_level": data["urgency_level"],
                "estimated_time": data.get("estimated_maintenance_time_hours", 2),
                "parts_needed": data.get("parts_needed", []),
                "technician_assigned": "Auto-assigned",
                "status": "Scheduled"
            }
            
            return jsonify(response)
        
        except Exception as e:
            return jsonify({"error": str(e)}), 500

if __name__ == '__main__':
    app.run(port=5001, debug=True) 