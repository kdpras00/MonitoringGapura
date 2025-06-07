import os
import json
import pandas as pd
import joblib
import requests
from datetime import datetime, timedelta
import google.generativeai as genai

# Konfigurasi API Gemini
GEMINI_API_KEY = "AIzaSyC-if6ei1E11uPPPs2JOAmfquSXCHPMtCo"
genai.configure(api_key=GEMINI_API_KEY)

# Load model predictive maintenance yang sudah ada
model_path = 'storage/app/python/maintenance_model.pkl'
try:
    model = joblib.load(model_path)
    print("Model predictive maintenance berhasil dimuat")
except Exception as e:
    print(f"Error memuat model: {str(e)}")
    model = None

# Fungsi untuk mendapatkan data sensor terbaru
def get_sensor_data():
    # Dalam implementasi nyata, ini akan mengambil data dari sensor atau database
    # Untuk contoh, kita gunakan data dummy
    return {
        'equipment_id': 'PUMP-101',
        'vibration': 5.8,
        'temperature': 82,
        'pressure': 118,
        'humidity': 60,
        'timestamp': datetime.now().isoformat()
    }

# Fungsi untuk melakukan prediksi dengan model ML yang sudah ada
def predict_maintenance(data):
    if not model:
        return {"error": "Model tidak tersedia"}
    
    features = ['vibration', 'temperature', 'pressure', 'humidity']
    input_data = pd.DataFrame([[data[f] for f in features]], columns=features)
    
    try:
        prediction = model.predict(input_data)
        probability = model.predict_proba(input_data).max()
        return {
            "maintenance_required": bool(prediction[0]),
            "confidence": float(probability),
            "method": "machine_learning"
        }
    except Exception as e:
        return {"error": f"Prediksi gagal: {str(e)}"}

# Fungsi untuk mendapatkan rekomendasi dari Gemini
def get_gemini_recommendation(data, ml_prediction):
    # Membuat prompt untuk Gemini
    equipment_history = """
    Equipment ID: PUMP-101
    Last maintenance: 45 days ago
    Previous failures: Bearing failure (90 days ago), Seal leak (180 days ago)
    Operating hours since last maintenance: 1080 hours
    """
    
    prompt = f"""
    Sebagai sistem predictive maintenance, analisis data sensor berikut dan berikan rekomendasi:
    
    Data sensor saat ini:
    - Vibration: {data['vibration']} mm/s
    - Temperature: {data['temperature']}°C
    - Pressure: {data['pressure']} PSI
    - Humidity: {data['humidity']}%
    - Timestamp: {data['timestamp']}
    
    Informasi peralatan:
    {equipment_history}
    
    Model machine learning memprediksi maintenance diperlukan: {ml_prediction['maintenance_required']}
    Confidence level: {ml_prediction['confidence']:.2f}
    
    Berikan analisis dan rekomendasi dalam format JSON dengan struktur berikut:
    {
        "maintenance_required": true/false,
        "urgency_level": "low"/"medium"/"high"/"critical",
        "recommended_action": "string",
        "potential_issues": ["issue1", "issue2"],
        "estimated_maintenance_time_hours": number,
        "parts_needed": ["part1", "part2"],
        "justification": "string"
    }
    
    Hanya berikan output dalam format JSON, tanpa penjelasan tambahan.
    """
    
    try:
        model = genai.GenerativeModel('gemini-1.5-flash')
        response = model.generate_content(prompt)
        
        # Parse response sebagai JSON
        result = json.loads(response.text)
        result["method"] = "gemini_ai"
        return result
    except Exception as e:
        return {"error": f"Gemini API error: {str(e)}"}

# Fungsi untuk menggabungkan hasil prediksi dan membuat keputusan final
def make_maintenance_decision(ml_result, gemini_result):
    # Jika salah satu metode gagal, gunakan hasil dari metode yang berhasil
    if "error" in ml_result:
        return gemini_result
    if "error" in gemini_result:
        return ml_result
    
    # Jika keduanya berhasil, buat keputusan berdasarkan kedua hasil
    maintenance_required = ml_result["maintenance_required"] or gemini_result["maintenance_required"]
    
    # Jika Gemini mendeteksi urgensi tinggi, prioritaskan rekomendasi Gemini
    if gemini_result.get("urgency_level") in ["high", "critical"]:
        decision = gemini_result
    # Jika ML confidence tinggi (>0.8) dan bertentangan dengan Gemini, prioritaskan ML
    elif ml_result["confidence"] > 0.8 and ml_result["maintenance_required"] != gemini_result["maintenance_required"]:
        decision = ml_result
        decision["justification"] = "High confidence ML prediction"
    # Jika keduanya setuju, gunakan detail dari Gemini dengan confidence dari ML
    else:
        decision = gemini_result
        decision["ml_confidence"] = ml_result["confidence"]
    
    # Tambahkan informasi gabungan
    decision["maintenance_required"] = maintenance_required
    decision["decision_method"] = "hybrid"
    
    return decision

# Fungsi utama untuk menjalankan proses predictive maintenance
def run_predictive_maintenance():
    # 1. Dapatkan data sensor terbaru
    sensor_data = get_sensor_data()
    print(f"Data sensor diterima untuk {sensor_data['equipment_id']}")
    
    # 2. Prediksi dengan model ML
    ml_prediction = predict_maintenance(sensor_data)
    print("Prediksi ML selesai")
    
    # 3. Dapatkan rekomendasi dari Gemini
    gemini_recommendation = get_gemini_recommendation(sensor_data, ml_prediction)
    print("Rekomendasi Gemini diterima")
    
    # 4. Buat keputusan final
    final_decision = make_maintenance_decision(ml_prediction, gemini_recommendation)
    
    # 5. Tampilkan hasil
    print("\n===== HASIL PREDICTIVE MAINTENANCE =====")
    print(f"Equipment ID: {sensor_data['equipment_id']}")
    print(f"Timestamp: {sensor_data['timestamp']}")
    print(f"Maintenance Required: {final_decision['maintenance_required']}")
    
    if "urgency_level" in final_decision:
        print(f"Urgency Level: {final_decision['urgency_level']}")
    
    if "recommended_action" in final_decision:
        print(f"Recommended Action: {final_decision['recommended_action']}")
    
    if "potential_issues" in final_decision:
        print("Potential Issues:")
        for issue in final_decision["potential_issues"]:
            print(f"- {issue}")
    
    print("\nFull decision data:")
    print(json.dumps(final_decision, indent=2))
    
    return final_decision

# Jalankan jika file dieksekusi langsung
if __name__ == "__main__":
    run_predictive_maintenance() 