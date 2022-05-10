<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Citizen;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

use function PHPUnit\Framework\isEmpty;

class StaffController extends Controller
{
    public function changeUserStatus(Request $request, $id, $status)
    {
        // Check Request User Role == Staff
        if ($request->user()->role != "staff") {
            return response()->json([
                'Message' => 'Access Denied'
            ], 403);
        }

        if ($status == "active" || $status == "blocked") {
            // Update User Status
            $user = User::find($id);
            $user->status = $status;
            $user->save();

            return response()->json(['Message' => 'Status Updated Successfully',], 200);
        } else {
            return response()->json(['Message' => 'Invalid Status',], 400);
        }
    }

    public function changeCompanyStatus(Request $request, $id, $status)
    {
        // Check Request User Role == Staff
        if ($request->user()->role != "staff") {
            return response()->json([
                'Message' => 'Access Denied'
            ], 403);
        }

        if ($status == "verified" || $status == "unverified") {
            // Update User Status
            $user = Company::find($id);
            $user->status = $status;
            $user->save();

            return response()->json(['Message' => 'Status Updated Successfully',], 200);
        } else {
            return response()->json(['Message' => 'Invalid Status',], 400);
        }
    }

    public function show(Request $request)
    {
        // Check Request User Role == Staff
        if ($request->user()->role != "staff") {
            return response()->json([
                'Message' => 'Access Denied'
            ], 403);
        }

        $staff = User::query()
            ->select('first_name', 'last_name', 'email', 'status')
            ->where('id', $request->user()->id)
            ->where('role', '=', 'staff')
            ->first();

        if ($staff == isEmpty()) {
            return response()->json(['Message' => 'Something Went Wrong',], 400);
        }

        return response()->json(['data' => $staff], 200);
    }


    public function update(Request $request)
    {
        // Check Request User Role == Staff
        if ($request->user()->role != "staff") {
            return response()->json([
                'Message' => 'Access Denied'
            ], 403);
        }

        $userId = $request->user()->id;

        // Valdiation
        $validator = Validator::make($request->all(), [
            'firstName'         => 'required|string|max:60',
            'lastName'          => 'required|string|max:60'
        ]);

        // Return Validation Errors
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // User Data
        $user = User::find($userId);
        $user->first_name = $request->firstName;
        $user->last_name = $request->lastName;
        $user->save();

        return response()->json([
            'Message' => 'Updated Successfully'
        ], 200);
    }

    

    public function verifyDocuments(Request $request, $documentId, $status)
    {
        // Check Request User Role == Staff
        if ($request->user()->role != "staff") {
            return response()->json([
                'Message' => 'Access Denied'
            ], 403);
        }

        if ($status == "verified" || $status == "unverified") {
            // Check Document ID is valid
            if ($query = DB::table('documents')->where('id', '=', $documentId)->first() == False) {
                return response()->json(['Message' => 'Invalid Document ID',], 400);
            }

            // Update Status
            DB::table('documents')
                ->where('id', $documentId)
                ->update(['status' => $status]);

            // Return Success Message
            return response()->json(['Message' => 'Status Updated Successfully'], 200);
        } else {
            return response()->json(['Message' => 'Invalid Status',], 400);
        }
    }

    public function verifyQulification(Request $request, $qualificationId, $status)
    {
        // Check Request User Role == Staff
        if ($request->user()->role != "staff") {
            return response()->json([
                'Message' => 'Access Denied'
            ], 403);
        }

        if ($status == "verified" || $status == "unverified") {
            // Check Qualification ID is valid
            if ($query = DB::table('qualifications')->where('id', '=', $qualificationId)->first() == False) {
                return response()->json(['Message' => 'Invalid Qualification ID',], 400);
            }

            // Update Status
            DB::table('qualifications')
                ->where('id', $qualificationId)
                ->update(['status' => $status]);

            // Return Success Message
            return response()->json(['Message' => 'Status Updated Successfully'], 200);
        } else {
            return response()->json(['Message' => 'Invalid Status',], 400);
        }
    }

    public function deleteCitizenProfile(Request $request, $nic)
    {
        $loggedInUserRole = $request->user()->role;

        // Check User Role
        if ($loggedInUserRole != "staff") {
            return response()->json(['Message' => 'Access Denied'], 403);
        }

        $citizen = Citizen::where('nic', $nic)->first(); // Get Citizen Data

        // Check User
        if ($citizen == False) {
            return response()->json(['Message' => 'Invaild NIC, User Not Found'], 403);
        }

        $citizenId = $citizen->id; // Citizen ID
        $citizenUserId = $citizen->user_id; // Citizen User ID

        // Delete Qualifications
        $qualifications = DB::table('qualifications')->where('citizen_id', $citizenId)->get();

        if ($qualifications != False) {
            foreach ($qualifications as $qualification) {
                $q = DB::table('qualifications')->where('id', $qualification->id)->first();

                // Check Uploaded Files
                if ($q->file_path != NULL) {
                    // Delete File From Storage
                    Storage::delete($q->file_path);
                }
                // Delete Data From DB
                DB::table('qualifications')->where('id', $qualification->id)->delete();
            }
        }

        // Delete Documents
        $documents = DB::table('documents')->where('citizen_id', $citizenId)->get();
        foreach ($documents as $document) {
            $d = DB::table('documents')->where('id', $document->id)->first();

            // Check Uploaded Files
            if ($d->file_path != NULL) {
                // Delete File From Storage
                Storage::delete($d->file_path);
            }
            // Delete Data From DB
            DB::table('documents')->where('id', $document->id)->delete();
        }


        // Delete Current Location
        DB::table('current_locations')->where('citizen_id', $citizenId)->delete();

        // Delete Address 
        Address::where('citizen_id', $citizenId)->delete();

        // Delete Citizen
        Citizen::find($citizenId)->delete();

        // Delete User
        User::find($citizenUserId)->delete();

        return response()->json(['Message' => 'Citizen Deleted Successfully'], 200);
    }
}
