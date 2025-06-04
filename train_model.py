import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
import joblib

# Load dataset
data = pd.read_csv('storage/app/data/maintenance_data.csv')

# Pisahkan fitur (X) dan target (y)
X = data[['vibration', 'temperature', 'pressure', 'humidity']]
y = data['maintenance_required']

# Bagi data menjadi training dan testing set
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# Buat dan train model
model = RandomForestClassifier()
model.fit(X_train, y_train)

# Simpan model
joblib.dump(model, 'storage/app/python/maintenance_model.pkl')
print("Model trained and saved!")
