@extends('layouts.master')

@section('title', 'Rangkuman ' . __('Sales Order'))

@section('css')
    <style>
        .clickable-row {
            cursor: pointer;
        }

        .clickable-row:hover,
        .clickable-row:focus {
            background-color: #f1f1f1;
        }

        .btn-insert {
            margin-bottom: 20px;
        }

        .btn-print,
        .btn-edit {
            margin-right: 10px;
        }

        .date-filter {
            margin-bottom: 20px;
        }

        .total-amount-row td {
            font-weight: bold;
            text-align: right;
            background-color: #f8f9fa;
        }

        /* Style for column filter inputs */
        .form-control {
            width: 100%;
            padding: 3px;
            margin-bottom: 5px;
            font-size: 12px;
        }
    </style>
@endsection

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
    <x-page-title title="{{__('Sales Order')}}" pagetitle="Rangkuman {{__('Sales Order')}}" />
    <hr>

    <!-- Date and Status Filter Form -->
    <div class="mb-3 card">
        <div class="card-body">
            <form method="GET" action="{{ route('transaction.sales_order.summary_detail') }}" id="filterForm">
                <div class="row">
                    <div class="col-md-3">
                        <label for="from_date">From Date</label>
                        <input type="date" name="from_date" id="from_date" class="form-control date-picker"
                            value="{{ request()->from_date }}">
                    </div>
                    <div class="col-md-3">
                        <label for="to_date">To Date</label>
                        <input type="date" name="to_date" id="to_date" class="form-control date-picker"
                            value="{{ request()->to_date }}">
                    </div>
                    <div class="col-md-3">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">All</option>
                            <option value="Open" {{ request()->status == 'Open' ? 'selected' : '' }}>Open</option>
                            <option value="Partial" {{ request()->status == 'Partial' ? 'selected' : '' }}>Partial</option>
                            <option value="Closed" {{ request()->status == 'Closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>
                    <div class="col-auto d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h6 class="mb-2 text-uppercase">Rangkuman {{__('Sales Order')}}</h6>
            <div class="table-responsive">
                <table id="example" class="table table-hover table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>{{__('Document Date')}}</th>
                            <th>{{__('Sales Order Number')}}</th>
                            <th>Kode {{__('Customer')}}</th>
                            <th>Grup Pelanggan</th>
                            <th>Nama {{__('Customer')}}</th>
                            <th>Nama Barang</th>
                            <th>Stok</th>
                            <th>Sent</th>
                            <th>Unsent</th>
                            <th>COLY</th>
                            <th>QTY</th>
                            <th>Harga</th>
                            @if(in_array('price', $privileges))
                            <th>Nominal</th>
                            @endif
                            <th>Action</th>
                        </tr>
                        <!-- Filter Input Row -->
                        <tr class="filter-row">
                            <th></th>
                            <th><input type="text" class="form-control" placeholder="Filter Date" data-sort="false"></th>
                            <th><input type="text" class="form-control" placeholder="Filter SO Number" data-sort="false"></th>
                            <th><input type="text" class="form-control" placeholder="Filter Kode" data-sort="false"></th>
                            <th><input type="text" class="form-control" placeholder="Filter Nama" data-sort="false"></th>
                            <th><input type="text" class="form-control" placeholder="Filter Nama" data-sort="false"></th>
                            <th><input type="text" class="form-control" placeholder="Filter Barang" data-sort="false"></th>
                            <th><input type="text" class="form-control" placeholder="Filter Stok" data-sort="false"></th>
                            <th><input type="text" class="form-control" placeholder="Filter Sent" data-sort="false"></th>
                            <th><input type="text" class="form-control" placeholder="Filter Unsent" data-sort="false"></th>
                            <th><input type="text" class="form-control" placeholder="Filter COLY" data-sort="false"></th>
                            <th><input type="text" class="form-control" placeholder="Filter QTY" data-sort="false"></th>
                            <th><input type="text" class="form-control" placeholder="Filter Harga" data-sort="false"></th>
                            @if(in_array('price', $privileges))

                            <th><input type="text" class="form-control" placeholder="Filter Nominal" data-sort="false"></th>
                            @endif
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $rowNumber = 0;
                        @endphp
                        @foreach ($salesOrders as $invoice)
                            @foreach ($invoice->details as $detail)
                                @php
                                    $rowNumber++;
                                @endphp

                                <tr class="clickable-row" id="row-{{$detail['id']}}">
                                    <td>{{ $rowNumber }}</td>
                                    <td>{{ \Carbon\Carbon::parse($invoice->document_date)->format('d M Y') }}</td>
                                    <td>{{ $invoice->sales_order_number }} @if($invoice->is_pbr) <span class="badge
                                        bg-info">
                                        PBR
                                    </span> @endif</td>
                                    <td>{{ $invoice->customers->customer_code }}</td>
                                    <td>
                                        {{ $customers->firstWhere(fn($customer) => $invoice->customers->group_customer === $customer->customer_code)?->customer_name ?? '' }}
                                    </td>
                                    <td>{{ $invoice->customers->customer_name }}</td>
                                    <td>{{ $detail['items']['item_name'] ?? 'N/A' }}</td>
                                    <td>{{$detail['stock']}}</td>
                                    <td>{{ number_format($detail['qty'] - $detail['qty_left'], 0) }}</td>
                                    <td>{{ number_format($detail['qty_left'], 0) }}</td>
                                    <td>{{ number_format($detail['qty'], 0) }}</td>
                                    <td>{{ number_format($detail['qty'] * ($detail['base_qty'] ?? 1), 0) }}</td>
                                    <td>{{ number_format($detail['price'] ?? 0, 0) }}</td>
                                    @if(in_array('price', $privileges))

                                    <td class="text-end">{{ number_format($detail['nominal'], 0) }}</td>
                                    @endif
                                    <td>
                                        @if($detail['qty_left'] > 0)
                                            <button type="button" class="btn btn-danger btn-sm cancel-detail @if(!in_array('cancel', $privileges)) disabled @endif" data-id="{{$detail['id']}}">
                                                <i class="material-icons-outlined">close</i>
                                            </button>
                                        @else

                                            @if ($detail['status']=='Cancelled')
                                            <span class="badge
                                                bg-danger">
                                                Cancelled
                                            </span>
                                        @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="total-amount-row">
                            @if(in_array('price', $privileges))
                            <td colspan="13">Total untuk range tanggal terpilih:</td>
                            <td>{{ number_format($totalAmount, 0) }}</td>
                            <td></td>
                            @endif
                        </tr>
                    </tfoot>
                </table>
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
        // Debug: Verify dependencies
        console.log('jQuery loaded:', typeof jQuery);
        console.log('DataTables loaded:', typeof $.fn.DataTable);
        console.log('Table element:', $('#example').length);

        // Ensure CSRF token for AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Initialize DataTable
        console.log('Attempting to initialize DataTable');
        var table = null;
        try {
            table = $('#example').DataTable({
                lengthChange: false,
                orderCellsTop: true,
                drawCallback: function() {
                        calculateTotalAmount();
                }
            });
            console.log('DataTable initialized:', table);
        } catch (e) {
            console.error('DataTable initialization failed:', e);
        }

        // Prevent sorting when clicking filter inputs
        $('#example thead tr.filter-row input[data-sort="false"]').on('click', function(e) {
            e.stopPropagation();
        });

        // Add filtering functionality to input fields
        $('#example thead tr.filter-row input').on('keyup change', function() {
            if (!table) {
                console.error('Cannot filter: DataTable not initialized');
                return;
            }
            var columnIndex = $(this).parent().index();
            table.column(columnIndex).search(this.value).draw();
        });

        // Restore filter inputs if page is reloaded with search params
        $('#example thead tr.filter-row input').each(function() {
            if (!table) {
                console.error('Cannot restore filters: DataTable not initialized');
                return;
            }
            var columnIndex = $(this).parent().index();
            $(this).val(table.column(columnIndex).search());
        });

        // Cancel detail button handler
        $(document).on('click', '.cancel-detail', function() {
            const detailId = $(this).data('id');
            const row = $(this).closest('tr');

            Swal.fire({
                title: 'Apakah anda yakin ingin membatalkan pesanan ini?',
                text: "Anda tidak bisa mengembalikannya!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, batalkan!',
                cancelButtonText: 'Tidak jadi'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("transaction.sales_order.cancel_detail") }}',
                        type: 'POST',
                        data: {
                            detail_id: detailId
                        },
                        success: function(response) {
                            Swal.fire(
                                'Cancelled!',
                                'The detail has been cancelled.',
                                'success'
                            );
                            location.reload();
                            calculateTotalAmount();

                        },
                        error: function(xhr) {
                            Swal.fire(
                                'Error!',
                                'Something went wrong.',
                                'error'
                            );
                        }
                    });
                }
            });
        });

        // Function to calculate total amount from visible Nominal column
        function calculateTotalAmount() {
            if (!table) {
                console.error('DataTable not initialized');
                return;
            }
            let total = 0;
            table.column(13, { search: 'applied' }).data().each(function(value) {
                let numericValue = parseFloat(value.replace(/,/g, '')) || 0;
                total += numericValue;
            });
            let formattedTotal = total.toLocaleString('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
            $('#example tfoot tr.total-amount-row td').eq(1).text(formattedTotal);
        }
    });
</script>
@endsection
