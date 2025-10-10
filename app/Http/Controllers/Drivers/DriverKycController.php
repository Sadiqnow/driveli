<?php

namespace App\Http\Controllers\Drivers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DriverKycController extends Controller
{
    // Return a simple JSON response for index
    public function index(Request $request)
    {
        return response()->json(['status' => 'ok']);
    }

    public function summary(Request $request)
    {
        return response()->json(['summary' => []]);
    }

    public function showStep1(Request $request)
    {
        return response()->json(['step' => 1]);
    }

    public function postStep1(Request $request)
    {
        // simple validation mimic
        $request->validate([
            'license_number' => 'required',
            'date_of_birth' => 'required|date',
        ]);

        return response()->json(['success' => true], 200);
    }

    public function showStep2(Request $request)
    {
        return response()->json(['step' => 2]);
    }

    public function postStep2(Request $request)
    {
        // minimal validation
        $request->validate([
            'first_name' => 'required',
            'surname' => 'required',
        ]);

        return response()->json(['success' => true], 200);
    }

    public function showStep3(Request $request)
    {
        return response()->json(['step' => 3]);
    }

    public function postStep3(Request $request)
    {
        // Accept file uploads in tests; validate file presence optionally
        $request->validate([
            'nin_document' => 'nullable|file',
        ]);

        return response()->json(['success' => true], 200);
    }
}
