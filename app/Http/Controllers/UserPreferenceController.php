<?php

namespace App\Http\Controllers;

use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserPreferenceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $preferences = $user->preferences ?? new UserPreference();

        return response()->json([
            'preferences' => $preferences,
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $preferences = $user->preferences ?? new UserPreference();

        $preferences->fill($request->all());
        $user->preferences()->save($preferences);

        return response()->json([
            'message' => 'User preferences updated successfully.',
        ]);
    }
}