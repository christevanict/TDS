@extends('layouts.master')

@section('title', 'Periode Closing & Restore')

@section('content')
    <div class="row">
        <x-page-title title="Periode Closing & Restore" pagetitle="Periode Closing & Restore" />

        <div class="card mb-3">
            <div class="card-header">
                <div class="card-body center">
                    <h1>Periode</h1>
                    <form action="{{route('transaction.closing.restore')}}" method="post">
                        @csrf
                        <button type="submit" class=" btn btn-warning" @if(!in_array('create', $privileges)) disabled @endif>Restore Previous Periode</button>
                    </form>
                    <div class="table-responsive ">
                        <table id="example" class="table table-hover table-bordered mt-3" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Periode Code</th>
                                    <th>Periode Start</th>
                                    <th>Periode End</th>
                                    <th>Periode Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($periodes as $periode) 
                                <tr>
                                        <td>{{$periode->periode_code}}</td>
                                        <td>{{\Carbon\Carbon::parse($periode->periode_start)->format('d F y')}}</td>
                                        <td>{{\Carbon\Carbon::parse($periode->periode_end)->format('d F y')}}</td>
                                        <td>{{ucfirst($periode->periode_active)}}</td>
                                        <td>
                                            <form id="close-periode-form" action="{{route('transaction.closing.closing')}}" method="post">
                                                @csrf
                                                <input type="hidden" name="start_date" value="{{\Carbon\Carbon::parse($periode->periode_start)->format('Y-m-d')}}">
                                                <input type="hidden" name="end_date" value="{{\Carbon\Carbon::parse($periode->periode_end)->format('Y-m-d')}}">
                                                <button type="button" onclick="confirmClose()" class=" btn btn-danger" style="width: auto; padding:0;">Close Periode</button>
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
    <script>
        function confirmClose() {
            Swal.fire({
                title: 'Are you sure?',
                text: "Are you sure to close this periode?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#0c6efd',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, close it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('close-periode-form').submit();
                }
            })
        }
    </script>
@endsection
