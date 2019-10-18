<?php

namespace App\Http\Controllers\Admin;

use App\Models\Holiday;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class HolidayController extends Controller
{
    public function getAllHolidays(Request $request)
    {
        $year = $request->get('year') ?? date("Y");
        $state = $request->get('state') ?? 'WP Kuala Lumpur';

        try {
            $query = Holiday::query();

            if ($year) {
                $query->whereYear('holiday_date', $year);
            }

            if ($state) {
                $query->where('state', $state);
            }

            $holidays = $query->get();

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'database_error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'holidays' => $holidays
        ]);
    }

    public function createHolidays(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'state' => 'required|string|max:50',
            'holiday_date' => 'required|date',
            'holiday_desc' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ]);
        }

        $state = $request->get('state');
        $date = Carbon::parse($request->get('holiday_date'))->toDateString();
        $desc = $request->get('holiday_desc');

        try {
            $holiday = Holiday::firstOrCreate([
                'state' => $state,
                'holiday_date' => $date,
            ],[
                'holiday_desc' => $desc
            ]);

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'database_error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'create_success',
            'holiday' => $holiday
        ]);
    }

    public function deleteHoliday(Request $request, $id)
    {
        try {
            $delete = Holiday::destroy($id);

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
            'id' => $id
        ]);
    }
}
