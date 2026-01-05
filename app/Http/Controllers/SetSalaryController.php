<?php

namespace App\Http\Controllers;

use App\Models\Allowance;
use App\Models\AllowanceOption;
use App\Models\Commission;
use App\Models\DeductionOption;
use App\Models\SalaryTax;
use App\Models\Eobi;
use App\Models\Employee;
use App\Models\Loan;
use App\Models\LoanOption;
use App\Models\OtherPayment;
use App\Models\BankAccount;
use App\Models\Overtime;
use App\Models\PayslipType;
use App\Models\SaturationDeduction;
use App\Models\Utility;
use Illuminate\Http\Request;

class SetSalaryController extends Controller
{
    public function index()
    {
        if(\Auth::user()->can('manage set salary'))
        {
            if (\Auth::user()->type == 'company') {
                $employees = Employee::where('created_by' , \Auth::user()->creatorId())->with('salaryType')->get();
            } else {
                $employees = Employee::where('owned_by' , \Auth::user()->ownedId())->with('salaryType')->get();
            }

            return view('setsalary.index', compact('employees'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    // 

    public function edit($id)
    {
        if(\Auth::user()->can('edit set salary'))
        {
            $payslip_type      = PayslipType::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $allowance_options = AllowanceOption::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $loan_options      = LoanOption::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $deduction_options = DeductionOption::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            if(\Auth::user()->type == 'Employee')
            {
                $currentEmployee      = Employee::where('user_id', '=', \Auth::user()->id)->first();
                $allowances           = Allowance::where('employee_id', $currentEmployee->id)->get();
                $commissions          = Commission::where('employee_id', $currentEmployee->id)->get();
                $loans                = Loan::where('employee_id', $currentEmployee->id)->get();
                $saturationdeductions = SaturationDeduction::where('employee_id', $currentEmployee->id)->get();
                $otherpayments        = OtherPayment::where('employee_id', $currentEmployee->id)->get();
                $overtimes            = Overtime::where('employee_id', $currentEmployee->id)->get();
                $employee             = Employee::where('user_id', '=', \Auth::user()->id)->first();
                return view('setsalary.employee_salary', compact('employee', 'payslip_type', 'allowance_options', 'commissions', 'loan_options', 'overtimes', 'otherpayments', 'saturationdeductions', 'loans', 'deduction_options', 'allowances'));
            }
            else
            {
                $allowances           = Allowance::where('employee_id', $id)->get();
                $commissions          = Commission::where('employee_id', $id)->get();
                $loans                = Loan::where('employee_id', $id)->get();
                $saturationdeductions = SaturationDeduction::where('employee_id', $id)->get();
                $otherpayments        = OtherPayment::where('employee_id', $id)->get();
                $overtimes            = Overtime::where('employee_id', $id)->get();
                $employee             = Employee::find($id);
                return view('setsalary.edit', compact('employee', 'payslip_type', 'allowance_options', 'commissions', 'loan_options', 'overtimes', 'otherpayments', 'saturationdeductions', 'loans', 'deduction_options', 'allowances'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

public function show($id)
{
    if (\Auth::user()->type == 'company') {
        $payslip_type      = PayslipType::where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
        $allowance_options = AllowanceOption::where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
        $loan_options      = LoanOption::where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
        $deduction_options = DeductionOption::where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
    } else {
        $payslip_type      = PayslipType::where('owned_by', \Auth::user()->ownedId())->pluck('name', 'id');
        $allowance_options = AllowanceOption::where('owned_by', \Auth::user()->ownedId())->pluck('name', 'id');
        $loan_options      = LoanOption::where('owned_by', \Auth::user()->ownedId())->pluck('name', 'id');
        $deduction_options = DeductionOption::where('owned_by', \Auth::user()->ownedId())->pluck('name', 'id');
    }

    if (\Auth::user()->type == 'Employee') {
        $currentEmployee      = Employee::where('user_id', \Auth::user()->id)->first();
        $allowances           = Allowance::where('employee_id', $currentEmployee->id)->with(['employee','allowanceOption'])->get();
        $commissions          = Commission::where('employee_id', $currentEmployee->id)->with(['employee'])->get();
        $loans                = Loan::where('employee_id', $currentEmployee->id)->with(['employee','loanOption'])->get();
        $saturationdeductions = SaturationDeduction::where('employee_id', $currentEmployee->id)->with(['deductionOption'])->get();
        $otherpayments        = OtherPayment::where('employee_id', $currentEmployee->id)->get();
        $overtimes            = Overtime::where('employee_id', $currentEmployee->id)->get();

        // ADDED: datasets for Tax + EOBI
        $salarytax            = SalaryTax::where('employee_id', $currentEmployee->id)->get();
        $eobi                 = Eobi::where('employee_id', $currentEmployee->id)->get();

        $employee             = Employee::where('user_id', \Auth::user()->id)->with('salaryType')->first();

        foreach ($allowances as $value) {
            if ($value->type == 'percentage') {
                $emp = Employee::find($value->employee_id);
                $value->tota_allow = $value->amount * $emp->salary / 100;
            }
        }
        foreach ($commissions as $value) {
            if ($value->type == 'percentage') {
                $emp = Employee::find($value->employee_id);
                $value->tota_allow = $value->amount * $emp->salary / 100;
            }
        }
        foreach ($loans as $value) {
            if ($value->type == 'percentage') {
                $emp = Employee::find($value->employee_id);
                $value->tota_allow = $value->amount * $emp->salary / 100;
            }
        }
        foreach ($saturationdeductions as $value) {
            if ($value->type == 'percentage') {
                $emp = Employee::find($value->employee_id);
                $value->tota_allow = $value->amount * $emp->salary / 100;
            }
        }
        foreach ($otherpayments as $value) {
            if ($value->type == 'percentage') {
                $emp = Employee::find($value->employee_id);
                $value->tota_allow = $value->amount * $emp->salary / 100;
            }
        }

        return view('setsalary.employee_salary', compact(
            'employee', 'payslip_type', 'allowance_options', 'commissions', 'loan_options',
            'overtimes', 'otherpayments', 'saturationdeductions', 'loans', 'deduction_options',
            'allowances', 'salarytax', 'eobi' // ADDED
        ));
    } else {
        $allowances           = Allowance::where('employee_id', $id)->get();
        $commissions          = Commission::where('employee_id', $id)->get();
        $loans                = Loan::where('employee_id', $id)->get();
        $saturationdeductions = SaturationDeduction::where('employee_id', $id)->get();
        $otherpayments        = OtherPayment::where('employee_id', $id)->get();
        $overtimes            = Overtime::where('employee_id', $id)->get();

        // ADDED: datasets for Tax + EOBI
        $salarytax            = SalaryTax::where('employee_id', $id)->get();
        $eobi                 = Eobi::where('employee_id', $id)->get();

        $employee             = Employee::with('salaryType')->find($id);

        foreach ($allowances as $value) {
            if ($value->type == 'percentage') {
                $emp = Employee::with('salaryType')->find($value->employee_id);
                $value->tota_allow = $value->amount * $emp->salary / 100;
            }
        }
        foreach ($commissions as $value) {
            if ($value->type == 'percentage') {
                $emp = Employee::with('salaryType')->find($value->employee_id);
                $value->tota_allow = $value->amount * $emp->salary / 100;
            }
        }
        foreach ($loans as $value) {
            if ($value->type == 'percentage') {
                $emp = Employee::with('salaryType')->find($value->employee_id);
                $value->tota_allow = $value->amount * $emp->salary / 100;
            }
        }
        foreach ($saturationdeductions as $value) {
            if ($value->type == 'percentage') {
                $emp = Employee::with('salaryType')->find($value->employee_id);
                $value->tota_allow = $value->amount * $emp->salary / 100;
            }
        }
        foreach ($otherpayments as $value) {
            if ($value->type == 'percentage') {
                $emp = Employee::with('salaryType')->find($value->employee_id);
                $value->tota_allow = $value->amount * $emp->salary / 100;
            }
        }

        return view('setsalary.employee_salary', compact(
            'employee', 'payslip_type', 'allowance_options', 'commissions', 'loan_options',
            'overtimes', 'otherpayments', 'saturationdeductions', 'loans', 'deduction_options',
            'allowances', 'salarytax', 'eobi' // ADDED
        ));
    }
}



    public function employeeUpdateSalary(Request $request, $id)
    {
        $validator = \Validator::make(
            $request->all(), [
                               'salary_type' => 'required',
                               'salary' => 'required',
                           ]
        );
        if($validator->fails())
        {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }
        $employee = Employee::findOrFail($id);
        $input    = $request->all();
        $employee->fill($input)->save();
        Utility::makeActivityLog(\Auth::user()->id,'Employee Salary',$employee->id,'Create Employee Salary',$employee->name);
        return redirect()->back()->with('success', 'Employee Salary Updated.');
    }

    public function employeeSalary()
    {
        if(\Auth::user()->type == "employee")
        {
            $employees = Employee::where('user_id', \Auth::user()->id)->get();
            return view('setsalary.index', compact('employees'));
        }
    }

    public function employeeBasicSalary($id)
    {
        if (\Auth::user()->type == 'company') {
            $payslip_type = PayslipType::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        } else {
            $payslip_type = PayslipType::where('owned_by', \Auth::user()->ownedId())->get()->pluck('name', 'id');
        }
        $payslip_type->prepend('Select Payslip Type','');
        $account = BankAccount::where('created_by', \Auth::user()->creatorId())->where('holder_name' , '!=', 'cash')->get()->pluck('bank_name', 'id');
        // dd($account);
        $employee     = Employee::find($id);
        return view('setsalary.basic_salary', compact('employee', 'payslip_type' , 'account'));
    }


}
