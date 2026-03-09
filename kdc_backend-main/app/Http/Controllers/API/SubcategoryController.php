<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Validator;

class SubcategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $getCategoryDetails = DB::table('subcategory')->get();
        if(!$getCategoryDetails->isEmpty()){
             return response()->json([
               "status"  => true,
               "message"  => "Fetch Subcategory details.",
               "data" => $getCategoryDetails
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
        $data = [
         "category_id" => $request->category_id,
         "subcategory_name" => $request->subcategory_name,
         "created_at" => date("Y-m-d H:i:s"),
         "updated_at" => null
        ];

        $saveCategory =  DB::table('subcategory')->insert($data);
        return response()->json([
                       "status"  => true,
                       "message"  => "Sub category insert successfully.",
                    ]);
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
             "category_id" => $request->category_id,
             "subcategory_name" => $request->subcategory_name,
             "updated_at" => date("Y-m-d H:i:s")
        ];

        $updateCategory =  DB::table('subcategory')->where('id',$request->id)->update($data);
        
        if($updateCategory){
              $selectQuery =  DB::table('subcategory')->where('id',$request->id)->first();

                    return response()->json([
                       "status"  => true,
                       "message"  => "Subcategory updated successfully.",
                       "data" => $selectQuery
                    ]);

        }
        else{
              return response()->json([
                       "status"  => false,
                       "message"  => "Subcategory updated failed",
                       "data" => []
              ]);
        }

     
    }


   
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        
    $deleteQuery= DB::table('subcategory')->where('id',$request->id)->delete();

    if($deleteQuery){

        return response()->json([
               "status"  => true,
               "message"  => "Subcategory deleted successfully.",

           ]);
    }
    else{
        return response()->json([
               "status"  => false,
               "message"  => "Subcategory delete failed.",
               "data" => []

           ]);
    }
    }
}
