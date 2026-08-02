<?php

namespace App\Support;

use App\Models\ChatConversation;
use App\Models\Customer;
use App\Models\User;

class ChatChannel
{
    public function join($user, string $conversation): bool
    {
        $conversationModel = ChatConversation::find($conversation);

        if (! $conversationModel) {
            return false;
        }

        if ($user instanceof Customer) {
            return (string) $user->id === (string) $conversationModel->customer_id;
        }

        if ($user instanceof User) {
            return $user->hasRole('Administrator')
                || (string) $conversationModel->store->user_id === (string) $user->id;
        }

        return false;
    }
}
