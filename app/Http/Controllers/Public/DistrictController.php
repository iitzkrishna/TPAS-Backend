<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\District;
use Illuminate\Http\Request;

class DistrictController extends Controller
{
    /**
     * Display a listing of districts with their associated province.
     */
    public function index()
    {
        $districts = District::with('province:province_id,province_name')->get(['district_id', 'district_name', 'province_id']);

        $data = $districts->map(function ($district) {
            return [
                'id' => $district->district_id,
                'district' => $district->district_name,
                'province' => $district->province ? $district->province->province_name : null
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }
} 