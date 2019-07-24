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
                    <a class="sidebar-link waves-effect waves-dark sidebar-link" href="{{ route('admin.dashboard') }}" aria-expanded="false">
                        <i class="mdi mdi-view-dashboard"></i>
                        <span class="hide-menu">Dashboard</span>
                    </a>
                </li>

                @foreach($menuHeader as $header)
                    <li class="sidebar-item">
                        <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false">
                            <i class="{{ $header->icon }}"></i>
                            <span class="hide-menu">{!! $header->name !!} </span>
                        </a>
                        <ul aria-expanded="false" class="collapse first-level">
                            @foreach($header->menu_header->menus as $menu)
                                <li class="sidebar-item">
                                    <a href="{{ route($menu->route) }}" class="sidebar-link">
                                        <i class="{{ $menu->icon }}"></i>
                                        <span class="hide-menu"> {{ $menu->name }} </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endforeach


                <li class="sidebar-item">
                    <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false">
                        <i class="mdi mdi-account-settings-variant"></i>
                        <span class="hide-menu">Master Dealer </span>
                    </a>
                    <ul aria-expanded="false" class="collapse  first-level">
                        <li class="sidebar-item">
                            <a href="{{route('admin.users.index')}}" class="sidebar-link">
                                <i class="mdi mdi-account"></i>
                                <span class="hide-menu"> Daftar MD </span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{route('admin.users.create')}}" class="sidebar-link">
                                <i class="mdi mdi-account"></i>
                                <span class="hide-menu"> Buat MD Baru</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{route('admin.user_categories.index')}}" class="sidebar-link">
                                <i class="mdi mdi-account"></i>
                                <span class="hide-menu"> Daftar Kategori MD </span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{route('admin.user_categories.create')}}" class="sidebar-link">
                                <i class="mdi mdi-account"></i>
                                <span class="hide-menu"> Buat Kategori MD Baru</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false">
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
                    <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false">
                        <i class="mdi mdi-account-settings-variant"></i>
                        <span class="hide-menu"> Produk </span>
                    </a>
                    <ul aria-expanded="false" class="collapse  first-level">
                        <li class="sidebar-item">
                            <a href="{{route('admin.product.index')}}" class="sidebar-link">
                                <i class="mdi mdi-account"></i>
                                <span class="hide-menu"> Produk </span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{route('admin.product.category.index')}}" class="sidebar-link">
                                <i class="mdi mdi-account"></i>
                                <span class="hide-menu"> Kategori Produk </span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{route('admin.product.brand.index')}}" class="sidebar-link">
                                <i class="mdi mdi-account"></i>
                                <span class="hide-menu"> Brand Produk </span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false">
                        <i class="mdi mdi-account-settings-variant"></i>
                        <span class="hide-menu">Order Request </span>
                    </a>
                    <ul aria-expanded="false" class="collapse  first-level">
                        <li class="sidebar-item">
                            <a href="{{route('admin.orders.index')}}" class="sidebar-link">
                                <i class="mdi mdi-account"></i>
                                <span class="hide-menu"> Daftar OR </span>
                            </a>
                        </li>
                    </ul>
                </li>

