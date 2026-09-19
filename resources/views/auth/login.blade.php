<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Iniciar sesión | {{ $config->nombre_empresa ?? config('app.name') }}</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
<style>
body{font-family:'Inter',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#28a745 0%,#155724 100%);margin:0}
.login-card{background:#fff;border-radius:20px;box-shadow:0 25px 60px rgba(0,0,0,.25);max-width:420px;width:90%;padding:3rem 2.5rem;animation:fadeIn .6s ease}
@keyframes fadeIn{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:none}}
.login-card .icon-wrap{width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#28a745,#1e7e34);display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;color:#fff;font-size:2rem;box-shadow:0 8px 20px rgba(40,167,69,.4)}
.login-card h3{text-align:center;font-weight:700;margin-bottom:.3rem;color:#1a2332}
.login-card .subtitulo{text-align:center;color:#6c757d;margin-bottom:2rem;font-size:.95rem}
.form-control{padding:.85rem 1rem;border-radius:10px;border:1px solid #e1e5ea}
.form-control:focus{border-color:#28a745;box-shadow:0 0 0 .2rem rgba(40,167,69,.15)}
.btn-login{background:linear-gradient(135deg,#28a745,#1e7e34);border:0;border-radius:10px;padding:.9rem;font-weight:600;width:100%;color:#fff;transition:transform .15s}
.btn-login:hover{transform:translateY(-1px);color:#fff}
.input-group-text{background:#f8f9fa;border:1px solid #e1e5ea;border-right:0}
.brand{text-align:center;font-weight:300;color:#fff;position:absolute;bottom:1.5rem;left:0;right:0;font-size:.85rem;opacity:.7}
</style>
</head>
<body>
<div class="login-card">
    <div class="icon-wrap"><i class="fas fa-utensils"></i></div>
    <h3>{{ $config->nombre_empresa ?? 'CRM TPV FastFood' }}</h3>
    <p class="subtitulo">Bienvenido. Inicia sesión para continuar</p>

    @if($errors->any())
        <div class="alert alert-danger small">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-3">
            <div class="input-group">
                <span class="input-group-text"><i class="far fa-envelope"></i></span>
                <input type="email" name="email" class="form-control" placeholder="Email" required value="{{ old('email','admin@tpv.local') }}" autofocus>
            </div>
        </div>
        <div class="mb-3">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" name="password" class="form-control" placeholder="Contraseña" required>
            </div>
        </div>
        <div class="form-check mb-3">
            <input type="checkbox" class="form-check-input" id="remember" name="remember">
            <label for="remember" class="form-check-label small">Recuérdame en este equipo</label>
        </div>
        <button type="submit" class="btn-login"><i class="fas fa-sign-in-alt me-2"></i> Iniciar sesión</button>
    </form>
</div>
<div class="brand">© {{ date('Y') }} TPV FastFood &middot; Sistema de gestión profesional</div>
</body>
</html>
