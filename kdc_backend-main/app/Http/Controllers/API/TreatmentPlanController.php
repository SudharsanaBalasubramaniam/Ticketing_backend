<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Validator;

class TreatmentPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $getTreatment = DB::table('treatmentplan')
            ->select('treatmentplan.id', 'treatmentplan.diagnosis_type_id', 'treatmentplan.diagnosis_id', 'treatmentplan.treatment_plan', 'treatmentplan.created_at', 'treatmentplan.updated_at', 'diagnosis.name as diagnosis_name', 'diagnosis_type.name as diagnosis_type_name')
            ->join('diagnosis', 'diagnosis.id', '=', 'treatmentplan.diagnosis_id')
            ->join('diagnosis_type', 'diagnosis_type.id', '=', 'treatmentplan.diagnosis_type_id')
            ->get();
        if (!$getTreatment->isEmpty()) {
            return response()->json([
                "status" => true,
                "message" => "Fetch Treatment Category details.",
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



    //   public function index()
//     {
//         // $getTreatment = DB::table('treatmentplan')->get();

    //           $getTreatment = DB::table('treatmentplan')
//           ->select('treatmentplan.id','treatmentplan.diagnosis_type_id','treatmentplan.diagnosis_id','category.id as category_id','treatmentplan.created_at','treatmentplan.updated_at','diagnosis.name as diagnosis_name','diagnosis_type.name as diagnosis_type_name','category.category_name')
//           ->join('diagnosis','diagnosis.id','=','treatmentplan.diagnosis_id')
//           ->join('diagnosis_type','diagnosis_type.id','=','treatmentplan.diagnosis_type_id')
//           ->join('category','category.id','=','treatmentplan.treatment_plan')
//           ->where('category.status',1)
//           ->where('diagnosis.status',1)
//           ->where('diagnosis_type.status',1)
//           ->get();

    //         if(!$getTreatment->isEmpty()){
//              return response()->json([
//               "status"  => true,
//               "message"  => "Fetch Treatment Category details.",
//               "data" => $getTreatment
//           ]);

    //         }
//         else{
//              return response()->json([
//               "status"  => false,
//               "message"  => "No data available.",
//               "data" => []

    //           ]);

    //         }
//     }
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
            'diagnosis_type_id' => 'required',
            'diagnosis_id' => 'required',
            'treatment_plan' => 'required|unique:treatmentplan',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {

            $data = [
                "diagnosis_type_id" => $request->diagnosis_type_id,
                "diagnosis_id" => $request->diagnosis_id,
                "treatment_plan" => $request->treatment_plan,
                "created_at" => now(),
                "updated_at" => null
            ];

            $saveMedicalCategory = DB::table('treatmentplan')->insert($data);
            if ($saveMedicalCategory) {
                $saveMedicalCategory = DB::table('treatmentplan')
                    ->select(
                        'treatmentplan.id',
                        'treatmentplan.diagnosis_id',
                        'treatmentplan.diagnosis_type_id',
                        'treatmentplan.treatment_plan',
                        'diagnosis.name as diagnosis_name',
                        'diagnosis_type.name as diagnosis_type_name',
                        'treatmentplan.created_at',
                        'treatmentplan.updated_at',
                    )
                    ->join('diagnosis', 'diagnosis.id', '=', 'treatmentplan.diagnosis_id')
                    ->join('diagnosis_type', 'diagnosis_type.id', '=', 'treatmentplan.diagnosis_type_id')
                    ->where('treatment_plan', $request->treatment_plan)->first();

                return response()->json([
                    "status" => true,
                    "message" => "Treatment category saved successfully.",
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
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.', "error" => $e->getMessage()]);
        }
    }

    // $getDiagnosis = DB::table('diagnosis')->where('id', $request->diagnosis_id)->first();
    // $getDiagnosis_type = DB::table('diagnosis_type')->where('id', $request->diagnosis_type_id)->first();


    // $saveMedicalCategory->diagnosis_name = $getDiagnosis->name;
    // $saveMedicalCategory->diagnosis_type_name = $getDiagnosis_type->name;
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
        $nameExists = DB::table('treatmentplan')->where('treatment_plan', $request->treatment_plan)->exists();
        if ($nameExists) {
            return response()->json([
                "status" => false,
                "message" => "Name already exists.",
            ]);
        }
        $data = [
            "diagnosis_type_id" => $request->diagnosis_type_id,
            "diagnosis_id" => $request->diagnosis_id,
            "treatment_plan" => $request->treatment_plan,
            "updated_at" => date("Y-m-d H:i:s"),
        ];
        $updateMedicalCategory = DB::table('treatmentplan')->where('id', $request->id)->update($data);

        if ($updateMedicalCategory) {
            $selectQuery = DB::table('treatmentplan')->where('id', $request->id)->first();

            return response()->json([
                "status" => true,
                "message" => "Treatment category updated successfully.",
                "data" => $selectQuery
            ]);

        } else {
            return response()->json([
                "status" => false,
                "message" => "Treatment category updated failed",
                "data" => []
            ]);
        }


    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {

        $deleteQuery = DB::table('treatmentplan')->where('id', $request->id)->delete();

        if ($deleteQuery) {

            return response()->json([
                "status" => true,
                "message" => "Treatment category deleted successfully.",

            ]);
        } else {
            return response()->json([
                "status" => false,
                "message" => "Treatment category delete failed.",
                "data" => []

            ]);
        }
    }

    public function getTreatmentPlan(Request $request)
    {
        $gettreatment_plan_category = DB::table('treatmentplan')
            ->where('diagnosis_type_id', $request->diagnosis_type_id)
            ->where('diagnosis_id', $request->doagnosis_id)
            ->get();
        // print_r($gettreatment_plan_category);
        // exit;
        if (!$gettreatment_plan_category->isEmpty()) {
            return response()->json([
                "status" => true,
                "message" => "Fetch diagnosis details.",
                "data" => $gettreatment_plan_category
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
