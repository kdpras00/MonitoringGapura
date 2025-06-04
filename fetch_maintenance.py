import requests
import os
from dotenv import load_dotenv

# Memuat variabel dari .env
load_dotenv()

# Ambil API key dari environment variable
api_key = os.getenv('ERP_API_KEY')

# Pastikan API key tersedia
if not api_key:
    raise ValueError("ERP_API_KEY tidak ditemukan di .env")

# Kirim permintaan ke API Laravel
url = 'http://127.0.0.1:8000/api/maintenance-data'
headers = {'Authorization': f'Bearer {api_key}'}

response = requests.get(url, headers=headers)

# Periksa status response
if response.status_code == 200:
    data = response.json()
    for task in data:
        print(f"Equipment: {task['equipment']}, Technician: {task['technician']}")
else:
    print(f"Error {response.status_code}: {response.text}")
