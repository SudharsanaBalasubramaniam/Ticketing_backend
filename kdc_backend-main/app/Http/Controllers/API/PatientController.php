<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use URL;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class PatientController extends Controller
{

  public function patient_appointment(Request $request)
  {


    $validator = Validator::make($request->all(), [
      // 'date' => 'required',
      'patient_id' => 'required'
    ]);

    if ($validator->fails()) {
      return response()->json(['status' => false, 'errors' => $validator->errors()->all()], 422);
    }


    $appointments = DB::table('appointments')
      ->select(
        DB::raw('DISTINCT appointments.id'),
        'doctor.first_name as doctor_first_name',
        'doctor.surname as doctor_surname',
        'users.first_name as patient_first_name',
        'users.surname as patient_surname',
        'treatment_plan_mapping.treatment_ran_id',
        'treatment_plan_mapping.patient_treatment',
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
      ->join('treatment_plan_mapping', 'treatment_plan_mapping.id', '=', 'appointments.treatment_id')
      ->where('appointment_status', '!=', "Rescheduled")
      ->where('appointment_status', '!=', "completed")
      ->where('appointments.patient_id', $request->patient_id)
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
        "data" => []
      ]);
    }
  }



  public function new_patient_list(Request $request)
  {

    try {

      $role = DB::table('role')->where('role_name', 'Patient')->first();
      $currentMonth = date('m');

      $patients = DB::table('members')
        ->select('members.id', 'members.role_id', 'role.role_name', 'members.first_name', 'members.surname', 'members.email', 'members.phone', 'members.dob', 'members.age', 'members.gender', 'members.image', 'members.address', 'members.profession', 'members.primary_contact_name', 'members.relationship', 'members.check1', 'members.check2', 'members.check3', 'members.check4', 'members.check5', 'members.status', 'members.created_at', 'members.updated_at')
        ->join('role', 'role.id', '=', 'members.role_id')
        ->whereRaw('MONTH(members.created_at) = ?', [$currentMonth])
        ->where('members.role_id', $role->id)
        ->orderBy('members.id', 'DESC')
        ->get();

      if (!$patients->isEmpty()) {

        return response()->json([
          "status" => true,
          "message" => "New patient details success",
          "data" => $patients
        ]);
      } else {

        return response()->json([
          "status" => false,
          "message" => "New patient details are not available.",

        ]);
      }
    } catch (\Exception $e) {

      \Log::error('Exception occurred: ' . $e->getMessage());
      return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
    }
  }
  public function old_patient_list(Request $request)
  {

    try {

      $role = DB::table('role')->where('role_name', 'Patient')->first();
      $currentMonth = date('m');

      $patients = DB::table('members')
        ->select('members.id', 'members.role_id', 'role.role_name', 'members.first_name', 'members.surname', 'members.email', 'members.phone', 'members.dob', 'members.age', 'members.gender', 'members.image', 'members.address', 'members.profession', 'members.primary_contact_name', 'members.relationship', 'members.check1', 'members.check2', 'members.check3', 'members.check4', 'members.check5', 'members.status', 'members.created_at', 'members.updated_at')
        ->join('role', 'role.id', '=', 'members.role_id')
        ->whereRaw('MONTH(members.created_at)  < ?', [$currentMonth])
        ->where('members.role_id', $role->id)
        ->orderBy('members.id', 'DESC')
        ->get();

      if (!$patients->isEmpty()) {

        return response()->json([
          "status" => true,
          "message" => "Old patient details success",
          "data" => $patients
        ]);
      } else {

        return response()->json([
          "status" => false,
          "message" => "Old patient details are not available.",

        ]);
      }
    } catch (\Exception $e) {

      \Log::error('Exception occurred: ' . $e->getMessage());
      return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
    }
  }


  // public function store_patient(Request $request)
  // {

  //   $validator = Validator::make($request->all(), [
  //     "first_name" => 'required',
  //     "phone" => 'required|unique:users',
  //     "patient_reg_no" => 'unique:users'
  //   ]);

  //   if ($validator->fails()) {
  //     return response()->json($validator->errors());
  //   }


  //   if (!empty($request->image)) {

  //     $file_name = 'patient-' . rand() . '.' . 'png';
  //     $new = base64_decode($request->image);
  //     $success = file_put_contents(public_path() . '/patients/' . $file_name, $new);
  //     $image = URL::to('/public/patients/' . $file_name);
  //   }
  //   if (empty($image)) {
  //     $image = "";
  //   }


  //   $users = DB::table('users')->get();

  //   if (!$users->isEmpty()) {

  //     $ran_id = array();
  //     foreach ($users as $users_key => $users_value) {

  //       if ($users_value->registration_number != NULL) {

  //         $ran_id[] = $users_value->registration_number;
  //       }
  //     }

  //     if (!empty($ran_id)) {
  //       $user_registration_number = max($ran_id) + 1;
  //       $registration_number = $user_registration_number;
  //     } else {
  //       $registration_number = '10001';
  //     }
  //   } else {
  //     $registration_number = '10001';
  //   }


  //   $role = DB::table('role')->where('role_name', 'Patient')->first();

  //   $data = [
  //     "registration_number" => $registration_number,
  //     "patient_reg_no" => $request->patient_reg_no,
  //     "role_id" => $role->id,
  //     "first_name" => $request->first_name,
  //     "surname" => $request->surname,
  //     "email" => $request->email,
  //     "phone" => $request->phone,
  //     "address" => $request->address,
  //     // "password" => Hash::make($request->password),
  //     "password" => Hash::make('patient@kdc'),
  //     "dob" => $request->dob,
  //     "age" => $request->age,
  //     "gender" => $request->gender,
  //     "image" => $image,
  //     "profession" => $request->profession,
  //     "primary_contact_name" => $request->primary_contact_name,
  //     "relationship" => $request->relationship,
  //     "contact_mobile" => $request->contact_mobile,
  //     "verify" => date("Y-m-d H:i:s"),
  //     "check1" => "Yes",
  //     "check2" => "Yes",
  //     "check3" => "Yes",
  //     "check4" => "Yes",
  //     "check5" => "Yes",
  //     "status" => 1,
  //     "created_at" => date("Y-m-d H:i:s"),
  //     "updated_at" => date("Y-m-d H:i:s")
  //   ];


  //   $user_data = DB::table('users')->where('phone', $request->phone)->first();

  //   if (!empty($user_data)) {

  //     return response()->json([
  //       "status" => false,
  //       "message" => "The mobile number is already taken",

  //     ]);
  //   } else {

  //     $user = DB::table('users')->insert($data);

  //     if ($user) {
  //       // $user_data1 =  DB::table('users')->where('phone',$request->phone)->where('status',1)->first();

  //       $user_data1 = DB::table('users')
  //         ->select('users.id', 'users.role_id', 'role.role_name', 'users.patient_reg_no', 'users.first_name', 'users.surname', 'users.email', 'users.phone', 'users.dob', 'users.age', 'users.gender', 'users.image', 'users.address', 'users.profession', 'users.primary_contact_name', 'users.relationship', 'users.check1', 'users.check2', 'users.check3', 'users.check4', 'users.check5', 'users.status', 'users.created_at', 'users.updated_at')
  //         ->join('role', 'role.id', '=', 'users.role_id')
  //         ->where('users.phone', $request->phone)
  //         ->where('users.status', 1)
  //         ->get();

  //       return response()->json([
  //         "status" => true,
  //         "message" => "Patient registration successfully",
  //         "data" => $user_data1
  //       ]);
  //     } else {

  //       return response()->json([
  //         "status" => false,
  //         "message" => "Patient registration failed",
  //         "data" => []
  //       ]);
  //     }
  //   }
  // }
  public function store_patient(Request $request)
  {

    if(!empty($request->user_type)){
      $validator = Validator::make($request->all(), [
        "first_name" => 'required',
        "phone" => 'required',
      ]);
  
      if ($validator->fails()) {
        return response()->json($validator->errors());
      }
    }else{
      $validator = Validator::make($request->all(), [
        "first_name" => 'required',
        "phone" => 'required',
        // "patient_reg_no" => 'unique:users,patient_reg_no|unique:members,patient_reg_no'
         "patient_reg_no" => 'unique:members,patient_reg_no'
      ]);
  
      if ($validator->fails()) {
        // return response()->json($validator->errors());
        return response()->json([
          "status" => false,
          "message" => "Patient registration must be unique",
          "data" => []
        ]);

      }
    }

    if(!empty($regNo)){
      return response()->json([
        "status" => false,
        "message" => "Patient registration failed",
        "data" => []
      ]);
    }


    if (!empty($request->image)) {

      $file_name = 'patient-' . rand() . '.' . 'png';
      $new = base64_decode($request->image);
      $success = file_put_contents(public_path() . '/patients/' . $file_name, $new);
      $image = URL::to('/public/patients/' . $file_name);
    }
    if (empty($image)) {
      $image = "";
    }


    $users = DB::table('members')->get();

    if (!$users->isEmpty()) {
      $ran_id = array();
      foreach ($users as $users_key => $users_value) {
        if ($users_value->registration_number != NULL) {
          $ran_id[] = $users_value->registration_number;
        }
      }

      if (!empty($ran_id)) {
        $user_registration_number = max($ran_id) + 1;
        $registration_number = $user_registration_number;
      } else {
        $registration_number = '10001';
      }
    } else {
      $registration_number = '10001';
    }


    $role = DB::table('role')->where('role_name', 'Patient')->first();

        // Get the latest doctor number from the database
        // $latestDoctor = DB::table('members')->orderBy('id', 'desc')->first();
        // $doctorNumber = $latestDoctor ? intval(substr($latestDoctor->employee_number, 5)) + 1 : 1;
        $latestDoctor = DB::table('members')->where('role_id', $role->id)->orderBy('id', 'desc')->first();
        $doctorNumber = $latestDoctor ? intval(substr($latestDoctor->patient_reg_no, 5)) + 1 : 1;
        $doctorNumberFormatted = str_pad($doctorNumber, 4, '0', STR_PAD_LEFT);
        $doctorNumberWithPrefix = 'KDC-P' . $doctorNumberFormatted;

    $data = [
      "registration_number" => $registration_number,
    //   "patient_reg_no" => $doctorNumberWithPrefix,
    "patient_reg_no" =>  $request->patient_reg_no ? $request->patient_reg_no : $doctorNumberWithPrefix,

      "role_id" => $role->id,
      "first_name" => $request->first_name,
      "surname" => $request->surname,
      "email" => $request->email,
      "phone" => $request->phone,
      "address" => $request->address,
      // "password" => Hash::make($request->password),
      "password" => Hash::make('patient@kdc'),
      "dob" => $request->dob,
      "age" => $request->age,
      "gender" => $request->gender,
      "image" => $image,
      "profession" => $request->profession,
      "primary_contact_name" => $request->primary_contact_name,
      "relationship" => $request->relationship,
      "contact_mobile" => $request->contact_mobile,
      "verify" => date("Y-m-d H:i:s"),
      "check1" => "Yes",
      "check2" => "Yes",
      "check3" => "Yes",
      "check4" => "Yes",
      "check5" => "Yes",
      "status" => 1,
      "created_at" => date("Y-m-d H:i:s"),
      "updated_at" => date("Y-m-d H:i:s")
    ];


    $user_data = DB::table('users')->where('phone', $request->phone)->first();

    if (!empty($user_data)) {
      $data['user_id'] =  $user_data->id;
      $user = DB::table('members')->insert($data);
      $user_data1 = DB::table('members')->where('user_id', $user_data->id)->first();
      $notification = [
        "patient_id" => $user_data1->id,
        "doctor_status" => "waiting",
        "staff_status" => "waiting",
        "admin_status" => "waiting",
        "created_at" => date("Y-m-d H:i:s"),
      ];
      DB::table('notification')->insert($notification);
      return response()->json([
        "status" => true,
        "message" => "Patient registration successfully.;",
        "data" => $user_data1
      ]);
    } else {
      $user = DB::table('users')->insert($data);
    

      if ($user) {
        $user_data1 = DB::table('users')->where('phone', $request->phone)->first();
        $data['user_id'] =  $user_data1->id;
        $user2 = DB::table('members')->insert($data);
        // $user_data1 =  DB::table('members')->where('phone',$request->phone)->where('status',1)->first();

        $user_data1 = DB::table('members')
          ->select('members.id', 'members.role_id', 'role.role_name', 'members.patient_reg_no', 'members.first_name', 'members.surname', 'members.email', 'members.phone', 'members.dob', 'members.age', 'members.gender', 'members.image', 'members.address', 'members.profession', 'members.primary_contact_name', 'members.relationship', 'members.check1', 'members.check2', 'members.check3', 'members.check4', 'members.check5', 'members.status', 'members.created_at', 'members.updated_at')
          ->join('role', 'role.id', '=', 'members.role_id')
          ->where('members.phone', $request->phone)
          ->where('members.status', 1)
          ->get();

          $regNo = DB::table('members')->where('patient_reg_no', $request->patient_reg_no)->first();
   

        return response()->json([
          "status" => true,
          "message" => "Patient registration successfully",
          "data" => $user_data1
        ]);
      } else {

        return response()->json([
          "status" => false,
          "message" => "Patient registration failed",
          "data" => []
        ]);
      }
    }
  }

