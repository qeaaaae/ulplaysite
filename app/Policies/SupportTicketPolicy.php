<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SupportTicket;
use App\Models\User;

class SupportTicketPolicy
{
    public function view(User $user, SupportTicket $ticket): bool
    {
        if ($user->is_admin) {
            return true;
        }

        if ($ticket->user_id === null) {
            return false;
        }

        return (int) $ticket->user_id === (int) $user->getKey();
    }
}
