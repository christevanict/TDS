@extends('layouts.master')

@section('title', 'Master User')
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
<x-page-title title="Master" pagetitle="Role" />
		<hr>
		<div class="card">
			<div class="card-body">
                <div class="container">
                    <h2>Role</h2>
                    <form id="form-role" method="POST" action="{{ route('roles.create') }}">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="role_number" class="form-label">Nomor Role</label>
                                <input type="text" class="form-control" id="role_number" name="role_number" readonly required>
                            </div>
                            <div class="col-md-6">
                                <label for="name" class="form-label">Nama Role</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>

                        <table class="table table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>
                                        <div class="form-check">
                                            <input type="checkbox" id="selectAllMenus" class="form-check-input">
                                            <label class="form-check-label" for="selectAllMenus">Menu</label>
                                        </div>
                                    </th>
                                    @php $actions = ['read', 'create', 'update','cancel', 'delete', 'print','discount','price']; @endphp
                                    @foreach($actions as $action)
                                    <th class="text-center">
                                        <div class="form-check d-inline-block">
                                            <input type="checkbox" id="selectAll{{ ucfirst($action) }}"
                                                class="form-check-input select-column" data-action="{{ $action }}">
                                            <label class="form-check-label" for="selectAll{{ ucfirst($action) }}">
                                                {{ ucfirst($action) }}
                                            </label>
                                        </div>
                                    </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $menus = [
                                        'master_user' => 'Master User',
                                        'master_salesman' => 'Master Salesman',
                                        'master_city' => 'Master City',
                                        'master_zone' => 'Master Zone',
                                        'master_role' => 'Master Role',
                                        'master_coa' => 'Master COA',
                                        'master_customer' => 'Master Pelanggan',
                                        'master_supplier' => 'Master Pemasok',
                                        'master_department' => 'Master Departemen',
                                        'master_warehouse' => 'Master Warehouse',
                                        'master_payment_method' => 'Master Metode Pembayaran',
                                        'master_tax_master' => 'Master Pajak',
                                        'master_item' => 'Master Barang',
                                        'master_item_sales' => 'Master Harga Beli',
                                        'master_item_purchase' => 'Master Harga Jual',
                                        'pbr'=>'Sales PBR',
                                        'purchase_order' => __('Purchase Order'),
                                        'sales_order' => __('Sales Order'),
                                        'purchase_invoice' => __('Purchase Invoice'),
                                        'sales_invoice' => __('Sales Invoice'),
                                        'purchase_return' => __('Purchase Return'),
                                        'sales_return' => __('Sales Return'),
                                        'good_receipt' => __('Good Receipt'),
                                        'receivable_list' => 'Tanda Terima',
                                        'receivable_list_salesman' => 'Daftar Tagihan',
                                        'bank_in' => 'Kas Masuk',
                                        'bank_out' => 'Kas Keluar',
                                        'general_journal' => 'Jurnal Umum',
                                        'payable_payment' => 'Pelunasan Hutang',
                                        'receivable_payment' => 'Pelunasan Piutang',
                                        'debt_other' => 'Hutang Lain',
                                        'closing' => 'Closing',
                                        'accounting_report' => 'Laporan Akuntansi',
                                        'inventory_report' => 'Laporan Stok Barang',
                                        'inventory_report' => 'Laporan Stok Barang',
                                        'system_report' => 'Laporan Sistem',

                                    ];
                                @endphp

                                @foreach($menus as $key => $label)
                                <tr>
                                    <td>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input select-row"
                                                   data-menu="{{ $key }}">
                                            <label class="form-check-label">{{ $label }}</label>
                                        </div>
                                    </td>
                                    @foreach($actions as $action)
                                    <td class="text-center">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input action-checkbox" type="checkbox"
                                                   name="privileges[{{ $key }}][{{ $action }}]"
                                                   value="1"
                                                   data-menu="{{ $key }}"
                                                   data-action="{{ $action }}">
                                        </div>
                                    </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <button type="button" onclick="resetForm()" class="btn btn-primary">Reset</button>
                        <button type="submit" id="btnSubmit" class="btn btn-success" @if(!in_array('create', $privileges)) disabled @endif>Tambah Role</button>
                    </form>
                </div>
			</div>
		</div>

        <div class="card">
			<div class="card-body">
                <div class="container">
                    <div class="mt-5">
                        <h3>Role</h3>
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nomor Role</th>
                                    <th>Nama Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($roles as $role)
                                <tr class="role-row"
                                    data-id="{{ $role->id }}"
                                    data-role-number="{{ $role->role_number }}"
                                    data-name="{{ $role->name }}"
                                    data-privileges="{{ json_encode($role->privileges) }}"
                                    style="cursor: pointer;">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $role->role_number }}</td>
                                    <td>{{ $role->name }}</td>
                                    <td>
                                        <form id="delete-form-{{ $role->id }}"
                                            action="{{ url('/TDS/master/role/delete/' . $role->id) }}" method="POST"
                                            style="display:inline;">
                                            @csrf
                                            <button type="button" class="btn btn-sm btn-danger"
                                                onclick="confirmDelete(event, '{{ $role->id }}')"
                                                @if (!in_array('delete', $privileges))
                                                    disabled
                                                @endif
                                                ><i
                                                    class="material-icons-outlined">delete</i></button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
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
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
            </script>
        @endif

@endsection
@section('scripts')
<script>

    let privil = @json($privileges);

