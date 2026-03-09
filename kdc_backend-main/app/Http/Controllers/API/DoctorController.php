<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use DB;
use URL;
use Illuminate\Support\Str;

class DoctorController extends Controller
{
    public function doctor_department_add(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {

            // $role = DB::table('doctor_departments')->first();

            $data =
                [
                    "title" => $request->title,
                    "description" => $request->description,
                    "created_at" => date("Y-m-d H:i:s"),
                    "updated_at" => date("Y-m-d H:i:s"),

                ];


            $insert = DB::table('doctor_departments')->insert($data);
            if ($insert) {

                $user = DB::table('doctor_departments')->where('title', $request->title)->first();

                return response()->json([
                    "status" => true,
                    "message" => "Doctor department details success!",
                    "data" => $user
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Doctor department details not available",
                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
        }
    }
    public function update_doctor_department(Request $request)
    {


        $validator = Validator::make($request->all(), [
            "id" => 'required',
            "title" => 'required|string|max:255',
            "description" => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        try {

            $user_id = $request->id;

            $data = [
                "title" => $request->title,
                "description" => $request->description,
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s"),
            ];

            $update = DB::table('doctor_departments')->where('id', $user_id)->update($data);



            if ($update) {

                $user = DB::table('doctor_departments')->where('id', $user_id)->first();

                return response()->json([
                    "status" => true,
                    "message" => "Doctor department details updated success",
                    "data" => $user
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Doctor department details update failed",
                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
        }
    }

    public function delete_doctor_department(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "id" => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }



        // $role = DB::table('doctor_departments')->where('role_name','Doctor')->first();
        $doctor = DB::table('doctor_departments')->where('id', $request->id)->delete();

        if ($doctor) {

            return response()->json([
                "status" => true,
                "message" => "Doctor department details deleted success",
                "data" => $doctor
            ]);
        } else {
            return response()->json([
                "status" => false,
                "message" => "Doctor department details delete failed",
                "data" => []

            ]);
        }
    }

    public function doctor_departments()
    {
        try {
            $doctor_departments = DB::table('doctor_departments')->get();

            if (!$doctor_departments->isEmpty()) {
                return response()->json([
                    "status" => true,
                    "message" => "Doctor department details success!",
                    "data" => $doctor_departments
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Doctor department details not available",
                    "data" => []

                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
        }
    }
    // public function store_doctor(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         "first_name" => 'required',
    //         "phone" => 'required|unique:users',
    //                     "aadhar_number" => 'required|unique:users',
    //     ]);
    //     if ($validator->fails()) {
    //         return response()->json($validator->errors());
    //     }
    //     if (!empty($request->image)) {
    //         $file_name = 'doctor-' . rand() . '.' . 'png';
    //         $new = base64_decode($request->image);
    //         $success = file_put_contents(public_path() . '/doctors/' . $file_name, $new);
    //         $image = URL::to('/public/doctors/' . $file_name);
    //     }
    //     if (empty($image)) {
    //         $image = "";
    //     }
    //     $role = DB::table('role')->where('role_name', 'Doctor')->first();

    //     // Get the latest doctor number from the database
    //     $latestDoctor = DB::table('users')->where('role_id', $role->id)->orderBy('id', 'desc')->first();
    //     $doctorNumber = $latestDoctor ? intval(substr($latestDoctor->employee_number, 5)) + 1 : 1;
    //     $doctorNumberFormatted = str_pad($doctorNumber, 4, '0', STR_PAD_LEFT);
    //     $doctorNumberWithPrefix = 'KDC-D' . $doctorNumberFormatted;

    //     $data = [
    //         "role_id" => $role->id,
    //         "department_id" => $request->department_id,
    //         "first_name" => $request->first_name,
    //         "surname" => $request->surname,
    //         "aadhar_number" => $request->aadhar_number,
    //         "email" => $request->email,
    //         "phone" => $request->phone,
    //         "address" => $request->address,
    //         // "password" => Hash::make($request->password),
    //         "password" => Hash::make($request->password),
    //         "confirm_password" => Hash::make($request->confirm_password),
    //         "dob" => $request->dob,
    //         "age" => $request->age,
    //         "gender" => $request->gender,
    //         "image" => $image,
    //         "qualification" => $request->qualification,
    //         "verify" => date("Y-m-d H:i:s"),
    //         "status" => 1,
    //         "created_at" => date("Y-m-d H:i:s"),
    //         "updated_at" => date("Y-m-d H:i:s"),
    //         "employee_number" => $doctorNumberWithPrefix, // Store the formatted doctor number
    //     ];
    //     $user_data = DB::table('users')->where('phone', $request->phone)->first();
    //     if (!empty($user_data)) {
    //         return response()->json([
    //             "status" => false,
    //             "message" => "The mobile number is already taken",
    //         ]);
    //     } else {
    //         if ($request->password == $request->confirm_password) {
    //             $user = DB::table('users')->insert($data);
    //             if ($user) {
    //                 // $user_data1 =  DB::table('users')->where('phone',$request->phone)->first();
    //                 $user_data1 = DB::table('users')
    //                     ->select('doctor_departments.title as department_name', 'doctor_departments.id as department_id', 'users.id', 'users.aadhar_number',  'users.employee_number', 'users.role_id', 'role.role_name', 'users.first_name', 'users.surname', 'users.email', 'users.phone', 'users.dob', 'users.age', 'users.gender', 'users.image', 'users.address', 'users.qualification', 'users.status', 'users.created_at', 'users.updated_at')
    //                     ->join('doctor_departments', 'doctor_departments.id', '=', 'users.department_id')
    //                     ->join('role', 'role.id', '=', 'users.role_id')
    //                     ->where('users.phone', $request->phone)->where('users.status', 1)->get();
    //                 return response()->json([
    //                     "status" => true,
    //                     "message" => "Doctor registration successfully",
    //                     "data" => $user_data1
    //                 ]);
    //             } else {
    //                 return response()->json([
    //                     "status" => false,
    //                     "message" => "Doctor registration failed",
    //                     "data" => []
    //                 ]);
    //             }
    //         } else {
    //             return response()->json([
    //                 "status" => false,
    //                 "message" => "Password and confirm password not matched"
    //             ]);
    //         }
    //     }
    // }
    public function store_doctor(Request $request)
{
    $validator = Validator::make($request->all(), [
        "first_name" => 'required',
        "phone" => 'required',
        "aadhar_number" => 'required|unique:users',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors());
    }

    if (!empty($request->image)) {
        $file_name = 'doctor-' . rand() . '.' . 'png';
        $new = base64_decode($request->image);
        $success = file_put_contents(public_path() . '/doctors/' . $file_name, $new);
        $image = URL::to('/public/doctors/' . $file_name);
    }

    if (empty($image)) {
        $image = "";
    }

    $role = DB::table('role')->where('role_name', 'Doctor')->first();

    // Get the latest doctor number from the database
    $latestDoctor = DB::table('users')->where('role_id', $role->id)->orderBy('id', 'desc')->first();
    $doctorNumber = $latestDoctor ? intval(substr($latestDoctor->employee_number, 5)) + 1 : 1;
    $doctorNumberFormatted = str_pad($doctorNumber, 4, '0', STR_PAD_LEFT);
    $doctorNumberWithPrefix = 'KDC-D' . $doctorNumberFormatted;

    $data = [
        "role_id" => $role->id,
        "department_id" => $request->department_id,
        "first_name" => $request->first_name,
        "surname" => $request->surname,
        "aadhar_number" => $request->aadhar_number,
        "email" => $request->email,
        "phone" => $request->phone,
        "address" => $request->address,
        "password" => Hash::make($request->password),
        "confirm_password" => Hash::make($request->confirm_password),
        "dob" => $request->dob,
        "age" => $request->age,
        "gender" => $request->gender,
        "image" => $image,
        "qualification" => $request->qualification,
        "specialist" => $request->specialist,
        "currently_practicing" => $request->currently_practicing,
        "experience" => $request->experience,
        "awards_achievements" => $request->awards_achievements,
        "journey" => $request->journey,
        "verify" => date("Y-m-d H:i:s"),
        "status" => 1,
        "created_at" => date("Y-m-d H:i:s"),
        "updated_at" => date("Y-m-d H:i:s"),
        "employee_number" => $doctorNumberWithPrefix, // Store the formatted doctor number
    ];

    $user_data = DB::table('users')->where('phone', $request->phone)->first();

    if ($user_data) {
      
        $updateUserTable = DB::table('users')->where('id', $user_data->id)->update($data);

        $user_data1 = DB::table('users')
        ->select('doctor_departments.title as department_name', 'doctor_departments.id as department_id', 'users.id', 'users.aadhar_number', 'users.employee_number', 'users.role_id', 'role.role_name', 'users.first_name', 'users.surname', 'users.email', 'users.phone', 'users.dob', 'users.age', 'users.gender', 'users.image', 'users.address', 'users.qualification', 'users.specialist', 'users.status', 'users.created_at', 'users.updated_at')
        ->join('doctor_departments', 'doctor_departments.id', '=', 'users.department_id')
        ->join('role', 'role.id', '=', 'users.role_id')
        ->where('users.phone', $request->phone)->where('users.status', 1)->get();


        return response()->json([
            "status" => true,
            "message" => "Doctor registration successfully",
            "data" => $user_data1
        ]);
    }

    if ($request->password == $request->confirm_password) {
        $user = DB::table('users')->insert($data);
        if ($user) {
            $user_data1 = DB::table('users')
                ->select('doctor_departments.title as department_name', 'doctor_departments.id as department_id', 'users.id', 'users.aadhar_number', 'users.employee_number', 'users.role_id', 'role.role_name', 'users.first_name', 'users.surname', 'users.email', 'users.phone', 'users.dob', 'users.age', 'users.gender', 'users.image', 'users.address', 'users.qualification', 'users.specialist', 'users.status', 'users.created_at', 'users.updated_at')
                ->join('doctor_departments', 'doctor_departments.id', '=', 'users.department_id')
                ->join('role', 'role.id', '=', 'users.role_id')
                ->where('users.phone', $request->phone)->where('users.status', 1)->get();

            return response()->json([
                "status" => true,
                "message" => "Doctor registration successfully",
                "data" => $user_data1
            ]);
        } else {
            return response()->json([
                "status" => false,
                "message" => "Doctor registration failed",
                "data" => []
            ]);
        }
    } else {
        return response()->json([
            "status" => false,
            "message" => "Password and confirm password not matched"
        ]);
    }
}



    // public function store_doctor(Request $request)
    // {



    //     $validator = Validator::make($request->all(), [
    //         "first_name" => 'required',
    //         "surname" => 'required',
    //         "email" => 'required',
    //         "phone" => 'required',
    //         "password" => 'required',
    //         "confirm_password" => 'required',
    //         "address" => 'required',
    //         "dob" => 'required',
    //         "age" => 'required',
    //         "gender" => 'required',
    //         // "image" => 'required',
    //         "qualification" => 'required',

    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json($validator->errors());
    //     }

    //     if (!empty($request->image)) {

    //         $file_name = 'doctor-' . rand() . '.' . 'png';
    //         $new = base64_decode($request->image);
    //         $success = file_put_contents(public_path() . '/doctors/' . $file_name, $new);
    //         $image = URL::to('/public/doctors/' . $file_name);
    //     }

    //     if (empty($image)) {

    //         $image = "";

    //     }


    //     $role = DB::table('role')->where('role_name', 'Doctor')->first();

    //     $data = [
    //         "role_id" => $role->id,
    //         "department_id" => $request->department_id,
    //         "first_name" => $request->first_name,
    //         "surname" => $request->surname,
    //         "email" => $request->email,
    //         "phone" => $request->phone,
    //         "address" => $request->address,
    //         // "password" => Hash::make($request->password),
    //         "password" => Hash::make($request->password),
    //         "confirm_password" => Hash::make($request->confirm_password),
    //         "dob" => $request->dob,
    //         "age" => $request->age,
    //         "gender" => $request->gender,
    //         "image" => $image,
    //         "qualification" => $request->qualification,
    //         "verify" => date("Y-m-d H:i:s"),
    //         "status" => 1,
    //         "created_at" => date("Y-m-d H:i:s"),
    //         "updated_at" => date("Y-m-d H:i:s")
    //     ];




    //     $user_data = DB::table('users')->where('phone', $request->phone)->first();

    //     if (!empty($user_data)) {

    //         return response()->json([
    //             "status" => false,
    //             "message" => "The mobile number is already taken",

    //         ]);

    //     } else {


    //         if ($request->password == $request->confirm_password) {


    //             $user = DB::table('users')->insert($data);

    //             if ($user) {
    //                 // $user_data1 =  DB::table('users')->where('phone',$request->phone)->first();
    //                 $user_data1 = DB::table('users')
    //                     ->select('doctor_departments.title as department_name', 'users.id', 'users.role_id', 'role.role_name', 'users.first_name', 'users.surname', 'users.email', 'users.phone', 'users.dob', 'users.age', 'users.gender', 'users.image', 'users.address', 'users.qualification', 'users.status', 'users.created_at', 'users.updated_at')
    //                     ->join('doctor_departments', 'doctor_departments.id', '=', 'users.department_id')
    //                     ->join('role', 'role.id', '=', 'users.role_id')
    //                     ->where('users.phone', $request->phone)->where('users.status', 1)->get();

    //                 return response()->json([
    //                     "status" => true,
    //                     "message" => "Doctor registration successfully",
    //                     "data" => $user_data1
    //                 ]);

    //             } else {

    //                 return response()->json([
    //                     "status" => false,
    //                     "message" => "Doctor registration failed",
    //                     "data" => []
    //                 ]);
    //             }


    //         } else {
    //             return response()->json([
    //                 "status" => false,
    //                 "message" => "Password and confirm password not matched"
    //             ]);
    //         }


    //     }

    // }
    public function doctor_list()
    {

        $role = DB::table('role')->where('role_name', 'Doctor')->first();

        $doctors = DB::table('users')
            ->select('doctor_departments.title as department_name', 'doctor_departments.id as department_id', 'users.id', 'users.aadhar_number', 'users.employee_number', 'users.role_id', 'role.role_name', 'users.first_name', 'users.surname', 'users.email', 'users.phone', 'users.dob', 'users.age', 'users.gender', 'users.image', 'users.address', 'users.qualification', 'users.specialist', 'users.currently_practicing', 'users.experience', 'users.awards_achievements', 'users.journey', 'users.status', 'users.created_at', 'users.updated_at')
            ->join('doctor_departments', 'doctor_departments.id', '=', 'users.department_id')
            ->join('role', 'role.id', '=', 'users.role_id')
            ->where('users.role_id', $role->id)
            ->orderBy('users.employee_number')
            // ->where('users.status','=', 1)
            ->get();

        // $doctors = DB::table('users')->where('role_id',$role->id)->where('status',1)->get();

        if (!$doctors->isEmpty()) {
            return response()->json([
                "status" => true,
                "message" => "Doctor details success!",
                "data" => $doctors
            ]);
        } else {
            return response()->json([
                "status" => false,
                "message" => "Doctor details not available",
                "data" => []

            ]);
        }
    }
//Old

    // public function doctor_list()
    // {

    //     $role = DB::table('role')->where('role_name', 'Doctor')->first();

    //     $doctors = DB::table('users')
    //         ->select('doctor_departments.title as department_name', 'doctor_departments.id as department_id', 'users.id', 'users.aadhar_number', 'users.employee_number', 'users.role_id', 'role.role_name', 'users.first_name', 'users.surname', 'users.email', 'users.phone', 'users.dob', 'users.age', 'users.gender', 'users.image', 'users.address', 'users.qualification', 'users.status', 'users.created_at', 'users.updated_at')
    //         ->join('doctor_departments', 'doctor_departments.id', '=', 'users.department_id')
    //         ->join('role', 'role.id', '=', 'users.role_id')
    //         ->where('users.role_id', $role->id)
    //          ->orderBy('users.employee_number')
    //          ->get();

    //     // $doctors = DB::table('users')->where('role_id',$role->id)->where('status',1)->get();

    //     if (!$doctors->isEmpty()) {
    //         return response()->json([
    //             "status" => true,
    //             "message" => "Doctor details success!",
    //             "data" => $doctors
    //         ]);
    //     } else {
    //         return response()->json([
    //             "status" => false,
    //             "message" => "Doctor details not available",
    //             "data" => []

    //         ]);
    //     }
    // }
    
    public function delete_doctor(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "id" => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }



        $role = DB::table('role')->where('role_name', 'Doctor')->first();
        $doctor = DB::table('users')->where('id', $request->id)->where('role_id', $role->id)->update([
            'status' => $request->isChecked ? 1:0
          ]);

        if ($doctor) {

            return response()->json([
                "status" => true,
                "message" => "Doctor details deleted success",

            ]);
        } else {
            return response()->json([
                "status" => false,
                "message" => "Doctor details delete failed",
                "data" => []

            ]);
        }
    }
    

    // public function delete_doctor(Request $request)
    // {

    //     $validator = Validator::make($request->all(), [
    //         "id" => 'required',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json($validator->errors());
    //     }



    //     $role = DB::table('role')->where('role_name', 'Doctor')->first();
    //     $doctor = DB::table('users')->where('id', $request->id)->where('role_id', $role->id)->delete();

    //     if ($doctor) {

    //         return response()->json([
    //             "status" => true,
    //             "message" => "Doctor details deleted success",

    //         ]);
    //     } else {
    //         return response()->json([
    //             "status" => false,
    //             "message" => "Doctor details delete failed",
    //             "data" => []

    //         ]);
    //     }
    // }


    public function update_doctor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "phone" => 'unique:users,phone,' . $request->id,
            "aadhar_number" => 'unique:users,aadhar_number,' . $request->id
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
    



        $data = [
            "department_id" => $request->department_id,
            "aadhar_number" => $request->aadhar_number,
            "first_name" => $request->first_name,
            "surname" => $request->surname,
            "email" => $request->email,
            "phone" => $request->phone,
            "address" => $request->address,
            "dob" => $request->dob,
            "age" => $request->age,
            "gender" => $request->gender,
            "contact_mobile"=> $request->contact_mobile,
            "qualification" => $request->qualification,
            "specialist" => $request->specialist,
            "currently_practicing" => $request->currently_practicing,
            "experience" => $request->experience,
            "awards_achievements" => $request->awards_achievements,
            "journey" => $request->journey,
            "updated_at" => date("Y-m-d H:i:s")
        ];

        if (!empty($request->image)) {
            if (Str::contains($request->image, 'https')) {
                $data['image'] = $request->image;
            } else {
                $file_name = 'doctor-' . rand() . '.' . 'png';
                $new = base64_decode($request->image);
                $success = file_put_contents(public_path() . '/doctors/' . $file_name, $new);
                $data['image'] = URL::to('/public/doctors/' . $file_name);
            }
        }

        $user = DB::table('users')->where('id', $request->id)->update($data);

        if ($user) {
            $user_data1 = DB::table('users')->select('doctor_departments.title as department_name', 'users.id', 'users.aadhar_number', 'users.employee_number', 'users.role_id', 'users.first_name', 'users.surname', 'users.email', 'users.phone', 'users.dob', 'users.age', 'users.gender', 'users.image', 'users.address', 'users.qualification', 'users.specialist', 'users.contact_mobile',  'users.status', 'users.created_at', 'users.updated_at')
                ->join('doctor_departments', 'doctor_departments.id', '=', 'users.department_id')
                ->where('users.id', $request->id)
                ->first();

            return response()->json([
                "status" => true,
                "message" => "Doctor details updated successfully",
                "data" => $user_data1
            ]);
        } else {

            return response()->json([
                "status" => false,
                "message" => "Doctor details updated failed",
                "data" => []
            ]);
        }
    }
    //indivdual appointment
    public function doctor_appointments(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'appointment_date' => 'required',
            'doctor_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()->all()], 422);
        }

        $doctor_appointmets = DB::table('appointments')
            ->select(
                DB::raw('DISTINCT appointments.id'),
                'doctor.first_name as doctor_first_name',
                'doctor.surname as doctor_surname',
                'users.first_name as patient_first_name',
                'users.surname as patient_surname',
                'treatmentplan.treatment_plan',
                'treatmentplan.diagnosis_id',
                'treatmentplan.diagnosis_type_id',
                'treatment_plan_procedure.treatment_procedure',
                'treatment_plan_method.treatment_method',
                'treatment_plan_method.method_price',
                'appointments.appointment_id',
                'appointments.patient_id',
                'appointments.doctor_id',
                'appointments.treatment_id',
                'appointments.appointment_date',
                'appointments.appointment_time',
                'appointments.created_at',
                'appointments.updated_at',

            )
            ->join('members as users', 'users.id', '=', 'appointments.patient_id')
            ->join('users as doctor', 'doctor.id', '=', 'appointments.doctor_id')
            ->join('treatmentplan', 'treatmentplan.id', '=', 'appointments.treatment_id')
            ->join('treatment_plan_procedure', 'treatment_plan_procedure.treatment_plan_id', '=', 'treatmentplan.id')
            ->join('treatment_plan_method', 'treatment_plan_method.treatment_procedure_id', '=', 'treatment_plan_procedure.id')
            ->where('appointment_status', '!=', "Rescheduled")
            ->where('appointment_date', $request->appointment_date)
            ->where('appointments.doctor_id', $request->doctor_id)
            ->where('users.status', '1')
            ->where('doctor.status', '1')
            ->get();
        // $doctor_appointmets = DB::table('appointments')->where('appointment_status', '!=', "Rescheduled")->where('appointment_date', $request->date)->where('doctor_id', $request->doctor_id)->get();

        if (!$doctor_appointmets->isEmpty()) {

            return response()->json([
                "status" => true,
                "message" => "Appointments details success",
                "data" => $doctor_appointmets
            ]);
        } else {


            return response()->json([
                "status" => false,
                "message" => "Appointments details not available",
                "data" => []
            ]);
        }
    }



    // overall appointment
    public function overall_doctor_appointments(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'appointment_date' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()->all()], 422);
        }


        $doctor_appointmets = DB::table('appointments')
            ->select(
                DB::raw('DISTINCT appointments.id'),
                'doctor.first_name as doctor_first_name',
                'doctor.surname as doctor_surname',
                'users.first_name as patient_first_name',
                'users.surname as patient_surname',
                'treatmentplan.treatment_plan',
                'treatmentplan.diagnosis_id',
                'treatmentplan.diagnosis_type_id',
                'treatment_plan_procedure.treatment_procedure',
                'treatment_plan_method.treatment_method',
                'treatment_plan_method.method_price',
                'appointments.appointment_id',
                'appointments.patient_id',
                'appointments.doctor_id',
                'appointments.treatment_id',
                'appointments.appointment_date',
                'appointments.appointment_time',
                'appointments.created_at',
                'appointments.updated_at',

            )
            ->join('members as users', 'users.id', '=', 'appointments.patient_id')
            ->join('users as doctor', 'doctor.id', '=', 'appointments.doctor_id')
            ->join('treatmentplan', 'treatmentplan.id', '=', 'appointments.treatment_id')
            ->join('treatment_plan_procedure', 'treatment_plan_procedure.treatment_plan_id', '=', 'treatmentplan.id')
            ->join('treatment_plan_method', 'treatment_plan_method.treatment_procedure_id', '=', 'treatment_plan_procedure.id')
            ->where('appointment_status', '!=', "Rescheduled")
            ->where('appointment_date', $request->appointment_date)
            ->where('users.status', '1')
            ->get();

        if (!$doctor_appointmets->isEmpty()) {

            return response()->json([
                "status" => true,
                "message" => "Appointments details success",
                "data" => $doctor_appointmets
            ]);
        } else {


            return response()->json([
                "status" => false,
                "message" => "Appointments details not available",
                "data" => []
            ]);
        }
    }
}
