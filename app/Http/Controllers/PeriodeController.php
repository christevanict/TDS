<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Journal;
use App\Models\Coa;
use App\Models\CoaType;
use App\Models\Periode;
use App\Models\BeginningBalance;
use App\Models\Company;
use App\Models\Department;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PeriodeController extends Controller
{

    public function showClosing(){

        $periodes = Periode::where('periode_active', 'active')->orderBy('id','asc')->get();
        $privileges = Auth::user()->roles->privileges['closing'];
        return view('transaction.closing.closing_show',compact('periodes','privileges'));
    }

    public function closing(Request $request)
    {

        // Define the date range for the query
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        if(Carbon::now()<$endDate){
            return redirect()->back()->with('error','Cannot close this periode');
        }
        $periodeCode = substr($endDate,0,4). substr($endDate,5,2);
        $nextPeriode = $this->getNextPeriodDetails($periodeCode);
        $company = Company::first();
        $department = Department::first();
        DB::beginTransaction();
        try {
            // Fetch the trial balance using Eloquent
            $trialBalance = Coa::with(['journals' => function($query) {
                $query->whereNotNull('account_number');
            }, 'coasss'])
            ->get()
            ->map(function ($coa) use ($startDate, $endDate) {
                // Calculate trial balance values
                $debitBeforeAdjustment = $coa->journals()
                    ->where('created_at', '<', $startDate)
                    ->sum('debet_nominal');

                $creditBeforeAdjustment = $coa->journals()
                    ->where('created_at', '<', $startDate)
                    ->sum('credit_nominal');

                // Calculate Trial Balance before Adjustment (Debit)
                $trialBalanceBeforeAdjustmentDebit = 0;

                if ($coa->account_type === null) {
                    if ($debitBeforeAdjustment > $creditBeforeAdjustment) {
                        $trialBalanceBeforeAdjustmentDebit = $debitBeforeAdjustment - $creditBeforeAdjustment;
                    }
                } else {
                    if ($debitBeforeAdjustment > $creditBeforeAdjustment) {
                        $trialBalanceBeforeAdjustmentDebit = $debitBeforeAdjustment - $creditBeforeAdjustment;
                    }
                }

                // Replace negative values with 0
                $trialBalanceBeforeAdjustmentDebit = max(0, $trialBalanceBeforeAdjustmentDebit);

                // Calculate adjustments
                $debitAdjustment = $coa->journals()
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->sum('debet_nominal') - $coa->journals()
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->sum('credit_nominal');

                $creditAdjustment = $coa->journals()
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->sum('credit_nominal') - $coa->journals()
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->sum('debet_nominal');

                // Replace negative values with 0 for adjustments
                $debitAdjustment = max(0, $debitAdjustment);
                $creditAdjustment = max(0, $creditAdjustment);

                return [
                    'account_number' => $coa->account_number,
                    'account_type' => $coa->coasss->account_type ?? null,
                    'begin_debet_nominal' => $trialBalanceBeforeAdjustmentDebit,
                    'begin_credit_nominal' => max(0, $creditBeforeAdjustment - $debitBeforeAdjustment), // Adjust this logic as needed
                    'adjust_debit_nominal' => $debitAdjustment,
                    'adjust_credit_nominal' => $creditAdjustment,
                    'ending_debet_balance' => max(0, $trialBalanceBeforeAdjustmentDebit + $debitAdjustment),
                    'ending_credit_balance' => max(0, (max(0, $creditBeforeAdjustment - $debitBeforeAdjustment)) + $creditAdjustment), // Adjust this logic as needed
                ];
            });
            // dd($trialBalance);
            foreach ($trialBalance as $key => $value) {
                // dd($value);
                BeginningBalance::create([
                    'account_number'=>$value['account_number'],
                    'begin_debet_nominal'=>$value['begin_debet_nominal'],
                    'begin_credit_nominal'=>$value['begin_credit_nominal'],
                    'adjust_debit_nominal'=>$value['adjust_debit_nominal'],
                    'adjust_credit_nominal'=>$value['adjust_credit_nominal'],
                    'ending_debet_balance'=>$value['ending_debet_balance'],
                    'ending_credit_balance'=>$value['ending_credit_balance'],
                    'periode'=>$periodeCode,
                    'created_by'=>Auth::user()->username,
                    'updated_by'=>Auth::user()->username,
                ]);

                //Jurnal pembalik hanya untuk Sales, COGS, Expenses, Other Expenses, Other Revenue.
                if($value['account_type']=='Sales'||$value['account_type']=='COGS'||$value['account_type']=='Expenses'||$value['account_type']=='Other Revenue'||$value['account_type']=='Other Expenses'){
                    Journal::create([
                        'document_number'=>'JP/'.$periodeCode,
                        'account_number'=>$value['account_number'],
                        'document_date'=>date('Y-m-d'),
                        'notes'=>'Reversing to journals for the period of '.$this->convertPeriodCodeToMonthYear($periodeCode),
                        'debet_nominal'=>$value['adjust_credit_nominal'],
                        'credit_nominal'=>$value['adjust_debit_nominal'],
                        'company_code'=>$company->company_code,
                        'department_code'=>$department->department_code,
                        'created_by'=>Auth::user()->username,
                        'updated_by'=>Auth::user()->username,
                    ]);
                }
            }
            Periode::where('periode_code',$periodeCode)->update([
                'periode_active'=>'closed',
                'closed_at'=>Carbon::now(),
                'closed_by'=>Auth::user()->username,
            ]);
            // dd($nextPeriode);

            if(Periode::where('periode_code',$nextPeriode['next_periode_code'])->count()<1){
                Periode::create([
                    'periode_code'=>$nextPeriode['next_periode_code'],
                    'periode_start'=>$nextPeriode['start_date'],
                    'periode_end'=>$nextPeriode['end_date'],
                    'periode_active'=>'active',
                ]);
            }
            DB::commit();
            return redirect()->back()->with('success','Successfully Closing Periode');
        }catch (\Throwable $e) {
            DB::rollBack();  // Roll back the transaction on error
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }
        // Return the results to a view or as a JSON response
    }

    public function restore()
    {
        DB::beginTransaction();  // Begin the transaction
        try {
            $oldActivePeriode = Periode::where('periode_active','active')->orderBy('id','asc')->first();
            $previousePreiodeCode = $this->getPreviousPeriodCode($oldActivePeriode->periode_code);
            Periode::where('periode_code',$previousePreiodeCode)->update(['periode_active'=>'active']);
            Journal::where('document_number','JP/'.$previousePreiodeCode)->delete();
            BeginningBalance::where('periode',$previousePreiodeCode)->delete();
            DB::commit();
            return redirect()->back()->with('success','Successfully Restoring Periode '.$this->convertPeriodCodeToMonthYear($previousePreiodeCode));
        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }
    }

    private function getNextPeriodDetails($currentPeriodCode) {
        // Extract year and month from the period code
        $year = substr($currentPeriodCode, 0, 4);
        $month = substr($currentPeriodCode, 4, 2);

        // Convert month to an integer
        $month = (int)$month;

        // Increment the month
        $month++;

        // If the month exceeds 12, reset to 1 and increment the year
        if ($month > 12) {
            $month = 1;
            $year++;
        }

        // Format the new period code
        $nextPeriodCode = sprintf('%04d%02d', $year, $month);

        // Get the start date of the next month
        $startDate = \Carbon\Carbon::createFromFormat('Y-m', $year . '-' . $month)->startOfMonth()->format('Y-m-d');

        // Get the end date of the next month
        $endDate = \Carbon\Carbon::createFromFormat('Y-m', $year . '-' . $month)->endOfMonth()->format('Y-m-d');

        return [
            'next_periode_code' => $nextPeriodCode,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    function convertPeriodCodeToMonthYear($periodCode) {
        // Extract year and month from the period code
        $year = substr($periodCode, 0, 4);
        $month = substr($periodCode, 4, 2);

        // Create a Carbon instance for the first day of the month
        $date = Carbon::createFromFormat('Y-m', $year . '-' . $month);

        // Format the date to "F Y" (e.g., "November 2024")
        return $date->format('F Y');
    }

    function getPreviousPeriodCode($currentPeriodCode) {
        // Extract year and month from the period code
        $year = substr($currentPeriodCode, 0, 4);
        $month = substr($currentPeriodCode, 4, 2);

        // Convert month to an integer
        $month = (int)$month;

        // Decrement the month
        $month--;

        // If the month is less than 1, reset to December and decrement the year
        if ($month < 1) {
            $month = 12;
            $year--;
        }

        // Format the previous period code
        $previousPeriodCode = sprintf('%04d%02d', $year, $month);

        return $previousPeriodCode;
    }
}
