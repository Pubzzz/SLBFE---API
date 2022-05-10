<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class JobController extends Controller
{
    public function index(Request $request)
    {
        if (isset($request->industryId)) {

            $jobs = DB::table('jobs as j')
                ->join('companies as c', 'j.company_id', '=', 'c.id')
                ->join('users as u', 'c.user_id', '=', 'u.id')
                ->join('industries as i', 'i.id', '=', 'j.industry_id')
                ->select(
                    'j.id as job_id',
                    'j.title',
                    'j.description',
                    'j.application_deadline',
                    'j.created_at',
                    'j.status as job_status',
                    'c.id as company_id',
                    'c.name',
                    'u.email',
                    'c.location',
                    'c.contact_no',
                    'c.status as company_status',
                    'i.name as industry_name'
                )
                ->where('i.id', $request->industryId)
                ->paginate(4);
        } else {
            $jobs = DB::table('jobs as j')
                ->join('companies as c', 'j.company_id', '=', 'c.id')
                ->join('users as u', 'c.user_id', '=', 'u.id')
                ->join('industries as i', 'i.id', '=', 'j.industry_id')
                ->select(
                    'j.id as job_id',
                    'j.title',
                    'j.description',
                    'j.application_deadline',
                    'j.created_at',
                    'j.status as job_status',
                    'c.id as company_id',
                    'c.name',
                    'u.email',
                    'c.location',
                    'c.contact_no',
                    'c.status as company_status',
                    'i.name as industry_name'
                )
                ->paginate(4);
        }

        return response()->json(['jobs' => $jobs], 200);
    }

    public function store(Request $request)
    {
        // Check User Role
        if ($request->user()->role != "company") {
            return response()->json([
                'Message' => 'Access Denied'
            ], 403);
        }

        // Request Data Validation
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:60',
            'description' => 'required|string',
            'industryId' => 'required|numeric',
            'applicationDeadline' => 'required|date'
        ]);

        // Return Validation Errors
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // User ID
        $userId = $request->user()->id;
        // Company ID
        $companyId = Company::where('user_id', $userId)->first()->id;


        // Create New Job
        $job = new Job;
        $job->title = $request->title;
        $job->description = $request->description;
        $job->industry_id = $request->industryId;
        $job->company_id = $companyId;
        $job->application_deadline = $request->applicationDeadline;
        $job->status = "active";
        $job->save();

        return response()->json([
            'Message' => 'New Job Post Added Successfully'
        ], 200);
    }

    public function show($id)
    {
        if ($job = Job::find($id)) {
            $companyId = $job->company_id;
            $company = Company::find($companyId);
            return response()->json([
                'job' => $job,
                'company' => $company
            ], 200);
        } else {
            return response()->json(['message' => 'Not Found'], 404);
        }
    }

    public function update(Request $request, $jobId)
    {
        // Check User Role
        if ($request->user()->role != "company") {
            return response()->json([
                'Message' => 'Access Denied'
            ], 403);
        }

        // Request Data Validation
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:60',
            'description' => 'required|string',
            'industryId' => 'required|numeric',
            'applicationDeadline' => 'required|date'
        ]);

        // Return Errors
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Update Job
        $job = Job::find($jobId);
        $job->title = $request->title;
        $job->description = $request->description;
        $job->industry_id = $request->industryId;
        $job->application_deadline = $request->applicationDeadline;
        $job->save();

        return response()->json([
            'Message' => 'Updated Successfully'
        ], 200);
    }

    public function changeJobStatus(Request $request, $jobId, $status)
    {
        // Check Request User Role == Staff OR Company
        if ($request->user()->role != "staff" and $request->user()->role != "company") {
            return response()->json([
                'Message' => 'Access Denied'
            ], 403);
        }

        if ($status == "active" || $status == "blocked") {
            // Update Job Status
            $job = Job::find($jobId);
            $job->status = $status;
            $job->save();

            return response()->json(['Message' => 'Status Updated Successfully',], 200);
        } else {
            return response()->json(['Message' => 'Invalid Status',], 400);
        }
    }

    public function deleteJob(Request $request, $jobId)
    {
        // Check Request User Role == Staff OR Company
        if ($request->user()->role != "company" and $request->user()->role != "staff") {
            return response()->json([
                'Message' => 'Access Denied'
            ], 403);
        }

        $job = Job::find($jobId);
        $job->delete();

        return response()->json(['Message' => 'Deleted Successfully',], 200);
    }

    public function search($keyword)
    {
        $searchResult = Job::query()
            ->where('status', '=', 'active')
            ->where('title', 'LIKE', "%{$keyword}%")
            ->paginate(4);;

        if ($searchResult->isEmpty()) {
            return response()->json(['message' => 'No results found'], 404);
        }

        return response()->json(['search results' => $searchResult], 200);
    }
}
