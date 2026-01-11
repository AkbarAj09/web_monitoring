<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ url('/') }}" class="brand-link">
        <img src="{{ asset('images/TRACERS_2.png') }}" alt="MyAds Logo" class="brand-image img-circle elevation-2">
        <span class="brand-text font-weight-bold">{{ Auth::user()->role }}</span>
    </a>

    <div class="sidebar">
        <!-- Sidebar user -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="info">
                <span class="badge badge-danger">{{ Str::limit(Auth::user()->name, 25) }}</span><br>
                @php
                $user = Auth::user();
                $isAdmin = $user->role === 'Admin';
                $isTsel = $user->role === 'Tsel';
                $isTreg = $user->role === 'Treg';
                $isCanv = $user->role === 'cvsr';
                $isPH = $user->role === 'PH';
                @endphp

                @if($isAdmin && $user->email === 'admin@telkomsel.co.id')
                <span class="badge badge-warning">SUPER ADMIN</span>
                @elseif($isAdmin)
                <span class="badge badge-warning">ADMIN</span>
                @elseif($isTsel)
                <span class="badge badge-success">TSEL</span>
                @elseif($isCanv)
                <span class="badge badge-primary">CANVASSER</span>
                @elseif($isTreg)
                @php
                $treg_name = DB::table('treg')->where('id', $user->treg_id)->value('treg_name');
                @endphp
                <span class="badge badge-info">TREG {{ $treg_name ?? '-' }}</span>
                @endif
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                {{-- ===== ADMIN: bisa akses semua menu (ALL + TREG) ===== --}}
                @if($isAdmin || $isTsel || $isCanv)
                <li class="nav-header">ALL DASHBOARD</li>
                <li class="nav-item">
                    <a href="{{ route('admin.home') }}"
                        class="nav-link waves-effect {{ request()->routeIs('admin.home') ? 'active' : '' }}">
                        <i class="nav-icon fa-solid fa-table-cells" style="color:rgb(255, 255, 255);"></i>
                        <p>Utama</p>
                    </a>
                </li>
                @endif


                {{-- <li class="nav-header">Report</li> --}}
                <li class="nav-item">
                    <a href="{{ route('leads-master.index') }}"
                        class="nav-link waves-effect {{ request()->routeIs('leads-master.index') ? 'active' : '' }}">
                        <i class="nav-icon fa-solid fa-star" style="color:rgb(240,236,1);"></i>
                        <p>Data Leads & Akun</p>
                    </a>
                </li>     
                <li class="nav-item">
                    <a href="{{ route('leads-master.create') }}"
                        class="nav-link waves-effect {{ request()->routeIs('leads-master.create') ? 'active' : '' }}">
                        <i class="nav-icon fa-solid fa-user-pen" style="color:rgb(1, 240, 172);"></i>
                        <p>New Leads</p>
                    </a>
                </li>      
                <li class="nav-item">
                    <a href="{{ route('leads-master.create-existing') }}"
                        class="nav-link waves-effect {{ request()->routeIs('leads-master.create-existing') ? 'active' : '' }}">
                        <i class="nav-icon fa-solid fa-user-tie" style="color:rgb(143, 142, 142);"></i>
                        <p>Akun Eksisting</p>
                    </a>
                </li>             
                <li class="nav-item">
                    <a href="{{ route('leads-master.index') }}"
                        class="nav-link waves-effect {{ request()->routeIs('leads-master.index') ? '' : '' }}">
                        <i class="nav-icon fa-solid fa-book" style="color:rgb(90,90,250);"></i>
                        <p>Logbook</p>
                    </a>
                </li>           
                {{-- <li class="nav-item">
                    <a href="{{ route('topup-canvasser') }}"
                        class="nav-link waves-effect {{ request()->routeIs('topup-canvasser') ? 'active' : '' }}">
                        <i class="nav-icon fa-solid fa-money-bill" style="color:rgb(80, 255, 80);"></i>
                        <p>Topup & Client Canvasser</p>
                    </a>
                </li>           --}}
                <li class="nav-item">
                    <a href="{{ route('region-target') }}"
                        class="nav-link waves-effect {{ request()->routeIs('region-target') ? 'active' : '' }}">
                        <i class="nav-icon fa-solid fa-chart-line" style="color:rgb(240, 37, 1);"></i>
                        <p>Region Target Topup</p>
                    </a>
                </li>   
                {{-- <li class="nav-item">
                    <a href="{{ route('logbook.index') }}"
                        class="nav-link waves-effect {{ request()->routeIs('logbook.index') ? 'active' : '' }}">
                        <i class="nav-icon fa-solid fa-book" style="color:rgb(118, 129, 255);"></i>
                        <p>New Logbook</p>
                    </a>
                </li> --}}
                {{-- ===== Menu khusus ADMIN ===== --}}
                {{-- @if($isAdmin)
                <li class="nav-header">Upload File</li>
                <li class="nav-item">
                    <a href="{{ route('admin.upload') }}"
                        class="nav-link waves-effect {{ request()->routeIs('admin.upload') ? 'active' : '' }}">
                        <i class="nav-icon fa-solid fa-bullseye" style="color:rgb(240,236,1);"></i>
                        <p>Revenue & Program</p>
                    </a>
                </li>
                @endif --}}

                @if($isAdmin || $isTreg || $isTsel || $isCanv)
                <li class="nav-header">System Management</li>
                @endif
                @if($isAdmin)
                <li class="nav-item">
                    <a href="{{ route('users.page') }}"
                        class="nav-link waves-effect {{ request()->routeIs('users.page') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-users-cog" style="color:#28a745;"></i>
                        <p>Manajemen Users</p>
                    </a>
                </li>
                @endif
                @if($isAdmin || $isTreg || $isTsel || $isCanv)
                <li class="nav-item">
                    <a href="{{ url('change-password') }}"
                        class="nav-link waves-effect {{ request()->routeIs('change-password') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-key" style="color:rgb(173, 176, 86);"></i>
                        <p>Change Password</p>
                    </a>
                </li>
                @endif



                {{-- ===== Logout untuk semua role yang ditangani di atas ===== --}}
                @if($isAdmin || $isTreg || $isTsel || $isCanv)
                <li class="nav-header">LOGOUT</li>
                <li class="nav-item">
                    <a href="{{ url('logout') }}"
                        class="nav-link waves-effect {{ request()->routeIs('logout') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-sign-out-alt" style="color:rgb(239,21,21);"></i>
                        <p>Logout</p>
                    </a>
                </li>
                @endif
            </ul>
        </nav>
    </div>
</aside>