@extends('layouts.master')

@section('title', 'POS List')

@section('css')
    <style>
        .clickable-row {
            cursor: pointer;
        }

        .clickable-row:hover,
        .clickable-row:focus {
            background-color: #f1f1f1;
        }

        .btn-view {
            margin-right: 10px;
        }
    </style>
@endsection

@section('content')
    <x-page-title title="POS List" pagetitle="POS {{__('Information')}}" />
    <hr>

    <div class="card">
        <div class="card-body">
            <h6 class="mb-2 text-uppercase">POS List</h6>
            <div class="table-responsive">
                <table id="example" class="table table-hover table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>POS Number</th>
                            <th>Transaction Date</th>
                            <th>Total Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($posList as $pos)
                            <tr class="clickable-row">
                                <td>{{ $pos->pos_number }}</td>
                                <td>{{ \Carbon\Carbon::parse($pos->transaction_date)->format('d M Y') }}</td>
                                <td class="currency-cell">{{ $pos->total_amount }}</td>
                                <td>
                                    <a href="{{ route('pos.receipt', $pos->id) }}" class="btn btn-info btn-view"><i
                                            class="material-icons-outlined">visibility</i></a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>POS Number</th>
                            <th>Transaction Date</th>
                            <th>Total Amount</th>
                            <th>Action</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ URL::asset('build/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
    <script>
        // Fungsi format mata uang Indonesia
        function formatCurrencyIndonesia(value) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(value);
        }

        // Inisialisasi DataTable
        $(document).ready(function() {
            const table = $('#example').DataTable();

            // Format kolom mata uang
            table.rows().every(function() {
                const data = this.data();
                const totalAmountCell = $(this.node()).find('.currency-cell');
                const totalAmount = parseFloat(totalAmountCell.text());
                totalAmountCell.text(formatCurrencyIndonesia(totalAmount));
            });
        });
    </script>
@endsection
