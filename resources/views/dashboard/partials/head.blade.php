<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f5f7f6"><title>@yield('title', 'لوحة المتابعة') | نبت للموارد البشرية</title>
    <link rel="stylesheet" href="{{ asset('assets/assets-back/css-rtl/vendors.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/assets-back/css-rtl/plugins/extensions/toastr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/hrms-toasts.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/assets-back/fonts/simple-line-icons/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/assets-back/fonts/line-awesome/css/line-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/assets-back/fonts/line-awesome/css/line-awesome-font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/assets-back/css-rtl/core/menu/menu-types/vertical-menu-modern.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/assets-back/css-rtl/custom-rtl.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/assets-back/css-rtl/plugins/ui/jqueryui.css') }}">
    <style>
        :root{--hr-bg:#f5f7f6;--hr-surface:#fff;--hr-ink:#182522;--hr-muted:#7a8883;--hr-line:#e9efec;--hr-primary:#147d64;--hr-primary-dark:#0e5f4d;--hr-mint:#e8f5ef;--hr-sidebar:#102d27;--hr-sidebar-muted:#91ada4;--hr-shadow:0 5px 24px rgba(22,55,43,.055)}
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;600;700;800&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;600;700;800&display=swap');
        * { box-sizing: border-box; }
        html { min-height: 100%; background: var(--hr-bg); }
        body {
            margin: 0;
            background: var(--hr-bg);
            color: var(--hr-ink);
            font-family: 'Tajawal', sans-serif !important;
            font-size: 15px;
            line-height: 1.55;
            direction: rtl;
            text-align: right;
            padding-top: 72px;
        }
        a { color: var(--hr-primary); }
        .header-navbar, .main-menu, .content-header, .footer { display: none !important; }
        .app-content.content {
            min-height: calc(100vh - 72px);
            margin: 0 !important;
            margin-right: 258px !important;
            padding: 24px 24px 40px !important;
            background: var(--hr-bg);
            transition: all 0.25s ease;
        }
        .content-wrapper {
            max-width: 1600px;
            margin: 0 auto;
            padding: 0 !important;
        }

        /* Topbar Core Layout */
        .hr-topbar {
            position: fixed;
            z-index: 1031;
            inset: 0 0 auto;
            height: 72px;
            background: rgba(255, 255, 255, 0.96);
            border-bottom: 1px solid var(--hr-line);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            backdrop-filter: blur(16px);
            transition: all 0.25s ease;
        }
        .hr-topbar-right {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .hr-topbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .hr-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--hr-ink);
            text-decoration: none;
            white-space: nowrap;
        }
        .hr-brand:hover {
            color: var(--hr-primary);
        }
        .hr-brand-mark {
            display: grid;
            place-items: center;
            width: 40px;
            height: 40px;
            min-width: 40px;
            border-radius: 12px;
            background: var(--hr-mint);
            color: var(--hr-primary);
            font-size: 22px;
        }
        .hr-brand-text {
            display: flex;
            flex-direction: column;
            line-height: 1.15;
            text-align: right;
        }
        .hr-brand-title {
            font-size: 19px;
            font-weight: 800;
            color: var(--hr-ink);
        }
        .hr-brand-sub {
            color: var(--hr-muted);
            font-size: 11px;
            font-weight: 500;
        }
        .hr-today {
            color: var(--hr-muted);
            font-size: 13.5px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .hr-today i {
            color: var(--hr-primary);
        }
        .hr-topbar-btn {
            position: relative;
            display: grid;
            place-items: center;
            width: 40px;
            height: 40px;
            min-width: 40px;
            border-radius: 12px;
            border: 1px solid var(--hr-line);
            background: #fff;
            color: var(--hr-ink);
            font-size: 20px;
            cursor: pointer;
            transition: all 0.2s ease;
            padding: 0;
        }
        .hr-topbar-btn:hover {
            background: #f0f7f4;
            color: var(--hr-primary);
            border-color: #c4ddd5;
        }
        .hr-badge-dot {
            position: absolute;
            top: 7px;
            right: 8px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #e53e3e;
            box-shadow: 0 0 0 2px #fff;
        }
        .hr-user-trigger {
            display: flex;
            align-items: center;
            gap: 10px;
            border: 0;
            background: none;
            padding: 4px;
            color: var(--hr-ink);
            font-family: inherit;
            cursor: pointer;
            text-align: right;
        }
        .hr-avatar {
            display: grid;
            place-items: center;
            width: 40px;
            height: 40px;
            min-width: 40px;
            background: #dceee5;
            color: var(--hr-primary-dark);
            border-radius: 12px;
            font-size: 17px;
            font-weight: 800;
        }
        .hr-user-info {
            display: flex;
            flex-direction: column;
            text-align: right;
            line-height: 1.2;
        }
        .hr-user-name {
            font-weight: 700;
            font-size: 14px;
            color: var(--hr-ink);
        }
        .hr-user-role {
            color: var(--hr-muted);
            font-size: 11px;
            font-weight: 500;
        }
        .hr-user-chevron {
            font-size: 12px;
            color: var(--hr-muted);
        }
        .hr-menu-toggle {
            display: none;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            min-width: 40px;
            border-radius: 12px;
            border: 1px solid var(--hr-line);
            background: #fff;
            color: var(--hr-ink);
            font-size: 22px;
            cursor: pointer;
            transition: all 0.2s ease;
            padding: 0;
        }
        .hr-menu-toggle:hover {
            background: var(--hr-mint);
            color: var(--hr-primary);
        }
        .hr-dropdown-menu {
            border: 1px solid var(--hr-line) !important;
            border-radius: 14px !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.12) !important;
            min-width: 250px;
            padding: 6px 0;
        }

        /* Sidebar Styling */
        .hr-sidebar {
            position: fixed;
            z-index: 1029;
            right: 0;
            top: 72px;
            bottom: 0;
            width: 258px;
            padding: 20px 14px 18px;
            background: var(--hr-sidebar);
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .hr-side-label {
            padding: 12px 13px 5px;
            color: var(--hr-sidebar-muted);
            font-size: 11px;
            font-weight: 700;
        }
        .hr-side-link {
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 44px;
            padding: 9px 13px;
            border-radius: 11px;
            color: #dae8e2;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: .18s;
        }
        .hr-side-link > i {
            width: 21px;
            color: #9bb5ab;
            font-size: 19px;
            text-align: center;
        }
        .hr-side-link:hover, .hr-side-link.active {
            background: #204d40;
            color: #fff;
        }
        .hr-side-link.active {
            box-shadow: inset -3px 0 #61c59e;
        }
        .hr-side-link.active > i {
            color: #78d2a8;
        }
        .hr-side-footer {
            margin-top: auto;
            padding: 17px 12px 0;
            border-top: 1px solid #29443d;
            color: #c5d7cf;
            font-size: 12px;
        }
        .hr-status-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            margin-left: 7px;
            border-radius: 50%;
            background: #6bd39d;
        }
        .hr-sidebar-overlay {
            display: none;
        }

        /* General Dashboard Elements */
        .card {
            border: 1px solid var(--hr-line) !important;
            border-radius: 16px !important;
            box-shadow: var(--hr-shadow) !important;
            overflow: hidden;
        }
        .card-header {
            padding: 16px 20px !important;
            background: #fff !important;
            border-bottom: 1px solid var(--hr-line) !important;
        }
        .card-title {
            font-size: 16px !important;
            font-weight: 700 !important;
            color: var(--hr-ink) !important;
        }
        .card-body {
            padding: 20px !important;
        }
        .btn {
            border-radius: 9px !important;
            font-family: 'Tajawal', sans-serif !important;
            font-weight: 600;
        }
        .btn-primary {
            background-color: var(--hr-primary) !important;
            border-color: var(--hr-primary) !important;
        }
        .text-primary {
            color: var(--hr-primary) !important;
        }
        .table th {
            color: var(--hr-muted);
            font-size: 12px;
            font-weight: 700;
            border-top: 0;
        }
        .table td {
            vertical-align: middle;
        }
        .hr-page-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 22px;
            flex-wrap: wrap;
        }
        .hr-page-heading h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 800;
        }
        .hr-page-heading p {
            margin: 5px 0 0;
            color: var(--hr-muted);
        }
        .badge-success { background: #e4f4eb; color: #218052; }
        .badge-secondary { background: #edf0ef; color: #66736d; }
        .badge-info { background: #e7f0fc; color: #4279bc; }
        .alert { border-radius: 11px; }

        /* Responsive Breakpoints */
        @media (max-width: 1199.98px) {
            .hr-today {
                display: none !important;
            }
        }
        @media (max-width: 991.98px) {
            body {
                padding-top: 64px !important;
            }
            .hr-topbar {
                height: 64px;
                padding: 0 16px;
            }
            .hr-menu-toggle {
                display: flex !important;
            }
            .hr-sidebar {
                top: 64px;
                width: 270px;
                transform: translateX(110%);
                transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: -5px 0 25px rgba(0,0,0,0.15);
            }
            .hr-sidebar.is-open {
                transform: translateX(0);
            }
            .hr-sidebar-overlay {
                position: fixed;
                z-index: 1028;
                inset: 64px 0 0;
                background: rgba(11, 30, 25, 0.45);
                backdrop-filter: blur(2px);
                display: none;
            }
            .hr-sidebar-overlay.is-visible {
                display: block;
            }
            .app-content.content {
                margin-right: 0 !important;
                padding: 20px 16px 36px !important;
                min-height: calc(100vh - 64px);
            }
            .hr-kpi-grid, .hr-payroll-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 767.98px) {
            .hr-topbar {
                padding: 0 12px;
                gap: 8px;
            }
            .hr-topbar-right {
                gap: 8px;
            }
            .hr-topbar-left {
                gap: 8px;
            }
            .hr-brand-sub {
                display: none !important;
            }
            .hr-brand-mark {
                width: 36px;
                height: 36px;
                min-width: 36px;
                font-size: 20px;
            }
            .hr-brand-title {
                font-size: 17px;
            }
            .hr-user-info, .hr-user-chevron {
                display: none !important;
            }
            .hr-menu-toggle, .hr-topbar-btn, .hr-avatar {
                width: 36px;
                height: 36px;
                min-width: 36px;
                font-size: 18px;
                border-radius: 10px;
            }
        }
        @media (max-width: 575.98px) {
            .hr-topbar {
                padding: 0 10px;
            }
            .app-content.content {
                padding: 16px 10px 28px !important;
            }
            .content-header-title, .hr-page-heading h1 {
                font-size: 20px !important;
            }
            .card-body {
                padding: 15px !important;
            }
            .hr-kpi-grid {
                gap: 9px;
            }
            .hr-kpi {
                min-height: 153px;
                padding: 14px;
            }
            .hr-kpi > strong {
                font-size: 24px;
            }
            .hr-welcome {
                align-items: flex-start;
                flex-direction: column;
                padding: 18px;
            }
            .hr-welcome-date {
                width: 100%;
            }
            .hr-dashboard .table {
                min-width: 650px;
            }
            .hr-page-heading {
                align-items: flex-start;
                flex-direction: column;
            }
        }
        /* ===== Modern Form & Select Global Styling ===== */
        .form-label, label {
            font-size: 13.5px !important;
            font-weight: 700 !important;
            color: #2c3e38 !important;
            margin-bottom: 7px !important;
            display: inline-block !important;
        }
        .form-control, .form-select, select.form-control, select {
            border: 1.5px solid #d3ded9 !important;
            border-radius: 10px !important;
            padding: 9px 14px !important;
            background-color: #ffffff !important;
            color: #1a2b25 !important;
            font-size: 14px !important;
            font-family: inherit !important;
            transition: all 0.2s ease-in-out !important;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02) !important;
            min-height: 42px !important;
            outline: none !important;
        }
        .form-control:focus, .form-select:focus, select:focus {
            border-color: var(--hr-primary) !important;
            box-shadow: 0 0 0 3.5px rgba(20, 125, 100, 0.15) !important;
            background-color: #fff !important;
        }
        /* Custom Arrow for select elements in RTL */
        .form-select, select.form-control, select {
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23527267' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: left 14px center !important;
            background-size: 16px 16px !important;
            padding-left: 38px !important;
            padding-right: 14px !important;
            cursor: pointer !important;
        }
        .form-select-sm, select.form-control-sm {
            min-height: 35px !important;
            padding: 5px 10px !important;
            padding-left: 30px !important;
            font-size: 13px !important;
            background-position: left 10px center !important;
            background-size: 13px 13px !important;
        }
        .form-control[type="file"] {
            padding: 8px 12px !important;
            line-height: 1.5 !important;
            cursor: pointer !important;
        }
        .form-control[type="date"] {
            cursor: pointer !important;
        }
        .card .card-header {
            font-size: 15px !important;
            font-weight: 800 !important;
            color: #15382e !important;
            background: #fafcfb !important;
            border-bottom: 1px solid #e5ece8 !important;
        }
        .btn {
            font-size: 14px !important;
            font-weight: 700 !important;
            padding: 9px 18px !important;
            transition: all 0.2s ease !important;
        }
        .btn-primary {
            background: linear-gradient(135deg, #147d64 0%, #0e5f4d 100%) !important;
            border: none !important;
            box-shadow: 0 4px 12px rgba(20, 125, 100, 0.25) !important;
        }
        .btn-primary:hover {
            box-shadow: 0 6px 16px rgba(20, 125, 100, 0.35) !important;
            transform: translateY(-1px) !important;
        }
        @stack('css')
    </style>
</head>
