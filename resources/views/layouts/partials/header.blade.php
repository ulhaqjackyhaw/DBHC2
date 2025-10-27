<header class="navbar navbar-expand-lg bg-white shadow-sm px-4 d-flex justify-content-between"
    style="min-height: 64px; z-index: 30;">
    {{-- Tombol untuk membuka/menutup sidebar --}}
    <button @click="sidebarOpen = !sidebarOpen" class="btn btn-light me-3">
        <i class="bi bi-list fs-5"></i>
    </button>

    <div class="d-none d-md-block">
        <h1 class="text-xl font-semibold text-slate-700">@yield('header-title')</h1>
    </div>

    {{-- Dropdown Pengguna --}}
    <div class="d-flex align-items-center">
        @auth {{-- Tampilkan hanya jika pengguna sudah login --}}
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="https://i.pravatar.cc/40?u={{ auth()->user()->email }}" alt="user" class="rounded-circle"
                        width="40" height="40">
                    <span class="d-none d-md-inline ms-2 text-slate-700">{{ Auth::user()->name }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2">
                    <li><a class="dropdown-item py-2" href="{{ route('profile.index') }}"><i
                                class="bi bi-person-circle me-2"></i> Profil</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        {{-- Form untuk Logout yang Aman --}}
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <a class="dropdown-item text-danger py-2" href="{{ route('logout') }}"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                <i class="bi bi-box-arrow-right me-2"></i> Keluar
                            </a>
                        </form>
                    </li>
                </ul>
            </div>
        @endauth
    </div>
</header>
