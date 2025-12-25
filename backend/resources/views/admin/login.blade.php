<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - QR Generator</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--primary-gradient);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .login-card {
            background: white;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .login-header h1 {
            font-size: 1.5rem;
            color: #2D3436;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: #636E72;
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }

        .input-group {
            margin-bottom: 1.5rem;
            position: relative;
            text-align: left;
        }

        .input-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #636E72;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #B2BEC3;
        }

        input {
            width: 100%;
            padding: 0.8rem 1rem 0.8rem 2.5rem;
            border: 2px solid #DFE6E9;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s;
            box-sizing: border-box;
        }

        input:focus {
            outline: none;
            border-color: #667eea;
        }

        .login-btn {
            width: 100%;
            padding: 0.8rem;
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .login-btn:hover {
            transform: translateY(-2px);
        }

        .error-msg {
            background: #ffecec;
            color: #ff6b6b;
            padding: 0.75rem;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
            text-align: left;
        }
    </style>
</head>

<body>
    <div class="login-card">
        <div class="login-header">
            <h1><i class="fa-solid fa-lock"></i> Admin Login</h1>
            <p>QR Generator Admin Panel</p>
        </div>

        @if ($errors->any())
            <div class="error-msg">
                <ul style="margin: 0; padding-left: 1.2rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.login') }}" method="POST">
            @csrf

            <div class="input-group">
                <label>Email Address</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" name="email" required value="{{ old('email') }}"
                        placeholder="admin@example.com">
                </div>
            </div>

            <div class="input-group">
                <label>Password</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" required placeholder="••••••••">
                </div>
            </div>

            <div style="text-align: left; margin-bottom: 1.5rem;">
                <label style="display: flex; align-items: center; gap: 0.5rem; color: #636E72; font-size: 0.9rem;">
                    <input type="checkbox" name="remember" style="width: auto; margin: 0;"> Remember me
                </label>
            </div>

            <button type="submit" class="login-btn">Login</button>
        </form>
    </div>
</body>

</html>