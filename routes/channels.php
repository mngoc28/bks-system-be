<?php

declare(strict_types=1);

use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Chỗ định nghĩa channel authorization callback. FE Partner Portal 360
| subscribe các channel sau (Pusher protocol qua Soketi/Pusher Cloud):
|   - private-partner.{partnerId}: chỉ chính partner đó được subscribe.
|   - private-property.{propertyId}: chỉ user sở hữu cơ sở (property) đó được subscribe.
|
*/

Broadcast::channel('App.Models.User.{id}', function (User $user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('conversation.{id}', function (User $user, $id) {
    $conversation = \App\Models\Conversation::find($id);
    if (!$conversation) {
        return false;
    }

    return (int) $user->id === (int) $conversation->user_id ||
           (int) $user->id === (int) $conversation->partner_id;
});

/**
 * Partner-scoped channel: chỉ chính partner subscribe được.
 * Phục vụ broadcast booking events (BookingCreated/Confirmed/Cancelled).
 */
Broadcast::channel('partner.{partnerId}', function (User $user, $partnerId) {
    return (int) $user->id === (int) $partnerId;
});

/**
 * Property-scoped channel: chỉ owner của property được subscribe.
 * Cho phép realtime cập nhật theo property (Phase 3 sẽ dùng cho calendar).
 */
Broadcast::channel('property.{propertyId}', function (User $user, $propertyId) {
    $property = Property::find($propertyId);
    if (! $property) {
        return false;
    }

    return (int) $user->id === (int) $property->user_id;
});
