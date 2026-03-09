<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Validator;
use Carbon\Carbon;

class SalaryController extends Controller
{
    public function get_staff_salary(Request $request)
    {
        $request->validate([
            'staff_type' => 'required|string',
            'staff_id' => 'required|string',
        ]);

        try {
            $staffType = $request->input('staff_type');
            $staffId = $request->input('staff_id');

            if ($staffType === 'staff') {
                $staffSalaries = DB::table('staff_salary')
                    ->select(
                        'staff_salary.*',
                        'users.first_name as staff_name',
                        'users.aadhar_number',
                        'users.staff_category_id',
                        'staff_categories.name as staff_category_name'
                    )
                    ->join('users', 'users.employee_number', '=', 'staff_salary.staff_id')
                    ->join('staff_categories', 'staff_categories.id', '=', 'users.staff_category_id')
                    ->where('staff_id', $staffId)
                    ->orderBy('created_at', 'desc')
                    ->get();

                return response()->json([
                    'status' => true,
                    'message' => 'Staff salaries retrieved successfully.',
                    'data' => $staffSalaries
                ]);
            }

            if ($staffType === 'doctor') {
                $staffSalaries = DB::table('doctor_salary')
                    ->select(
                        'doctor_salary.*',
                        'users.first_name as staff_name',
                        'users.aadhar_number',
                        'users.department_id',
                        'doctor_departments.title as doctor_department_name'
                    )
                    ->join('users', 'users.employee_number', '=', 'doctor_salary.doctor_id')
                    ->join('doctor_departments', 'doctor_departments.id', '=', 'users.department_id')
                    ->where('doctor_id', $staffId)
                    ->orderBy('created_at', 'desc')
                    ->get();

                return response()->json([
                    'status' => true,
                    'message' => 'Staff salaries retrieved successfully.',
                    'data' => $staffSalaries
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => 'Invalid staff type'
            ]);
        } catch (\Exception $e) {
            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Something went wrong.', "error" => $e->getMessage()]);
        }
    }

    public function list_salaries(Request $request)
    {
        // Optional filter by specific staff
        $staffId = $request->input('staff_id');
        $staffType = $request->input('staff_type');

        $staffQuery = DB::table('staff_salary')
            ->select(
                'staff_salary.id as staff_salary_id',
                'users.id as user_id',
                'staff_salary.staff_id',
                'staff_salary.salary',
                'staff_salary.allowance',
                'staff_salary.income_tax',
                'staff_salary.otc',
                'staff_salary.epf',
                'staff_salary.gross_salary',
                'staff_salary.created_at',
                'staff_salary.updated_at',
                'users.first_name as staff_name',
                'users.aadhar_number',
                'users.staff_category_id',
                'staff_categories.name as staff_category_name'
            )
            ->join('users', 'users.employee_number', '=', 'staff_salary.staff_id')
            ->join('staff_categories', 'staff_categories.id', '=', 'users.staff_category_id');

        if ($staffId && $staffType === 'staff') {
            $staffQuery->where('staff_salary.staff_id', $staffId);
        }

        $staffSalaries = $staffQuery->get();
        $staffSalaries = $staffSalaries->map(function ($item) {
            $item->type = 'staff';
            $item->doctor_id = null;
            $item->doctor_salary_id = null;
            $item->department_id = null;
            $item->doctor_department_name = null;
            return $item;
        });

        $doctorQuery = DB::table('doctor_salary')
            ->select(
                'doctor_salary.id as doctor_salary_id',
                'users.id as user_id',
                'doctor_salary.doctor_id',
                'doctor_salary.salary',
                'doctor_salary.allowance',
                'doctor_salary.income_tax',
                'doctor_salary.otc',
                'doctor_salary.epf',
                'doctor_salary.gross_salary',
                'doctor_salary.created_at',
                'doctor_salary.updated_at',
                'users.first_name as staff_name',
                'users.aadhar_number',
                'users.department_id',
                'doctor_departments.title as doctor_department_name'
            )
            ->join('users', 'users.employee_number', '=', 'doctor_salary.doctor_id')
            ->join('doctor_departments', 'doctor_departments.id', '=', 'users.department_id');

        if ($staffId && $staffType === 'doctor') {
            $doctorQuery->where('doctor_salary.doctor_id', $staffId);
        }

        $doctorSalaries = $doctorQuery->get();
        $doctorSalaries = $doctorSalaries->map(function ($item) {
            $item->type = 'doctor';
            $item->staff_id = null;
            $item->staff_salary_id = null;
            $item->staff_category_id = null;
            $item->staff_category_name = null;
            return $item;
        });

        $mergedSalaries = $staffSalaries->merge($doctorSalaries);
        $sortedSalaries = $mergedSalaries->sortByDesc('created_at')->values()->all();

        $grouped_salary = collect($sortedSalaries)->groupBy(function ($item) {
            return Carbon::parse($item->created_at)->format('Y-m');
        })->toArray();
        $monthwiseSum1 = [];
        foreach ($grouped_salary as $key => $group1) {
            $Monsum1['year'] = Carbon::parse($key)->year;
            $Monsum1['month'] = Carbon::parse($key)->month;
            $Monsum1['date'] = $key;
            $Monsum1['salary_sum'] = collect($group1)->sum('gross_salary');
            $Monsum1['salary_list'] = $group1;
            $monthwiseSum1[] = $Monsum1;
        }
        return response()->json([
            "status" => true,
            "message" => "salary summing success",
            "data" => $monthwiseSum1
        ]);
    }

    public function store_staff_salary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "staff_id" => 'required',
            "salary" => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {
            $gross_salary = $request->salary + $request->allowance + $request->otc - $request->income_tax - $request->epf;
            $data = [
                "staff_id" => $request->staff_id,
                "salary" => $request->salary,
                "allowance" => $request->allowance,
                "income_tax" => $request->income_tax,
                "otc" => $request->otc,
                "epf" => $request->epf,
                "gross_salary" => $gross_salary,
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s")
            ];

            $staff_salary = DB::table('staff_salary')->insert($data);

            $salary = DB::table('staff_salary')
                ->select(
                    DB::raw('DISTINCT staff_salary.id'),
                    'staff_salary.staff_id',
                    'staff_salary.salary',
                    'staff_salary.allowance',
                    'staff_salary.income_tax',
                    'staff_salary.otc',
                    'staff_salary.epf',
                    'staff_salary.gross_salary',
                    'staff_salary.created_at',
                    'staff_salary.updated_at',
                    'users.first_name as staff_name',
                    'users.staff_category_id',
                    'staff_categories.name as staff_category_name'
                )
                ->join('users', 'users.employee_number', '=', 'staff_salary.staff_id')
                ->join('staff_categories', 'staff_categories.id', '=', 'users.staff_category_id')
                ->where('staff_salary.staff_id', $request->staff_id)
                ->orderBy('staff_salary.created_at', 'DESC')
                ->first();

            return response()->json([
                "status" => true,
                "message" => "Staff salary saved successfully.",
                "data" => $salary
            ]);
        } catch (\Exception $e) {
            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Something went wrong.', "error" => $e->getMessage()]);
        }
    }