public function patient_list()
  {

    $role = DB::table('role')->where('role_name', 'Patient')->first();
    // $patients = DB::table('members')->where('role_id',$role->id)->get();

    $patients = DB::table('users')
      ->select('users.id', 'users.registration_number', 'users.employee_number', 'users.patient_reg_no', 'users.role_id', 'role.role_name', 'users.first_name', 'users.surname', 'users.email', 'users.phone', 'users.contact_mobile', 'users.dob', 'users.age', 'users.gender', 'users.image', 'users.address', 'users.profession', 'users.primary_contact_name', 'users.contact_mobile', 'users.relationship', 'users.check1', 'users.check2', 'users.check3', 'users.check4', 'users.check5', 'users.status', 'users.created_at', 'users.updated_at')
      ->join('role', 'role.id', '=', 'users.role_id')
      ->where('users.role_id', $role->id)
      ->get();


    $members = DB::table('members')
      ->select(
        'members.id',
        'members.registration_number',
        'members.employee_number',
        'members.patient_reg_no',
        'members.role_id',
        'members.first_name',
        'members.user_id',
        'members.surname',
        'members.email',
        'members.phone',
        'members.contact_mobile',
        'members.dob',
        'members.age',
        'members.gender',
        'members.image',
        'members.address',
        'members.profession',
        'members.primary_contact_name',
        'members.contact_mobile',
        'members.relationship',
        'members.check1',
        'members.check2',
        'members.check3',
        'members.check4',
        'members.check5',
        'members.status',
        'members.created_at',
        'members.updated_at'
      )
      ->where('members.status','=', 1)
      ->get();

    // $mergeList = $patients->merge($members);


    // if (!$patients->isEmpty()) {
    //   return response()->json([
    //     "status" => true,
    //     "message" => "Patient details success!",
    //     "data" => $mergeList

    //   ]);
    // } else {
    //   return response()->json([
    //     "status" => false,
    //     "message" => "Patient details not available",
    //     "data" => []
    //   ]);
    // }
    if (!$members->isEmpty()) {
      return response()->json([
        "status" => true,
        "message" => "Patient and member details success!",
        "data" => $members
        // "data" => $mergeList
      ]);
    } else {
      return response()->json([
        "status" => false,
        "message" => "Patient and member details not available",
        "data" => []
      ]);
    }
  }

  public function delete_patient(Request $request)
  {

    $validator = Validator::make($request->all(), [
      "id" => 'required',
    ]);

    if ($validator->fails()) {
      return response()->json($validator->errors());
    }

    $role = DB::table('role')->where('role_name', 'Patient')->first();
    $patient = DB::table('members')->where('id', $request->id)->where('role_id', $role->id)->update([
      'status' => 0
    ]);

    if ($patient) {

      return response()->json([
        "status" => true,
        "message" => "Patient details deleted success",

      ]);
    } else {
      return response()->json([
        "status" => false,
        "message" => "Patient details delete failed",
        "data" => []

      ]);
    }
  }

