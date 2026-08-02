<?php

use App\Support\ChatChannel;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.{conversation}', ChatChannel::class);
