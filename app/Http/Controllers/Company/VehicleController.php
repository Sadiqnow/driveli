<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Vehicle;
use App\Models\Fleet;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $company = Auth::guard('company')->user();

        $query = Vehicle::where('company_id', $company->id)->with('fleet');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('fleet_id')) {
            $query->where('fleet_id', $request->fleet_id);
        }

        if ($request->filled('vehicle_type')) {
            $query->where('vehicle_type', $request->vehicle_type);
        }

        $vehicles = $query->latest()->paginate(15);

        $fleets = Fleet::where('company_id', $company->id)->select('id', 'name')->get();

        $stats = [
            'total_vehicles' => Vehicle::where('company_id', $company->id)->count(),
            'active_vehicles' => Vehicle::where('company_id', $company->id)->where('status', 'active')->count(),
            'maintenance_vehicles' => Vehicle::where('company_id', $company->id)->where('status', 'maintenance')->count(),
            'available_vehicles' => Vehicle::where('company_id', $company->id)->where('status', 'active')->count(),
        ];

        return view('company.vehicles', compact('vehicles', 'fleets', 'stats'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $company = Auth::guard('company')->user();

        $validator = Validator::make($request->all(), [
            'registration_number' => 'required|string|max:20|unique:vehicles',
            'vehicle_type' => 'required|in:truck,van,pickup,motorcycle,car',
            'make' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'capacity' => 'nullable|numeric|min:0',
            'fleet_id' => 'nullable|exists:fleets,id',
            'status' => 'required|in:active,inactive,maintenance',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check if fleet belongs to company
        if ($request->filled('fleet_id')) {
            $fleet = Fleet::where('id', $request->fleet_id)
                         ->where('company_id', $company->id)
                         ->first();
            if (!$fleet) {
                $validator->errors()->add('fleet_id', 'Invalid fleet selected.');
            }
        }

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            $vehicleData = $request->all();
            $vehicleData['company_id'] = $company->id;

            $vehicle = Vehicle::create($vehicleData);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Vehicle added successfully!',
                    'data' => $vehicle
                ]);
            }

            return redirect()->route('company.vehicles.index')
                ->with('success', 'Vehicle added successfully!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to add vehicle'
                ], 500);
            }

            return back()->withErrors(['error' => 'Failed to add vehicle. Please try again.'])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Vehicle $vehicle)
    {
        $this->authorize('view', $vehicle);

        $vehicle->load(['fleet', 'company']);

        return view('company.vehicles.show', compact('vehicle'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle);

        $company = Auth::guard('company')->user();

        $validator = Validator::make($request->all(), [
            'registration_number' => 'required|string|max:20|unique:vehicles,registration_number,' . $vehicle->id,
            'vehicle_type' => 'required|in:truck,van,pickup,motorcycle,car',
            'make' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'capacity' => 'nullable|numeric|min:0',
            'fleet_id' => 'nullable|exists:fleets,id',
            'status' => 'required|in:active,inactive,maintenance',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check if fleet belongs to company
        if ($request->filled('fleet_id')) {
            $fleet = Fleet::where('id', $request->fleet_id)
                         ->where('company_id', $company->id)
                         ->first();
            if (!$fleet) {
                $validator->errors()->add('fleet_id', 'Invalid fleet selected.');
            }
        }

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            $vehicle->update($request->all());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Vehicle updated successfully!'
                ]);
            }

            return redirect()->route('company.vehicles.show', $vehicle)
                ->with('success', 'Vehicle updated successfully!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update vehicle'
                ], 500);
            }

            return back()->withErrors(['error' => 'Failed to update vehicle. Please try again.'])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicle $vehicle)
    {
        $this->authorize('delete', $vehicle);

        try {
            $vehicle->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Vehicle deleted successfully!'
                ]);
            }

            return redirect()->route('company.vehicles.index')
                ->with('success', 'Vehicle deleted successfully!');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete vehicle'
                ], 500);
            }

            return back()->withErrors(['error' => 'Failed to delete vehicle. Please try again.']);
        }
    }
}
