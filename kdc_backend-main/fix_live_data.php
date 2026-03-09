<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== LIVE DATA FIX SCRIPT ===\n\n";

// Task 1: Delete Ravikumar's prescription payment record (Invoice: #PRES - 969437365)
echo "Task 1: Deleting Ravikumar's prescription payment (Invoice: #PRES - 969437365, ₹220)\n";

// Search by invoice number
$prescPayment = DB::table('prescription_payments')
    ->where('invoice', 'like', '%969437365%')
    ->first();

if ($prescPayment) {
    $patient = DB::table('users')->where('id', $prescPayment->patient_id)->first();
    $patientName = $patient ? $patient->first_name . ' ' . $patient->surname : 'Unknown';
    
    echo "Found prescription payment:\n";
    echo "  ID: {$prescPayment->id}\n";
    echo "  Invoice: {$prescPayment->invoice}\n";
    echo "  Patient: {$patientName} (ID: {$prescPayment->patient_id})\n";
    echo "  Date: {$prescPayment->date}\n";
    echo "  Amount: ₹{$prescPayment->paid_amt}\n";
    
    DB::table('prescription_payments')->where('id', $prescPayment->id)->delete();
    echo "✓ DELETED successfully!\n\n";
} else {
    echo "✗ Invoice #PRES - 969437365 not found in database\n\n";
}

// Task 2: Change Sashi Kumar S to KDC-T0049
echo "Task 2: Changing Sashi Kumar S registration number to KDC-T0049\n";

$sashiKumar = DB::table('users')
    ->where('first_name', 'like', '%Sashi Kumar%')
    ->where('role_id', 4)
    ->first();

if ($sashiKumar) {
    echo "Found: {$sashiKumar->first_name} {$sashiKumar->surname} (ID: {$sashiKumar->id})\n";
    echo "Current Registration No: {$sashiKumar->patient_reg_no}\n";
    
    $newRegNo = 'KDC-T0049';
    
    // Check if KDC-T0049 already exists
    $existingPatient = DB::table('users')
        ->where('patient_reg_no', $newRegNo)
        ->where('id', '!=', $sashiKumar->id)
        ->first();
    
    if ($existingPatient) {
        echo "✗ ERROR: {$newRegNo} already exists for patient: {$existingPatient->first_name} {$existingPatient->surname} (ID: {$existingPatient->id})\n";
        echo "Cannot proceed with change.\n\n";
    } else {
        echo "✓ {$newRegNo} is available\n";
        
        DB::table('users')
            ->where('id', $sashiKumar->id)
            ->update(['patient_reg_no' => $newRegNo]);
        
        echo "✓ Changed from {$sashiKumar->patient_reg_no} to {$newRegNo}\n\n";
    }
} else {
    echo "Sashi Kumar S not found\n\n";
}

echo "=== SCRIPT COMPLETED ===\n";