    public function store_doctor_salary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "doctor_id" => 'required',
            "salary" => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {
            $gross_salary = $request->salary + $request->allowance + $request->otc - $request->income_tax - $request->epf;
            $data = [
                "doctor_id" => $request->doctor_id,
                "salary" => $request->salary,
                "allowance" => $request->allowance,
                "income_tax" => $request->income_tax,
                "otc" => $request->otc,
                "epf" => $request->epf,
                "gross_salary" => $gross_salary,
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s")
            ];

            $doctor_salary = DB::table('doctor_salary')->insert($data);

            $salary = DB::table('doctor_salary')
                ->select(
                    DB::raw('DISTINCT doctor_salary.id'),
                    'doctor_salary.doctor_id',
                    'doctor_salary.salary',
                    'doctor_salary.allowance',
                    'doctor_salary.income_tax',
                    'doctor_salary.otc',
                    'doctor_salary.epf',
                    'doctor_salary.gross_salary',
                    'doctor_salary.created_at',
                    'doctor_salary.updated_at',
                    'users.first_name as staff_name',
                    'users.department_id',
                    'doctor_departments.title as doctor_department_name'
                )
                ->join('users', 'users.employee_number', '=', 'doctor_salary.doctor_id')
                ->join('doctor_departments', 'doctor_departments.id', '=', 'users.department_id')
                ->where('doctor_salary.doctor_id', $request->doctor_id)
                ->orderBy('doctor_salary.created_at', 'DESC')
                ->first();

            return response()->json([
                "status" => true,
                "message" => "doctor salary saved successfully.",
                "data" => $salary
            ]);
        } catch (\Exception $e) {
            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Something went wrong.', "error" => $e->getMessage()]);
        }
    }

    public function update_salary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "employee_id" => 'required',
            "employee_type" => 'required|in:staff,doctor',
            "salary" => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {
            $gross_salary = $request->salary + $request->allowance + $request->otc - $request->income_tax - $request->epf;

            $data = [
                "id" => $request->id,
                "{$request->employee_type}_id" => $request->employee_id,
                "salary" => $request->salary,
                "allowance" => $request->allowance,
                "income_tax" => $request->income_tax,
                "otc" => $request->otc,
                "epf" => $request->epf,
                "gross_salary" => $gross_salary,
                "created_at" => now(),
                "updated_at" => now(),
            ];

            $table = $request->employee_type === 'staff' ? 'staff_salary' : 'doctor_salary';

            // Check if a record already exists for the provided employee_id
            $existingRecord = DB::table($table)
                ->where("id", $request->id)
                ->orderBy('created_at', 'DESC')
                ->first();

            if ($existingRecord) {
                // Update the existing record
                DB::table($table)
                    ->where("id", $request->id)
                    ->update([
                        "id" => $request->id,
                        "{$request->employee_type}_id" => $request->employee_id,
                        "salary" => $request->salary,
                        "allowance" => $request->allowance,
                        "income_tax" => $request->income_tax,
                        "otc" => $request->otc,
                        "epf" => $request->epf,
                        "gross_salary" => $gross_salary,
                        "updated_at" => now(),
                    ]);
            } else {
                // Insert a new record
                DB::table($table)->insert($data);
            }

            // Retrieve the updated or inserted record
            $salary = DB::table($table)
                ->select(
                    DB::raw("DISTINCT {$table}.id"),
                    "{$table}.{$request->employee_type}_id",
                    "{$table}.salary",
                    "{$table}.allowance",
                    "{$table}.income_tax",
                    "{$table}.otc",
                    "{$table}.epf",
                    "{$table}.created_at",
                    "{$table}.updated_at",
                    'users.first_name as employee_name',
                    'users.department_id',
                    'doctor_departments.title as doctor_department_name',
                    'staff_categories.name as staff_category_name'
                )
                ->join('users', 'users.employee_number', '=', "{$table}.{$request->employee_type}_id")
                ->leftJoin('doctor_departments', 'doctor_departments.id', '=', 'users.department_id')
                ->leftJoin('staff_categories', 'staff_categories.id', '=', 'users.staff_category_id')
                ->where("{$table}.{$request->employee_type}_id", $request->employee_id)
                ->orderBy("{$table}.created_at", 'DESC')
                ->first();

            return response()->json([
                "status" => true,
                "message" => "{$request->employee_type} salary updated successfully.",
                "data" => $salary,
            ]);
        } catch (\Exception $e) {
            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Something went wrong.', "error" => $e->getMessage()]);
        }
    }

    public function delete_salary(Request $request)
    {
        $table = $request->type === 'staff' ? 'staff_salary' : 'doctor_salary';
        $deleteQuery = DB::table($table)
            ->where("id", $request->id)
            ->orderBy('created_at', 'DESC')
            ->first();

        if ($deleteQuery) {
            DB::table($table)
                ->where("id", $request->id)
                ->delete();
        }

        if ($deleteQuery) {
            return response()->json([
                "status" => true,
                "message" => "salary deleted successfully.",
            ]);
        } else {
            return response()->json([
                "status" => false,
                "message" => "salary delete failed.",
                "data" => []
            ]);
        }
    }
}