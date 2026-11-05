@extends('dashboard.auth.master')
@section('title', 'تسجيل الدخول')
@section('content')
<style>
  :root {
    --primary: #4f46e5;
    --primary-hover: #4338ca;
    --primary-light: #eef2ff;
    --dark-slate: #0f172a;
    --text-main: #1e293b;
    --text-muted: #64748b;
    --border-color: #e2e8f0;
  }

  body {
    margin: 0;
    padding: 0;
    min-height: 100vh;
    background: #0f172a;
    font-family: 'Tajawal', sans-serif !important;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow-x: hidden;
  }

  /* Animated / Modern Geometric Background */
  .auth-bg {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: 0;
    background: radial-gradient(circle at 15% 50%, rgba(79, 70, 229, 0.18) 0%, transparent 45%),
                radial-gradient(circle at 85% 30%, rgba(14, 165, 233, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 50% 80%, rgba(99, 102, 241, 0.12) 0%, transparent 50%),
                #0b0f19;
  }

  .auth-container {
    position: relative;
    z-index: 10;
    width: 100%;
    max-width: 460px;
    padding: 20px;
    margin: auto;
  }

  .auth-card {
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.45), 0 0 0 1px rgba(255, 255, 255, 0.05);
    padding: 40px 36px;
    transition: all 0.3s ease;
  }

  .brand-section {
    text-align: center;
    margin-bottom: 30px;
  }

  .brand-logo-icon {
    width: 64px;
    height: 64px;
    margin: 0 auto 16px;
    background: linear-gradient(135deg, #4f46e5 0%, #06b6d4 100%);
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 30px;
    box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.4);
  }

  .brand-title {
    font-size: 24px;
    font-weight: 800;
    color: var(--dark-slate);
    margin: 0 0 6px 0;
    letter-spacing: -0.5px;
  }

  .brand-subtitle {
    font-size: 14px;
    color: var(--text-muted);
    margin: 0;
    font-weight: 400;
  }

  .form-group-custom {
    margin-bottom: 22px;
    position: relative;
  }

  .form-label-custom {
    display: block;
    font-size: 13.5px;
    font-weight: 600;
    color: #334155;
    margin-bottom: 8px;
    text-align: right;
  }

  .input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
  }

  .input-wrapper i {
    position: absolute;
    right: 16px;
    font-size: 20px;
    color: #94a3b8;
    transition: color 0.2s ease;
    pointer-events: none;
  }

  .input-custom {
    width: 100%;
    padding: 13px 48px 13px 16px;
    border-radius: 14px;
    border: 1.5px solid var(--border-color);
    background: #f8fafc;
    font-size: 14.5px;
    color: var(--text-main);
    transition: all 0.2s ease;
    outline: none;
    text-align: right;
  }

  .input-custom:focus {
    background: #ffffff;
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.12);
  }

  .input-custom:focus + i,
  .input-wrapper:focus-within i {
    color: var(--primary);
  }

  .remember-forgot {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    font-size: 13.5px;
  }

  .checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--text-muted);
    cursor: pointer;
    user-select: none;
    margin: 0;
  }

  .checkbox-label input[type="checkbox"] {
    width: 17px;
    height: 17px;
    accent-color: var(--primary);
    cursor: pointer;
  }

  .btn-submit {
    width: 100%;
    padding: 14px 20px;
    background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
    color: white;
    border: none;
    border-radius: 14px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.35);
    transition: all 0.25s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
  }

  .btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 24px -5px rgba(79, 70, 229, 0.45);
    background: linear-gradient(135deg, #4338ca 0%, #2563eb 100%);
  }

  .btn-submit:active {
    transform: translateY(0);
  }

  .error-feedback {
    color: #ef4444;
    font-size: 12.5px;
    margin-top: 6px;
    text-align: right;
    display: block;
    font-weight: 500;
  }

  .card-footer-info {
    margin-top: 28px;
    text-align: center;
    font-size: 13px;
    color: #94a3b8;
    border-top: 1px solid #f1f5f9;
    padding-top: 20px;
  }

  /* Quick Auto-fill badge for convenience */
  .demo-creds {
    background: #f1f5f9;
    border-radius: 12px;
    padding: 10px 14px;
    margin-top: 18px;
    font-size: 12px;
    color: #475569;
    text-align: center;
    display: flex;
    justify-content: space-around;
  }
</style>

<div class="auth-bg"></div>

<div class="auth-container">
  <div class="auth-card">
    <div class="brand-section">
      <div class="brand-logo-icon">
        <i class="la la-building"></i>
      </div>
      <h2 class="brand-title">نَبْت للموارد البشرية</h2>
      <p class="brand-subtitle">نظام إدارة الموارد البشرية وشؤون الموظفين</p>
    </div>

    <form action="{{ route('login') }}" method="POST">
      @csrf

      <div class="form-group-custom">
        <label class="form-label-custom" for="email">البريد الإلكتروني</label>
        <div class="input-wrapper">
          <input
            type="email"
            name="email"
            id="email"
            class="input-custom"
            value="{{ old('email') }}"
            required
            autofocus
          >
          <i class="la la-envelope"></i>
        </div>
        @error('email')
          <span class="error-feedback">{{ $message }}</span>
        @enderror
      </div>

      <div class="form-group-custom">
        <label class="form-label-custom" for="password">كلمة المرور</label>
        <div class="input-wrapper">
          <input
            type="password"
            name="password"
            id="password"
            class="input-custom"
            placeholder="••••••••"
            required
          >
          <i class="la la-lock"></i>
        </div>
        @error('password')
          <span class="error-feedback">{{ $message }}</span>
        @enderror
      </div>

      <div class="remember-forgot">
        <label class="checkbox-label" for="remember">
          <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
          <span>تذكرني على هذا الجهاز</span>
        </label>
      </div>

      @error('g-recaptcha-response')
        <div class="error-feedback mb-2">{{ $message }}</div>
      @enderror

      <button type="submit" class="btn-submit">
        <span>تسجيل الدخول</span>
        <i class="la la-arrow-left"></i>
      </button>
    </form>

    <div class="card-footer-info">
      <span>جميع الحقوق محفوظة &copy; {{ date('Y') }} نَبْت HRMS</span>
    </div>
  </div>
</div>
@endsection
