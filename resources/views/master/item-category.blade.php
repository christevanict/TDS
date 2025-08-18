@extends('layouts.master')

@section('title', 'Master Kategori Barang')

@section('css')
<style>
    .clickable-row {
        cursor: pointer;
    }

    .clickable-row:hover, .clickable-row:focus {
        background-color: #f1f1f1;
    }
</style>
@endsection

@section('content')
<x-page-title title="Master" pagetitle="Kategori Barang" />
<hr>
<div class="card">
    <div class="card-body">
        <h6 class="mb-2 text-uppercase">Kategori Barang</h6>
        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" onclick="cancelEdit()" data-bs-target="#modalInput"
        @if(!in_array('create', $privileges)) disabled @endif
        >
            Tambah Baru
        </button>
        <div class="table-responsive">
            <table id="example" class="table table-hover table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Kategori Barang</th>
                        <th>Kategori Barang</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($itemCategories as $itemCategory)
                        <tr class='clickable-row' data-bs-toggle="modal" data-bs-target="#modalInput" onclick="editItemCategory(
                        '{{ addslashes($itemCategory->item_category_code?? '') }}',
                        '{{ addslashes($itemCategory->item_category_name?? '') }}',
                            '{{ addslashes($itemCategory->company_code?? '') }}',
                            '{{ addslashes($itemCategory->acc_number_purchase?? '') }}',
                            '{{ addslashes($itemCategory->acc_number_purchase_return?? '') }}',
                            '{{ addslashes($itemCategory->acc_number_purchase_discount?? '') }}',
                            '{{ addslashes($itemCategory->acc_number_sales?? '') }}',
                            '{{ addslashes($itemCategory->acc_number_sales_return?? '') }}',
                            '{{ addslashes($itemCategory->acc_number_sales_discount?? '') }}',
                            '{{ addslashes($itemCategory->acc_number_grpo?? '') }}',
                            '{{ addslashes($itemCategory->acc_number_do?? '') }}',
                            '{{ addslashes($itemCategory->acc_number_wip?? '') }}',
                            '{{ addslashes($itemCategory->acc_number_wip_variance?? '') }}',
                            '{{ addslashes($itemCategory->account_inventory?? '') }}',
                            '{{ addslashes($itemCategory->acc_cogs?? '') }}',
                            @if (Auth::user()->role != 1 &&Auth::user()->role != 2)
                                true
                            @else
                                false
                            @endif
                        )">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $itemCategory->item_category_code }}</td>
                            <td>{{ $itemCategory->item_category_name }}</td>
                            <td>
                                <button class="btn btn-sm btn-warning"
                                onclick="editItemCategory(
                                    '{{ addslashes($itemCategory->company_code?? '') }}',
                                    '{{ addslashes($itemCategory->acc_number_purchase?? '') }}',
                                    '{{ addslashes($itemCategory->acc_number_purchase_return?? '') }}',
                                    '{{ addslashes($itemCategory->acc_number_purchase_discount?? '') }}',
                                    '{{ addslashes($itemCategory->acc_number_sales?? '') }}',
                                    '{{ addslashes($itemCategory->acc_number_sales_return?? '') }}',
                                    '{{ addslashes($itemCategory->acc_number_sales_discount?? '') }}',
                                    '{{ addslashes($itemCategory->acc_number_grpo?? '') }}',
                                    '{{ addslashes($itemCategory->acc_number_do?? '') }}',
                                    '{{ addslashes($itemCategory->acc_number_wip?? '') }}',
                                    '{{ addslashes($itemCategory->acc_number_wip_variance?? '') }}',
                                    '{{ addslashes($itemCategory->account_inventory?? '') }}',
                                    '{{ addslashes($itemCategory->acc_cogs?? '') }}',
                                    @if (Auth::user()->role != 1 &&Auth::user()->role != 2)
                                        true
                                    @else
                                        false
                                    @endif
                                )"><i class="material-icons-outlined">edit</i></button>

                                <form id="delete-form-{{ $itemCategory->item_category_code }}" action="{{ url('/TDS/master/item-category/delete/' . $itemCategory->item_category_code) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('POST')
                                    <button type="button" @if(!in_array('delete', $privileges)) disabled @endif class="btn btn-sm btn-danger" onclick="confirmDelete(event, '{{ $itemCategory->item_category_code }}')"
                                        ><i class="material-icons-outlined">delete</i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>

                <tfoot>
                    <tr>
                        <th>No</th>
                        <th>Kode Kategori Barang</th>
                        <th>Kategori Barang</th>
                        <th>Action</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalInput" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <legend id="legendForm">Input Kategori Barang</legend>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form name="item-category-form" id="item-category-form" method="post" action="{{ url('master/item-category/insert') }}">
                    @csrf
                    {{-- <label for="item_category_code" class="form-label">Kategori Barang Code</label>
                    <div class="input-group mb-3">
                        <input type="text" id="item_category_code" name="item_category_code" class="form-control" placeholder="Kategori Barang Code" aria-label="item_category_code" aria-describedby="basic-addon1" required>
                    </div> --}}
                    <label for="item_category" class="form-label">Nama Kategori Barang</label>
                    <div class="input-group mb-3">
                        <input type="text" id="item_category_name" name="item_category_name" class="form-control" placeholder="Kategori Barang" aria-label="item_category" aria-describedby="basic-addon1" required value="SERVICE">
                    </div>
                    <label hidden for="company_code" class="form-label">Company</label>
                    <div class="input-group mb-3">
                        <select hidden class="form-select" id="company_code" name="company_code" required>
                            @foreach ($companies as $company)
                                <option value={{$company->company_code}}>{{$company->company_name.' ('.$company->company_code.')'}}</option>
                            @endforeach
                        </select>
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Account Number Purchase</label>
                    <div class="form-group mb-3">
                        <div class="input-group">
                            <input type="text" id="search-acc-purchase" class="form-control" placeholder="Search by Account Number or Account Name" >
                            <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-purchase')"><i class="material-icons-outlined">edit</i></button>
                        </div>
                        <div id="search-result-acc-purchase" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                            <!-- Search results will be injected here -->
                        </div>
                        <input type="hidden" name="acc_number_purchase" id="acc_number_purchase">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Account Number Purchase Return</label>
                    <div class="form-group mb-3">
                        <div class="input-group">
                            <input type="text" id="search-acc-purchase-return" class="form-control" placeholder="Search by Account Number or Account Name" >
                            <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-purchase-return')"><i class="material-icons-outlined">edit</i></button>
                        </div>
                        <div id="search-result-acc-purchase-return" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                            <!-- Search results will be injected here -->
                        </div>
                        <input type="hidden" name="acc_number_purchase_return" id="acc_number_purchase_return">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Account Number Purchase Discount</label>
                    <div class="form-group mb-3">
                        <div class="input-group">
                            <input type="text" id="search-acc-purchase-disc" class="form-control" placeholder="Search by Account Number or Account Name" >
                            <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-purchase-disc')"><i class="material-icons-outlined">edit</i></button>
                        </div>
                        <div id="search-result-acc-purchase-disc" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                            <!-- Search results will be injected here -->
                        </div>
                        <input type="hidden" name="acc_number_purchase_discount" id="acc_number_purchase_discount">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Account Number Sales</label>
                    <div class="form-group mb-3">
                        <div class="input-group">
                            <input type="text" id="search-acc-sales" class="form-control" placeholder="Search by Account Number or Account Name" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-sales')"><i class="material-icons-outlined">edit</i></button>
                        </div>
                        <div id="search-result-acc-sales" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                            <!-- Search results will be injected here -->
                        </div>
                        <input type="hidden" name="acc_number_sales" id="acc_number_sales">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Account Number Sales Return</label>
                    <div class="form-group mb-3">
                        <div class="input-group">
                            <input type="text" id="search-acc-sales-return" class="form-control" placeholder="Search by Account Number or Account Name" >
                            <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-sales-return')"><i class="material-icons-outlined">edit</i></button>
                        </div>
                        <div id="search-result-acc-sales-return" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                            <!-- Search results will be injected here -->
                        </div>
                        <input type="hidden" name="acc_number_sales_return" id="acc_number_sales_return">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Account Number Sales Discount</label>
                    <div class="form-group mb-3">
                        <div class="input-group">
                            <input type="text" id="search-acc-sales-disc" class="form-control" placeholder="Search by Account Number or Account Name" >
                            <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-sales-disc')"><i class="material-icons-outlined">edit</i></button>
                        </div>
                        <div id="search-result-acc-sales-disc" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                            <!-- Search results will be injected here -->
                        </div>
                        <input type="hidden" name="acc_number_sales_discount" id="acc_number_sales_discount">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Account Number GRPO</label>
                    <div class="form-group mb-3">
                        <div class="input-group">
                            <input type="text" id="search-acc-grpo" class="form-control" placeholder="Search by Account Number or Account Name" >
                            <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-grpo')"><i class="material-icons-outlined">edit</i></button>
                        </div>
                        <div id="search-result-acc-grpo" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                            <!-- Search results will be injected here -->
                        </div>
                        <input type="hidden" name="acc_number_grpo" id="acc_number_grpo">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Account Number DO</label>
                    <div class="form-group mb-3">
                        <div class="input-group">
                            <input type="text" id="search-acc-do" class="form-control" placeholder="Search by Account Number or Account Name" >
                            <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-do')"><i class="material-icons-outlined">edit</i></button>
                        </div>
                        <div id="search-result-acc-do" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                            <!-- Search results will be injected here -->
                        </div>
                        <input type="hidden" name="acc_number_do" id="acc_number_do">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Account Number WIP</label>
                    <div class="form-group mb-3">
                        <div class="input-group">
                            <input type="text" id="search-acc-wip" class="form-control" placeholder="Search by Account Number or Account Name" >
                            <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-wip')"><i class="material-icons-outlined">edit</i></button>
                        </div>
                        <div id="search-result-acc-wip" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                            <!-- Search results will be injected here -->
                        </div>
                        <input type="hidden" name="acc_number_wip" id="acc_number_wip">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Account Number WIP Variance</label>
                    <div class="form-group mb-3">
                        <div class="input-group">
                            <input type="text" id="search-acc-wip-var" class="form-control" placeholder="Search by Account Number or Account Name" >
                            <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-wip-var')"><i class="material-icons-outlined">edit</i></button>
                        </div>
                        <div id="search-result-acc-wip-var" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                            <!-- Search results will be injected here -->
                        </div>
                        <input type="hidden" name="acc_number_wip_variance" id="acc_number_wip_variance">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Account Inventory</label>
                    <div class="form-group mb-3">
                        <div class="input-group">
                            <input type="text" id="search-acc-inv" class="form-control" placeholder="Search by Account Number or Account Name" >
                            <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-inv')"><i class="material-icons-outlined">edit</i></button>
                        </div>
                        <div id="search-result-acc-inv" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                            <!-- Search results will be injected here -->
                        </div>
                        <input type="hidden" name="account_inventory" id="account_inventory">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Account COGS</label>
                    <div class="form-group mb-3">
                        <div class="input-group">
                            <input type="text" id="search-acc-cogs" class="form-control" placeholder="Search by Account Number or Account Name" >
                            <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-cogs')"><i class="material-icons-outlined">edit</i></button>
                        </div>
                        <div id="search-result-acc-cogs" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                            <!-- Search results will be injected here -->
                        </div>
                        <input type="hidden" name="acc_cogs" id="acc_cogs">
                    </div>
                    <button id="btn-action" name="btn-action" type="submit" class="btn btn-primary btn-md"
                    @if(Auth::user()->role == 1)
                        style="display: none"
                    @endif
                    >Insert</button>

                    <button type="button" class="btn btn-danger" id="cancelButton" style="display:none;" data-bs-dismiss="modal" onclick="cancelEdit()">Cancel</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="cancelEdit()">Close</button>
            </div>
        </div>
    </div>
