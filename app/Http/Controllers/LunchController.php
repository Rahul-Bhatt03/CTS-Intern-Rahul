<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LunchSettingsRequest;
use App\Http\Requests\LunchRequestRequest;
use App\Services\LunchService;

class LunchController extends Controller
{
     protected $lunchService;

    public function __construct(LunchService $lunchService)
    {
        $this->lunchService = $lunchService;
        $this->middleware('auth');
    }

    public function getSettings()
    {
        $settings = $this->lunchService->getCurrentSettings();
        return response()->json($settings);
    }

    public function updateSettings(LunchSettingsRequest $request)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $settings = $this->lunchService->updateSettings($request->validated());
        return response()->json($settings);
    }

    public function submitLunchRequest(LunchRequestRequest $request)
    {
        $lunchRequest = $this->lunchService->submitLunchRequest(
            $request->has_lunch,
            $request->date
        );
        
        return response()->json($lunchRequest);
    }

    public function getUserLunchRequest()
    {
        $lunchRequest = $this->lunchService->getUserTodayLunchRequest();
        return response()->json($lunchRequest);
    }

    public function getRequests(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $requests = $this->lunchService->getLunchRequests($request->status);
        return response()->json($requests);
    }

    public function updateRequestStatus(Request $request, $id)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate(['status' => 'required|in:approved,rejected']);
        
        $updated = $this->lunchService->updateRequestStatus($id, $request->status);
        return response()->json(['success' => $updated]);
    }
}


?>
