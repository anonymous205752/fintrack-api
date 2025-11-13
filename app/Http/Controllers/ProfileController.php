<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    // Get current user profile
    public function show()
    {
        $user = Auth::user();

        // Append full URL for profile image if available
        if ($user->profile_image) {
            $user->profile_image_url = asset('storage/' . $user->profile_image);
        }

        return response()->json($user);
    }

    // Update profile image
    public function update(Request $request)
    {
        $request->validate([
            'profile_image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = Auth::user();

        // Delete old image if exists
        if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
            Storage::disk('public')->delete($user->profile_image);
        }

        // Store new image
        $file = $request->file('profile_image');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('profiles', $filename, 'public');

        $user->profile_image = $path;
        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user,
            'profile_image_url' => asset('storage/' . $path),
        ]);
    }
}

