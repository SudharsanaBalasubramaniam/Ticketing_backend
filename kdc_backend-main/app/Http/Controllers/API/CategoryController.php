<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Validator;


// use\Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */



    public function index()
    {
        try {
            $getCategoryDetails = DB::table('category')->get();
            if (!$getCategoryDetails->isEmpty()) {
                return response()->json([
                    "status" => true,
                    "message" => "Fetch category details.",
                    "data" => $getCategoryDetails
                ]);

            } else {
                return response()->json([
                    "status" => false,
                    "message" => "No data available.",
                    "data" => []

                ]);

            }

        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
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
            'category_name' => 'required'

        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        try {
            $data = [
                "category_name" => $request->category_name,
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => null
            ];

            $saveCategory = DB::table('category')->insert($data);
            if ($saveCategory) {
                $Category = DB::table('category')->where('category_name', $request->category_name)->first();
                return response()->json([
                    "status" => true,
                    "message" => "Category insert successfully.",
                    "data" => $Category
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
            'category_name' => 'required'

        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        try{
        $data = [

            "category_name" => $request->category_name,
            "updated_at" => date("Y-m-d H:i:s")
        ];

        $updateCategory = DB::table('category')->where('id', $request->id)->update($data);

        if ($updateCategory) {
            $selectQuery = DB::table('category')->where('id', $request->id)->first();

            return response()->json([
                "status" => true,
                "message" => "Category updated successfully.",
                "data" => $selectQuery
            ]);

        } else {
            return response()->json([
                "status" => false,
                "message" => "Category updated failed",
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
            'id' => 'required'

        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
try{
        $deleteQuery = DB::table('category')->where('id', $request->id)->delete();

        if ($deleteQuery) {

            return response()->json([
                "status" => true,
                "message" => "Category deleted successfully.",

            ]);
        } else {
            return response()->json([
                "status" => false,
                "message" => "Category delete failed.",
                "data" => []

            ]);
        }
 
    
} catch (\Exception $e) {

    \Log::error('Exception occurred: ' . $e->getMessage());
    return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
    }
}
}