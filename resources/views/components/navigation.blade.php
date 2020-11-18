<div>
    <!-- Sidebar navigation-->
    <nav class="sidebar-nav">
        <ul id="sidebarnav">
{{--            <li> <a  class=" waves-effect waves-dark" href="index.php" aria-expanded="false"><i class="icon-speedometer"></i>Dashboard </a>--}}
                <!--   <ul aria-expanded="false" class="collapse">
                      <li><a href="index-2.html">Minimal</a></li>
                      <li><a href="index2.html">Analytical</a></li>
                      <li><a href="index3.html">Demographical</a></li>
                      <li><a href="index4.html">Modern</a></li>
                      <li><a href="index5.html">Cryptocurrency</a></li>
                  </ul> -->
            </li>
            <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-layout-grid2"></i><span class="hide-menu">Master</span></a>
                <ul aria-expanded="false" class="collapse">
                    <li><a href="{{ route('companies.index') }}">Companies list</a></li>
                    <li><a href="{{ route('users.index') }}">Users list</a></li>
                    <li><a href="{{ route('roles.index') }}">Roles list</a></li>
                    <li><a href="{{ route('banks.index') }}">Banks list</a></li>
                    <!--  <li><a href="app-chat.html">Chat app</a></li> -->

                </ul>
            </li>

            <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-layout-media-right-alt"></i><span class="hide-menu">Contacts</span></a>
                <ul aria-expanded="false" class="collapse">
                    <li><a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-files"></i><span class="hide-menu">Customers</span>
                        </a>
                        <ul aria-expanded="false" class="collapse">
                            <li><a href="{{ route('customers.create') }}">Add New Customer</a></li>
                            <li><a href="{{ route('customers.index') }}">Manage Customers</a></li>
                            <li><a href="{{ route('customer_advances.create') }}">Add Advances</a></li>
                            <li><a href="{{ route('customer_advances.index') }}">Manage Advances</a></li>
                        </ul>
                    </li>

                    <li><a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-files"></i><span class="hide-menu">Suppliers</span>
                        </a>
                        <ul aria-expanded="false" class="collapse">
                            <li><a href="{{ route('suppliers.create') }}">Add New Supplier</a></li>
                            <li><a href="{{ route('suppliers.index') }}">Manage Suppliers</a></li>
                            <li><a href="{{ route('supplier_advances.create') }}">Add Advances</a></li>
                            <li><a href="{{ route('supplier_advances.index') }}">Manage Advances</a></li>
                        </ul>
                    </li>

                </ul>
            </li>


            <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-layout-media-right-alt"></i><span class="hide-menu">Vehicles</span></a>
                <ul aria-expanded="false" class="collapse">
                    <li><a href="{{ route('vehicles.create') }}">Add new Vehicle</a></li>
                    <li><a href="{{ route('vehicles.index') }}">Manage Vehicles</a></li>
                    <!--  <li><a href="app-chat.html">Chat app</a></li> -->
                </ul>
            </li>

            <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-layout-media-right-alt"></i><span class="hide-menu">Drivers</span></a>
                <ul aria-expanded="false" class="collapse">
                    <li><a href="{{ route('drivers.create') }}">Add new Driver</a></li>
                    <li><a href="{{ route('drivers.index') }}">Manage Drivers</a></li>
                    <!--  <li><a href="app-chat.html">Chat app</a></li> -->
                </ul>
            </li>


            <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-files"></i><span class="hide-menu">Purchase</span></a>
                <ul aria-expanded="false" class="collapse">
                    <li><a href="{{ url('purchases') }}">Add Purchase</a></li>
                    <li><a href="{{ url('purchases/index') }}">Manage Purchase</a></li>
{{--                    <li><a href="app-calendar.html">Payables</a></li>--}}
                    <!--  <li><a href="app-chat.html">Chat app</a></li> -->

                </ul>
            </li>

            <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-files"></i><span class="hide-menu">Sales</span></a>
                <ul aria-expanded="false" class="collapse">
                    <li><a href="{{ url('sales') }}">Add Sales</a></li>
                    <li><a href="{{ url('sales/index') }}">Manage Sales</a></li>
{{--                    <li><a href="app-calendar.html">Receivables</a></li>--}}
                </ul>
            </li>


            <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-files"></i><span class="hide-menu">Expenses</span></a>
                <ul aria-expanded="false" class="collapse">
                    <li><a href="{{ url('expenses') }}">Add Expenses</a></li>
                    <li><a href="{{ url('expenses/index') }}">Manage Expenses</a></li>
                </ul>
            </li>


            <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-files"></i><span class="hide-menu">Meter Readings</span></a>
                <ul aria-expanded="false" class="collapse">
                    <li><a href="{{ url('add_meter') }}">Add Meter</a></li>
                    <li><a href="{{ url('meterReading') }}">Add Meter Reading</a></li>
                    <li><a href="{{ url('meterReading/index') }}">Manage Meter Records</a></li>
                </ul>
            </li>

            <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-files"></i><span class="hide-menu">Accounts</span></a>
                <ul aria-expanded="false" class="collapse">
                    <li>
                        <a href="#">Chart of Account</a>
                    </li>
                    <li>
                        <a href="#">Manufacturer Payment</a>
                    </li>
                    <li>
                        <a href="#">Supplier Payment</a>
                    </li>
                    <li>
                        <a href="#">Customer Receive</a>
                    </li>
                    <li>
                        <a href="#">Cash Adjustment</a>
                    </li>
                    <li>
                        <a href="#">Debit Voucher</a>
                    </li>
                    <li>
                        <a href="#">Credit Voucher</a>
                    </li>
                    <li>
                        <a href="#">Conta Voucher</a>
                    </li>
                    <li>
                        <a href="#">Journal Voucher</a>
                    </li>
                    <li>
                        <a href="#"> Voucher Approval</a>
                    </li>
                    <li><a href="{{ url('loan') }}">Add Loan</a></li>
                    <li><a href="{{ url('loan/index') }}">Manage Loan</a></li>
                </ul>
            </li>

            <li>
                <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-files"></i><span class="hide-menu">Reports</span>
                </a>

                <ul aria-expanded="false" class="collapse">
                    <li><a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-files"></i><span class="hide-menu">Sales Reports</span>
                        </a>
                        <ul aria-expanded="false" class="collapse">
                            <li><a href="app-calendar.html">Sales Date-To-Date</a></li>
                            <li><a href="app-calendar.html">By Vehicle Date-to-Date</a></li>
                        </ul>

                    </li>

                    <li><a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-files"></i><span class="hide-menu">Purchase Reports</span>
                        </a>
                        <ul aria-expanded="false" class="collapse">
                            <li><a href="app-calendar.html">Purchase Date-To-Date</a></li>
                            <li><a href="app-calendar.html">By Vehicle Date-to-Date</a></li>
                        </ul>

                    </li>

                    <li><a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-files"></i><span class="hide-menu">Accounts Reports</span>
                        </a>
                        <ul aria-expanded="false" class="collapse">
                            <li>
                                <a href="#">Cash Book</a>
                            </li>
                            <li>
                                <a href="#">Bank Book</a>
                            </li>
                            <li>
                                <a href="#">General Ledger</a>
                            </li>
                            <li>
                                <a href="#">Cash flow</a>
                            </li>
                            <li>
                                <a href="#">Profit Loss Statement</a>
                            </li>
                            <li>
                                <a href="#">Trial balance</a>
                            </li>
                            <li>
                                <a href="#">COA Print</a>
                            </li>
                        </ul>

                    </li>
                </ul>
            </li>


            <li class="nav-small-cap"></li>


        </ul>
    </nav>
    <!-- End Sidebar navigation -->
</div>
