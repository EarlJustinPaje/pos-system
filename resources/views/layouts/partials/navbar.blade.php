<header class="p-3 bg-dark text-white">
  <div class="container">
    <div class="d-flex flex-wrap align-items-center justify-content-between">
      <a href="/" class="d-flex align-items-center mb-2 mb-lg-0 text-white text-decoration-none">
        <strong>My App</strong>
      </a>

      <ul class="nav col-12 col-lg-auto mb-2 justify-content-center mb-md-0">
        <li><a href="/" class="nav-link px-2 text-secondary">Home</a></li>
      </ul>

      @auth
        <span class="me-3">{{ auth()->user()->username ?? auth()->user()->email }}</span>
        <a href="{{ route('logout.perform') }}" class="btn btn-outline-light me-2">Logout</a>
      @endauth

      @guest
	<a href="{{ route('login') }}" class="btn btn-outline-light me-2">Login</a>
        <a href="{{ route('register.show') }}" class="btn btn-warning">Sign-up</a>
      @endguest
    </div>
  </div>
</header>