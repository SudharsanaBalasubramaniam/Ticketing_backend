<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use DB;
use Carbon\Carbon;
use Validator;

class AppointmentsController extends Controller
{

    public function update_appointments(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "id" => 'required',
            "appointment_id" => 'required',
            "patient_id" => 'required',
            "doctor_id" => 'required',
            "treatment_id" => 'required',
            "appointment_date" => 'required',
            "appointment_time" => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        try {


            $data = [
                "tooth_id" => $request->tooth_id,
                "patient_id" => $request->patient_id,
                "doctor_id" => $request->doctor_id,
                "method_id" => $request->method_id,
                "treatment_id" => $request->treatment_id,
                "treatment_name" => $request->treatment_name,
                "appointment_date" => $request->appointment_date,
                "appointment_time" => $request->appointment_time,
                "appointment_status" => "Open",
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s")
            ];


            $update = DB::table('appointments')->where('appointment_id', $request->appointment_id)->update($data);



            if ($update) {

                $user = DB::table('appointments')->where('appointment_id', $request->appointment_id)->first();

                return response()->json([
                    "status" => true,
                    "message" => "User status updated success",
                    "data" => $user
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "User status updated failed",
                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
        }
    }
    public function delete_appointments(Request $request)
    {

        $delete = DB::table('members')->where('id', $request->id)->delete();

        if ($delete) {

            return response()->json([
                "status" => True,
                "message" => "Delete Successfully",

            ]);
        } else {
            return response()->json([
                "status" => false,
                "message" => "User status updated failed",
            ]);
        }
    }
    public function treatment_list()
    {

        try {
            $treatments = DB::table('treatmentplan')->get();

            if (!$treatments->isEmpty()) {

                return response()->json([
                    "status" => true,
                    "message" => "Treatments  list success",
                    "data" => $treatments
                ]);
            } else {


                return response()->json([
                    "status" => false,
                    "message" => "Treatments list not available",

                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
        }
    }
    public function appointment_details()
    {
        try {
            $appointments = DB::table('appointments')
                ->select(
                    DB::raw('DISTINCT appointments.id'),
                    'appointments.appointment_id',
                    'members.first_name as patient_first_name',
                    'members.surname as patient_sur_name',
                    'doctor.first_name as doctor_first_name',
                    'doctor.surname as doctor_sur_name',
                    'treatment_plan_mapping.patient_treatment',
                    'appointments.method_id',
                    'appointments.treatment_id',
                    'appointments.treatment_name',
                    'appointments.appointment_date',
                    'appointments.appointment_time',
                    'appointments.appointment_status',
                    'appointments.created_at',
                    'appointments.updated_at'
                )
                ->join('members', 'members.id', '=', 'appointments.patient_id')
                ->join('users as doctor', 'doctor.id', '=', 'appointments.doctor_id')

                ->join('treatment_plan_mapping', 'treatment_plan_mapping.id', '=', 'appointments.treatment_id')
                ->where('appointment_status', '!=', "Rescheduled")
                ->orderBy('appointments.id', 'DESC')
                ->get();
            if (!$appointments->isEmpty()) {
                return response()->json([
                    "status" => true,
                    "message" => "Appointments list success",
                    "data" => $appointments
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Appointments list not available",
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Something went wrong.']);
        }
    }
    public function particular_appointment_list(Request $request)
    {


        $validator = Validator::make($request->all(), [
            "id" => 'required',

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        try {
            $appointments = DB::table('appointments')->where('id', $request->id)->get();

            if (!$appointments->isEmpty()) {

                return response()->json([
                    "status" => true,
                    "message" => "Appointments  details success",
                    "data" => $appointments
                ]);
            } else {


                return response()->json([
                    "status" => false,
                    "message" => "Appointments details not available",

                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
        }
    }
    // public function appointment_list(Request $request)
    // {

    //     try {
    //         $appointments = DB::table('appointments')
    //             ->select(
    //                 DB::raw('DISTINCT appointments.id'),
    //                 'appointments.appointment_id',
    //                 'members.first_name as patient_first_name',
    //                 'members.surname as patient_sur_name',
    //                 'doctor.first_name as doctor_first_name',
    //                 'doctor.surname as doctor_sur_name',
    //                 'treatment_plan_mapping.patient_treatment',
    //                 // 'treatment_plan_method.method_price',
    //                 'appointments.patient_id',
    //                 'appointments.doctor_id',
    //                 'appointments.tooth_id',
    //                 'appointments.method_id',
    //                 'appointments.treatment_id',
    //                 'appointments.service_id',
    //                 'appointments.treatment_name',
    //                 'appointments.appointment_date',
    //                 'appointments.appointment_time',
    //                 'appointments.appointment_status',
    //                 'appointments.created_at',
    //                 'appointments.updated_at'
    //             )
    //             ->join('members', 'members.id', '=', 'appointments.patient_id')
    //             ->join('users as doctor', 'doctor.id', '=', 'appointments.doctor_id')
    //             // ->join('treatment_plan_method', 'treatment_plan_method.id', '=', 'appointments.method_id')
    //             ->join('treatment_plan_mapping', 'treatment_plan_mapping.id', '=', 'appointments.service_id')
    //             ->get();

    //         // if (!empty($appointments)) {
    //         //     foreach ($appointments as $notify) {
    //         //         $service = DB::table('treatment_plan_mapping')->where("id", $notify->service_id)->first();
    //         //         $notify->service_details = $service;
    //         //     }
    //         // }

    //         if (!$appointments->isEmpty()) {

    //             return response()->json([
    //                 "status" => true,
    //                 "message" => "Appointments  details success",
    //                 "data" => $appointments
    //             ]);
    //         } else {


    //             return response()->json([
    //                 "status" => false,
    //                 "message" => "Appointments details not available",

    //             ]);
    //         }
    //     } catch (\Exception $e) {

    //         \Log::error('Exception occurred: ' . $e->getMessage());
    //         return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.', 'error' => $e->getMessage()]);
    //     }
    // }

    public function appointment_list(Request $request)
    {

        try {
            $appointments = DB::table('appointments')
                ->select(
                    DB::raw('DISTINCT appointments.id'),
                    'appointments.appointment_id',
                    'members.first_name as patient_first_name',
                    'members.surname as patient_sur_name',
                    'doctor.first_name as doctor_first_name',
                    'doctor.surname as doctor_sur_name',
                    'treatment_plan_mapping.patient_treatment',
                    // 'treatment_plan_method.method_price',
                    'appointments.patient_id',
                    'appointments.doctor_id',
                    'appointments.tooth_id',
                    'appointments.method_id',
                    'appointments.treatment_id',
                    'appointments.service_id',
                    'appointments.treatment_name',
                    'appointments.appointment_date',
                    'appointments.appointment_time',
                    'appointments.appointment_status',
                    'appointments.inprogress_date',
                    'appointments.complete_date',
                    'appointments.created_at',
                    'appointments.updated_at'
                )
                ->join('members', 'members.id', '=', 'appointments.patient_id')
                ->join('users as doctor', 'doctor.id', '=', 'appointments.doctor_id')
                ->join('treatment_plan_mapping', 'treatment_plan_mapping.id', '=', 'appointments.service_id')
                ->get();


            if (!$appointments->isEmpty()) {

                return response()->json([
                    "status" => true,
                    "message" => "Appointments  details success",
                    "data" => $appointments
                ]);
            } else {


                return response()->json([
                    "status" => false,
                    "message" => "Appointments details not available",

                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.', 'error' => $e->getMessage()]);
        }
    }

    public function patient_appointment_list(Request $request)
    {

        try {
            $patientId = request()->input('patient_id');

            $appointments = DB::table('appointments')
                ->select(
                    DB::raw('DISTINCT appointments.id'),
                    'appointments.appointment_id',
                    'members.first_name as patient_first_name',
                    'members.surname as patient_sur_name',
                    'doctor.first_name as doctor_first_name',
                    'doctor.surname as doctor_sur_name',
                    'treatment_plan_mapping.patient_treatment',
                    'appointments.patient_id',
                    'appointments.doctor_id',
                    'appointments.tooth_id',
                    'appointments.method_id',
                    'appointments.treatment_id',
                    'appointments.service_id',
                    'appointments.treatment_name',
                    'appointments.appointment_date',
                    'appointments.appointment_time',
                    'appointments.appointment_status',
                    'appointments.inprogress_date',
                    'appointments.complete_date',
                    'appointments.created_at',
                    'appointments.updated_at'
                )
                ->join('members', 'members.id', '=', 'appointments.patient_id')
                ->join('users as doctor', 'doctor.id', '=', 'appointments.doctor_id')
                ->join('treatment_plan_mapping', 'treatment_plan_mapping.id', '=', 'appointments.service_id')
                ->where('appointments.patient_id', '=', $patientId)
                ->orderBy('appointments.service_id')
                ->orderBy('appointments.id', 'ASC')
                ->get();

            // Show ALL appointments with summaries up to that appointment
            foreach ($appointments as $appointment) {
                $serviceId = $appointment->service_id;
                $appointmentId = $appointment->id;
                
                // Check if there's ANY appointment after this one in the treatment chain
                // This keeps Update button visible until treatment is fully completed
                $hasNextAppointment = DB::table('appointments')
                    ->where('service_id', $serviceId)
                    ->where('patient_id', $patientId)
                    ->where('created_at', '>', $appointment->created_at)
                    ->exists();
                
                $appointment->has_next_appointment = $hasNextAppointment;
                
                // Get summaries only up to this appointment (by created_at)
                $appointment->summaries = DB::table('appointment_summary')
                    ->leftJoin('treatment_method_stages', 'treatment_method_stages.id', '=', 'appointment_summary.treatment_stage_id')
                    ->whereIn('appointment_summary.appointment_id', function($query) use ($serviceId, $patientId, $appointmentId) {
                        $query->select('id')
                            ->from('appointments')
                            ->where('service_id', $serviceId)
                            ->where('patient_id', $patientId)
                            ->where('id', '<=', $appointmentId);
                    })
                    ->select(
                        'appointment_summary.id',
                        'appointment_summary.summary',
                        'appointment_summary.treatment_stage_id',
                        'appointment_summary.created_at',
                        'treatment_method_stages.stage'
                    )
                    ->orderBy('appointment_summary.created_at', 'ASC')
                    ->get();
            }

            if (!$appointments->isEmpty()) {

                return response()->json([
                    "status" => true,
                    "message" => "Appointments  details success",
                    "data" => $appointments
                ]);
            } else {


                return response()->json([
                    "status" => false,
                    "message" => "Appointments details not available",

                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.', 'error' => $e->getMessage()]);
        }
    }


    public function store_appointment(Request $request)
    {


        $validator = Validator::make($request->all(), [
            "patient_id" => 'required',
            "doctor_id" => 'required',
            "treatment_id" => 'required',
            "service_id" => 'required',
            "appointment_date" => 'required',
            "appointment_time" => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {
            $appointment_id = '#AP - ' . rand();

            $data = [
                "appointment_id" => $appointment_id,
                "tooth_id" => $request->tooth_id,
                "patient_id" => $request->patient_id,
                "doctor_id" => $request->doctor_id,
                "method_id" => $request->method_id,
                "treatment_id" => $request->treatment_id, //#TRE- JSON
                "service_id" => $request->service_id, //MAPPING ID
                "treatment_name" => $request->treatment_name,
                "appointment_date" => $request->appointment_date,
                "appointment_time" => $request->appointment_time,
                "appointment_status" => "Open",
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s")
            ];
            $appointment_is_exist = DB::table('appointments')->where('treatment_id', $request->treatment_id)->where('service_id', $request->service_id)->first();


            if (empty($appointment_is_exist)) {
                DB::table('appointments')->insert($data);
                DB::table('treatment_plan_mapping')->where('id', $request->service_id)->update(['status' => 'booked']);
                $appointment_is_exist = DB::table('appointments')->where('treatment_id', $request->treatment_id)->where('service_id', $request->service_id)->first();

                return response()->json([
                    "status" => true,
                    "message" => "Appointment details added successfully",
                    "data" => $appointment_is_exist
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Appointment Already created.",
                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Something went wrong.', "error" => $e->getMessage()]);
        }
    }
    // $treatment = DB::table('treatment_plan_mapping')->where("id",$request->treatment_id)->first();
    // $planList = json_decode($treatment->patient_treatment);
    // $planList[$request->index_id]->is_booked = true;
    // $encoded = json_encode($planList);
    // $appointments = DB::table('treatment_plan_mapping')->where('id', $request->treatment_id)->update(['patient_treatment' => $encoded]);


    public function appointment_custom_date(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "date" => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        try {

            $appointments = DB::table('appointments')
                ->select(
                    DB::raw('DISTINCT appointments.id'),
                    'appointments.appointment_id',
                    'users.first_name as patient_first_name',
                    'users.surname as patient_sur_name',
                    'doctor.first_name as doctor_first_name',
                    'doctor.surname as doctor_sur_name',
                    'treatment_plan_mapping.patient_treatment',
                    'appointments.method_id',
                    'appointments.treatment_id',
                    'appointments.service_id',
                    'appointments.treatment_name',
                    'appointments.appointment_date',
                    'appointments.appointment_time',
                    'appointments.appointment_status',
                    'appointments.created_at',
                    'appointments.updated_at'
                )

                ->join('members as users', 'users.id', '=', 'appointments.patient_id')
                ->join('users as doctor', 'doctor.id', '=', 'appointments.doctor_id')
                ->join('treatment_plan_mapping', 'treatment_plan_mapping.id', '=', 'appointments.service_id')
                ->where('appointment_status', '!=', "Rescheduled")
                ->where('appointments.appointment_date', $request->date)
                ->get();


            if (!$appointments->isEmpty()) {

                return response()->json([
                    "status" => true,
                    "message" => "Appointments details success",
                    "data" => $appointments
                ]);
            } else {


                return response()->json([
                    "status" => false,
                    "message" => "Appointments details not available",
                    "data" => []
                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.', 'error' => $e->getMessage()]);
        }
    }
    public function reschedule_appointment(Request $request)
    {


        $validator = Validator::make($request->all(), [
            "id" => 'required',
            "patient_id" => 'required',
            "doctor_id" => 'required',
            "treatment_id" => 'required',
            "appointment_date" => 'required',
            "appointment_time" => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {
            $appointments_table = DB::table('appointments')->where('service_id', $request->service_id)->first();
            if ($appointments_table && $appointments_table->appointment_status == "Rescheduled") {
                return response()->json([
                    "status" => false,
                    "message" => "This appointment is already rescheduled!",
                    "data" => []
                ]);
            }
            $reschedule_appointments = DB::table('appointments')->where('id', $request->id)->first();

            // $appointment_id = 'AP - ' . date('Y-m-d H:m:s');

            $data = [
                // "appointment_id" => $appointment_id,
                "appointment_id" => $reschedule_appointments->appointment_id,
                "tooth_id" => $reschedule_appointments->tooth_id,
                "patient_id" => $reschedule_appointments->patient_id,
                "doctor_id" => $request->doctor_id,
                "method_id" => $reschedule_appointments->method_id,
                "treatment_id" => $reschedule_appointments->treatment_id,
                "service_id" => $reschedule_appointments->service_id,
                "treatment_name" => $reschedule_appointments->treatment_name,
                "appointment_date" => $request->appointment_date,
                "appointment_time" => $request->appointment_time,
                "appointment_status" => "Open",
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s")
            ];
            $appointments = DB::table('appointments')->insert($data);
            if ($appointments) {

                $update_status = ["appointment_status" => "Rescheduled"];
                $appointments_update = DB::table('appointments')->where('id', $request->id)->update($update_status);

                $appointments_data = DB::table('appointments')
                    ->select(
                        DB::raw('DISTINCT appointments.id'),
                        'appointments.appointment_id',
                        'users.first_name as patient_first_name',
                        'users.surname as patient_sur_name',
                        'doctor.first_name as doctor_first_name',
                        'doctor.surname as doctor_sur_name',
                        'treatment_plan_mapping.patient_treatment',
                        'appointments.patient_id',
                        'appointments.doctor_id',
                        'appointments.tooth_id',
                        'appointments.method_id',
                        'appointments.treatment_id',
                        'appointments.service_id',
                        'appointments.treatment_name',
                        'appointments.appointment_date',
                        'appointments.appointment_time',
                        'appointments.appointment_status',
                        'appointments.created_at',
                        'appointments.updated_at'
                    )
                    ->join('members as users', 'users.id', '=', 'appointments.patient_id')
                    ->join('users as doctor', 'doctor.id', '=', 'appointments.doctor_id')
                    ->join('treatment_plan_mapping', 'treatment_plan_mapping.id', '=', 'appointments.service_id')
                    ->where('appointment_id', $reschedule_appointments->appointment_id)->first();

                return response()->json([
                    "status" => true,
                    "message" => "Rescheduled appointment successfully",
                    "data" => $appointments_data
                ]);
            } else {

                return response()->json([
                    "status" => false,
                    "message" => "Appointment details added failed",
                    "data" => []
                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.', "error" => $e->getMessage()]);
        }
    }

    public function appointment_summary(Request $request)
    {
        try {
            // Validate incoming request
            $validated = $request->validate([
                'appointment_id' => 'required|integer|exists:appointments,id',
                'summary' => 'nullable|string',
                'treatment_stage_id' => 'nullable|integer|exists:treatment_method_stages,id',
                'appointment_status' => 'required'
            ]);

            $data = [
                "appointment_status" => $request->appointment_status,
                "inprogress_date" => $request->inprogress_date,
                "complete_date" => $request->complete_date,
                "updated_at" => date("Y-m-d")
            ];
            $update_appointment_status = DB::table('appointments')->where('id', $request->appointment_id)->update($data);

            $hasSummary = isset($validated['summary']) && trim($validated['summary']) !== '';
            $hasStageId = isset($validated['treatment_stage_id']) && $validated['treatment_stage_id'] !== null;

            if ($hasSummary || $hasStageId) {
                // Check if a summary already exists for this appointment on the same day
                $existing = DB::table('appointment_summary')
                    ->whereDate('created_at', Carbon::today())
                    ->where('appointment_id', $validated['appointment_id'])
                    ->first();

                $data = [
                    'appointment_id'     => $validated['appointment_id'],
                    'summary'            => $validated['summary'] ?? null,
                    'treatment_stage_id' => $validated['treatment_stage_id'] ?? null,
                    'updated_at'         => now(),
                ];

                if ($existing) {
                    // Update existing summary
                    DB::table('appointment_summary')
                        ->where('id', $existing->id)
                        ->update($data);

                    return response()->json([
                        'status' => true,
                        'message' => 'Appointment summary updated for today.',
                        'id' => $existing->id
                    ]);
                } else {
                    // Insert new summary
                    $data['created_at'] = now();
                    $id = DB::table('appointment_summary')->insertGetId($data);

                    return response()->json([
                        'status' => true,
                        'message' => 'Appointment summary inserted successfully.',
                        'id' => $id
                    ]);
                }
            } else {
                if ($update_appointment_status) {
                    return response()->json([
                        'status' => true,
                        'message' => 'Appointment status updated successfully!'
                    ]);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.', "error" => $e->getMessage()]);
        }
    }

    public function upcoming_appointments()
    {

        try {

            $curr_date = date('Y-m-d');
            $next_day = date('Y-m-d H:i:s', strtotime('+1 days'));
            // return $curr_date." - ".$next_day;
            // exit;

            // $next_three_days = date('Y-m-d',strtotime('+3 days'));

            // $appointments = DB::table('appointments')->where('appointment_status','!=','Rescheduled')
            // ->whereDate('appointment_date', '>=',$curr_date)
            // ->whereDate('appointment_date', '<=',$next_three_days)->get();

            $appointments = DB::table('appointments')
                ->select(
                    DB::raw('DISTINCT appointments.id as appointment_primary_id'),
                    'appointments.appointment_id',
                    'users.id',
                    'users.registration_number',
                    'users.employee_number',
                    'users.patient_reg_no',
                    'users.role_id',
                    'users.first_name',
                    'users.surname',
                    'users.email',
                    'users.phone',
                    'users.contact_mobile',
                    'users.dob',
                    'users.age',
                    'users.gender',
                    'users.image',
                    'users.address',
                    'users.profession',
                    'users.primary_contact_name',
                    'users.contact_mobile',
                    'users.relationship',
                    'users.check1',
                    'users.check2',
                    'users.check3',
                    'users.check4',
                    'users.check5',
                    'users.status',
                    'doctor.first_name as doctor_first_name',
                    'doctor.surname as doctor_sur_name',
                    'treatment_plan_mapping.patient_treatment',
                    'appointments.method_id',
                    'appointments.treatment_id',
                    'appointments.patient_id as user_id',
                    'appointments.service_id',
                    'appointments.treatment_name',
                    'appointments.appointment_date',
                    'appointments.appointment_time',
                    'appointments.appointment_status',
                    'appointments.created_at',
                    'appointments.updated_at'
                )
                ->join('members as users', 'users.id', '=', 'appointments.patient_id')
                ->join('users as doctor', 'doctor.id', '=', 'appointments.doctor_id')
                ->join('treatment_plan_mapping', 'treatment_plan_mapping.id', '=', 'appointments.service_id')
                ->where('appointment_status', '!=', "Rescheduled")
                ->where('appointment_status', '!=', "Cancel")
                ->whereDate('appointment_date', '>=', $curr_date)
                ->whereDate('appointment_date', '<=', $next_day)
                ->orderBy('appointments.id', 'DESC')
                ->get();



            if (!$appointments->isEmpty()) {

                return response()->json([
                    "status" => true,
                    "message" => "Upcoming appointments details successfully!",
                    "data" => $appointments
                ]);
            } else {

                return response()->json([
                    "status" => false,
                    "message" => "Upcoming appointments details failed!",
                    "data" => []
                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.', "error" => $e->getMessage()]);
        }
    }


    // public function appointment_status_update(Request $request)
    // {

    //     $validator = Validator::make($request->all(), [
    //         'id' => 'required',
    //         'appointment_status' => 'required'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json($validator->errors(), 422);
    //     }

    //     try {
    //         $data = [
    //             "appointment_status" => $request->appointment_status,
    //             "updated_at" => date("Y-m-d")
    //         ];
    //         $update_appointment_status = DB::table('appointments')->where('id', $request->id)->update($data);

    //         if ($update_appointment_status) {
    //             $appointment = DB::table('appointments')->where('id', $request->id)->first();

    //             return response()->json([
    //                 "status" => true,
    //                 "message" => "Appointment status updated successfully!",
    //                 "data" => $appointment
    //             ]);
    //         } else {

    //             return response()->json([
    //                 "status" => false,
    //                 "message" => "Appointment status updated failed!",
    //                 "data" => []
    //             ]);
    //         }
    //     } catch (\Exception $e) {

    //         \Log::error('Exception occurred: ' . $e->getMessage());
    //         return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
    //     }
    // }

    public function appointment_status_update(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'appointment_status' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $data = [
                "appointment_status" => $request->appointment_status,
                "inprogress_date" => $request->inprogress_date,
                "complete_date" => $request->complete_date,
                "updated_at" => date("Y-m-d")
            ];
            $update_appointment_status = DB::table('appointments')->where('id', $request->id)->update($data);

            if (!empty($update_appointment_status)) {
                $appointment_status = DB::table('appointments')->where('id', $request->id)->first();
                return response()->json([
                    "status" => true,
                    "message" => "Appointment status updated successfully!",
                    "data" => $appointment_status,
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Appointment status updated failed!",
                    "data" => []
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
        }
    }


    public function ongoing_appointments()
    {


        try {

            $appointments = DB::table('appointments')
                ->select('appointments.id', 'appointments.appointment_id', 'users.first_name', 'users.surname', 'doctor.first_name as doctor_first_name', 'doctor.surname as doctor_sur_name', 'appointments.appointment_date', 'appointments.appointment_time', 'appointments.appointment_status', 'appointments.created_at', 'appointments.updated_at')
                ->join('members as users', 'users.id', '=', 'appointments.patient_id')
                ->join('users as doctor', 'doctor.id', '=', 'appointments.doctor_id')
                ->where('appointment_status', '=', "Inprogress")
                ->orderBy('appointments.id', 'DESC')
                ->get();


            if (!$appointments->isEmpty()) {

                return response()->json([
                    "status" => true,
                    "message" => "Appointments details success",
                    "data" => $appointments
                ]);
            } else {


                return response()->json([
                    "status" => false,
                    "message" => "Appointments details not availavle",

                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
        }
    }

    public function complete_appointments()
    {

        try {

            $appointments = DB::table('appointments')
                ->select('appointments.id', 'appointments.appointment_id', 'users.first_name', 'users.surname', 'doctor.first_name as doctor_first_name', 'doctor.surname as doctor_sur_name', 'appointments.appointment_date', 'appointments.appointment_time', 'appointments.appointment_status', 'appointments.created_at', 'appointments.updated_at')
                ->join('members as users', 'users.id', '=', 'appointments.patient_id')
                ->join('users as doctor', 'doctor.id', '=', 'appointments.doctor_id')
                ->where('appointment_status', '=', "Completed")
                ->orderBy('appointments.id', 'DESC')
                ->get();


            if (!$appointments->isEmpty()) {

                return response()->json([
                    "status" => true,
                    "message" => "Completed appointments details success",
                    "data" => $appointments
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Completed appointments details not available",
                    "data" => []
                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
        }
    }

    public function upcoming_appointments_v2(Request $request)
    {
        try {
            $appointments = DB::table('appointments')
                ->select(
                    DB::raw('DISTINCT appointments.id as appointment_primary_id'),
                    'appointments.appointment_id',
                    'users.id',
                    'users.registration_number',
                    'users.employee_number',
                    'users.patient_reg_no',
                    'users.role_id',
                    'users.first_name',
                    'users.surname',
                    'users.email',
                    'users.phone',
                    'users.contact_mobile',
                    'users.dob',
                    'users.age',
                    'users.gender',
                    'users.image',
                    'users.address',
                    'users.profession',
                    'users.primary_contact_name',
                    'users.contact_mobile',
                    'users.relationship',
                    'users.check1',
                    'users.check2',
                    'users.check3',
                    'users.check4',
                    'users.check5',
                    'users.status',
                    'doctor.first_name as doctor_first_name',
                    'doctor.surname as doctor_sur_name',
                    'treatment_plan_mapping.patient_treatment',
                    'appointments.method_id',
                    'appointments.treatment_id',
                    'appointments.patient_id as user_id',
                    'appointments.service_id',
                    'appointments.treatment_name',
                    'appointments.appointment_date',
                    'appointments.appointment_time',
                    'appointments.appointment_status',
                    'appointments.created_at',
                    'appointments.updated_at'
                )
                ->join('members as users', 'users.id', '=', 'appointments.patient_id')
                ->join('users as doctor', 'doctor.id', '=', 'appointments.doctor_id')
                ->join('treatment_plan_mapping', 'treatment_plan_mapping.id', '=', 'appointments.service_id')
                ->where('appointment_status', '!=', "Rescheduled")
                ->where('appointment_status', '!=', "Cancel")
                ->whereDate('appointment_date', '>=', $request->from)
                ->whereDate('appointment_date', '<=', $request->to)
                ->orderBy('appointments.id', 'DESC')
                ->get();

            $groupedAppointments = $appointments->groupBy('user_id')->map(function ($patientAppointments, $user_id) {
                $patient = [
                    'patient_id' => $user_id,
                    'patient_first_name' => $patientAppointments->first()->first_name,
                    'patient_sur_name' => $patientAppointments->first()->surname,
                    'appointment_date' => $patientAppointments->first()->appointment_date,
                    'appointments' => $patientAppointments
                ];

                return $patient;
            })->values();

            // Convert the collection to array
            $groupedAppointmentsArray = $groupedAppointments->toArray();

            if ($groupedAppointmentsArray) {
                return response()->json([
                    "status" => true,
                    "message" => "Upcoming appointments details successfully!",
                    "data" => $groupedAppointmentsArray
                ]);
            } else {

                return response()->json([
                    "status" => false,
                    "message" => "Upcoming appointments details failed!",
                    "data" => []
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.', "error" => $e->getMessage()]);
        }
    }

    public function appointment_list_v2(Request $request)
    {
        try {
            $appointments = DB::table('appointments')
                ->select(
                    DB::raw('DISTINCT appointments.id'),
                    'appointments.appointment_id',
                    'users.first_name as patient_first_name',
                    'users.surname as patient_sur_name',
                    'doctor.first_name as doctor_first_name',
                    'doctor.surname as doctor_sur_name',
                    'treatment_plan_mapping.patient_treatment',
                    'appointments.patient_id',
                    'appointments.doctor_id',
                    'appointments.tooth_id',
                    'appointments.method_id',
                    'appointments.treatment_id',
                    'appointments.service_id',
                    'appointments.treatment_name',
                    'appointments.appointment_date',
                    'appointments.appointment_time',
                    'appointments.appointment_status',
                    'appointments.created_at',
                    'appointments.updated_at'
                )
                ->join('members as users', 'users.id', '=', 'appointments.patient_id')
                ->join('users as doctor', 'doctor.id', '=', 'appointments.doctor_id')
                ->join('treatment_plan_mapping', 'treatment_plan_mapping.id', '=', 'appointments.service_id')
                ->whereDate('appointments.appointment_date', '=', $request->date)
                ->get();


            $groupedAppointments = $appointments->groupBy('patient_id')->map(function ($patientAppointments, $patientId) {
                $patient = [
                    'patient_id' => $patientId,
                    'patient_first_name' => $patientAppointments->first()->patient_first_name,
                    'patient_sur_name' => $patientAppointments->first()->patient_sur_name,
                    'appointment_date' => $patientAppointments->first()->appointment_date,
                    'appointments' => $patientAppointments
                ];

                return $patient;
            })->values();

            // Convert the collection to array
            $groupedAppointmentsArray = $groupedAppointments->toArray();
            if (!$appointments->isEmpty()) {
                return response()->json([
                    "status" => true,
                    "message" => "Appointments  details success",
                    "data" => $groupedAppointmentsArray
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Appointments details not available",

                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.', 'error' => $e->getMessage()]);
        }
    }


    // public function next_appointment_v2(Request $request)
    // {


    //     $validator = Validator::make($request->all(), [
    //         "id" => 'required',
    //         "patient_id" => 'required',
    //         "doctor_id" => 'required',
    //         "treatment_id" => 'required',
    //         "appointment_date" => 'required',
    //         "appointment_time" => 'required'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json($validator->errors());
    //     }

    //     try {

    //         $reschedule_appointments = DB::table('appointments')->where('id', $request->id)->first();

    //         // $appointment_id = 'AP - ' . date('Y-m-d H:m:s');

    //         $data = [
    //             "appointment_id" => $reschedule_appointments->appointment_id,
    //             "tooth_id" => $reschedule_appointments->tooth_id,
    //             "patient_id" => $reschedule_appointments->patient_id,
    //             "doctor_id" => $request->doctor_id,
    //             "method_id" => $reschedule_appointments->method_id,
    //             "treatment_id" => $reschedule_appointments->treatment_id,
    //             "service_id" => $reschedule_appointments->service_id,
    //             "treatment_name" => $reschedule_appointments->treatment_name,
    //             "appointment_date" => $request->appointment_date,
    //             "appointment_time" => $request->appointment_time,
    //             "appointment_status" => $reschedule_appointments->appointment_status,
    //             "created_at" => date("Y-m-d H:i:s"),
    //             "updated_at" => date("Y-m-d H:i:s")
    //         ];
    //         $appointments = DB::table('appointments')->insert($data);
    //         if ($appointments) {

    //             $update_status = ["appointment_status" => "Next"];
    //             $appointments_update = DB::table('appointments')->where('id', $request->id)->update($update_status);

    //             $appointments_data = DB::table('appointments')
    //                 ->select(
    //                     DB::raw('DISTINCT appointments.id'),
    //                     'appointments.appointment_id',
    //                     'users.first_name as patient_first_name',
    //                     'users.surname as patient_sur_name',
    //                     'doctor.first_name as doctor_first_name',
    //                     'doctor.surname as doctor_sur_name',
    //                     'treatment_plan_mapping.patient_treatment',
    //                     'appointments.patient_id',
    //                     'appointments.doctor_id',
    //                     'appointments.tooth_id',
    //                     'appointments.method_id',
    //                     'appointments.treatment_id',
    //                     'appointments.service_id',
    //                     'appointments.treatment_name',
    //                     'appointments.appointment_date',
    //                     'appointments.appointment_time',
    //                     'appointments.appointment_status',
    //                     'appointments.created_at',
    //                     'appointments.updated_at'
    //                 )
    //                 ->join('members as users', 'users.id', '=', 'appointments.patient_id')
    //                 ->join('users as doctor', 'doctor.id', '=', 'appointments.doctor_id')
    //                 ->join('treatment_plan_mapping', 'treatment_plan_mapping.id', '=', 'appointments.service_id')
    //                 ->where('appointment_id', $reschedule_appointments->appointment_id)
    //                 ->orderBy('appointments.id', 'DESC')->first();

    //             return response()->json([
    //                 "status" => true,
    //                 "message" => "Rescheduled appointment successfully",
    //                 "data" => $appointments_data
    //             ]);
    //         } else {

    //             return response()->json([
    //                 "status" => false,
    //                 "message" => "Appointment details added failed",
    //                 "data" => []
    //             ]);
    //         }
    //     } catch (\Exception $e) {

    //         \Log::error('Exception occurred: ' . $e->getMessage());
    //         return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.', "error" => $e->getMessage()]);
    //     }
    // }
    public function next_appointment_v2(Request $request)
    {


        $validator = Validator::make($request->all(), [
            "id" => 'required',
            "patient_id" => 'required',
            "doctor_id" => 'required',
            "treatment_id" => 'required',
            "appointment_date" => 'required',
            "appointment_time" => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {

            $reschedule_appointments = DB::table('appointments')->where('id', $request->id)->first();

            // $appointment_id = 'AP - ' . date('Y-m-d H:m:s');

            $data = [
                "appointment_id" => $reschedule_appointments->appointment_id,
                "tooth_id" => $reschedule_appointments->tooth_id,
                "patient_id" => $reschedule_appointments->patient_id,
                "doctor_id" => $request->doctor_id,
                "method_id" => $reschedule_appointments->method_id,
                "treatment_id" => $reschedule_appointments->treatment_id,
                "service_id" => $reschedule_appointments->service_id,
                "treatment_name" => $reschedule_appointments->treatment_name,
                "appointment_date" => $request->appointment_date,
                "appointment_time" => $request->appointment_time,
                // "appointment_status" => $reschedule_appointments->appointment_status,
                "appointment_status" => "Open",
                // "inprogress_date" => $reschedule_appointments->inprogress_date,
                // "complete_date" => $reschedule_appointments->complete_date,
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s")
            ];
            // $appointments = DB::table('appointments')->insert($data);
            $appointments = DB::table('appointments')->insertGetId($data);
            if ($appointments) {

                $update_status = ["appointment_status" => "Next"];
                $appointments_update = DB::table('appointments')->where('id', $request->id)->update($update_status);

                $appointments_data = DB::table('appointments')
                    ->select(
                        DB::raw('DISTINCT appointments.id'),
                        'appointments.appointment_id',
                        'users.first_name as patient_first_name',
                        'users.surname as patient_sur_name',
                        'doctor.first_name as doctor_first_name',
                        'doctor.surname as doctor_sur_name',
                        'treatment_plan_mapping.patient_treatment',
                        'appointments.patient_id',
                        'appointments.doctor_id',
                        'appointments.tooth_id',
                        'appointments.method_id',
                        'appointments.treatment_id',
                        'appointments.service_id',
                        'appointments.treatment_name',
                        'appointments.appointment_date',
                        'appointments.appointment_time',
                        'appointments.appointment_status',
                        'appointments.created_at',
                        'appointments.updated_at',
                        'appointments.inprogress_date',
                        'appointments.complete_date'
                    )
                    ->join('members as users', 'users.id', '=', 'appointments.patient_id')
                    ->join('users as doctor', 'doctor.id', '=', 'appointments.doctor_id')
                    ->join('treatment_plan_mapping', 'treatment_plan_mapping.id', '=', 'appointments.service_id')
                    ->where('appointment_id', $reschedule_appointments->appointment_id)
                    ->orderBy('appointments.id', 'DESC')->first();

                return response()->json([
                    "status" => true,
                    "message" => "Next appointment added successfully",
                    "data" => $appointments_data
                ]);
            } else {

                return response()->json([
                    "status" => false,
                    "message" => "Appointment details added failed",
                    "data" => []
                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.', "error" => $e->getMessage()]);
        }
    }
}
