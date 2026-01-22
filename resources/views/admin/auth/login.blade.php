<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>CQC360 Login</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- ===== FAVICON ===== -->
<link rel="icon" href="/cqc360.png">
<link rel="apple-touch-icon" href="/cqc360.png">

<!-- ===== SOCIAL SHARE ===== -->
<meta property="og:title" content="CQC360 Login">
<meta property="og:description" content="Secure login to CQC360 dashboard">
<meta property="og:image" content="{{ asset('cqc360.png') }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:type" content="website">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:image" content="{{ asset('cqc360.png') }}">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

body{
    height:100vh;
    overflow:hidden;
}

/* ===== BACKGROUND ===== */
.page-bg{
    position:fixed;
    inset:0;
    background:url('/b1.png') center/cover no-repeat;
    animation:bgZoom 30s ease-in-out infinite alternate;
}

@keyframes bgZoom{
    from{transform:scale(1)}
    to{transform:scale(1.08)}
}

.overlay{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.45);
}

/* ===== MAIN LAYOUT ===== */
.login-page{
    position:relative;
    z-index:2;
    height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:40px;
}

.login-container{
    width:100%;
    max-width:1100px;
    display:grid;
    grid-template-columns:1fr 420px;
    gap:40px;
    color:#fff;
}

/* ===== LEFT CONTENT ===== */
.welcome{
    display:flex;
    flex-direction:column;
    align-items: center;
    justify-content:center;
}

.welcome h1{
    font-size:48px;
    font-weight:700;
    line-height:1.2;
}

.welcome p{
    margin-top:20px;
    max-width:420px;
    opacity:.9;
}

.social{
    margin-top:30px;
    display:flex;
    gap:15px;
}

.social a{
    width:36px;
    height:36px;
    border:1px solid rgba(255,255,255,.7);
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    color:#fff;
    text-decoration:none;
    transition:.3s;
}

.social a:hover{
    background:#fff;
    color:#000;
}

/* ===== RIGHT FORM ===== */
.form-box{
    background:transparent;
    padding:40px;
    border-radius:8px;
    box-shadow:0 20px 40px rgba(0,0,0,.5);
    animation:fadeUp 1s ease forwards;
}

@keyframes fadeUp{
    from{opacity:0; transform:translateY(40px)}
    to{opacity:1; transform:translateY(0)}
}

.form-box h3{
    margin-bottom:20px;
    font-size:22px;
}

.input-group{
    margin-bottom:15px;
    position:relative;
}

.input-group input{
    width:100%;
    padding:12px 40px 12px 14px;
    border:none;
    outline:none;
    border-radius:4px;
    font-size:14px;
}

.toggle-password{
    position:absolute;
    right:12px;
    top:50%;
    transform:translateY(-50%);
    cursor:pointer;
    color:#333;
}

.remember{
    display:flex;
    align-items:center;
    font-size:13px;
    margin:10px 0 20px;
}

.remember input{margin-right:8px}

.login-btn{
    width:100%;
    padding:12px;
    background:#2f80ed;
    border:none;
    color:#fff;
    font-weight:600;
    border-radius:4px;
    cursor:pointer;
    position:relative;
}

.login-btn:hover{
    background:#ff6320;
}

.spinner{
    width:18px;
    height:18px;
    border:3px solid rgba(255,255,255,.4);
    border-top:3px solid #fff;
    border-radius:50%;
    animation:spin 1s linear infinite;
    display:none;
    margin:0 auto;
}

@keyframes spin{
    to{transform:rotate(360deg)}
}

.form-links{
    margin-top:15px;
    font-size:12px;
}

.form-links a{
    color:#ddd;
    text-decoration:none;
}

.form-links a:hover{text-decoration:underline}

/* ===== RESPONSIVE ===== */
@media(max-width:900px){
    .login-container{
        grid-template-columns:1fr;
        text-align:center;
    }
    .welcome{
        display:none;
    }
    .form-box{
        background:transparent;
        padding:0;
        border-radius:8px;
        box-shadow:none;
        animation:fadeUp 1s ease forwards;
    }
}

