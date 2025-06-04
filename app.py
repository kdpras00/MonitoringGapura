import os
import joblib
import pandas as pd
from flask import Flask, request, jsonify

app = Flask(__name__)

# Tentukan path absolut ke model
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
MODEL_PATH = os.path.join(BASE_DIR, 'storage', 'app', 'python', 'maintenance_model.pkl')

# Load model dengan pengecekan error
model = None
if os.path.exists(MODEL_PATH):
    try:
        model = joblib.load(MODEL_PATH)
        print("Model berhasil dimuat.")
    except Exception as e:
        print(f"Error saat memuat model: {str(e)}")
else:
    print(f"Error: Model tidak ditemukan di {MODEL_PATH}")

@app.route('/predict', methods=['GET', 'POST'])
def predict():
    if request.method == 'GET':
        return jsonify({
            "message": "Gunakan metode POST dengan data JSON untuk melakukan prediksi.",
            "example": {
                "vibration": 5.6,
                "temperature": 85,
                "pressure": 120,
                "humidity": 65
            }
        })
    
    if not model:
        return jsonify({"error": "Model tidak ditemukan. Pastikan file model tersedia."}), 500

    # Ambil data dari request
    data = request.get_json()

    # Validasi input
    required_fields = ["vibration", "temperature", "pressure", "humidity"]
    missing_fields = [field for field in required_fields if field not in data]

    if missing_fields:
        return jsonify({"error": f"Field berikut harus ada dalam request JSON: {', '.join(missing_fields)}"}), 400

    input_data = pd.DataFrame([data])

    # Lakukan prediksi
    try:
        prediction = model.predict(input_data)
        return jsonify({'prediction': int(prediction[0])})
    except Exception as e:
        return jsonify({"error": f"Terjadi kesalahan dalam prediksi: {str(e)}"}), 500

if __name__ == '__main__':
    app.run(port=5000, debug=True)
