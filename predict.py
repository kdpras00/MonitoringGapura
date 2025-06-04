import warnings
warnings.filterwarnings('ignore')  # Menghilangkan peringatan dari sklearn

import joblib
import pandas as pd

# Load model
model = joblib.load('storage/app/python/maintenance_model.pkl')

# Data untuk prediksi
feature_names = ['vibration', 'temperature', 'pressure', 'humidity']
input_data = pd.DataFrame([[5.2, 78, 115, 52]], columns=feature_names)

# Lakukan prediksi
prediction = model.predict(input_data)
print("Prediction Result:", prediction[0])