//   public function patient_list()
//   {

//     $role = DB::table('role')->where('role_name', 'Patient')->first();
//     // $patients = DB::table('members')->where('role_id',$role->id)->get();

//     $patients = DB::table('users')
//       ->select('users.id', 'users.registration_number', 'users.employee_number', 'users.patient_reg_no', 'users.role_id', 'role.role_name', 'users.first_name', 'users.surname', 'users.email', 'users.phone', 'users.contact_mobile', 'users.dob', 'users.age', 'users.gender', 'users.image', 'users.address', 'users.profession', 'users.primary_contact_name', 'users.contact_mobile', 'users.relationship', 'users.check1', 'users.check2', 'users.check3', 'users.check4', 'users.check5', 'users.status', 'users.created_at', 'users.updated_at')
//       ->join('role', 'role.id', '=', 'users.role_id')
//       ->where('users.role_id', $role->id)->get();


//       $members = DB::table('members')
//       ->select('members.id', 'members.registration_number', 'members.employee_number', 'members.patient_reg_no', 'members.role_id', 'members.first_name', 
//       'members.user_id','members.surname', 'members.email', 'members.phone', 'members.contact_mobile', 'members.dob', 'members.age', 'members.gender', 'members.image', 'members.address', 'members.profession', 'members.primary_contact_name', 'members.contact_mobile', 'members.relationship', 'members.check1', 'members.check2', 'members.check3', 'members.check4', 'members.check5', 'members.status', 'members.created_at', 'members.updated_at')->get();

