<?php

namespace App\Models;

use DB;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $login
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read Collection|Lock[] $locks
 * @property-read DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @method static Builder|User query()
 * @method static Builder|User whereDoesntAuthenticatedUser()
 * @mixin Eloquent
 */
class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'login',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'has_lock_access'
    ];

    public static function getAuthenticated(): User
    {
        /** @var ?User $authenticatedUser */
        $authenticatedUser = auth('api')->user();

        return $authenticatedUser ?? new User();
    }

    public function scopeWhereTerm(Builder $query, string $term)
    {
        return $query->where(function (Builder $query) use ($term) {
            $query->where('name', 'like', "%$term%")
                ->orWhere('email', 'like', "%$term%")
                ->orWhere('login', 'like', "%$term%")
                ->orWhereHas('locks', function ($query) use ($term) {
                    $query->where('name', 'like', "%$term%");
                });
        });
    }

    public function scopeWhereDoesntAuthenticatedUser(Builder $query): Builder
    {
        return $query->where('id', '!=', User::getAuthenticated()->id);
    }

    public function locks()
    {
        return $this->belongsToMany(Lock::class, 'user_has_locks')->withPivot('lock_name');
    }

    public function getHasLockAccessAttribute(): bool
    {
        $lockId = request()->request->filter('has_access_to_lock_id');

        if ($lockId) {
            return DB::table('user_has_locks')
                ->where('user_id', $this->id)
                ->where('lock_id', $lockId)
                ->exists();
        }

        return false;
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
