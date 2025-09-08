<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Notification",
 *     description="Operations related to notify operations"
 * )
 */
class NotificationController extends Controller
{   
    /**
     * @OA\Get(
     *     path="/api/notifications",
     *     tags={"Notifications"},
     *     summary="List all notifications for the authenticated user",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Notifications list returned"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $notifications = $user->notifications()->latest()->get();

        return response()->json([
            'message' => 'User notifications',
            'data' => $notifications
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/notifications/unread",
     *     tags={"Notifications"},
     *     summary="List only unread notifications",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Unread notifications list returned"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function unread()
    {   
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $notifications = $user->unreadNotifications()->latest()->get();

        return response()->json([
            'message' => 'Unread notifications',
            'data' => $notifications
        ]);
    }


    /**
     * @OA\Patch(
     *     path="/api/notifications/{id}/read",
     *     tags={"Notifications"},
     *     summary="Mark a notification as read",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Notification marked as read"),
     *     @OA\Response(response=404, description="Notification not found"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function markAsRead($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read',
            'data' => $notification
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/notifications/unread/count",
     *     tags={"Notifications"},
     *     summary="Get the number of unread notifications",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Unread notifications count returned"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function unreadCount()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $count = $user->unreadNotifications()->count();

        return response()->json([
            'message' => 'Unread notifications count',
            'data' => $count
        ]);
    }
}
