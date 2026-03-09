<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class GlobalController extends Controller
{

  

      public function global(Request $request)
      {


        $validator = Validator::make($request->all(), [
          
          'phone' => 'required'
      ]);
      if ($validator->fails()) {
        return response()->json($validator->errors());
    }

    try {
         $search = DB::table('members')
        ->where('phone','LIKE','%'.$request->phone.'%')
        ->get();

        if(!$search->isEmpty()){
          $user = DB::table('members')->where('phone','LIKE','%'.$request->phone.'%')->get();

          return response()->json([
              "status" => true,
              "message" => "success",
              "data" => $user
            ]);
        }
        else{
          return response()->json([
            "status" => false,
            "message" => "failed",
        ]);
        }
      
    } catch (\Exception $e) {

        \Log::error('Exception occurred: ' . $e->getMessage());
        return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
    }
  }
  }       



        

          // // Get the four numbers from the request
          // $fourNumbers = $request->input('four_numbers');
  
          // // Query the user table for records where the 'phone' column contains the four numbers
          // $users = User::where('phone', 'LIKE', "%$fourNumbers%")->get();
  
          // // Return the user data as JSON response
          // return response()->json(['data' => $users]);
 
//   public function global ($phone)

//   {
//     // Perform necessary string operations on the mobile number if required
//     // For example, removing any special characters or formatting the number

//     $user = User::where('phone', $phone)->first();

//     if ($user) {
//         return response()->json($user, 200);
//     } else {
//         return response()->json(['message' => 'User not found'], 404);
//     }
// }
//   // public function global ($phone)
  // {
  //   $global= DB->select('user.id, user.firstName, user.email')->get();
  //   if (stripos($global, 'phone')) { 
  //     echo "PHP string contains 'Hire'";
  //   } else {
  //     echo "PHP string does not contain 'Hire";
  //   }
  // }

  // public function global() {
  //     $phone = $this->phone;

  //     $ac = substr($phone, 0, 3);
  //     $prefix = substr($phone, 3, 3);
  //     $suffix = substr($phone, 6);

  //     return "({$ac}) {$prefix}-{$suffix}";
  // }

  //   public function index()
  //   {
  //    $admin_applist = DB::table('user')
  //   ->select('id','Fname', 'Lname', 'phone', 'email')
  //   ->get();
  //   //  return view('/admin')->with(compact('admin_applist'->phone_formatted));
  //   }
  // public function global($phoneNumber)
  // {
  //     // Query the global search API using the $phoneNumber
  //     $relatedPhoneNumber = $this->queryGlobalSearchAPI($phoneNumber);

  //     // Retrieve the user data from the user table
  //     $user = User::where('phone', $relatedPhoneNumber)->first();

  //     if ($user) {
  //         // Return the user data
  //         return response()->json($user);
  //     } else {
  //         // Handle case when no user is found
  //         return response()->json(['message' => 'No user found.'], 404);
  //     }
  // }

  // private function queryGlobalSearchAPI($phoneNumber)
  // {
  //     // Implement the logic to query the global search API
  //     // and retrieve the related phone number
  // }
  // public function global (Request $request)
  // {
  //     // $validator = Validator::make($request->all(),[
  //     //     "phone" => 'required'
  //     //   ]);

  //     // if ($validator->fails()) {
  //     //     return response()->json($validator->errors());
  //     // }

  //     try {

  //         // $role = DB::table('user')->where('phone', $request->phone)->first();


  //         $data =
  //             [
  //                 // "role_id" => $role->id,

  //                 "phone" => $request->phone,

  //             ];


  //             $show = DB::table('users')->get($data);


  //         if ($show) {

  //             $user = DB::table('users')->where('phone','=', $request->phone)->first();

  //             return response()->json([
  //                 "status" => true,
  //                 "message" => "Register success",
  //                 "data" => $user
  //             ]);
  //         } else {
  //             return response()->json([
  //                 "status" => false,
  //                 "message" => "User status updated failed",
  //             ]);
  //         }
  //     } catch (\Exception $e) {

  //         \Log::error('Exception occurred: ' . $e->getMessage());
  //         return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
  //     }
  // }






// public function global(Request $request)
// {
//     // Get the phone number entered by the user
//     $phone = $request->input('phone');

//     // Query the global phone number search API using the $phoneNumber

//     // Assuming the API response contains the related mobile number
//     $relatedphone = $this->queryGlobalPhoneAPI($phone);

//     // Retrieve the related mobile number from the user table
//     $user = \App\User::where('phone', $relatedphone)->first();

//     if ($user) {
//         // Return the related mobile number
//         return response()->json(['phone' => $user->phone]);
//     } else {
//         // Handle case when no related mobile number is found
//         return response()->json(['message' => 'No related mobile number found.']);
//     }
// }

// private function queryGlobalPhoneAPI($phone)
// {
//     // Implement the logic to query the global phone number search API
//     // and retrieve the related mobile number
// }
