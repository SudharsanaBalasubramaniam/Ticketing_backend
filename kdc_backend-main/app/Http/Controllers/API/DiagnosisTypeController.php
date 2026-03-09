<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Validator;

class DiagnosisTypeController extends Controller
{


    


    public function index()
    {
        try {
            $getDiagnosistype = DB::table('diagnosis_type')->get();
            if (!$getDiagnosistype->isEmpty()) {
                return response()->json([
                    "status" => true,
                    "message" => "Fetch diagnosis type details.",
                    "data" => $getDiagnosistype
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

            'name' => 'required|string|max:255|unique:diagnosis_type',

        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {
            $data = [
                "name" => $request->name,
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s")
            ];

            $saveMedicalCategory = DB::table('diagnosis_type')->insert($data);
            if ($saveMedicalCategory) {
                $MedicalCategory = DB::table('diagnosis_type')->where(  "name" ,$request->name,)->first();
                return response()->json([
                    "status" => true,
                    "message" => "Diagnosis type saved successfully.",
                    "data" => $MedicalCategory
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
           "name"=> 'required'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {
            $data = [
                "name" => $request->name,
                "updated_at" => date("Y-m-d H:i:s"),
            ];
            $updateMedicalCategory = DB::table('diagnosis_type')->where('id', $request->id)->update($data);

            if ($updateMedicalCategory) {
                $selectQuery = DB::table('diagnosis_type')->where('id', $request->id)->first();

                return response()->json([
                    "status" => true,
                    "message" => "Diagnosis type updated successfully.",
                    "data" => $selectQuery
                ]);

            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Diagnosis updated failed",
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
            $deleteQuery = DB::table('diagnosis_type')->where('id', $request->id)->delete();

            if ($deleteQuery) {

                return response()->json([
                    "status" => true,
                    "message" => "Diagnosis type deleted successfully.",

                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Diagnosis delete failed.",
                    "data" => []

                ]);
            }

        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
        }
    }
}