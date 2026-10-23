<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\SupportConversation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SupportConversationController extends Controller
{
    public function showPublic(string $token): View
    {
        $conversation = $this->publicConversation($token);
        $conversation->load('messages.senderUser:id,name');

        return view('support.public', compact('conversation', 'token'));
    }

    public function storePublic(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:100'],
            'body' => ['required', 'string', 'max:5000'],
        ]);
        $token = Str::random(64);
        $conversation = SupportConversation::query()->create([
            'source' => 'public',
            'guest_name' => $data['name'] ?? null,
            'guest_token_hash' => hash('sha256', $token),
        ]);

        $this->saveMessage($conversation, null, 'public', $data['body']);
        $this->notifyAdmins($conversation, $data['body']);

        return redirect()->route('support.public.show', $token)
            ->with('success', 'Pesan terkirim. Simpan tautan percakapan ini untuk melihat balasan.');
    }

    public function replyPublic(Request $request, string $token): RedirectResponse
    {
        $conversation = $this->publicConversation($token);
        $data = $request->validate(['body' => ['required', 'string', 'max:5000']]);

        $this->saveMessage($conversation, null, 'public', $data['body']);
        $this->notifyAdmins($conversation, $data['body']);

        return redirect()->route('support.public.show', $token)
            ->with('success', 'Balasan terkirim.');
    }

    public function adminIndex(Request $request): View
    {
        $conversations = SupportConversation::query()
            ->with('user:id,name')
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->paginate(20);
        $conversationId = $request->integer('conversation_id');
        $selectedConversation = $conversationId > 0
            ? SupportConversation::query()->findOrFail($conversationId)
            : $conversations->first();

        if ($selectedConversation !== null) {
            $selectedConversation->load('messages.senderUser:id,name');
            $request->user()->appNotifications()
                ->where('support_conversation_id', $selectedConversation->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        return view('support.admin', compact('conversations', 'selectedConversation'));
    }

    public function replyAdmin(Request $request, SupportConversation $conversation): RedirectResponse
    {
        $data = $request->validate(['body' => ['required', 'string', 'max:5000']]);
        $this->saveMessage($conversation, $request->user(), 'admin', $data['body']);

        if ($conversation->user_id !== null) {
            AppNotification::query()->create([
                'user_id' => $conversation->user_id,
                'type' => 'support',
                'support_conversation_id' => $conversation->id,
                'title' => 'Balasan Admin',
                'message' => Str::limit($data['body'], 180),
            ]);
        }

        return redirect()->route('admin.support', ['conversation_id' => $conversation->id])
            ->with('success', 'Balasan terkirim.');
    }

    public function destroyAdmin(SupportConversation $conversation): RedirectResponse
    {
        DB::transaction(function () use ($conversation): void {
            AppNotification::query()
                ->where('support_conversation_id', $conversation->id)
                ->delete();
            $conversation->delete();
        });

        return redirect()->route('admin.support')->with('success', 'Percakapan dan seluruh pesannya berhasil dihapus.');
    }

    public function guruIndex(Request $request): View
    {
        $conversation = SupportConversation::query()
            ->where('user_id', $request->user()->id)
            ->where('source', 'guru')
            ->latest('last_message_at')
            ->first();
        $conversation?->load('messages.senderUser:id,name');

        if ($conversation !== null) {
            $request->user()->appNotifications()
                ->where('support_conversation_id', $conversation->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        return view('support.guru', compact('conversation'));
    }

    public function storeGuru(Request $request): RedirectResponse
    {
        $data = $request->validate(['body' => ['required', 'string', 'max:5000']]);
        $conversation = SupportConversation::query()
            ->where('user_id', $request->user()->id)
            ->where('source', 'guru')
            ->where('status', 'open')
            ->latest('last_message_at')
            ->first();

        if ($conversation === null) {
            $conversation = SupportConversation::query()->create([
                'user_id' => $request->user()->id,
                'source' => 'guru',
            ]);
        }

        $this->saveMessage($conversation, $request->user(), 'guru', $data['body']);
        $this->notifyAdmins($conversation, $data['body']);

        return redirect()->route('guru.support')->with('success', 'Pesan terkirim ke Admin.');
    }

    public function replyGuru(Request $request, SupportConversation $conversation): RedirectResponse
    {
        abort_unless(
            $conversation->user_id === $request->user()->id && $conversation->source === 'guru',
            404,
        );
        $data = $request->validate(['body' => ['required', 'string', 'max:5000']]);

        $this->saveMessage($conversation, $request->user(), 'guru', $data['body']);
        $this->notifyAdmins($conversation, $data['body']);

        return redirect()->route('guru.support')->with('success', 'Pesan terkirim.');
    }

    public function destroyGuru(Request $request, SupportConversation $conversation): RedirectResponse
    {
        abort_unless(
            $conversation->user_id === $request->user()->id && $conversation->source === 'guru',
            404,
        );

        DB::transaction(function () use ($conversation): void {
            AppNotification::query()
                ->where('support_conversation_id', $conversation->id)
                ->delete();
            $conversation->delete();
        });

        return redirect()->route('guru.support')->with('success', 'Percakapan dan seluruh pesannya berhasil dihapus.');
    }

    private function publicConversation(string $token): SupportConversation
    {
        abort_unless(preg_match('/\A[A-Za-z0-9]{64}\z/', $token) === 1, 404);

        return SupportConversation::query()
            ->where('source', 'public')
            ->where('guest_token_hash', hash('sha256', $token))
            ->firstOrFail();
    }

    private function saveMessage(
        SupportConversation $conversation,
        ?User $sender,
        string $senderType,
        string $body,
    ): void {
        $conversation->messages()->create([
            'sender_user_id' => $sender?->id,
            'sender_type' => $senderType,
            'body' => $body,
        ]);
        $conversation->update([
            'last_message_at' => now(),
            'status' => 'open',
        ]);
    }

    private function notifyAdmins(SupportConversation $conversation, string $body): void
    {
        User::query()
            ->where('role', 'admin')
            ->where('status', 'active')
            ->each(function (User $admin) use ($conversation, $body): void {
                AppNotification::query()->create([
                    'user_id' => $admin->id,
                    'type' => 'support',
                    'support_conversation_id' => $conversation->id,
                    'title' => 'Pesan bantuan baru',
                    'message' => Str::limit($body, 180),
                ]);
            });
    }
}
