<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;


class MedicalController extends Controller
{
     public function index()
    {
        $getMedicalCategory = DB::table('medical_category')->get();
        if(!$getMedicalCategory->isEmpty()){
             return response()->json([
               "status"  => true,
               "message"  => "Fetch MedicalCategory details.",
               "data" => $getMedicalCategory
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
    $nameExists = DB::table('medical_category')->where('name', $request->name)->exists();
    if ($nameExists) {
        return response()->json([
            "status" => false,
            "message" => "Name already exists.",
        ]);
    }

    $data = [
        "name" => $request->name,
        "created_at" => now(),
        "updated_at" => null
    ];

    $saveMedicalCategory = DB::table('medical_category')->insert($data);
    if ($saveMedicalCategory) {
        return response()->json([
            "status" => true,
            "message" => "Medical category saved successfully.",
        ]);
    } else {
        return response()->json([
            "status" => false,
            "message" => "Failed to save medical category.",
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
    	$nameExists = DB::table('medical_category')->where('name', $request->name)->exists();
	    if ($nameExists) {
	        return response()->json([
	            "status" => false,
	            "message" => "Name already exists.",
	        ]);
	    }
        $data = [
         "name" => $request->name,
         "updated_at" => date("Y-m-d H:i:s"),
        ];
        $updateMedicalCategory =  DB::table('medical_category')->where('id',$request->id)->update($data);

        if($updateMedicalCategory){
              $selectQuery =  DB::table('medical_category')->where('id',$request->id)->first();

                    return response()->json([
                       "status"  => true,
                       "message"  => "Medical category updated successfully.",
                       "data" => $selectQuery
                    ]);

        }
        else{
              return response()->json([
                       "status"  => false,
                       "message"  => "Medical category updated failed",
                       "data" => []
              ]);
        }

     
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        
    $deleteQuery= DB::table('medical_category')->where('id',$request->id)->delete();

    if($deleteQuery){

        return response()->json([
               "status"  => true,
               "message"  => "Medical category deleted successfully.",

           ]);
    }
    else{
        return response()->json([
               "status"  => false,
               "message"  => "Medical category delete failed.",
               "data" => []

           ]);
    }
    }
}
