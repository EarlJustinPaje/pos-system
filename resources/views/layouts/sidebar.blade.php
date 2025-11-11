<nav id="sidebar">
    <ul class="list-unstyled components">
        
        @if(auth()->user()->hasPermission(\App\Models\Permission::VIEW_DASHBOARD))
            <li>
                <a href="{{ route('dashboard') }}">Dashboard</a>
            </li>
        @endif

        @if(auth()->user()->hasPermission(\App\Models\Permission::VIEW_POS))
            <li>
                <a href="{{ route('pos.index') }}">POS</a>
            </li>
        @endif

        @if(!auth()->user()->isCashier())
            @if(auth()->user()->hasPermission(\App\Models\Permission::VIEW_PRODUCTS))
                <li>
                    <a href="#productSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">Products</a>
                    <ul class="collapse list-unstyled" id="productSubmenu">
                        <li><a href="{{ route('products.index') }}">Product List</a></li>
                        
                        @if(auth()->user()->hasPermission(\App\Models\Permission::CREATE_PRODUCTS))
                            <li><a href="{{ route('products.create') }}">Add Product</a></li>
                        @endif

                        @if(auth()->user()->hasPermission(\App\Models\Permission::MANAGE_CATEGORIES))
                            <li><a href="{{ route('categories.index') }}">Categories</a></li>
                        @endif

                        @if(auth()->user()->hasPermission(\App\Models\Permission::IMPORT_PRODUCTS))
                            <li><a href="{{ route('products.imports.index') }}">Bulk Import</a></li>
                        @endif
                    </ul>
                </li>
            @endif

            <li>
                <a href="#inventorySubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">Inventory</a>
                <ul class="collapse list-unstyled" id="inventorySubmenu">
                    @if(auth()->user()->hasPermission(\App\Models\Permission::MANAGE_SUPPLIERS))
                        <li><a href="{{ route('suppliers.index') }}">Suppliers</a></li>
                    @endif
                    
                    @if(auth()->user()->hasPermission(\App\Models\Permission::VIEW_FORECASTING))
                        <li><a href="{{ route('forecasting.reorder-alerts') }}">Reorder Alerts</a></li>
                        <li><a href="{{ route('forecasting.index') }}">Demand Forecast</a></li>
                    @endif
                </ul>
            </li>

            @if(auth()->user()->hasPermission(\App\Models\Permission::VIEW_REPORTS))
                <li>
                    <a href="{{ route('reports.index') }}">Reports</a>
                </li>
            @endif

            @if(auth()->user()->isAdmin())
                <li class="menu-header">Admin Settings</li>
                
                @if(auth()->user()->hasPermission(\App\Models\Permission::VIEW_USERS))
                    <li>
                        <a href="{{ route('users.index') }}">User Management</a>
                    </li>
                @endif

                <li>
                    <a href="#storeSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">Store Settings</a>
                    <ul class="collapse list-unstyled" id="storeSubmenu">
                        @if(auth()->user()->hasPermission(\App\Models\Permission::MANAGE_BRANCHES))
                            <li><a href="{{ route('branches.index') }}">Branches</a></li>
                        @endif

                        @if(auth()->user()->hasPermission(\App\Models\Permission::MANAGE_PROMOTIONS))
                            <li><a href="{{ route('promotions.index') }}">Promotions</a></li>
                        @endif

                        @if(auth()->user()->hasPermission(\App\Models\Permission::MANAGE_PAYMENT_METHODS))
                            <li><a href="{{ route('payment-methods.index') }}">Payment Methods</a></li>
                        @endif
                    </ul>
                </li>

                <li>
                    <a href="#systemSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">System</a>
                    <ul class="collapse list-unstyled" id="systemSubmenu">
                        @if(auth()->user()->hasPermission(\App\Models\Permission::MANAGE_SETTINGS))
                            <li><a href="{{ route('settings.index') }}">Global Settings</a></li>
                        @endif

                        @if(auth()->user()->hasPermission(\App\Models\Permission::VIEW_AUDIT))
                            <li><a href="{{ route('audit.index') }}">Audit Trail</a></li>
                        @endif
                    </ul>
                </li>
            @endif 
        @endif <li>
            @php
                $unreadCount = auth()->user()->getUnreadNotificationsCount();
            @endphp
            <a href="{{ route('notifications.index') }}">
                Notifications 
                @if($unreadCount > 0)
                    <span class="badge badge-danger">{{ $unreadCount }}</span>
                @endif
            </a>
        </li>
    </ul>
</nav>