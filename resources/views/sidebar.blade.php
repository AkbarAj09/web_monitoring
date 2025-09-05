<aside class="main-sidebar sidebar-dark-primary elevation-4">

    <!-- Brand Logo -->

    <a href="\" class="brand-link">

        <img src="{{ asset('images/myads_logo.png') }}" alt="MyAds Logo" class="brand-image img-circle elevation-2">
    

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