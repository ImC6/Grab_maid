<?php

namespace App\Http\Controllers\Admin;

use App\User;
use App\Models\Company;
use App\Models\Bank;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    public function getCompaniesByVendorId(Request $request, $vendorGuid)
    {
        try {
            $vendor = User::where('guid', $vendorGuid)->vendor()->with('companies.bank')->first();

            if (!$vendor) {
                return response()->json([
                    'status' => 404,
                    'message' => 'vendor_not_found'
                ]);
            }

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'database_error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'vendor' => $vendor,
            'companies' => $vendor->companies
        ]);
    }

    public function createCompanyByVendorId(Request $request, $vendorGuid)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'desc' => 'string|max:150',
            'address_line' => 'required|string|max:100',
            'postcode' => 'required|digits:5',
            'region' => 'required|string|max:50',
            'state' => 'required|string|max:50',
            'city' => 'string|max:50',
            'company_logo' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ]);
        }

        if ($request->hasFile('company_logo') && $request->file('company_logo')->isValid()) {
            $companyLogo = $request->file('company_logo');
            $logoPath = $companyLogo->store('public');
        }

        try {
            $vendor = User::where('guid', $vendorGuid)->vendor()->first();

            if (!$vendor) {
                return response()->json([
                    'status' => 404,
                    'message' => 'vendor_not_found'
                ]);
            }

            $company = new Company([
                'guid' => guidv4(),
                'name' => $request->get('name'),
                'desc' => $request->get('desc'),
                'address_line' => $request->get('address_line'),
                'postcode' => $request->get('postcode'),
                'region' => $request->get('region'),
                'state' => $request->get('state'),
                'city' => $request->get('city'),
                'company_logo' => $logoPath
            ]);

            $vendor->companies()->save($company);

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'database_error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Company is created',
            'companies' => $vendor->companies
        ]);
    }

    public function updateCompanyById(Request $request, $companyId)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'desc' => 'string|max:100',
            'address_line' => 'string|max:100',
            'postcode' => 'digits:5',
            'region' => 'string|max:50',
            'city' => 'string|max:50',
            'state' => 'string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ]);
        }

        if (!$company = Company::find($companyId)) {
            return response()->json([
                'status' => 404,
                'message' => 'company_not_found'
            ]);
        }

        $updateArr = [];

        if ($name = $request->get('name')) {
            $updateArr['name'] = $name;
        }

        if ($desc = $request->get('desc')) {
            $updateArr['desc'] = $desc;
        }

        if ($adddressLine = $request->get('address_line')) {
            $updateArr['address_line'] = $adddressLine;
        }

        if ($postcode = $request->get('postcode')) {
            $updateArr['postcode'] = $postcode;
        }

        if ($city = $request->get('city')) {
            $updateArr['city'] = $city;
        }

        if ($region = $request->get('region')) {
            $updateArr['region'] = $region;
        }

        if ($state = $request->get('state')) {
            $updateArr['state'] = $state;
        }

        if ($request->hasFile('company_logo') && $request->file('company_logo')->isValid()) {
            $companyLogo = $request->file('company_logo');
            $updateArr['company_logo'] = $companyLogo->store('public');
        }

        if (count($updateArr) === 0) {
            return response()->json([
                'status' => 422,
                'message' => 'missing_parameter'
            ]);
        }

        try {
            $update = $company->update($updateArr);

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

        return response()->json([
            'status' => 200,
            'message' => 'update_success',
            'updated_data' => $company,
        ]);
    }

    public function createBankByCompanyId(Request $request, $companyId)
    {
        $validator = Validator::make($request->all(), [
            'bank_name' => 'required|string|max:100',
            'bank_account' => 'required|digits_between:5,30',
            'bank_account_name' => 'required|string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ]);
        }

        // TODO use guid
        if (!$company = Company::find($companyId)) {
            return response()->json([
                'status' => 404,
                'message' => 'Company not found'
            ]);
        }

        try {
            $bank = new Bank;
            $bank->bank_name = $request->get('bank_name');
            $bank->bank_account = $request->get('bank_account');
            $bank->bank_account_name = $request->get('bank_account_name');

            if (!$company->bank()->save($bank)) {
                return response()->json([
                    'status' => 500,
                    'message' => 'database_error'
                ]);
            }

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'database_error',
                'e' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Bank is added'
        ]);
    }

    public function updateBankById(Request $request, $bankId)
    {
        $validator = Validator::make($request->all(), [
            'bank_name' => 'string|max:100',
            'bank_account' => 'digits_between:5,30',
            'bank_account_name' => 'string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ]);
        }

        try {
            if (!$bank = Bank::find($bankId)) {
                return response()->json([
                    'status' => 404,
                    'message' => 'bank_not_found'
                ]);
            }

            $updateArr = [];

            if ($bankName = $request->get('bank_name')) {
                $updateArr['bank_name'] = $bankName;
            }

            if ($bankAccount = $request->get('bank_account')) {
                $updateArr['bank_account'] = $bankAccount;
            }

            if ($bankAccountName = $request->get('bank_account_name')) {
                $updateArr['bank_account_name'] = $bankAccountName;
            }

            if (count($updateArr) === 0) {
                return response()->json([
                    'status' => 422,
                    'message' => 'missing_parameter'
                ]);
            }

            if (!$bank->update($updateArr)) {
                return response()->json([
                    'status' => 500,
                    'message' => 'database_error'
                ]);
            }

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'database_error',
                'e' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Bank is updated',
            'bank' => $bank
        ]);
    }
}
