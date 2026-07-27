<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageRequest;
use App\Models\CareBooking;
use App\Models\Message;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        $contacts = $this->contactsQuery($user)->orderBy('name')->get(['id', 'name']);
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
                'unread_count' => (int) ($unreadCounts[$contact->id] ?? 0),
                'last_message' => $lastMessages->get($contact->id)?->only(['body', 'sender_id', 'created_at']),
            ])->values(),
        ]);
    }

    public function conversation(Request $request, User $user): JsonResponse
    {
        $authUser = $request->user();

        abort_unless($this->canMessage($authUser, $user), 403, 'You can only message people linked to one of your bookings.');

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
            'contact' => $user->only(['id', 'name']),
        ]);
    }

    public function store(StoreMessageRequest $request, User $user): JsonResponse
    {
        $authUser = $request->user();

        abort_unless($this->canMessage($authUser, $user), 403, 'You can only message people linked to one of your bookings.');

        $message = Message::create([
            'sender_id' => $authUser->id,
            'recipient_id' => $user->id,
            'body' => $request->validated('body'),
        ]);

        return response()->json(['data' => $message], 201);
    }

    private function contactsQuery(User $user): Builder
    {
        if ($user->hasRole('care_giver') || $user->careGiverProfile()->exists()) {
            return User::whereIn('id', CareBooking::where('care_giver_id', $user->id)->select('user_id'));
        }

        return User::whereIn('id', CareBooking::where('user_id', $user->id)->whereNotNull('care_giver_id')->select('care_giver_id'));
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

        return CareBooking::where(function (Builder $query) use ($authUser, $user) {
            $query->where('user_id', $authUser->id)->where('care_giver_id', $user->id);
        })
            ->orWhere(function (Builder $query) use ($authUser, $user) {
                $query->where('user_id', $user->id)->where('care_giver_id', $authUser->id);
            })
            ->exists();
    }
}
