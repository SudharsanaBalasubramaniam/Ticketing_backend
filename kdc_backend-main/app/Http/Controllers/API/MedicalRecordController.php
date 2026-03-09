<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class MedicalRecordController extends Controller
{
    public function medical_record_list()
    {
        try {

            $getCategoryDetails = DB::table('medical_record')
            ->select('medical_record.*','members.first_name')
            ->join('members', 'members.id', '=', 'medical_record.patient_id')
            ->get();
         
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

    public function store_medical_record(Request $request)
    {


        try {
            $med_ran_id = '#MED - ' . rand();

            $data = [
                "med_ran_id" => $med_ran_id,
                "patient_id" => $request->patient_id,
                "astjma" => $request->astjma,
                "epilepsy" => $request->epilepsy,
                "diabets" => $request->diabets,
                "heart_problem" => $request->heart_problem,
                "blood_disease" => $request->blood_disease,
                "jaundice" => $request->jaundice,
                "others" => $request->others,
                // "other_discription"=> $request->other_discription,
                "pregnant" => $request->pregnant,
                "due_date" => $request->due_date,
                "alcohol" => $request->alcohol,
                "paan" => $request->paan,
                "tobacco" => $request->tobacco,
                "medication" => $request->medication,
                "aspirin" => $request->aspirin,
                "sulfa" => $request->sulfa,
                "local_aneshthetic" => $request->local_aneshthetic,
                "penicilin" => $request->penicilin,
                "ibuprofen" => $request->ibuprofen,
                "mention_others" => $request->mention_others,
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => null
            ];
            $saveCategory = DB::table('medical_record')->insert($data);
            if ($saveCategory) {
                $medical_record = DB::table('medical_record')->where('med_ran_id', $med_ran_id)->first();
                return response()->json([
                    "status" => true,
                    "message" => "medical record insert successfully.",
                    "data" => $medical_record
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "medical record insert failed",
                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
        }
    }
    public function update_medical_record(Request $request)
    {


        try {

            $data = [
             
                "patient_id" => $request->patient_id,
                "astjma" => $request->astjma,
                "epilepsy" => $request->epilepsy,
                "diabets" => $request->diabets,
                "heart_problem" => $request->heart_problem,
                "blood_disease" => $request->blood_disease,
                "jaundice" => $request->jaundice,
                "others" => $request->others,
                "other_discription"=> $request->other_discription,
                "pregnant" => $request->pregnant,
                "due_date" => $request->due_date,
                "alcohol" => $request->alcohol,
                "paan" => $request->paan,
                "tobacco" => $request->tobacco,
                "medication" => $request->medication,
                "aspirin" => $request->aspirin,
                "sulfa" => $request->sulfa,
                "local_aneshthetic" => $request->local_aneshthetic,
                "penicilin" => $request->penicilin,
                "ibuprofen" => $request->ibuprofen,
                "mention_others" => $request->mention_others,
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => null
            ];
            $saveCategory = DB::table('medical_record')->where('id', $request->id)->update($data);
            if ($saveCategory) {
                $medical_record = DB::table('medical_record')->where('id', $request->id)->first();
                return response()->json([
                    "status" => true,
                    "message" => "medical record updated successfully.",
                    "data" => $medical_record
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "medical record updated failed"
                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.','error'=> $e->getMessage()]);
        }
    }
    public function delete_medical_record(Request $request)
    {
        try {

            $getCategoryDetails = DB::table('medical_record')->where('id',$request->id)->delete();
          
         
            if ($getCategoryDetails) {
                return response()->json([
                    "status" => true,
                    "message" => "delete success"
                ]);

            } else {
                return response()->json([
                    "status" => false,
                    "message" => "delete failed"

                ]);

            }

        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.','error'=> $e->getMessage()]);
        }
    }


}
