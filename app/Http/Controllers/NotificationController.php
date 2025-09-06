<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
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
