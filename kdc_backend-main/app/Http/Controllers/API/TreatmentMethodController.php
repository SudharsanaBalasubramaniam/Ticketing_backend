<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Validator;

class TreatmentMethodController extends Controller
{


    public function store_treatment_method(Request $request)
    {


        $validator = Validator::make($request->all(), [
            "tooth_id" => 'required',
            "patient_id" => 'required',
            "doctor_id" => 'required',
            "method_id" => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {
            $appointment_id = '#AP - ' . rand();
            // $appointment_id == "" ? "" : $appointment_id,
            $data = [
                "appointment_id" => $appointment_id,
                "tooth_id" => $request->tooth_id,
                "patient_id" => $request->patient_id,
                "doctor_id" => $request->doctor_id,
                "method_id" => $request->method_id,
                "treatment_id" => $request->treatment_id,
                "appointment_date" => $request->appointment_date,
                "appointment_time" => $request->appointment_time,
                "appointment_status" => $request->appointment_status,
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s")
            ];

            $appointments = DB::table('appointments')->insert($data);

            if ($appointments) {

                $appointments_data = DB::table('appointments')->where('method_id', $request->method_id)->first();

                return response()->json([
                    "status" => true,
                    "message" => "Appointment details added successfully",
                    "data" => $appointments_data
                ]);

            } else {

                return response()->json([
                    "status" => false,
                    "message" => "Appointment details added failed",

                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
        }
    }






    public function treatment_method_mapping()
    {


        try {

            // $method_mapping = DB::table('appointments')
            //     ->select(
            //         // 'appointments.*',
            //         // 'diagnosis.*',
            //         // 'diagnosis_type.*',
            //         // 'category.*',
            //         // 'subcategory.*',
            //         // 'treatment_methods.*',




            //         'appointments.*',
            //         'diagnosis.name as diagnosis_name',
            //         'diagnosis.status as diagnosis_status',
            //         'diagnosis_type.name as diagnosis_type_name',
            //         'diagnosis_type.status as diagnosis_type_status',
            //         'category.category_name',
            //         'category.status as category_status',
            //         'subcategory.subcategory_name',
            //         'subcategory.price',
            //         'subcategory.status as subcategory_status',
            //         'treatment_methods.name as treatment_methods_name',
            //         'treatment_methods.status as treatment_methods_status'
            //     )
            //     ->join('users', 'users.id', '=', 'appointments.patient_id')
            //     ->join('users as doctor', 'doctor.id', '=', 'appointments.doctor_id')
            //     ->join('treatment_plan_method', 'treatment_plan_method.id', '=', 'appointments.method_id')
            //     ->join('treatment_methods', 'treatment_methods.id', '=', 'treatment_plan_method.treatment_method')
            //     ->join('treatment_plan_procedure', 'treatment_plan_procedure.id', '=', 'treatment_plan_method.treatment_procedure_id')
            //     ->join('subcategory', 'subcategory.id', '=', 'treatment_plan_procedure.treatment_procedure')
            //     ->join('treatmentplan', 'treatmentplan.id', '=', 'treatment_plan_procedure.treatment_plan_id')
            //     ->join('category', 'category.id', '=', 'treatmentplan.treatment_plan')
            //     ->join('diagnosis', 'diagnosis.id', '=', 'treatmentplan.diagnosis_id')
            //     ->join('diagnosis_type', 'diagnosis_type.id', '=', 'treatmentplan.diagnosis_type_id')
            //     // ->join('treatmentplan', 'treatmentplan.treatment_plan', '=', 'category.id')
            //     // ->join('treatment_plan_procedure', 'treatment_plan_procedure.treatment_procedure', '=', 'subcategory.id')
            //     // ->join('treatment_plan_method', 'treatment_plan_method.treatment_method', '=', 'treatment_methodS.id')
            //     ->get();
            $method_mapping = DB::table('treatmentplan')
            ->select(
                'treatmentplan.id',
                'treatmentplan.diagnosis_id',
                'treatmentplan.diagnosis_type_id',
                'treatmentplan.treatment_plan',
                'treatmentplan.created_at',
                'treatmentplan.updated_at',
                'treatment_plan_procedure.treatment_procedure',
                'treatment_plan_method.treatment_method as method_name',
                'treatment_plan_method.method_price'
            )
            ->Join('treatment_plan_procedure', 'treatment_plan_procedure.treatment_plan_id', '=', 'treatmentplan.id')
            ->Join('diagnosis_', 'diagnosis_.id', '=', 'treatmentplan.diagnosis_id')
            ->Join('diagnosis_type', 'diagnosis_type.id', '=', 'treatmentplan.diagnosis_type_id')
            ->Join('treatment_plan_method', 'treatment_plan_method.treatment_procedure_id', '=', 'treatment_plan_procedure.id')

            ->get();

            if (!$method_mapping->isEmpty()) {

                return response()->json([
                    "status" => true,
                    "message" => "method_mapping  list success",
                    "data" => $method_mapping
                ]);

            } else {

                return response()->json([
                    "status" => false,
                    "message" => "method_mapping list not available",

                ]);

            }


        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
        }
    }


    public function addrole(Request $request)
    {


        $validator = Validator::make($request->all(), [
            "role_name" => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {

            $data = [
                "role_name" => $request->role_name,
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s")
            ];

            $role = DB::table('role')->insert($data);

            if ($role) {

                $role = DB::table('role')->where('role_name', $request->role_name)->first();

                return response()->json([
                    "status" => true,
                    "message" => "roles added successfully",
                    "data" => $role
                ]);

            } else {

                return response()->json([
                    "status" => false,
                    "message" => "roles details added failed",

                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
        }
    }




    public function viewrole()
    {
        try {

            $role = DB::table('role')->get();

            if ($role) {


                return response()->json([
                    "status" => true,
                    "message" => "role details fetch successfully",
                    "data" => $role
                ]);

            } else {

                return response()->json([
                    "status" => false,
                    "message" => "role details fetch failed",

                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
        }
    }



    public function updaterole(Request $request)
    {


        $validator = Validator::make($request->all(), [
            "id" => 'required',
            "role_name" => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {
            $id = $request->id;
            $data = [
                "id" => $id,
                "role_name" => $request->role_name,
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s")
            ];

            $role = DB::table('role')->where('id', $id)->update($data);

            if ($role) {

                $role = DB::table('role')->where('id', $id)->first();

                return response()->json([
                    "status" => true,
                    "message" => "role details updated successfully",
                    "data" => $role
                ]);

            } else {

                return response()->json([
                    "status" => false,
                    "message" => "role details updated failed",

                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
        }
    }

    public function deleterole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id" => 'required',

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {

      
            $role = DB::table('role')->where('id', $request->id)->delete();



            if ($role) {

                $role = DB::table('role')->where('id', $request->id)->delete();

                return response()->json([
                    "status" => true,
                    "message" => "role details deleted successfully",
                    "data" => $role
                ]);

            } else {

                return response()->json([
                    "status" => false,
                    "message" => "role details deleted failed",

                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
        }
    }


}