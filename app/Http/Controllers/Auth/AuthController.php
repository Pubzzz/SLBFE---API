<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Citizen;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validate
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        $credentials = request(['email', 'password']);

        if (!Auth::attempt($credentials))
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);

        $user = $request->user();
        $userId = $user->id;
        $userRole = $user->role;

        // Access Token
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        $token->save();

        if ($userRole == "citizen") {
            $nic = Citizen::where('user_id', $userId)->first()->nic;

            return response()->json([
                'access_token'  => $tokenResult->accessToken,
                'token_type'    => 'Bearer',
                'user_id'       => $userId,
                'role'          => $userRole,
                'nic'           => $nic
            ]);
        } elseif ($userRole == "company") {
            $companyId = Company::where('user_id', $userId)->first()->id;

            return response()->json([
                'access_token'  => $tokenResult->accessToken,
                'token_type'    => 'Bearer',
                'user_id'       => $userId,
                'role'          => $userRole,
                'company_id'    => $companyId
            ]);
        } else {
            return response()->json([
                'access_token'  => $tokenResult->accessToken,
                'token_type'    => 'Bearer',
                'user_id'       => $userId,
                'role'          => $userRole
            ]);
        }
    }


    public function register(Request $request)
    {
        // Valdiation
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|max:60',
            'lastName'  => 'required|string|max:60',
            'role'      => 'required|string|max:10',
            'email'     => 'required|string|email|unique:users',
            'password'  => 'required|string'
        ]);

        // Return Validation Errors
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if ($request->role == "citizen") {
            // Valdiation
            $validator = Validator::make($request->all(), [
                'nic'                   => 'required|string|max:15|unique:citizens',
                'passportNo'            => 'nullable|string|max:15',
                'passportExpiryDate'    => 'nullable|date',
                'dob'                   => 'required|date',
                'mobile'                => 'required|string|max:15',
                'profession'            => 'required|string|max:60',
                'employeeName'          => 'nullable|string|max:80',
                'expLevel'              => 'required|string',
                'industryId'            => 'required|numeric',
                'addressLineOne'        => 'required|string|max:40',
                'addressLineTwo'        => 'nullable|string|max:40',
                'city'                  => 'required|string|max:40',
                'postalCode'            => 'nullable|string|max:10'
            ]);
        } elseif ($request->role == "company") {
            // Valdiation
            $validator = Validator::make($request->all(), [
                'companyName'   => 'required|string|max:80',
                'location'      => 'required|string|max:30',
                'industryId'    => 'required|numeric',
                'contactNo'     => 'required|string|max:20',
            ]);
        }

        // Return Validation Errors
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Insert Data to DB
        $user = new User;
        $user->first_name = $request->firstName;
        $user->last_name = $request->lastName;
        $user->role = $request->role;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->status = "active";
        $user->save();

        $userId = $user->id;
        $userRole = $user->role;


        if ($userRole == "citizen") {

            // Insert New Citizen
            $citizen = new Citizen;
            $citizen->nic = $request->nic;
            $citizen->passport_no = $request->passportNo;
            $citizen->passport_expiry_date = $request->passportExpiryDate;
            $citizen->date_of_birth = $request->dob;
            $citizen->mobile = $request->mobile;
            $citizen->profession = $request->profession;
            $citizen->employee_name = $request->employeeName;
            $citizen->experience_level = $request->expLevel;
            $citizen->industry_id = $request->industryId;
            $citizen->user_id = $userId;
            $citizen->status = "unverified";
            $citizen->save();

            // Insert Address
            $address = new Address;
            $address->citizen_id = $citizen->id;
            $address->address_line_one = $request->addressLineOne;
            $address->address_line_two = $request->addressLineTwo;
            $address->city = $request->city;
            $address->postal_code = $request->postalCode;
            $address->save();

            DB::table('documents')->insert([
                ['type' => 'NIC', 'citizen_id' => $citizen->id],
                ['type' => 'Passport', 'citizen_id' => $citizen->id],
                ['type' => 'CV', 'citizen_id' => $citizen->id],
                ['type' => 'Birth Certificate', 'citizen_id' => $citizen->id],
            ]);

            DB::table('current_locations')->insert([
                ['location' => $request->location, 'citizen_id' => $citizen->id],
            ]);

        } elseif ($userRole == "company") {
            // Insert New Company
            $comapny = new Company;
            $comapny->name = $request->companyName;
            $comapny->location = $request->location;
            $comapny->user_id = $userId;
            $comapny->contact_no = $request->contactNo;
            $comapny->industry_id = $request->industryId;
            $comapny->status = "unverified";
            $comapny->save();
        }

        // Access Token
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        $token->save();

        if ($userRole == "citizen") {
            $nic = Citizen::where('user_id', $userId)->first()->nic;

            return response()->json([
                'access_token'  => $tokenResult->accessToken,
                'token_type'    => 'Bearer',
                'user_id'       => $userId,
                'role'          => $userRole,
                'nic'           => $nic
            ]);
        } elseif ($userRole == "company") {
            $companyId = Company::where('user_id', $userId)->first()->id;

            return response()->json([
                'access_token'  => $tokenResult->accessToken,
                'token_type'    => 'Bearer',
                'user_id'       => $userId,
                'role'          => $userRole,
                'company_id'    => $companyId
            ]);
        } else {
            return response()->json([
                'access_token'  => $tokenResult->accessToken,
                'token_type'    => 'Bearer',
                'user_id'       => $userId,
                'role'          => $userRole
            ]);
        }
    }


    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'Successfully logged out'
        ], 201);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function changePassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'currentPassword'  => 'required|string',
            'newPassword'  => 'required|string',
        ]);

        // Return Errors
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Logged In User
        $user = $request->user();

        if (Hash::check($request->currentPassword, $user->password)) {
            $user->password = Hash::make($request->newPassword);
            $user->save();

            return response()->json([
                'Message' => 'Password Changed Successfully'
            ], 200);
        } else {
            return response()->json([
                'Message' => 'Password Mismatch'
            ], 403);
        }
    }
}
