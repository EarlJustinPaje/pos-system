<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <title>Signin Template · Bootstrap v5.1</title>

  <!-- Bootstrap core CSS -->
  <link href="{{ asset('assets/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/signin.css') }}" rel="stylesheet">
</head>
<body class="text-center">
  <main class="form-signin">
    @yield('content')
  </main>
  <script src="{{ asset('assets/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>