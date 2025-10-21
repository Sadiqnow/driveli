<?php

namespace App\Events;

use App\Models\AdminUser;
use App\Models\Role;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserRoleModified implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public AdminUser $targetUser;
    public Role $role;
    public AdminUser $actor;
    public string $action;

    /**
     * Create a new event instance.
     */
    public function __construct(AdminUser $targetUser, Role $role, AdminUser $actor, string $action = 'assigned')
    {
        $this->targetUser = $targetUser;
        $this->role = $role;
        $this->actor = $actor;
        $this->action = $action;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.' . $this->actor->id),
            new PrivateChannel('user.' . $this->targetUser->id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'user.role.modified';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'target_user_id' => $this->targetUser->id,
            'target_user_name' => $this->targetUser->name,
            'role_id' => $this->role->id,
            'role_name' => $this->role->name,
            'action' => $this->action,
            'timestamp' => now()->toISOString(),
            'actor' => [
                'id' => $this->actor->id,
                'name' => $this->actor->name,
            ]
        ];
    }
}
