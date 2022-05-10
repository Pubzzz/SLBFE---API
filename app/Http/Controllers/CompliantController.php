<?php

namespace App\Http\Controllers;

use App\Models\Compliant;
use App\Models\CompliantStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class CompliantController extends Controller
{
    public function index()
    {
        $compalints = Compliant::orderBy('created_at', 'DESC')->get();

        return response()->json(['data'   => $compalints,], 200);
    }

    public function store(Request $request)
    {
        // Valdiation
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string',
            'lastName'  => 'required|string',
            'email'      => 'required|string|email',
            'nic'     => 'required|string',
            'contactNo'  => 'required|string',
            'reason'  => 'required|string',
            'message'  => 'required|string'
        ]);

        // Return Errors
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        } else {
            // Insert Validate Data to DB
            $compliant = new Compliant;
            $compliant->first_name  = $request->firstName;
            $compliant->last_name   = $request->lastName;
            $compliant->email       = $request->email;
            $compliant->nic         = $request->nic;
            $compliant->contact_no  = $request->contactNo;
            $compliant->reason      = $request->reason;
            $compliant->message     = $request->message;
            $compliant->save();

            $compliantId = $compliant->id;

            return response()->json([
                'message'   => 'Successfully add compliant!',
                'complaint_id' => $compliantId
            ], 200);
        }
    }


    public function show($id)
    {
        $complaint = Compliant::find($id);

        $complaintStatus = CompliantStatus::where('complaint_id', $id)->get();

        if ($complaint == True) {
            return response()->json([
                'complaint'     => $complaint,
                'complaintStatus' => $complaintStatus
            ], 200);
        } else {
            return response()->json([
                'message'   => 'Complaint Not Found',
            ], 404);
        }
    }



    public function updateStatus(Request $request, $id)
    {
        // Valdiation
        $validator = Validator::make($request->all(), [
            'status' => 'required|string',
            'comments'  => 'required|string'
        ]);

        // Return Errors
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        } elseif (auth('api')->user()->role == "staff") {
            // Logged In User ID
            $userId = auth('api')->user()->id;

            // Add New Compliant Status
            $compliantStatus = new CompliantStatus;
            $compliantStatus->status = $request->status;
            $compliantStatus->comments = $request->comments;
            $compliantStatus->complaint_id = $id;
            $compliantStatus->user_id = $userId;
            $compliantStatus->save();

            return response()->json([
                'message'   => 'Successfully add compliant status!',
            ], 200);
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }
}
