<?php

namespace App\Http\Controllers\Admin;

use App\User;
use App\Models\Company;
use App\Models\Service;
use App\Models\VendorService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ServiceController extends Controller
{
    public function getAllServices(Request $request)
    {
        try {
            $services = Service::all();

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'database_error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'services' => $services
        ]);
    }

    public function getServiceById(Request $request, $id)
    {
        try {
            $service = Service::find($id);
        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'database_error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'service' => $service
        ]);
    }

    public function createService(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'details' => 'required|string|max:100',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ]);
        }

        $name = trimSpaces($request->get('name'));
        $slug = slugify($name);

        $service = new Service;
        $service->name = $name;
        $service->slug = $slug;
        $service->details = $request->get('details');

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $serviceImg = $request->file('image');
            $service->image = $serviceImg->store('public');
        }

        try {
            $service->save();

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'database_error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Service is created',
            'service' => $service
        ]);
    }

    public function updateService(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:50',
            'details' => 'string|max:100',
            // 'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ]);
        }

        $updateArr = [];

        if ($name = $request->get('name')) {
            $name = trimSpaces($name);
            $updateArr['name'] = $name;
            $updateArr['slug'] = slugify($name);
        }

        if ($details = $request->get('details')) {
            $updateArr['details'] = $details;
        }

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $serviceImg = $request->file('image');
            $updateArr['image'] = $serviceImg->store('public');
        }

        if (count($updateArr) === 0) {
            return response()->json([
                'status' => 422,
                'message' => 'missing_parameter'
            ]);
        }

        try {
            $update = Service::where('id', $id)->update($updateArr);

            if (!$update) {
                return response()->json([
                    'status' => 500,
                    'message' => 'database_error'
                ]);
            }
        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'database_error',
                'errors' => $e
            ]);
        }

        $returnArr = ['id' => $id];

        if ($name) {
            $returnArr['name'] = $name;
        }

        if ($details) {
            $returnArr['details'] = $details;
        }

        return response()->json([
            'status' => 200,
            'message' => 'Service is updated',
            'updated_data' => $returnArr
        ]);
    }

    public function deleteService(Request $request, $id) 
    {
        try {
            $delete = Service::destroy($id);

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

    public function getVendorService(Request $request, $companyId)
    {
        try {
            // $vendor = User::where('guid', $guid)->vendor()->with('services')->first();
            $company = Company::where('id', $companyId)->with('services', 'vendor')->first();

            if (!$company) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Company not found'
                ]);
            }

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'database_error',
                'errors' => $e
            ]);
        }

        $cleanerGuids = $company->services->map(function($service) {
            return json_decode($service->pivot->cleaners);
        })->flatten(1)->unique();
        $cleaners = User::select('guid', 'name', 'mobile_no', 'email')->whereIn('guid', $cleanerGuids->toArray())->get()->keyBy('guid');

        $services = $company->services->map(function($service) use ($cleaners, $company) {
            $cleanerGuids = json_decode($service->pivot->cleaners);
            $filteredUser = [];
            foreach($cleanerGuids as $guid) {
                $filteredUser[] = $cleaners->get($guid);
            }

            return [
                'service_id' => $service->id,
                'name' => $service->name,
                'vendor_service_id' => $service->pivot->id,
                'regions' => json_decode($service->pivot->regions),
                'city' => $service->pivot->city,
                'state' => $service->pivot->state,
                'start_time' => $service->pivot->start_time,
                'duration' => $service->pivot->duration,
                'start_date' => $service->pivot->start_date ? $service->pivot->start_date->format('Y-m-d') : null,
                'end_date' => $service->pivot->end_date ? $service->pivot->end_date->format('Y-m-d') : null,
                'price' => $service->pivot->price,
                'cleaners' => $filteredUser,
                'working_day' => $service->pivot->working_day,
                'created_at' => $service->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'status' => 200,
            'vendor' => $company->vendor,
            'services' => $services
        ]);

    }

    public function createVendorService(Request $request, $companyId)
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|numeric|exists:services,id',
            'start_time' => 'required|string',
            'duration' => 'required|numeric|min:2|max:6',
            'regions' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'price' => 'required|numeric',
            'cleaners' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'working_day' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ]);
        }

        // $serIdsValidation = $this->serviceIdValidation($request->get('service_id'));
        // if (!$serIdsValidation) {
        //     return response()->json([
        //         'status' => 400,
        //         'errors' => [
        //             'service_id' => ['Service value is not valid']
        //         ]
        //     ]);
        // }

        $cleanerValidation = $this->jsonValidation($request->get('cleaners'));
        if (!$cleanerValidation) {
            return response()->json([
                'status' => 400,
                'errors' => [
                    'cleaners' => ['Cleaner value is not valid']
                ]
            ]);
        }

        $regionValidation = $this->regionsValidation($request->get('regions'));
        if (!$regionValidation) {
            return response()->json([
                'status' => 400,
                'errors' => [
                    'regions' => ['Region value is not valid']
                ]
            ]);
        }

        $cleanersArr = $cleanerValidation;
        $serviceId = $request->get('service_id');
        $startTime = $request->get('start_time');
        $duration = $request->get('duration');
        $regions = $request->get('regions');
        $city = $request->get('city');
        $state = $request->get('state');
        $price = $request->get('price');
        $cleaners = $request->get('cleaners');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $workingDayArr = [];

        if ($workingDay = $request->get('working_day')) {
            $workingDayValidation = $this->workingDayValidation($workingDay);
            if (!$workingDayValidation) {
                return response()->json([
                    'status' => 400,
                    'errors' => [
                        'working_day' => ['Working day value is not valid']
                    ]
                ]);
            }

            $workingDayArr = $workingDayValidation;
        }

        try {
            // TODO use guid
            $company = Company::where('id', $companyId)->with('services')->first();

            if (!$company) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Company not found'
                ]);
            }

            $vendor = $company->vendor;

            if (!$vendor) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Vendor not found'
                ]);
            }

            $count = User::whereIn('guid', $cleanersArr)->cleaner()->cleanerOf($vendor->id)->count();
            if ($count !== count($cleanersArr)) {
                return response()->json([
                    'status' => 400,
                    'errors' => [
                        'cleaners' => ['Cleaners do not belong to this vendor']
                    ]
                ]);
            }

            // check if cleaner is ady been tied to a session between start data and end date and specific time
            if ($this->cleanerIsInAnotherSession($cleanersArr, $startDate, $endDate, $startTime, $duration, $workingDayArr, null)) {
                return response()->json([
                    'status' => 400,
                    'errors' => [
                        'cleaners' => ['Cleaners is already selected for another session']
                    ]
                ]);
            }

            // $vendorService = new VendorService();
            // $vendorService->guid = guidv4();
            // $vendorService->start_time = $start_time;
            // $vendorService->duration = $duration;
            // $vendorService->end_time = $this->generateEndTime($startTime, $duration);
            // $vendorService->regions = $regions;
            // $vendorService->city = $city;
            // $vendorService->state = $state;
            // $vendorService->price = $price;
            // $vendorService->cleaners = $cleaners;
            // $vendorService->working_day = $workingDay ?? [];
            // $vendorService->status = 1;

            $createArr = [
                'guid' => guidv4(),
                'start_time' => $startTime,
                'duration' => $duration,
                'end_time' => $this->generateEndTime($startTime, $duration),
                'regions' => $regions,
                'city' => $city,
                'state' => $state,
                'price' => $price,
                'cleaners' => $cleaners,
                'working_day' => $workingDay ?? [],
                'status' => 1,
            ];

            if ($startDate) {
                // $vendorService->start_date = $startDate;
                $createArr['start_date'] = $startDate;
            }

            if ($endDate) {
                // $vendorService->end_date = $endDate;
                $createArr['end_date'] = $endDate;
            }

            $company->services()->attach($serviceId, $createArr);

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'database_error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Service is created',
        ]);
    }

    public function updateVendorService(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|numeric|exists:services,id',
            'start_time' => 'required|string',
            'duration' => 'required|numeric|min:2|max:6',
            'regions' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'price' => 'required|numeric',
            'cleaners' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'working_day' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ]);
        }

        $cleanerValidation = $this->jsonValidation($request->get('cleaners'));
        if (!$cleanerValidation) {
            return response()->json([
                'status' => 400,
                'errors' => [
                    'cleaners' => ['Cleaner value is not valid']
                ]
            ]);
        }

        $regionValidation = $this->regionsValidation($request->get('regions'));
        if (!$regionValidation) {
            return response()->json([
                'status' => 400,
                'errors' => [
                    'regions' => ['Region value is not valid']
                ]
            ]);
        }

        $cleanersArr = $cleanerValidation;
        $serviceId = $request->get('service_id');
        $startTime = $request->get('start_time');
        $duration = $request->get('duration');
        $regions = $request->get('regions');
        $city = $request->get('city');
        $state = $request->get('state');
        $price = $request->get('price');
        $cleaners = $request->get('cleaners');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $workingDay = $request->get('working_day');
        $workingDayArr = [];

        if ($workingDay = $request->get('working_day')) {
            $workingDayValidation = $this->workingDayValidation($workingDay);
            if (!$workingDayValidation) {
                return response()->json([
                    'status' => 400,
                    'errors' => [
                        'working_day' => [$workingDayValidation]
                    ]
                ]);
            }
            $workingDayArr = $workingDayValidation;
        }

        try {
            if (!$vendorService = VendorService::find($id)) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Service not found'
                ]);
            }

            $vendor = $vendorService->company->vendor;
            if (!$vendor) {
                return response()->json([
                    'status' => 500,
                    'message' => 'Vendor is missing from company'
                ]);
            }

            $count = User::whereIn('guid', $cleanersArr)->cleaner()->cleanerOf($vendor->id)->count();
            if ($count !== count($cleanersArr)) {
                return response()->json([
                    'status' => 400,
                    'errors' => [
                        'cleaners' => ['Cleaners do not belong to this vendor']
                    ]
                ]);
            }

            // check if cleaner is ady been tied to a session between start data and end date and specific time
            if ($this->cleanerIsInAnotherSession(
                $cleanersArr,
                $startDate,
                $endDate,
                $startTime,
                $duration,
                $workingDayArr ?? [],
                $vendorService->id
            )) {
                return response()->json([
                    'status' => 400,
                    'errors' => [
                        'cleaners' => ['Cleaners is already selected for another session']
                    ]
                ]);
            }

            $vendorService->service_id = $serviceId;
            $vendorService->start_time = $startTime;
            $vendorService->duration = $duration;
            $vendorService->regions = $regions;
            $vendorService->city = $city;
            $vendorService->state = $state;
            $vendorService->price = $price;
            $vendorService->cleaners = $cleaners;
            $vendorService->start_date = $startDate;
            $vendorService->end_date = $endDate;
            $vendorService->working_day = $workingDay;

            if (!$vendorService->save()) {
                return response()->json([
                    'status' => 500,
                    'message' => 'database_error'
                ]);
            }
        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'database_error',
                'errors' => $e,
                'sql' => $e->getSql(),
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Service is updated',
            'service' => $vendorService,
        ]);
    }

    private function jsonValidation($json)
    {
        $arr = json_decode($json);
        if ($arr === null && json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }
        if (count($arr) === 0) {
            return false;
        }

        return $arr;
    }

    private function serviceIdValidation($json)
    {
        $serviceArr = $this->jsonValidation($json);
        if (!$serviceArr) {
            return false;
        }
        // TODO check service ids in DB
        return $serviceArr;
    }

    private function regionsValidation($regionJson)
    {
        $regionArr = $this->jsonValidation($regionJson);
        if (!$regionArr) {
            return false;
        }
        if (count($regionArr) > 5) {
            return false;
        }

        return $regionArr;
    }

    private function workingDayValidation($workingDayJson)
    {
        $workingDayArr = $this->jsonValidation($workingDayJson);
        if (!$workingDayArr) {
            return false;
        }
        foreach($workingDayArr as $day) {
            if ($day < 1 || $day > 7) {
                return false;
                break;
            }
        }

        return $workingDayArr;
    }

    private function cleanerIsInAnotherSession(array $cleanerArr, $startDate, $endDate, $startTime, $duration, array $workingDayArr, $vendorServiceId = null)
    {
        $vendorServicesQuery = VendorService::where(function($query) use($startDate, $endDate) {
            if ($startDate) {
                $query->where('end_date', '>=', $startDate);
            }
            if ($endDate) {
                $query->orWhere('start_date', '<=', $endDate);
            }
            $query->orWhereNull('start_date')->orWhereNull('end_date');
        });

        if ($vendorServiceId) {
            $vendorServicesQuery->where('id', '!=', $vendorServiceId);
        }

        $startTime = Carbon::createFromFormat('H:i', $startTime);
        $endTime = $startTime->copy()->addHours(intval($duration));
        $bufferedStartTime = $startTime->subHours(config('grabmaid.session.buffer.hours', 2))->format('H:i');
        $bufferedEndTime = $endTime->addHours(config('grabmaid.session.buffer.hours', 2))->format('H:i');
        $vendorServicesQuery->where(function($query) use($bufferedStartTime, $bufferedEndTime) {
            $query->whereRaw('time(`end_time`) > time(?)', [$bufferedStartTime])
            ->whereRaw('time(`start_time`) < time(?)',  [$bufferedEndTime]);
        });

        if (count($workingDayArr) > 0) {
            $vendorServicesQuery->where(function($query) use($workingDayArr) {
                $query->whereJsonContains('working_day', $workingDayArr[0]);
                foreach($workingDayArr as $index => $workingDay) {
                    if ($index === 0) {
                        continue;
                    }
                    $query->orWhereJsonContains('working_day', $workingDay);
                }
            });
        }

        if (count($cleanerArr) > 0) {
            $vendorServicesQuery->where(function($query) use($cleanerArr) {
                $query->whereJsonContains('cleaners', $cleanerArr[0]);
                foreach($cleanerArr as $index => $cleaner) {
                    if ($index === 0) {
                        continue;
                    }
                    $query->orWhereJsonContains('cleaners', $cleaner);
                }
            });
        }

        $count = $vendorServicesQuery->count();

        return $count >= 1;
    }

    private function generateEndTime($startTime, int $duration)
    {
        return Carbon::createFromFormat('H:i', $startTime)->addHours($duration)->format('H:i');
    }

}
