<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Validator;

class PrescriptionSubCategoryController extends Controller
{


    public function index()
    {
        $getTreatment = DB::table('prescription_subcategory')
            // ->select('prescription_subcategory.*', 'pharmacy_categories.name as category_name')
            // ->join('pharmacy_categories', 'pharmacy_categories.id', '=', 'prescription_subcategory.category_id')
            ->get();



        if (!$getTreatment->isEmpty()) {
            return response()->json([
                "status" => true,
                "message" => "prescription sub-category listed successfully.",
                "data" => $getTreatment
            ]);

        } else {
            return response()->json([
                "status" => false,
                "message" => "No data available."

            ]);

        }
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "category_id" => 'required',
            "sub_name" => 'required',
            // "unit" => 'required',
            "price" => 'required',
            "available_stock" => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {
                            $pres_sub_name = DB::table('pharmacy_categories')->where("id", $request->category_id)->first();

            $data = [
                "category_name" => $pres_sub_name->name,
                "category_id" => $request->category_id,
                "sub_name" => $request->sub_name,
                "unit" => $request->unit,
                "price" => $request->price,
                "available_stock" => $request->available_stock,
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s")
            ];

            $pres_sub = DB::table('prescription_subcategory')->insert($data);
            if ($pres_sub) {
                $pres_sub = DB::table('prescription_subcategory')->where("sub_name", $request->sub_name)->first();
                return response()->json([
                    "status" => true,
                    "message" => "prescription sub-category saved successfully.",
                    "data" => $pres_sub
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "prescription sub-category save failed.",
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
        $validator = Validator::make($request->all(), [
            "id" => 'required'

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        try {


            $data = [
                "id" => $request->id,
                "category_name" => $request->category_name,
                "category_id" => $request->category_id,//pharmacy_category_id
                "sub_name" => $request->sub_name,
                "unit" => $request->unit,
                "price" => $request->price,
                "available_stock" => $request->available_stock,
                "updated_at" => date("Y-m-d H:i:s"),
            ];
            $updateMedicalCategory = DB::table('prescription_subcategory')->where('id', $request->id)->update($data);

            if ($updateMedicalCategory) {
                $selectQuery = DB::table('prescription_subcategory')->where('id', $request->id)->first();

                return response()->json([
                    "status" => true,
                    "message" => "Prescription sub-category updated successfully.",
                    "data" => $selectQuery
                ]);

            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Prescription sub-category updated failed",
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
            $deleteQuery = DB::table('prescription_subcategory')->where('id', $request->id)->delete();

            if ($deleteQuery) {

                return response()->json([
                    "status" => true,
                    "message" => "Prescription sub-category deleted successfully.",

                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Prescription sub-category delete failed.",
                    "data" => []

                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
        }
    }
}
