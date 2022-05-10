<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Job;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{

    public function update(Request $request, $companyId)
    {
        // Check Request User Role == company
        if ($request->user()->role != "company") {
            return response()->json([
                'Message' => 'Access Denied'
            ], 403);
        }

        // Valdiation
        $validator = Validator::make($request->all(), [
            'companyName'   => 'required|string|max:80',
            'location'      => 'required|string|max:30',
            'industryId'    => 'required|numeric',
            'contactNo'     => 'required|string|max:20',
        ]);

        // Return Validation Errors
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $company = Company::find($companyId);

        if ($company->user_id == $request->user()->id) {
            $company->name = $request->companyName;
            $company->location = $request->location;
            $company->industry_id = $request->industryId;
            $company->contact_no = $request->contactNo;
            $company->save();

            return response()->json([
                'Message' => 'Updated Successfully'
            ], 200);
        }
    }

    public function index()
    {
        $companies = Company::query()
            ->join('industries as i', 'i.id', 'companies.industry_id')
            ->select(
                'companies.id',
                'companies.name as company_name',
                'location',
                'contact_no',
                'i.name',
                'companies.status'
            )
            ->get();

        return response()->json([
            'companies' => $companies
        ], 200);

    }

    public function show($companyId)
    {
        $company = Company::query()
            ->join('industries as i', 'i.id', 'companies.industry_id')
            ->join('users as u', 'u.id', 'companies.user_id')
            ->select(
                'companies.id',
                'companies.name as company_name',
                'location',
                'contact_no',
                'i.name as industry_name',
                'companies.status',
                'u.id as user_id',
                'u.first_name',
                'u.last_name',
                'u.email',
                'u.role',
                'u.created_at as user_created_at',
                'u.status as user_status'
            )
            ->where('companies.id', $companyId)
            ->first();

        return response()->json([
            'company' => $company
        ], 200);
    }

    public function deleteCompany(Request $request, $companyId)
    {

        if ($company = Company::find($companyId)) {
            $userId = $company->user_id;
            $loggedInUserId = $request->user()->id;

            if ($loggedInUserId != $userId) {
                return response()->json(['Message' => 'Unauthorized'], 403);
            }

            $user = User::find($userId);

            $user->delete();
            $company->delete();

            return response()->json(['Message' => 'Deleted Successfully'], 200);
        } else {
            return response()->json(['Message' => 'Company Not Found'], 404);
        }
    }

    public function industries()
    {
        $data = DB::table('industries')->get();
        return response()->json(['industries' => $data], 200);

    }

    public function companyJobs(Request $request)
    {
        // Check Request User Role == company
        if ($request->user()->role != "company") {
            return response()->json([
                'Message' => 'Access Denied'
            ], 403);
        }

        $userId = $request->user()->id;

        $companyId = Company::where('user_id', $userId)->first()->id;

        $jobPosts = Job::where('company_id', $companyId)->get();

        return response()->json(['jobs' => $jobPosts], 200);
    }
}
