<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">

@include('dashboard.partials.head')
@flasher_render

<body class="vertical-layout vertical-menu-modern 2-columns   menu-expanded fixed-navbar" data-open="click" data-menu="vertical-menu-modern"
  data-col="2-columns">
  <!-- fixed-top-->
  @include('dashboard.partials.navbar')
  <!-- ////////////////////////////////////////////////////////////////////////////-->
  @include('dashboard.partials.sidebar')
  <div class="app-content content">
      <div class="content-wrapper">
          @yield('content')
      </div>
  </div>
  <!-- ////////////////////////////////////////////////////////////////////////////-->
  <!-- BEGIN VENDOR JS-->
  @include('dashboard.partials.scripts')
</body>

</html>
