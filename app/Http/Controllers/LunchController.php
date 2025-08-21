<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LunchSettingsRequest;
use App\Http\Requests\LunchRequestRequest;
use App\Services\LunchService;
use GrahamCampbell\ResultType\Success;

class LunchController extends Controller
{
    protected $lunchService;

    public function __construct(LunchService $lunchService)
    {
        $this->lunchService = $lunchService;
        $this->middleware('auth:sanctum');
    }

    public function getSettings()
    {
        try {
            $settings = $this->lunchService->getCurrentSettings();
            return response()->json(['success' => true, 'date' => $settings]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch settings'
            ], 500);
        }
    }

    public function updateSettings(LunchSettingsRequest $request)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        try {
            $settings = $this->lunchService->updateSettings($request->validated());
            return response()->json([
                'success' => true,
                'data' => $settings,
                'message' => 'Settings updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings'
            ], 500);
        }
    }

    public function submitLunchRequest(LunchRequestRequest $request)
    {
        try {
            $lunchRequest = $this->lunchService->submitLunchRequest(
                $request->has_lunch,
                $request->date
            );

            return response()->json(['success'=>true,'data'=>$lunchRequest]);
        } catch (\Exception $e) { return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

   public function getUserLunchRequest(Request $request)
    {
        try {
            $date = $request->get('date', now()->toDateString());
            $lunchRequest = $this->lunchService->getUserLunchRequest($date);
            
            return response()->json([
                'success' => true,
                'data' => $lunchRequest
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch lunch request'
            ], 500);
        }
    }

    public function getUserLunchHistory(Request $request)
    {
        try {
            $history = $this->lunchService->getUserLunchHistory(
                $request->get('start_date'),
                $request->get('end_date')
            );
            
            return response()->json([
                'success' => true,
                'data' => $history
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch lunch history'
            ], 500);
        }
    }


    public function getRequests(Request $request)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        try {
            $requests = $this->lunchService->getLunchRequests(
                $request->status,
                $request->date,
                $request->user_id
            );
            
            return response()->json([
                'success' => true,
                'data' => $requests
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch lunch requests'
            ], 500);
        }
    }
     public function updateRequestStatus(Request $request, $id)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        $request->validate([
            'status' => 'required|in:approved,rejected,pending'
        ]);
        
        try {
            $updated = $this->lunchService->updateRequestStatus($id, $request->status);
            
            return response()->json([
                'success' => true,
                'message' => 'Request status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update request status'
            ], 500);
        }
    }
     public function deleteLunchRequest($id)
    {
        try {
            $deleted = $this->lunchService->deleteLunchRequest($id);
            
            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lunch request not found or unauthorized'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Lunch request deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete lunch request'
            ], 500);
        }
    }
}