function resetForm() {
        const form = document.getElementById('form-role');
        form.action = "{{ route('roles.create') }}";
        form.reset();
        document.getElementById('btnSubmit').innerHTML = 'Tambah Role';
        document.querySelectorAll('.action-checkbox').forEach(cb => cb.checked = false);
        document.querySelectorAll('.select-row').forEach(cb => cb.checked = false);
        document.querySelectorAll('.select-column').forEach(cb => cb.checked = false);
        document.getElementById('selectAllMenus').checked = false;
        if(!privil.includes('create')){
            document.getElementById('btnSubmit').disabled=true;
        }else{
            document.getElementById('btnSubmit').disabled=false;
        }
    }

    function confirmDelete(event, id) {
        event.stopPropagation();
        Swal.fire({
            title: 'Are you sure want delete this role',
            text : "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0c6efd',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
document.addEventListener('DOMContentLoaded', function() {

    // Select All Functionality // 1. Row Selection
    document.querySelectorAll('.select-row').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            console.log('a');

            const menu = this.dataset.menu;
            const isChecked = this.checked;
            this.closest('tr').querySelectorAll('.action-checkbox')
                .forEach(cb => cb.checked = isChecked);
            updateColumnCheckboxes();
            updateMasterCheckbox();
        });
    });

    // 2. Column Selection
    document.querySelectorAll('.select-column').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            console.log('b');
            const action = this.dataset.action;
            const isChecked = this.checked;
            document.querySelectorAll(`.action-checkbox[data-action="${action}"]`)
                .forEach(cb => cb.checked = isChecked);
            updateRowCheckboxes();
            updateMasterCheckbox();
        });
    });

    // 3. Master Selection
    document.getElementById('selectAllMenus').addEventListener('change', function() {
        console.log('c');
        const isChecked = this.checked;
        document.querySelectorAll('.action-checkbox').forEach(cb => cb.checked = isChecked);
        document.querySelectorAll('.select-row, .select-column').forEach(cb => cb.checked = isChecked);
    });

    // 4. Individual Checkbox Handler
    document.querySelectorAll('.action-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            console.log('d');
            updateRowCheckboxes();
            updateColumnCheckboxes();
            updateMasterCheckbox();
        });
    });

    // Update Functions =======================================================
    function updateRowCheckboxes() {
        document.querySelectorAll('.select-row').forEach(rowCheckbox => {
            const menu = rowCheckbox.dataset.menu;
            const checkboxes = rowCheckbox.closest('tr').querySelectorAll('.action-checkbox');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            rowCheckbox.checked = allChecked;
        });
    }

    function updateColumnCheckboxes() {
        document.querySelectorAll('.select-column').forEach(columnCheckbox => {
            const action = columnCheckbox.dataset.action;
            const checkboxes = document.querySelectorAll(`.action-checkbox[data-action="${action}"]`);
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            columnCheckbox.checked = allChecked;
        });
    }

    function updateMasterCheckbox() {
        const allChecked = Array.from(document.querySelectorAll('.action-checkbox'))
            .every(cb => cb.checked);
        document.getElementById('selectAllMenus').checked = allChecked;
    }



    // Role Editing Functionality =============================================
    document.querySelectorAll('.role-row').forEach(row => {
        row.addEventListener('click', function() {
            resetForm();
            document.getElementById('btnSubmit').innerHTML = 'Ubah Role';
            const form = document.getElementById('form-role');
            form.action = `/TDS/master/role/edit/${this.dataset.id}`;
            form.innerHTML += '<input type="hidden" name="_method" value="POST">';
            // Populate form fields
            document.getElementById('role_number').value = this.dataset.roleNumber;
            document.getElementById('name').value = this.dataset.name;
            if(!privil.includes('update')){
                document.getElementById('btnSubmit').disabled=true;
            }
            // Set checkboxes
            const privileges = JSON.parse(this.dataset.privileges);
            Object.entries(privileges).forEach(([menu, actions]) => {
                actions.forEach(action => {
                    const checkbox = document.querySelector(
                        `input[name="privileges[${menu}][${action}]"]`
                    );
                    if(checkbox) checkbox.checked = true;
                });
            });

            document.querySelectorAll('.select-row').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            console.log('a');

            const menu = this.dataset.menu;
            const isChecked = this.checked;
            this.closest('tr').querySelectorAll('.action-checkbox')
                .forEach(cb => cb.checked = isChecked);
            updateColumnCheckboxes();
            updateMasterCheckbox();
        });
    });

    // 2. Column Selection
    document.querySelectorAll('.select-column').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            console.log('b');
            const action = this.dataset.action;
            const isChecked = this.checked;
            document.querySelectorAll(`.action-checkbox[data-action="${action}"]`)
                .forEach(cb => cb.checked = isChecked);
            updateRowCheckboxes();
            updateMasterCheckbox();
        });
    });

    // 3. Master Selection
    document.getElementById('selectAllMenus').addEventListener('change', function() {
        console.log('c');
        const isChecked = this.checked;
        document.querySelectorAll('.action-checkbox').forEach(cb => cb.checked = isChecked);
        document.querySelectorAll('.select-row, .select-column').forEach(cb => cb.checked = isChecked);
    });

    // 4. Individual Checkbox Handler
    document.querySelectorAll('.action-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            console.log('d');
            updateRowCheckboxes();
            updateColumnCheckboxes();
            updateMasterCheckbox();
        });
    });

            // Update all parent checkboxes
            updateRowCheckboxes();
            updateColumnCheckboxes();
            updateMasterCheckbox();

            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });

    // Reset Functionality ====================================================


    document.addEventListener('click', function(e) {
        if(!e.target.closest('.role-row') && !e.target.closest('#form-role')) {
            resetForm();
        }
    });

    // Delete Confirmation ====================================================

});
</script>
@endsection
