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
        *{box-sizing:border-box}html{min-height:100%;background:var(--hr-bg)}body{margin:0;background:var(--hr-bg);color:var(--hr-ink);font-family:'Tajawal',sans-serif!important;font-size:15px;line-height:1.55;direction:rtl;text-align:right;padding-top:76px}a{color:var(--hr-primary)}.header-navbar,.main-menu,.content-header,.footer{display:none!important}.app-content.content{min-height:calc(100vh - 76px);margin:0!important;padding:28px 28px 44px!important;background:var(--hr-bg)}.content-wrapper{max-width:1600px;margin:0 auto;padding:0!important}.hr-topbar{position:fixed;z-index:1031;inset:0 0 auto;height:76px;background:rgba(255,255,255,.95);border-bottom:1px solid var(--hr-line);display:flex;align-items:center;gap:22px;padding:0 30px;backdrop-filter:blur(16px)}.hr-brand{display:flex;align-items:center;gap:11px;color:var(--hr-ink);font-size:19px;font-weight:800;text-decoration:none;white-space:nowrap}.hr-brand:hover{color:var(--hr-primary)}.hr-brand small{display:block;color:var(--hr-muted);font-size:11px;font-weight:500;line-height:1.2}.hr-brand-mark{display:grid;place-items:center;width:40px;height:40px;border-radius:13px;background:var(--hr-mint);color:var(--hr-primary);font-size:24px}.hr-topbar-spacer{flex:1}.hr-today{color:var(--hr-muted);font-size:14px}.hr-today i{margin-left:5px;color:var(--hr-primary)}.hr-user-menu{position:relative}.hr-user-trigger{display:flex;align-items:center;gap:10px;border:0;background:none;padding:4px;color:var(--hr-ink);font-family:inherit;text-align:right}.hr-avatar{display:grid;place-items:center;width:40px;height:40px;background:#dceee5;color:var(--hr-primary-dark);border-radius:50%;font-size:17px;font-weight:800}.hr-user-name{font-weight:700}.hr-user-name small{display:block;color:var(--hr-muted);font-weight:400;font-size:12px}.hr-menu-toggle{display:none;border:0;background:transparent;font-size:25px;color:var(--hr-ink)}.hr-sidebar{position:fixed;z-index:1029;right:0;top:76px;bottom:0;width:258px;padding:25px 15px 18px;background:var(--hr-sidebar);overflow-y:auto;display:flex;flex-direction:column;gap:4px}.hr-side-label{padding:12px 13px 5px;color:var(--hr-sidebar-muted);font-size:11px;font-weight:700}.hr-side-link{display:flex;align-items:center;gap:12px;min-height:45px;padding:10px 13px;border-radius:11px;color:#dae8e2;font-size:14px;font-weight:500;text-decoration:none;transition:.18s}.hr-side-link>i{width:21px;color:#9bb5ab;font-size:19px;text-align:center}.hr-side-link:hover,.hr-side-link.active{background:#204d40;color:#fff}.hr-side-link.active{box-shadow:inset -3px 0 #61c59e}.hr-side-link.active>i{color:#78d2a8}.hr-side-footer{margin-top:auto;padding:17px 12px 0;border-top:1px solid #29443d;color:#c5d7cf;font-size:12px}.hr-status-dot{display:inline-block;width:8px;height:8px;margin-left:7px;border-radius:50%;background:#6bd39d}.hr-sidebar-overlay{display:none}.app-content.content{margin-right:258px!important}.content-header{display:block!important;padding:2px 0 20px!important;border:0!important}.content-header-title{font-size:24px;font-weight:800;color:var(--hr-ink)}.content-header-subtitle{color:var(--hr-muted);font-size:14px;font-weight:400}.card{border:1px solid var(--hr-line)!important;border-radius:16px!important;box-shadow:var(--hr-shadow)!important;overflow:hidden}.card-header{padding:18px 21px!important;background:#fff!important;border-bottom:1px solid var(--hr-line)!important}.card-title{font-size:16px!important;font-weight:700!important;color:var(--hr-ink)!important}.card-body{padding:20px!important}.btn{border-radius:9px!important;font-family:'Tajawal',sans-serif!important;font-weight:600}.btn-primary{background-color:var(--hr-primary)!important;border-color:var(--hr-primary)!important}.text-primary{color:var(--hr-primary)!important}.table th{color:var(--hr-muted);font-size:12px;font-weight:700;border-top:0}.table td{vertical-align:middle}.form-control{min-height:42px;border-radius:9px;font-family:inherit}.hr-page-heading{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:22px}.hr-page-heading h1{margin:0;font-size:24px;font-weight:800}.hr-page-heading p{margin:5px 0 0;color:var(--hr-muted)}.pagination{justify-content:flex-start;gap:3px}.page-link{color:var(--hr-primary);font-family:inherit;border-radius:7px!important}.page-item.active .page-link{background-color:var(--hr-primary);border-color:var(--hr-primary)}.badge-success{background:#e4f4eb;color:#218052}.badge-secondary{background:#edf0ef;color:#66736d}.badge-info{background:#e7f0fc;color:#4279bc}.alert{border-radius:11px}
        @media(max-width:991.98px){body{padding-top:65px}.hr-topbar{height:65px;padding:0 17px;gap:12px}.hr-menu-toggle{display:block}.hr-brand{font-size:17px}.hr-brand-mark{width:36px;height:36px}.hr-today{display:none}.hr-sidebar{top:65px;width:278px;transform:translateX(110%);transition:transform .22s ease}.hr-sidebar.is-open{transform:translateX(0)}.hr-sidebar-overlay{position:fixed;z-index:1028;inset:65px 0 0;background:rgba(11,30,25,.43)}.hr-sidebar-overlay.is-visible{display:block}.app-content.content{margin-right:0!important;padding:23px 18px 35px!important;min-height:calc(100vh - 65px)}.hr-kpi-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.hr-payroll-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @media(max-width:575.98px){.app-content.content{padding:18px 13px 30px!important}.hr-user-name{display:none}.content-header-title,.hr-page-heading h1{font-size:21px}.card-body{padding:15px!important}.hr-kpi-grid{gap:9px}.hr-kpi{min-height:153px;padding:14px}.hr-kpi>strong{font-size:24px}.hr-kpi-icon{top:13px;left:12px;width:34px;height:34px;font-size:18px}.hr-welcome{align-items:flex-start;flex-direction:column;padding:18px}.hr-welcome-date{width:100%}.hr-attendance-overview{align-items:flex-start;flex-wrap:wrap}.hr-attendance-ring{width:82px;height:82px}.hr-attendance-ring>div{width:62px;height:62px}.hr-dashboard .table{min-width:650px}.hr-page-heading{align-items:flex-start;flex-direction:column}}
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
