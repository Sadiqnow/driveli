<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\State;
use App\Models\LocalGovernment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * LGA Fallback Controller
 * 
 * Provides LGA data with fallback to static data when database is not populated
 */
class LGAFallbackController extends Controller
{
    /**
     * Static LGA data as fallback
     */
    private static $staticLGAs = [
        'Lagos' => [
            'Agege', 'Ajeromi-Ifelodun', 'Alimosho', 'Amuwo-Odofin', 'Apapa',
            'Badagry', 'Epe', 'Eti-Osa', 'Ibeju-Lekki', 'Ifako-Ijaiye',
            'Ikeja', 'Ikorodu', 'Kosofe', 'Lagos Island', 'Lagos Mainland',
            'Mushin', 'Ojo', 'Oshodi-Isolo', 'Shomolu', 'Surulere'
        ],
        'Ogun' => [
            'Abeokuta North', 'Abeokuta South', 'Ado-Odo/Ota', 'Egbado North',
            'Egbado South', 'Ewekoro', 'Ifo', 'Ijebu East', 'Ijebu North',
            'Ijebu North East', 'Ijebu Ode', 'Ikenne', 'Imeko Afon',
            'Ipokia', 'Obafemi Owode', 'Odeda', 'Odogbolu', 'Ogun Waterside',
            'Remo North', 'Shagamu'
        ],
        'Kano' => [
            'Ajingi', 'Albasu', 'Bagwai', 'Bebeji', 'Bichi', 'Bunkure',
            'Dala', 'Dambatta', 'Dawakin Kudu', 'Dawakin Tofa', 'Doguwa',
            'Fagge', 'Gabasawa', 'Garko', 'Garun Mallam', 'Gaya', 'Gezawa',
            'Gwale', 'Gwarzo', 'Kabo', 'Kano Municipal', 'Karaye', 'Kibiya',
            'Kiru', 'Kumbotso', 'Kunchi', 'Kura', 'Madobi', 'Makoda',
            'Minjibir', 'Nasarawa', 'Rano', 'Rimin Gado', 'Rogo', 'Shanono',
            'Sumaila', 'Takai', 'Tarauni', 'Tofa', 'Tsanyawa', 'Tudun Wada',
            'Ungogo', 'Warawa', 'Wudil'
        ],
        'FCT' => [
            'Abaji', 'Abuja Municipal', 'Bwari', 'Gwagwalada', 'Kuje', 'Kwali'
        ],
        'Rivers' => [
            'Abua/Odual', 'Ahoada East', 'Ahoada West', 'Akuku-Toru', 'Andoni',
            'Asari-Toru', 'Bonny', 'Degema', 'Eleme', 'Emuoha', 'Etche',
            'Gokana', 'Ikwerre', 'Khana', 'Obio/Akpor', 'Ogba/Egbema/Ndoni',
            'Ogu/Bolo', 'Okrika', 'Omuma', 'Opobo/Nkoro', 'Oyigbo',
            'Port Harcourt', 'Tai'
        ]
    ];

    /**
     * Get LGAs by state ID with fallback
     */
    public function getLGAs(Request $request, $stateId = null): JsonResponse
    {
        $stateId = $stateId ?? $request->input('state_id');
        
        if (!$stateId) {
            return response()->json([
                'success' => false,
                'message' => 'State ID is required',
                'data' => []
            ], 400);
        }

        try {
            // First try to get from database
            $lgas = LocalGovernment::where('state_id', $stateId)->orderBy('name')->get();
            
            if ($lgas->isNotEmpty()) {
                return response()->json($lgas->map(function($lga) {
                    return [
                        'id' => $lga->id,
                        'name' => $lga->name,
                        'state_id' => $lga->state_id
                    ];
                }));
            }

            // Fallback to static data if database is empty
            $state = State::find($stateId);
            if (!$state) {
                return response()->json([
                    'success' => false,
                    'message' => 'State not found',
                    'data' => []
                ], 404);
            }

            $staticLGAs = self::$staticLGAs[$state->name] ?? [];
            
            if (empty($staticLGAs)) {
                return response()->json([]);
            }

            // Return static data with mock IDs
            $data = [];
            foreach ($staticLGAs as $index => $lgaName) {
                $data[] = [
                    'id' => ($stateId * 1000) + $index + 1,
                    'name' => $lgaName,
                    'state_id' => $stateId
                ];
            }

            return response()->json($data);

        } catch (\Exception $e) {
            \Log::error('LGA API Error: ' . $e->getMessage());
            
            // Final fallback - return empty array with info message
            return response()->json([
                'success' => false,
                'message' => 'Unable to load LGA data. Please try again later.',
                'data' => []
            ], 500);
        }
    }
}