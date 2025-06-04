@app.route('/predict', methods=['POST'])
def predict():
    # Ambil data dari request
    data = request.json
    
    # Pastikan format input sesuai dengan model
    feature_names = ['vibration', 'temperature', 'pressure', 'humidity']
    input_data = pd.DataFrame([data], columns=feature_names)

    # Lakukan prediksi
    prediction = model.predict(input_data)
    return jsonify({'prediction': int(prediction[0])})