</div>

@if (session('success'))
    <script>
        Swal.fire({
            title: 'Success!',
            text: "{{ session('success') }}",
            icon: 'success',
            confirmButtonText: 'OK'
        });
    </script>
@endif

@if (session('error'))
    <script>
        Swal.fire({
            title: 'Error!',
            text: "{{ session('error') }}",
            icon: 'error',
            confirmButtonText: 'OK'
        });
    </script>
@endif

@endsection

@section('scripts')
<script src="{{ URL::asset('build/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('build/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#example').DataTable();
      } );
</script>
<script>
    $(document).ready(function() {
        var table = $('#example2').DataTable( {
            lengthChange: false,
            buttons: [ 'copy', 'excel', 'pdf', 'print']
        } );

        table.buttons().container()
            .appendTo( '#example2_wrapper .col-md-6:eq(0)' );
    } );
</script>
<script>

    function confirmDelete(event, id) {
        event.stopPropagation();
        event.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0c6efd',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
    let privileges = @json($privileges);
    function cancelEdit() {
        // document.getElementById('item_category_code').value = '';
        document.getElementById('item_category_name').value = 'SERVICE';
        document.getElementById('search-acc-purchase').value ='';
        document.getElementById('search-acc-purchase').readOnly = false;

        document.getElementById('search-acc-purchase-return').value ='';
        document.getElementById('search-acc-purchase-return').readOnly = false;

        document.getElementById('search-acc-purchase-disc').value ='';
        document.getElementById('search-acc-purchase-disc').readOnly = false;

        document.getElementById('search-acc-sales').value ='';
        document.getElementById('search-acc-sales').readOnly = false;

        document.getElementById('search-acc-sales-return').value ='';
        document.getElementById('search-acc-sales-return').readOnly = false;

        document.getElementById('search-acc-sales-disc').value = '';
        document.getElementById('search-acc-sales-disc').readOnly = false;

        document.getElementById('search-acc-grpo').value = '';
        document.getElementById('search-acc-grpo').readOnly = false;

        document.getElementById('search-acc-do').value = '';
        document.getElementById('search-acc-do').readOnly = false;

        document.getElementById('search-acc-wip').value = '';
        document.getElementById('search-acc-wip').readOnly = false;

        document.getElementById('search-acc-wip-var').value = '';
        document.getElementById('search-acc-wip-var').readOnly = false;

        document.getElementById('search-acc-inv').value = '';
        document.getElementById('search-acc-inv').readOnly = false;

        document.getElementById('search-acc-cogs').value = '';
        document.getElementById('search-acc-cogs').readOnly = false;
        document.getElementById('legendForm').innerText = 'Kategori Barang Insert';
        document.getElementById('cancelButton').style.display = 'none';
        document.getElementById('btn-action').innerText = 'Insert';
        document.getElementById('btn-action').disabled=false;
        document.getElementById('item-category-form').action = `/TDS/master/item-category/insert`;
        if(!privileges.includes('create')){
                document.getElementById('btn-action').disabled =true
            }else{
                document.getElementById('btn-action').disabled =false
            }
    }

    function editItemCategory(item_category_code, item_category_name, company_code,acc_number_purchase,acc_number_purchase_return,acc_number_purchase_discount, acc_number_sales,acc_number_sales_return,acc_number_sales_discount,acc_number_grpo,acc_number_do,acc_number_wip,acc_number_wip_variance,account_inventory,acc_cogs,update) {

        document.getElementById('btn-action').innerText = 'Edit';
        if(!update){
            document.getElementById('btn-action').style.display = 'none';
        }else{
            document.getElementById('btn-action').style.display = 'inline';
        }
        // document.getElementById('item_category_code').value = item_category_code;
        if(item_category_name.toLowerCase()=='service'){
            document.getElementById('item_category_name').readOnly = true;
        }
        document.getElementById('item_category_name').value = item_category_name;
        document.getElementById('company_code').value = company_code;
        let textDisplay ='';
        if(acc_number_purchase){
            document.getElementById('acc_number_purchase').value = acc_number_purchase;
            textDisplay = coas.find((element)=>element.account_number ==acc_number_purchase).account_name;
            document.getElementById('search-acc-purchase').value = acc_number_purchase+' - '+textDisplay;
            document.getElementById('search-acc-purchase').readOnly = true;
        }

        if(acc_number_purchase_return){
            document.getElementById('acc_number_purchase_return').value = acc_number_purchase_return;
            textDisplay = coas.find((element)=>element.account_number ==acc_number_purchase_return).account_name;
            document.getElementById('search-acc-purchase-return').value = acc_number_purchase_return+' - '+textDisplay;
            document.getElementById('search-acc-purchase-return').readOnly = true;
        }

        if(acc_number_purchase_discount){
            document.getElementById('acc_number_purchase_discount').value = acc_number_purchase_discount;
            textDisplay = coas.find((element)=>element.account_number ==acc_number_purchase_discount).account_name;
            document.getElementById('search-acc-purchase-disc').value = acc_number_purchase_discount+' - '+textDisplay;
            document.getElementById('search-acc-purchase-disc').readOnly = true;
        }

        if(acc_number_sales){
            document.getElementById('acc_number_sales').value = acc_number_sales;
            textDisplay = coas.find((element)=>element.account_number ==acc_number_sales).account_name;
            document.getElementById('search-acc-sales').value = acc_number_sales+' - '+textDisplay;
            document.getElementById('search-acc-sales').readOnly = true;
        }

        if(acc_number_sales_return){
            document.getElementById('acc_number_sales_return').value = acc_number_sales_return;
            textDisplay = coas.find((element)=>element.account_number ==acc_number_sales_return).account_name;
            document.getElementById('search-acc-sales-return').value = acc_number_sales_return+' - '+textDisplay;
            document.getElementById('search-acc-sales-return').readOnly = true;
        }

        if(acc_number_sales_discount){
            document.getElementById('acc_number_sales_discount').value = acc_number_sales_discount;
            textDisplay = coas.find((element)=>element.account_number ==acc_number_sales_discount).account_name;
            document.getElementById('search-acc-sales-disc').value = acc_number_sales_discount+' - '+textDisplay;
            document.getElementById('search-acc-sales-disc').readOnly = true;
        }

        if(acc_number_grpo){
            document.getElementById('acc_number_grpo').value = acc_number_grpo;
            textDisplay = coas.find((element)=>element.account_number ==acc_number_grpo).account_name;
            document.getElementById('search-acc-grpo').value = acc_number_grpo+' - '+textDisplay;
            document.getElementById('search-acc-grpo').readOnly = true;
        }

        if(acc_number_do){
            document.getElementById('acc_number_do').value = acc_number_do;
            textDisplay = coas.find((element)=>element.account_number ==acc_number_do).account_name;
            document.getElementById('search-acc-do').value = acc_number_do+' - '+textDisplay;
            document.getElementById('search-acc-do').readOnly = true;
        }

        if(acc_number_wip){
            document.getElementById('acc_number_wip').value = acc_number_wip;
            textDisplay = coas.find((element)=>element.account_number ==acc_number_wip).account_name;
            document.getElementById('search-acc-wip').value = acc_number_wip+' - '+textDisplay;
            document.getElementById('search-acc-wip').readOnly = true;
        }

        if(acc_number_wip_variance){
            document.getElementById('acc_number_wip_variance').value = acc_number_wip_variance;
            textDisplay = coas.find((element)=>element.account_number ==acc_number_wip_variance).account_name;
            document.getElementById('search-acc-wip-var').value = acc_number_wip_variance+' - '+textDisplay;
            document.getElementById('search-acc-wip-var').readOnly = true;
        }

        if(account_inventory){
            document.getElementById('account_inventory').value = account_inventory;
            textDisplay = coas.find((element)=>element.account_number ==account_inventory).account_name;
            document.getElementById('search-acc-inv').value = account_inventory+' - '+textDisplay;
            document.getElementById('search-acc-inv').readOnly = true;
        }

        if(acc_cogs){
            document.getElementById('acc_cogs').value = acc_cogs;
            textDisplay = coas.find((element)=>element.account_number ==acc_cogs).account_name;
            document.getElementById('search-acc-cogs').value = acc_cogs+' - '+textDisplay;
            document.getElementById('search-acc-cogs').readOnly = true;
        }

        document.getElementById('legendForm').innerText = 'Kategori Barang Update';
        document.getElementById('cancelButton').style.display = 'inline-block';

        document.getElementById('item-category-form').action = `/TDS/master/item-category/edit/${item_category_code}`;

        if(!privileges.includes('update')){
            document.getElementById('btn-action').disabled =true
        }else{
            document.getElementById('btn-action').disabled =false
        }
    }

    // Get the company and account_payable select elements
    var companySelect = document.getElementById('company_code');
    var accNumPurcSelect = document.getElementById('acc_number_purchase');
    var accNumPurcReturnSelect = document.getElementById('acc_number_purchase_return');
    var accNumPurcDiscSelect = document.getElementById('acc_number_purchase_discount');
    var accNumSalesSelect = document.getElementById('acc_number_sales');
    var accNumSalesReturnSelect = document.getElementById('acc_number_sales_return');
    var accNumSalesDiscSelect = document.getElementById('acc_number_sales_discount');
    var accNumGrpoSelect = document.getElementById('acc_number_grpo');
    var accNumDoSelect = document.getElementById('acc_number_do');
    var accNumWipSelect = document.getElementById('acc_number_wip');
    var accNumWipVarSelect = document.getElementById('acc_number_wip_variance');
    var accInvenSelect = document.getElementById('account_inventory');
    var accCogsSelect = document.getElementById('acc_cogs');

    accNumPurcSelect.value = '';
        accNumPurcReturnSelect.value = '';
        accNumPurcDiscSelect.value = '';
        accNumSalesSelect.value = '';
        accNumSalesReturnSelect.value = '';
        accNumSalesDiscSelect.value = '';
        accNumGrpoSelect.value = '';
        accNumDoSelect.value = '';
        accNumWipSelect.value = '';
        accNumWipVarSelect.value = '';
        accInvenSelect.value = '';
        accCogsSelect.value = '';

        const coas = @json($coas);

        function setupSearch(inputId, resultsContainerId,inputHid) {
            const inputElement = document.getElementById(inputId);
            const resultsContainer = document.getElementById(resultsContainerId);

            inputElement.addEventListener('input', function () {
                activeIndex = -1;
                let query = this.value.toLowerCase();
                resultsContainer.innerHTML = '';
                resultsContainer.style.display = 'none';

                if (query.length > 0) {
                    let filteredResults = coas.filter(item =>
                        item.account_number.toLowerCase().includes(query) ||
                        item.account_name.toLowerCase().includes(query)
                    );

                    if (filteredResults.length > 0) {
                        resultsContainer.style.display = 'block';
                        filteredResults.forEach(item => {
                            let listItem = document.createElement('a');
                            listItem.className = 'list-group-item list-group-item-action';
                            listItem.href = '#';
                            listItem.innerHTML = `
                                <strong>${item.account_number}</strong> -
                                ${item.account_name} <br>`;
                            listItem.addEventListener('click', function(e) {
                                e.preventDefault();
                                inputElement.value = item.account_number + ' - ' + item.account_name;
                                inputElement.readOnly = true;
                                document.getElementById(inputHid).value = item.account_number;
                                resultsContainer.style.display = 'none';
                            });
                            resultsContainer.appendChild(listItem);
                        });
                    }
                }
            });
            // Keydown event listener for navigation
            inputElement.addEventListener('keydown', function(e) {
                const items = resultsContainer.querySelectorAll('.list-group-item');
                if (items.length === 0) return;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (activeIndex < items.length - 1) {
                        activeIndex++;
                        updateActiveItem(items);
                    }
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (activeIndex > -1) { // Allow going back to no selection
                        activeIndex--;
                        updateActiveItem(items);
                    }
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (activeIndex >= 0 && items[activeIndex]) {
                        items[activeIndex].click();
                    }
                }
            });
        }

        function clearInput(inputId) {
            document.getElementById(inputId).value = '';
            document.getElementById(inputId).readOnly = false;
        }
        function updateActiveItem(items) {
            items.forEach((item, index) => {
                item.classList.toggle('active', index === activeIndex);
            });
            if (activeIndex >= 0) {
                items[activeIndex].scrollIntoView({ block: 'nearest' });
            }
        }
        setupSearch('search-acc-purchase', 'search-result-acc-purchase','acc_number_purchase');
        setupSearch('search-acc-purchase-return', 'search-result-acc-purchase-return','acc_number_purchase_return');
        setupSearch('search-acc-purchase-disc', 'search-result-acc-purchase-disc','acc_number_purchase_discount');
        setupSearch('search-acc-sales', 'search-result-acc-sales','acc_number_sales');
        setupSearch('search-acc-sales-return', 'search-result-acc-sales-return','acc_number_sales_return');
        setupSearch('search-acc-sales-disc', 'search-result-acc-sales-disc','acc_number_sales_discount');
        setupSearch('search-acc-grpo', 'search-result-acc-grpo','acc_number_grpo');
        setupSearch('search-acc-do', 'search-result-acc-do','acc_number_do');
        setupSearch('search-acc-wip', 'search-result-acc-wip','acc_number_wip');
        setupSearch('search-acc-wip-var', 'search-result-acc-wip-var','acc_number_wip_variance');
        setupSearch('search-acc-inv', 'search-result-acc-inv','account_inventory');
        setupSearch('search-acc-cogs', 'search-result-acc-cogs','acc_cogs');
</script>
@endsection