//       // $mergeList = $patients->merge($members);
      

//     // if (!$patients->isEmpty()) {
//     //   return response()->json([
//     //     "status" => true,
//     //     "message" => "Patient details success!",
//     //     "data" => $mergeList

//     //   ]);
//     // } else {
//     //   return response()->json([
//     //     "status" => false,
//     //     "message" => "Patient details not available",
//     //     "data" => []
//     //   ]);
//     // }
//     if (!$members->isEmpty()) {
//       return response()->json([
//           "status" => true,
//           "message" => "Patient and member details success!",
//           "data" => $members
//           // "data" => $mergeList
//       ]);
//   } else {
//       return response()->json([
//           "status" => false,
//           "message" => "Patient and member details not available",
//           "data" => []
//       ]);
//   }
//   }

//   public function delete_patient(Request $request)
//   {

//     $validator = Validator::make($request->all(), [
//       "id" => 'required',
//     ]);

//     if ($validator->fails()) {
//       return response()->json($validator->errors());
//     }

//     $role = DB::table('role')->where('role_name', 'Patient')->first();
//     $patient = DB::table('members')->where('id', $request->id)->where('role_id', $role->id)->delete();

//     if ($patient) {

//       return response()->json([
//         "status" => true,
//         "message" => "Patient details deleted success",

