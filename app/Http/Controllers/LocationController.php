<?php

namespace App\Http\Controllers;

use App\Models\Province;
use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LocationController extends Controller
{
    /**
     * Get all provinces with optional search
     */
    public function getProvinces(Request $request)
    {
        $search = $request->input('search', '');

        // Cache key based on search
        $cacheKey = "provinces:{$search}";

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($search) {
            $query = Province::query();

            if ($search) {
                $query->where('province_name', 'like', "%{$search}%");
            }

            return $query->select('province_id as id', 'province_name as name')
                        ->orderBy('province_id')
                        ->get();
        });
    }

    /**
     * Get districts by province with optional search
     */
    public function getDistricts(Request $request)
    {
        $provinceId = $request->input('province_id');
        $search = $request->input('search', '');

        // Cache key based on province and search
        $cacheKey = "districts:{$provinceId}:{$search}";

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($provinceId, $search) {
            $query = District::join('provinces', 'districts.province_id', '=', 'provinces.province_id');

            if ($provinceId) {
                $query->where('districts.province_id', $provinceId);
            }

            if ($search) {
                $query->where('districts.district_name', 'like', "%{$search}%");
            }

            return $query->select(
                'districts.district_id as id',
                'districts.district_name as name',
                'provinces.province_name as province'
            )
            ->orderBy('districts.district_id')
            ->get();
        });
    }

    /**
     * Get a specific province with its districts
     */
    public function getProvinceWithDistricts($id)
    {
        $cacheKey = "province_with_districts:{$id}";

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($id) {
            $province = Province::select('province_id as id', 'province_name as name')
                ->findOrFail($id);

            $districts = District::select('district_id as id', 'district_name as name')
                ->where('province_id', $id)
                ->orderBy('district_id')
                ->get();

            return [
                'id' => $province->id,
                'name' => $province->name,
                'districts' => $districts
            ];
        });
    }

    /**
     * Get a specific district with its province
     */
    public function getDistrictWithProvince($id)
    {
        $cacheKey = "district_with_province:{$id}";

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($id) {
            return District::join('provinces', 'districts.province_id', '=', 'provinces.province_id')
                ->select(
                    'districts.district_id as id',
                    'districts.district_name as name',
                    'provinces.province_name as province'
                )
                ->where('districts.district_id', $id)
                ->firstOrFail();
        });
    }
} 