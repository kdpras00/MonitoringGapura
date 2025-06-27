<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Gapura</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            height: 100vh;
            justify-content: center;
            align-items: center;
            background-image: url('{{ asset('img/gapura-background.jpg') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .login-container {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 400px;
        }

        .login-container img {
            width: 100px;
            margin-bottom: 10px;
        }

        .sign-in-text {
            font-size: 24px;
            font-weight: 500;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            font-size: 14px;
        }
        
        input[type="email"],
        input[type="password"] {
            width: calc(100% - 20px);
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            margin: 15px 0;
            text-align: left;
        }
        
        .remember-me input {
            margin-right: 8px;
        }
        
        button {
            width: 100%;
            padding: 10px;
            background: #FF7A00;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            font-size: 16px;
        }
        
        .password-field {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
        }

        .error-message {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
            text-align: left;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <img src="https://gapura.id/assets/uploads/media-uploader/gapuralogo-fullcolour-cmyk-copy11647292698.PNG"
            alt="Gapura Logo">
        <div class="sign-in-text">Sign in</div>

        @if (session()->has('error'))
            <div class="error-message">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('filament.auth.attempt') }}">
            @csrf
            <div class="form-group">
                <label for="email">Email address*</label>
                <input type="email" id="email" name="email" required>
                @error('email')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="password">Password*</label>
                <div class="password-field">
                    <input type="password" id="password" name="password" required>
                    <span class="password-toggle" onclick="togglePasswordVisibility()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </span>
                </div>
                @error('password')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="remember-me">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Remember me</label>
            </div>
            
            <button type="submit">Sign in</button>
        </form>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
            } else {
                passwordInput.type = 'password';
            }
        }
    </script>
</body>

</html>