//       ]);
//     } else {
//       return response()->json([
//         "status" => false,
//         "message" => "Patient details delete failed",
//         "data" => []

//       ]);
//     }
//   }


  // public function update_patient(Request $request)
  // {

  //   $validator = Validator::make($request->all(), [
  //     "phone" => 'unique:users,phone,' . $request->id,
  //     "aadhar_number" => 'unique:users,aadhar_number,' . $request->id
  //   ]);
  //   if ($validator->fails()) {
  //     return response()->json($validator->errors());
  //   }


  //   $image = ""; // Initialize the $image variable
  //   if (!empty($request->image)) {
  //     if (Str::contains($request->image, 'https')) {
  //       $image = $request->image;
  //     } else {
  //       $file_name = 'patient-' . rand() . '.' . 'png';
  //       $new = base64_decode($request->image);
  //       $success = file_put_contents(public_path() . '/patients/' . $file_name, $new);
  //       $image = URL::to('/public/patients/' . $file_name);
  //     }
  //   }
  //   if (empty($image)) {
  //     $image = "";
  //   }

  //   $data = [

  //     "first_name" => $request->first_name,
  //     "patient_reg_no" => $request->patient_reg_no,
  //     "surname" => $request->surname,
  //     "email" => $request->email,
  //     "phone" => $request->phone,
  //     "address" => $request->address,
  //     "dob" => $request->dob,
  //     "age" => $request->age,
  //     "gender" => $request->gender,
  //     "image" => $image,
  //     "profession" => $request->profession,
  //     "primary_contact_name" => $request->primary_contact_name,
  //     "relationship" => $request->relationship,
  //     "contact_mobile" => $request->contact_mobile,
  //     "check1" => $request->check1,
  //     "check2" => $request->check2,
  //     "check3" => $request->check3,
  //     "check4" => $request->check4,
  //     "check5" => $request->check5,
  //     "updated_at" => date("Y-m-d H:i:s")
  //   ];


  //   $user = DB::table('users')->where('id', $request->id)->update($data);

  //   if ($user) {
  //     $user_data1 = DB::table('users')->where('id', $request->id)->first();

  //     return response()->json([
  //       "status" => true,
  //       "message" => "Patient details updated successfully",
  //       "data" => $user_data1
  //     ]);
  //   } else {

  //     return response()->json([
  //       "status" => false,
  //       "message" => "Patient details updated failed",
  //       "data" => []
  //     ]);
  //   }
  // }


