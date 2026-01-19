<?php

use App\Helpers\AppHelper;
use App\Helpers\DateConverter;
use Illuminate\Support\Facades\Auth;

if (!function_exists('getAuthUserCode')) {
    function getAuthUserCode()
    {
        $user = Auth::user();
        return $user?->id;
    }
}

if (!function_exists('removeSpecialChars')) {
    function removeSpecialChars($string): string
    {
        $string = preg_replace('/[^A-Za-z0-9\-]/', ' ', $string);
        return ucfirst($string);
    }
}

if (!function_exists('convertTimeFormat')) {
    function convertTimeFormat($time): string
    {
        return date("H:i", strtotime($time));
    }
}

if (!function_exists('removeHtmlTags')) {
    function removeHtmlTags($value): string
    {
        return strip_tags($value);
    }
}

if (!function_exists('convertDateTimeFormat')) {
    function convertDateTimeFormat($dateTime): string
    {
        if (AppHelper::check24HoursTimeAppSetting()) {
            if (AppHelper::ifDateInBsEnabled()) {
                $date = AppHelper::getDayMonthYearFromDate($dateTime);
                $dateInBs = (new DateConverter())->engToNep($date['year'], $date['month'], $date['day']);
                $time = date('H:i', strtotime($dateTime));
                return $dateInBs['date'] . ' ' . $dateInBs['nmonth'] .' '.$dateInBs['year']. ' ' . $time;
            }
            return date('M d Y H:i ', strtotime($dateTime));
        } else {
            if (AppHelper::ifDateInBsEnabled()) {
                $date = AppHelper::getDayMonthYearFromDate($dateTime);
                $dateInBs = (new DateConverter())->engToNep($date['year'], $date['month'], $date['day']);
                $time = date('h:i A', strtotime($dateTime));
                return $dateInBs['date'] . ' ' . $dateInBs['nmonth'] . ' '.$dateInBs['year']. ' ' . $time;
            }
            return date('M d Y h:i A', strtotime($dateTime));
        }
    }
}

if (!function_exists('removeSpecialChar')) {
    function removeSpecialChar($str): string
    {
        $res = str_replace('_', ' ', $str);
        return ucfirst($res);
    }
}

if (!function_exists('getRecordPerPage')) {
    function getRecordPerPage()
    {
        $key = 'records_per_page';
        $limit = \App\Models\GeneralSetting::query()->where('key',$key)->first();
        return $limit->value ?? 15;
    }
}








