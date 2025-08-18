@extends('layouts.master')

@section('title','Rangkuman '. __('Sales PBR'))

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

        /* Align the total amount row to the right */
        .total-amount-row td {
            font-weight: bold;
            text-align: right;
            background-color: #f8f9fa;
        }
    </style>
@endsection

@section('content')
    <x-page-title title="{{__('Sales PBR')}}" pagetitle="Rangkuman {{__('Sales PBR')}}" />
    <hr>

    <!-- Date Filter Form -->
    <div class="mb-3 card">
        <div class="card-body">
            <form method="GET" action="{{ route('transaction.pbr.summary_detail') }}">
                <div class="row">
                    <div class="col-md-4">
                        <label for="from_date">From Date</label>
                        <input type="date" name="from_date" id="from_date" class="form-control date-picker"
                            value="{{ request()->from_date }}">
                    </div>
                    <div class="col-md-4">
                        <label for="to_date">To Date</label>
                        <input type="date" name="to_date" id="to_date" class="form-control date-picker"
                            value="{{ request()->to_date }}">
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
            <h6 class="mb-2 text-uppercase">Rangkuman {{__('Sales PBR')}}</h6>
            <div class="table-responsive">
                <table id="example" class="table table-hover table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>{{__('Document Date')}}</th>
                            <th>Nomor {{__('Sales PBR')}}</th>
                            <th>Kode {{__('Customer')}}</th>
                            <th>Grup {{__('Customer')}}</th>
                            <th>Nama {{__('Customer')}}</th>
                            <th>Nama Barang</th>
                            <th>COLY</th>
                            <th>QTY</th>
                            <th>Harga</th>
                            <th>Diskon</th>
                            <th>Nominal</th>
                        </tr>
                        <tr class="filter-row">
                            <th></th>
                            <th><input type="text" class="form-control" placeholder="Filter Tanggal" data-sort="false"></th>
                            <th><input type="text" class="form-control" placeholder="Filter Nomor PBR" data-sort="false"></th>
                            <th><input type="text" class="form-control" placeholder="Filter Kode Pelanggan" data-sort="false"></th>
                            <th><input type="text" class="form-control" placeholder="Filter Grup Pelanggan" data-sort="false"></th>
                            <th><input type="text" class="form-control" placeholder="Filter Pelanggan" data-sort="false"></th>
                            <th><input type="text" class="form-control" placeholder="Filter Nama Barang" data-sort="false"></th>
                            <th><input type="text" class="form-control" placeholder="Filter COLY" data-sort="false"></th>
                            <th><input type="text" class="form-control" placeholder="Filter QTY" data-sort="false"></th>
                            <th><input type="text" class="form-control" placeholder="Filter Harga" data-sort="false"></th>
                            <th><input type="text" class="form-control" placeholder="Filter Diskon" data-sort="false"></th>
                            <th><input type="text" class="form-control" placeholder="Filter Nominal" data-sort="false"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $rowNumber = 0; // Initialize counter outside both loops
                        @endphp
                        @foreach ($pbrs as $invoice)
                        @foreach($invoice->details as $detail)
                        @php
                            $rowNumber++; // Increment for each detail row
                        @endphp
                        <tr class="clickable-row">
                            <td>{{$rowNumber}}</td>
                                    <td>{{ \Carbon\Carbon::parse($invoice->document_date)->format('d M Y') }}</td>
                                    <td>{{ $invoice->pbr_number }}</td>
                                    <td>{{ $invoice->customers->customer_code }}</td>
                                    <td>{{ $customers->firstWhere(fn($customer) => $invoice->customers->group_customer === $customer->customer_code)?->customer_name ?? '' }}</td>
                                    <td>{{ $invoice->customers->customer_name }}</td>
                                    <td>{{$detail->items->item_name}}</td>
                                    <td>{{number_format($detail->qty,0)}}</td>
                                    <td>{{number_format(($detail->qty*$detail->base_qty),0)}}</td>
                                    <td>{{number_format(($detail->price),0)}}</td>
                                    <td>{{number_format(($detail->disc_nominal+($detail->disc_percent*$detail->qty*$detail->base_qty*$detail->price)),0)}}</td>
                                    <td class="text-end">{{ number_format($detail->nominal,0) }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="total-amount-row">
                            <td colspan="11">Total untuk range tanggal terpilih:</td>
                            <td>{{ number_format($totalAmount,0) }}</td>
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
            var table = $('#example').DataTable({
                lengthChange: false,
                orderCellsTop: true,
                buttons: [ 'copy', 'excel', 'pdf', 'print']
            });
            table.buttons().container()
				.appendTo( '#example_wrapper .col-md-6:eq(0)' );

            // Prevent clicks on filter inputs from triggering sorting
            $('#example thead tr.filter-row input').on('click', function(e) {
                e.stopPropagation();
            });

            // Add filtering functionality to input fields
            $('#example thead tr.filter-row input').on('keyup change', function() {
                var columnIndex = $(this).parent().index();
                table.column(columnIndex).search(this.value).draw();
            });

            // Restore filter inputs if page is reloaded with search params
            $('#example thead tr.filter-row input').each(function() {
                var columnIndex = $(this).parent().index();
                $(this).val(table.column(columnIndex).search());
            });
		} );
    </script>
@endsection
