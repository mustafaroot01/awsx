<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $settings = \App\Models\Setting::all()->pluck('value', 'key');
        return response()->json($settings);
    }

    public function update(Request $request): \Illuminate\Http\JsonResponse
    {
        // Handle file uploads if present
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $path = $file->store('branding', 'public');
            
            \App\Models\Setting::updateOrCreate(
                ['key' => 'app_logo_url'],
                ['value' => '/storage/' . $path]
            );
        }

        // Handle other settings
        foreach ($request->except('logo') as $key => $value) {
            \App\Models\Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return response()->json(['message' => 'Settings updated successfully']);
    }
}
