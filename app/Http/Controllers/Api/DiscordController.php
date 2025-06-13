<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Geofence;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DiscordController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'discord_id' => 'required|string|unique:employees,discord_id',
            'name' => 'required|string|max:255',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:employees,email',
            'position' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        Log::info("Registering user", ['discord_id' => $request->discord_id]);

        $user = Employee::create([
            'discord_id' => $request->discord_id,
            'name' => $request->name,
            'full_name' => $request->full_name,
            'email' => $request->email,
            'position' => $request->position,
        ]);

        return response()->json(['message' => 'User registered', 'user' => $user]);
    }

    public function timeLog(Request $request)
    {
        $user = Employee::where('discord_id', $request->discord_id)->first();
        if (!$user) return response()->json(['message' => 'User not found'], 404);

        $lat = $request->latitude;
        $lng = $request->longitude;

        $inRange = false;

        foreach (Geofence::all() as $fence) {
            $distance = $this->getDistance($lat, $lng, $fence->latitude, $fence->longitude);
            if ($distance <= $fence->radius) {
                $inRange = true;
                break;
            }
        }

        $loggedAt = $request->has('logged_at') ? Carbon::parse($request->logged_at) : now();

        $attendance = Attendance::create([
            'employee_id' => $user->id,
            'type' => $request->type,
            'latitude' => $lat,
            'longitude' => $lng,
            'in_range' => $inRange,
            'logged_at' => $loggedAt
        ]);

        return response()->json([
            'message' => $inRange ? 'Time logged successfully' : 'Outside geofence area',
            'in_range' => $inRange
        ]);
    }

    public function getGeofences()
    {
        $geofences = Geofence::all();
        return response()->json($geofences);
    }
    
    private function getDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}
