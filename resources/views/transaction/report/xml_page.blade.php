@extends('layouts.master')

@section('title', 'Export XML PPN Keluaran')

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
</style>
@endsection

@section('content')
<x-page-title title="Pajak" pagetitle="Export XML PPN Keluaran" />
<hr>

<div class="card">
    <div class="card-body">
        <h6 class="mb-2 text-uppercase">Export XML PPN Keluaran</h6>
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
            <div class="col-md-3 pt-4 ">
                <button type="button" class="btn btn-primary" id="btn-search">Cari</button>
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
        x = {
            headers: {
                "X-CSRF-TOKEN": "{{csrf_token()}}"
            }
        }
        $.ajaxSetup(x);
        $('#btn-search').on('click', function() {
    var dateFrom = $('#date_from').val();
    var dateTo = $('#date_to').val();

    if (!dateFrom) {
        Swal.fire({ title: 'Error!', text: "Date From must be filled", icon: 'error', confirmButtonText: 'OK' });
        return;
    }
    if (!dateTo) {
        Swal.fire({ title: 'Error!', text: "Date To must be filled", icon: 'error', confirmButtonText: 'OK' });
        return;
    }

    let filename = 'export_pajak_keluaran_' + dateFrom + '-' + dateTo + '.xml';
    let url = '{{ route("report.ppn_out.xml_file") }}?' + $.param({ date_from: dateFrom, date_to: dateTo });

    fetch(url, {
        method: 'GET'
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => { throw new Error(`Server error: ${response.status} - ${text}`); });
        }
        return response.blob().then(blob => ({
            blob: blob,
            filename: response.headers.get('content-disposition')?.match(/filename="(.+)"/)?.[1] || filename
        }));
    })
    .then(({ blob, filename }) => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    })
    .catch(error => {
        console.error('Fetch error:', error);
        Swal.fire({ title: 'Error!', text: error.message, icon: 'error', confirmButtonText: 'OK' });
    });
});
    });
</script>
@endsection
