<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Auth;

class TreatmentPlanMappingController extends Controller
{







    public function plan_proc_method_mapping(Request $request)
    {
        // $getTreatment = DB::table('treatment_plan_method')
        //     ->select(
        //         'category.*',
        //         'subcategory.*',
        //         'treatment_methods.name as method_name',
        //         // 'treatmentplan.*',
        //         // 'treatment_plan_procedure.*',
        //         // 'treatment_plan_method.*'
        //     )
        //     ->rightJoin('treatment_methods', 'treatment_methods.id', '=', 'treatment_plan_method.treatment_method')
        //     ->join('treatment_plan_procedure', 'treatment_plan_procedure.id', '=', 'treatment_plan_method.treatment_procedure_id')

        //     ->join('treatmentplan', 'treatmentplan.id', '=', 'treatment_plan_procedure.treatment_plan_id')
        //     ->rightJoin('category', 'category.id', '=', 'treatmentplan.treatment_plan')
        //     ->rightJoin('subcategory', 'subcategory.category_id', '=', 'category.id')

        //     ->get();


        $getTreatment = DB::table('treatmentplan')
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
            ->leftJoin('treatment_plan_procedure', 'treatment_plan_procedure.treatment_plan_id', '=', 'treatmentplan.id')
            ->leftJoin('treatment_plan_method', 'treatment_plan_method.treatment_procedure_id', '=', 'treatment_plan_procedure.id')


            ->get();


        if (!$getTreatment->isEmpty()) {
            return response()->json([
                "status" => true,
                "message" => "treatment listed successfully.",
                "data" => $getTreatment
            ]);

        } else {
            return response()->json([
                "status" => false,
                "message" => "No data available."

            ]);

        }
    }


    
    public function store_treatment_plan_mapping(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'patient_treatment' => 'required',
            'patient_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        try {
            $treatment_id = '#TRE - ' . rand();
            $patient_treatment = $request->patient_treatment;
            
            $insertedData = [];
            foreach ($patient_treatment as $treatment) {
                $data = [
                    "treatment_id" => $treatment_id,
                    "patient_treatment" => json_encode($treatment), //summary data
                    "patient_id" => $request->patient_id,
                    "doctor_id" => $request->doctor_id,
                    "status" => 'waiting',
                    "created_at" => date("Y-m-d H:i:s"),
                    "updated_at" => date("Y-m-d H:i:s"),
                ];
                
                $insert = DB::table('treatment_plan_mapping')->insert($data);
            
                if ($insert) {
                    $insertedData[] = $data;
                }
            }
            
            if (!empty($insertedData)) {
                
                return response()->json([
                    "status" => true,
                    "message" => "Treatment plan mapping success",
                    "data" => $insertedData
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Treatment plan mapping failed",
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Something went wrong.', "error" => $e->getMessage()]);
        }
    }

    public function update_treatment_plan_mapping(Request $request)
    
        {

                $validator = Validator::make($request->all(), [
                    'id'=> 'required',
                    'patient_treatment' => 'required',
                    'patient_id' => 'required',
        
        
                ]);
                if ($validator->fails()) {
                    return response()->json($validator->errors());
                }
        
                try {                    
             
                        $patient_treatment = json_encode($request->patient_treatment);
                    $data =
                        [
                            "patient_treatment" => $patient_treatment, //summary data
                            "patient_id" => $request->patient_id,
                            "doctor_id" => $request->doctor_id,
                            "status" => $request->status,
                            "created_at" => date("Y-m-d H:i:s"),
                            "updated_at" => date("Y-m-d H:i:s"),
                        ];
        
        
                    $insert = DB::table('treatment_plan_mapping')->where('id', $request->id)->update($data);
        
                    if ($insert) {
                        $fetchdata = DB::table('treatment_plan_mapping')->where('id', $request->id)->first();
        
                        return response()->json([
                            "status" => true,
                            "message" => "Treatment plan mapping updated success",
                            "data" => $fetchdata
                        ]);
                    } else {
                        return response()->json([
                            "status" => false,
                            "message" => "Treatment plan mapping updated failed",
                        ]);
                    }
                } catch (\Exception $e) {
        
                    \Log::error('Exception occurred: ' . $e->getMessage());
                    return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.',"error"=>$e->getMessage()]);
                }
    }

    public function treatment_plan_mapping_list()
    {

        $getPrescriptionCategory = DB::table('treatment_plan_mapping')->get();

        if (!$getPrescriptionCategory->isEmpty()) {
            return response()->json([
                "status" => true,
                "message" => "Treatment plan mapping success.",
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

    public function delete_treatment_plan_mapping(Request $request)
    {
        try {
        $getPrescriptionCategory = DB::table('treatment_plan_mapping')->where('id', $request->id)->delete();

        if ($getPrescriptionCategory) {
            return response()->json([
                "status" => true,
                "message" => "Treatment plan mapping deleted success.",
                "data" => $getPrescriptionCategory
            ]);

        } else {
            return response()->json([
                "status" => false,
                "message" => "delete failed.",
                "data" => []

            ]);

        }
    } catch (\Exception $e) {
        
        \Log::error('Exception occurred: ' . $e->getMessage());
        return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.',"error"=>$e->getMessage()]);
    }
    }


    // public function treatment_plan_mapping_list1()
    // {

    //     $getPrescriptionCategory = DB::table('treatment_plan_mapping')

    //         ->select(
    //             'treatment_plan_mapping.id',
    //             'appointments.appointment_date',
    //             'appointments.appointment_time',
    //             'appointments.appointment_status',
    //             'appointments.doctor_id',
    //             'users.first_name',
    //             'treatment_plan_mapping.*',

    //         )
    //         ->join('appointments', 'appointments.patient_id', '=', 'treatment_plan_mapping.patient_id')
    //         ->join('users', 'users.id', '=', 'appointments.doctor_id')
    //         ->where('appointment_status', '!=', "Rescheduled")
    //         ->get();

    //     if (!$getPrescriptionCategory->isEmpty()) {
    //         return response()->json([
    //             "status" => true,
    //             "message" => "Fetch Prescription Category details.",
    //             "data" => $getPrescriptionCategory
    //         ]);

    //     } else {
    //         return response()->json([
    //             "status" => false,
    //             "message" => "No data available.",
    //             "data" => []

    //         ]);

    //     }
    // }


    // public function store_treatment_plan_mapping(Request $request)
    // {

    //     $validator = Validator::make($request->all(), [
    //         'diagnosis_type' => 'required',
    //         'diagnosis_name' => 'required',
    //         'tooth_number' =>'required',
    //         'category' => 'required',
    //         'subcategory' => 'required',
    //         'method' => 'required',
    //         'price' => 'required',
    //         'patient_id' => 'required',
    //         'doctor_id' => 'required',

    //     ]);
    //    if ($validator->fails()) {
    //         return response()->json($validator->errors());
    //     }

    //     try{

    //          $doctor_id = Auth::user()->id;
    //          $treatment_id =  '#TR - '. rand();
    //         $data =
    //             [
    //                 "treatment_id" => $treatment_id,
    //                 "diagnosis_type" => $request->diagnosis_type,
    //                 "diagnosis_name" => $request->diagnosis_name,
    //                 "tooth_number" => $request->tooth_number,
    //                 "category" => $request->category,
    //                 "subcategory" => $request->subcategory,
    //                 "method" => $request->method,
    //                 "price" => $request->price,
    //                 "patient_id" => $request->patient_id,
    //                 "doctor_id" => $doctor_id,
    //                 "created_at" => date("Y-m-d H:i:s"),
    //                 "updated_at" => date("Y-m-d H:i:s"),
    //             ];


    //         $insert = DB::table('treatment_plan_mapping')->insert($data);

    //         if ($insert){
    //             $fetchdata = DB::table('treatment_plan_mapping')->where('treatment_id',$treatment_id)->first();

    //             return response()->json([
    //                 "status" => true,
    //                 "message" => "Treatment plan mapping success",
    //                 "data" => $fetchdata
    //             ]);
    //         } else {
    //             return response()->json([
    //                 "status" => false,
    //                 "message" => "Treatment plan mapping failed",
    //             ]);
    //         }
    //     } catch (\Exception $e) {

    //         \Log::error('Exception occurred: ' . $e->getMessage());
    //         return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
    //     }
    // }


    //     public function treatment_plan_mapping_list()
//   {

    //     try {

    //       $doctor_id = Auth::user()->id;
//       $treatment_plan_mapping_list = DB::table('treatment_plan_mapping')
//         ->select(
//           'treatment_plan_mapping.*',
//           'diagnosis.name as diagnosis',
//           'diagnosis_type.name as diagnosis_type_name',
//           'users.first_name as patient_name',
//           'doctor.first_name as doctor_name',
//         )
//         ->join('diagnosis', 'diagnosis.id', '=', 'treatment_plan_mapping.diagnosis_name')
//         ->join('diagnosis_type', 'diagnosis_type.id', '=', 'treatment_plan_mapping.diagnosis_type')
//         ->join('users', 'users.id', '=', 'treatment_plan_mapping.patient_id')
//         ->join('users as doctor', 'doctor.id', '=', 'treatment_plan_mapping.doctor_id')
//         ->where('doctor.id', $doctor_id)
//         ->get();

    //       if (!$treatment_plan_mapping_list->isEmpty()) {

    //         return response()->json([
//           "status" => true,
//           "message" => "treatment plan mapping list success",
//           "data" => $treatment_plan_mapping_list
//         ]);
//       } else {
//         return response()->json([
//           "status" => false,
//           "message" => "treatment plan mapping list failed",
//         ]);
//       }
//     } catch (\Exception $e) {

    //       \Log::error('Exception occurred: ' . $e->getMessage());
//       return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
//     }
//   }

}