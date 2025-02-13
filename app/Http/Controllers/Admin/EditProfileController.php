<?php

// namespace App\Http\Controllers\Admin;

// use App\Http\Controllers\Controller;

// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;
// use App\Http\Models;
// class AdminController extends Controller
// {
//     public function updateProfile(Request $request)
//     {
//         $request->validate([
//             'first_name' => 'required|string|max:255',
//             'last_name' => 'required|string|max:255',
//         ]);

//         $admin = Auth::user();

//         if (!$admin) {
//             return response()->json([
//                 'error' => 'Unauthorized - Admin not authenticated'
//             ], 401);
//         }

//         $admin->updateProfileAdmin($request->only(['first_name', 'last_name']));

//         return response()->json([
//             'message' => 'Profile updated successfully',
//             'admin' => $admin
//         ]);
//     }
// }
