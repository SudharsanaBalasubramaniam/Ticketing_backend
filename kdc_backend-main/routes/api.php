<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RegistrationController;
use App\Http\Controllers\API\DoctorController;
use App\Http\Controllers\API\StaffController;
use App\Http\Controllers\API\PatientController;
use App\Http\Controllers\API\PharmacyController;
use App\Http\Controllers\API\InventoryController;
use App\Http\Controllers\API\AppointmentsController;
use App\Http\Controllers\API\StatusController;
use App\Http\Controllers\API\CountController;
use App\Http\Controllers\API\Doctor\ProfileController;
use App\Http\Controllers\API\Staff\StaffprofileController;
use App\Http\Controllers\API\Patient\PatientprofileController;
use App\Http\Controllers\API\Patient\PatientdataController;
use App\Http\Controllers\API\ReportController;
use App\Http\Controllers\API\UserRegistrationController;
use App\Http\Controllers\API\UpdateRegisterController;
use App\Http\Controllers\API\GlobalController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\SubcategoryController;
use App\Http\Controllers\API\TreatmentPlanController;
use App\Http\Controllers\API\LoginWithGoogleController;
use App\Http\Controllers\API\PatientMedicalReportController;
use App\Http\Controllers\API\MedicalController;
use App\Http\Controllers\API\DiagnosisController;
use App\Http\Controllers\API\TreatmentPlanCategoryController;
use App\Http\Controllers\API\PrescriptionCategoryController;
use App\Http\Controllers\API\TreatmentPlanSubCategoryController;
use App\Http\Controllers\API\PrescriptionSubCategoryController;
use App\Http\Controllers\API\DiagnosisTypeController;
use App\Http\Controllers\API\TreatmentPlanProcedureController;
use App\Http\Controllers\API\TreatmentMethodStageController;
use App\Http\Controllers\API\TreatmentPlanMethodController;
use App\Http\Controllers\API\DiagnosisTreatmentPlanController;
use App\Http\Controllers\API\StatusActiveController;
use App\Http\Controllers\API\TreatmentMethodController;
use App\Http\Controllers\API\TreatmentPlanMappingController;
use App\Http\Controllers\API\PatientPrescription;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\MedicalRecordController;
use App\Http\Controllers\API\AccountsController;
use App\Http\Controllers\API\SalaryController;
use App\Http\Controllers\API\EventController;
use App\Http\Controllers\API\DiscountTypeController;
use App\Http\Controllers\API\DashboardController;
/*

|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/getEvent', [EventController::class, 'getEvent'])->name('getEvent');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});




Route::post('/patient_registration', [RegistrationController::class, 'patient_registration']);
Route::post('/doctorStaffAsPatient', [RegistrationController::class, 'doctorStaffAsPatient']);
Route::post('/otp_registration', [RegistrationController::class, 'otp_registration']);
Route::post('/verify_otp', [RegistrationController::class, 'verify_otp']);
Route::post('/postlogin', [RegistrationController::class, 'postlogin']);
// Route::post('/user_registration', [UserRegistrationController::class, 'user_registration']);
Route::post('/update_registration', [UpdateRegisterController::class, 'update_registration']);
Route::post('/forgot_password', [RegistrationController::class, 'forgot']);
Route::post('/forgotverifyOtp', [RegistrationController::class, 'forgotverifyOtp']);
Route::post('/forgot_change_pw', [RegistrationController::class, 'forgot_change_pw']);


// in mail
Route::post('/forgot_mail', [RegistrationController::class, 'forgot_mail'])->name('/forgot_mail');
Route::post('passwordApproval', [RegistrationController::class, 'passwordApproval'])->name('password.approval');
Route::post('forgot_mail_change_pw', [RegistrationController::class, 'forgot_mail_change_pw']);

Route::middleware('auth:sanctum')->group(function () {
    //dashboard
    Route::get('/dashboard/home', [DashboardController::class, 'home']);
    
    //start super admin

    Route::post('/logout', [App\Http\Controllers\API\RegistrationController::class, 'logout'])
        ->name('logout');

    //appointments

    Route::get('/treatment_list', [AppointmentsController::class, 'treatment_list']);
    Route::post('/particular_appointment_list', [AppointmentsController::class, 'particular_appointment_list']);
    Route::get('/appointment_details', [AppointmentsController::class, 'appointment_details']);


    Route::get('/appointment_list', [AppointmentsController::class, 'appointment_list']);
    Route::post('/patient_appointment_list', [AppointmentsController::class, 'patient_appointment_list']);
    Route::post('/store_appointment', [AppointmentsController::class, 'store_appointment']);
    Route::put('/update_appointments', [AppointmentsController::class, 'update_appointments']);
    Route::delete('/delete_appointments', [AppointmentsController::class, 'delete_appointments']);
    Route::post('/update_employee_number', [StatusController::class, 'update_employee_number']);


    Route::post('/appointment_custom_date', [AppointmentsController::class, 'appointment_custom_date']);
    Route::post('/reschedule_appointment', [AppointmentsController::class, 'reschedule_appointment']);
    Route::post('/appointment_summary', [AppointmentsController::class, 'appointment_summary']);
    Route::post('/next_appointment_v2', [AppointmentsController::class, 'next_appointment_v2']);
    Route::get('/upcoming_appointments', [AppointmentsController::class, 'upcoming_appointments']);
    Route::put('/appointment_status_update', [AppointmentsController::class, 'appointment_status_update']);
    Route::get('/ongoing_appointments', [AppointmentsController::class, 'ongoing_appointments']);
    Route::get('/complete_appointments', [AppointmentsController::class, 'complete_appointments']);
    Route::post('/upcoming_appointments_v2', [AppointmentsController::class, 'upcoming_appointments_v2']);
    Route::post('/appointment_list_v2', [AppointmentsController::class, 'appointment_list_v2']);

    //doctor department
    Route::get('/doctor_departments', [DoctorController::class, 'doctor_departments']);
    Route::post('/doctor_department_add', [DoctorController::class, 'doctor_department_add']);
    Route::put('/update_doctor_department', [DoctorController::class, 'update_doctor_department']);
    Route::delete('/delete_doctor_department', [DoctorController::class, 'delete_doctor_department']);

    //doctor
    Route::get('/doctor_list', [DoctorController::class, 'doctor_list']);
    Route::post('/store_doctor', [DoctorController::class, 'store_doctor']);
    Route::delete('/delete_doctor', [DoctorController::class, 'delete_doctor']);
    Route::put('/update_doctor', [DoctorController::class, 'update_doctor']);


    //doctor appointments

    Route::post('/doctor_appointments', [DoctorController::class, 'doctor_appointments']);
    Route::post('/overall_doctor_appointments', [DoctorController::class, 'overall_doctor_appointments']);
    Route::post('/doctor_complete_appointments', [ReportController::class, 'doctor_complete_appointments']);
    Route::get('/upcoming_appointments', [AppointmentsController::class, 'upcoming_appointments']);


    //staff
    Route::post('/store_staff_category', [StaffController::class, 'store_staff_category']);
    Route::get('/staff_category_list', [StaffController::class, 'staff_category_list']);
    Route::put('/update_staff_category', [StaffController::class, 'update_staff_category']);
    Route::delete('/delete_staff_category', [StaffController::class, 'delete_staff_category']);

    Route::get('/staff_list', [StaffController::class, 'staff_list']);
    Route::post('/store_staff', [StaffController::class, 'store_staff']);
    Route::delete('/delete_staff', [StaffController::class, 'delete_staff']);
    Route::put('/update_staff', [StaffController::class, 'update_staff']);

    //patient
    Route::get('/new_patient_list', [PatientController::class, 'new_patient_list']);
    Route::get('/old_patient_list', [PatientController::class, 'old_patient_list']);
    Route::get('/patient_list', [PatientController::class, 'patient_list']);
    Route::post('/store_patient', [PatientController::class, 'store_patient']);
    Route::delete('/delete_patient', [PatientController::class, 'delete_patient']);
    Route::put('/update_patient', [PatientController::class, 'update_patient']);
    Route::post('/patient_appointment', [PatientController::class, 'patient_appointment']);

    //pharmacy
    Route::get('/pharmacy_category_list', [PharmacyController::class, 'pharmacy_category_list']);
    Route::post('/store_pharmacy_category', [PharmacyController::class, 'store_pharmacy_category']);
    Route::delete('/delete_pharmacy_category', [PharmacyController::class, 'delete_pharmacy_category']);
    Route::put('/update_pharmacy_category', [PharmacyController::class, 'update_pharmacy_category']);

    Route::get('/pharmacy_list', [PharmacyController::class, 'pharmacy_list']);
    Route::post('/store_pharmacy', [PharmacyController::class, 'store_pharmacy']);
    Route::delete('/delete_pharmacy', [PharmacyController::class, 'delete_pharmacy']);
    Route::put('/update_pharmacy', [PharmacyController::class, 'update_pharmacy']);


    //inventory
    Route::get('/inventory_category_list', [InventoryController::class, 'inventory_category_list']);
    Route::post('/store_inventory_category', [InventoryController::class, 'store_inventory_category']);
    Route::put('/update_inventory_category', [InventoryController::class, 'update_inventory_category']);
    Route::delete('/delete_inventory_category', [InventoryController::class, 'delete_inventory_category']);

    Route::get('/inventory_list', [InventoryController::class, 'inventory_list']);
    Route::post('/store_inventory', [InventoryController::class, 'store_inventory']);
    Route::delete('/delete_inventory', [InventoryController::class, 'delete_inventory']);
    Route::put('/update_inventory', [InventoryController::class, 'update_inventory']);
    Route::post('/inventory_details', [InventoryController::class, 'inventory_details']);

    //status update
    Route::post('/active_inactive', [StatusController::class, 'active_inactive']);

    //counts
    Route::get('/counts', [CountController::class, 'counts']);


    //global
    Route::post('/global', [GlobalController::class, 'global']);

    //statusactive
    Route::put('/updatestatus_category', [StatusActiveController::class, 'updatestatus_category']);
    Route::put('/updatestatus_subcategory', [StatusActiveController::class, 'updatestatus_subcategory']);
    Route::put('/updatestatus_diagnosis', [StatusActiveController::class, 'updatestatus_diagnosis']);
    Route::put('/updatestatus_diagnosistype', [StatusActiveController::class, 'updatestatus_diagnosistype']);
    Route::put('/updatestatus_treatmentmethod', [StatusActiveController::class, 'updatestatus_treatmentmethod']);






    Route::post('/admin_profile', [ProfileController::class, 'admin_profile']);

    //end super admin


    //start doctor
    Route::post('/doctor_profile', [ProfileController::class, 'profile']);
    Route::post('/doctor_change_password', [ProfileController::class, 'change_password']);
    Route::post('/treatment_plan_list', [ProfileController::class, 'treatment_plan_list']);
    Route::post('/doctor_earning', [ProfileController::class, 'doctor_earning']);

    //end doctor

    //start staff
    Route::post('/staff_profile', [StaffprofileController::class, 'profile']);
    Route::post('/staff_change_password', [StaffprofileController::class, 'change_password']);
    //end staff

    //start patient
    Route::post('/patient_profile', [PatientprofileController::class, 'profile']);
    Route::post('/patient_change_password', [PatientprofileController::class, 'change_password']);
    Route::get('/patient_info', [PatientdataController::class, 'patient_info']);
    //end patient


    Route::get('/category_list', [CategoryController::class, 'index']);
    Route::post('/store_category', [CategoryController::class, 'store']);
    Route::delete('/delete_category', [CategoryController::class, 'destroy']);
    Route::put('/update_category', [CategoryController::class, 'update']);


    Route::get('/subcategory_list', [SubcategoryController::class, 'index']);
    Route::post('/store_subcategory', [SubcategoryController::class, 'store']);
    Route::delete('/delete_subcategory', [SubcategoryController::class, 'destroy']);
    Route::put('/update_subcategory', [SubcategoryController::class, 'update']);

    // TREATMENT PLAN
    Route::get('/treatmentList', [TreatmentPlanController::class, 'index']);
    Route::post('/storetreatment', [TreatmentPlanController::class, 'store']);
    Route::delete('/delete_subcategory', [TreatmentPlanController::class, 'destroy']);
    Route::put('/updateTreatmentList', [TreatmentPlanController::class, 'update']);

    Route::get('/medicalCategoryList', [MedicalController::class, 'index']);
    Route::post('/storeMedicalcategory', [MedicalController::class, 'store']);
    Route::delete('/deleteMedicalcategory', [MedicalController::class, 'destroy']);
    Route::put('/updateMedicalcategory', [MedicalController::class, 'update']);



    Route::get('/diagnosistypeList', [DiagnosisTypeController::class, 'index']);
    Route::post('/storediagnosistype', [DiagnosisTypeController::class, 'store']);
    Route::delete('/deletediagnosistype', [DiagnosisTypeController::class, 'destroy']);
    Route::put('/updatediagnosistype', [DiagnosisTypeController::class, 'update']);


    Route::get('/diagnosisList', [DiagnosisController::class, 'index']);
    Route::post('/storediagnosis', [DiagnosisController::class, 'store']);
    Route::delete('/deletediagnosis', [DiagnosisController::class, 'destroy']);
    Route::put('/updatediagnosis', [DiagnosisController::class, 'update']);
    Route::post('/getDiagnosis', [DiagnosisController::class, 'getDiagnosis']);


    //no needed
    Route::get('/prescriptionCategoryList', [PrescriptionCategoryController::class, 'index']);
    Route::post('/storetprescriptionCategory', [PrescriptionCategoryController::class, 'store']);
    Route::delete('/deletetprescriptionCategory', [PrescriptionCategoryController::class, 'destroy']);
    Route::put('/updatetprescriptionCategory', [PrescriptionCategoryController::class, 'update']);

    //pres.cate-subcategory
    Route::get('/prescriptionsubCategoryList', [PrescriptionSubCategoryController::class, 'index']);
    Route::post('/storetprescriptionsubCategory', [PrescriptionSubCategoryController::class, 'store']);
    Route::delete('/deletetprescriptionsubCategory', [PrescriptionSubCategoryController::class, 'destroy']);
    Route::put('/updatetprescriptionsubCategory', [PrescriptionSubCategoryController::class, 'update']);

    // Route::get('/treatmentList', [TreatmentPlanController::class, 'index']);
    // Route::post('/storetreatment', [TreatmentPlanController::class, 'store']);
    // Route::delete('/deletetreatment', [TreatmentPlanController::class, 'destroy']);
    // Route::put('/updatetreatment', [TreatmentPlanController::class, 'update']);

    // Route::post('/getTreatmentPlan', [TreatmentPlanController::class, 'getTreatmentPlan']);

    // Route::get('/treatmentList', [DiagnosisTreatmentPlanController::class, 'index']);
    // Route::post('/storetreatment', [DiagnosisTreatmentPlanController::class, 'store']);
    // Route::delete('/deletetreatment', [DiagnosisTreatmentPlanController::class, 'destroy']);
    // Route::put('/updatetreatment', [DiagnosisTreatmentPlanController::class, 'update']);

    // Route::post('/getTreatmentPlan', [DiagnosisTreatmentPlanController::class, 'getTreatmentPlan']);





    //treatment_plan_mapping or summary
    Route::get('/treatment_plan_mapping_list', [TreatmentPlanMappingController::class, 'treatment_plan_mapping_list']);
    Route::post('/store_treatment_plan_mapping', [TreatmentPlanMappingController::class, 'store_treatment_plan_mapping']);
    Route::put('/update_treatment_plan_mapping', [TreatmentPlanMappingController::class, 'update_treatment_plan_mapping']);
    Route::delete('/delete_treatment_plan_mapping', [TreatmentPlanMappingController::class, 'delete_treatment_plan_mapping']);




    //treatment method mapping
    Route::get('/treatment_method_mapping', [TreatmentMethodController::class, 'treatment_method_mapping']);
    Route::post('/store_treatment_method', [TreatmentMethodController::class, 'store_treatment_method']);

    //roles
    Route::post('/addrole', [TreatmentMethodController::class, 'addrole']);
    Route::get('/viewrole', [TreatmentMethodController::class, 'viewrole']);
    Route::put('/updaterole', [TreatmentMethodController::class, 'updaterole']);
    Route::delete('/deleterole', [TreatmentMethodController::class, 'deleterole']);



    //treatment plan procedure
    Route::get('/treatmentProcedureList', [TreatmentPlanProcedureController::class, 'index']);
    Route::post('/storetreatmentProcedure', [TreatmentPlanProcedureController::class, 'store']);
    Route::delete('/deletetreatmentProcedure', [TreatmentPlanProcedureController::class, 'destroy']);
    Route::put('/updatetreatmentProcedure', [TreatmentPlanProcedureController::class, 'update']);
    Route::post('/getProcedureTreatmentPlan', [TreatmentPlanProcedureController::class, 'getProcedureTreatmentPlan']);

    //plan_pro_method
    Route::get('/plan_proc_method_mapping', [TreatmentPlanMappingController::class, 'plan_proc_method_mapping']);

    //Medical Record
    Route::get('/medical_record_list', [MedicalRecordController::class, 'medical_record_list']);
    Route::post('/store_medical_record', [MedicalRecordController::class, 'store_medical_record']);
    Route::post('/update_medical_record', [MedicalRecordController::class, 'update_medical_record']);
    Route::post('/delete_medical_record', [MedicalRecordController::class, 'delete_medical_record']);

    // treatment_method_stages
    Route::get('/treatmentmethodstageList', [TreatmentMethodStageController::class, 'index']);
    Route::post('/storetreatmentmethodstage', [TreatmentMethodStageController::class, 'store']);
    Route::delete('/deletetreatmentmethodstage', [TreatmentMethodStageController::class, 'destroy']);
    Route::put('/updatetreatmentmethodstage', [TreatmentMethodStageController::class, 'update']);
    Route::post('/treatmentstagebymethod', [TreatmentMethodStageController::class, 'getByMethodId']);


    //treatment_plan_method
    Route::get('/treatmentmethodList', [TreatmentPlanMethodController::class, 'index']);
    Route::post('/storetreatmentmethod', [TreatmentPlanMethodController::class, 'store']);
    Route::delete('/deletetreatmentmethod', [TreatmentPlanMethodController::class, 'destroy']);
    Route::put('/updatetreatmentmethod', [TreatmentPlanMethodController::class, 'update']);
    Route::post('/getmethodTreatmentPlan', [TreatmentPlanMethodController::class, 'getmethodTreatmentPlan']);

    //patient prescription
    Route::get('/patient_prescription_list', [PatientPrescription::class, 'patient_prescription_list']);
    Route::post('/patient_prescription_list_mapping', [PatientPrescription::class, 'patient_prescription_list_mapping']);
    Route::post('/store_patient_prescription', [PatientPrescription::class, 'store_patient_prescription']);
    Route::put('/update_patient_prescription', [PatientPrescription::class, 'update_patient_prescription']);
    Route::delete('/delete_patient_prescription', [PatientPrescription::class, 'delete_patient_prescription']);
    Route::post('/store_prescription_Payments', [PatientPrescription::class, 'store_prescription_Payments']);
    Route::post('/update_prescription_Payments', [PatientPrescription::class, 'update_prescription_Payments']);


    //doctor treatment
    Route::get('/doc_treatment_list', [PatientPrescription::class, 'doc_treatment_list']);
    Route::post('/store_doc_treatment', [PatientPrescription::class, 'store_doc_treatment']);

    //payment
    Route::get('/payment_list', [PaymentController::class, 'payment_list']);
    Route::post('/store_payment', [PaymentController::class, 'store_payment']);

    // store_payment_trans
    Route::get('/payment_trans_list', [PaymentController::class, 'payment_trans_list']);
    Route::get('/payment_trans_list_mapping', [PaymentController::class, 'payment_trans_list_mapping']);
    Route::post('/store_payment_trans', [PaymentController::class, 'store_payment_trans']);
    Route::post('/list_payment_trans', [PaymentController::class, 'list_payment_trans']);
    Route::post('/update_payment_trans', [PaymentController::class, 'update_payment_trans']);

    //store consultant payment
    Route::post('/store_consultant_payment', [PaymentController::class, 'store_consultant_payment']);
    Route::post('/update_consultant_payment', [PaymentController::class, 'update_consultant_payment']);

    //discount type
    Route::get('/get_discount_type', [DiscountTypeController::class, 'getDiscountType']);
    Route::post('/insert_discount_type', [DiscountTypeController::class, 'createDiscountType']);
    Route::put('/update_discount_type', [DiscountTypeController::class, 'updateDiscountType']);
    Route::put('/update_discount_type_status', [DiscountTypeController::class, 'updateDiscountTypeStatus']);
    Route::delete('/delete_discount_type', [DiscountTypeController::class, 'deleteDiscountType']);


    //end super admin
    Route::post('/getPatientMedicalReport', [PatientMedicalReportController::class, 'getPatientMedicalReport']);
    Route::post('/savePatientMedicalReport', [PatientMedicalReportController::class, 'savePatientMedicalReport']);
    Route::post('/updatePatientMedicalReport', [PatientMedicalReportController::class, 'updatePatientMedicalReport']);
    Route::delete('/deletePatientMedicalReport', [PatientMedicalReportController::class, 'deletePatientMedicalReport']);


    //accounts
    Route::post('/accountslist', [AccountsController::class, 'accountslist']);


    //income-patient partpayment for method
    Route::post('/acc_method_payment', [AccountsController::class, 'acc_method_payment']);
    //patient prescription
    Route::post('/acc_prescription_payment', [AccountsController::class, 'acc_prescription_payment']);

    //expenses-pharmacy
    Route::post('/store_pharmacy_account', [AccountsController::class, 'store_pharmacy_account']);
    Route::post('/list_pharmacy_account', [AccountsController::class, 'list_pharmacy_account']);
    Route::delete('/pharmacy_account_delete', [AccountsController::class, 'pharmacy_account_delete']);
    //inventory
    Route::post('/store_inventory_account', [AccountsController::class, 'store_inventory_account']);
    Route::post('/list_inventory_account', [AccountsController::class, 'list_inventory_account']);
    Route::delete('/inventory_account_delete', [AccountsController::class, 'inventory_account_delete']);
    //lab
    Route::post('/store_lab_account', [AccountsController::class, 'store_lab_account']);
    Route::post('/list_lab_account', [AccountsController::class, 'list_lab_account']);
    Route::delete('/lab_account_delete', [AccountsController::class, 'lab_account_delete']);
    //stationary
    Route::post('/store_stationery_account', [AccountsController::class, 'store_stationery_account']);
    Route::post('/list_stationery_account', [AccountsController::class, 'list_stationery_account']);
    Route::delete('/stationery_account_delete', [AccountsController::class, 'stationery_account_delete']);
    //doctor price
    Route::post('/acc_treatment_plan_method', [AccountsController::class, 'acc_treatment_plan_method']);
    Route::post('/doctor_consultant_details', [AccountsController::class, 'doctor_consultant_details']);
    //staff doctor salary
    Route::post('/get_staff_salary', [SalaryController::class, 'get_staff_salary']);
    Route::post('/store_staff_salary', [SalaryController::class, 'store_staff_salary']);
    Route::post('/list_salaries', [SalaryController::class, 'list_salaries']);
    Route::post('/store_doctor_salary', [SalaryController::class, 'store_doctor_salary']);
    Route::put('/update_salary', [SalaryController::class, 'update_salary']);
    Route::delete('/delete_salary', [SalaryController::class, 'delete_salary']);


    //Event
    Route::post('/createEvent', [EventController::class, 'createEvent'])->name('createEvent');

    Route::post('/updateEvent', [EventController::class, 'updateEvent'])->name('updateEvent');
    Route::delete('/deleteEvent', [EventController::class, 'deleteEvent'])->name('deleteEvent');


    Route::post('/acc_prescription_subcategory', [AccountsController::class, 'acc_prescription_subcategory']);
    Route::post('/acc_patient_prescription', [AccountsController::class, 'acc_patient_prescription']);
    Route::post('/acc_inventory', [AccountsController::class, 'acc_inventory']);
    Route::post('/acc_consultant_fee', [AccountsController::class, 'acc_consultant_fee']);
    //update accounts
    Route::post('/update_pharmacy_account', [AccountsController::class, 'update_pharmacy_account']);
    Route::post('/update_inventory_account', [AccountsController::class, 'update_inventory_account']);
    Route::post('/update_lab_account', [AccountsController::class, 'update_lab_account']);
    Route::post('/update_stationary_account', [AccountsController::class, 'update_stationary_account']);





    // notifications
    Route::post('/get_notificationsList', [RegistrationController::class, 'get_notificationsList']);
    Route::post('/change_notification_status', [RegistrationController::class, 'change_notification_status']);

    // enquiry notifications
    Route::post('/store_enquiry', [RegistrationController::class, 'store_enquiry']);
    Route::post('/get_enquiry_list', [RegistrationController::class, 'get_enquiry_list']);
    Route::post('/change_enquiry_status', [RegistrationController::class, 'change_enquiry_status']);

    //outpatient_notification
    Route::post('/get_out_patient_enquiry_list', [RegistrationController::class, 'get_out_patient_enquiry_list']);
    Route::post('/change_out_patient_enquiry_status', [RegistrationController::class, 'change_out_patient_enquiry_status']);




    Route::post('/appointment_summary', [AppointmentsController::class, 'appointment_summary']);
    Route::post('/treatmentstagebymethod', [TreatmentMethodStageController::class, 'getByMethodId']);
});
