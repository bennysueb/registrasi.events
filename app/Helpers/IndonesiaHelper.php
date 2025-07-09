<?php

namespace App\Helpers;

class IndonesiaHelper
{
    public static function getProvinceNameById($id)
    {
        $path = storage_path('app/provinces.json');
        if (!file_exists($path)) {
            return 'Data provinsi tidak tersedia';
        }

        $provinces = json_decode(file_get_contents($path), true);
        foreach ($provinces as $prov) {
            if ($prov['id'] == $id) {
                return $prov['name'];
            }
        }

        return 'Provinsi tidak ditemukan';
    }

    public static function getCityNameByName($provinceId, $cityName)
    {
        $path = storage_path("app/regencies/{$provinceId}.json");

        if (!file_exists($path)) {
            return 'Data kota tidak tersedia';
        }

        $cities = json_decode(file_get_contents($path), true);

        foreach ($cities as $city) {
            if (strtolower($city['name']) == strtolower($cityName)) {
                return $city['name'];
            }
        }

        return 'Kota tidak ditemukan';
    }
}
