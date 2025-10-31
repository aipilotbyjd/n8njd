<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * The organizations that belong to the user.
     */
    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'organization_user')
                    ->withPivot('role', 'permissions', 'joined_at', 'invited_at')
                    ->withTimestamps();
    }

    /**
     * The workflows created by the user.
     */
    public function workflows(): HasMany
    {
        return $this->hasMany(Workflow::class, 'created_by');
    }

    /**
     * The executions triggered by the user.
     */
    public function executions(): HasMany
    {
        return $this->hasMany(Execution::class, 'triggered_by');
    }

    /**
     * The credentials created by the user.
     */
    public function credentials(): HasMany
    {
        return $this->hasMany(Credential::class, 'created_by');
    }

    /**
     * The files uploaded by the user.
     */
    public function files(): HasMany
    {
        return $this->hasMany(FileUser::class);
    }

    /**
     * The notifications received by the user.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * The teams created by the user.
     */
    public function teams(): HasMany
    {
        return $this->hasMany(Team::class, 'created_by');
    }

    /**
     * The tags created by the user.
     */
    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class, 'created_by');
    }

    /**
     * The templates created by the user.
     */
    public function templates(): HasMany
    {
        return $this->hasMany(Template::class, 'created_by');
    }

    /**
     * The workflow versions created by the user.
     */
    public function workflowVersions(): HasMany
    {
        return $this->hasMany(WorkflowVersion::class, 'created_by');
    }

    /**
     * The variables created by the user.
     */
    public function variables(): HasMany
    {
        return $this->hasMany(Variable::class, 'created_by');
    }

    /**
     * The notification channels created by the user.
     */
    public function notificationChannels(): HasMany
    {
        return $this->hasMany(NotificationChannel::class);
    }

    /**
     * The audit logs created by the user.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }
}
