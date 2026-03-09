<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SummaryController extends Controller
{
    

  // public function store_payment_trans(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         "patient_id" => 'required',
    //         "paid_amt" => 'required',
    //         "status" => 'required',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json($validator->errors());
    //     }

    //     try {
    //         $invoice = '#PAY - ' . rand();
    //         //    $current_date = date("Y-m-d");

    //         // Retrieve the total amount from the patient_treatment JSON field
    //         $totalAmount = DB::table('treatment_plan_mapping')
    //             ->join('appointments', 'appointments.service_id', '=', 'treatment_plan_mapping.id')
    //             ->where("appointments.appointment_status", "=", "Completed")
    //             ->where('treatment_plan_mapping.patient_id', $request->patient_id)
    //             ->where('appointments.appointment_date', $request->date)
    //             ->sum('patient_treatment->discountPrice');


    //         // Subtract all paid amounts with the same payment ID from the total amount
    //         $total_paid_amt = DB::table('payment_transaction')
    //             ->where('patient_id', $request->patient_id)
    //             ->sum('paid_amt');


    //         $prev_balance1 = DB::table('payment_transaction')->where('payment_transaction.patient_id', $request->patient_id)->orderBy('balance', 'DESC')->first();


    //         if (!empty($prev_balance1)) {
    //             $prev_balance = $prev_balance1->balance;
    //             $totalAmount += $prev_balance;
    //             //    return($totalAmount);
    //             //    exit;
    //         }

    //         $balance = $totalAmount - $request->paid_amt;
    //         $balance -= $total_paid_amt;
    //         $data = [
    //             "invoice" => $invoice,
    //             "patient_id" => $request->patient_id,
    //             "already_paid" =>  $total_paid_amt,
    //             "paid_amt" => $request->paid_amt,
    //             "balance" => $balance,
    //             "status" => $request->status,
    //             "created_at" => date("Y-m-d H:i:s"),
    //             "updated_at" => date("Y-m-d H:i:s")
    //         ];

    //         $totalAmount = DB::table('treatment_plan_mapping')
    //             ->join('appointments', 'appointments.service_id', '=', 'treatment_plan_mapping.id')
    //             ->where("appointments.appointment_status", "=", "Completed")->first();

    //         // if(empty($totalAmount)){
    //         $pres_sub = DB::table('payment_transaction')->insert($data);

    //         if ($pres_sub) {
    //             $pres_sub = DB::table('payment_transaction')
    //                 ->select(
    //                     // DB::raw('DISTINCT payment_transaction.id'),
    //                     'payment_transaction.id',
    //                     'payment_transaction.invoice',
    //                     'payment_transaction.patient_id',
    //                     'payment_transaction.already_paid',
    //                     'payment_transaction.paid_amt',
    //                     'payment_transaction.balance',
    //                     'payment_transaction.status',
    //                     'treatment_plan_mapping.patient_treatment',
    //                     'appointments.appointment_id',
    //                     'appointments.service_id',
    //                     'appointments.appointment_status',
    //                     'appointments.created_at',
    //                     'appointments.updated_at',

    //                 )
    //                 ->join('treatment_plan_mapping', 'treatment_plan_mapping.patient_id', '=', 'payment_transaction.patient_id')
    //                 ->join('appointments', 'appointments.service_id', '=', 'treatment_plan_mapping.id')
    //                 ->where("appointments.appointment_status", "=", "Completed")
    //                 ->where("invoice", $invoice)
    //                 ->get();

    //             return response()->json([
    //                 "status" => true,
    //                 "message" => "Payment transaction saved successfully.",
    //                 "total_amount" => $totalAmount,
    //                 "data" => $pres_sub
    //             ]);
    //         } else {
    //             return response()->json([
    //                 "status" => false,
    //                 "message" => "Payment transaction save failed.",
    //             ]);
    //         }
    //         // }
    //     } catch (\Exception $e) {
    //         \Log::error('Exception occurred: ' . $e->getMessage());
    //         return response()->json(['status' => false, 'message' => 'An error occurred. Something went wrong.', "error" => $e->getMessage()]);
    //     }

}
