@extends('layouts.master')

@section('title', 'Master Zone')
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
<x-page-title title="Master" pagetitle="Zone" />
		<hr>
		<div class="card">
			<div class="card-body">
                <h6 class="mb-2 text-uppercase">Zone</h6>
                <a href="{{ route('zone.input') }}" class="btn btn-primary mb-3"
                @if(!in_array('create', $privileges)) disabled @endif
                >
                    Tambah Baru
                </a>
				<div class="table-responsive">
					<table id="example" class="table table-hover table-bordered" style="width:100%">
						<thead>
							<tr>
								<th>No</th>
                                <th>Code</th>
								<th>Name</th>
                                <th>Action</th>
							</tr>
						</thead>
						<tbody>
                            @foreach ($zones as $zone)
                                <tr class='clickable-row'>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{$zone->zone_code}}</td>
                                    <td>{{$zone->zone_name}}</td>
                                    <td>
                                    <a href="{{ route('zone.edit', ['id' => $zone->id]) }}" class="btn btn-sm btn-warning @if(!in_array('update', $privileges)) disabled  @endif">
                                        <i class="material-icons-outlined">edit</i>
                                    </a>
                                        <form id="inactive-form-{{ $zone->zone_code }}" action="{{ url('/TDS/master/zone/inactive/' . $zone->zone_code) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('POST')
                                            <button type="button" class="btn btn-sm btn-info"
                                            onclick="confirmDelete('{{ $zone->zone_code }}','{{$zone->is_active==1?'inactive':'active'}}'
                                            )"><i class="material-icons-outlined">
                                                @if ($zone->is_active*1==1)
                                                    toggle_off
                                                @else
                                                    toggle_on
                                                @endif
                                            </i>
                                            </button>
                                        </form>
                                        <form id="delete-form-{{ $zone->zone_code }}" action="{{ url('/TDS/master/zone/delete/' . $zone->zone_code) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('POST')
                                            <button type="button" class="btn btn-sm btn-danger"
                                            onclick="confirmDelete('{{ $zone->zone_code }}','delete')"><i class="material-icons-outlined">delete</i></button>
                                        </form>

                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
						<tfoot>
							<tr>
								<th>No</th>
								<th>Code</th>
								<th>Name</th>
								<th>Action</th>
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
			var table = $('#example').DataTable( {
				lengthChange: false,
				buttons: [ 'copy', 'excel', 'pdf', 'print']
			} );

			table.buttons().container()
				.appendTo( '#example_wrapper .col-md-6:eq(0)' );
		} );
	</script>
    <script>
        function confirmDelete(id,type) {
            Swal.fire({
                title: type=='inactive'?'Are you sure want deactivate this Zone':(type=='active')?'Are you sure want to activate this Zone':'Are you sure want delete this Zone',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#0c6efd',
                cancelButtonColor: '#d33',
                confirmButtonText: type == 'inactive' ? 'Yes, deactivate it' : (type =='active' ? 'Yes, activate it' : 'Yes, delete it!')
            }).then((result) => {
                if (result.isConfirmed) {
                    if(type=='inactive'||type=='active'){
                        document.getElementById('inactive-form-' + id).submit();
                    }else{
                        document.getElementById('delete-form-' + id).submit();
                    }
                }
            })
        }
        let privileges = @json($privileges);
    </script>
@endsection
