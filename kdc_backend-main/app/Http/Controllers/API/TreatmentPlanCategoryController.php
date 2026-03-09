<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class TreatmentPlanCategoryController extends Controller
{
     public function index()
    {
        $getTreatment = DB::table('treatment_plan_category')->get();
        if(!$getTreatment->isEmpty()){
             return response()->json([
               "status"  => true,
               "message"  => "Fetch Treatment Category details.",
               "data" => $getTreatment
           ]);

        }
        else{
             return response()->json([
               "status"  => false,
               "message"  => "No data available.",
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
    $nameExists = DB::table('treatment_plan_category')->where('treatment_plan', $request->treatment_plan)->exists();
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
        "created_at" => now(),
        "updated_at" => null
    ];

    $saveMedicalCategory = DB::table('treatment_plan_category')->insert($data);
    if ($saveMedicalCategory) {
        return response()->json([
            "status" => true,
            "message" => "Treatment category saved successfully.",
        ]);
    } else {
        return response()->json([
            "status" => false,
            "message" => "Failed to save diagnosis.",
        ]);
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
    	$nameExists = DB::table('treatment_plan_category')->where('treatment_plan', $request->treatment_plan)->exists();
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
        $updateMedicalCategory =  DB::table('treatment_plan_category')->where('id',$request->id)->update($data);

        if($updateMedicalCategory){
              $selectQuery =  DB::table('treatment_plan_category')->where('id',$request->id)->first();

                    return response()->json([
                       "status"  => true,
                       "message"  => "Treatment category updated successfully.",
                       "data" => $selectQuery
                    ]);

        }
        else{
              return response()->json([
                       "status"  => false,
                       "message"  => "Treatment category updated failed",
                       "data" => []
              ]);
        }

     
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        
    $deleteQuery= DB::table('treatment_plan_category')->where('id',$request->id)->delete();

    if($deleteQuery){

        return response()->json([
               "status"  => true,
               "message"  => "Treatment category deleted successfully.",

           ]);
    }
    else{
        return response()->json([
               "status"  => false,
               "message"  => "Treatment category delete failed.",
               "data" => []

           ]);
    }
    }

             public function getTreatmentPlan(Request $request)
    {
      $gettreatment_plan_category = DB::table('treatment_plan_category')
                    ->where('diagnosis_type_id', $request->diagnosis_type_id)
                    ->where('diagnosis_id', $request->doagnosis_id)
                    ->get();
                    // print_r($gettreatment_plan_category);
                    // exit;
        if(!$gettreatment_plan_category->isEmpty()){
             return response()->json([
               "status"  => true,
               "message"  => "Fetch diagnosis details.",
               "data" => $gettreatment_plan_category
           ]);

        }
        else{
             return response()->json([
               "status"  => false,
               "message"  => "No data available.",
               "data" => []

           ]);

        }
    }

}