/* ===== WELCOME ANIMATION ===== */
.animate-welcome{
    animation:welcomeFade 1s ease forwards;
    opacity:0;
}

@keyframes welcomeFade{
    to{opacity:1}
}

/* Heading animation */
.welcome-title span{
    display:inline-block;
    opacity:0;
    transform:translateY(30px);
    animation:titleReveal .8s ease forwards;
}

.welcome-title span:nth-child(1){
    animation-delay:.2s;
}

.welcome-title span:nth-child(3){
    animation-delay:.4s;
}

@keyframes titleReveal{
    to{
        opacity:1;
        transform:translateY(0);
    }
}

/* Paragraph animation */
.welcome-text{
    opacity:0;
    transform:translateY(20px);
    animation:textFade .8s ease forwards;
    animation-delay:.6s;
    text-align: center;
    color:#fff;
}

@keyframes textFade{
    to{
        opacity:1;
        transform:translateY(0);
    }
}

/* Social icons animation */
.welcome-social .icon{
    opacity:0;
    transform:scale(.6);
    animation:iconPop .5s ease forwards;
}

.delay-1{animation-delay:.9s}
.delay-2{animation-delay:1.05s}
.delay-3{animation-delay:1.2s}
.delay-4{animation-delay:1.35s}

@keyframes iconPop{
    to{
        opacity:1;
        transform:scale(1);
    }
}

/* Hover polish */
.welcome-social .icon:hover{
    transform:scale(1.15);
}

.logo img{
    width:60px;
    height:60px;
    object-fit:contain;
    margin-bottom:20px;
    animation:logoPop 1.2s ease forwards;
    border-radius:50%;
}
@keyframes logoPop{
    0%{transform:scale(.4);opacity:0}
    60%{transform:scale(1.15)}
    100%{transform:scale(1);opacity:1}
}

</style>
</head>

<body>

<div class="page-bg"></div>
<div class="overlay"></div>

<div class="login-page">
    <div class="login-container">

        <!-- LEFT -->
        <div class="welcome animate-welcome">

            <h1 class="welcome-title">
                <span>Welcome</span>
                <span>Back</span>
            </h1>

            <p class="welcome-text">
               Nice to see you again! <br> Enter your credentials to access your account and continue your journey with us.
            </p>

            <div class="social welcome-social">
                <a href="#" class="icon delay-1"><i class="bi bi-facebook"></i></a>
                <a href="#" class="icon delay-2"><i class="bi bi-twitter"></i></a>
                <a href="#" class="icon delay-3"><i class="bi bi-youtube"></i></a>
                <a href="#" class="icon delay-4"><i class="bi bi-instagram"></i></a>
            </div>

        </div>


        <!-- RIGHT -->
        <div class="form-box">
            <div class="logo">
                <img src="/cqc360.png" alt="CQC360 Logo">
            </div>
            <h3>Sign in</h3>

            <form method="POST" action="{{ route('login.check') }}" id="loginForm">
                @csrf

                <div class="input-group">
                    <input type="email" name="email" placeholder="Email Address" required>
                </div>

                <div class="input-group">
                    <input type="password" id="password" name="password" placeholder="Password" required>
                    <i class="bi bi-eye toggle-password" id="togglePassword"></i>
                </div>

                <div class="remember">
                    <input type="checkbox"> Remember Me
                </div>

                <button class="login-btn" id="loginBtn">
                    <span class="btn-text">Sign in now</span>
                    <div class="spinner"></div>
                </button>
            </form>
        </div>

    </div>
</div>

<script>
/* password toggle */
const toggle = document.getElementById('togglePassword');
const pass = document.getElementById('password');

toggle.onclick = ()=>{
    pass.type = pass.type === 'password' ? 'text' : 'password';
    toggle.classList.toggle('bi-eye');
    toggle.classList.toggle('bi-eye-slash');
};

/* spinner */
const form = document.getElementById('loginForm');
const btn = document.getElementById('loginBtn');
const spinner = btn.querySelector('.spinner');
const text = btn.querySelector('.btn-text');

form.addEventListener('submit',()=>{
    text.style.display='none';
    spinner.style.display='block';
    btn.disabled=true;
});
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
            actions.style.gap = '18px';
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
