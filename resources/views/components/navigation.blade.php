<div>
    <nav class="sidebar-nav">
        <ul id="sidebarnav">
            </li>
            <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-layout-grid2"></i><span class="hide-menu">Master</span></a>
                <ul aria-expanded="false" class="collapse">
                    @if(Session::get('role_name')=='superadmin' || Session::get('role_name')=='admin')
                    <li class="border-bottom"><a href="{{ route('users.index') }}">Users list</a></li>
                    <li class="border-bottom"><a href="{{ route('employees.index') }}">Employees list</a></li>
                    @endif
                    <li class="border-bottom"><a href="{{ route('countries.index') }}">Countries list</a></li>
                    <li class="border-bottom"><a href="{{ route('states.index') }}">States list</a></li>
                    <li class="border-bottom"><a href="{{ route('cities.index') }}">Cities list</a></li>
                    <li class="border-bottom"><a href="{{ route('regions.index') }}">Regions list</a></li>

{{--                    <li class="border-bottom"><a href="{{ route('units.index') }}">Units list</a></li>--}}
{{--                    <li class="border-bottom"><a href="{{ route('products.index') }}">Products list</a></li>--}}
{{--                    <li class="border-bottom"><a href="{{ route('company_types.index') }}">Company Type list</a></li>--}}
{{--                    <li class="border-bottom"><a href="{{ route('payment_types.index') }}">Payment Type list</a></li>--}}
                    @if(Session::get('role_name')=='superadmin')
                        <li class="border-bottom"><a href="{{ route('companies.index') }}">Companies list</a></li>
                        <li class="border-bottom"><a href="{{ route('roles.index') }}">Roles list</a></li>
                        <li class="border-bottom"><a href="{{ route('banks.index') }}">Banks list</a></li>
                    @endif
                    <li><a href="{{ route('payment_terms.index') }}">Payment Terms list</a></li>
                </ul>
            </li>

            <li > <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-user"></i><span class="hide-menu">Contacts</span></a>
                <ul aria-expanded="false" class="collapse">
                    <li class="border-bottom"><a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-files"></i><span class="hide-menu">Customers</span>
                        </a>
                        <ul aria-expanded="false" class="collapse">
                            <li class="border-bottom"><a href="{{ route('customers.create') }}">Add New Customer</a></li>
                            <li class="border-bottom"><a href="{{ route('customers.index') }}">Manage Customers</a></li>
                            <li><a href="{{ route('customer_prices.index') }}">Manage Prices</a></li>
                        </ul>
                    </li>
                    @if(Session::get('role_name')=='superadmin' || Session::get('role_name')=='admin')
                    <li class="border-bottom"><a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-files"></i><span class="hide-menu">Suppliers</span>
                        </a>
                        <ul aria-expanded="false" class="collapse">
                            <li class="border-bottom"><a href="{{ route('suppliers.create') }}">Add New Supplier</a></li>
                            <li class="border-bottom"><a href="{{ route('suppliers.index') }}">Manage Suppliers</a></li>
                        </ul>
                    </li>
                    @endif

                    @if(Session::get('role_name')=='superadmin' || Session::get('role_name')=='admin')
                        <li><a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-files"></i><span class="hide-menu">Financer</span>
                            </a>
                            <ul aria-expanded="false" class="collapse">
                                <li><a href="{{ route('financer.index') }}">Manage Financer</a></li>
                            </ul>
                        </li>
                    @endif
                </ul>
            </li>

            <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-car"></i><span class="hide-menu">Vehicles</span></a>
                <ul aria-expanded="false" class="collapse">
                    <li class="border-bottom"><a href="{{ route('vehicles.create') }}">Add new Vehicle</a></li>
                    <li class="border-bottom"><a href="{{ route('vehicles.index') }}">Manage Vehicles</a></li>
                    <li><a href="{{ route('getVehicleList') }}">Print Vehicles List</a></li>
                </ul>
            </li>

            <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-drivers-license"></i><span class="hide-menu">Drivers</span></a>
                <ul aria-expanded="false" class="collapse">
                    <li  class="border-bottom"><a href="{{ route('drivers.create') }}">Add new Driver</a></li>
                    <li><a href="{{ route('drivers.index') }}">Manage Drivers</a></li>
                </ul>
            </li>

            @if(Session::get('role_name')=='superadmin' || Session::get('role_name')=='admin')
            <li>
                <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-shopping-basket"></i><span class="hide-menu">Purchase</span></a>
                <ul aria-expanded="false" class="collapse">
                    <li  class="border-bottom"><a href="{{ route('purchases.create') }}">Add Purchase</a></li>
                    <li><a href="{{ route('purchases.index') }}">Manage Purchase</a></li>
                </ul>
            </li>
            @endif

            <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-cart-plus"></i><span class="hide-menu">Sales</span></a>
                <ul aria-expanded="false" class="collapse">
                    <li class="border-bottom"><a href="{{ route('sales.create') }}">Add Sales</a></li>
                    <li class="border-bottom"><a href="{{ route('get_today_sale') }}">Today Sales</a></li>
                    <li class="border-bottom"><a href="{{ route('get_sale_of_date') }}">Sales of Date</a></li>
                    <li><a href="{{ route('sales.index') }}">Manage All Sales</a></li>
                </ul>
            </li>

            @if(Session::get('role_name')=='superadmin' || Session::get('role_name')=='admin')
            <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-money"></i><span class="hide-menu">Expenses</span></a>
                <ul aria-expanded="false" class="collapse">
                    <li  class="border-bottom"><a href="{{ route('expenses.create') }}">Add Expenses</a></li>
                    <li class="border-bottom"><a href="{{ route('expenses.index') }}">Manage Expenses</a></li>
                    <li><a href="{{ route('expense_categories.index') }}">Expenses Categories</a></li>
                </ul>
            </li>
            @endif

            <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-sort-numeric-asc"></i><span class="hide-menu">Meter Readings</span></a>
                <ul aria-expanded="false" class="collapse">
                    <li  class="border-bottom"><a href="{{ route('meter_readers.index') }}">Add Meter</a></li>
                    <li  class="border-bottom"><a href="{{ route('meter_readings.create') }}">Add Meter Reading</a></li>
                    <li><a href="{{ route('meter_readings.index') }}">Manage Meter Records</a></li>
                </ul>
            </li>

            <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-sort-numeric-asc"></i><span class="hide-menu">Advances</span></a>
                <ul aria-expanded="false" class="collapse">
                    @if(Session::get('role_name')=='superadmin' || Session::get('role_name')=='admin')
                    <li  class="border-bottom"><a href="{{ route('supplier_advances.index') }}">Supplier Advances</a></li>
                    @endif
                    <li><a href="{{ route('customer_advances.index') }}">Customer Advances</a></li>
                </ul>
            </li>

            <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-bar-chart"></i><span class="hide-menu">Accounts</span></a>
                <ul aria-expanded="false" class="collapse">
                    @if(Session::get('role_name')=='superadmin' || Session::get('role_name')=='admin')
                    <li class="border-bottom">
                        <a href="{{ route('supplier_payments.index') }}">Supplier Payment</a>
                    </li>
                    @endif
                    <li class="border-bottom">
                        <a href="{{ route('payment_receives.index') }}">Customer Receive</a>
                    </li>
                    @if(Session::get('role_name')=='superadmin' || Session::get('role_name')=='admin')
                    <li class="border-bottom">
                        <a href="{{ route('deposits.index') }}">Deposits</a>
                    </li>
                    <li class="border-bottom"><a href="{{ route('inward_loans.index') }}">Inward Loan</a></li>
                    @endif
                    <li><a href="{{ route('outward_loans.index') }}">OutWard Loan</a></li>
                </ul>
            </li>

            <li>
                <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-files"></i><span class="hide-menu">Reports</span>
                </a>

                <ul aria-expanded="false" class="collapse">
                    <li  class="border-bottom"><a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-files"></i><span class="hide-menu">Summaries</span>
                        </a>
                        <ul aria-expanded="false" class="collapse">
                            <li class="border-bottom"><a href="{{ route('GetCustomerStatement') }}">Receivable Summary</a></li>
                            <li class="border-bottom"><a href="{{ route('GetReceivedAdvancesSummary') }}">Customer Advances Summary</a></li>
                            @if(Session::get('role_name')=='superadmin' || Session::get('role_name')=='admin')
                            <li class="border-bottom"><a href="{{ route('GetSupplierStatement') }}">Payable Summary</a></li>
                            <li class="border-bottom"><a href="{{ route('GetPaidAdvancesSummary') }}">Supplier Advance Summary</a></li>
                            <li><a href="{{ route('GetReceivableSummaryAnalysis') }}">Receivable Summary Analysis</a></li>
                            @endif
                        </ul>
                    </li>

                    <li  class="border-bottom"><a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-files"></i><span class="hide-menu">Statements</span>
                        </a>
                        <ul aria-expanded="false" class="collapse">
                            <li class="border-bottom"><a href="{{ route('GetDetailCustomerStatement') }}">Customer Statement</a></li>
                            @if(Session::get('role_name')=='superadmin' || Session::get('role_name')=='admin')
                            <li><a href="{{ route('GetDetailSupplierStatement') }}">Supplier Statement</a></li>
                            @endif
                        </ul>
                    </li>

                    <li  class="border-bottom"><a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-files"></i><span class="hide-menu">Sales Reports</span>
                        </a>
                        <ul aria-expanded="false" class="collapse">
                            <li  class="border-bottom"><a href="{{ route('SalesReport') }}">Sales Date-To-Date</a></li>
                            <li ><a href="{{ route('SalesReportByVehicle') }}">By Vehicle Date-to-Date</a></li>
                            <li ><a href="{{ route('SalesReportByCustomer') }}">By Customer Date-to-Date</a></li>
                        </ul>
                    </li>

                    @if(Session::get('role_name')=='superadmin' || Session::get('role_name')=='admin')
                    <li  class="border-bottom"><a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-files"></i><span class="hide-menu">Purchase Reports</span>
                        </a>
                        <ul aria-expanded="false" class="collapse">
                            <li  class="border-bottom"><a href="{{ route('PurchaseReport') }}">Purchase Date-To-Date</a></li>
                        </ul>
                    </li>
                    <li  class="border-bottom"><a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-files"></i><span class="hide-menu">Expense Reports</span>
                        </a>
                        <ul aria-expanded="false" class="collapse">
                            <li  class="border-bottom"><a href="{{ route('ExpenseReport') }}">Expense Date-To-Date</a></li>
                            <li  class="border-bottom"><a href="{{ route('GetExpenseAnalysis') }}">Expense Analysis</a></li>
                        </ul>
                    </li>
                    @endif

                    @if(Session::get('role_name')=='superadmin' || Session::get('role_name')=='admin')
                    <li><a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-files"></i><span class="hide-menu">Accounts Reports</span>
                        </a>
                        <ul aria-expanded="false" class="collapse">
                            <li  class="border-bottom">
                                <a href="{{ route('CashReport') }}">Cash Book</a>
                            </li>
                            <li class="border-bottom">
                                <a href="{{ route('BankReport') }}">Bank Book</a>
                            </li>
                           {{-- <li class="border-bottom">
                                <a href="{{ route('GeneralLedger') }}">General Ledger</a>
                            </li>
                            <li class="border-bottom">
                                <a href="#">Cash flow</a>
                            </li>
                            <li class="border-bottom">
                                <a href="#">Trial balance</a>
                            </li>--}}
                            @if(Session::get('role_name')=='superadmin')
                            <li class="border-bottom">
                                <a href="{{ route('Profit_loss') }}">Profit Loss Statement</a>
                            </li>
                            <li>
                                <a href="{{ route('Garage_value') }}">Garage Value</a>
                            </li>
                            @endif
                        </ul>
                    </li>
                    @endif
                </ul>
            </li>
            <li class="nav-small-cap"></li>
        </ul>
    </nav>
</div>
