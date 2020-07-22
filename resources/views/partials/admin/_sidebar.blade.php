<!-- ============================================================== -->
<!-- Left Sidebar - style you can find in sidebar.scss  -->
<!-- ============================================================== -->
<aside class="left-sidebar" data-sidebarbg="skin5">
    <!-- Sidebar scroll-->
    <div class="scroll-sidebar">
        <!-- Sidebar navigation-->
        <nav class="sidebar-nav">
            <ul id="sidebarnav" class="pt-3 mt-3" style="border-top: 1px solid #eeeeee;">
                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark sidebar-link" href="{{ route('admin.dashboard') }}"
                        aria-expanded="false">
                        <i class="mdi mdi-view-dashboard"></i>
                        <span class="hide-menu">Dashboard</span>
                    </a>
                </li>

{{--                @foreach($menuHeader as $header)--}}
{{--                <li class="sidebar-item">--}}
{{--                    <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)"--}}
{{--                        aria-expanded="false">--}}
{{--                        <i class="{{ $header->icon }}"></i>--}}
{{--                        <span class="hide-menu">{!! $header->name !!} </span>--}}
{{--                    </a>--}}
{{--                    <ul aria-expanded="false" class="collapse first-level">--}}
{{--                        @foreach($header->menu_header->menus as $menu)--}}
{{--                        <li class="sidebar-item">--}}
{{--                            <a href="{{ route($menu->route) }}" class="sidebar-link">--}}
{{--                                <i class="{{ $menu->icon }}"></i>--}}
{{--                                <span class="hide-menu"> {{ $menu->name }} </span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        @endforeach--}}
{{--                    </ul>--}}
{{--                </li>--}}
{{--                @endforeach--}}



                <li class="sidebar-item">
                    <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)"
                        aria-expanded="false">
                        <i class="mdi mdi-account-settings-variant"></i>
                        <span class="hide-menu"> Project </span>
                    </a>
                    <ul aria-expanded="false" class="collapse  first-level">
                        <li class="sidebar-item">
                            <a href="{{ route('admin.project.information.index') }}" class="sidebar-link">
                                <i class="mdi mdi-account"></i>
                                <span class="hide-menu"> Daftar Project </span>
                            </a>
                        </li>
{{--                        <li class="sidebar-item">--}}
{{--                            <a href="{{ route('admin.project.information.create') }}" class="sidebar-link">--}}
{{--                                <i class="mdi mdi-account"></i>--}}
{{--                                <span class="hide-menu"> Tambah Project </span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
                    </ul>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)"
                        aria-expanded="false">
                        <i class="mdi mdi-account-settings-variant"></i>
                        <span class="hide-menu">Karyawan </span>
                    </a>
                    <ul aria-expanded="false" class="collapse  first-level">
                        <li class="sidebar-item">
                            <a href="{{ route('admin.employee.index') }}" class="sidebar-link">
                                <i class="mdi mdi-account"></i>
                                <span class="hide-menu"> Daftar Karyawan </span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('admin.employee.create') }}" class="sidebar-link">
                                <i class="mdi mdi-account"></i>
                                <span class="hide-menu"> Buat Upper Management</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('admin.employee_role.index') }}" class="sidebar-link">
                                <i class="mdi mdi-account"></i>
                                <span class="hide-menu"> Daftar Jabatan Karyawan </span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('admin.employee_role.create') }}" class="sidebar-link">
                                <i class="mdi mdi-account"></i>
                                <span class="hide-menu"> Buat Jabatan Karyawan Baru</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)"
                        aria-expanded="false">
                        <i class="mdi mdi-account-settings-variant"></i>
                        <span class="hide-menu">Customer </span>
                    </a>
                    <ul aria-expanded="false" class="collapse  first-level">
                        <li class="sidebar-item">
                            <a href="{{route('admin.customer.index')}}" class="sidebar-link">
                                <i class="mdi mdi-account"></i>
                                <span class="hide-menu"> Daftar Customer </span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{route('admin.customer.create')}}" class="sidebar-link">
                                <i class="mdi mdi-account"></i>
                                <span class="hide-menu"> Buat Customer Baru</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)"
                        aria-expanded="false">
                        <i class="mdi mdi-account-settings-variant"></i>
                        <span class="hide-menu">User Admin </span>
                    </a>
                    <ul aria-expanded="false" class="collapse  first-level">
                        <li class="sidebar-item">
                            <a href="{{route('admin.admin-users.index')}}" class="sidebar-link">
                                <i class="mdi mdi-account"></i>
                                <span class="hide-menu"> Daftar Admin </span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{route('admin.admin-users.create')}}" class="sidebar-link">
                                <i class="mdi mdi-account"></i>
                                <span class="hide-menu"> Buat Admin Baru</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)"
                        aria-expanded="false">
                        <i class="mdi mdi-account-settings-variant"></i>
                        <span class="hide-menu">Place </span>
                    </a>
                    <ul aria-expanded="false" class="collapse  first-level">
                        <li class="sidebar-item">
                            <a href="{{route('admin.place.index')}}" class="sidebar-link">
                                <i class="mdi mdi-account"></i>
                                <span class="hide-menu"> Daftar Place </span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{route('admin.place.create')}}" class="sidebar-link">
                                <i class="mdi mdi-account"></i>
                                <span class="hide-menu"> Buat Place Baru</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)"
                        aria-expanded="false">
                        <i class="mdi mdi-account-settings-variant"></i>
                        <span class="hide-menu"> Object </span>
                    </a>
                    <ul aria-expanded="false" class="collapse  first-level">
                        <li class="sidebar-item">
                            <a href="{{route('admin.unit.index')}}" class="sidebar-link">
                                <i class="mdi mdi-account"></i>
                                <span class="hide-menu"> Daftar Object </span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{route('admin.sub1unit.index')}}" class="sidebar-link">
                                <i class="mdi mdi-account"></i>
                                <span class="hide-menu"> Daftar Sub Object 1 </span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{route('admin.sub2unit.index')}}" class="sidebar-link">
                                <i class="mdi mdi-account"></i>
                                <span class="hide-menu"> Daftar Sub Object 2</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{route('admin.unit.create')}}" class="sidebar-link">
                                <i class="mdi mdi-account"></i>
                                <span class="hide-menu"> Tambah Object </span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{route('admin.sub1unit.create')}}" class="sidebar-link">
                                <i class="mdi mdi-account"></i>
                                <span class="hide-menu"> Tambah Sub Object 1 </span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{route('admin.sub2unit.create')}}" class="sidebar-link">
                                <i class="mdi mdi-account"></i>
                                <span class="hide-menu"> Tambah Sub Object 2 </span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)"
                        aria-expanded="false">
                        <i class="mdi mdi-account-settings-variant"></i>
                        <span class="hide-menu"> Action </span>
                    </a>
                    <ul aria-expanded="false" class="collapse  first-level">
                        <li class="sidebar-item">
                            <a href="{{route('admin.action.index')}}" class="sidebar-link">
                                <i class="mdi mdi-account"></i>
                                <span class="hide-menu"> Daftar Action </span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{route('admin.action.create')}}" class="sidebar-link">
                                <i class="mdi mdi-account"></i>
                                <span class="hide-menu"> Tambah Action </span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)"
                       aria-expanded="false">
                        <i class="mdi mdi-exclamation"></i>
                        <span class="hide-menu"> Keluhan </span>
                    </a>
                    <ul aria-expanded="false" class="collapse  first-level">
                        <li class="sidebar-item">
                            <a href="{{route('admin.complaint.index', ['type' => 'customers'])}}" class="sidebar-link">
                                <i class="mdi mdi-format-list-bulleted"></i>
                                <span class="hide-menu"> Daftar Keluhan Customer </span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{route('admin.complaint.index', ['type' => 'internals'])}}" class="sidebar-link">
                                <i class="mdi mdi-format-list-bulleted"></i>
                                <span class="hide-menu"> Daftar Keluhan Internal </span>
                            </a>
                        </li>
{{--                        <li class="sidebar-item">--}}
{{--                            <a href="{{route('admin.complaint.index', ['type' => 'others'])}}" class="sidebar-link">--}}
{{--                                <i class="mdi mdi-format-list-bulleted"></i>--}}
{{--                                <span class="hide-menu"> Daftar Keluhan Other</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
                    </ul>
                </li>
            </ul>
        </nav>
        <!-- End Sidebar navigation -->
    </div>
    <!-- End Sidebar scroll-->
</aside>
<!-- ============================================================== -->
<!-- End Left Sidebar - style you can find in sidebar.scss  -->
<!-- ============================================================== -->
