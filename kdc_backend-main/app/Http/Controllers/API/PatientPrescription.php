<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;


class PatientPrescription extends Controller
{
    public function doc_treatment_list()
    {
        $getTreatment = DB::table('doctor_treatment')->get();
        if (!$getTreatment->isEmpty()) {
            return response()->json([
                "status" => true,
                "message" => "doctor treatment listed successfully.",
                "data" => $getTreatment
            ]);
        } else {
            return response()->json([
                "status" => false,
                "message" => "No data available."

            ]);
        }
    }

    public function store_doc_treatment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "treatment_map_id" => 'required',
            "patient_id" => 'required',
            "doctor_id" => 'required',
            "price" => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {
            $ran_id = 'Tre - ' . rand();
            $data = [
                "random_id" => $ran_id,
                "treatment_map_id" => $request->treatment_map_id,
                "patient_id" => $request->patient_id,
                "doctor_id" => $request->doctor_id,
                "price" => $request->price,
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s")
            ];

            $pres_sub = DB::table('doctor_treatment')->insert($data);
            if ($pres_sub) {
                $pres_sub = DB::table('doctor_treatment')->where("treatment_map_id", $request->treatment_map_id)->first();
                return response()->json([
                    "status" => true,
                    "message" => "doctor treatment saved successfully.",
                    "data" => $pres_sub
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "doctor treatment save failed.",
                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
        }
    }
    // public function patient_prescription_list_mapping(Request $request)
    // {
    //     $getTreatment = DB::table('patient_prescription')
    //         ->select(
    //             'patient_prescription.id',
    //             'prescription_subcategory.id as prescription_subcategory_id',
    //             'prescription_subcategory.category_name',
    //             'prescription_subcategory.sub_name',
    //             'patient_prescription.patient_id',
    //             'patient_prescription.quantity',
    //             'prescription_subcategory.price',
    //             'patient_prescription.total_amt',
    //             'patient_prescription.morning',
    //             'patient_prescription.afternoon',
    //             'patient_prescription.evening',
    //             'patient_prescription.night',
    //             'patient_prescription.before_food',
    //             'patient_prescription.after_food',
    //             'patient_prescription.duration',
    //             'patient_prescription.created_at',
    //             'patient_prescription.updated_at'
    //         )
    //         ->join('prescription_subcategory', 'prescription_subcategory.id', '=', 'patient_prescription.prescription_subcategory_id')
    //         ->where('patient_id', $request->patient_id)
    //         ->get();

    //     $pres_pay = DB::table('prescription_payments')
    //         ->where('patient_id', $request->patient_id)
    //         ->get();



    //     $pay = [];
    //     foreach ($getTreatment as $prescription) {

    //         $prescriptionIdString = (string)$prescription->id;


    //         $pay1['prescription_payment_id'] = null;
    //         $pay1['total_pay_amt'] = null;
    //         $pay1['balance'] = null;
    //         $pay1['already_paid'] = null;
    //         $pay1['status'] = null;
    //         $pay1['paid_amt'] = null;

    //         if (!empty($pres_pay) && count($pres_pay) > 0) {
    //             foreach ($pres_pay as $payments) {
    //                 $payIds = json_decode($payments->prescription_id);



    //                 if (in_array($prescriptionIdString, $payIds)) {

    //                     // $already_paid = DB::table('prescription_payments')
    //                     // ->where('patient_id', $request->patient_id)
    //                     // ->where('date', $payIds)
    //                     // ->sum('paid_amt');



    //                     $pay1['prescription_payment_id'] = $payments->id;
    //                     $pay1['paid_amt'] = $payments->paid_amt;
    //                     $pay1['total_amt'] = $payments->total_amt;
    //                     $pay1['total_pay_amt'] = $payments->total_pay_amt;
    //                     $pay1['balance'] = $payments->balance;
    //                     $pay1['already_paid'] = $payments->already_paid;
    //                     $pay1['status'] = $payments->status;
    //                 }
    //             }
    //         }

    //         $pay1['id'] = $prescription->id;
    //         $pay1['prescription_subcategory_id'] = $prescription->prescription_subcategory_id;
    //         $pay1['category_name'] = $prescription->category_name;
    //         $pay1['patient_id'] = $prescription->patient_id;
    //         $pay1['price'] = $prescription->price;
    //         $pay1['sub_name'] = $prescription->sub_name;
    //         $pay1['prescription_subcategory_id'] = $prescription->prescription_subcategory_id;
    //         $pay1['quantity'] = $prescription->quantity;
    //         $pay1['duration'] = $prescription->duration;
    //         $pay1['morning'] = $prescription->morning;
    //         $pay1['afternoon'] = $prescription->afternoon;
    //         $pay1['evening'] = $prescription->evening;
    //         $pay1['night'] = $prescription->night;
    //         $pay1['total_amt'] = $prescription->total_amt;
    //         $pay1['before_food'] = $prescription->before_food;
    //         $pay1['after_food'] = $prescription->after_food;
    //         $pay1['created_at'] = $prescription->created_at;
    //         $pay1['updated_at'] = $prescription->updated_at;
    //         $pay[] = $pay1;
    //     }
    //     // return $pay;
    //     if (!empty($pay)) {
    //         $groupedDataArray = collect($pay)->groupBy(function ($item) {
    //             return Carbon::parse($item['created_at'])->format('Y-m-d');
    //         });

    //         $transformedData = [];
    //         foreach ($groupedDataArray as $date => $items) {
    //             $transformedData[] = [
    //                 'date' => $date,
    //                 'sum' => collect($items)->sum('total_amt'),
    //                 'prescription' => $items->toArray(),
    //             ];
    //         }

    //         return response()->json([
    //             "status" => true,
    //             "message" => "patient_prescription listed successfully.",
    //             "data" => $transformedData
    //         ]);
    //     } else {
    //         return response()->json([
    //             "status" => false,
    //             "message" => "No data available."
    //         ]);
    //     }
    // }
    public function patient_prescription_list_mapping(Request $request)
    {
        $getTreatment = DB::table('patient_prescription')
            ->select(
                'patient_prescription.id',
                'prescription_subcategory.id as prescription_subcategory_id',
                'prescription_subcategory.category_name',
                'prescription_subcategory.sub_name',
                'patient_prescription.patient_id',
                'patient_prescription.quantity',
                'prescription_subcategory.price',
                'patient_prescription.total_amt',
                'patient_prescription.morning',
                'patient_prescription.afternoon',
                'patient_prescription.evening',
                'patient_prescription.night',
                'patient_prescription.before_food',
                'patient_prescription.after_food',
                'patient_prescription.duration',
                'patient_prescription.created_at',
                'patient_prescription.updated_at'
            )
            ->join('prescription_subcategory', 'prescription_subcategory.id', '=', 'patient_prescription.prescription_subcategory_id')
            ->where('patient_id', $request->patient_id)
            ->get();

      

        if (!empty($getTreatment)) {
            $groupedDataArray = collect($getTreatment)->groupBy(function ($item) {
                return Carbon::parse($item->created_at)->format('Y-m-d');
            });
            $transformedData = [];
            foreach ($groupedDataArray as $date => $items) {
                $pay = [];
                foreach ($items as $prescription) {
                    
                    $prescriptionIdString = (string)$prescription->id;
        
        
                    $pay1['prescription_payment_id'] = null;
                    $pay1['total_pay_amt'] = null;
                    $pay1['balance'] = null;
                    $pay1['already_paid'] = null;
                    $pay1['status'] = null;
                    $pay1['paid_amt'] = null;
        
                    $pres_pay = DB::table('prescription_payments')
                    ->where('patient_id', $request->patient_id)
                    ->where('date', $date)
                    ->get();
                    if (!empty($pres_pay) && count($pres_pay) > 0) {
                        foreach ($pres_pay as $payments) {
                            $payIds = json_decode($payments->prescription_id);
        
        
        
                            if (in_array($prescriptionIdString, $payIds)) {
        
                                $already_paid = DB::table('prescription_payments')
                                ->where('patient_id', $request->patient_id)
                                ->where('date', $date)
                                ->sum('paid_amt');
        
        
        
                                $pay1['prescription_payment_id'] = $payments->id;
                                $pay1['paid_amt'] = $payments->paid_amt;
                                $pay1['total_amt'] = $payments->total_amt;
                                $pay1['total_pay_amt'] = $payments->total_pay_amt;
                                $pay1['balance'] = $payments->balance;
                                $pay1['already_paid'] = $already_paid;
                                $pay1['status'] = $payments->status;
                            }
                        }
                    }
        
                    $pay1['id'] = $prescription->id;
                    $pay1['prescription_subcategory_id'] = $prescription->prescription_subcategory_id;
                    $pay1['category_name'] = $prescription->category_name;
                    $pay1['patient_id'] = $prescription->patient_id;
                    $pay1['price'] = $prescription->price;
                    $pay1['sub_name'] = $prescription->sub_name;
                    $pay1['prescription_subcategory_id'] = $prescription->prescription_subcategory_id;
                    $pay1['quantity'] = $prescription->quantity;
                    $pay1['duration'] = $prescription->duration;
                    $pay1['morning'] = $prescription->morning;
                    $pay1['afternoon'] = $prescription->afternoon;
                    $pay1['evening'] = $prescription->evening;
                    $pay1['night'] = $prescription->night;
                    $pay1['total_amt'] = $prescription->total_amt;
                    $pay1['before_food'] = $prescription->before_food;
                    $pay1['after_food'] = $prescription->after_food;
                    $pay1['created_at'] = $prescription->created_at;
                    $pay1['updated_at'] = $prescription->updated_at;
                    $pay[] = $pay1;



                }
            $transformedData[] = [
                'date' => $date,
                'sum' => collect($items)->sum('total_amt'),
                'prescription' => $pay
            ];
            }
          

            return response()->json([
                "status" => true,
                "message" => "patient_prescription listed successfully.",
                "data" => $transformedData
            ]);
        } else {
            return response()->json([
                "status" => false,
                "message" => "No data available."
            ]);
        }
    }
    public function store_prescription_Payments(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "prescription_id" => 'required',
            "patient_id" => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {
            $invoice = '#PRES - ' . rand();

            $requested_prescription_ids = $request->prescription_id;

            $already_paid = DB::table('prescription_payments')
                ->where('patient_id', $request->patient_id)
                ->where('date',  $request->date)
                // ->where(function ($query) use ($requested_prescription_ids) {
                //     foreach ($requested_prescription_ids as $prescription_id) {
                //         $query->orWhereRaw('JSON_CONTAINS(prescription_id, ?)', [$prescription_id]);
                //     }
                // })
                ->sum('paid_amt');

            $total_pay_amt = $request->total_amt - $already_paid;
            $balance =  $total_pay_amt - $request->paid_amt;
            $data = [
                "invoice" => $invoice,
                "patient_id" => $request->patient_id,
                "date" => $request->date,
                "prescription_id" => json_encode($request->prescription_id),
                "total_amt" => $request->total_amt,
                "total_pay_amt" => $total_pay_amt,
                "already_paid" => $already_paid,
                "paid_amt" => $request->paid_amt,
                "balance" => $balance,
                "status" => $request->status,
                "created_at" => now(),
                "updated_at" => now()
            ];

            $pres_sub1 = DB::table('prescription_payments')->insert($data);
            if ($pres_sub1) {
                $pres_sub = DB::table('prescription_payments')->where("invoice", $invoice)->first();
                return response()->json([
                    "status" => true,
                    "message" => "Patient prescription amount details added successfully.",
                    "data" => $pres_sub
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" =>  "Patient prescription amount details added failed.",
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }
    public function update_prescription_Payments(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id" => 'required',

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {

            $pres_sub1 = DB::table('prescription_payments')->where('id', $request->id)->get();
            $balance = $pres_sub1[0]->total_pay_amt - $request->paid_amt;
            $data = [
                "patient_id" => $request->patient_id,
                "prescription_id" => json_encode($request->prescription_id),
                "paid_amt" => $request->paid_amt,
                "balance" => $balance,
                "status"  => $request->status,
                "updated_at" => now()
            ];

            $pres_sub1 = DB::table('prescription_payments')->where('id', $request->id)->where('patient_id', $request->patient_id)->update($data);
            if ($pres_sub1) {
                $pres_sub =  DB::table('prescription_payments')->where('id', $request->id)->where('patient_id', $request->patient_id)->first();
                return response()->json([
                    "status" => true,
                    "message" => "Patient prescription amount details updated successfully.",
                    "data" => $pres_sub
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" =>  "Patient prescription amount details update failed.",
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }


    public function store_patient_prescription(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "patient_id" => 'required',
            "prescription_subcategory_id" => 'required',
            "quantity" => 'required',

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $quantity = $request->quantity;
        $prescription_subcategory = DB::table('prescription_subcategory')->where("id", $request->prescription_subcategory_id)->first();
        $total_amt = $quantity * $prescription_subcategory->price;

        if ($prescription_subcategory->available_stock < $quantity) {

            return response()->json([
                "status" => false,
                "message" => "Stock not available.",
                "available stock" => $prescription_subcategory->available_stock
            ]);
        }


        $updated_available_stock = $prescription_subcategory->available_stock - $quantity;


        DB::table('prescription_subcategory')->where("id", $request->prescription_subcategory_id)->update([
            "available_stock" => $updated_available_stock,
            "updated_at" => date("Y-m-d H:i:s")
        ]);

        //  $invoice="#PRES - ".rand();
        $data = [
            // "invoice"=>$invoice,
            "category_id" => $request->category_id,
            "prescription_subcategory_id" => $request->prescription_subcategory_id,
            "patient_id" => $request->patient_id,
            "quantity" => $request->quantity,
            "morning" => $request->morning,
            "afternoon" => $request->afternoon,
            "evening" => $request->evening,
            "night" => $request->night,
            "before_food" => $request->before_food,
            "after_food" => $request->after_food,
            "duration" => $request->duration,
            "total_amt" => $total_amt,
            "created_at" => date("Y-m-d H:i:s"),
            "updated_at" => date("Y-m-d H:i:s")
        ];

        $pres_sub = DB::table('patient_prescription')->insert($data);
        if ($pres_sub) {
            $pres_sub = DB::table('patient_prescription')->orderBy('created_at', 'DESC')->first();

            return response()->json([
                "status" => true,
                "message" => "patient prescription saved successfully.",
                "data" => $pres_sub
            ]);
        } else {
            return response()->json([
                "status" => false,
                "message" => "patient prescription save failed.",
            ]);
        }
    }

    public function update_patient_prescription(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id" => 'required',

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {

            $quantity = $request->quantity;
            $prescription_subcategory = DB::table('prescription_subcategory')->where("id", $request->prescription_subcategory_id)->first();
            $total_amt = $quantity * $prescription_subcategory->price;




            $updated_available_stock = $prescription_subcategory->available_stock - $quantity;

            DB::table('prescription_subcategory')->where("id", $request->prescription_subcategory_id)->update([
                "available_stock" => $updated_available_stock,
                "updated_at" => date("Y-m-d H:i:s")
            ]);


            if ($prescription_subcategory->available_stock < $quantity) {
                return response()->json([
                    "status" => false,
                    "message" => "Stock not available.",
                    "available stock" => $prescription_subcategory->available_stock
                ]);
            }

            $data = [
                "id" => $request->id,
                "prescription_subcategory_id" => $request->prescription_subcategory_id,
                "quantity" => $request->quantity,
                "morning" => $request->morning,
                "afternoon" => $request->afternoon,
                "evening" => $request->evening,
                "night" => $request->night,
                "before_food" => $request->before_food,
                "after_food" => $request->after_food,
                "duration" => $request->duration,
                "total_amt" => $total_amt,
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s")
            ];
            $updateMedicalCategory = DB::table('patient_prescription')->where('id', $request->id)->update($data);

            if ($updateMedicalCategory) {
                $selectQuery = DB::table('patient_prescription')->where('id', $request->id)->first();

                return response()->json([
                    "status" => true,
                    "message" => "patient prescription updated successfully.",
                    "data" => $selectQuery
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "patient prescription update failed",
                    "data" => []
                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete_patient_prescription(Request $request)
    {


        try {

            $deleteQuery = DB::table('patient_prescription')->where('id', $request->id)->delete();

            if ($deleteQuery) {

                return response()->json([
                    "status" => true,
                    "message" => "patient prescription deleted successfully.",

                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "patient prescription delete failed.",
                    "data" => []

                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
        }
    }
}
