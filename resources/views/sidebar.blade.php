<aside class="main-sidebar sidebar-dark-primary elevation-4">

    <!-- Brand Logo -->

    <a href="\" class="brand-link">

        <img src="{{ asset('images/TRACERS_2.png') }}" alt="MyAds Logo" class="brand-image img-circle elevation-2">


        @if(Auth::user()->role == 'User')

        <span class="brand-text font-weight-bold">CS</span>

        @else

        <span class="brand-text font-weight-bold">{{Auth::user()->role}}</span>

        @endif

    </a>

    <!-- Sidebar -->

    <div class="sidebar">

        <!-- Sidebar user (optional) -->

        <div class="user-panel mt-3 pb-3 mb-3 d-flex">

            <div class="info">

                <span class="badge badge-danger">{{ Str::limit(Auth::user()->name, 25) }}</span>

                <br>

                @if(Auth::user()->role == 'Admin' and Auth::user()->email == 'admin@telkomsel.co.id')

                <span class="badge badge-warning">SUPER ADMIN</span>

                @elseif(Auth::user()->role == 'Admin' and Auth::user()->email <> 'admin@telkomsel.co.id')

                    <span class="badge badge-warning">ADMIN {{Auth::user()->region}}</span>

                    @elseif(Auth::user()->role == 'User')

                    <span class="badge badge-warning">CS {{Auth::user()->grapari}}</span>
                    @elseif(Auth::user()->role == 'TL')

                    <span class="badge badge-warning">TL {{Auth::user()->grapari}}</span>
                    @elseif(Auth::user()->role == 'PIC')
                    @php
                    $nama_mitra = DB::table('mitra')->where('id', Auth::user()->mitra_id)->value('nama_mitra');
                    @endphp
                    <span class="badge badge-info">PIC {{ $nama_mitra }}</span>
                    @endif


            </div>

        </div>
        <!-- Sidebar Menu -->

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-header">ALL DASHBOARD</li>
                <li class="nav-item">
                    <a href="{{ route('admin.home') }}"
                        class="nav-link waves-effect {{ request()->routeIs('admin.home') ? 'active' : '' }}">
                        <i class="nav-icon fa-solid fa-bullseye" style="color:rgb(240, 236, 1);"></i>
                        <p>Home Dashboard</p>
                    </a>
                </li>
                <li class="nav-header">All Program</li>
                <li class="nav-item">
                    <a href="{{ route('admin.monitoring.padi_umkm') }}"
                        class="nav-link waves-effect {{ request()->routeIs('admin.monitoring.padi_umkm') ? 'active' : '' }}">
                        <i class="nav-icon fa-solid fa-shop" style="color: #4b66ffff;"></i>
                        <p>Padi UMKM</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.monitoring.event_sponsorship') }}"
                        class="nav-link waves-effect {{ request()->routeIs('admin.monitoring.event_sponsorship') ? 'active' : '' }}">
                        <i class="nav-icon fa-solid fa-handshake" style="color: #ff5733ff;"></i>
                        <p>Event Sponsorship</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.monitoring.creator_partner') }}"
                        class="nav-link waves-effect {{ request()->routeIs('admin.monitoring.creator_partner') ? 'active' : '' }}">
                        <i class="nav-icon fa-solid fa-user-pen" style="color: #efff5eff;"></i>
                        <p>Creator Partner</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.monitoring.rekruter_kol_buzzer') }}"
                        class="nav-link waves-effect {{ request()->routeIs('admin.monitoring.rekruter_kol_buzzer') ? 'active' : '' }}">
                        <i class="nav-icon fa-solid fa-user-pen" style="color: #efff5eff;"></i>
                        <p>Rekruter KOL Buzzer</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.monitoring.simpati_tiktok') }}"
                        class="nav-link waves-effect {{ request()->routeIs('admin.monitoring.simpati_tiktok') ? 'active' : '' }}">
                        <i class="nav-icon fa-brands fa-tiktok" style="color: #fe0404ff;"></i>
                        <p>Simpati Tiktok</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.monitoring.referral_champion') }}"
                        class="nav-link waves-effect {{ request()->routeIs('admin.monitoring.referral_champion') ? 'active' : '' }}">
                        <i class="nav-icon fa-solid fa-user-check" style="color: #42f554ff;"></i>
                        <p>Referral Champion</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.monitoring.sultam_racing') }}"
                        class="nav-link waves-effect {{ request()->routeIs('admin.monitoring.sultam_racing') ? 'active' : '' }}">
                        <i class="nav-icon fa-solid fa-car" style="color: #f39c12;"></i>
                        <p>Sultam Racing</p>
                    </a>
                </li>
                <li class="nav-header">Upload File</li>
                <li class="nav-item">
                    <a href="{{ route('admin.upload') }}"
                        class="nav-link waves-effect {{ request()->routeIs('admin.upload') ? 'active' : '' }}">
                        <i class="nav-icon fa-solid fa-bullseye" style="color:rgb(240, 236, 1);"></i>
                        <p>Revenue & Program</p>
                    </a>
                </li>

                <!-- Logout -->
                <li class="nav-header">LOGOUT</li>

                <li class="nav-item">

                    <a href="{{url('logout')}}"
                        class="nav-link waves-effect {{ request()->routeIs('logout') ? 'active' : '' }}">

                        <i class="nav-icon fas fa-sign-out-alt" style="color:rgb(239, 21, 21);"></i>

                        <p>Logout</p>

                    </a>

                </li>

            </ul>

        </nav>

        <!-- /.sidebar-menu -->

    </div>

    <!-- /.sidebar -->

</aside>