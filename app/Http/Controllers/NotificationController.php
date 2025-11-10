<?php
// app/Http/Controllers/NotificationController.php

namespace App\Http\Controllers;

use App\Models\SystemNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = auth()->user()->systemNotifications();

        if ($request->has('type')) {
            $query->byType($request->get('type'));
        }

        if ($request->has('unread_only') && $request->get('unread_only')) {
            $query->unread();
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead($id)
    {
        $notification = auth()->user()->systemNotifications()->findOrFail($id);
        $notification->markAsRead();

        return redirect()->back()
            ->with('success', 'Notification marked as read');
    }

    public function markAllAsRead()
    {
        auth()->user()->systemNotifications()->unread()->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'All notifications marked as read');
    }

    public function getUnreadCount()
    {
        return response()->json([
            'count' => auth()->user()->getUnreadNotificationsCount()
        ]);
    }

    public function destroy($id)
    {
        $notification = auth()->user()->systemNotifications()->findOrFail($id);
        $notification->delete();

        return redirect()->back()
            ->with('success', 'Notification deleted successfully');
    }
}