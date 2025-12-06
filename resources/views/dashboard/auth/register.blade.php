<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء حساب - Salumida</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --pink: #e91e63;
            --pink-light: #ec407a;
            --dark-bg: #1a1a2e;
            --darker-bg: #16213e;
            --card-bg: #1e1e2e;
            --border-pink: rgba(233,30,99,0.2);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, var(--dark-bg), var(--darker-bg));
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .register-container {
            width: 100%;
            max-width: 450px;
            margin: 0 auto;
        }

        .register-card {
            background: var(--card-bg);
            border: 1px solid var(--border-pink);
            border-radius: 20px;
            padding: 3rem 2.5rem;
            width: 100%;
            box-shadow: 0 20px 50px rgba(233,30,99,0.3);
            animation: fadeInUp 0.8s ease;
            text-align: center;
            position: relative;
        }

        .register-card h2 {
            color: var(--pink);
            font-weight: 800;
            font-size: 2.4rem;
            margin-bottom: 2rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .form-control {
            background: #2c2c44;
            border: 1px solid var(--border-pink);
            color: #fff;
            border-radius: 12px;
            padding: 0.9rem 1.2rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--pink);
            box-shadow: 0 0 0 0.2rem rgba(233,30,99,0.3);
            background: #33334d;
            color: #fff;
        }

        .form-control::placeholder {
            color: #aaa;
        }

        .input-group-text {
            background: var(--pink);
            color: #fff;
            border: none;
            border-radius: 12px 0 0 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .input-group-text:hover {
            background: var(--pink-light);
        }

        .btn-register {
            background: var(--pink);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 0.9rem;
            font-weight: 600;
            font-size: 1.1rem;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(233,30,99,0.3);
        }

        .btn-register:hover {
            background: var(--pink-light);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(233,30,99,0.4);
        }

        .register-footer {
            text-align: center;
            margin-top: 1.5rem;
        }

        .register-footer a {
            color: var(--pink);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .register-footer a:hover {
            color: var(--pink-light);
        }

        .text-danger {
            font-size: 0.875rem;
        }

        .form-label {
            color: #fff;
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: block;
            text-align: right;
        }

        .form-check-label {
            color: #fff;
            cursor: pointer;
        }

        .form-check-input {
            background-color: #2c2c44;
            border: 1px solid var(--border-pink);
        }

        .form-check-input:checked {
            background-color: var(--pink);
            border-color: var(--pink);
        }

        .password-strength {
            margin-top: 0.5rem;
            font-size: 0.8rem;
        }

        .strength-weak { color: #ff4757; }
        .strength-medium { color: #ffa502; }
        .strength-strong { color: #2ed573; }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* تأثيرات إضافية */
        .register-card::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, var(--pink), transparent, var(--pink-light));
            border-radius: 22px;
            z-index: -1;
            opacity: 0.3;
            animation: borderGlow 3s ease-in-out infinite;
        }

        @keyframes borderGlow {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 0.6; }
        }

        /* تحسينات للشاشات الصغيرة */
        @media (max-width: 576px) {
            .register-card {
                padding: 2.5rem 2rem;
                margin: 0;
            }

            .register-card h2 {
                font-size: 2rem;
            }

            body {
                padding: 0.5rem;
            }
        }

        /* تحسينات للشاشات الكبيرة */
        @media (min-width: 1200px) {
            .register-container {
                max-width: 480px;
            }

            .register-card {
                padding: 3.5rem 3rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <h2>إنشاء حساب</h2>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">الاسم الكامل</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" required autofocus placeholder="أدخل اسمك الكامل">
                    @error('name')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">البريد الإلكتروني</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}" required placeholder="email@example.com">
                    @error('email')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">كلمة المرور</label>
                    <div class="input-group">
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror"
                               required placeholder="••••••••" oninput="checkPasswordStrength(this.value)">
                        <span class="input-group-text" onclick="togglePass('password')">
                            <i class="fas fa-eye" id="eye-password"></i>
                        </span>
                    </div>
                    <div class="password-strength" id="password-strength"></div>
                    @error('password')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">تأكيد كلمة المرور</label>
                    <div class="input-group">
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="form-control" required placeholder="••••••••">
                        <span class="input-group-text" onclick="togglePass('password_confirmation')">
                            <i class="fas fa-eye" id="eye-password_confirmation"></i>
                        </span>
                    </div>
                </div>

                <div class="mb-4 form-check text-start">
                    <input type="checkbox" name="terms" id="terms" class="form-check-input" required>
                    <label class="form-check-label" for="terms">
                        أوافق على <a href="#" class="text-pink">الشروط والأحكام</a> و <a href="#" class="text-pink">سياسة الخصوصية</a>
                    </label>
                    @error('terms')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn-register">
                    <i class="fas fa-user-plus me-2"></i> إنشاء حساب
                </button>
            </form>

            <div class="register-footer">
                <p class="text-light mb-2">لديك حساب بالفعل؟
                    <a href="{{ route('login') }}">سجل الدخول</a>
                </p>
            </div>
        </div>
    </div>

    <!-- Toast Error -->
    <div class="position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1100; top: 20px;">
        <div id="errorToast" class="toast align-items-center text-white bg-danger border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body" id="error-message"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePass(fieldId) {
            const pass = document.getElementById(fieldId);
            const eye = document.getElementById('eye-' + fieldId);
            if (pass.type === 'password') {
                pass.type = 'text';
                eye.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                pass.type = 'password';
                eye.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        function checkPasswordStrength(password) {
            const strengthBar = document.getElementById('password-strength');
            let strength = 0;
            let message = '';
            let className = '';

            // Check password length
            if (password.length >= 8) strength++;
            // Check for mixed case
            if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) strength++;
            // Check for numbers
            if (password.match(/([0-9])/)) strength++;
            // Check for special characters
            if (password.match(/([!,%,&,@,#,$,^,*,?,_,~])/)) strength++;

            switch(strength) {
                case 0:
                case 1:
                    message = 'كلمة مرور ضعيفة';
                    className = 'strength-weak';
                    break;
                case 2:
                    message = 'كلمة مرور متوسطة';
                    className = 'strength-medium';
                    break;
                case 3:
                case 4:
                    message = 'كلمة مرور قوية';
                    className = 'strength-strong';
                    break;
            }

            strengthBar.innerHTML = message;
            strengthBar.className = 'password-strength ' + className;
        }

        // التحقق من تطابق كلمات المرور
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password_confirmation').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                document.getElementById('error-message').textContent = 'كلمات المرور غير متطابقة';
                new bootstrap.Toast(document.getElementById('errorToast')).show();
            }
        });

        @error('name')
            document.addEventListener('DOMContentLoaded', () => {
                document.getElementById('error-message').textContent = '{{ $message }}';
                new bootstrap.Toast(document.getElementById('errorToast')).show();
            });
        @enderror

        @error('email')
            document.addEventListener('DOMContentLoaded', () => {
                document.getElementById('error-message').textContent = '{{ $message }}';
                new bootstrap.Toast(document.getElementById('errorToast')).show();
            });
        @enderror

        @error('password')
            document.addEventListener('DOMContentLoaded', () => {
                document.getElementById('error-message').textContent = '{{ $message }}';
                new bootstrap.Toast(document.getElementById('errorToast')).show();
            });
        @enderror

        @error('terms')
            document.addEventListener('DOMContentLoaded', () => {
                document.getElementById('error-message').textContent = '{{ $message }}';
                new bootstrap.Toast(document.getElementById('errorToast')).show();
            });
        @enderror
    </script>
</body>
</html>
