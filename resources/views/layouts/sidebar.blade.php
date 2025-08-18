<aside class="sidebar-wrapper">
    <div class="sidebar-header">
        <div class="logo-icon">
             <img src="{{ URL::asset('build/images/favicon-32x32.png') }}" class="logo-img mt-1" alt="" style="width:auto;height:70px;">
        </div>
        <div class="logo-name flex-grow-1">
            <h5 class="mb-0"></h5>
        </div>
        <div class="sidebar-close">
            <span class="material-icons-outlined">close</span>
        </div>
    </div>
    <div class="sidebar-nav" data-simplebar="true">
        <!--navigation-->
        @if(has_access('master_user')||has_access('master_role')||has_access('master_coa')||has_access('master_customer')||has_access('master_supplier')||has_access('master_department')||has_access('master_warehouse')||has_access('master_payment_method')||has_access('master_tax_master')||has_access('master_item')||has_access('master_item_sales')||has_access('master_item_purchase'))
        <ul class="metismenu" id="sidenav">
                <li class="menu-label">Master</li>
                <li>
                    <a href="javascript:;" class="has-arrow">
                        <div class="parent-icon"><i class="material-icons-outlined">settings</i>
                        </div>
                        <div class="menu-title">Master</div>
                    </a>
                    <ul>
                            @if(has_access('master_user'))
                            <ul>
                                <li><a href="/TDS/master/users"><i class="material-icons-outlined">person</i>Users</a>
                                </li>
                            </ul>
                            @endif
                            @if(has_access('master_role'))
                            <ul>
                                <li><a href="/TDS/master/role"><i class="material-icons-outlined">shield</i>Role</a>
                                </li>
                            </ul>
                            @endif
                            @if(Auth::user()->username=='superadminICT')
                            <li><a class="has-arrow" href="javascript:;"><i
                                        class="material-icons-outlined">apartment</i>Company</a>
                                <ul>
                                    <li><a href="/TDS/master/type-company"><i
                                                class="material-icons-outlined">assured_workload</i>Type Company</a>
                                    </li>
                                </ul>
                                <ul>
                                    <li><a href="/TDS/master/company"><i
                                                class="material-icons-outlined">apartment</i>Company</a>
                                    </li>
                                </ul>
                            </li>
                            @endif
                        @if(has_access('master_coa'))
                        <ul>
                            <li><a href="/TDS/master/coa-type"><i class="material-icons-outlined">account_circle</i>COA
                                    Type</a>
                            </li>
                        </ul>
                        <ul>
                            <li><a href="/TDS/master/coa"><i class="material-icons-outlined">account_circle</i>COA</a>
                            </li>
                        </ul>
                        @endif
                        <ul style="display: none">
                            <li><a href="/TDS/master/promo"><i class="material-icons-outlined">local_offer</i>Promo</a>
                            </li>
                        </ul>
                        @if(has_access('master_customer'))
                        <ul class="d-none">
                            <li><a href="/TDS/master/group-customer"><i
                                        class="material-icons-outlined">groups</i>Grup {{__('Customer')}}</a>
                            </li>
                        </ul>
                        <ul>
                            <li><a href="/TDS/master/customer"><i
                                        class="material-icons-outlined">groups</i>{{__('Customer')}}</a>
                            </li>
                        </ul>
                        @endif
                        @if(has_access('master_supplier'))
                        <ul class="d-none">
                            <li><a href="/TDS/master/supplier"><i
                                        class="material-icons-outlined">inventory</i>{{__('Supplier')}}</a>
                            </li>
                        </ul>
                        @endif
                        @if(has_access('master_city'))
                            <ul class="d-none">
                                <li><a href="/TDS/master/city"><i class="material-icons-outlined">apartment</i>City</a>
                                </li>
                            </ul>
                        @endif
                        @if(has_access('master_zone'))
                            <ul class="d-none">
                                <li><a href="/TDS/master/zone"><i class="material-icons-outlined">apartment</i>Zone</a>
                                </li>
                            </ul>
                        @endif
                        @if(has_access('master_salesman'))
                            <ul class="d-none">
                                <li><a href="/TDS/master/salesman"><i class="material-icons-outlined">person</i>Salesman</a>
                                </li>
                            </ul>
                        @endif
                        @if(has_access('master_department'))
                        <ul class="d-none">
                            <li><a href="/TDS/master/department"><i
                                        class="material-icons-outlined">foundation</i>{{__('Department')}}</a>
                            </li>
                        </ul>
                        @endif
                        @if(has_access('master_warehouse'))
                        <ul class="d-none">
                            <li><a href="/TDS/master/warehouse"><i
                                        class="material-icons-outlined">warehouse</i>{{__('Warehouse')}}</a>
                            </li>
                        </ul>
                        @endif
                        @if(has_access('master_payment_method'))
                        <ul>
                            <li><a href="/TDS/master/payment-method"><i
                                        class="material-icons-outlined">credit_card</i>Metode Pembayaran</a>
                            </li>
                        </ul>
                        @endif
                        @if(has_access('master_tax_master'))
                        <ul>
                            <li><a href="/TDS/master/tax-master"><i class="material-icons-outlined">paid</i>{{__('Tax')}}</a>
                            </li>
                        </ul>
                        @endif
                        @if(has_access('master_item')||has_access('master_item_sales')||has_access('master_item_purchase'))
                        <li><a class="has-arrow" href="javascript:;"><i
                                    class="material-icons-outlined">apartment</i>Jasa</a>
                            @if(has_access('master_item'))
                            <ul>
                                <li><a href="/TDS/master/item-category"><i
                                            class="material-icons-outlined">category</i>Kategori Jasa</a>
                                </li>
                            </ul>
                            <ul>
                                <li><a href="/TDS/master/item-unit"><i class="material-icons-outlined">category</i>Unit Jasa</a>
                                </li>
                            </ul>
                            <ul>
                                <li><a href="/TDS/master/item"><i class="material-icons-outlined">category</i>Jasa</a>
                                </li>
                            </ul>
                            @endif
                            {{-- <ul>
                                il</a>
                            </li>
                            </ul> --}}
                            @if(has_access('master_item_purchase'))
                            <ul class="d-none">
                                <li><a href="/TDS/master/item-purchase"><i
                                            class="material-icons-outlined">category</i>Daftar Harga Beli Jasa</a>
                                </li>
                            </ul>
                            @endif
                            @if(has_access('master_item_sales'))
                            <ul>
                                <li><a href="/TDS/master/item-sales-price"><i
                                            class="material-icons-outlined">category</i>Daftar Harga Jual Jasa</a>
                                </li>
                            </ul>
                            @endif
                        <li>
                        @endif
                    </ul>
                </li>
                @endif
                @if(has_access('sales_order')||has_access('sales_invoice')||has_access('sales_return')||has_access('pbr'))
                <ul class="metismenu" id="sidenav">
                <li class="menu-label">{{__('Sales')}}</li>
                @endif
                @if(has_access('sales_order'))
                    <li class="d-none">
                        <a href="javascript:;" class="has-arrow">
                            <div class="parent-icon"><i class="material-icons-outlined">description</i>
                            </div>
                            <div class="menu-title">{{__('Sales Order')}}</div>
                        </a>
                        <ul>
                            <li><a href="/TDS/transaction/sales-order/create"><i
                                        class="material-icons-outlined">arrow_right</i>Input {{__('Sales Order')}}</a>
                            </li>
                            <li><a href="/TDS/transaction/sales-order"><i
                                        class="material-icons-outlined">arrow_right</i>Daftar {{__('Sales Order')}} </a>
                            </li>
                            <li><a href="/TDS/transaction/sales-order/summary"><i
                                        class="material-icons-outlined">arrow_right</i>Rangkuman {{__('Sales Order')}}</a>
                            </li>
                            <li><a href="/TDS/transaction/sales-order/summary-detail"><i
                                        class="material-icons-outlined">arrow_right</i>Analisa {{__('Sales Order')}}</a>
                            </li>
                        </ul>
                    </li>
                </li>
                @endif

                @if(has_access('sales_invoice'))
                <li>
                    <a href="javascript:;" class="has-arrow">
                        <div class="parent-icon"><i class="material-icons-outlined">description</i>
                        </div>
                        <div class="menu-title">{{__('Sales Invoice')}}</div>
                    </a>
                    <ul>
                        <li><a href="/TDS/transaction/sales-invoice/create"><i
                            class="material-icons-outlined">arrow_right</i>Input {{__('Sales Invoice')}}</a>
                        </li>
                        <li><a href="/TDS/transaction/sales-invoice"><i
                                    class="material-icons-outlined">arrow_right</i>Daftar {{__('Sales Invoice')}}</a>
                        </li>
                        <li><a href="/TDS/transaction/sales-invoice/summary"><i
                                    class="material-icons-outlined">arrow_right</i>Rangkuman {{__('Sales Invoice')}}</a>
                        </li>
                        <li><a href="/TDS/transaction/sales-invoice/summary-detail"><i
                                    class="material-icons-outlined">arrow_right</i>Analisa {{__('Sales Invoice')}}</a>
                        </li>
                        <li class="d-none"><a href="/TDS/transaction/sales-invoice/delivery-confirmation"><i
                                    class="material-icons-outlined">arrow_right</i>Konfirmasi Pengiriman Faktur</a>
                        </li>
                        <li class="d-none"><a href="/TDS/transaction/sales-invoice/delivery-report"><i
                                    class="material-icons-outlined">arrow_right</i>Laporan Pengiriman Faktur</a>
                        </li>
                        <li class="d-none"><a href="/TDS/transaction/sales-invoice/delivery-confirmation-cancel"><i
                                    class="material-icons-outlined">arrow_right</i>Pembatalan Konfirmasi Pengiriman Faktur</a>
                        </li>
                    </ul>
                </li>
                @endif
                @if(Auth::user()->username=='superadminICT')
                <li class="d-none">
                    <a href="javascript:;" class="has-arrow">
                        <div class="parent-icon"><i class="material-icons-outlined">history</i>
                        </div>
                        <div class="menu-title">Rekap {{__('Sales Invoice')}}</div>
                    </a>
                    <ul>
                        <li><a href="/TDS/transaction/sales-invoice-recap/create"><i
                            class="material-icons-outlined">arrow_right</i>Input {{__('Sales Invoice')}}</a>
                        </li>
                        <li><a href="/TDS/transaction/sales-invoice-recap"><i
                                    class="material-icons-outlined">arrow_right</i>Daftar {{__('Sales Invoice')}}</a>
                        </li>
                        <li><a href="/TDS/transaction/sales-invoice-recap/summary"><i
                                    class="material-icons-outlined">arrow_right</i>Rangkuman {{__('Sales Invoice')}}</a>
                        </li>
                        <li><a href="/TDS/transaction/sales-invoice-recap/summary-detail"><i
                                    class="material-icons-outlined">arrow_right</i>Analisa {{__('Sales Invoice')}}</a>
                        </li>
                        <li><a href="/TDS/transaction/sales-invoice-recap/delivery-confirmation"><i
                                    class="material-icons-outlined">arrow_right</i>Konfirmasi Pengiriman Faktur</a>
                        </li>
                        <li><a href="/TDS/transaction/sales-invoice-recap/delivery-confirmation-cancel"><i
                                    class="material-icons-outlined">arrow_right</i>Pembatalan Konfirmasi Pengiriman Faktur</a>
                        </li>
                    </ul>
                </li>
                @endif
                @if(has_access('sales_return'))
                <li class="d-none">
                    <a href="javascript:;" class="has-arrow">
                      <div class="parent-icon"><i class="material-icons-outlined">description</i>
                      </div>
                      <div class="menu-title">{{__('Sales Return')}}</div>
                    </a>
                    <ul>
                        <li><a href="/TDS/transaction/sales-return/create"><i class="material-icons-outlined">arrow_right</i> {{__('Sales Return')}} Baru</a>
                        </li>
                        <li><a href="/TDS/transaction/sales-return"><i class="material-icons-outlined">arrow_right</i>Daftar {{__('Sales Return')}} </a>
                        </li>
                        <li><a href="/TDS/transaction/sales-return/summary"><i
                            class="material-icons-outlined">arrow_right</i>Rangkuman {{__('Sales Return')}}</a></li>
                        </ul>
                    </li>
                @endif
                @if(Auth::user()->username=='superadminICT')
                <li class="d-none">
                    <a href="javascript:;" class="has-arrow">
                      <div class="parent-icon"><i class="material-icons-outlined">history</i>
                      </div>
                      <div class="menu-title">Rekap {{__('Sales Return')}}</div>
                    </a>
                    <ul>
                        <li><a href="/TDS/transaction/sales-return-recap/create"><i class="material-icons-outlined">arrow_right</i> {{__('Sales Return')}} Baru</a>
                        </li>
                        <li><a href="/TDS/transaction/sales-return-recap"><i class="material-icons-outlined">arrow_right</i>Daftar {{__('Sales Return')}} </a>
                        </li>
                        <li><a href="/TDS/transaction/sales-return-recap/summary"><i
                            class="material-icons-outlined">arrow_right</i>Rangkuman {{__('Sales Return')}}</a></li>
                        </ul>
                    </li>
                @endif
                @if(has_access('purchase_order')||has_access('purchase_invoice')||has_access('purchase_return'))
                <ul class="metismenu d-none" id="sidenav">
                <li class="menu-label d-none">{{__('Purchase')}}</li>
                @endif
                <li style="display:none;">
                    <a href="javascript:;" class="has-arrow">
                        <div class="parent-icon"><i class="material-icons-outlined">shopping_cart</i>
                        </div>
                        <div class="menu-title">Purchase Requisition</div>
                    </a>
                    <ul>
                        <li><a href="/TDS/transaction/purchase-requisition/create"><i class="material-icons-outlined">arrow_right</i>New Purchase Requisition</a>
                        </li>
                    <li><a href="/TDS/transaction/purchase-requisition"><i class="material-icons-outlined">arrow_right</i>Purchase Requisition List</a>
                        </li>
                    <li><a href="/TDS/transaction/purchase-requisition/summary"><i class="material-icons-outlined">arrow_right</i>Purchase Requisition Summary</a>
                        </li>
                </ul>
              </li>
              @if(has_access('purchase_order'))
              <li class="d-none">
                <a href="javascript:;" class="has-arrow">
                  <div class="parent-icon"><i class="material-icons-outlined">shopping_cart</i>
                  </div>
                  <div class="menu-title">{{__('Purchase Order')}}</div>
                </a>
                <ul>
                    <li><a href="/TDS/transaction/purchase-order/create"><i class="material-icons-outlined">arrow_right</i>Input {{__('Purchase Order')}}</a>
                    </li>
                    <li><a href="/TDS/transaction/purchase-order"><i class="material-icons-outlined">arrow_right</i>Daftar {{__('Purchase Order')}}</a>
                    </li>
                </ul>
            </li>
            @endif
            @if(has_access('purchase_invoice'))
            <li class="d-none">
                <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="material-icons-outlined">shopping_cart</i>
                </div>
                <div class="menu-title">{{__('Purchase Invoice')}}</div>
                </a>
                <ul>
                    <li><a href="/TDS/transaction/purchase-invoice/create"><i class="material-icons-outlined">arrow_right</i>{{__('Purchase Invoice')}}</a>
                    </li>
                    <li><a href="/TDS/transaction/purchase-invoice"><i class="material-icons-outlined">arrow_right</i>Daftar {{__('Purchase Invoice')}}</a>
                    </li>
                    <li><a href="/TDS/transaction/purchase-invoice/summary"><i
                        class="material-icons-outlined">arrow_right</i>Rangkuman {{__('Purchase Invoice')}}</a></li>
                    <li><a href="/TDS/transaction/purchase-invoice/summary-detail"><i
                        class="material-icons-outlined">arrow_right</i>Analisa {{__('Purchase Invoice')}}</a></li>
                </ul>
            </li>
            @endif
            @if(Auth::user()->username=='superadminICT')
            <li class="d-none">
                <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="material-icons-outlined">history</i>
                </div>
                <div class="menu-title">Rekap {{__('Purchase Invoice')}}</div>
                </a>
                <ul>
                    <li><a href="/TDS/transaction/purchase-invoice-recap/create"><i class="material-icons-outlined">arrow_right</i>Rekap {{__('Purchase Invoice')}}</a>
                    </li>
                    <li><a href="/TDS/transaction/purchase-invoice-recap"><i class="material-icons-outlined">arrow_right</i>Daftar {{__('Purchase Invoice')}}</a>
                    </li>
                    <li><a href="/TDS/transaction/purchase-invoice-recap/summary"><i
                        class="material-icons-outlined">arrow_right</i>Rangkuman {{__('Purchase Invoice')}}</a></li>
                    <li><a href="/TDS/transaction/purchase-invoice-recap/summary-detail"><i
                        class="material-icons-outlined">arrow_right</i>Analisa {{__('Purchase Invoice')}}</a></li>
                </ul>
            </li>
            @endif
            @if(has_access('purchase_return'))
                <li class="d-none">
                <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="material-icons-outlined">shopping_cart</i>
                </div>
                <div class="menu-title">{{__('Purchase Return')}}</div>
                </a>
                <ul>
                    <li><a href="/TDS/transaction/purchase-return/create"><i class="material-icons-outlined">arrow_right</i>Input {{__('Purchase Return')}}</a>
                    </li>
                    <li><a href="/TDS/transaction/purchase-return"><i class="material-icons-outlined">arrow_right</i>Daftar {{__('Purchase Return')}}</a>
                    </li>
                    <li><a href="/TDS/transaction/purchase-return/summary"><i
                        class="material-icons-outlined">arrow_right</i>Rangkuman {{__('Purchase Return')}}</a></li>
                    </ul>
                </li>
            @endif
            @if(Auth::user()->username=='superadminICT')
                <li class="d-none">
                <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="material-icons-outlined">history</i>
                </div>
                <div class="menu-title">Rekap {{__('Purchase Return')}}</div>
                </a>
                <ul>
                    <li><a href="/TDS/transaction/purchase-return-recap/create"><i class="material-icons-outlined">arrow_right</i>Input {{__('Purchase Return')}}</a>
                    </li>
                    <li><a href="/TDS/transaction/purchase-return-recap"><i class="material-icons-outlined">arrow_right</i>Daftar {{__('Purchase Return')}}</a>
                    </li>
                    <li><a href="/TDS/transaction/purchase-return-recap/summary"><i
                        class="material-icons-outlined">arrow_right</i>Rangkuman {{__('Purchase Return')}}</a></li>
                    </ul>
                </li>
            @endif

                {{-- @if (Auth::user()->role == 7 || Auth::user()->role == 5 || Auth::user()->role == 2)
                <li class="menu-label">Reimburse</li>
                <li>
                    <a href="javascript:;" class="has-arrow">
                        <div class="parent-icon"><i class="material-icons-outlined"><span
                                    class="material-symbols-outlined">
                                    receipt_long
                                </span></i>
                        </div>
                        <div class="menu-title">Reimburse</div>
                    </a>
                    <ul>
                        <li><a href="/TDS/transaction/reimburse/create"><i
                                    class="material-icons-outlined">arrow_right</i>New Reimburse</a>
                        </li>
                        <li><a href="/TDS/transaction/reimburse"><i
                                    class="material-icons-outlined">arrow_right</i>Reimburse List</a>
                        </li>
                    </ul>
                </li>
                @endif --}}

            @if(has_access('good_receipt'))
                <li class="menu-label">{{__('Warehouse')}}</li>
                <li class="d-none">
                    <a href="javascript:;" class="has-arrow">
                        <div class="parent-icon"><i class="material-icons-outlined"><span
                                    class="material-symbols-outlined">
                                    receipt_long
                                </span></i>
                        </div>
                        <div class="menu-title">{{__('Good Receipt')}}</div>
                    </a>
                    <ul>
                        <li><a href="/TDS/transaction/good-receipt/create"><i
                                    class="material-icons-outlined">arrow_right</i>Input {{__('Good Receipt')}}</a>
                        </li>
                        <li><a href="/TDS/transaction/good-receipt/"><i
                                    class="material-icons-outlined">arrow_right</i>Daftar {{__('Good Receipt')}}</a>
                        </li>
                    </ul>
                </li>
                <li style="display:none;">
                    <a href="javascript:;" class="has-arrow">
                        <div class="parent-icon"><i class="material-icons-outlined"><span
                                    class="material-symbols-outlined">
                                    receipt_long
                                </span></i>
                        </div>
                        <div class="menu-title">Delivery Order</div>
                    </a>
                    <ul>
                        <li><a href="/TDS/transaction/delivery-order/create"><i
                                    class="material-icons-outlined">arrow_right</i>New Delivery Order</a>
                        </li>
                        <li><a href="/TDS/transaction/delivery-order/"><i
                                    class="material-icons-outlined">arrow_right</i>Delivery Order List</a>
                        </li>
                    </ul>
                </li>
            </ul>
            @endif

        @if(has_access('bank_in')||has_access('bank_out')||has_access('general_journal')||has_access('payable_payment')||has_access('receivable_payment')||has_access('closing'))
        <ul class="metismenu" id="sidenav">
            <li class="menu-label">Akuntansi</li>
            @if(has_access('bank_in'))
            <li>
                <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="material-icons-outlined">account_balance</i>
                </div>
                <div class="menu-title">{{__('Bank / Cash In')}}</div>
                </a>
                <ul>
                    <li><a href="/TDS/transaction/bank-cash-in/create"><i class="material-icons-outlined">arrow_right</i>Input {{__('Bank / Cash In')}} </a>
                    </li>
                    <li><a href="/TDS/transaction/bank-cash-in"><i class="material-icons-outlined">arrow_right</i>Daftar {{__('Bank / Cash In')}}</a>
                    </li>
                </ul>
            </li>
            @endif
            @if(has_access('bank_out'))
            <li>
                <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="material-icons-outlined">account_balance</i>
                </div>
                <div class="menu-title">{{__('Bank / Cash Out')}}</div>
                </a>
                <ul>
                    <li><a href="/TDS/transaction/bank-cash-out/create"><i class="material-icons-outlined">arrow_right</i>Input {{__('Bank / Cash Out')}} </a>
                    </li>
                    <li><a href="/TDS/transaction/bank-cash-out"><i class="material-icons-outlined">arrow_right</i>Daftar {{__('Bank / Cash Out')}}</a>
                    </li>
                </ul>
            </li>
            @endif
            @if(has_access('general_journal'))
            <li>
                <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="material-icons-outlined">menu_book</i>
                </div>
                <div class="menu-title">{{__('General Journal')}}</div>
                </a>
                <ul>
                    <li><a href="/TDS/transaction/general-journal/create"><i class="material-icons-outlined">arrow_right</i>Input {{__('General Journal')}}</a>
                    </li>
                    <li><a href="/TDS/transaction/general-journal"><i class="material-icons-outlined">arrow_right</i>Daftar {{__('General Journal')}}</a>
                    </li>
                </ul>
            </li>
            @endif
            <li style="display: none">
                <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="material-icons-outlined">edit_note</i>
                </div>
                <div class="menu-title">Debit Note</div>
                </a>
                <ul>
                    <li><a href="{{ route('transaction.sales_debt_credit_notes.create', ['type' => 'debit']) }}"><i class="material-icons-outlined">arrow_right</i>Sales Debit Note Transaction</a>
                    </li>

                    <li><a href="/TDS/transaction/sales-debt-credit-notes/debit"><i class="material-icons-outlined">arrow_right</i>Sales Debit Note List</a>
                    </li>

                    <li><a href="{{ route('transaction.purchase_debt_credit_notes.create', ['type' => 'debit']) }}"><i class="material-icons-outlined">arrow_right</i>Purchase Debit Note Transaction</a>
                    </li>

                    <li><a href="/TDS/transaction/purchase-debt-credit-notes/debit"><i class="material-icons-outlined">arrow_right</i>Purchase Debt  Note List</a>
                    </li>


                </ul>
            </li>
            <li style="display: none">
                <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="material-icons-outlined">edit_note</i>
                </div>
                <div class="menu-title">Credit Note</div>
                </a>
                <ul>
                    <li><a href="{{ route('transaction.sales_debt_credit_notes.create', ['type' => 'credit']) }}"><i class="material-icons-outlined">arrow_right</i>Sales Credit Note Transaction</a>
                    </li>
                    <li><a href="/TDS/transaction/sales-debt-credit-notes/credit"><i class="material-icons-outlined">arrow_right</i>Sales Credit Note List</a>
                    </li>
                    <li><a href="{{ route('transaction.purchase_debt_credit_notes.create', ['type' => 'credit']) }}"><i class="material-icons-outlined">arrow_right</i>Purchase Credit Note Transaction</a>
                    </li>
                    <li><a href="/TDS/transaction/purchase-debt-credit-notes/credit"><i class="material-icons-outlined">arrow_right</i>Purchase Debt  Note List</a>
                    </li>

                </ul>
            </li>
            @if(has_access('payable_payment'))
            <li class="d-none">
                <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="material-icons-outlined">payments</i>
                </div>
                <div class="menu-title">{{__('Payable Payment')}}</div>
                </a>
                <ul>
                    <li><a href="/TDS/transaction/payable-payment/create"><i class="material-icons-outlined">arrow_right</i>Input {{__('Payable Payment')}}</a>
                    </li>
                    <li><a href="/TDS/transaction/payable-payment"><i class="material-icons-outlined">arrow_right</i>Daftar {{__('Payable Payment')}}</a>
                    </li>
                </ul>
            </li>
            @endif
            @if(has_access('receivable_payment'))
            <li>
                <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="material-icons-outlined">payments</i>
                </div>
                <div class="menu-title">{{__('Receivable Payment')}}</div>
                </a>
                <ul>
                    <li><a href="/TDS/transaction/receivable-payment/create"><i class="material-icons-outlined">arrow_right</i>Input {{__('Receivable Payment')}}</a>
                    </li>
                    <li><a href="/TDS/transaction/receivable-payment"><i class="material-icons-outlined">arrow_right</i>Daftar {{__('Receivable Payment')}}</a>
                    </li>

            </li>
            @endif
            @if(has_access('closing'))
                </ul>
                <li>
                    <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class="material-icons-outlined">event_busy</i>
                    </div>
                    <div class="menu-title">Closing</div>
                    </a>
                    <ul>
                        <li><a href="/TDS/transaction/closing/show"><i class="material-icons-outlined">arrow_right</i>Periode Closing & Restore</a>
                        </li>
                    </ul>
                    </li>
                </li>
                </li>
            </li>
            @endif
            </ul>
        @endif
        @if(has_access('accounting_report')||has_access('system_report')||has_access('inventory_report'))
        <ul class="metismenu" id="sidenav">
        <li class="menu-label">Laporan</li>

        @if(has_access('accounting_report'))
        <li>
            <a class="has-arrow" href="javascript:;">
                <div class="parent-icon">
                <i class="material-icons-outlined">bar_chart</i></div>
                <div class="menu-title">Laporan Akuntansi</div>
            </a>
            <ul>
                <li><a href="/TDS/transaction/journal"><i class="material-icons-outlined">arrow_right</i>Laporan {{__('General Journal')}}</a>
                </li>
                <li><a href="/TDS/transaction/journal/journal-ledger"><i
                    class="material-icons-outlined">arrow_right</i>Laporan Buku Besar</a></li>
                <li><a href="/TDS/transaction/journal/trial-balance"><i
                    class="material-icons-outlined">arrow_right</i>Neraca Saldo</a></li>
                    <li><a href="/TDS/transaction/journal/balance-sheet"><i
                        class="material-icons-outlined">arrow_right</i>Neraca Keuangan</a></li>
                <li><a href="/TDS/transaction/journal/income-statement"><i
                        class="material-icons-outlined">arrow_right</i>Laporan Laba Rugi</a></li>
                <li><a href="/TDS/transaction/journal/income-statement-accum"><i
                        class="material-icons-outlined">arrow_right</i>Laporan Laba Rugi Akumulatif</a></li>
                <li class="d-none"><a href="/TDS/transaction/journal/payable-report"><i
                    class="material-icons-outlined">arrow_right</i>Laporan Hutang</a></li>
                <li class="d-none"><a href="/TDS/transaction/journal/payable-aging"><i
                    class="material-icons-outlined">arrow_right</i>Laporan Umur  Hutang</a></li>
                <li class="d-none"><a href="/TDS/transaction/journal/payable-aging-summary"><i
                    class="material-icons-outlined">arrow_right</i>Ringkasan Umur  Hutang</a></li>
                <li><a href="/TDS/transaction/journal/receivable-report"><i
                    class="material-icons-outlined">arrow_right</i>Laporan Piutang</a></li>
                <li><a href="/TDS/transaction/journal/receivable-aging"><i
                    class="material-icons-outlined">arrow_right</i>Laporan Umur Piutang</a></li>
                <li><a href="/TDS/transaction/journal/receivable-aging-summary"><i
                    class="material-icons-outlined">arrow_right</i>Ringkasan Umur Piutang</a></li>
            </ul>
        </li>
        @endif
        @if(has_access('system_report'))
        <li>
            <a class="has-arrow" href="javascript:;"><div class="parent-icon">
            <i class="material-icons-outlined">bar_chart</i></div> <div class="menu-title">Laporan Sistem</div></a>
            <ul>
                <li><a href="{{route('transaction.delete_log')}}"><i
                    class="material-icons-outlined">arrow_right</i>Laporan Hapus Dokumen</a></li>
                <li><a href="{{route('transaction.cancel_log')}}"><i
                    class="material-icons-outlined">arrow_right</i>Laporan Dokumen Batal</a></li>
            </ul>
        </li>
        @endif
        @if(has_access('inventory_report'))
        <li class="d-none">
            <a class="has-arrow" href="javascript:;">
                <div class="parent-icon">
                    <i class="material-icons-outlined">bar_chart</i>
                </div>
                <div class="menu-title">Laporan Stock Gudang</div></a>
            <ul>
                <li><a href="{{route('report.inventory')}}"><i
                    class="material-icons-outlined">arrow_right</i>Laporan Detail Stock Gudang</a></li>
                <li><a href="{{route('report.recap-inventory')}}"><i
                    class="material-icons-outlined">arrow_right</i>Laporan Rekap Stock Gudang</a></li>
            </ul>
        </li>
        @endif
        @if(has_access('system_report'))
        <li class="d-none">
            <a class="has-arrow" href="javascript:;"><div class="parent-icon">
            <i class="material-icons-outlined">bar_chart</i></div> <div class="menu-title">Laporan PPn</div></a>
            <ul>
                <li><a href="{{route('report.ppn_in')}}"><i
                    class="material-icons-outlined">arrow_right</i>Laporan PPn Masukan</a></li>
                <li><a href="{{route('report.ppn_out')}}"><i
                    class="material-icons-outlined">arrow_right</i>Laporan PPn Keluaran</a></li>
                <li><a href="{{route('report.ppn_out.xml')}}"><i
                    class="material-icons-outlined">arrow_right</i>Export XML PPn Keluaran</a></li>
            </ul>
        </li>
        @endif
        @endif
    </ul>

        </ul>
    </div>
    <div class="sidebar-bottom gap-4">
        <div class="dark-mode">
            <a href="javascript:;" class="footer-icon dark-mode-icon">
                <i class="material-icons-outlined">dark_mode</i>
            </a>
        </div>
    </div>
</aside>