public function update_patient(Request $request)
{
    // Validate only Aadhar number
    $validator = Validator::make($request->all(), [
        //"patient_reg_no" => 'unique:users,patient_reg_no,' . $request->id
    ]);

    // Check if validation fails
    if ($validator->fails()) {
        // Return validation errors to the client
        // return response()->json($validator->errors());
        return response()->json([
          "status" => false,
          "message" => "The patient reg no has already been taken.",
          "data" => []
      ]);
    }

     // Initialize the $image variable
    $image = "";

    // Prepare data for update
    $data = [
      "first_name" => $request->first_name,
      "patient_reg_no" => $request->patient_reg_no,
      "surname" => $request->surname,
      "email" => $request->email,
      "phone" => $request->phone,
      "address" => $request->address,
      "dob" => $request->dob,
      "age" => $request->age,
      "gender" => $request->gender,
      "image" => $image,
      "profession" => $request->profession,
      "primary_contact_name" => $request->primary_contact_name,
      "relationship" => $request->relationship,
      "contact_mobile" => $request->contact_mobile,
      "check1" => $request->check1,
      "check2" => $request->check2,
      "check3" => $request->check3,
      "check4" => $request->check4,
      "check5" => $request->check5,
        "aadhar_number" => $request->aadhar_number,
        "updated_at" => now()->toDateTimeString()
    ];

    try {
       $user_data = DB::table('members')->where('id', $request->id)->first();

      $exist_user = DB::table('users')->where('id', '!=', $user_data->user_id)
        ->where('status', 1)
        ->where('phone', $request->phone)
        ->first();
      if ($exist_user) {
        return response()->json([
          "status" => false,
          "message" => "Patient Phone already exist to another user"
        ]);

      }
      $updated = DB::table('members')->where('id', $request->id)->update($data);
      if ($updated) {

        if ($request->phone) {
          $user_data1 = DB::table('users')->where('id', $user_data->user_id)->where('status', 1)->update([
            "phone" => $request->phone,
          ]);
        }
            return response()->json([
                "status" => true,
                "message" => "Patient details updated successfully",
                "data" => $user_data
            ]);
        } else {
            // Log the failure
            Log::error('Patient details update failed: No rows affected');

            // Return failure response
            return response()->json([
                "status" => false,
                "message" => "Patient details update failed",
                "data" => []
            ]);
        }
    } catch (\Exception $e) {
        // Log the exception
        Log::error('Patient details update failed: ' . $e->getMessage());

        // Return failure response
        return response()->json([
            "status" => false,
            "message" => "Patient details update failedd",
            "data" => []
        ]);
    }
}
}
