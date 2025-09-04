@extends('layouts.master')

@section('title', 'Neraca Saldo Information')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        .btn-square {
            width: 100px;
        }
        .select2-container .select2-selection--single {
            height: calc(2.25rem + 2px);
            padding: 0.375rem 0.75rem;
        }
        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 0.375rem;
        }
    </style>
@endsection

@section('content')
    <x-page-title title="Neraca Saldo" pagetitle="Neraca Saldo" />
    <hr>

    <div class="card">
        <div class="card-body">
            <h6 class="mb-2 text-uppercase">Neraca Saldo {{__('Information')}}</h6>
            <form id="trialBalanceForm" action="{{ route('transaction.journal.fetchTrialBalance') }}" method="GET">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="date_to">Date To:</label>
                        <select id="date" name="date" class="form-select filter-date" required>
                            @php
                                $currentMonth = now()->startOfMonth();
                                $startMonth = \Carbon\Carbon::create(2024, 10, 1);
                                $selectedDate = request()->query('date') ? \Carbon\Carbon::parse(request()->query('date'))->format('Y-m-d') : null;

                                while ($startMonth <= $currentMonth) {
                                    $value = $startMonth->format('Y-m-d');
                                    $display = $startMonth->format('F Y');
                                    $selected = $selectedDate === $value ? 'selected' : '';
                                    echo "<option value=\"$value\" $selected>$display</option>";
                                    $startMonth->addMonth();
                                }
                            @endphp
                        </select>
                    </div>

                    <!-- Search Button -->
                    <div class="col-md-2 pt-4">
                        <button type="submit" class="btn btn-primary" id="btn-search">Search</button>
                    </div>
                </div>
            </form>

            <!-- DataTable -->
            <div class="table-responsive">
                <table id="trialBalanceTable" class="table table-hover mt-3" style="width:100%">
                    <thead>
                        <tr>
                            <th style="display: none">No</th>
                            <th>Account Code</th>
                            <th>Account</th>
                            <th>Sawal Debet</th>
                            <th>Sawal Credit</th>
                            <th>Mutasi Debet</th>
                            <th>Mutasi Credit</th>
                            <th>Penyesuaian Debet</th>
                            <th>Penyesuaian Credit</th>
                            <th>Saldo Akhir Debet</th>
                            <th>Saldo Akhir Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($trialBalanceData))
                            @foreach($trialBalanceData as $group)
                                <tr>
                                    <td style="display: none"></td>
                                    <td></td>
                                    <td><div class="account-type-container"><strong class="account-type-header">{{ $group['accountType'] }}</strong></div></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                @foreach($group['accounts'] as $account)
                                    <tr>
                                        <td style="display: none"></td>
                                        <td>{{ $account['account_code'] }}</td>
                                        <td>{{ $account['account'] }}</td>
                                        <td class="text-end">{{ $account['sawal_debit'] ? number_format((float)$account['sawal_debit'], 2, '.', ',') : '0.00' }}</td>
                                        <td class="text-end">{{ $account['sawal_credit'] ? number_format((float)$account['sawal_credit'], 2, '.', ',') : '0.00' }}</td>
                                        <td class="text-end">{{ $account['transaction_debit'] ? number_format((float)$account['transaction_debit'], 2, '.', ',') : '0.00' }}</td>
                                        <td class="text-end">{{ $account['transaction_credit'] ? number_format((float)$account['transaction_credit'], 2, '.', ',') : '0.00' }}</td>
                                        <td class="text-end">{{ $account['adjustment_debit'] ? number_format((float)$account['adjustment_debit'], 2, '.', ',') : '0.00' }}</td>
                                        <td class="text-end">{{ $account['adjustment_credit'] ? number_format((float)$account['adjustment_credit'], 2, '.', ',') : '0.00' }}</td>
                                        <td class="text-end">{{ $account['balance_debit'] ? number_format((float)$account['balance_debit'], 2, '.', ',') : '0.00' }}</td>
                                        <td class="text-end">{{ $account['balance_credit'] ? number_format((float)$account['balance_credit'], 2, '.', ',') : '0.00' }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        @endif
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" style="text-align:right">Total:</th>
                            <th class="text-end" id="totalSawalDebit">0.00</th>
                            <th class="text-end" id="totalSawalCredit">0.00</th>
                            <th class="text-end" id="totalTransactionDebit">0.00</th>
                            <th class="text-end" id="totalTransactionCredit">0.00</th>
                            <th class="text-end" id="totalAdjustmentDebit">0.00</th>
                            <th class="text-end" id="totalAdjustmentCredit">0.00</th>
                            <th class="text-end" id="totalBalanceDebit">0.00</th>
                            <th class="text-end" id="totalBalanceCredit">0.00</th>
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2 for date filters
            // $('.filter-date').select2({
            //     theme: 'bootstrap-5',
            //     placeholder: 'Select Month',
            //     allowClear: true,
            //     width: '100%'
            // });

            var table = $('#trialBalanceTable').DataTable({
                dom: 'Bfrtip',
                @if(Auth::user()->role=='RO01')
                buttons: ['copy', 'print',{
                    extend: 'excel',
                    filename: function() {
                        var accountName = $('#date').find('option:selected').text().replace(' ','');
                        return 'MajuBersama_NeracaSaldo_'+accountName;
                    }
                },
                {
                    extend: 'pdf',
                    filename: function() {
                        var accountName = $('#date').find('option:selected').text().replace(' ','');
                        return 'MajuBersama_NeracaSaldo_'+accountName;
                    }
                }],
                @endif
                lengthChange: true,
                sorting: false,
                columns: [
                    { data: 'number', visible: false },
                    { data: 'account' },
                    { data: 'account_code' },
                    { data: 'sawal_debit' },
                    { data: 'sawal_credit' },
                    { data: 'transaction_debit' },
                    { data: 'transaction_credit' },
                    { data: 'adjustment_debit' },
                    { data: 'adjustment_credit' },
                    { data: 'balance_debit' },
                    { data: 'balance_credit' }
                ],
                footerCallback: function(row, data, start, end, display) {
                    var api = this.api();
                    var totalSawalDebit = 0;
                    var totalSawalCredit = 0;
                    var totalTransactionDebit = 0;
                    var totalTransactionCredit = 0;
                    var totalAdjustmentDebit = 0;
                    var totalAdjustmentCredit = 0;
                    var totalBalanceDebit = 0;
                    var totalBalanceCredit = 0;

                    api.rows().every(function() {
                        var row = this.data();
                        totalSawalDebit += parseFloat(row.sawal_debit.replaceAll(',','')) || 0;
                        totalSawalCredit += parseFloat(row.sawal_credit.replaceAll(',','')) || 0;
                        totalTransactionDebit += parseFloat(row.transaction_debit.replaceAll(',','')) || 0;
                        totalTransactionCredit += parseFloat(row.transaction_credit.replaceAll(',','')) || 0;
                        totalAdjustmentDebit += parseFloat(row.adjustment_debit.replaceAll(',','')) || 0;
                        totalAdjustmentCredit += parseFloat(row.adjustment_credit.replaceAll(',','')) || 0;
                        totalBalanceDebit += parseFloat(row.balance_debit.replaceAll(',','')) || 0;
                        totalBalanceCredit += parseFloat(row.balance_credit.replaceAll(',','')) || 0;
                    });


                    $(api.column(3).footer()).html(totalSawalDebit.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
                    $(api.column(4).footer()).html(totalSawalCredit.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
                    $(api.column(5).footer()).html(totalTransactionDebit.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
                    $(api.column(6).footer()).html(totalTransactionCredit.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
                    $(api.column(7).footer()).html(totalAdjustmentDebit.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
                    $(api.column(8).footer()).html(totalAdjustmentCredit.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
                    $(api.column(9).footer()).html(totalBalanceDebit.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
                    $(api.column(10).footer()).html(totalBalanceCredit.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
                }
            });

            table.buttons().container()
                .appendTo('#trialBalanceTable_wrapper .col-md-6:eq(0)');

            $('#trialBalanceForm').on('submit', function(e) {
                var date = $('#date').val();

                if (!date) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Error!',
                        text: 'Please provide Date.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });

        function formatDateToDDMMMYYYY(dateString) {
            if (!dateString) return 'all'; // Fallback for empty dates
            var date = new Date(dateString);
            var day = String(date.getDate()).padStart(2, '0');
            var month = date.toLocaleString('en-US', { month: 'short' });
            var year = date.getFullYear();
            return `${day}${month}${year}`;
        }
    </script>
@endsection
