import joblib
import os

# Path model
model_path = os.path.abspath('storage/app/python/maintenance_model.pkl')

# Cek apakah file ada sebelum dibuka
if not os.path.exists(model_path):
    print("File model tidak ditemukan!")
else:
    # Load model
    model = joblib.load(model_path)
    print("Model Loaded Successfully!")
    print(model)
