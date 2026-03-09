<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class PatientMedicalReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getPatientMedicalReport(Request $request)
    {

        $getTreatmentPlan = DB::table('patient_medical_reports')
            ->select('patient_medical_reports.*', 'medical_category.name as category_name')
            ->join('medical_category', 'medical_category.id', '=', 'patient_medical_reports.category_id')
            ->where('patient_id', '=', $request->patient_id)
            ->get();
        if (!$getTreatmentPlan->isEmpty()) {
            return response()->json([
                "status" => true,
                "message" => "Fetch treatmentplan details.",
                "data" => $getTreatmentPlan
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
    public function savePatientMedicalReport(Request $request)
    {
        // if (!$request->hasFile('document')) {
        //     return response()->json(['upload_file_not_found'], 400);
        // }
        $documentName_path = "";
        if (!empty($request->document)) {
            $document_image = time() . '.' . $request->document->extension();
            $nes = $request->document->move(public_path('upload/document'), $document_image);
            $documentName_path = '/public/upload/document/' . $document_image;
        }

        $data = [
            "patient_id" => $request->patient_id,
            "category_id" => $request->category_id,
            "document" => $documentName_path,
            "created_at" => date("Y-m-d H:i:s"),
            "medical_document" => $request->medical_document ?? "",
            "updated_at" => null
        ];




        $saveTreatmentPlan = DB::table('patient_medical_reports')->insert($data);
        if ($saveTreatmentPlan) {

            $saveTreatmentPlan = DB::table('patient_medical_reports')->where('patient_id', $request->patient_id)->latest()->first();

            return response()->json([
                "status" => true,
                "message" => "Patient medical report save successfully.",
                "data" => $saveTreatmentPlan
            ]);
        } else {
            return response()->json([
                "status" => false,
                "message" => "Patient medical report save failed",
            ]);
        }
    }



    public function updatePatientMedicalReport(Request $request)
    {


        try {

            // if (!$request->hasFile('document')) {
            //     return response()->json(['upload_file_not_found'], 400);
            // }
          


            $documentName_path = "";
            if (!empty($request->document)) {
                if (Str::contains($request->document, 'https')) {
                    $documentName_path = $request->document;
                } else {
                    $document_image = time() . '.' . $request->document->extension();
                    $nes = $request->document->move(public_path('upload/document'), $document_image);
                    $documentName_path = '/public/upload/document/' . $document_image;
                }
            }
            if (empty($documentName_path)) {
              $documentName_path = "";
            }

            

            $data = [
                "id" => $request->id,
                "patient_id" => $request->patient_id,
                "category_id" => $request->category_id,
                "document" => $documentName_path,
                "created_at" => date("Y-m-d H:i:s"),

                "updated_at" => date("Y-m-d H:i:s"),
            ];
            $updateTreatmentPlan = DB::table('patient_medical_reports')->where('id', $request->id)->update($data);

            if ($updateTreatmentPlan) {
                $selectQuery = DB::table('patient_medical_reports')->where('id', $request->id)->first();

                return response()->json([
                    "status" => true,
                    "message" => "Patient medical report updated successfully.",
                    "data" => $selectQuery
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Patient medical report update failed",
                    "data" => []
                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
        }
    }

    public function deletePatientMedicalReport(Request $request)
    {
        try {
            $id = $request->id;
            $deleteQuery = DB::table('patient_medical_reports')->where('id', $id)->delete();

            if ($deleteQuery) {

                return response()->json([
                    "status" => true,
                    "message" => "Patient medical report deleted successfully.",

                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Patient medical report delete failed",
                    "data" => []

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


    //no needed
    public function update(Request $request)
    {
        $data = [
            "category_id" => $request->category_id,
            "subcategory_id" => $request->subcategory_id,
            "treatment_plan" => $request->treatment_plan,
            "treatment_plan_price" => $request->treatment_plan_price,
            "doctor_price" => $request->doctor_price,
            "updated_at" => date("Y-m-d H:i:s"),
        ];
        $updateTreatmentPlan = DB::table('treatmentplan')->where('id', $request->id)->update($data);

        if ($updateTreatmentPlan) {
            $selectQuery = DB::table('treatmentplan')->where('id', $request->id)->first();

            return response()->json([
                "status" => true,
                "message" => "Treatmentplan updated successfully.",
                "data" => $selectQuery
            ]);
        } else {
            return response()->json([
                "status" => false,
                "message" => "Treatmentplan updated failed",
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
                "message" => "Treatmentplan deleted successfully.",

            ]);
        } else {
            return response()->json([
                "status" => false,
                "message" => "Treatmentplan delete failed.",
                "data" => []

            ]);
        }
    }
}
