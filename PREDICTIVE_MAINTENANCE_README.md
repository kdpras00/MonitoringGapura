# Sistem Predictive Maintenance dengan Gemini API

Sistem ini menggabungkan model machine learning tradisional dengan kemampuan AI generatif dari Google Gemini untuk memberikan prediksi maintenance yang lebih akurat dan terperinci.

## Fitur Utama

1. **Hybrid Prediction**: Menggabungkan hasil prediksi dari model ML tradisional dengan analisis kontekstual dari Gemini AI
2. **Penjadwalan Otomatis**: Menjadwalkan maintenance secara otomatis berdasarkan prediksi
3. **Monitoring Terjadwal**: Menjalankan prediksi secara berkala untuk semua peralatan
4. **API Endpoint**: Menyediakan endpoint API untuk integrasi dengan sistem lain
5. **Logging Komprehensif**: Menyimpan semua hasil prediksi dan jadwal maintenance

## Cara Kerja

Sistem ini bekerja dengan alur sebagai berikut:

1. Mengumpulkan data sensor dari peralatan (vibrasi, suhu, tekanan, kelembaban)
2. Melakukan prediksi awal menggunakan model machine learning tradisional
3. Mengirim data dan hasil prediksi awal ke Gemini API untuk analisis lebih mendalam
4. Menggabungkan kedua hasil untuk membuat keputusan final
5. Jika maintenance diperlukan, sistem akan menjadwalkan secara otomatis berdasarkan tingkat urgensi

## Struktur File

- `gemini_maintenance.py`: Implementasi utama integrasi Gemini dengan model ML
- `gemini_api.py`: API Flask untuk mengakses fungsi predictive maintenance
- `scheduled_maintenance.py`: Script untuk menjalankan prediksi secara terjadwal
- `requirements.txt`: Daftar dependensi Python

## Cara Penggunaan

### Instalasi

1. Install semua dependensi:
   ```
   pip install -r requirements.txt
   ```

2. Pastikan model ML sudah tersedia di `storage/app/python/maintenance_model.pkl`

### Menjalankan API

```bash
python gemini_api.py
```

API akan berjalan di http://localhost:5001 dengan endpoint berikut:
- `/api/predict` - Prediksi menggunakan model ML saja
- `/api/gemini-predict` - Prediksi menggunakan Gemini API
- `/api/hybrid-predict` - Prediksi hybrid (gabungan ML dan Gemini)
- `/api/schedule-maintenance` - Menjadwalkan maintenance

### Menjalankan Scheduler

```bash
python scheduled_maintenance.py
```

Scheduler akan menjalankan prediksi untuk semua peralatan setiap jam dan menjadwalkan maintenance jika diperlukan.

### Contoh Request API

```bash
curl -X POST http://localhost:5001/api/hybrid-predict \
  -H "Content-Type: application/json" \
  -d '{
    "equipment_id": "PUMP-101",
    "vibration": 5.8,
    "temperature": 82,
    "pressure": 118,
    "humidity": 60
  }'
```

## Konfigurasi Gemini API

API key Gemini sudah dikonfigurasi dalam kode. Jika ingin mengubah API key:

1. Edit file `gemini_maintenance.py`
2. Ubah nilai `GEMINI_API_KEY`

## Pengembangan Lebih Lanjut

Beberapa ide untuk pengembangan lebih lanjut:

1. Integrasi dengan sistem notifikasi (email, SMS, Slack)
2. Dashboard web untuk melihat status peralatan dan jadwal maintenance
3. Finetune model ML berdasarkan feedback dari hasil maintenance aktual
4. Tambahkan lebih banyak parameter sensor untuk prediksi yang lebih akurat
5. Implementasi sistem feedback loop untuk meningkatkan akurasi model 