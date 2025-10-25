<?php

namespace App\Http\Controllers\API;

use App\Helpers\DrivelinkHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use App\Models\CompanyMember;
use App\Services\CompanyService;
use Illuminate\Http\Request;

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

        return DrivelinkHelper::respondJson('success', 'Company profile retrieved successfully', new CompanyResource($company));
    }

    public function update(UpdateCompanyRequest $request)
    {
        $company = $request->user();

        $company->update($request->validated());

        return DrivelinkHelper::respondJson('success', 'Company updated successfully', new CompanyResource($company));
    }

    public function members(Request $request)
    {
        $company = $request->user();

        $members = $company->members()->with('user')->paginate(15);

        return DrivelinkHelper::respondJson('success', 'Company members retrieved successfully', $members);
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

        return DrivelinkHelper::respondJson('success', 'Member added successfully', $member, 201);
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

        return DrivelinkHelper::respondJson('success', 'Member updated successfully', $member);
    }

    public function removeMember(CompanyMember $member)
    {
        $this->authorize('update', $member->company);

        $member->delete();

        return DrivelinkHelper::respondJson('success', 'Member removed successfully');
    }

    public function dashboard(Request $request)
    {
        $company = $request->user();

        $dashboard = $this->companyService->getDashboardData($company);

        return DrivelinkHelper::respondJson('success', 'Dashboard data retrieved successfully', $dashboard);
    }
}
