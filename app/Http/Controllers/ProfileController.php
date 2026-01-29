<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $role = $user->assignedRole;
        $authorizedLocations = $user->authorizedLocations();
        
        return view('profile.show', compact('user', 'role', 'authorizedLocations'));
    }
}
