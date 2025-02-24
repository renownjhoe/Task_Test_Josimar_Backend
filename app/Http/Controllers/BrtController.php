<?php

namespace App\Http\Controllers;

use App\Models\Brt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Events\BrtCreated;
use App\Events\BrtUpdated;
use App\Events\BrtDeleted;
use Illuminate\Support\Facades\Auth;

class BrtController extends Controller
{
    // Retrieve all BRTs for the authenticated user
    public function index(Request $request)
    {
        $user = Auth::user();
        $brts = Brt::where('user_id', $user->id)->get();
        return response()->json($brts, 200);
    }

    // Create a new BRT
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reserved_amount' => 'required|numeric',
            'status' => 'required|in:active,expired',
        ]);

        if ($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $brt = Brt::create([
            'user_id' => Auth::id(),
            'brt_code' => strtoupper(Str::random(10)), // generate a unique code
            'reserved_amount' => $request->reserved_amount,
            'status' => $request->status,
        ]);

        // Dispatch a real-time notification event
        event(new BrtCreated($brt));

        return response()->json($brt, 201);
    }

    // Retrieve a specific BRT by ID
    public function show($id)
    {
        $brt = Brt::where('id', $id)->where('user_id', Auth::id())->first();
        if (!$brt) {
            return response()->json(['message' => 'BRT not found'], 404);
        }
        return response()->json($brt, 200);
    }

    // Update a specific BRT by ID
    public function update(Request $request, $id)
    {
        $brt = Brt::where('id', $id)->where('user_id', Auth::id())->first();
        if (!$brt) {
            return response()->json(['message' => 'BRT not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'reserved_amount' => 'sometimes|required|numeric',
            'status' => 'sometimes|required|in:active,expired',
        ]);

        if ($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $brt->update($request->only('reserved_amount', 'status'));

        // Dispatch a real-time notification event
        event(new BrtUpdated($brt));

        return response()->json($brt, 200);
    }

    // Delete a specific BRT by ID
    public function destroy($id)
    {
        $brt = Brt::where('id', $id)->where('user_id', Auth::id())->first();
        if (!$brt) {
            return response()->json(['success' => false, 'message' => 'BRT not found'], 404);
        }
        
        $brt->delete();

        // Dispatch a real-time notification event
        event(new BrtDeleted($brt));

        return response()->json(['success' => true, 'message' => 'BRT deleted'], 200);
    }
}
