<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\URL;
use Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AccountsController extends Controller
{

    function getMonthwiseSum($data, $columnName, $name)
    {
        return collect($data)->groupBy(function ($item) {
            return Carbon::parse($item->created_at)->format('Y-m');
        })->map(function ($group, $key) use ($columnName, $name) {
            $sum = collect($group)->sum($columnName);
            return [
                'year' => Carbon::parse($key)->year,
                'month' => Carbon::parse($key)->month,
                'date' => $key,
                $name => $sum,
            ];
        })->values()
            ->toArray();
    }
    function getMonthwiseSumconsultant($data, $columnName, $name)
    {
        return collect($data)->groupBy(function ($item) {
            return Carbon::parse($item->month)->format('Y-m');
        })->map(function ($group, $key) use ($columnName, $name) {
            $sum = collect($group)->sum($columnName);

            return [
                'year' => Carbon::parse($key)->year,
                'month' => Carbon::parse($key)->month,
                'date' => $key,
                $name => $sum,
            ];
        })->values()->toArray();
    }
    public function accountslist(Request $request)
    {
        try {
            // Fetching data from different tables
            $payment_transaction = DB::table('payment_transaction')->get();
            $prescription_subcategory = DB::table('prescription_payments')->get();
            $treatment_plan_method = DB::table('consultant_payment')->get();
            $pharmacy_account = DB::table('pharmacy_account')->get();
            $inventory_accounts = DB::table('inventory_accounts')->get();
            $lab_accounts = DB::table('lab_accounts')->get();
            $stationery_accounts = DB::table('stationery_accounts')->get();
            $staff_salary = DB::table('staff_salary')->get();
            $doctor_salary = DB::table('doctor_salary')->get();

            // Calculating month-wise sums
            $monthwiseSum1 = $this->getMonthwiseSum($payment_transaction, 'paid_amt', 'patient_clinic_income');
            $monthwiseSum2 = $this->getMonthwiseSum($prescription_subcategory, 'paid_amt', 'pharmacy_income');
            $monthwiseSum3 = $this->getMonthwiseSumconsultant($treatment_plan_method, 'paid_amt', 'consultant_payment');
            $monthwiseSum4 = $this->getMonthwiseSum($pharmacy_account, 'amount', 'pharmacy_account');
            $monthwiseSum5 = $this->getMonthwiseSum($inventory_accounts, 'amount', 'inventory_accounts');
            $monthwiseSum6 = $this->getMonthwiseSum($lab_accounts, 'amount', 'lab_accounts');
            $monthwiseSum7 = $this->getMonthwiseSum($stationery_accounts, 'amount', 'stationery_accounts');
            $monthwiseSum8 = $this->getMonthwiseSum($staff_salary, 'gross_salary', 'staff_salary');
            $monthwiseSum9 = $this->getMonthwiseSum($doctor_salary, 'gross_salary', 'doctor_salary');

            // Merging all the data
            $combinedData = [];
            $allSums = [$monthwiseSum1, $monthwiseSum2, $monthwiseSum3, $monthwiseSum4, $monthwiseSum5, $monthwiseSum6, $monthwiseSum7, $monthwiseSum8, $monthwiseSum9];

            foreach ($allSums as $monthwiseSum) {
                foreach ($monthwiseSum as $data) {
                    $key = $data['date'];
                    if (!isset($combinedData[$key])) {
                        $combinedData[$key] = $data;
                    } else {
                        $combinedData[$key] = array_merge($combinedData[$key], $data);
                    }
                }
            }

            // Convert combined data to a regular array
            $finalCombinedData = array_values($combinedData);

            // Sorting data by year and month in descending order
            usort($finalCombinedData, function ($a, $b) {
                return $b['year'] <=> $a['year'] ?: $b['month'] <=> $a['month'];
            });

            return response()->json([
                "status" => true,
                "message" => "All accounts data sum success",
                "data" => $finalCombinedData
            ]);
        } catch (\Exception $e) {
            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Something went wrong.', 'error' => $e->getMessage()]);
        }
    }


    //INCOME:

    //PATIENT PARTPAYMENT FOR METHOD PRICE
    public function acc_method_payment(Request $request)
    {
        try {
            $payment_transactions = DB::table('payment_transaction')
                ->select(
                    DB::raw('DISTINCT payment_transaction.id'),
                    'payment_transaction.invoice',
                    'members.first_name as patient_name',
                    'members.age',
                    'members.gender',
                    'payment_transaction.date as appointment_date',
                    DB::raw('DATE(payment_transaction.created_at) as date'),
                    'payment_transaction.appointment_id',
                    'payment_transaction.patient_id',
                    'payment_transaction.discount',
                    'payment_transaction.discount_amt',
                    'payment_transaction.already_paid',
                    'payment_transaction.paid_amt',
                    'payment_transaction.balance',
                    'payment_transaction.status',
                    'payment_transaction.created_at',
                    'payment_transaction.updated_at'
                )
                ->join('members', 'members.id', '=', 'payment_transaction.patient_id')
                ->get();


            $groupedData = collect($payment_transactions)->groupBy(function ($item) {
                if (isset($item->created_at)) {
                    $carbonDate = Carbon::parse($item->created_at);
                    return $carbonDate->format('Y-m'); // Group by year and month
                } else {
                    return "null";
                }
            })->toArray();

            $monthwiseSum = [];
            foreach ($groupedData as $key => $group) {
                foreach ($group as $key2 => $group2) {
                    $group2->doctor_id = null;
                    if ($group2->appointment_id != null) {
                        $payment_appointment_id = $group2->appointment_id;
                        $appointments = DB::table('appointments')->get();
                        $doctorIds = [];
                        foreach ($appointments as $appointment) {
                            $appointment_ids = json_decode($appointment->id);
                            foreach (json_decode($payment_appointment_id) as $key65 => $group76) {
                                if ($appointment_ids == $group76) {
                                    $users = DB::table('users')->where('id', $appointment->doctor_id)->first();
                                    $service = DB::table('treatment_plan_mapping')->where('id', $appointment->service_id)->first();
                                    if ($users && $service) {
                                        $doctorIds[] = [
                                            "appointment_id" => $group76,
                                            "doctor_id" => $appointment->doctor_id,
                                            "doctor_name" => $users->first_name,
                                            "service_id" => $appointment->service_id,
                                            "service" => $service->patient_treatment,
                                        ];
                                    }
                                }
                            }
                        }

                        $group2->doctor_id = $doctorIds;
                    }
                }

                $Monsum['year'] = Carbon::parse($key)->year;
                $Monsum['month'] = Carbon::parse($key)->month;
                $Monsum['date'] = $key;
                $Monsum['payment_transaction'] = collect($group)->sum('paid_amt');
                $Monsum['data'] = $group;
                $monthwiseSum[] = $Monsum;
            }

            if ($payment_transactions) {
                return response()->json([
                    "status" => true,
                    "message" => "payment_transaction sum success",
                    "sum" => $monthwiseSum,
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "payment_transaction sum failed",
                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.', 'error' => $e->getMessage()]);
        }
    }

    //prescription payment for patient
    public function acc_prescription_payment(Request $request)
    {

        try {

            $prescription_payments = DB::table('prescription_payments')
                ->select(
                    DB::raw('DISTINCT prescription_payments.id'),

                    'prescription_payments.invoice',
                    // 'patient_prescription.prescription_subcategory_id',
                    // 'prescription_subcategory.category_name',
                    // 'prescription_subcategory.sub_name',
                    // 'prescription_subcategory.price as subcategory_price',
                    'prescription_payments.patient_id',
                    'members.first_name as patient_name',
                    'prescription_payments.date',
                    'prescription_payments.prescription_id',
                    'prescription_payments.total_pay_amt as total_amt',
                    'prescription_payments.already_paid',
                    'prescription_payments.paid_amt',
                    'prescription_payments.balance',
                    'prescription_payments.status',
                    'prescription_payments.created_at',
                    'prescription_payments.updated_at'
                )
                ->join('members', 'members.id', '=', 'prescription_payments.patient_id')
                // ->join('patient_prescription', 'patient_prescription.id', '=', 'prescription_payments.prescription_id')
                // ->join('prescription_subcategory', 'prescription_subcategory.id', '=', 'patient_prescription.prescription_subcategory_id')
                ->get();


            $groupedData = collect($prescription_payments)->groupBy(function ($item) {
                if (isset($item->created_at)) {
                    $carbonDate = Carbon::parse($item->created_at);
                    return $carbonDate->format('Y-m'); // Group by year and month
                } else {
                    return "null";
                }
            })->toArray();

            $monthwiseSum = [];

            foreach ($groupedData as $key => $group) {
                $Monsum['year'] = Carbon::parse($key)->year;
                $Monsum['month'] = Carbon::parse($key)->month;
                $Monsum['date'] = $key;
                $Monsum['prescription_payment'] = collect($group)->sum('paid_amt');
                $Monsum['data'] = $group;
                $monthwiseSum[] = $Monsum;
                // $monthSum = collect($group)->sum('paid_amt');
                // $monthwiseSum[$key] = $monthSum;
            }

            if ($prescription_payments) {

                return response()->json([
                    "status" => true,
                    "message" => "prescription_payments sum success",
                    "sum" => $monthwiseSum
                    // "data" => $groupedData
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "prescription_payments sum failed"
                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.', 'error' => $e->getMessage()]);
        }
    }

    //EXPENSES:
    public function acc_treatment_plan_method(Request $request)
    {
        try {
            $appointments = DB::table('appointments')->where('appointment_status', 'Completed')->get();

            $treatment_mapping_sum = 0;
            $mapped_method_data = array();

            foreach ($appointments as $appointment) {
                $serviceId = $appointment->service_id;
                $doctor_id = $appointment->doctor_id;
                $patient_id = $appointment->patient_id;


                if ($serviceId) {
                    $treatment_mapping = DB::table('treatment_plan_mapping')->where('id', $serviceId)->get();
                    $doctor_name = DB::table('users')->where('id', $doctor_id)->get();
                    $patient_name = DB::table('members')->where('id', $patient_id)->get();

                    if (count($doctor_name) > 0 && count($patient_name) > 0) {
                        $doctor_nm = $doctor_name[0]->first_name;
                        $patient_nm = $patient_name[0]->first_name;


                        if (count($treatment_mapping) != 0) {

                            $treatment_mapping1 = json_decode($treatment_mapping[0]->patient_treatment, true);
                            // $treatment_mapping2 = $treatment_mapping1->itemsPr;

                            if (isset($treatment_mapping1['itemsPr'])) {
                                $treatment_mapping2 = $treatment_mapping1['itemsPr'];
                            } else {
                                $treatment_mapping2 = [];
                            }



                            $mapped_method_data1 = array();

                            foreach ($treatment_mapping2 as $mnameData) {
                                $treatment_mapping3 = DB::table('treatment_plan_method')->where('treatment_method', $mnameData['mname'])->get();

                                if (!empty($treatment_mapping3)) {
                                    $treatment_mapping_sum += $treatment_mapping3->sum('doctor_price') == null ? 0 : $treatment_mapping3->sum('doctor_price');

                                    foreach ($treatment_mapping3 as $mapped) {
                                        $mapped_method_data1[] = $mapped;
                                    }
                                }
                            }

                            $arrayUnique = collect($mapped_method_data1)->unique('treatment_method')->values()->all();
                            $mapped_method_data12['doctor_id'] = $doctor_id;
                            $mapped_method_data12['doctor_name'] = $doctor_nm;
                            $mapped_method_data12['patient_name'] = $patient_nm;
                            $mapped_method_data12['created_at'] = $appointment->updated_at;
                            $mapped_method_data12['treatments'] = $arrayUnique;
                            $mapped_method_data[] = $mapped_method_data12;
                        }
                    }
                }
            }

            if (!empty($treatment_mapping_sum)) {

                $groupedData = collect($mapped_method_data)
                    ->filter(function ($item) {
                        return isset($item['created_at']); // Filter out items without 'created_at'
                    })
                    ->sortBy(function ($item) {
                        return Carbon::parse($item['created_at'])->format('Y-m');
                    })
                    ->groupBy(function ($item) {
                        return Carbon::parse($item['created_at'])->format('Y-m');
                    })
                    ->toArray();


                $monthwiseSum = [];

                foreach ($groupedData as $key => $group) {

                    $doctor_wise = [];
                    $doctor_wise4 = [];
                    $groupedData1 = collect($group)->groupBy(function ($item) {
                        return $item['doctor_id'];
                    })->toArray();

                    foreach ($groupedData1 as $key1 => $group1) {
                        $mapped_method_data4 = [];

                        $currentMonth = Carbon::parse($group1[0]['created_at'])->format('Y-m');

                        $payments = DB::table('consultant_payment')
                            ->where('doctor_id', $key1)
                            ->whereRaw('month = ?', [$currentMonth])
                            // ->whereRaw('DATE_FORMAT(created_at, "%Y-%m") = ?', [$currentMonth])
                            ->get();
                        $already_paid = DB::table('consultant_payment')
                            ->where('doctor_id', $key1)
                            ->where('month', [$currentMonth])
                            ->sum('paid_amt');


                        $mapped_method_data3['payment_id'] = null;
                        $mapped_method_data3['month'] = null;
                        $mapped_method_data3['total_amount'] = null;
                        $mapped_method_data3['total_pay_amt'] = null;
                        $mapped_method_data3['already_paid'] = null;
                        $mapped_method_data3['paid_amount'] = null;
                        $mapped_method_data3['balance'] = null;
                        $mapped_method_data3['status'] = null;
                        if (!empty($payments) && count($payments) > 0) {
                            foreach ($payments as $payment) {
                                $mapped_method_data3['payment_id'] = $payment->id;
                                $mapped_method_data3['month'] = $payment->month;
                                $mapped_method_data3['total_amount'] = $payment->total_amount;
                                $mapped_method_data3['total_pay_amt'] = $payment->total_pay_amt;
                                $mapped_method_data3['already_paid'] = $already_paid ?? 0;
                                $mapped_method_data3['paid_amount'] = $payment->paid_amt;
                                $mapped_method_data3['balance'] = $payment->balance;
                                $mapped_method_data3['status'] = $payment->status;
                            }
                        }
                        $mapped_method_data4[] = $mapped_method_data3;

                        $monthSum1 = collect($group1)->sum(function ($item) {
                            return collect($item['treatments'])->sum('doctor_price');
                        });

                        foreach ($group1 as $key2 => $group2) {
                            $d = $group2['doctor_name'];
                            $doctor_wise1[$d] = $d;
                        }
                        $doctor_wise[] = [
                            'total' => $monthSum1,
                            'doctor_name' => $doctor_wise1[$d],
                            'doctor_id' => $key1,
                            'payment' => $mapped_method_data4,
                            'data' => $group1

                        ];
                    }
                    $monthSum = collect($group)->sum(function ($item) {
                        return collect($item['treatments'])->sum('doctor_price');
                    });


                    $monthwiseSum[] = [
                        'year' => Carbon::parse($key)->year,
                        'month' => Carbon::parse($key)->month,
                        'date' => $key,
                        'treatment_plan_method' => $monthSum,
                        'data' => $group,
                        "doctor_wise" => $doctor_wise,
                    ];
                }


                return response()->json([
                    "status" => true,
                    "message" => "treatment_plan_method sum success",
                    "monthwiseSum" => $monthwiseSum
                    // "data" => $groupedData

                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "treatment_plan_method sum failed",
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Something went wrong.', 'error' => $e->getMessage()]);
        }
    }

    public function doctor_consultant_details(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'doctor_id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'errors' => $validator->errors()->all()], 422);
            }

            $doctorId = $request->input('doctor_id');
            $fromDate = $request->input('from_date');
            $toDate = $request->input('to_date');
            $month = $request->input('month'); // Format: Y-m (e.g., 2024-01)

            $appointmentsQuery = DB::table('appointments')
                ->where('appointment_status', 'Completed')
                ->where('doctor_id', $doctorId);

            // Apply date filters
            if ($fromDate && $toDate) {
                $appointmentsQuery->whereBetween('updated_at', [$fromDate, $toDate]);
            } elseif ($month) {
                $appointmentsQuery->whereRaw('DATE_FORMAT(updated_at, "%Y-%m") = ?', [$month]);
            } elseif ($fromDate) {
                $appointmentsQuery->where('updated_at', '>=', $fromDate);
            } elseif ($toDate) {
                $appointmentsQuery->where('updated_at', '<=', $toDate);
            }

            $appointments = $appointmentsQuery->get();

            $treatment_mapping_sum = 0;
            $mapped_method_data = array();

            foreach ($appointments as $appointment) {
                $serviceId = $appointment->service_id;
                $doctor_id = $appointment->doctor_id;
                $patient_id = $appointment->patient_id;


                if ($serviceId) {
                    $treatment_mapping = DB::table('treatment_plan_mapping')->where('id', $serviceId)->get();
                    $doctor_name = DB::table('users')->where('id', $doctor_id)->get();
                    $patient_name = DB::table('members')->where('id', $patient_id)->get();

                    if (count($doctor_name) > 0 && count($patient_name) > 0) {
                        $doctor_nm = $doctor_name[0]->first_name;
                        $patient_nm = $patient_name[0]->first_name;


                        if (count($treatment_mapping) != 0) {

                            $treatment_mapping1 = json_decode($treatment_mapping[0]->patient_treatment, true);
                            // $treatment_mapping2 = $treatment_mapping1->itemsPr;

                            if (isset($treatment_mapping1['itemsPr'])) {
                                $treatment_mapping2 = $treatment_mapping1['itemsPr'];
                            } else {
                                $treatment_mapping2 = [];
                            }



                            $mapped_method_data1 = array();

                            foreach ($treatment_mapping2 as $mnameData) {
                                $treatment_mapping3 = DB::table('treatment_plan_method')->where('treatment_method', $mnameData['mname'])->get();

                                if (!empty($treatment_mapping3)) {
                                    $treatment_mapping_sum += $treatment_mapping3->sum('doctor_price') == null ? 0 : $treatment_mapping3->sum('doctor_price');

                                    foreach ($treatment_mapping3 as $mapped) {
                                        $mapped_method_data1[] = $mapped;
                                    }
                                }
                            }

                            $arrayUnique = collect($mapped_method_data1)->unique('treatment_method')->values()->all();
                            $mapped_method_data12['doctor_id'] = $doctor_id;
                            $mapped_method_data12['doctor_name'] = $doctor_nm;
                            $mapped_method_data12['patient_name'] = $patient_nm;
                            $mapped_method_data12['created_at'] = $appointment->updated_at;
                            $mapped_method_data12['treatments'] = $arrayUnique;
                            $mapped_method_data[] = $mapped_method_data12;
                        }
                    }
                }
            }



            if (!empty($treatment_mapping_sum)) {

                $groupedData = collect($mapped_method_data)
                    ->filter(function ($item) {
                        return isset($item['created_at']); // Filter out items without 'created_at'
                    })
                    ->sortBy(function ($item) {
                        return Carbon::parse($item['created_at'])->format('Y-m');
                    })
                    ->groupBy(function ($item) {
                        return Carbon::parse($item['created_at'])->format('Y-m');
                    })
                    ->toArray();


                $monthwiseSum = [];

                foreach ($groupedData as $key => $group) {

                    $doctor_wise = [];
                    $doctor_wise4 = [];
                    $groupedData1 = collect($group)->groupBy(function ($item) {
                        return $item['doctor_id'];
                    })->toArray();

                    foreach ($groupedData1 as $key1 => $group1) {
                        $mapped_method_data4 = [];

                        $currentMonth = Carbon::parse($group1[0]['created_at'])->format('Y-m');

                        $payments = DB::table('consultant_payment')
                            ->where('doctor_id', $key1)
                            ->whereRaw('month = ?', [$currentMonth])
                            // ->whereRaw('DATE_FORMAT(created_at, "%Y-%m") = ?', [$currentMonth])
                            ->get();
                        $already_paid = DB::table('consultant_payment')
                            ->where('doctor_id', $key1)
                            ->where('month', [$currentMonth])
                            ->sum('paid_amt');


                        $mapped_method_data3['payment_id'] = null;
                        $mapped_method_data3['month'] = null;
                        $mapped_method_data3['total_amount'] = null;
                        $mapped_method_data3['total_pay_amt'] = null;
                        $mapped_method_data3['already_paid'] = null;
                        $mapped_method_data3['paid_amount'] = null;
                        $mapped_method_data3['balance'] = null;
                        $mapped_method_data3['status'] = null;
                        if (!empty($payments) && count($payments) > 0) {
                            foreach ($payments as $payment) {
                                $mapped_method_data3['payment_id'] = $payment->id;
                                $mapped_method_data3['month'] = $payment->month;
                                $mapped_method_data3['total_amount'] = $payment->total_amount;
                                $mapped_method_data3['total_pay_amt'] = $payment->total_pay_amt;
                                $mapped_method_data3['already_paid'] = $already_paid ?? 0;
                                $mapped_method_data3['paid_amount'] = $payment->paid_amt;
                                $mapped_method_data3['balance'] = $payment->balance;
                                $mapped_method_data3['status'] = $payment->status;
                            }
                        }
                        $mapped_method_data4[] = $mapped_method_data3;

                        $monthSum1 = collect($group1)->sum(function ($item) {
                            return collect($item['treatments'])->sum('doctor_price');
                        });

                        foreach ($group1 as $key2 => $group2) {
                            $d = $group2['doctor_name'];
                            $doctor_wise1[$d] = $d;
                        }
                        $doctor_wise[] = [
                            'total' => $monthSum1,
                            'doctor_name' => $doctor_wise1[$d],
                            'doctor_id' => $key1,
                            'payment' => $mapped_method_data4,
                            'data' => $group1

                        ];
                    }
                    $monthSum = collect($group)->sum(function ($item) {
                        return collect($item['treatments'])->sum('doctor_price');
                    });


                    $monthwiseSum[] = [
                        'year' => Carbon::parse($key)->year,
                        'month' => Carbon::parse($key)->month,
                        'date' => $key,
                        'treatment_plan_method' => $monthSum,
                        'data' => $group,
                        "doctor_wise" => $doctor_wise,
                    ];
                }
                return response()->json([
                    "status" => true,
                    "message" => "treatment_plan_method sum success",
                    "monthwiseSum" => $monthwiseSum,
                    "total_records" => count($monthwiseSum)
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "treatment_plan_method sum failed",
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Something went wrong.', 'error' => $e->getMessage()]);
        }
    }
    //PHARMACY
    public function store_pharmacy_account(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "invoice_no" => 'required',
            "company_name" => 'required',
            "bill_copy" => 'required|file',
            // Add file validation rule
            "amount" => 'required',
            "status" => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {
            if ($request->hasFile('bill_copy') && $request->file('bill_copy')->isValid()) {
                $bill_copy = time() . '.' . $request->bill_copy->extension();
                $request->bill_copy->move(public_path('pharmacy_documents'), $bill_copy);
                $bill_copy_path = URL::to('/public/pharmacy_documents/' . $bill_copy);

                $data = [
                    "invoice_no" => $request->invoice_no,
                    "company_name" => $request->company_name,
                    "bill_copy" => $bill_copy_path,
                    "amount" => $request->amount,
                    "status" => $request->status,
                    "created_at" => date("Y-m-d H:i:s"),
                    "updated_at" => null
                ];

                $saveCategory = DB::table('pharmacy_account')->where('invoice_no', $request->invoice_no)->first();

                if (empty($saveCategory)) {
                    $saveCategory = DB::table('pharmacy_account')->insert($data);

                    $pharmacy_account = DB::table('pharmacy_account')->where('invoice_no', $request->invoice_no)->first();

                    return response()->json([
                        "status" => true,
                        "message" => "Pharmacy account inserted successfully.",
                        "data" => $pharmacy_account
                    ]);
                } else {
                    return response()->json([
                        "status" => false,
                        "message" => "Pharmacy account invoice number already insert",
                    ]);
                }
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Invalid bill copy file.",
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Something went wrong.', 'error' => $e->getMessage()]);
        }
    }
    public function list_pharmacy_account(Request $request)
    {
        try {
            $acc_prescription_subcategory = DB::table('pharmacy_account')
                ->select(
                    'pharmacy_account.id',
                    'pharmacy_account.invoice_no',
                    'pharmacy_account.company_name',
                    'pharmacy_account.bill_copy',
                    'pharmacy_account.amount',
                    'pharmacy_account.status',
                    'pharmacy_account.created_at',
                    'pharmacy_account.updated_at'
                )
                ->get();

            $groupedData = collect($acc_prescription_subcategory)->groupBy(function ($item) {
                if (isset($item->created_at)) {
                    $carbonDate = Carbon::parse($item->created_at);
                    return $carbonDate->format('Y-m'); // Group by year and month
                } else {
                    return "null";
                }
            })->toArray();

            $monthwiseSum = [];

            foreach ($groupedData as $key => $group) {
                $Monsum['year'] = Carbon::parse($key)->year;
                $Monsum['month'] = Carbon::parse($key)->month;
                $Monsum['date'] = $key;
                $Monsum['pharmacy_accounts'] = collect($group)->sum('amount');
                $Monsum['data'] = $group;
                $monthwiseSum[] = $Monsum;
                // $monthSum = collect($group)->sum('amount');
                // $monthwiseSum[$key] = $monthSum;
            }


            if ($acc_prescription_subcategory) {
                return response()->json([
                    "status" => true,
                    "message" => "pharmacy account sum success",
                    "sum" => $monthwiseSum
                    // "data" => $monthwiseSum
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "pharmacy account sum failed"
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Something went wrong.', 'error' => $e->getMessage()]);
        }
    }

    public function pharmacy_account_delete(Request $request)
    {

        $deleteQuery = DB::table('pharmacy_account')->where('id', $request->id)->delete();

        if ($deleteQuery) {

            return response()->json([
                "status" => true,
                "message" => "pharmacy_account deleted successfully.",

            ]);
        } else {
            return response()->json([
                "status" => false,
                "message" => "pharmacy_account delete failed.",
                "data" => []

            ]);
        }
    }

    //INVENTORY
    public function store_inventory_account(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "invoice_no" => 'required',
            "company_name" => 'required',
            "bill_copy" => 'required|file',
            // Add file validation rule
            "amount" => 'required',
            "status" => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {
            if ($request->hasFile('bill_copy') && $request->file('bill_copy')->isValid()) {
                $bill_copy = time() . '.' . $request->bill_copy->extension();
                $request->bill_copy->move(public_path('inventory_document'), $bill_copy);
                $bill_copy_path = URL::to('/public/inventory_document/' . $bill_copy);

                $data = [
                    "invoice_no" => $request->invoice_no,
                    "company_name" => $request->company_name,
                    "bill_copy" => $bill_copy_path,
                    "amount" => $request->amount,
                    "status" => $request->status,
                    "created_at" => date("Y-m-d H:i:s"),
                    "updated_at" => null
                ];

                $saveCategory = DB::table('inventory_accounts')->where('invoice_no', $request->invoice_no)->first();

                if (empty($saveCategory)) {
                    $saveCategory = DB::table('inventory_accounts')->insert($data);

                    $pharmacy_account = DB::table('inventory_accounts')->where('invoice_no', $request->invoice_no)->first();
                    return response()->json([
                        "status" => true,
                        "message" => "inventory account inserted successfully.",
                        "data" => $pharmacy_account

                    ]);
                } else {
                    return response()->json([
                        "status" => false,
                        "message" => "inventory account invoice number already insert",
                    ]);
                }
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Invalid bill copy file.",
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Something went wrong.', 'error' => $e->getMessage()]);
        }
    }
    public function list_inventory_account(Request $request)
    {
        try {
            $acc_prescription_subcategory = DB::table('inventory_accounts')
                ->select(
                    'inventory_accounts.id',
                    'inventory_accounts.invoice_no',
                    'inventory_accounts.company_name',
                    'inventory_accounts.bill_copy',
                    'inventory_accounts.amount',
                    'inventory_accounts.status',
                    'inventory_accounts.created_at',
                    'inventory_accounts.updated_at'
                )
                ->get();
            $groupedData = collect($acc_prescription_subcategory)->groupBy(function ($item) {
                if (isset($item->created_at)) {
                    $carbonDate = Carbon::parse($item->created_at);
                    return $carbonDate->format('Y-m'); // Group by year and month
                } else {
                    return "null";
                }
            })->toArray();

            $monthwiseSum = [];

            foreach ($groupedData as $key => $group) {
                $Monsum['year'] = Carbon::parse($key)->year;
                $Monsum['month'] = Carbon::parse($key)->month;
                $Monsum['date'] = $key;
                $Monsum['inventory_accounts'] = collect($group)->sum('amount');
                $Monsum['data'] = $group;
                $monthwiseSum[] = $Monsum;
                // $monthSum = collect($group)->sum('amount');
                // $monthwiseSum[$key] = $monthSum;
            }

            if ($acc_prescription_subcategory) {

                return response()->json([
                    "status" => true,
                    "message" => "inventory account sum success",
                    "sum" => $monthwiseSum
                    // "data" => $groupedData
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "inventory account sum failed"
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Something went wrong.', 'error' => $e->getMessage()]);
        }
    }
    public function inventory_account_delete(Request $request)
    {

        $deleteQuery = DB::table('inventory_accounts')->where('id', $request->id)->delete();

        if ($deleteQuery) {

            return response()->json([
                "status" => true,
                "message" => "inventory_accounts deleted successfully.",

            ]);
        } else {
            return response()->json([
                "status" => false,
                "message" => "inventory_accounts delete failed.",
                "data" => []

            ]);
        }
    }

    //LAB
    public function store_lab_account(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "invoice_no" => 'required',
            "company_name" => 'required',
            "bill_copy" => 'required|file',
            // Add file validation rule
            "amount" => 'required',
            "status" => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {
            if ($request->hasFile('bill_copy') && $request->file('bill_copy')->isValid()) {
                $bill_copy = time() . '.' . $request->bill_copy->extension();
                $request->bill_copy->move(public_path('lab_document'), $bill_copy);
                $bill_copy_path = URL::to('/public/lab_document/' . $bill_copy);

                $data = [
                    "invoice_no" => $request->invoice_no,
                    "company_name" => $request->company_name,
                    "bill_copy" => $bill_copy_path,
                    "amount" => $request->amount,
                    "status" => $request->status,
                    "created_at" => date("Y-m-d H:i:s"),
                    "updated_at" => null
                ];

                $saveCategory = DB::table('lab_accounts')->where('invoice_no', $request->invoice_no)->first();

                if (empty($saveCategory)) {
                    $saveCategory = DB::table('lab_accounts')->insert($data);

                    $pharmacy_account = DB::table('lab_accounts')->where('invoice_no', $request->invoice_no)->first();
                    return response()->json([
                        "status" => true,
                        "message" => "lab account inserted successfully.",
                        "data" => $pharmacy_account

                    ]);
                } else {
                    return response()->json([
                        "status" => false,
                        "message" => "lab account invoice number already insert",
                    ]);
                }
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Invalid bill copy file.",
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Something went wrong.', 'error' => $e->getMessage()]);
        }
    }

    public function list_lab_account(Request $request)
    {
        try {
            $acc_prescription_subcategory = DB::table('lab_accounts')
                ->select(
                    'lab_accounts.id',
                    'lab_accounts.invoice_no',
                    'lab_accounts.company_name',
                    'lab_accounts.bill_copy',
                    'lab_accounts.amount',
                    'lab_accounts.status',
                    'lab_accounts.created_at',
                    'lab_accounts.updated_at'
                )
                ->get();
            $groupedData = collect($acc_prescription_subcategory)->groupBy(function ($item) {
                if (isset($item->created_at)) {
                    $carbonDate = Carbon::parse($item->created_at);
                    return $carbonDate->format('Y-m'); // Group by year and month
                } else {
                    return "null";
                }
            })->toArray();

            $monthwiseSum = [];

            foreach ($groupedData as $key => $group) {
                $Monsum['year'] = Carbon::parse($key)->year;
                $Monsum['month'] = Carbon::parse($key)->month;
                $Monsum['date'] = $key;
                $Monsum['lab_accounts'] = collect($group)->sum('amount');
                $Monsum['data'] = $group;
                $monthwiseSum[] = $Monsum;
                // $monthSum = collect($group)->sum('amount');
                // $monthwiseSum[$key] = $monthSum;
            }

            if ($acc_prescription_subcategory) {

                return response()->json([
                    "status" => true,
                    "message" => "lab account sum success",
                    "sum" => $monthwiseSum
                    // "data" => $groupedData
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "lab account sum failed"
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Something went wrong.', 'error' => $e->getMessage()]);
        }
    }
    public function lab_account_delete(Request $request)
    {

        $deleteQuery = DB::table('lab_accounts')->where('id', $request->id)->delete();

        if ($deleteQuery) {

            return response()->json([
                "status" => true,
                "message" => "lab_accounts deleted successfully.",

            ]);
        } else {
            return response()->json([
                "status" => false,
                "message" => "lab_accounts delete failed.",
                "data" => []

            ]);
        }
    }

    //STATIONERY
    public function store_stationery_account(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "invoice_no" => 'required',
            "company_name" => 'required',
            "bill_copy" => 'required|file',
            // Add file validation rule
            "amount" => 'required',
            "status" => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {
            if ($request->hasFile('bill_copy') && $request->file('bill_copy')->isValid()) {
                $bill_copy = time() . '.' . $request->bill_copy->extension();
                $request->bill_copy->move(public_path('stationery_document'), $bill_copy);
                $bill_copy_path = URL::to('/public/stationery_document/' . $bill_copy);

                $data = [
                    "invoice_no" => $request->invoice_no,
                    "company_name" => $request->company_name,
                    "bill_copy" => $bill_copy_path,
                    "amount" => $request->amount,
                    "status" => $request->status,
                    "created_at" => date("Y-m-d H:i:s"),
                    "updated_at" => null
                ];

                $saveCategory = DB::table('stationery_accounts')->where('invoice_no', $request->invoice_no)->first();

                if (empty($saveCategory)) {
                    $saveCategory = DB::table('stationery_accounts')->insert($data);

                    $pharmacy_account = DB::table('stationery_accounts')->where('invoice_no', $request->invoice_no)->first();
                    return response()->json([
                        "status" => true,
                        "message" => "stationery account inserted successfully.",
                        "data" => $pharmacy_account

                    ]);
                } else {
                    return response()->json([
                        "status" => false,
                        "message" => "stationery account invoice number already insert",
                    ]);
                }
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Invalid bill copy file.",
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Something went wrong.', 'error' => $e->getMessage()]);
        }
    }

    public function list_stationery_account(Request $request)
    {
        try {
            $acc_prescription_subcategory = DB::table('stationery_accounts')
                ->select(
                    'stationery_accounts.id',
                    'stationery_accounts.invoice_no',
                    'stationery_accounts.company_name',
                    'stationery_accounts.bill_copy',
                    'stationery_accounts.amount',
                    'stationery_accounts.status',
                    'stationery_accounts.created_at',
                    'stationery_accounts.updated_at'
                )
                ->get();

            $groupedData = collect($acc_prescription_subcategory)->groupBy(function ($item) {
                if (isset($item->created_at)) {
                    $carbonDate = Carbon::parse($item->created_at);
                    return $carbonDate->format('Y-m'); // Group by year and month
                } else {
                    return "null";
                }
            })->toArray();

            $monthwiseSum = [];

            foreach ($groupedData as $key => $group) {
                $Monsum['year'] = Carbon::parse($key)->year;
                $Monsum['month'] = Carbon::parse($key)->month;
                $Monsum['date'] = $key;
                $Monsum['stationery_accounts'] = collect($group)->sum('amount');
                $Monsum['data'] = $group;
                $monthwiseSum[] = $Monsum;
                // $monthSum = collect($group)->sum('amount');
                // $monthwiseSum[$key] = $monthSum;
            }

            if ($acc_prescription_subcategory) {

                return response()->json([
                    "status" => true,
                    "message" => "stationery account sum success",
                    "sum" => $monthwiseSum
                    // "data" => $groupedData
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "stationery account sum failed"
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Something went wrong.', 'error' => $e->getMessage()]);
        }
    }
    public function stationery_account_delete(Request $request)
    {

        $deleteQuery = DB::table('stationery_accounts')->where('id', $request->id)->delete();

        if ($deleteQuery) {

            return response()->json([
                "status" => true,
                "message" => "stationery_accounts deleted successfully.",

            ]);
        } else {
            return response()->json([
                "status" => false,
                "message" => "stationery_accounts delete failed.",
                "data" => []

            ]);
        }
    }
    //consulatant fee
    public function acc_consultant_fee(Request $request)
    {


        try {
            $Month = $request->month;

            $consultant_fee = DB::table('payment')
                ->select(
                    'payment.id',
                    'payment.consultant_fee'
                )
                // ->whereRaw('MONTH(created_at) = ?', [$Month])
                ->get();

            if ($consultant_fee) {

                $acc = DB::table('payment')
                    //   ->whereRaw('MONTH(created_at) = ?', [$Month])
                    ->sum('payment.consultant_fee');

                return response()->json([
                    "status" => true,
                    "message" => "consultant fee sum success",
                    "sum" => $acc,
                    "data" => $consultant_fee,
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "consultant fee sum failed",
                    "data" => $consultant_fee
                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.', 'error' => $e->getMessage()]);
        }
    }

    //update accounts
    public function update_pharmacy_account(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id" => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $bill_copy_path = ""; // Initialize the $image variable
        if (!empty($request->bill_copy)) {
            if (Str::contains($request->bill_copy, 'https' || 'http')) {
                $bill_copy_path = $request->bill_copy;
            } else {
                $bill_copy = time() . '.' . $request->bill_copy->extension();
                $request->bill_copy->move(public_path('pharmacy_documents'), $bill_copy);
                $bill_copy_path = URL::to('/public/pharmacy_documents/' . $bill_copy);
            }
        }
        if (empty($bill_copy_path)) {
            $bill_copy_path = "";
        }

        try {

            $id = $request->id;
            $data = [
                "invoice_no" => $request->invoice_no,
                "company_name" => $request->company_name,
                "bill_copy" => $bill_copy_path,
                "amount" => $request->amount,
                "status" => $request->status,
                "updated_at" => date("Y-m-d H:i:s")
            ];

            $role = DB::table('pharmacy_account')->where('id', $id)->update($data);

            if ($role) {

                $role = DB::table('pharmacy_account')->where('id', $id)->first();

                return response()->json([
                    "status" => true,
                    "message" => "pharmacy account details updated successfully",
                    "data" => $role
                ]);
            } else {

                return response()->json([
                    "status" => false,
                    "message" => "pharmacy account details updated failed",

                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
        }
    }
    public function update_inventory_account(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id" => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {

            $bill_copy_path = ""; // Initialize the $image variable
            if (!empty($request->bill_copy)) {
                if (Str::contains($request->bill_copy, 'https' || 'http')) {
                    $bill_copy_path = $request->bill_copy;
                } else {
                    $bill_copy = time() . '.' . $request->bill_copy->extension();
                    $request->bill_copy->move(public_path('inventory_document'), $bill_copy);
                    $bill_copy_path = URL::to('/public/inventory_document/' . $bill_copy);
                }
            }
            if (empty($bill_copy_path)) {
                $bill_copy_path = "";
            }

            $id = $request->id;
            $data = [
                "invoice_no" => $request->invoice_no,
                "company_name" => $request->company_name,
                "bill_copy" => $bill_copy_path,
                "amount" => $request->amount,
                "status" => $request->status,
                "updated_at" => date("Y-m-d H:i:s")
            ];

            $role = DB::table('inventory_accounts')->where('id', $id)->update($data);

            if ($role) {

                $role = DB::table('inventory_accounts')->where('id', $id)->first();

                return response()->json([
                    "status" => true,
                    "message" => "inventory accounts details updated successfully",
                    "data" => $role
                ]);
            } else {

                return response()->json([
                    "status" => false,
                    "message" => "inventory accounts details updated failed",

                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
        }
    }
    public function update_lab_account(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id" => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {


            $bill_copy_path = ""; // Initialize the $image variable
            if (!empty($request->bill_copy)) {
                if (Str::contains($request->bill_copy, 'https' || 'http')) {
                    $bill_copy_path = $request->bill_copy;
                } else {
                    $bill_copy = time() . '.' . $request->bill_copy->extension();
                    $request->bill_copy->move(public_path('lab_document'), $bill_copy);
                    $bill_copy_path = URL::to('/public/lab_document/' . $bill_copy);
                }
            }
            if (empty($bill_copy_path)) {
                $bill_copy_path = "";
            }

            $id = $request->id;
            $data = [
                "invoice_no" => $request->invoice_no,
                "company_name" => $request->company_name,
                "bill_copy" => $bill_copy_path,
                "amount" => $request->amount,
                "status" => $request->status,
                "updated_at" => date("Y-m-d H:i:s")
            ];

            $role = DB::table('lab_accounts')->where('id', $id)->update($data);

            if ($role) {

                $role = DB::table('lab_accounts')->where('id', $id)->first();

                return response()->json([
                    "status" => true,
                    "message" => "lab accounts updated successfully",
                    "data" => $role
                ]);
            } else {

                return response()->json([
                    "status" => false,
                    "message" => "lab accounts updated failed",

                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
        }
    }
    public function update_stationary_account(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id" => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {
            $bill_copy_path = ""; // Initialize the $image variable
            if (!empty($request->bill_copy)) {
                if (Str::contains($request->bill_copy, 'https' || 'http')) {
                    $bill_copy_path = $request->bill_copy;
                } else {
                    $bill_copy = time() . '.' . $request->bill_copy->extension();
                    $request->bill_copy->move(public_path('stationery_document'), $bill_copy);
                    $bill_copy_path = URL::to('/public/stationery_document/' . $bill_copy);
                }
            }
            if (empty($bill_copy_path)) {
                $bill_copy_path = "";
            }


            $id = $request->id;
            $data = [
                "invoice_no" => $request->invoice_no,
                "company_name" => $request->company_name,
                "bill_copy" => $bill_copy_path,
                "amount" => $request->amount,
                "status" => $request->status,
                "updated_at" => date("Y-m-d H:i:s")
            ];

            $role = DB::table('stationery_accounts')->where('id', $id)->update($data);

            if ($role) {

                $role = DB::table('stationery_accounts')->where('id', $id)->first();

                return response()->json([
                    "status" => true,
                    "message" => "stationery_accounts updated successfully",
                    "data" => $role
                ]);
            } else {

                return response()->json([
                    "status" => false,
                    "message" => "stationery_accounts updated failed",

                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
        }
    }
}
