<?php

namespace App\Events;

use App\Models\Role;
use App\Models\AdminUser;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoleUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Role $role;
    public AdminUser $actor;
    public array $changes;
    public string $action;

    /**
     * Create a new event instance.
     */
    public function __construct(Role $role, AdminUser $actor, string $action = 'updated', array $changes = [])
    {
        $this->role = $role;
        $this->actor = $actor;
        $this->action = $action;
        $this->changes = $changes;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.' . $this->actor->id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'role.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'role_id' => $this->role->id,
            'role_name' => $this->role->name,
            'action' => $this->action,
            'changes' => $this->changes,
            'timestamp' => now()->toISOString(),
            'actor' => [
                'id' => $this->actor->id,
                'name' => $this->actor->name,
            ]
        ];
    }
}
