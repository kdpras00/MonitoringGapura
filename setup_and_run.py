import os
import sys
import subprocess
import time
import threading

def print_colored(text, color):
    """Print colored text"""
    colors = {
        'red': '\033[91m',
        'green': '\033[92m',
        'yellow': '\033[93m',
        'blue': '\033[94m',
        'purple': '\033[95m',
        'cyan': '\033[96m',
        'end': '\033[0m'
    }
    print(f"{colors.get(color, '')}{text}{colors['end']}")

def install_requirements():
    """Install required packages"""
    print_colored("\n[1/4] Installing required packages...", "blue")
    try:
        subprocess.check_call([sys.executable, "-m", "pip", "install", "-r", "requirements.txt"])
        print_colored("✓ Packages installed successfully!", "green")
        return True
    except subprocess.CalledProcessError as e:
        print_colored(f"✗ Error installing packages: {str(e)}", "red")
        return False

def create_directories():
    """Create necessary directories"""
    print_colored("\n[2/4] Creating necessary directories...", "blue")
    directories = [
        "storage/app/python",
        "storage/app/data",
        "storage/app/logs"
    ]
    
    try:
        for directory in directories:
            os.makedirs(directory, exist_ok=True)
        print_colored("✓ Directories created successfully!", "green")
        return True
    except Exception as e:
        print_colored(f"✗ Error creating directories: {str(e)}", "red")
        return False

def create_dummy_model():
    """Create a dummy model if not exists"""
    print_colored("\n[3/4] Creating dummy model if not exists...", "blue")
    model_path = "storage/app/python/maintenance_model.pkl"
    
    if os.path.exists(model_path):
        print_colored("✓ Model already exists!", "green")
        return True
    
    try:
        # Create a simple RandomForest model
        import pandas as pd
        import numpy as np
        from sklearn.ensemble import RandomForestClassifier
        import joblib
        
        # Generate dummy data
        np.random.seed(42)
        n_samples = 1000
        
        # Generate features with some correlation to the target
        vibration = np.random.normal(5, 1, n_samples)
        temperature = np.random.normal(80, 10, n_samples)
        pressure = np.random.normal(120, 15, n_samples)
        humidity = np.random.normal(60, 10, n_samples)
        
        # Create target: maintenance required when vibration > 6 or temperature > 90
        maintenance_required = ((vibration > 6) | (temperature > 90)).astype(int)
        
        # Create DataFrame
        data = pd.DataFrame({
            'vibration': vibration,
            'temperature': temperature,
            'pressure': pressure,
            'humidity': humidity,
            'maintenance_required': maintenance_required
        })
        
        # Save data
        data_path = "storage/app/data/maintenance_data.csv"
        data.to_csv(data_path, index=False)
        
        # Train a simple model
        X = data[['vibration', 'temperature', 'pressure', 'humidity']]
        y = data['maintenance_required']
        
        model = RandomForestClassifier(n_estimators=100, random_state=42)
        model.fit(X, y)
        
        # Save model
        joblib.dump(model, model_path)
        
        print_colored(f"✓ Dummy model created and saved to {model_path}", "green")
        print_colored(f"✓ Dummy data saved to {data_path}", "green")
        return True
    except Exception as e:
        print_colored(f"✗ Error creating dummy model: {str(e)}", "red")
        return False

def run_api_server():
    """Run the API server"""
    print_colored("\nStarting API server...", "cyan")
    try:
        process = subprocess.Popen([sys.executable, "gemini_api.py"])
        print_colored("API server started on http://localhost:5001", "green")
        return process
    except Exception as e:
        print_colored(f"✗ Error starting API server: {str(e)}", "red")
        return None

def run_scheduler():
    """Run the maintenance scheduler"""
    print_colored("\nStarting maintenance scheduler...", "cyan")
    try:
        process = subprocess.Popen([sys.executable, "scheduled_maintenance.py"])
        print_colored("Maintenance scheduler started", "green")
        return process
    except Exception as e:
        print_colored(f"✗ Error starting maintenance scheduler: {str(e)}", "red")
        return None

def run_test():
    """Run the test script"""
    print_colored("\nRunning tests...", "cyan")
    time.sleep(2)  # Give API server time to start
    try:
        subprocess.call([sys.executable, "test_gemini_maintenance.py"])
        print_colored("Tests completed", "green")
    except Exception as e:
        print_colored(f"✗ Error running tests: {str(e)}", "red")

def main():
    """Main function"""
    print_colored("===== PREDICTIVE MAINTENANCE SYSTEM SETUP =====", "purple")
    
    # Install requirements
    if not install_requirements():
        print_colored("Setup failed at package installation step.", "red")
        return
    
    # Create directories
    if not create_directories():
        print_colored("Setup failed at directory creation step.", "red")
        return
    
    # Create dummy model
    if not create_dummy_model():
        print_colored("Setup failed at model creation step.", "red")
        return
    
    print_colored("\n[4/4] Starting services...", "blue")
    
    # Run API server
    api_process = run_api_server()
    if not api_process:
        print_colored("Setup failed at API server startup.", "red")
        return
    
    # Run scheduler in a separate thread
    scheduler_process = run_scheduler()
    if not scheduler_process:
        print_colored("Setup failed at scheduler startup.", "red")
        api_process.terminate()
        return
    
    # Run tests
    run_test_thread = threading.Thread(target=run_test)
    run_test_thread.daemon = True
    run_test_thread.start()
    
    print_colored("\n===== SETUP COMPLETED SUCCESSFULLY =====", "purple")
    print_colored("\nPress Ctrl+C to stop all services...", "yellow")
    
    try:
        while True:
            time.sleep(1)
    except KeyboardInterrupt:
        print_colored("\nStopping services...", "yellow")
        api_process.terminate()
        scheduler_process.terminate()
        print_colored("Services stopped.", "green")

if __name__ == "__main__":
    main() 