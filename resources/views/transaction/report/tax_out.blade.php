@extends('layouts.master')

@section('title', 'Laporan PPN Keluaran')

@section('css')
<style>
    .clickable-row {
        cursor: pointer;
    }

    .clickable-row:hover, .clickable-row:focus {
        background-color: #f1f1f1;
    }

    .btn-insert {
        margin-bottom: 20px;
    }

    .btn-print, .btn-edit {
        margin-right: 10px; /* Add space between buttons */
    }
    .totals {
        margin-top: 10px;
        font-weight: bold;
    }
</style>
@endsection

@section('content')
<x-page-title title="Pajak" pagetitle="Laporan PPN Keluaran" />
<hr>

<div class="card">
    <div class="card-body">
        <h6 class="mb-2 text-uppercase">Laporan PPN Keluaran</h6>
        <div class="row mb-3 ">
            <div class="col-md-3 ">
                <label for="date_from">Dari:</label>
                <div class="input-group">
                    <input type="date" id="date_from" class="form-control date-picker filter-date me-0">
                    <button type="button" class="btn btn-danger clear-date" data-target="#date_from"><i class="material-icons-outlined">close</i></button>
                </div>
            </div>
            <div class="col-md-3 ">
                <label for="date_to">Sampai:</label>
                <div class="input-group">
                    <input type="date" id="date_to" class="form-control date-picker filter-date me-0">
                    <button type="button" class="btn btn-danger clear-date" data-target="#date_to"><i class="material-icons-outlined">close</i></button>
                </div>
            </div>
            <div class="col-md-3 pt-4">
                <button type="button" class="btn btn-primary" id="btn-search">Cari</button>
            </div>
        </div>
        <!-- Add Totals Display Here -->

        <div class="table-responsive ">
            <table id="example" class="table table-hover table-bordered mt-3" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>No Faktur</th>
                        <th>Tanggal</th>
                        <th>Kode Pelanggan</th>
                        <th>Pelanggan</th>
                        <th>DPP</th>
                        <th>PPn</th>
                    </tr>
                    <tr>
                        <th colspan="5" class="text-end">Total</th>
                        <th><span class="total_dpp">0</span></th>
                        <th><span class="total_ppn">0</span></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="5" class="text-end">Total</th>
                        <th><span class="total_dpp">0</span></th>
                        <th><span class="total_ppn">0</span></th>
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
        x = {
            headers: {
                "X-CSRF-TOKEN": "{{csrf_token()}}"
            }
        }
        $.ajaxSetup(x);

        var table = $('#example').DataTable({
            lengthChange: false,
            buttons: ['copy', 'excel', 'pdf', 'print'],
            columns: [
                { data: null, render: function(data, type, row, meta) {
                    return meta.row + 1;
                }},
                { data: 'invoice_number' },
                { data: 'date' },
                { data: 'customer_code' },
                { data: 'customer_name' },
                { data: 'dpp', className: 'text-end' },
                { data: 'ppn', className: 'text-end' },
            ],
            paging: false
        });

        table.buttons().container()
            .appendTo('#example_wrapper .col-md-6:eq(0)');

        // Function to calculate and update totals
        function updateTotals(data) {
            let totalDpp = 0;
            let totalPpn = 0;

            data.forEach(row => {
                totalDpp += parseFloat(row.dpp.replace(/,/g, '')) || 0; // Remove commas before parsing
                totalPpn += parseFloat(row.ppn.replace(/,/g, '')) || 0;  // Remove commas before parsing
            });

            $('.total_dpp').text(totalDpp.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            $('.total_ppn').text(totalPpn.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        }

        const todayDate = new Date().toISOString().split('T')[0];
        const firstDay = new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0];
        $.ajax({
            url: '{{ route("report.ppn_out.search") }}',
            method: 'POST',
            data: {
                date_from: firstDay,
                date_to: todayDate,
            },
            success: function(response) {
                table.clear().rows.add(response).draw();
                updateTotals(response); // Update totals after loading data
            },
            error: function(xhr) {
                console.log(xhr);
                alert('An error occurred while fetching data.');
            }
        });

        $('#btn-search').on('click', function() {
            var dateFrom = $('#date_from').val();
            var dateTo = $('#date_to').val();
            if (!dateFrom) {
                Swal.fire({
                    title: 'Error!',
                    text: "Date From must be filled",
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return;
            }

            $.ajax({
                url: '{{ route("report.ppn_out.search") }}',
                method: 'POST',
                data: {
                    date_from: dateFrom,
                    date_to: dateTo,
                },
                success: function(response) {
                    table.clear().rows.add(response).draw();
                    updateTotals(response); // Update totals after search
                },
                error: function(xhr) {
                    alert('An error occurred while fetching data.');
                }
            });
        });
    });
</script>
@endsection
