<?php

namespace App\Events;

use App\Models\Permission;
use App\Models\AdminUser;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PermissionChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Permission $permission;
    public AdminUser $actor;
    public array $changes;
    public string $action;

    /**
     * Create a new event instance.
     */
    public function __construct(Permission $permission, AdminUser $actor, string $action = 'updated', array $changes = [])
    {
        $this->permission = $permission;
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
        return 'permission.changed';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'permission_id' => $this->permission->id,
            'permission_name' => $this->permission->name,
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
