<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login</title>
  <style>
        /* Fix for SweetAlert2 button override */
        .swal2-container button.swal2-confirm,
        .swal2-container button.swal2-cancel {
          all: unset;
          padding: 10px 22px;
          font-size: 14px;
          background-color: #E85A72;
          color: white;
          border-radius: 6px;
          border: none;
          cursor: pointer;
          text-align: center;
          display: inline-block;
        }
    
    </style>
  <style>
    * {
      box-sizing: border-box;
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      padding: 0;
    }

    body {
     background: url('/02-Login.png');
     no-repeat center center fixed;
     background-size: cover;
     background-color: #FFFFFF;
     min-height: 100vh;
     position: relative;
    }

    .login-wrapper {
      max-width: 360px;
       margin-left: 22rem;
      margin: 0 auto;
      padding: 80px 20px 40px;
      top: 250px;
    }
       

    h1 {
      font-size: 36px;
      font-weight: 700;
      color: #535353;
      margin-bottom: 2rem;
    }

    label {
      font-size: 14px;
      font-weight: 600;
      color: #17215F;
      display: block;
      margin-bottom: 6px;
    }

    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 12px 16px;
      border: none;
      border-radius: 999px;
      background-color: #305ED9;
      color: #FFFFFF;
      font-weight: 500;
      font-size: 14px;
      margin-bottom: 15px;
      
    }

    input::placeholder {
      color: #c9d8f9;
    }

    .forgot-password {
      text-align: right;
      /* margin-top: 10px; */
      margin-bottom:2rem;
    }

    .forgot-password a {
      font-size: 15px;
      color: #17215F;
      font-weight: 500;
      text-decoration: underline;
    }

    .button-div{
        margin-top: 2rem;
        display: flex;
        align-items: center;
        justify-content: end
    }
    button {
      width: 40%;
      padding: 12px;
      background-color: #E85A72;
      color: #FFFFFF;
      border: none;
      border-radius: 999px;
      font-size: 15px;
      cursor: pointer;
    }

   

    /* Decorative circles */
    .circle {
      position: absolute;
      border-radius: 50%;
      z-index: -1;
    }

    .circle-blue-top {
      top: 0;
      right: 0;
      width: 180px;
      height: 180px;
      background-color: #b4e4fa;
    }

    .circle-blue-bottom {
      bottom: -40px;
      left: -40px;
      width: 180px;
      height: 180px;
      background-color: #52a8f3;
    }

    @media (min-width: 768px) {
      .login-wrapper {
        padding-top: 360px;
        margin-left: 22rem;
        top: 250px;
      }

      h2 {
        font-size: 24px;
      }

      button {
        font-size: 16px;
      }
    }
  </style>
</head>
<body>

  <!-- Circles -->
  {{-- <div class="circle circle-blue-top"></div> --}}
  {{-- <div class="circle circle-blue-bottom"></div> --}}

  <!-- Login Form -->
  <div>
    <div class="login-wrapper">
    <h1>Login</h1>
    <form method="POST" action="{{ route('login.check') }}">
      @csrf

      <label for="email">Email</label>
      <input type="email" id="email" name="email" placeholder="Your registered email" required />

      <label for="password">Password</label>
      <input type="password" id="password" name="password" placeholder="••••••••" required />

       <div class="forgot-password">
        <a href="/forgot-password">Forgot password ?</a>
      </div> 

      <div class="button-div">
        <button type="submit">Login</button>
      </div>
    </form>
   
   
  <!-- ✅ Load SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    @if(session('error'))
      <script>
        Swal.fire({
          icon: 'error',
          title: 'Oops...',
          text: '{{ session('error') }}',
          confirmButtonColor: '#E85A72',
        });
      </script>
    @endif
    @if(session('success'))
      <script>
        Swal.fire({
          icon: 'success',
          title: 'Success!',
          text: '{{ session('success') }}',
          confirmButtonColor: '#49479D',
        });
    </script>
    @endif

  </div>
  </div>
</body>
</html>
