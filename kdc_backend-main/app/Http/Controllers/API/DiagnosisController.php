<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Validator;


class DiagnosisController extends Controller
{

    



     public function index()
    {
        try{
        // $getDiagnosis = DB::table('diagnosis')->get();
        $getDiagnosis = DB::table('diagnosis')
            ->join('diagnosis_type', 'diagnosis.diagnosis_type', '=', 'diagnosis_type.id')
            ->select('diagnosis.*', 'diagnosis_type.name as dname')
            ->get();
        if(!$getDiagnosis->isEmpty()){
             return response()->json([
               "status"  => true,
               "message"  => "Fetch diagnosis details.",
              "data" => $getDiagnosis
           ]);

        }
        else{
             return response()->json([
               "status"  => false,
               "message"  => "No data available.",
               "data" => []

           ]);

        }
    } catch (\Exception $e) {

        \Log::error('Exception occurred: ' . $e->getMessage());
        return response()->json(['status' => false, 'message' => $e->getMessage()]);
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
        'diagnosis_type' => 'required|string|max:255',
        'name' => 'required|string|unique:diagnosis'
    ]);
if ($validator->fails()) {
        return response()->json($validator->errors());
    }
    try{
    // $nameExists = DB::table('diagnosis')->where('name', $request->name)->exists();
    // if ($nameExists) {
    //     return response()->json([
    //         "status" => false,
    //         "message" => "Name already exists.",
    //     ]);
    // }

    $data = [
        "diagnosis_type" => $request->diagnosis_type,
        "name" => $request->name,
        "created_at" => now(),
        "updated_at" => null
    ];

    $saveMedicalCategory = DB::table('diagnosis')->insert($data);

    if ($saveMedicalCategory) {
        $MedicalCategory = DB::table('diagnosis')->where('name',$request->name)->first();
        return response()->json([
            "status" => true,
            "message" => "Diagnosis save successfully.",
            "data"=>  $MedicalCategory
        ]);
    } else {
        return response()->json([
            "status" => false,
            "message" => "Failed to save diagnosis.",
        ]);
    }
} catch (\Exception $e) {

    \Log::error('Exception occurred: ' . $e->getMessage());
    return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
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
            "id"=>'required',
            'diagnosis_type' => 'required|string|max:255',
            'name' => 'required|string'
        ]);
    if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        try{
    	// $nameExists = DB::table('diagnosis')->where('name', $request->name)->exists();
	    // if ($nameExists) {
	    //     return response()->json([
	    //         "status" => false,
	    //         "message" => "Name already exists.",
	    //     ]);
	    // }
        $data = [
        "diagnosis_type" => $request->diagnosis_type,
        "name" => $request->name,
        "updated_at" => date("Y-m-d H:i:s"),
        ];
        $updateMedicalCategory =  DB::table('diagnosis')->where('id',$request->id)->update($data);

        if($updateMedicalCategory){
              $selectQuery =  DB::table('diagnosis')->where('id',$request->id)->first();

                    return response()->json([
                       "status"  => true,
                       "message"  => "Diagnosis updated successfully.",
                       "data" => $selectQuery
                    ]);

        }
        else{
              return response()->json([
                       "status"  => false,
                       "message"  => "Diagnosis updated failed",
                       "data" => []
              ]);
        }
    } catch (\Exception $e) {

        \Log::error('Exception occurred: ' . $e->getMessage());
        return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
    }
     
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "id"=>'required',
        ]);
    if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        try{
        
    $deleteQuery= DB::table('diagnosis')->where('id',$request->id)->delete();

    if($deleteQuery){

        return response()->json([
               "status"  => true,
               "message"  => "Diagnosis deleted successfully.",

           ]);
    }
    else{
        return response()->json([
               "status"  => false,
               "message"  => "Diagnosis delete failed.",
               "data" => []

           ]);
    }
} catch (\Exception $e) {

    \Log::error('Exception occurred: ' . $e->getMessage());
    return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
}
    }






         public function getDiagnosis(Request $request)
    {

     
        try{
      $getdiagnosis = DB::table('diagnosis')
                    ->where('diagnosis_type', $request->doagnosis_id)
                    ->get();
        if(!$getdiagnosis->isEmpty()){
             return response()->json([
               "status"  => true,
               "message"  => "Fetch diagnosis details.",
               "data" => $getdiagnosis
           ]);

        }
        else{
             return response()->json([
               "status"  => false,
               "message"  => "No data available.",
               "data" => []

           ]);

        }
    } catch (\Exception $e) {

        \Log::error('Exception occurred: ' . $e->getMessage());
        return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
    }
    }

}
