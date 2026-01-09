<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Change Password</title>
  <style>
    body {
      font-family: "Poppins", sans-serif;
      background: linear-gradient(135deg, #667eea, #764ba2);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }

    .card {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
      width: 100%;
      max-width: 400px;
      padding: 40px 30px;
      box-sizing: border-box;
      text-align: center;
    }

    h3 {
      margin-bottom: 25px;
      color: #333;
    }

    input[type="password"] {
      width: 100%;
      padding: 12px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 14px;
      transition: border 0.3s;
    }

    input[type="password"]:focus {
      border-color: #667eea;
      outline: none;
    }

    button {
      width: 100%;
      padding: 12px;
      background-color: #667eea;
      border: none;
      border-radius: 8px;
      color: #fff;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      margin-top: 10px;
      transition: 0.3s;
    }

    button:hover {
      background-color: #5a67d8;
    }

    p {
      margin-top: 15px;
      font-size: 14px;
    }

    .success {
      color: green;
    }

    .error {
      color: red;
    }
  </style>
</head>
<body>

  <div class="card">
    <form action="{{ route('change.password.update') }}" method="POST">
      @csrf
      <input type="hidden" name="email" value="{{ $email }}">

      <h3>🔒 Change Your Password</h3>

      <input type="password" name="password" placeholder="Enter New Password" required>
      <input type="password" name="password_confirmation" placeholder="Confirm New Password" required>

      <button type="submit">Update Password</button>

      @if(session('status'))
        <p class="success">{{ session('status') }}</p>
      @endif

      @error('password')
        <p class="error">{{ $message }}</p>
      @enderror
    </form>
  </div>

</body>
</html>
