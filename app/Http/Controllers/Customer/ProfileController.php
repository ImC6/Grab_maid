<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:100',
            'gender' => 'nullable|in:male,female',
            'mobile_no' => 'nullable|digits_between:9,15',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ]);
        }

        if (!$user = \Auth::user()) {
            return response()->json([
                'status' => 400,
                'errors' => 'User not found'
            ]);
        }

        $updateArr = [];

        if ($name = $request->get('name')) {
            $updateArr['name'] = $name;
        }

        if ($gender = $request->get('gender')) {
            $updateArr['gender'] = $gender;
        }

        if ($mobileNo = $request->get('mobile_no')) {
            $updateArr['mobile_no'] = $mobileNo;
            // TODO: trigger mobile no verification | PENDING
        }

        if (count($updateArr) === 0) {
            return response()->json([
                'status' => 422,
                'message' => 'Missing parameter',
            ]);
        }

        try {
            $update = $user->update($updateArr);

            if (!$update) {
                return response()->json([
                    'status' => 500,
                    'message' => 'Database error',
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
            'message' => 'Profile is updated',
            'user' => $user
        ]);
    }

    public function updateProfilePicture(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profile_pic' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ]);
        }

        if (!$user = \Auth::user()) {
            return response()->json([
                'status' => 400,
                'errors' => 'User not found'
            ]);
        }

        if ($request->hasFile('profile_pic') && $request->file('profile_pic')->isValid()) {
            $profilePic = $request->file('profile_pic');
            $profilePicPath = $profilePic->store('public');
        }

        try {
            if (!is_null($user->profile_pic)) {
                $profilePicToBeDeleted = $user->getOriginal('profile_pic');
                Storage::delete($profilePicToBeDeleted);
                // unlink(storage_path('app/' . $profilePicToBeDeleted));
            }

            $user->profile_pic = $profilePicPath;
            $user->save();

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Profile picture is updated',
            'user' => $user
        ]);
    }
}
