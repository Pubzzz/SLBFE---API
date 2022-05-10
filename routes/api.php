<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CompliantController;
use App\Http\Controllers\CitizenController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\StaffController;

Route::group([
    'prefix' => 'v1'
], function () {

    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('register', [AuthController::class, 'register']);

    Route::group([
        'middleware' => 'auth:api'
    ], function () {
        Route::get('user', [AuthController::class, 'user']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
        Route::get('logout', [AuthController::class, 'logout']);

        // Citizen Routes
        Route::put('citizen', [CitizenController::class, 'update']);
        Route::put('citizen/address', [CitizenController::class, 'updateAddress']);
        Route::put('citizen/location/{nic}', [CitizenController::class, 'updateLocation']);
        Route::get('citizen/qualifications/{nic}', [CitizenController::class, 'userQualifications']);
        Route::get('citizen/documents/{nic}', [CitizenController::class, 'citizenDocuments']);
        Route::post('citizen/qualification', [CitizenController::class, 'newQualification']);
        Route::put('citizen/qualification/{qualificationId}', [CitizenController::class, 'updateQualification']);
        Route::delete('citizen/qualification/{qualificationId}', [CitizenController::class, 'deleteQualification']);
        Route::delete('citizen/document/{documentId}', [CitizenController::class, 'deleteDocument']);
        Route::post('citizen/upload-profile-image', [CitizenController::class, 'uploadProfileImage']);
        Route::post('citizen/upload/document/{documentId}', [CitizenController::class, 'documentUpload']);
        Route::post('citizen/upload/qualification/{qualificationId}', [CitizenController::class, 'uploadQualification']);


        // Company
        Route::post('job', [JobController::class, 'store']);
        Route::delete('job/{id}', [JobController::class, 'deleteJob']);
        Route::put('company/{companyId}', [CompanyController::class, 'update']);
        Route::delete('company/{companyId}', [CompanyController::class, 'deleteCompany']);
        Route::get('company/jobs', [CompanyController::class, 'companyJobs']);
        Route::put('job/{jobId}', [JobController::class, 'update']);
        Route::get('citizens/qualification/find', [CitizenController::class, 'citizenByQualification']);
        Route::get('citizens/industry/find', [CitizenController::class, 'citizenByIndustry']);

        // Staff
        Route::get('citizen/{nic}', [CitizenController::class, 'show']);
        Route::get('citizens', [CitizenController::class, 'index']);
        Route::delete('citizen/{nic}', [StaffController::class, 'deleteCitizenProfile']);
        Route::get('citizen/document/{documentId}/{status}', [StaffController::class, 'verifyDocuments']);
        Route::get('citizen/qualification/{qualificationId}/{status}', [StaffController::class, 'verifyQulification']);
        Route::get('citizen/verification/{nic}/{status}', [CitizenController::class, 'verification']);
        Route::get('user/{id}/{status}', [StaffController::class, 'changeUserStatus']);
        Route::get('staff', [StaffController::class, 'show']);
        Route::put('staff', [StaffController::class, 'update']);
        Route::put('company/{id}/{status}', [StaffController::class, 'changeCompanyStatus']);
        Route::get('job/status/{jobId}/{status}', [JobController::class, 'changeJobStatus']);
        Route::get('companies', [CompanyController::class, 'index']);
        Route::get('company/{companyId}', [CompanyController::class, 'show']);
        Route::get('citizens/{nic}/contacts', [CitizenController::class, 'citizenContactInfo']);


        // New Route Add to Documentation
        Route::get('compliants', [CompliantController::class, 'index']);
    });

    // Jobs Route
    Route::get('jobs', [JobController::class, 'index']);
    Route::get('job/{id}', [JobController::class, 'show']);
    Route::get('job/search/{keyword}', [JobController::class, 'search']);

    // Compliants Routes
    Route::post('compliant', [CompliantController::class, 'store']);
    Route::get('compliant/{id}', [CompliantController::class, 'show']);
    Route::post('compliant/status/{id}', [CompliantController::class, 'updateStatus']);

    Route::get('industries', [CompanyController::class, 'industries']);
    
});
