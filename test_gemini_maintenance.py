import json
import requests
from gemini_maintenance import (
    get_sensor_data,
    predict_maintenance,
    get_gemini_recommendation,
    make_maintenance_decision,
    run_predictive_maintenance
)

def test_local_prediction():
    """
    Pengujian prediksi lokal menggunakan model ML
    """
    print("\n===== TEST LOCAL PREDICTION =====")
    
    # Dapatkan data sensor
    sensor_data = get_sensor_data()
    print(f"Sensor data: {json.dumps(sensor_data, indent=2)}")
    
    # Lakukan prediksi dengan model ML
    ml_prediction = predict_maintenance(sensor_data)
    print(f"ML Prediction: {json.dumps(ml_prediction, indent=2)}")
    
    return ml_prediction

def test_gemini_recommendation(sensor_data, ml_prediction):
    """
    Pengujian rekomendasi dari Gemini API
    """
    print("\n===== TEST GEMINI RECOMMENDATION =====")
    
    # Dapatkan rekomendasi dari Gemini
    gemini_recommendation = get_gemini_recommendation(sensor_data, ml_prediction)
    print(f"Gemini Recommendation: {json.dumps(gemini_recommendation, indent=2)}")
    
    return gemini_recommendation

def test_hybrid_decision(ml_prediction, gemini_recommendation):
    """
    Pengujian keputusan hybrid
    """
    print("\n===== TEST HYBRID DECISION =====")
    
    # Buat keputusan final
    final_decision = make_maintenance_decision(ml_prediction, gemini_recommendation)
    print(f"Final Decision: {json.dumps(final_decision, indent=2)}")
    
    return final_decision

def test_api_endpoints():
    """
    Pengujian endpoint API
    """
    print("\n===== TEST API ENDPOINTS =====")
    
    # Data untuk pengujian
    test_data = {
        "equipment_id": "TEST-EQUIPMENT",
        "vibration": 6.2,
        "temperature": 88,
        "pressure": 125,
        "humidity": 65
    }
    
    # Coba akses API
    try:
        # Test endpoint ML prediction
        print("\nTesting /api/predict endpoint...")
        ml_response = requests.post("http://localhost:5001/api/predict", json=test_data)
        if ml_response.status_code == 200:
            print(f"ML Prediction Response: {json.dumps(ml_response.json(), indent=2)}")
        else:
            print(f"Error: {ml_response.status_code} - {ml_response.text}")
        
        # Test endpoint Gemini prediction
        print("\nTesting /api/gemini-predict endpoint...")
        gemini_response = requests.post("http://localhost:5001/api/gemini-predict", json=test_data)
        if gemini_response.status_code == 200:
            print(f"Gemini Prediction Response: {json.dumps(gemini_response.json(), indent=2)}")
        else:
            print(f"Error: {gemini_response.status_code} - {gemini_response.text}")
        
        # Test endpoint hybrid prediction
        print("\nTesting /api/hybrid-predict endpoint...")
        hybrid_response = requests.post("http://localhost:5001/api/hybrid-predict", json=test_data)
        if hybrid_response.status_code == 200:
            print(f"Hybrid Prediction Response: {json.dumps(hybrid_response.json(), indent=2)}")
            
            # Test endpoint schedule maintenance
            if hybrid_response.json().get("maintenance_required", False):
                print("\nTesting /api/schedule-maintenance endpoint...")
                schedule_data = {
                    "equipment_id": test_data["equipment_id"],
                    "maintenance_required": hybrid_response.json()["maintenance_required"],
                    "urgency_level": hybrid_response.json().get("urgency_level", "medium")
                }
                schedule_response = requests.post("http://localhost:5001/api/schedule-maintenance", json=schedule_data)
                if schedule_response.status_code == 200:
                    print(f"Schedule Response: {json.dumps(schedule_response.json(), indent=2)}")
                else:
                    print(f"Error: {schedule_response.status_code} - {schedule_response.text}")
        else:
            print(f"Error: {hybrid_response.status_code} - {hybrid_response.text}")
    
    except Exception as e:
        print(f"API test failed: {str(e)}")
        print("Make sure the API server is running (python gemini_api.py)")

def run_all_tests():
    """
    Menjalankan semua pengujian
    """
    print("===== RUNNING ALL TESTS =====")
    
    # Test 1: Prediksi lokal
    sensor_data = get_sensor_data()
    ml_prediction = test_local_prediction()
    
    # Test 2: Rekomendasi Gemini
    if "error" not in ml_prediction:
        gemini_recommendation = test_gemini_recommendation(sensor_data, ml_prediction)
        
        # Test 3: Keputusan hybrid
        if "error" not in gemini_recommendation:
            test_hybrid_decision(ml_prediction, gemini_recommendation)
    
    # Test 4: Endpoint API
    test_api_endpoints()
    
    print("\n===== ALL TESTS COMPLETED =====")

if __name__ == "__main__":
    # Jalankan semua pengujian
    run_all_tests()
    
    # Atau jalankan hanya full predictive maintenance
    # print("\n===== RUNNING FULL PREDICTIVE MAINTENANCE =====")
    # run_predictive_maintenance() 