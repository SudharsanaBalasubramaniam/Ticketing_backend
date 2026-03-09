<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use  Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PaymentController extends Controller
{

    public function list_payment_trans(Request $request)
    {
        try {
            $totalAmount = DB::table('treatment_plan_mapping')
                ->select(
                    'treatment_plan_mapping.id',
                    'treatment_plan_mapping.treatment_id',
                    'treatment_plan_mapping.patient_treatment',
                    'treatment_plan_mapping.patient_id',
                    'treatment_plan_mapping.doctor_id',
                    'treatment_plan_mapping.status',
                    'appointments.id as appointment_id',
                    'appointments.tooth_id',
                    'appointments.method_id',
                    'appointments.service_id',
                    'appointments.treatment_name',
                    'appointments.appointment_date',
                    'appointments.appointment_time',
                    'appointments.appointment_status',
                    'appointments.created_at',
                    'appointments.inprogress_date',
                    'appointments.complete_date'
                )
                ->join('appointments', 'appointments.service_id', '=', 'treatment_plan_mapping.id')
                ->whereIn("appointments.appointment_status", ["Open", "Completed", "Inprogress", "Next", "Rescheduled"])
                ->where('treatment_plan_mapping.patient_id', $request->patient_id)
                ->get();

            // Group by service_id to maintain one payment record per treatment
            $serviceGroups = [];
            foreach ($totalAmount as $item) {
                if (!isset($serviceGroups[$item->service_id])) {
                    $serviceGroups[$item->service_id] = [];
                }
                $serviceGroups[$item->service_id][] = $item;
            }

            $data = [];

            foreach ($serviceGroups as $service_id => $users_value) {
                // Get first appointment date for this service
                $firstAppointment = collect($users_value)->sortBy('created_at')->first();
                $vars = $firstAppointment->appointment_date;
                $totalValue = 0;
                $uniqueServices = [];
                
                // Group by service_id and take only the latest appointment per service
                $serviceGroups = [];
                foreach ($users_value as $d) {
                    if (!isset($serviceGroups[$d->service_id])) {
                        $serviceGroups[$d->service_id] = [];
                    }
                    $serviceGroups[$d->service_id][] = $d;
                }
                
                // For each service, take only the latest appointment
                foreach ($serviceGroups as $serviceId => $appointments) {
                    // Sort by appointment_id descending to get the latest
                    usort($appointments, function($a, $b) {
                        return $b->appointment_id - $a->appointment_id;
                    });
                    
                    $latestAppointment = $appointments[0];
                    $uniqueServices[] = $latestAppointment;
                    
                    $enC = json_decode($latestAppointment->patient_treatment);
                    if (!empty($enC->discountPrice)) {
                        $totalValue += ($enC->discountPrice);
                    }
                }

                // Get payment for this service using service_id
                $pay = DB::table('payment_transaction')
                    ->where('patient_id', $request->patient_id)
                    ->where('service_id', $service_id)
                    ->get();

                $data1['payment_id'] = null;
                $data1['already_paid_amount'] = null;
                $data1['consultation_fee'] = null;
                $data1['discount_type'] = null;
                $data1['discount'] = null;
                $data1['discount_amt'] = null;
                $data1['balance_amount'] = null;
                $data1['appointment_id'] = null;
                $data1['date'] = null;
                $data1['paid_amt'] = null;
                $data1['payment_created_at'] = null;
                $data1['payment_updated_at'] = null;


                if ($pay->count() > 0) {
                    $already_paid = DB::table('payment_transaction')
                        ->where('patient_id', $request->patient_id)
                        ->where('service_id', $service_id)
                        ->sum('paid_amt');
                    
                    \Log::info('Payment calculation for date: ' . $vars);
                    \Log::info('Patient ID: ' . $request->patient_id);
                    \Log::info('Payment count: ' . $pay->count());
                    \Log::info('Already paid sum: ' . $already_paid);
                    \Log::info('All payments: ' . json_encode($pay));

                    $lastPayment = $pay->last();
                    $data1['payment_id'] = $lastPayment->id ?? "";
                    $data1['date'] = $lastPayment->date;
                    $data1['already_paid_amount'] = $already_paid;
                    $data1['paid_amt'] = $lastPayment->paid_amt;
                    $data1['consultation_fee'] = $lastPayment->consultation_fee;
                    $data1['appointment_id'] = $lastPayment->appointment_id;
                    $data1['discount_type'] = $lastPayment->discount_type;
                    $data1['discount'] = $lastPayment->discount;
                    $data1['discount_amt'] = $lastPayment->discount_amt;
                    $data1['balance_amount'] = $lastPayment->balance;
                    $data1['payment_created_at'] = $lastPayment->created_at ?? now();
                    $data1['payment_updated_at'] = $lastPayment->updated_at ?? now();

                    // Step 1: Convert JSON string to array
                    $appointmentIds = json_decode($data1['appointment_id'], true);

                    // Step 2: Fetch appointments
                    $appointments = DB::table('appointments')
                        ->whereIn('id', $appointmentIds)
                        ->get();

                    // Step 3: Collect doctor_ids
                    $doctorIds = $appointments->pluck('doctor_id')->unique();

                    // Step 4: Get doctor names from users table
                    $doctors = DB::table('users')
                        ->whereIn('id', $doctorIds)
                        ->select('first_name', 'surname')
                        ->get();
                }

                $data1['doctors'] = $doctors ?? [];
                $data1['total'] = $totalValue;
                $data1['updated_at'] = $vars;
                $data1['services'] = $uniqueServices;
                $data[] = $data1;
            }
            // Don't group by date - return each service as separate row
            $transformedData = [];
            foreach ($data as $item) {
                $date = isset($item['updated_at']) ? Carbon::parse($item['updated_at'])->format('Y-m-d') : null;
                $transformedData[] = [
                    'date' => $date,
                    'payment' => [$item], // Each service gets its own row
                ];
            }


            if ($data) {
                return response()->json([
                    "status" => true,
                    "message" => "Payment transaction fetched successfully.",
                    "data" => $transformedData
                ]);
            } else {
                // Fallback: Check for orphaned payments without treatment plan links
                $orphanedPayments = DB::table('payment_transaction')
                    ->where('patient_id', $request->patient_id)
                    ->get();
                
                if ($orphanedPayments->count() > 0) {
                    $fallbackData = [];
                    foreach ($orphanedPayments as $payment) {
                        $fallbackData[] = [
                            'date' => $payment->date,
                            'payment' => [[
                                'payment_id' => $payment->id,
                                'already_paid_amount' => $payment->paid_amt,
                                'consultation_fee' => $payment->consultation_fee,
                                'discount_type' => $payment->discount_type,
                                'discount' => $payment->discount,
                                'discount_amt' => $payment->discount_amt,
                                'balance_amount' => $payment->balance,
                                'appointment_id' => $payment->appointment_id,
                                'date' => $payment->date,
                                'paid_amt' => $payment->paid_amt,
                                'payment_created_at' => $payment->created_at ?? now(),
                                'payment_updated_at' => $payment->updated_at ?? now(),
                                'doctors' => [],
                                'total' => $payment->paid_amt,
                                'updated_at' => $payment->date,
                                'services' => []
                            ]]
                        ];
                    }
                    
                    return response()->json([
                        "status" => true,
                        "message" => "Payment transaction fetched successfully (fallback mode).",
                        "data" => $fallbackData
                    ]);
                }
                
                return response()->json([
                    "status" => false,
                    "message" => "Payment transaction failed.",
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Something went wrong.', "error" => $e->getMessage()]);
        }
    }
    public function store_payment_trans(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "patient_id" => 'required',
            "date" => 'required',
            'appointment_id' => 'required',
            // "paid_amt" => 'required',
            "status" => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {
            $invoice = '#PAY - ' . rand();

            $already_paid = DB::table('payment_transaction')
                ->where('patient_id', $request->patient_id)
                ->where('service_id', $request->service_id)
                ->sum('paid_amt');
            if (empty($already_paid)) {
                $already_paid = 0;
            }


            $data = [
                "invoice" => $invoice,
                "patient_id" => $request->patient_id,
                "date" => $request->date,
                "appointment_id" => json_encode($request->appointment_id),
                "treatment_id" => $request->treatment_id,
                "service_id" => $request->service_id,
                "consultation_fee" => $request->consultation_fee,
                "discount_type" => $request->discount_type,
                "discount" =>  $request->discount,
                "discount_amt" => floor($request->discount_amt),
                "already_paid" => $request->already_paid,
                // "already_paid" => $already_paid,
                "paid_amt" => $request->paid_amt,
                "balance" => $request->balance,
                "status" => $request->status,
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s")
            ];



            $pres_sub = DB::table('payment_transaction')->insert($data);

            if ($pres_sub) {
                $pay = DB::table('payment_transaction')->where('date', $request->date)->orderBy('created_at', 'DESC')->first();

                return response()->json([
                    "status" => true,
                    "message" => "Payment transaction saved successfully.",
                    "data" => $pay
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Payment transaction save failed.",
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Something went wrong.', "error" => $e->getMessage()]);
        }
    }
    // public function update_payment_trans(Request $request)
    // {
    //     try {
    //         $data = [
    //             "discount_type" => $request->discount_type,
    //             "discount" =>  $request->discount,
    //             "discount_amt" => floor($request->discount_amt),
    //             "balance" => $request->balance,
    //             "paid_amt" => $request->paid_amt,
    //             // "discount" => $request->discount ?? "",
    //             "updated_at" => date("Y-m-d H:i:s")
    //         ];

    //         $pres_sub = DB::table('payment_transaction')->where('id', $request->id)->update($data);

    //         if ($pres_sub) {
    //             $pay = DB::table('payment_transaction')->where('id', $request->id)->first();


    //             return response()->json([
    //                 "status" => true,
    //                 "message" => "Payment transaction updated successfully.",
    //                 "data" => $pay
    //             ]);
    //         } else {
    //             return response()->json([
    //                 "status" => false,
    //                 "message" => "Payment transaction update failed.",
    //             ]);
    //         }
    //     } catch (\Exception $e) {
    //         \Log::error('Exception occurred: ' . $e->getMessage());
    //         return response()->json(['status' => false, 'message' => 'An error occurred. Something went wrong.', "error" => $e->getMessage()]);
    //     }
    // }

    public function update_payment_trans(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "patient_id" => 'required',
            "date" => 'required',
            'id' => 'required',
            // "paid_amt" => 'required',
            "status" => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {
            // Normalize appointment_id to match DB format (e.g., '[494]')
            $appointmentId = $request->id;

            // If it's an array, convert to JSON string (e.g., [494] => "[494]")
            if (is_array($appointmentId)) {
                $appointmentIdStr = json_encode($appointmentId);
            } else {
                // If it's already a string, check if it's numeric and wrap in brackets if needed
                if (is_numeric($appointmentId)) {
                    $appointmentIdStr = '[' . $appointmentId . ']';
                } else {
                    $appointmentIdStr = $appointmentId;
                }
            }
            $today = Carbon::today()->toDateString();

            // Find the payment transaction by patient_id and appointment_id string
            $payment = DB::table('payment_transaction')
                ->where('patient_id', $request->patient_id)
                // ->where('appointment_id', $appointmentIdStr)
                ->where('id', $request->edit_payment_id)
                ->orderBy('id', 'desc')
                ->first();

            if (!$payment) {
                return response()->json([
                    "status" => false,
                    "message" => "Payment transaction not found for given patient and appointment.",
                ]);
            }

            $operationSuccess = false;
            $latestId = null;

            // Always insert new payment record for additional payments
            $invoice = '#PAY - ' . rand();

            $data = [
                "invoice" => $invoice,
                "patient_id" => $request->patient_id,
                "date" => $request->date,
                "appointment_id" => json_encode($request->id),
                "treatment_id" => $request->treatment_id,
                "service_id" => $request->service_id,
                "consultation_fee" => $request->consultation_fee,
                "discount_type" => $request->discount_type,
                "discount" =>  $request->discount,
                "discount_amt" => floor($request->discount_amt),
                "already_paid" => $request->already_paid,
                "paid_amt" => $request->paid_amt,
                "balance" => $request->balance,
                "status" => $request->status,
                "created_at" => now(),
                "updated_at" => now()
            ];

            $latestId = DB::table('payment_transaction')->insertGetId($data);

            if ($latestId) {
                $operationSuccess = true;
            }

            // if ($updated) {
            //     $pay = DB::table('payment_transaction')->where('id', $payment->id)->first();
            if ($operationSuccess && $latestId) {
                $pay = DB::table('payment_transaction')->where('id', $latestId)->first();
                
                $already_paid = DB::table('payment_transaction')
                    ->where('patient_id', $request->patient_id)
                    ->where('service_id', $request->service_id)
                    ->sum('paid_amt');
                
                return response()->json([
                    "status" => true,
                    "message" => "Payment transaction updated successfully.",
                    "data" => $pay,
                    "total_paid" => $already_paid
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Payment transaction update failed.",
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Something went wrong.',
                "error" => $e->getMessage()
            ]);
        }
    }
    //doctor price payment and update
    public function store_consultant_payment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "doctor_id" => 'required',
            "paid_amt" => 'required',
            "status" => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {

            $invoice = '#PAY - ' . rand();

            $already_paid = DB::table('consultant_payment')
                ->where('doctor_id', $request->doctor_id)
                ->where('month', $request->month)
                ->sum('paid_amt');



            if (empty($already_paid)) {
                $already_paid = 0;
            }


            $total_pay_amt = $request->total_amount - $already_paid;

            $balance =  $total_pay_amt - $request->paid_amt;

            $data = [
                "invoice" => $invoice,
                "doctor_id" => $request->doctor_id,
                "month" => $request->month,
                "total_amount" => $request->total_amount,
                "total_pay_amt" => $total_pay_amt,
                "already_paid" => $already_paid,
                "paid_amt" => $request->paid_amt,
                "balance" => $balance,
                "status" => $request->status,
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s")
            ];



            $pres_sub = DB::table('consultant_payment')->insert($data);

            if ($pres_sub) {
                $pay = DB::table('consultant_payment')->orderBy('created_at', 'DESC')->first();

                return response()->json([
                    "status" => true,
                    "message" => "Payment transaction saved successfully.",
                    "data" => $pay
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Payment transaction save failed.",
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Something went wrong.', "error" => $e->getMessage()]);
        }
    }
    public function update_consultant_payment(Request $request)
    {
        try {
            $pres_sub1 = DB::table('consultant_payment')->where('id', $request->id)->first();

            $balance = $pres_sub1->total_pay_amt - $request->paid_amt;
            // print_r($balance);
            // exit;
            // $balance = $pres_sub1[0]->total_pay_amt - $request->paid_amt;
            $data = [
                "paid_amt" => $request->paid_amt,
                "balance" => $balance,
                "status" => $request->status,
                "updated_at" => date("Y-m-d H:i:s")
            ];

            // $pres_sub = DB::table('consultant_payment')->where('id', $request->id)->update($data);
            // $pres_sub = DB::table('consultant_payment')->where('doctor_id', $request->doctor_id)->where('month', $request->month)->orderBy('created_at', 'DESC')->update($data);
            $pres_sub = DB::table('consultant_payment')->where('id', $request->id)->where('doctor_id', $request->doctor_id)->where('month', $request->month)->update($data);


            if ($pres_sub) {
                $pay = DB::table('consultant_payment')->where('id', $request->id)->first();


                return response()->json([
                    "status" => true,
                    "message" => "Payment transaction updated successfully.",
                    "data" => $pay
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Payment transaction update failed.",
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Something went wrong.', "error" => $e->getMessage()]);
        }
    }
}
