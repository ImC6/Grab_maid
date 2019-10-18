<?php

namespace App\Http\Controllers\Admin;

use App\Models\Zone;
use App\Models\Region;
use App\Models\State;
use App\Models\City;
use App\Models\Area;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class ZoneController extends Controller
{
    public function getZones(Request $request)
    {
        try {
            $zone = Zone::where('id','<','20')->get();

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'zones' => $zone,
        ]);
    }

    public function createZone(Request $request)
    {
        $zone = new Zone;
        $zone->postcode = $request->get('postcode');
        $zone->area = $request->get('detail');
        $zone->region = $request->get('region');
        $zone->city = $request->get('city');
        $zone->state = $request->get('state');

        try {
            $zone->save();

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'create_success',
            'service' => $zone
        ]);
    }

    public function updateZone(Request $request, $id)
    {

        if (!$zone = Zone::find($id)) {
            return response()->json([
                'status' => 404,
                'message' => 'zone_not_found'
            ]);
        }

        $update = Zone::where('id', $id)->first();
        $update->postcode = $request->postcode;
        $update->area = $request->area;
        $update->region = $request->region;
        $update->city = $request->city;
        $update->state = $request->state;


        try {
            $update->save();

            if (!$update) {
                return response()->json([
                    'status' => 500,
                    'message' => 'Database error'
                ]);
            }

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'update_success',
            'updated_data' => $zone
        ]);
    }

    public function deleteZone(Request $request, $id){
        {
            try {
                $delete = Zone::destroy($id);
    
            } catch (QueryException $e) {
                return response()->json([
                    'status' => 500,
                    'message' => 'database_error',
                    'errors' => $e
                ]);
            }
    
            return response()->json([
                'status' => 200,
                'message' => 'delete_success',
                'updated_data' => [
                    'id' => $id
                ]
            ]);
        }
    }

    public function getRegions(Request $request)
    {
        try {
            $regions = Region::all();

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'regions' => $regions
        ]);
    }

    public function createRegions(Request $request){
        $region = new Region;
        $region->name = $request->name;

        try {
            $region->save();

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'create_success',
            'service' => $region
        ]);
    }

    public function createCities(Request $request){
        $city = new City;
        $city->name = $request->name;

        try {
            $city->save();

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'create_success',
            'service' => $city
        ]);
    }

    public function createStates(Request $request)
    {
        $state = new State;
        $state->name = $request->name;

        try {
            $state->save();

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'create_success',
            'service' => $state
        ]);
    }

    public function getAllCities(Request $request)
    {
        try {
            $cities = Zone::select('city')->enabled()->distinct()->get()->pluck('city');

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'cities' => $cities
        ]);
    }

    public function getAllStates(Request $request)
    {
        try {
            $states = Zone::select('state')->enabled()->distinct()->get()->pluck('state');

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'states' => $states
        ]);
    }
    
    public function getAllLocations(Request $request)
    {
        $state = $request->get('state');
        $city = $request->get('city');

        try {
            $zonesQuery = Zone::select('state', 'city', 'region')->enabled();
            $states =  $zonesQuery->distinct()->get()->pluck('state')->unique()->values();

            if ($state) {
                $zonesQuery->where('state', $state);
            }

            if ($city) {
                $zonesQuery->where('city', $city);
            }

            $zones = $zonesQuery->distinct()->get();
            $cities = $zones->pluck('city')->unique()->values();
            $regions = $zones->pluck('region')->unique();

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'states' => $states,
            'cities' => $cities,
            'regions' => $regions,
        ]);
    }

    public function getLocationInString(Request $request)
    {
        try {
            $zonesQuery = Zone::select('id', 'state', 'city', 'region')->enabled();
            $zones = $zonesQuery->distinct()->get();

            $returnData = $zones->sortBy('region')->values()->map(function($zone) {
                return [
                    'id' => $zone->id,
                    'location' => $zone->region . ', ' . $zone->city . ', ' . $zone->state
                ];
            });

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'locations' => $returnData
        ]);
    }

    //New

    public function getStates(Request $request)
    {
        try {
            $states = State::all();

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'states' => $states
        ]);
    }



    public function updateState(Request $request, $stateId)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ]);
        }

        if (!$state = State::where('id', $stateId)->first()) {
            return response()->json([
                'status' => 400,
                'message' => 'State not found'
            ]);
        }

        try {
            $code = $request->get('code');
            $name = $request->get('name');

            $state->code = $code;
            $state->name = $name;
            $state->save();

            $states = State::all();

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'State is updated',
            'states' => $states
        ]);
    }

    public function deleteState(Request $request, $stateId)
    {
        if (!$state = State::where('id', $stateId)->first()) {
            return response()->json([
                'status' => 400,
                'message' => 'State not found'
            ]);
        }

        try {
            $cities = $state->cities;
            if ($cities) {
                $cityIds = $cities->pluck('id')->toArray();
                Area::whereIn('city_id', $cityIds)->delete();
            }
            $state->cities()->delete();
            $state->delete();
            $states = State::all();

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'State is deleted!',
            'states' => $states
        ]);
    }

    public function getCities(Request $request)
    {
        try {
            $cities = City::all();

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'cities' => $cities
        ]);
    }

    public function createCity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'state_id' => 'numeric',
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ]);
        }

        $code = $request->get('code');
        $name = $request->get('name');
        $stateId = $request->get('state_id');

        try {
            if ($stateId) {
                if (!$state = State::where('id', $stateId)->first()) {
                    return response()->json([
                        'status' => 400,
                        'message' => 'State not found'
                    ]);
                }
            }

            $city = new City();
            $city->code = $code;
            $city->name = $name;

            if ($state) {
                $state->cities()->save($city);
            } else {
                $city->save();
            }

            $cities = City::all();

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'City is created',
            'cities' => $cities
        ]);
    }

    public function updateCity(Request $request, $cityId)
    {
        $validator = Validator::make($request->all(), [
            'state_id' => 'numeric',
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ]);
        }

        $code = $request->get('code');
        $name = $request->get('name');
        $stateId = $request->get('state_id');

        try {
            if (!$city = City::where('id', $cityId)->first()) {
                return response()->json([
                    'status' => 400,
                    'message' => 'City not found'
                ]);
            }

            if ($stateId) {
                if (!State::where('id', $stateId)->exists()) {
                    return response()->json([
                        'status' => 400,
                        'message' => 'State not found'
                    ]);
                }
                $city->state_id = $stateId;
            }

            $city->code = $code;
            $city->name = $name;
            $city->save();

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'City is updated',
            'city' => $city
        ]);
    }

    public function deleteCity(Request $request, $cityId)
    {
        if (!$city = City::where('id', $cityId)->first()) {
            return response()->json([
                'status' => 400,
                'message' => 'City not found'
            ]);
        }

        try {
            $city->areas()->delete();
            $city->delete();

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'City is deleted!',
        ]);
    }

    public function getAllAreaNew(Request $request)
    {
        $state = $request->get('state');
        $city = $request->get('city');

        try {
            $areaQuery = Area::query();
            if ($state) {
                $areaQuery->where('state', $state);
            }
            if ($city) {
                $areaQuery->where('city', $city);
            }

            $areas = $areaQuery->get();

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'areas' => $areas
        ]);
    }

    public function createArea(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'state_id' => 'numeric',
            'city_id' => 'numeric',
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ]);
        }

        $name = $request->get('name');
        $stateId = $request->get('state_id');
        $cityId = $request->get('city_id');

        try {
            if ($stateId) {
                if (State::where('id', $stateId)->exists()) {
                    return response()->json([
                        'status' => 400,
                        'message' => 'State not found'
                    ]);
                }
            }

            if ($cityId) {
                if (City::where('id', $cityId)->exists()) {
                    return response()->json([
                        'status' => 400,
                        'message' => 'City not found'
                    ]);
                }
            }

            $area = new Area();
            $area->state_id = $stateId;
            $area->city_id = $cityId;
            $area->name = $name;
            $area->save();

            $areasQuery = Area::query();
            if ($stateId) {
                $areasQuery->where('state_id', $stateId);
            }
            if ($cityId) {
                $areasQuery->where('city_id', $cityId);
            }
            $areas = $areasQuery->get();

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Area is created',
            'areas' => $areas
        ]);
    }

    public function updateArea(Request $request, $areaId)
    {
        $validator = Validator::make($request->all(), [
            'state_id' => 'numeric',
            'city_id' => 'numeric',
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ]);
        }

        $name = $request->get('name');
        $stateId = $request->get('state_id');
        $cityId = $request->get('city_id');

        try {
            if (!$area = Area::where('id', $areaId)->first()) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Area not found'
                ]);
            }

            if ($stateId) {
                if (State::where('id', $stateId)->exists()) {
                    return response()->json([
                        'status' => 400,
                        'message' => 'State not found'
                    ]);
                }
            }

            if ($cityId) {
                if (City::where('id', $cityId)->exists()) {
                    return response()->json([
                        'status' => 400,
                        'message' => 'City not found'
                    ]);
                }
            }

            $area->state_id = $stateId;
            $area->city_id = $cityId;
            $area->name = $name;
            $area->save();

            $areasQuery = Area::query();
            if ($stateId) {
                $areasQuery->where('state_id', $stateId);
            }
            if ($cityId) {
                $areasQuery->where('city_id', $cityId);
            }
            $areas = $areasQuery->get();

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Area is updated',
            'areas' => $areas
        ]);
    }

    public function deleteArea(Request $request, $areaId)
    {
        if (!$area = Area::where('id', $areaId)->first()) {
            return response()->json([
                'status' => 400,
                'message' => 'Area not found'
            ]);
        }

        try {
            $area->delete();

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Area is deleted!',
        ]);
    }
}