{{--                <li class="sidebar-item">--}}
{{--                    <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false">--}}
{{--                        <i class="mdi mdi-book"></i>--}}
{{--                        <span class="hide-menu">Transaksi Penjemputan<br/>Rutin</span>--}}
{{--                    </a>--}}
{{--                    <ul aria-expanded="false" class="collapse  first-level">--}}
{{--                        <li class="sidebar-item">--}}
{{--                            <a href="{{ route('admin.transactions.penjemputan_rutin.index') }}" class="sidebar-link">--}}
{{--                                <i class="mdi mdi-book-multiple"></i>--}}
{{--                                <span class="hide-menu"> Daftar Transaksi </span>--}}
{{--                            </a>--}}
{{--                            <a href="{{ route('admin.user.penjemputan_rutin.index') }}" class="sidebar-link">--}}
{{--                                <i class="mdi mdi-book-multiple"></i>--}}
{{--                                <span class="hide-menu"> Daftar User </span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                    </ul>--}}
{{--                </li>--}}

{{--                <li class="sidebar-item">--}}
{{--                    <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false">--}}
{{--                        <i class="mdi mdi-book"></i>--}}
{{--                        <span class="hide-menu">Transaksi Antar Sendiri</span>--}}
{{--                    </a>--}}
{{--                    <ul aria-expanded="false" class="collapse  first-level">--}}
{{--                        <li class="sidebar-item">--}}
{{--                            <a href="{{ route('admin.transactions.antar_sendiri.index') }}" class="sidebar-link">--}}
{{--                                <i class="mdi mdi-book-multiple"></i>--}}
{{--                                <span class="hide-menu"> Daftar Transaksi </span>--}}
{{--                            </a>--}}
{{--                            <a href="{{ route('admin.transactions.antar_sendiri.dws.create') }}" class="sidebar-link">--}}
{{--                                <i class="fas fa-plus"></i>--}}
{{--                                <span class="hide-menu"> Transaksi Kategori DWS </span>--}}
{{--                            </a>--}}
{{--                            <a href="{{ route('admin.transactions.antar_sendiri.masaro.create') }}" class="sidebar-link">--}}
{{--                                <i class="fas fa-plus"></i>--}}
{{--                                <span class="hide-menu"> Transaksi Kategori Masaro </span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                    </ul>--}}
{{--                </li>--}}

{{--                <li class="sidebar-item">--}}
{{--                    <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false">--}}
{{--                        <i class="mdi mdi-book"></i>--}}
{{--                        <span class="hide-menu">Transaksi Penjemputan<br/>Sekarang</span>--}}
{{--                    </a>--}}
{{--                    <ul aria-expanded="false" class="collapse  first-level">--}}
{{--                        <li class="sidebar-item">--}}
{{--                            <a href="{{ route('admin.transactions.on_demand.list') }}" class="sidebar-link">--}}
{{--                                <i class="mdi mdi-book-multiple"></i>--}}
{{--                                <span class="hide-menu"> Daftar Transaksi Baru </span>--}}
{{--                            </a>--}}
{{--                            <a href="{{ route('admin.transactions.on_demand.index') }}" class="sidebar-link">--}}
{{--                                <i class="mdi mdi-book-multiple"></i>--}}
{{--                                <span class="hide-menu"> Riwayat Transaksi </span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                    </ul>--}}
{{--                </li>--}}

{{--                <li class="sidebar-item">--}}
{{--                    <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false">--}}
{{--                        <i class="mdi mdi-book"></i>--}}
{{--                        <span class="hide-menu">Vouchers</span>--}}
{{--                    </a>--}}
{{--                    <ul aria-expanded="false" class="collapse  first-level">--}}
{{--                        <li class="sidebar-item">--}}
{{--                            <a href="{{ route('admin.voucher.users.index') }}" class="sidebar-link">--}}
{{--                                <i class="mdi mdi-book-multiple"></i>--}}
{{--                                <span class="hide-menu"> Daftar Penggunaan Voucher </span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="sidebar-item">--}}
{{--                            <a href="{{ route('admin.vouchers.index') }}" class="sidebar-link">--}}
{{--                                <i class="mdi mdi-book-multiple"></i>--}}
{{--                                <span class="hide-menu"> Daftar Voucher </span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="sidebar-item">--}}
{{--                            <a href="{{ route('admin.voucher-categories.index') }}" class="sidebar-link">--}}
{{--                                <i class="mdi mdi-book-multiple"></i>--}}
{{--                                <span class="hide-menu"> Daftar Kategori Voucher </span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                    </ul>--}}
{{--                </li>--}}

{{--                <li class="sidebar-item">--}}
{{--                    <a class="sidebar-link waves-effect waves-dark sidebar-link" href="{{ route('admin.points.index') }}" aria-expanded="false">--}}
{{--                        <i class="mdi mdi-view-dashboard"></i>--}}
{{--                        <span class="hide-menu">Point</span>--}}
{{--                    </a>--}}
{{--                </li>--}}

{{--                <li class="sidebar-item">--}}
{{--                    <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false">--}}
{{--                        <i class="mdi mdi-book"></i>--}}
{{--                        <span class="hide-menu">Waste Collector</span>--}}
{{--                    </a>--}}
{{--                    <ul aria-expanded="false" class="collapse  first-level">--}}
{{--                        <li class="sidebar-item">--}}
{{--                            <a href="{{ route('admin.wastecollectors.index') }}" class="sidebar-link">--}}
{{--                                <i class="mdi mdi-book-multiple"></i>--}}
{{--                                <span class="hide-menu"> Daftar Waste Collector </span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="sidebar-item">--}}
{{--                            <a href="{{ route('admin.wastecollectors.create') }}" class="sidebar-link">--}}
{{--                                <i class="fas fa-plus"></i>--}}
{{--                                <span class="hide-menu"> Tambah Baru </span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                    </ul>--}}
{{--                </li>--}}

{{--                <li class="sidebar-item">--}}
{{--                    <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false">--}}
{{--                        <i class="mdi mdi-account-settings-variant"></i>--}}
{{--                        <span class="hide-menu">Master Data </span>--}}
{{--                    </a>--}}
{{--                    <ul aria-expanded="false" class="collapse  first-level">--}}
{{--                        <li class="sidebar-item">--}}
{{--                            <a href="{{ route('admin.admin-users.index') }}" class="sidebar-link">--}}
{{--                                <i class="mdi mdi-account-star-variant"></i>--}}
{{--                                <span class="hide-menu"> Admin Users </span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="sidebar-item">--}}
{{--                            <a href="{{ route('admin.waste-banks.index') }}" class="sidebar-link">--}}
{{--                                <i class="mdi mdi-note-outline"></i>--}}
{{--                                <span class="hide-menu"> Waste Processor </span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="sidebar-item">--}}
{{--                            <a href="{{ route('admin.wastebanks-radius.setting') }}" class="sidebar-link">--}}
{{--                                <i class="mdi mdi-note-outline"></i>--}}
{{--                                <span class="hide-menu"> Waste Processor Radius </span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="sidebar-item">--}}
{{--                            <a href="{{ route('admin.setting') }}" class="sidebar-link">--}}
{{--                                <i class="mdi mdi-note-plus"></i>--}}
{{--                                <span class="hide-menu"> Category Setting </span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="sidebar-item">--}}
{{--                            <a href="{{ route('admin.dws-wastes.index') }}" class="sidebar-link">--}}
{{--                                <i class="mdi mdi-note-plus"></i>--}}
{{--                                <span class="hide-menu"> Dws Waste Categories </span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="sidebar-item">--}}
{{--                            <a href="{{ route('admin.dws-waste-items.index') }}" class="sidebar-link">--}}
{{--                                <i class="mdi mdi-note-plus"></i>--}}
{{--                                <span class="hide-menu"> Dws Waste Items </span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="sidebar-item">--}}
{{--                            <a href="{{ route('admin.masaro-wastes.index') }}" class="sidebar-link">--}}
{{--                                <i class="mdi mdi-note-plus"></i>--}}
{{--                                <span class="hide-menu"> Masaro Waste Categories </span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                    </ul>--}}
{{--                </li>--}}
            </ul>
        </nav>
        <!-- End Sidebar navigation -->
    </div>
    <!-- End Sidebar scroll-->
</aside>
<!-- ============================================================== -->
<!-- End Left Sidebar - style you can find in sidebar.scss  -->
<!-- ============================================================== -->