<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Citizen;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CitizenController extends Controller
{

    public function index(Request $request)
    {
        // Check Request User Role == Staff
        if ($request->user()->role != "staff" and $request->user()->role != "company") {
            return response()->json([
                'Message' => 'Access Denied'
            ], 403);
        }

        $citizens = DB::table('users as u')
            ->join('citizens as c', 'u.id', '=', 'c.user_id')
            ->join('industries as i', 'c.industry_id', '=', 'i.id')
            ->select(
                'u.id as user_id',
                'u.first_name',
                'u.last_name',
                'u.email',
                'u.role',
                'u.created_at as user_created_at',
                'u.status as user_status',
                'c.nic',
                'i.name',
                'c.profile_image_path',
                'c.experience_level',
                'c.status as citizen_verification_status',
            )
            ->where('u.role', 'citizen')->paginate(3);

        return response()->json([
            'data' => $citizens
        ], 200);
    }

    public function show($nic)
    {
        $citizen = DB::table('users as u')
            ->join('citizens as c', 'u.id', '=', 'c.user_id')
            ->join('addresses as a', 'c.id', '=', 'a.citizen_id')
            ->join('industries as i', 'c.industry_id', '=', 'i.id')
            ->join('current_locations as l', 'c.id', '=', 'l.citizen_id')
            ->select(
                'u.id as user_id',
                'u.first_name',
                'u.last_name',
                'u.email',
                'u.role',
                'u.created_at as user_created_at',
                'u.status as user_status',
                'c.nic',
                'c.passport_no',
                'c.passport_expiry_date',
                'c.profile_image_path',
                'c.date_of_birth',
                'c.mobile',
                'c.profession',
                'c.employee_name',
                'c.experience_level',
                'c.status as citizen_verification_status',
                'c.employee_name',
                'i.name',
                'c.experience_level',
                'a.address_line_one',
                'a.address_line_two',
                'a.city',
                'a.postal_code',
                'l.location',
            )
            ->where('c.nic', $nic)->first();

        return response()->json([
            'data'      => $citizen
        ], 200);
    }


    public function update(Request $request)
    {
        // Check Request User Role == Citizen
        if ($request->user()->role != "citizen") {
            return response()->json([
                'Message' => 'Access Denied'
            ], 403);
        }

        // Logged In User ID
        $userId = $request->user()->id;

        // Request Data Validation
        $validator = Validator::make($request->all(), [
            'firstName'         => 'required|string|max:60',
            'lastName'          => 'required|string|max:60',
            'nic'               => 'required|string|max:15',
            'passportNo'        => 'nullable|string|max:15',
            'passportExpiryDate' => 'nullable|date',
            'dob'               => 'required|date',
            'mobile'            => 'required|string|max:15',
            'profession'        => 'nullable|string|max:60',
            'employeeName'      => 'nullable|string|max:80',
            'expLevel'          => 'required|string',
            'industryId'        => 'required|numeric'
        ]);

        // Return Errors
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // User Data
        $user = User::find($userId);
        $user->first_name = $request->firstName;
        $user->last_name = $request->lastName;
        $user->save();

        // Citizen Data
        $citizen = Citizen::where('user_id', $userId)->first();
        $citizen->nic = $request->nic;
        $citizen->passport_no = $request->passportNo;
        $citizen->passport_expiry_date = $request->passportExpiryDate;
        $citizen->date_of_birth = $request->dob;
        $citizen->mobile = $request->mobile;
        $citizen->profession = $request->profession;
        $citizen->employee_name = $request->employeeName;
        $citizen->experience_level = $request->expLevel;
        $citizen->industry_id = $request->industryId;
        $citizen->save();

        return response()->json([
            'Message' => 'Updated Successfully'
        ], 200);
    }

    public function updateAddress(Request $request)
    {
        // Check Request User Role == Citizen
        if ($request->user()->role != "citizen") {
            return response()->json([
                'Message' => 'Access Denied'
            ], 403);
        }

        // Request Data Validation
        $validator = Validator::make($request->all(), [
            'addressLineOne'        => 'required|string|max:40',
            'addressLineTwo'        => 'nullable|string|max:40',
            'city'                  => 'required|string|max:40',
            'postalCode'            => 'nullable|string|max:10'
        ]);

        // Return Errors
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Logged In User ID
        $userId = $request->user()->id;

        // Citizen ID
        $citizenId = Citizen::where('user_id', $userId)->first()->id;

        // Update Citizen Address
        $address = Address::where('citizen_id', $citizenId)->first();
        $address->address_line_one = $request->addressLineOne;
        $address->address_line_two = $request->addressLineTwo;
        $address->city = $request->city;
        $address->postal_code = $request->postalCode;

        if ($address->save()) {
            return response()->json([
                'Message' => 'Updated Successfully'
            ], 200);
        }
    }

    public function verification(Request $request, $nic, $status)
    {
        // Check Request User Role == Staff
        if ($request->user()->role != "staff") {
            return response()->json([
                'Message' => 'Access Denied'
            ], 403);
        }

        $citizen = Citizen::where('nic', $nic)->first();
        $citizen->status = $status;
        $citizen->save();

        return response()->json([
            'Message' => 'Verification Status Updated Successfully',
        ], 200);
    }

    public function updateLocation(Request $request, $nic)
    {
        // Citizen ID
        $citizen = Citizen::where('nic', $nic)->first();
        $citizenUserId = $citizen->user_id;

        // User Validation
        if ($citizenUserId != True) {
            return response()->json(['Message'  =>  'Invalid NIC'], 404);
        } elseif ($request->user()->id != $citizenUserId) {
            return response()->json(['Message'  =>  'Unauthorized'], 403);
        }

        $citizenId = $citizen->id;

        // Request Data Validation
        $validator = Validator::make($request->all(), [
            'location'  => 'required|string|max:191',
            'latitude'  => 'required|string|max:191',
            'longitude' => 'required|string|max:191',
        ]);

        // Return Validation Errors
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }


        $currentLocation = DB::table('current_locations')
            ->where('citizen_id', $citizenId)
            ->update([
                'location'  => $request->location,
                'latitude'  => $request->latitude,
                'longitude'  => $request->longitude,
            ]);

        if ($currentLocation != True) {
            return response()->json(['Message'  =>  'Failed'], 400);
        }

        return response()->json(['Message'  =>  'Location Updated Successfully'], 200);
    }

    public function userQualifications(Request $request, $nic)
    {
        // Citizen ID
        $citizenId = Citizen::where('nic', $nic)->first()->id;

        $qualifications = DB::table('qualifications as q')
            ->join('qualification_types as qt', 'qt.id', 'q.qualification_type_id')
            ->select('q.id as id', 'q.title', 'qt.type', 'q.field', 'q.school_university', 'q.file_path', 'q.status')
            ->where('citizen_id', $citizenId)
            ->get();

        if ($qualifications != True) {
            return response()->json(['Message'  =>  'Not Found'], 404);
        }

        return response()->json(['Qualifications'  =>  $qualifications], 200);
    }

    public function newQualification(Request $request)
    {
        // Valdiation
        $validator = Validator::make($request->all(), [
            'qualificationType' => 'required|numeric',
            'title'             => 'required|string|max:80',
            'field'             => 'required|string|max:80',
            'schoolUniversity'    => 'required|string|max:80',
        ]);

        // Return Validation Errors
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Citizen ID
        $citizenId = Citizen::where('user_id', $request->user()->id)->first()->id;

        $qualification = DB::table('qualifications')->insert([
            'citizen_id' => $citizenId,
            'qualification_type_id' => $request->qualificationType,
            'title' => $request->title,
            'field' => $request->field,
            'school_university' => $request->schoolUniversity,
            'status' => 'upload',
        ]);

        if ($qualification != True) {
            return response()->json(['Message'  =>  'Failed'], 400);
        }

        return response()->json(['Message'  =>  'Qualification Added Successfully'], 200);
    }

    public function updateQualification(Request $request, $qualificationId)
    {
        // Citizen ID
        $citizenId = Citizen::where('user_id', $request->user()->id)->first()->id;

        $q = DB::table('qualifications')->where('id', $qualificationId)->first();

        if ($q == False) {
            return response()->json(['Message'  =>  'Qualification Not Found'], 404);
        } elseif ($q->citizen_id != $citizenId) {
            return response()->json(['Message'  =>  'Unauthorized'], 403);
        }

        // Valdiation
        $validator = Validator::make($request->all(), [
            'qualificationType' => 'required|numeric',
            'title'             => 'required|string|max:60',
            'field'             => 'required|string|max:80',
            'schoolUniversity'    => 'required|string|max:80',
        ]);

        // Return Validation Errors
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $qualification = DB::table('qualifications')->where('id', $qualificationId)
            ->update([
                'qualification_type_id' => $request->qualificationType,
                'title' => $request->title,
                'field' => $request->field,
                'school_university' => $request->schoolUniversity,
            ]);

        if ($qualification != True) {
            return response()->json(['Message'  =>  'Failed'], 400);
        }

        return response()->json(['Message'  =>  'Qualification Updated Successfully'], 200);
    }

    public function uploadQualification(Request $request, $qualificationId)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:doc,docx,pdf,png,jpeg,jpg|max:10240',
        ]);

        // Return Validation Errors
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if ($file = $request->file('file')) {
            $path = $file->store('qualifications');

            //store your file into directory and db
            $document = DB::table('qualifications')->where('id', $qualificationId)
                ->update([
                    'file_path' => $path,
                    'status' => "unverified"
                ]);

            return response()->json(['Message'  =>  'Qualification Uploaded Successfully'], 200);
        }
    }

    public function deleteQualification(Request $request, $qualificationId)
    {
        // Citizen ID
        $citizenId = Citizen::where('user_id', $request->user()->id)->first()->id;

        $q = DB::table('qualifications')->where('id', $qualificationId)->first();

        if ($q == False) {
            return response()->json(['Message'  =>  'Qualification Not Found'], 404);
        } elseif ($q->citizen_id != $citizenId) {
            return response()->json(['Message'  =>  'Unauthorized'], 403);
        }

        $qualification = DB::table('qualifications')->where('id', $qualificationId)->delete();

        if ($qualification != True) {
            return response()->json(['Message'  =>  'Failed'], 400);
        }

        return response()->json(['Message'  =>  'Qualification Deleted Successfully'], 200);
    }

    public function uploadProfileImage(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:png,jpeg,jpg|max:4096',
        ]);

        // Return Validation Errors
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if ($file = $request->file('file')) {
            $userId = $request->user()->id;

            $path = $file->store('profile-images');

            //store your file into directory and db
            $citizen = Citizen::where('user_id', $userId)->first();
            $citizen->profile_image_path = $path;
            $citizen->save();

            return response()->json(['Message'  =>  'Profile Iamge Uploaded Successfully'], 200);
        }
    }

    public function documentUpload(Request $request, $documentId)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:doc,docx,pdf,png,jpeg,jpg|max:10240',
        ]);

        // Return Validation Errors
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if ($file = $request->file('file')) {
            $path = $file->store('documents');

            //store your file into directory and db
            $document = DB::table('documents')->where('id', $documentId)
                ->update([
                    'file_path' => $path,
                    'status' => "unverified",
                    'created_at' => Carbon::now()
                ]);

            if ($document != True) {
                return response()->json(['Message'  =>  'Failed'], 400);
            }

            return response()->json(['Message'  =>  'Document Uploaded Successfully'], 200);
        }
    }

    public function citizenDocuments(Request $request, $nic)
    {

        $citizenId = Citizen::where('nic', $nic)->first()->id;

        $documents = DB::table('documents')
            ->select('id', 'type', 'file_path', 'status', 'created_at')
            ->where('citizen_id', $citizenId)
            ->get();

        return response()->json(['documents'  =>  $documents], 200);
    }

    public function deleteDocument(Request $request, $documentId)
    {
        $document = DB::table('documents')->where('id', $documentId)->first();
        $userId = Citizen::find($document->citizen_id)->user_id;

        if ($request->user()->id == $userId) {
            // Check for File path
            if ($document->file_path == NULL) {
                return response()->json(['Message'  =>  'Bad Request'], 400);
            }

            // Remove Document from Storage
            Storage::delete($document->file_path);

            // Update status and Delete file_path
            DB::table('documents')->where('id', $documentId)
                ->update([
                    'file_path' => "",
                    'status'    => "upload",
                    'created_at' => null
                ]);

            return response()->json(['Message'  =>  'Document Deleted Successfully'], 200);
        } else {
            return response()->json(['Message'  =>  'Unauthorized'], 403);
        }
    }

    public function citizenContactInfo($nic)
    {
        if (Citizen::where('nic', $nic)->first()) {
            $citizenContactDetails = DB::table('citizens as c')
                ->join('users as u', 'u.id', 'c.user_id')
                ->join('addresses as a', 'a.citizen_id', 'c.id')
                ->join('current_locations as l', 'l.citizen_id', 'c.id')
                ->select(
                    'u.first_name',
                    'u.last_name',
                    'u.email',
                    'c.mobile',
                    'a.address_line_one',
                    'a.address_line_two',
                    'a.city',
                    'a.postal_code',
                    'l.location'
                )
                ->where('c.nic', $nic)
                ->first();

            return response()->json(['data'  =>  $citizenContactDetails], 200);
        } else {
            return response()->json(['message'  =>  "User not found"], 404);
        }
    }

    public function citizenByQualification(Request $request)
    {
        $qualificationId = $request->qualification;

        $citizens = DB::table('citizens as c')
            ->join('users as u', 'u.id', 'c.user_id')
            ->join('industries as i', 'i.id', 'c.industry_id')
            ->join('qualifications as q', 'q.citizen_id', 'c.id')
            ->select(
                'u.first_name',
                'u.last_name',
                'c.profile_image_path',  
                'c.experience_level',
                'c.profession',
                'u.email',
                'i.name as name'
            )
            ->where('q.qualification_type_id', $qualificationId)
            ->groupBy('q.citizen_id')->paginate(4);  

        return response()->json(['data'  =>  $citizens], 200);
    }

    public function citizenByIndustry(Request $request)
    {
        $industryId = $request->industry;

        $citizens = DB::table('citizens as c')
            ->join('users as u', 'u.id', 'c.user_id')
            ->join('industries as i', 'i.id', 'c.industry_id')
            ->select(
                'u.first_name',
                'u.last_name',
                'c.profile_image_path',  
                'c.experience_level',
                'c.profession',
                'u.email',
                'i.name as name'
            )
            ->where('i.id', $industryId)->paginate(4);  

        return response()->json(['data'  =>  $citizens], 200);
    }


}
