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
            <form method="GET" action="{{ route('transaction.pbr.summary') }}">
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
                            <th>Nomor {{__('Sales PBR')}}</th>
                            <th>{{__('Document Date')}}</th>
                            <th>{{__('Customer Code')}}</th>
                            <th>{{__('Group Customer')}}</th>
                            <th>{{__('Customer')}}</th>
                            <th>Status Kirim</th>
                            <th>Total</th>
                        </tr>
                        <tr class="filter-row">
                            <th></th>
                            <th><input type="text" class="form-control" placeholder="Filter Nomor PBR" data-sort="false"></th>
                            <th><input type="text" class="form-control" placeholder="Filter Tanggal" data-sort="false"></th>
                            <th><input type="text" class="form-control" placeholder="Filter Kode Pelanggan" data-sort="false"></th>
                            <th><input type="text" class="form-control" placeholder="Filter Grup Pelanggan" data-sort="false"></th>
                            <th><input type="text" class="form-control" placeholder="Filter Pelanggan" data-sort="false"></th>
                            <th><input type="text" class="form-control" placeholder="Filter Status" data-sort="false"></th>
                            <th><input type="text" class="form-control" placeholder="Filter Total" data-sort="false"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pbrs as $pbr)
                            <tr class="clickable-row">
                                <td>{{$loop->iteration}}</td>
                                <td>{{ $pbr->pbr_number }}</td>
                                <td>{{ \Carbon\Carbon::parse($pbr->document_date)->format('d M Y') }}</td>
                                <td>{{ $pbr->customer_code }}</td>
                                <td>{{ $customers->firstWhere(fn($customer) => $pbr->customers->group_customer === $customer->customer_code)?->customer_name ?? '' }}</td>
                                <td>{{ $pbr->customers->customer_name }}</td>
                                <td>{{ $pbr->delivery_date?'Terkirim':'Belum' }}</td>
                                <td class="text-end">{{ number_format($pbr->total,0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="total-amount-row">
                            <td colspan="7">Total untuk range tanggal terpilih:</td>
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
