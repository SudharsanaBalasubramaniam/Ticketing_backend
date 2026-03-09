<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Validator;

class PrescriptionCategoryController extends Controller
{



    public function index()
    {

        $getPrescriptionCategory = DB::table('prescription_category')->get();

        if (!$getPrescriptionCategory->isEmpty()) {
            return response()->json([
                "status" => true,
                "message" => "Fetch Prescription Category details.",
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

            'name' => 'required|string|max:255|unique:prescription_category',



        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }


        try {
            $data = [
                "name" => $request->name,

                "created_at" => now(),
                "updated_at" => null
            ];

            $saveMedicalCategory = DB::table('prescription_category')->insert($data);
            if ($saveMedicalCategory) {
                return response()->json([
                    "status" => true,
                    "message" => "Prescription category saved successfully.",
                    "data" => $data
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
            "id" => 'required',

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {
            $data = [
                "id" =>  $request->id,
                "name" => $request->name,
                "updated_at" => date("Y-m-d H:i:s"),
            ];
            $updateMedicalCategory = DB::table('prescription_category')->where('id', $request->id)->update($data);

            if ($updateMedicalCategory) {
                $selectQuery = DB::table('prescription_category')->where('id', $request->id)->first();

                return response()->json([
                    "status" => true,
                    "message" => "Prescription category updated successfully.",
                    "data" => $selectQuery
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Prescription category updated failed",
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
        try {
            $deleteQuery = DB::table('prescription_category')->where('id', $request->id)->delete();

            if ($deleteQuery) {

                return response()->json([
                    "status" => true,
                    "message" => "Prescription category deleted successfully.",

                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Prescription category delete failed.",
                    "data" => []

                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
        }
    }
}
