<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Apex Attendance') }} - @yield('title', 'Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS & DaisyUI via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.14/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        .sidebar-link.active {
            background: rgba(var(--p) / 0.1);
            color: hsl(var(--p));
            border-left: 4px solid hsl(var(--p));
        }
    </style>
</head>

<body class="font-sans antialiased" x-data="{ 
        sidebarOpen: true, 
        darkMode: localStorage.getItem('darkMode') === 'true',
        toggleDarkMode() {
            this.darkMode = !this.darkMode;
            localStorage.setItem('darkMode', this.darkMode);
            document.documentElement.setAttribute('data-theme', this.darkMode ? 'dark' : 'light');
        }
    }" x-init="document.documentElement.setAttribute('data-theme', darkMode ? 'dark' : 'light')">

    <div class="drawer lg:drawer-open">
        <input id="sidebar-drawer" type="checkbox" class="drawer-toggle" x-model="sidebarOpen" />

        <div class="drawer-content flex flex-col min-h-screen">
            <!-- Top Navbar -->
            <div class="navbar bg-base-100 border-b border-base-300 sticky top-0 z-30">
                <div class="flex-none lg:hidden">
                    <label for="sidebar-drawer" class="btn btn-square btn-ghost">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            class="inline-block w-6 h-6 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </label>
                </div>

                <div class="flex-1">
                    <span class="lg:hidden text-xl font-bold text-primary">Apex</span>
                </div>

                <!-- Search Bar -->
                <div class="flex-none hidden md:block">
                    <div class="form-control">
                        <div class="input-group">
                            <input type="text" placeholder="Search employees..."
                                class="input input-bordered input-sm w-64" />
                            <button class="btn btn-sm btn-square btn-ghost">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex-none gap-2">
                    <!-- Dark Mode Toggle -->
                    <button class="btn btn-ghost btn-circle" @click="toggleDarkMode()">
                        <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        <svg x-show="darkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </button>

                    <!-- Notifications -->
                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-ghost btn-circle">
                            <div class="indicator">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <span class="badge badge-xs badge-primary indicator-item">3</span>
                            </div>
                        </label>
                        <div tabindex="0"
                            class="mt-3 z-[1] card card-compact dropdown-content w-80 bg-base-100 shadow-xl border border-base-300">
                            <div class="card-body">
                                <span class="font-bold text-lg">Notifications</span>
                                <div class="divide-y divide-base-200">
                                    <div class="py-2">
                                        <p class="text-sm">Leave request pending approval</p>
                                        <p class="text-xs text-base-content/60">2 hours ago</p>
                                    </div>
                                    <div class="py-2">
                                        <p class="text-sm">New employee joined Operations</p>
                                        <p class="text-xs text-base-content/60">1 day ago</p>
                                    </div>
                                </div>
                                <div class="card-actions">
                                    <a href="#" class="btn btn-primary btn-block btn-sm">View all</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- User Profile -->
                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-ghost btn-circle avatar placeholder">
                            <div class="bg-primary text-primary-content rounded-full w-10">
                                <span class="text-sm">{{ substr(Auth::user()->name, 0, 2) }}</span>
                            </div>
                        </label>
                        <ul tabindex="0"
                            class="mt-3 z-[1] p-2 shadow menu menu-sm dropdown-content bg-base-100 rounded-box w-52 border border-base-300">
                            <li class="menu-title px-4 py-2">
                                <span class="text-primary font-semibold">{{ Auth::user()->name }}</span>
                                <span class="text-xs opacity-60">{{ Auth::user()->role_display }}</span>
                            </li>
                            <li><a href="{{ route('profile.edit') }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Profile
                                </a></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Page Content -->
            <main class="flex-grow p-4 md:p-6 bg-base-200">
                <!-- Breadcrumb -->
                @isset($breadcrumbs)
                    <div class="text-sm breadcrumbs mb-4">
                        <ul>
                            {{ $breadcrumbs }}
                        </ul>
                    </div>
                @endisset

                <!-- Page Header -->
                @isset($header)
                    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
                        <h1 class="text-2xl font-bold">{{ $header }}</h1>
                        @isset($actions)
                            <div class="mt-2 md:mt-0">
                                {{ $actions }}
                            </div>
                        @endisset
                    </div>
                @endisset

                {{ $slot }}
            </main>

            <!-- Footer -->
            <footer class="footer footer-center p-4 bg-base-100 text-base-content border-t border-base-300">
                <div>
                    <p>© {{ date('Y') }} Apex Attendance Management System</p>
                </div>
            </footer>
        </div>

        <!-- Sidebar -->
        <div class="drawer-side z-40">
            <label for="sidebar-drawer" class="drawer-overlay"></label>
            <aside class="w-64 min-h-screen bg-base-100 border-r border-base-300">
                <!-- Logo -->
                <div class="p-4 border-b border-base-300">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <div
                            class="w-10 h-10 rounded-lg bg-gradient-to-br from-primary to-secondary flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-xl font-bold">Apex</span>
                            <span class="text-xs block text-base-content/60">Attendance</span>
                        </div>
                    </a>
                </div>

                <ul class="menu p-4 gap-1">
                    <!-- Dashboard -->
                    <li>
                        <a href="{{ route('dashboard') }}"
                            class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Dashboard
                        </a>
                    </li>



                    @if(Auth::user()->isManager() || Auth::user()->hasAdminAccess())
                        <!-- Management -->
                        <li class="menu-title mt-4">
                            <span>Management</span>
                        </li>
                        <li>
                            <a href="{{ route('reports.attendance') }}"
                                class="sidebar-link {{ request()->routeIs('reports.attendance') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Employee Attendance
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('leave.approvals') }}"
                                class="sidebar-link {{ request()->routeIs('leave.approvals') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Approvals
                            </a>
                        </li>
                    @endif

                    @if(Auth::user()->hasAdminAccess())
                        <!-- Admin Section -->
                        <li class="menu-title mt-4">
                            <span>Administration</span>
                        </li>
                        <li>
                            <a href="{{ route('admin.employees.index') }}"
                                class="sidebar-link {{ request()->routeIs('admin.employees.*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                Employees
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.departments.index') }}"
                                class="sidebar-link {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                Departments
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.locations.index') }}"
                                class="sidebar-link {{ request()->routeIs('admin.locations.*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Locations
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('reports.index') }}"
                                class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Reports
                            </a>
                        </li>
                        <li>
                            <a href="#" class="sidebar-link">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Settings
                            </a>
                        </li>
                    @endif
                </ul>
            </aside>
        </div>
    </div>
</body>

</html>