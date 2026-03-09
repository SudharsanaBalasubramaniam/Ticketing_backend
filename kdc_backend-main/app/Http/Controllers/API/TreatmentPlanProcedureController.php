<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Validator;
class TreatmentPlanProcedureController extends Controller
{
    public function index()
    {
        $getTreatment = DB::table('treatment_plan_procedure')
        ->select('treatment_plan_procedure.id','treatment_plan_procedure.treatment_procedure','treatment_plan_procedure.treatment_plan_id','treatment_plan_procedure.created_at','treatment_plan_procedure.updated_at','treatmentplan.treatment_plan as treatment_plan_name')
        ->join('treatmentplan','treatmentplan.id','=','treatment_plan_procedure.treatment_plan_id')
        ->get();
        if (!$getTreatment->isEmpty()) {
            return response()->json([
                "status" => true,
                "message" => "Fetch Treatment sub-category details.",
                "data" => $getTreatment
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
            'treatment_plan_id' => 'required',
            'treatment_procedure' => 'required|unique:treatment_plan_procedure',
    
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {
        $data = [
            "treatment_plan_id" => $request->treatment_plan_id,
            "treatment_procedure" => $request->treatment_procedure,
            "created_at" => now(),
            "updated_at" => null
        ];
        $saveMedicalCategory = DB::table('treatment_plan_procedure')->insert($data);
       
        if ($saveMedicalCategory) {
          
            $saveMedicalCategory = DB::table('treatment_plan_procedure')
            ->select(
                'treatment_plan_procedure.id',
                'treatment_plan_procedure.treatment_procedure',
                'treatment_plan_procedure.treatment_plan_id',
                'treatmentplan.treatment_plan as treatment_plan_name',
                'treatment_plan_procedure.created_at',
                'treatment_plan_procedure.updated_at',
            )
            ->join('treatmentplan', 'treatmentplan.id', '=', 'treatment_plan_procedure.treatment_plan_id')
            ->where('treatment_procedure', $request->treatment_procedure)
            ->first();


            return response()->json([
                "status" => true,
                "message" => "Treatment sub-category saved successfully.",
                "data" => $saveMedicalCategory
            ]);
        } else {
            return response()->json([
                "status" => false,
                "message" => "Failed to save diagnosis.",
            ]);
        }
    } catch (\Exception $e) {

        \Log::error('Exception occurred: ' . $e->getMessage());
        return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.',"error"=>$e->getMessage()]);
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
       
        $data = [

            "treatment_plan_id" => $request->treatment_plan_id,
            "treatment_procedure" => $request->treatment_procedure,
            "updated_at" => date("Y-m-d H:i:s"),
        ];
        $updateMedicalCategory = DB::table('treatment_plan_procedure')->where('id', $request->id)->update($data);

        if ($updateMedicalCategory) {
            $selectQuery = DB::table('treatment_plan_procedure')->where('id', $request->id)->first();

            return response()->json([
                "status" => true,
                "message" => "Treatment sub-category updated successfully.",
                "data" => $selectQuery
            ]);

        } else {
            return response()->json([
                "status" => false,
                "message" => "Treatment sub-category updated failed",
                "data" => []
            ]);
        }


    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {

        $deleteQuery = DB::table('treatment_plan_procedure')->where('id', $request->id)->delete();

        if ($deleteQuery) {

            return response()->json([
                "status" => true,
                "message" => "Treatment sub-category deleted successfully.",

            ]);
        } else {
            return response()->json([
                "status" => false,
                "message" => "Treatment sub-category delete failed.",
                "data" => []

            ]);
        }
    }

    public function getsubTreatmentPlan(Request $request)
    {
        $gettreatment_sub_plan_category = DB::table('treatmentplan_procedure')
            ->where('diagnosis_type_id', $request->diagnosis_type_id)
            ->where('diagnosis_type_id', $request->diagnosis_type_id)
            ->where('treatment_category_id', $request->treatment_category_id)
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