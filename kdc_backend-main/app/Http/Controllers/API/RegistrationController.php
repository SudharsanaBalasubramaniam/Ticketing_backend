<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\EnquiryMail;
use App\Mail\ResetPasswordAdmin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
// use Auth;

class RegistrationController extends Controller
{
    //forgot with otp
    public function forgot(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()->all()], 422);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid user',
                'data' => []
            ]);
        }

        // Generate and send OTP
        $otp = rand(100000, 999999);
        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => env('FAST2SMS_API_URL') . "?authorization=" . env('FAST2SMS_API_TOKEN') . "&variables_values=" . $otp . "&route=otp&numbers=" . urlencode($user->phone),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "cache-control: no-cache",
                ),
            )
        );
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to send OTP.',
                'data' => []
            ]);
        }

        // Update OTP field in the user table
        $user->otp = $otp;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'OTP sent successfully.',
            'data' => [
                'otp' => $otp
            ]
        ]);
    }

    public function forgotverifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'otp' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()->all()], 422);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid user',
                'data' => []
            ]);
        }

        // Verify OTP
        if ($user->otp == $request->otp) {
            return response()->json([
                'status' => true,
                'message' => 'otp verified success'
            ]);
        } else {

            return response()->json([
                'status' => false,
                'message' => 'Invalid OTP',
                'data' => []
            ]);
        }
    }
    public function forgot_change_pw(Request $request)
    {

        $validator = Validator::make($request->all(), [

            "phone" => 'required',
            "password" => 'required',
            "confirm_password" => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $phone = $request->phone;
        if (!empty($request->password == $request->confirm_password)) {
            $data = [
                "password" => Hash::make($request->password),
                "confirm_password" => Hash::make($request->confirm_password),
                "otp" => null,
            ];

            $user = DB::table('users')->where('phone', $phone)->update($data);
            if ($user) {
                return response()->json([
                    'status' => true,
                    'message' => 'password success'
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'invalid mobilenumber'
                ]);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'password and confirm_password not matched',
            ]);
        }
    }



    public function patient_registration(Request $request)
    {


        $validator = Validator::make($request->all(), [
            "first_name" => 'required',
            "phone" => 'required'
            // "phone" => 'required|unique:users'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }



        $users = DB::table('users')->get();

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
        $latestDoctor = DB::table('members')->orderBy('id', 'desc')->first();
        $doctorNumber = $latestDoctor ? intval(substr($latestDoctor->patient_reg_no, 5)) + 1 : 1;
        $doctorNumberFormatted = str_pad($doctorNumber, 4, '0', STR_PAD_LEFT);
        $doctorNumberWithPrefix = 'ONL-P' . $doctorNumberFormatted;

        if (!empty($request->image)) {

            $file_name = 'patient-' . rand() . '.' . 'png';
            $new = base64_decode($request->image);
            $success = file_put_contents(public_path() . '/patients/' . $file_name, $new);
            $image = URL::to('/public/patients/' . $file_name);
        }

        if (empty($image)) {

            $image = "";
        }

        $data = [
            "registration_number" => $registration_number,
            "patient_reg_no" => $doctorNumberWithPrefix,
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
            "check1" => $request->check1,
            "check2" => $request->check2,
            "check3" => $request->check3,
            "check4" => $request->check4,
            "check5" => $request->check5,
            // "status" => 1,
            "created_at" => date("Y-m-d H:i:s"),
            "updated_at" => date("Y-m-d H:i:s"),
            "employee_number" => $doctorNumberWithPrefix
        ];


        $user_data = DB::table('users')->where('phone', $request->phone)->first();

        if (!empty($user_data)) {
            $data['user_id'] = $user_data->id;

            $user = DB::table('members')->insert($data);


            // $user_data1 = DB::table('members')->where('phone', $request->phone)->first();
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
                "message" => "Patient registration successfully",
                "data" => $user_data1
            ]);
        } else {


            $user = DB::table('users')->insert($data);


            if ($user) {
                $user_data1 = DB::table('users')->where('phone', $request->phone)->first();


                $data['user_id'] = $user_data1->id;

                $user2 = DB::table('members')->insert($data);

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


    public function doctorStaffAsPatient(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id" => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        $user_data = DB::table('users')->where('id', $request->id)->first();
        $role = DB::table('role')->where('role_name', 'Patient')->first();


        if (!empty($user_data)) {

            $data = [
                "user_id" => $user_data->id,
                "registration_number" => $user_data->registration_number,
                "patient_reg_no" => $user_data->patient_reg_no,
                "role_id" => $role->id,
                "first_name" => $user_data->first_name,
                "surname" => $user_data->surname,
                "email" => $user_data->email,
                "phone" => $user_data->phone,
                "address" => $user_data->address,
                "password" => Hash::make('patient@kdc'),
                "dob" => $user_data->dob,
                "age" => $user_data->age,
                "gender" => $user_data->gender,
                "image" => $user_data->image,
                "profession" => $user_data->profession,
                "primary_contact_name" => $user_data->primary_contact_name,
                "relationship" => $user_data->relationship,
                "contact_mobile" => $user_data->contact_mobile,
                "verify" => date("Y-m-d H:i:s"),
                "check1" => $user_data->check1,
                "check2" => $user_data->check2,
                "check3" => $user_data->check3,
                "check4" => $user_data->check4,
                "check5" => $user_data->check5,
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s"),
                "employee_number" => $user_data->employee_number
            ];

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
                "message" => "Patient registration successfully",
            ]);
        } else {
            return response()->json([
                "status" => false,
                "message" => "User Not Found",
            ]);
        }
    }

    public function otp_registration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()->all()], 422);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid user',
                'data' => []
            ]);
        }

        // Generate and send OTP
        $otp = rand(100000, 999999);
        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => env('FAST2SMS_API_URL') . "?authorization=" . env('FAST2SMS_API_TOKEN') . "&variables_values=" . $otp . "&route=otp&numbers=" . urlencode($user->phone),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "cache-control: no-cache",
                ),
            )
        );
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to send OTP.',
                'data' => []
            ]);
        }

        // Update OTP field in the user table
        $user->otp = $otp;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'OTP sent successfully.',
            'data' => [
                'otp' => $otp
            ]
        ]);
    }

    public function verify_otp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'otp' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()->all()], 422);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid user',
                'data' => []
            ]);
        }

        // Verify OTP
        if ($user->otp == $request->otp) {
            $user->status = true;
            $user->save();
            return response()->json([
                'status' => true,
                'message' => 'otp verified success'
            ]);
        } else {

            return response()->json([
                'status' => false,
                'message' => 'Invalid OTP',
                'data' => []
            ]);
        }
    }
    public function postlogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()->all()], 422);
        }

        $credentials = $request->only('phone', 'password');

        if (Auth::attempt($credentials)) {
            $user = User::where('phone', $request->phone)->first();

            if ($user && $user->status == 1) {
                $token = $user->createToken("API TOKEN")->plainTextToken;
                $user_data1 = DB::table('members')->where('user_id', $user->id)->where('status', 1)->get();
                // $user_data1 = DB::table('members')->where('user_id', $user->id)->get();


                switch ($user->role_id) {
                    case 1:
                        // Admin
                        return response()->json([
                            "status" => true,
                            "message" => "Admin login success",
                            "data" => $user,
                            "token" => $token,
                            "members_list" => $user_data1
                        ]);
                        break;
                    case 2:
                        // Doctor
                        $user = DB::table("users")
                            ->select('users.id', 'users.aadhar_number', 'users.employee_number', 'users.registration_number', 'users.role_id', 'users.first_name', 'users.surname', 'users.email', 'users.phone', 'users.dob', 'users.age', 'users.gender', 'users.image', 'users.address', 'users.qualification', 'users.contact_mobile', 'users.status', 'users.created_at', 'users.updated_at', 'doctor_departments.id as department_id', 'doctor_departments.title as doctor_department_name')
                            ->join('doctor_departments', 'doctor_departments.id', '=', 'users.department_id')
                            ->where('users.phone', $request->phone)
                            ->first();

                        return response()->json([
                            "status" => true,
                            "message" => "Doctor login success",
                            "data" => $user,
                            "token" => $token,
                            "members_list" => $user_data1
                        ]);
                        break;
                    case 3:
                        // Staff
                        $user = DB::table("users")
                            ->select('users.id', 'users.aadhar_number', 'users.employee_number', 'users.registration_number', 'users.role_id', 'users.first_name', 'users.surname', 'users.email', 'users.phone', 'users.dob', 'users.age', 'users.gender', 'users.image', 'users.address', 'users.qualification', 'users.contact_mobile', 'users.status', 'users.created_at', 'users.updated_at', 'staff_categories.id as category_id', 'staff_categories.name as staff_category_name')
                            ->leftJoin('staff_categories', 'staff_categories.id', '=', 'users.staff_category_id')
                            ->where('users.phone', $request->phone)
                            ->first();

                        return response()->json([
                            "status" => true,
                            "message" => "Staff login success",
                            "data" => $user,
                            "token" => $token,
                            "members_list" => $user_data1
                        ]);
                        break;
                    case 4:
                        // Patient
                        $user = DB::table("users")
                            ->select('users.id', 'users.aadhar_number', 'users.employee_number', 'users.patient_reg_no', 'users.registration_number', 'users.role_id', 'users.first_name', 'users.surname', 'users.email', 'users.phone', 'users.dob', 'users.age', 'users.gender', 'users.image', 'users.address', 'users.qualification', 'users.contact_mobile', 'users.status', 'users.created_at', 'users.updated_at')
                            // 'appointments.appointment_id', 'appointments.doctor_id', 'doctor.first_name as doctor_name', 'doctor.surname as doctor_sur_name', 'appointments.appointment_date', 'appointments.appointment_time', 'appointments.appointment_status')
                            // ->join('appointments', 'appointments.patient_id', '=', 'users.id')
                            // ->join('users as doctor', 'doctor.id', '=', 'appointments.doctor_id')
                            ->where('users.phone', $request->phone)
                            ->first();

                        if ($user) {
                            if (str_starts_with($user->patient_reg_no, 'KDC-T')) {
                                return response()->json([
                                    "status" => false,
                                    "message" => "Please contact administration for login and access.",
                                    "data" => [],
                                ]);
                            }
                        }

                        return response()->json([
                            "status" => true,
                            "message" => "Patient login success",
                            "data" => $user,
                            "token" => $token,
                            "members_list" => $user_data1
                        ]);
                        break;
                    default:
                        return response()->json([
                            "status" => false,
                            "message" => "Invalid user",
                            "data" => [],
                            "members_list" => $user_data1
                        ]);
                }
            } else if ($user && $user->status != 1) {
                return response()->json([
                    "status" => false,
                    "message" => "Account is not active. Please contact administrator.",
                    "data" => []
                ]);
            }
        }

        return response()->json([
            "status" => false,
            "message" => "Invalid credentials",
            "data" => []
        ]);
    }



    public function logout()
    {

        $user = request()->user(); //or Auth::user()
        $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();
        return response()->json([
            "status" => true,
            "message" => "Logout success"
        ]);
    }

    public function get_notificationsList(Request $request)
    {

        $notificationsList = DB::table('notification')->get();
        if (empty($notificationsList)) {
            return response()->json([
                "notifications" => []
            ]);
        } else {
            $notifications = [];
            foreach ($notificationsList as $notify) {
                $patient = DB::table('members')->where("id", $notify->patient_id)->first();
                // return $patient
                if (!empty($patient)) {

                    $data = json_encode($notify, true);
                    $data1 = (array) json_decode($data);
                    $value = $data1[$request->role];

                    if (empty($value)) {
                        $patient->doctor_status = $notify->doctor_status;
                        $patient->staff_status = $notify->staff_status;
                        $patient->admin_status = $notify->admin_status;
                        $patient->notification_id = $notify->id;
                        $notifications[] = $patient;
                    } else {
                        $decodedList = (array) json_decode($value);
                        if (!in_array($request->id, $decodedList)) {
                            $patient->doctor_status = $notify->doctor_status;
                            $patient->staff_status = $notify->staff_status;
                            $patient->admin_status = $notify->admin_status;
                            $patient->notification_id = $notify->id;
                            $notifications[] = $patient;
                        }
                    }
                }
            }


            return response()->json([
                "notifications" => $notifications
            ]);
        }
    }


    public function change_notification_status(Request $request)
    {

        if (empty($request->notification_id)) {
            $notification = DB::table('notification')
                ->get();

            if ($notification) {
                foreach ($notification as $notification) {
                    $data = json_encode($notification, true);
                    $data1 = (array) json_decode($data);
                    $value = $data1[$request->role];
                    if (empty($value)) {
                        $data = DB::table('notification')->where('id', $notification->id)
                            ->update([
                                $request->role => [$request->id],
                            ]);
                    } else {
                        $arrayIds = (array) json_decode($value);
                        if (in_array($request->id, $arrayIds, TRUE)) {
                        } else {
                            $arrayIds[] = $request->id;
                        }
                        $data = DB::table('notification')->where('id', $notification->id)
                            ->update([
                                $request->role => $arrayIds,
                            ]);
                    }
                }

                return response()->json([
                    "status" => true,
                    "message" => "Status updated successfully"
                ]);
            }
        } else {
            $notifications = DB::table('notification')
                ->where('id', $request->notification_id)->get();
            foreach ($notifications as $notification) {
                $data = json_encode($notification, true);
                $data1 = (array) json_decode($data);
                $value = $data1[$request->role];
                if (empty($value)) {
                    $data = DB::table('notification')->where('id', $notification->id)
                        ->update([
                            $request->role => [$request->id],
                        ]);
                } else {
                    $arrayIds = (array) json_decode($value);
                    if (in_array($request->id, $arrayIds, TRUE)) {
                    } else {
                        $arrayIds[] = $request->id;
                    }
                    $data = DB::table('notification')->where('id', $notification->id)
                        ->update([
                            $request->role => $arrayIds,
                        ]);
                }
            }

            return response()->json([
                "status" => true,
                "message" => "Status updated successfully"
            ]);
        }
    }
    //mail with enquiry notification

    // public function store_enquiry(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'patient_id' => 'required'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json($validator->errors());
    //     }

    //     try {
    //         $data = [
    //             "date" => $request->date,
    //             "patient_id" => $request->patient_id,
    //             "enquiry" => $request->enquiry,
    //             "doctor_status" => "waiting",
    //             "staff_status" => "waiting",
    //             "admin_status" => "waiting",
    //             "created_at" => now(),
    //             "updated_at" => now()
    //         ];

    //         $save_enquiry = DB::table('enquiry')->insert($data);

    //         if ($save_enquiry) {
    //             $enquiry = DB::table('enquiry')->orderBy('id', 'DESC')->first();
    //             $patient = DB::table('users')->where("id", $enquiry->patient_id)->first();

    //             Mail::to('bhuvanamic@gmail.com')->send(new EnquiryMail((array) $enquiry));


    //             return response()->json([
    //                 "status" => true,
    //                 "message" => "Enquiry inserted successfully. Enquiry Mail sent successfully.",
    //                 "data" => $enquiry
    //             ]);
    //         } else {
    //             return response()->json([
    //                 "status" => false,
    //                 "message" => "EnquiryMail not sent",
    //             ]);
    //         }
    //     } catch (\Exception $e) {
    //         \Log::error('Exception occurred: ' . $e->getMessage());
    //         return response()->json(['status' => false, 'message' => 'An error occurred. Something went wrong.']);
    //     }
    // }
    public function store_enquiry(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'patient_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        // try {
        $data = [
            "date" => $request->date,
            "patient_id" => $request->patient_id,
            "enquiry" => $request->enquiry,
            "doctor_status" => "waiting",
            "staff_status" => "waiting",
            "admin_status" => "waiting",
            "created_at" => now(),
            "updated_at" => now()
        ];

        $save_enquiry = DB::table('enquiry')->insert($data);

        if ($save_enquiry) {
            $enquiry = DB::table('enquiry')->orderBy('id', 'DESC')->first();

            // Fetch patient name from the users table
            $patient = DB::table('members')->where("id", $enquiry->patient_id)->first();

            // Check if patient data is available before sending the email
            if ($patient) {
                // Pass both $enquiry and $patient to EnquiryMail
                Mail::to('Kirthikadentalcare@gmail.com')->send(new EnquiryMail((array) $enquiry, (array) $patient));
                // Mail::to($patient->email)->send(new EnquiryMail((array) $enquiry, (array) $patient));

                return response()->json([
                    "status" => true,
                    "message" => "Enquiry inserted successfully. Enquiry Mail sent successfully.",
                    "data" => $enquiry
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Patient not found",
                ]);
            }
        } else {
            return response()->json([
                "status" => false,
                "message" => "EnquiryMail not sent",
            ]);
        }
        // } catch (\Exception $e) {
        //     \Log::error('Exception occurred: ' . $e->getMessage());
        //     return response()->json(['status' => false, 'message' => 'An error occurred. Something went wrong.']);
        // }
    }


    public function get_enquiry_list(Request $request)
    {

        $notificationsList = DB::table('enquiry')->get();
        if (empty($notificationsList)) {
            return response()->json([
                "notifications" => []
            ]);
        } else {
            $notifications = [];
            foreach ($notificationsList as $notify) {
                $patient = DB::table('members')->where("id", $notify->patient_id)->first();
                if (!empty($patient)) {

                    $data = json_encode($notify, true);
                    $data1 = (array) json_decode($data);
                    $value = $data1[$request->role];
                    if (empty($value)) {
                        $patient->date = $notify->date;
                        $patient->enquiry = $notify->enquiry;
                        $patient->patient_id = $notify->patient_id;
                        $patient->doctor_status = $notify->doctor_status;
                        $patient->staff_status = $notify->staff_status;
                        $patient->admin_status = $notify->admin_status;
                        $patient->notification_id = $notify->id;
                        $notifications[] = $patient;
                    } else {
                        $decodedList = (array) json_decode($value);
                        if (!in_array($request->id, $decodedList)) {
                            $patient->date = $notify->date;
                            $patient->enquiry = $notify->enquiry;
                            $patient->patient_id = $notify->patient_id;
                            $patient->doctor_status = $notify->doctor_status;
                            $patient->staff_status = $notify->staff_status;
                            $patient->admin_status = $notify->admin_status;
                            $patient->notification_id = $notify->id;
                            $notifications[] = $patient;
                        }
                    }
                }
            }


            return response()->json([
                "notifications" => $notifications
            ]);
        }
    }
    public function change_enquiry_status(Request $request)
    {

        if (empty($request->notification_id)) {
            $notification = DB::table('enquiry')->get();

            if ($notification) {
                foreach ($notification as $notification) {
                    $data = json_encode($notification, true);
                    $data1 = (array) json_decode($data);
                    $value = $data1[$request->role];

                    if (empty($value)) {
                        $data = DB::table('enquiry')->where('id', $notification->id)
                            ->update([
                                $request->role => [$request->id],
                            ]);
                    } else {
                        $arrayIds = (array) json_decode($value);
                        if (in_array($request->id, $arrayIds, TRUE)) {
                        } else {
                            $arrayIds[] = $request->id;
                        }
                        $data = DB::table('enquiry')->where('id', $notification->id)
                            ->update([
                                $request->role => $arrayIds,
                            ]);
                    }
                }

                return response()->json([
                    "status" => true,
                    "message" => "Status updated successfully"
                ]);
            }
        } else {
            $notifications = DB::table('enquiry')
                ->where('id', $request->notification_id)->get();
            foreach ($notifications as $notification) {
                $data = json_encode($notification, true);
                $data1 = (array) json_decode($data);
                $value = $data1[$request->role];
                if (empty($value)) {
                    $data = DB::table('enquiry')->where('id', $notification->id)
                        ->update([
                            $request->role => [$request->id],
                        ]);
                } else {
                    $arrayIds = (array) json_decode($value);
                    if (in_array($request->id, $arrayIds, TRUE)) {
                    } else {
                        $arrayIds[] = $request->id;
                    }
                    $data = DB::table('enquiry')->where('id', $notification->id)
                        ->update([
                            $request->role => $arrayIds,
                        ]);
                }
            }

            return response()->json([
                "status" => true,
                "message" => "Status updated successfully"
            ]);
        }
    }

    //  public function get_out_patient_enquiry_list(Request $request)
    // {

    //     $notificationsList = DB::table('out_patient_enquiry')->get();
    //     if (empty($notificationsList)) {
    //         return response()->json([
    //             "notifications" => []
    //         ]);
    //     } else {
    //         $notifications = [];
    //         foreach ($notificationsList as $notify) {

    //             $data = json_encode($notify, true);
    //             $data1 = (array)json_decode($data);
    //             $value = $data1[$request->role];


    //             if (empty($value)) {
    //                 $dd["enquiry_id"] = $notify->id;
    //                 $dd["patient_name"] = $notify->patient_name;
    //                 $dd["mobile_number"] = $notify->mobile_number;
    //                 $dd["appointment_date"] = $notify->appointment_date;
    //                 $dd["enquiry"] = $notify->enquiry;
    //                 $dd["feed_back"] = $notify->feed_back;
    //                 $dd["doctor_status"] = $notify->doctor_status;
    //                 $dd["staff_status"] = $notify->staff_status;
    //                 $dd["admin_status"] = $notify->admin_status;
    //                 $notifications[] = $dd;
    //             } else {
    //                 $decodedList = (array)json_decode($value);
    //                 if (!in_array($request->id, $decodedList)) {
    //                     $dd["enquiry_id"] = $notify->id;
    //                     $dd["patient_name"] = $notify->patient_name;
    //                     $dd["mobile_number"] = $notify->mobile_number;
    //                     $dd["appointment_date"] = $notify->appointment_date;
    //                     $dd["enquiry"] = $notify->enquiry;
    //                     $dd["feed_back"] = $notify->feed_back;
    //                     $dd["doctor_status"] = $notify->doctor_status;
    //                     $dd["staff_status"] = $notify->staff_status;
    //                     $dd["admin_status"] = $notify->admin_status;
    //                     $notifications[] = $dd;
    //                 }
    //             }
    //         }


    //         return response()->json([
    //             "notifications" => $notifications
    //         ]);
    //     }
    // }
    public function get_out_patient_enquiry_list(Request $request)
    {

        $notificationsList = DB::table('out_patient_enquiry')->get();
        if (empty($notificationsList)) {
            return response()->json([
                "notifications" => []
            ]);
        } else {
            $notifications = [];
            foreach ($notificationsList as $notify) {

                $data = json_encode($notify, true);
                $data1 = (array) json_decode($data);
                $value = $data1[$request->role];


                if (empty($value)) {
                    $dd["enquiry_id"] = $notify->id;
                    $dd["patient_name"] = $notify->patient_name;
                    $dd["service_name"] = $notify->service_name;
                    $dd["mobile_number"] = $notify->mobile_number;
                    $dd["appointment_date"] = $notify->appointment_date;
                    $dd["enquiry"] = $notify->enquiry;
                    $dd["appointment_time"] = $notify->appointment_time;
                    $dd["doctor_status"] = $notify->doctor_status;
                    $dd["staff_status"] = $notify->staff_status;
                    $dd["admin_status"] = $notify->admin_status;
                    $dd["created_at"] = $notify->created_at;
                    $dd["updated_at"] = $notify->updated_at;
                    $notifications[] = $dd;
                } else {
                    $decodedList = (array) json_decode($value);
                    // if (!in_array($request->id, $decodedList)) {
                    $dd["enquiry_id"] = $notify->id;
                    $dd["patient_name"] = $notify->patient_name;
                    $dd["service_name"] = $notify->service_name;
                    $dd["mobile_number"] = $notify->mobile_number;
                    $dd["appointment_date"] = $notify->appointment_date;
                    $dd["enquiry"] = $notify->enquiry;
                    $dd["appointment_time"] = $notify->appointment_time;
                    $dd["doctor_status"] = $notify->doctor_status;
                    $dd["staff_status"] = $notify->staff_status;
                    $dd["admin_status"] = $notify->admin_status;
                    $dd["created_at"] = $notify->created_at;
                    $dd["updated_at"] = $notify->updated_at;
                    $notifications[] = $dd;
                    // }
                }
            }


            return response()->json([
                "notifications" => $notifications
            ]);
        }
    }
    public function change_out_patient_enquiry_status(Request $request)
    {

        if (empty($request->notification_id)) {
            $notifications = DB::table('out_patient_enquiry')->get();

            if ($notifications) {
                foreach ($notifications as $notification) {
                    $data = json_encode($notification, true);
                    $data1 = (array) json_decode($data);
                    $value = $data1[$request->role];

                    if (empty($value)) {
                        $data = DB::table('out_patient_enquiry')->where('id', $notification->id)
                            ->update([
                                $request->role => [$request->id],
                            ]);
                    } else {
                        $arrayIds = (array) json_decode($value);

                        if (in_array($request->id, $arrayIds, TRUE)) {
                        } else {
                            $arrayIds[] = $request->id;
                        }

                        $data = DB::table('out_patient_enquiry')->where('id', $notification->id)
                            ->update([
                                $request->role => $arrayIds,
                            ]);
                    }
                }

                return response()->json([
                    "status" => true,
                    "message" => "Status updated successfully"
                ]);
            }
        } else {
            $notifications = DB::table('out_patient_enquiry')
                ->where('id', $request->notification_id)->get();
            foreach ($notifications as $notification) {
                $data = json_encode($notification, true);
                $data1 = (array) json_decode($data);
                $value = $data1[$request->role];
                if (empty($value)) {
                    $data = DB::table('out_patient_enquiry')->where('id', $notification->id)
                        ->update([
                            $request->role => [$request->id],
                        ]);
                } else {
                    $arrayIds = (array) json_decode($value);
                    if (in_array($request->id, $arrayIds, TRUE)) {
                    } else {
                        $arrayIds[] = $request->id;
                    }
                    $data = DB::table('out_patient_enquiry')->where('id', $notification->id)
                        ->update([
                            $request->role => $arrayIds,
                        ]);
                }
            }
            return response()->json([
                "status" => true,
                "message" => "Status updated successfully"
            ]);
        }
    }
}
