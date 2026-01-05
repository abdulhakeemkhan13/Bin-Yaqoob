<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Eobi;
use App\Models\SalaryTax;
use App\Models\AttendanceEmployee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class Employee extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'dob',
        'gender',
        'phone',
        'address',
        'email',
        'password',
        'employee_id',
        'branch_id',
        'department_id',
        'designation_id',
        'deployed_at',
        'deployed_date',
        'report_to',
        'company_doj',
        'documents',
        'account_holder_name',
        'account_number',
        'bank_name',
        'bank_identifier_code',
        'branch_location',
        'tax_payer_id',
        'salary_type',
        'biometric_emp_id',
        'account',
        'salary',
        'created_by',
        'owned_by',
        'termination_date',
    ];

    public function documents()
    {
        return $this->hasMany('App\Models\EmployeeDocument', 'employee_id', 'employee_id')->get();
    }

    public function salary_type()
    {
        return $this->hasOne('App\Models\PayslipType', 'id', 'salary_type')->pluck('name')->first();
    }

    public function allowances()
    {
        return $this->hasMany(Allowance::class);
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function saturationDeductions()
    {
        return $this->hasMany(SaturationDeduction::class);
    }

    public function otherPayments()
    {
        return $this->hasMany(OtherPayment::class);
    }

    public function overtimes()
    {
        return $this->hasMany(Overtime::class);
    }

    public function employeeIncrements()
    {
        return $this->hasMany(EmployeeIncrement::class);
    }

    public function proratedBasic(string $salaryMonth): float
    {
        $info = self::leave($this->id, $salaryMonth);
        if (!$info) {
            return (float) $this->salary;
        }

        $working  = (int)   ($info['working_days']   ?? 0);
        $present  = (float) ($info['present_days']   ?? 0);
        $approved = (float) ($info['leave_days']     ?? 0);
        $paid     = (float) ($info['paid']           ?? 0);

        if ($working <= 0) {
            return (float) $this->salary;
        }

        $payableDays = $present + $approved + $paid;
        $dailyRate   = (float) $this->salary / $working;

        return round($dailyRate * $payableDays, 2);
    }

    public function get_net_salary(string $salaryMonth = null): float
    {
        $basicBase = $salaryMonth ? $this->proratedBasic($salaryMonth) : (float) $this->salary;
        $pctBase   = $basicBase; // change to (float)$this->salary if you want % rows on full salary

        $total_allowance = $this->allowances->sum(
            fn($a) =>
            $a->type === 'fixed' ? (float)$a->amount : ((float)$a->amount * $pctBase / 100)
        );

        $total_commission = $this->commissions->sum(
            fn($c) =>
            $c->type === 'fixed' ? (float)$c->amount : ((float)$c->amount * $pctBase / 100)
        );

        $total_loan = $this->loans->sum(
            fn($l) =>
            $l->type === 'fixed' ? (float)$l->amount : ((float)$l->amount * $pctBase / 100)
        );

        $total_saturation_deduction = $this->saturationDeductions->sum(
            fn($d) =>
            $d->type === 'fixed' ? (float)$d->amount : ((float)$d->amount * $pctBase / 100)
        );

        $total_other_payment = $this->otherPayments->sum(
            fn($op) =>
            $op->type === 'fixed' ? (float)$op->amount : ((float)$op->amount * $pctBase / 100)
        );

        $total_over_time = $this->overtimes->sum(
            fn($ot) =>
            (float)$ot->number_of_days * (float)$ot->hours * (float)$ot->rate
        );

        $total_sal_tax = SalaryTax::where('employee_id', $this->id)->sum('amount');
        $total_eobi    = Eobi::where('employee_id', $this->id)->sum('amount');

        return $basicBase
            + $total_allowance
            + $total_commission
            - $total_loan
            - $total_saturation_deduction
            - (float)$total_sal_tax
            - (float)$total_eobi
            + $total_other_payment
            + $total_over_time;
    }

    public static function tax($id)
    {
        $taxs = SalaryTax::where('employee_id', '=', $id)->get();
        // keep behavior identical to portal: return JSON of the rows
        return json_encode($taxs);
    }

    public static function eobi($id)
    {
        $eobis = Eobi::where('employee_id', $id)->get();
        return json_encode($eobis);
    }


   
    public static function allowance($id)
    {

        //allowance
        $allowances      = Allowance::where('employee_id', '=', $id)->get();
        $total_allowance = 0;
        foreach ($allowances as $allowance) {
            $total_allowance = $allowance->amount + $total_allowance;
        }

        $allowance_json = json_encode($allowances);

        return $allowance_json;
    }

    public static function commission($id)
    {
        //commission
        $commissions      = Commission::where('employee_id', '=', $id)->get();
        $total_commission = 0;
        foreach ($commissions as $commission) {
            $total_commission = $commission->amount + $total_commission;
        }
        $commission_json = json_encode($commissions);

        return $commission_json;
    }

    public static function loan($id)
    {
        //Loan
        $loans      = Loan::where('employee_id', '=', $id)->get();
        $total_loan = 0;
        foreach ($loans as $loan) {
            $total_loan = $loan->amount + $total_loan;
        }
        $loan_json = json_encode($loans);

        return $loan_json;
    }

    public static function leave($id, $date)
    {
        // $date is "YYYY-MM"
        $startDate = date('Y-m-01', strtotime($date));
        $endDate   = date('Y-m-t',  strtotime($date));

        // 1) Month days
        $monthDays = Carbon::parse($startDate)->daysInMonth;

        // 2) Holidays in the month
        $holidays = Holiday::whereBetween('date', [$startDate, $endDate])->count();

        // 3) Sundays in the month
        $sundays = 0;
        $cursor  = Carbon::parse($startDate);
        $endC    = Carbon::parse($endDate);
        while ($cursor->lte($endC)) {
            if ($cursor->dayOfWeek === Carbon::SUNDAY) {
                $sundays++;
            }
            $cursor->addDay();
        }

        // 4) Working days (Mon–Sat), excluding Sundays + holidays
        $workingDays = $monthDays - ($holidays + $sundays);

        // 5) Presents (from employeeattendance)
        //   Expecting columns: employee_id, date, status ('Present' / 'Absent' / 'Leave' etc.)
        $presentDays = DB::table('attendance_employees')
            ->where('employee_id', $id)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', 'Present')
            ->count();

        // 6) Approved leave (your existing Leave model)
        $leaves = Leave::where('employee_id', $id)->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
                ->orWhereBetween('end_date',   [$startDate, $endDate])
                ->orWhere(function ($q2) use ($startDate, $endDate) {
                    $q2->where('start_date', '<=', $startDate)
                        ->where('end_date',   '>=', $endDate);
                });
        })->get();

        $totalLeaveDays = 0;
        foreach ($leaves as $lv) {
            $s = Carbon::parse($lv->start_date)->max($startDate);
            $e = Carbon::parse($lv->end_date)->min($endDate);
            $days = $e->diffInDays($s) + 1;
            if (($lv->duration ?? null) === 'half') {
                $days -= 0.5;
            }
            $totalLeaveDays += max(0, $days);
        }

        // 7) Late → if your employeeattendance table has a 'late' column (boolean/int),
        //    use it. Otherwise, we default to 0.
        $lateCount = 0;
        if (Schema::hasTable('employeeattendance') && Schema::hasColumn('employeeattendance', 'late')) {
            $lateCount = DB::table('employeeattendance')
                ->where('employee_id', $id)
                ->whereBetween('date', [$startDate, $endDate])
                ->where('status', 'Present')
                ->where('late', 1)
                ->count();
        }
        $lateAsLeave = $lateCount / 4; // display metric only (policy)

        // 8) Absent days for info: working - (present + approved leave)
        $absentDays = max(0, $workingDays - ($presentDays + $totalLeaveDays));

        // Your UI expects keys: total_leave, paid, unpaid, late.
        // Keep same keys and also expose detailed metrics for PDF/modal.
        $paid   = min($absentDays, 2);           // example policy: first 2 as paid
        $unpaid = max(0, $absentDays - $paid);

        return [
            'total_leave'  => $absentDays,       // legacy key (absences)
            'paid'         => $paid,
            'unpaid'       => $unpaid,
            'late'         => $lateAsLeave,

            // extra fields
            'working_days' => $workingDays,
            'month_days'   => $monthDays,
            'holidays'     => $holidays,
            'sundays'      => $sundays,
            'present_days' => $presentDays,
            'leave_days'   => $totalLeaveDays,
        ];
    }


    public static function saturation_deduction($id)
    {
        //Saturation Deduction
        $saturation_deductions      = SaturationDeduction::where('employee_id', '=', $id)->get();
        $total_saturation_deduction = 0;
        foreach ($saturation_deductions as $saturation_deduction) {
            $total_saturation_deduction = $saturation_deduction->amount + $total_saturation_deduction;
        }
        $saturation_deduction_json = json_encode($saturation_deductions);

        return $saturation_deduction_json;
    }

    public static function other_payment($id)
    {
        //OtherPayment
        $other_payments      = OtherPayment::where('employee_id', '=', $id)->get();
        $total_other_payment = 0;
        foreach ($other_payments as $other_payment) {
            $total_other_payment = $other_payment->amount + $total_other_payment;
        }
        $other_payment_json = json_encode($other_payments);

        return $other_payment_json;
    }

    public static function overtime($id)
    {
        //Overtime
        $over_times      = Overtime::where('employee_id', '=', $id)->get();
        $total_over_time = 0;
        foreach ($over_times as $over_time) {
            $total_work      = $over_time->number_of_days * $over_time->hours;
            $amount          = $total_work * $over_time->rate;
            $total_over_time = $amount + $total_over_time;
        }
        $over_time_json = json_encode($over_times);

        return $over_time_json;
    }

    public static function employee_id()
    {
        $employee = Employee::latest()->first();

        return !empty($employee) ? $employee->id + 1 : 1;
    }

    public function branch()
    {
        return $this->hasOne('App\Models\Branch', 'id', 'branch_id');
    }

    public function department()
    {
        return $this->hasOne('App\Models\Department', 'id', 'department_id');
    }

    public function designation()
    {
        return $this->hasOne('App\Models\Designation', 'id', 'designation_id');
    }

    public function salaryType()
    {
        return $this->hasOne('App\Models\PayslipType', 'id', 'salary_type');
    }

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function report()
    {
        return $this->hasOne('App\Models\Employee', 'id', 'report_to');
    }

    public function paySlip()
    {
        return $this->hasOne('App\Models\PaySlip', 'id', 'employee_id');
    }

    public function bankAccount()
    {
        return $this->hasOne('App\Models\BankAccount', 'id', 'account');
    }


    public function present_status($employee_id, $data)
    {
        return AttendanceEmployee::where('employee_id', $employee_id)->where('date', $data)->first();
    }


    public static function employee_salary($salary)
    {
        $employee = Employee::where("salary", $salary)->first();
        if ($employee->salary == '0' || $employee->salary == '0.0') {
            return "-";
        } else {
            return $employee->salary;
        }
    }
}
