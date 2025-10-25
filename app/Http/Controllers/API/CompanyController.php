<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyMember;
use App\Services\CompanyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    protected $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    public function show(Request $request)
    {
        $company = $request->user();

        $company->load(['profile', 'members']);

        return response()->json([
            'status' => 'success',
            'data' => $company,
        ]);
    }

    public function update(Request $request)
    {
        $company = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:companies,email,' . $company->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'website' => 'nullable|url|max:255',
            'industry' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $company->update($request->only([
            'name', 'email', 'phone', 'address', 'website', 'industry', 'description'
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Company updated successfully',
            'data' => $company,
        ]);
    }

    public function members(Request $request)
    {
        $company = $request->user();

        $members = $company->members()->with('user')->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $members,
        ]);
    }

    public function addMember(Request $request)
    {
        $company = $request->user();

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|string|max:100',
            'permissions' => 'nullable|array',
        ]);

        $member = $this->companyService->addMember($company, $request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Member added successfully',
            'data' => $member,
        ], 201);
    }

    public function updateMember(Request $request, CompanyMember $member)
    {
        $this->authorize('update', $member->company);

        $request->validate([
            'role' => 'sometimes|string|max:100',
            'permissions' => 'nullable|array',
            'status' => 'sometimes|in:active,inactive',
        ]);

        $member->update($request->only(['role', 'permissions', 'status']));

        return response()->json([
            'status' => 'success',
            'message' => 'Member updated successfully',
            'data' => $member,
        ]);
    }

    public function removeMember(CompanyMember $member)
    {
        $this->authorize('update', $member->company);

        $member->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Member removed successfully',
        ]);
    }

    public function dashboard(Request $request)
    {
        $company = $request->user();

        $dashboard = $this->companyService->getDashboardData($company);

        return response()->json([
            'status' => 'success',
            'data' => $dashboard,
        ]);
    }
}
