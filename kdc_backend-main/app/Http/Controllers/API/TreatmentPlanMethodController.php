<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Validator;


class TreatmentPlanMethodController extends Controller
{
    public function index()
    {
        $getPrescriptionCategory = DB::table('treatment_plan_method')
            ->select(
                'treatment_plan_method.id',
                'treatment_plan_method.treatment_procedure_id',
                'treatment_plan_method.treatment_method',
                'treatment_plan_method.method_price',
                'treatment_plan_method.doctor_id',
                'treatment_plan_method.doctor_price',
                'treatment_plan_method.created_at',
                'treatment_plan_method.updated_at',
                'treatment_plan_procedure.treatment_procedure'
            )
            ->join('treatment_plan_procedure', 'treatment_plan_procedure.id', '=', 'treatment_plan_method.treatment_procedure_id')
            ->get();




        if (!$getPrescriptionCategory->isEmpty()) {
            return response()->json([
                "status" => true,
                "message" => "Treatment plan method success",
                "data" => $getPrescriptionCategory
            ]);

        } else {
            return response()->json([
                "status" => false,
                "message" => "No data available.",
                "data" => []

            ]);

        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'treatment_procedure_id' => 'required',
            'treatment_method' => 'required|unique:treatment_plan_method',
            'method_price' => 'required',

        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {

            $data =
                [
                    "treatment_procedure_id" => $request->treatment_procedure_id,
                    "treatment_method" => $request->treatment_method,
                    "method_price" => $request->method_price,
                    "doctor_id" => $request->doctor_id,
                    "doctor_price" => $request->doctor_price,
                    "created_at" => date("Y-m-d H:i:s"),
                    "updated_at" => date("Y-m-d H:i:s"),
                ];
            $insert = DB::table('treatment_plan_method')->insert($data);
            if ($insert) {

                $fetchdata = DB::table('treatment_plan_method')
                    ->select(
                        'treatment_plan_method.id',
                        'treatment_plan_method.treatment_procedure_id',
                        'treatment_plan_method.treatment_method',
                        'treatment_plan_method.method_price',
                        'treatment_plan_method.doctor_id',
                        'users.first_name as doctor_name',
                        'treatment_plan_method.doctor_price',
                        'treatment_plan_procedure.treatment_procedure',
                        'treatment_plan_method.created_at',
                        'treatment_plan_method.updated_at',
                    )
                    ->join('treatment_plan_procedure', 'treatment_plan_procedure.id', '=', 'treatment_plan_method.treatment_procedure_id')
                    ->join('users', 'users.id', '=', 'treatment_plan_method.doctor_id')
                    ->where('treatment_method', $request->treatment_method)
                    ->first();

                return response()->json([
                    "status" => true,
                    "message" => "Treatment plan method success",
                    "data" => $fetchdata
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Treatment plan method failed",
                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.', "error" => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required'

        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        $data = [

            "treatment_procedure_id" => $request->treatment_procedure_id,
            "treatment_method" => $request->treatment_method,
            "method_price" => $request->method_price,
            "doctor_id" => $request->doctor_id,
            "doctor_price" => $request->doctor_price,
            "created_at" => date("Y-m-d H:i:s"),
            "updated_at" => date("Y-m-d H:i:s")
        ];
        $updateMedicalCategory = DB::table('treatment_plan_method')->where('id', $request->id)->update($data);

        if ($updateMedicalCategory) {
            $selectQuery = DB::table('treatment_plan_method')->where('id', $request->id)->first();

            return response()->json([
                "status" => true,
                "message" => "Treatment plan method updated successfully.",
                "data" => $selectQuery
            ]);

        } else {
            return response()->json([
                "status" => false,
                "message" => "Treatment plan method updated failed",
                "data" => []
            ]);
        }



    }




    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {

        $deleteQuery = DB::table('treatment_plan_method')->where('id', $request->id)->delete();

        if ($deleteQuery) {

            return response()->json([
                "status" => true,
                "message" => "Treatment method deleted successfully.",

            ]);
        } else {
            return response()->json([
                "status" => false,
                "message" => "Treatment method delete failed.",
                "data" => []

            ]);
        }
    }

    public function getmethodTreatmentPlan(Request $request)
    {
        $gettreatment_sub_plan_category = DB::table('treatment_plan_method')
            ->where('diagnosis_type_id', $request->diagnosis_type_id)
            ->where('diagnosis_id', $request->diagnosis_id)
            ->where('treatment_plan_id', $request->treatment_plan_id)
            ->where('treatment_procedure_id', $request->treatment_procedure_id)
            ->get();
        // print_r($gettreatment_sub_plan_category);
        // exit;
        if (!$gettreatment_sub_plan_category->isEmpty()) {
            return response()->json([
                "status" => true,
                "message" => "Fetch diagnosis details.",
                "data" => $gettreatment_sub_plan_category
            ]);

        } else {
            return response()->json([
                "status" => false,
                "message" => "No data available.",
                "data" => []

            ]);

        }
    }
}