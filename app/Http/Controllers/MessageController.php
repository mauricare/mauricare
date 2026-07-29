<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageRequest;
use App\Mail\AdminMessageReceived;
use App\Models\CareBooking;
use App\Models\Message;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MessageController extends Controller
{
    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'count' => Message::where('recipient_id', $request->user()->id)
                ->whereNull('read_at')
                ->count(),
        ]);
    }

    public function contacts(Request $request): JsonResponse
    {
        $user = $request->user();
        $contacts = $this->contactsQuery($user)->with(['media', 'roles'])->orderBy('name')->get(['id', 'name']);
        $contactIds = $contacts->pluck('id');

        $unreadCounts = Message::where('recipient_id', $user->id)
            ->whereIn('sender_id', $contactIds)
            ->whereNull('read_at')
            ->selectRaw('sender_id, count(*) as total')
            ->groupBy('sender_id')
            ->pluck('total', 'sender_id');

        $lastMessages = Message::where(function (Builder $query) use ($user, $contactIds) {
            $query->where('sender_id', $user->id)->whereIn('recipient_id', $contactIds);
        })
            ->orWhere(function (Builder $query) use ($user, $contactIds) {
                $query->where('recipient_id', $user->id)->whereIn('sender_id', $contactIds);
            })
            ->latest('id')
            ->get()
            ->groupBy(fn (Message $message) => $message->sender_id === $user->id ? $message->recipient_id : $message->sender_id)
            ->map(fn ($messages) => $messages->first());

        return response()->json([
            'data' => $contacts->map(fn (User $contact) => [
                'id' => $contact->id,
                'name' => $contact->name,
                'avatar_url' => $contact->avatar_url,
                'is_admin' => $contact->hasRole('admin'),
                'unread_count' => (int) ($unreadCounts[$contact->id] ?? 0),
                'last_message' => $lastMessages->get($contact->id)?->only(['id', 'body', 'sender_id', 'created_at', 'updated_at']),
            ])->values(),
        ]);
    }

    public function conversation(Request $request, User $user): JsonResponse
    {
        $authUser = $request->user();

        abort_unless($this->canMessage($authUser, $user), 403, 'You can only message linked care users or an administrator.');

        Message::where('sender_id', $user->id)
            ->where('recipient_id', $authUser->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = $this->conversationQuery($authUser, $user)
            ->latest('id')
            ->limit(100)
            ->get()
            ->reverse()
            ->values();

        return response()->json([
            'data' => $messages,
            'contact' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar_url' => $user->avatar_url,
            ],
        ]);
    }

    public function store(StoreMessageRequest $request, User $user): JsonResponse
    {
        $authUser = $request->user();

        abort_unless($this->canMessage($authUser, $user), 403, 'You can only message linked care users or an administrator.');

        $message = Message::create([
            'sender_id' => $authUser->id,
            'recipient_id' => $user->id,
            'body' => $request->validated('body'),
        ]);

        if ($user->hasRole('admin')) {
            Mail::to(config('mail.admin_message_notification_to'))
                ->send(new AdminMessageReceived($authUser, $message));
        }

        return response()->json(['data' => $message], 201);
    }

    public function update(StoreMessageRequest $request, Message $message): JsonResponse
    {
        abort_unless(
            $message->sender_id === $request->user()->id,
            403,
            'You can only edit messages you sent.',
        );

        $message->update([
            'body' => $request->validated('body'),
        ]);

        return response()->json(['data' => $message->fresh()]);
    }

    public function destroy(Request $request, Message $message): JsonResponse
    {
        abort_unless(
            $message->sender_id === $request->user()->id,
            403,
            'You can only delete messages you sent.',
        );

        $message->delete();

        return response()->json([], 204);
    }

    private function contactsQuery(User $user): Builder
    {
        if ($user->hasRole('admin')) {
            return User::whereHas('roles', fn (Builder $query) => $query
                ->whereIn('name', ['care_seeker', 'care_giver']));
        }

        if ($user->hasRole('care_giver') || $user->careGiverProfile()->exists()) {
            return User::whereIn('id', CareBooking::where('care_giver_id', $user->id)->select('user_id'))
                ->orWhereHas('roles', fn (Builder $query) => $query->where('name', 'admin'));
        }

        return User::whereIn(
            'id',
            CareBooking::where('user_id', $user->id)
                ->whereNotNull('care_giver_id')
                ->select('care_giver_id'),
        )->orWhereHas('roles', fn (Builder $query) => $query->where('name', 'admin'));
    }

    private function conversationQuery(User $authUser, User $user): Builder
    {
        return Message::where(function (Builder $query) use ($authUser, $user) {
            $query->where('sender_id', $authUser->id)->where('recipient_id', $user->id);
        })
            ->orWhere(function (Builder $query) use ($authUser, $user) {
                $query->where('sender_id', $user->id)->where('recipient_id', $authUser->id);
            });
    }

    private function canMessage(User $authUser, User $user): bool
    {
        if ($authUser->id === $user->id) {
            return false;
        }

        if ($authUser->hasRole('admin') || $user->hasRole('admin')) {
            $otherUser = $authUser->hasRole('admin') ? $user : $authUser;

            return $otherUser->hasAnyRole(['care_seeker', 'care_giver'])
                || $otherUser->careSeekerProfile()->exists()
                || $otherUser->careGiverProfile()->exists();
        }

        return CareBooking::where(function (Builder $query) use ($authUser, $user) {
            $query->where('user_id', $authUser->id)->where('care_giver_id', $user->id);
        })
            ->orWhere(function (Builder $query) use ($authUser, $user) {
                $query->where('user_id', $user->id)->where('care_giver_id', $authUser->id);
            })
            ->exists();
    }
}
