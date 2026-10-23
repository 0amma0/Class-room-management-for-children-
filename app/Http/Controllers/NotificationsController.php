<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationsController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()->appNotifications()
            ->latest()
            ->paginate(20);
        $notificationGroups = $notifications->getCollection()->groupBy(function (AppNotification $notification): string {
            $date = $notification->created_at->timezone(config('app.timezone'));

            return match (true) {
                $date->isToday() => 'Hari ini',
                $date->isYesterday() => 'Kemarin',
                default => $date->translatedFormat('l, d F Y'),
            };
        });

        return view('notifications.index', compact('notifications', 'notificationGroups'));
    }

    public function open(Request $request, AppNotification $notification): RedirectResponse
    {
        $notification = $request->user()->appNotifications()->findOrFail($notification->id);
        if ($notification->read_at === null) {
            $notification->update(['read_at' => now()]);
        }

        if ($notification->teacher_reflection_id !== null) {
            return redirect()->route('reflections.show', $notification->teacher_reflection_id);
        }

        if ($notification->public_download_request_id !== null && $request->user()->isAdmin()) {
            return redirect()->to(route('admin.materials', [
                'request' => $notification->public_download_request_id,
            ]).'#request-'.$notification->public_download_request_id);
        }

        if ($notification->support_conversation_id !== null) {
            if ($request->user()->isAdmin()) {
                return redirect()->route('admin.support', [
                    'conversation_id' => $notification->support_conversation_id,
                ]);
            }

            if ($request->user()->isGuru()) {
                return redirect()->route('guru.support');
            }
        }

        return redirect()->route('notifications.index');
    }

    public function destroy(Request $request, AppNotification $notification): RedirectResponse
    {
        $request->user()->appNotifications()->findOrFail($notification->id)->delete();

        return redirect()->route('notifications.index')->with('success', 'Notifikasi berhasil dihapus.');
    }
}
