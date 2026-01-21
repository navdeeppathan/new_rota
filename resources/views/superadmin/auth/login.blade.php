{{-- <!DOCTYPE html>
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

@if(session('login_success'))
<script>
Swal.fire({
    title: 'Login Successful!',
    text: 'Where do you want to go?',
    icon: 'success',
    showCancelButton: true,
    confirmButtonText: 'Go to CQC',
    cancelButtonText: 'Go to ROTA',
    confirmButtonColor: '#E85A72',
    cancelButtonColor: '#49479D',

    didOpen: () => {
        const actions = Swal.getActions();
        actions.style.display = 'flex';
        actions.style.gap = '18px';   // 👈 gap between buttons
        actions.style.justifyContent = 'center';

        const confirmBtn = Swal.getConfirmButton();
        const cancelBtn = Swal.getCancelButton();

        confirmBtn.style.minWidth = '120px';
        cancelBtn.style.minWidth = '120px';
    }
}).then((result) => {
    if (result.isConfirmed) {
        window.location.href = "/cqc-index";
    } else {
        window.location.href = "/dashboard";
    }
});
</script>
@endif



  </div>
  </div>
</body>
</html> --}}



<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
body{
    background:#eaf1f8;
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    font-family:'Poppins', sans-serif;
    padding:15px;
}

.login-wrapper{
    max-width:900px;
    width:100%;
    background:#fff;
    border-radius:15px;
    overflow:hidden;
    box-shadow:0 15px 40px rgba(0,0,0,.1);
}

/* LEFT PANEL */
.login-left{
    background:linear-gradient(135deg,#2f80ed,#56ccf2);
    color:#fff;
    padding:50px;
    position:relative;
    min-height:520px;
}

.login-left::after{
    content:'';
    position:absolute;
    inset:0;
    background-image:
      radial-gradient(circle at 20% 30%,rgba(255,255,255,.2) 2px,transparent 3px),
      radial-gradient(circle at 70% 60%,rgba(255,255,255,.2) 2px,transparent 3px),
      radial-gradient(circle at 40% 80%,rgba(255,255,255,.2) 2px,transparent 3px);
    background-size:150px 150px;
}

.login-left h1{
    font-size:34px;
    font-weight:700;
}

.login-left p{
    opacity:.9;
    margin-top:20px;
    line-height:1.7;
}

/* RIGHT PANEL */
.login-right{
    padding:60px 50px;
}

.login-right h4{
    font-weight:700;
    color:#2f80ed;
}

.form-control{
    height:48px;
    border-radius:10px;
    background:#f4f7fb;
    border:none;
}

.form-control:focus{
    box-shadow:none;
    border:2px solid #2f80ed;
    background:#fff;
}

.login-btn{
    background:#2f80ed;
    border:none;
    padding:14px;
    width:100%;
    color:#fff;
    border-radius:30px;
    font-weight:600;
    margin-top:10px;
}

.login-btn:hover{
    background:#1c6ed5;
}

/* MOBILE FIXES */
@media(max-width:768px){
    .login-left{
        min-height:auto;
        padding:40px 25px;
        text-align:center;
    }

    .login-right{
        padding:40px 25px;
    }

    .login-left h1{
        font-size:26px;
    }
}
</style>
</head>

<body>

<div class="login-wrapper container-fluid">
    <div class="row g-0">

        <!-- LEFT -->
        <div class="col-lg-6 col-md-6 col-12 login-left d-flex flex-column justify-content-center text-center">
            <small class="fw-bold">CQC360</small>
            <h1 class="mt-4">WELCOME BACK</h1>
            <p>Nice to see you again!  
            Enter your credentials to access your account and continue your journey with us.</p>
        </div>

        <!-- RIGHT -->
        <div class="col-lg-6 col-md-6 col-12 login-right d-flex flex-column justify-content-center">
            <h4>Login Account</h4>
            <p class="text-muted mb-4">Enter your email and password to login</p>

            <form method="POST" action="{{ route('superadmin.login.check') }}">
                @csrf
                <input type="email" name="email" class="form-control mb-3" placeholder="Email ID" required>
                <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
                <button class="login-btn">Login</button>
            </form>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session('login_success'))
  <script>
    Swal.fire({
        title: 'Login Successful!',
        text: 'Where do you want to go?',
        icon: 'success',
    })
  </script>
  
@endif

@if (session('login_error'))
  <script>
    Swal.fire({
        icon: 'error',
        title: 'Login Failed',
        text: 'Invalid email or password',
    });
  </script>
@endif

</body>
</html>

</body>
</html>
