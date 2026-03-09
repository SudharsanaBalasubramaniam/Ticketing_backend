<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SlotController extends Controller
{
    public function slot_list()
    {
        try {
            $getCategoryDetails = DB::table('slot')->get();
            if (!$getCategoryDetails->isEmpty()) {
                return response()->json([
                    "status" => true,
                    "message" => "Fetch slot details.",
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

    
 public function slot_store(Request $request){


    $validator = Validator::make($request->all(), [
        'doctor_id' => 'required',
        'work_time' => 'required',


    ]);
    if ($validator->fails()) {
        return response()->json($validator->errors());
    }

    try {

        $data =
            [
         
                "doctor_id" => $request->doctor_id,
                "work_time" => $request->work_time,
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s"),
            ];


        $insert = DB::table('slot')->insert($data);

        if ($insert) {
            $fetchdata = DB::table('slot')->where('doctor_id', $request->doctor_id)->first();

            return response()->json([
                "status" => true,
                "message" => "doctor slot success",
                "data" => $fetchdata
            ]);
        } else {
            return response()->json([
                "status" => false,
                "message" => "doctor slot failed",
            ]);
        }
    } catch (\Exception $e) {

        \Log::error('Exception occurred: ' . $e->getMessage());
        return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
    }
}

}