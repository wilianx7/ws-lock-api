<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * App\Models\Lock
 *
 * @property int $id
 * @property int $created_by_user_id
 * @property string $mac_address
 * @property string $state
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read Collection|User[] $users
 * @property-read User $createdByUser
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User query()
 * @method static Builder|User whereBelongsToUser()
 * @mixin Eloquent
 */
class Lock extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'created_by_user_id',
        'mac_address',
        'state',
    ];

    protected $appends = [
        'name'
    ];

    public function scopeWhereBelongsToUser(Builder $query): Builder
    {
        return $query->whereHas('users', function ($users) {
            $users->where('users.id', User::getAuthenticated()->id);
        });
    }

    public function scopeWhereTerm(Builder $query, string $term)
    {
        return $query->where(function (Builder $query) use ($term) {
            $query->whereHas('users', function ($query) use ($term) {
                $query->where(function (Builder $query) use ($term) {
                    $query->where('name', 'like', "%$term%")
                        ->orWhere('email', 'like', "%$term%")
                        ->orWhere('login', 'like', "%$term%");
                });
            });
        });
    }

    public function getNameAttribute(): ?string
    {
        return $this->users->first(fn($user) => $user->id == User::getAuthenticated()->id)->pivot->lock_name;
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_has_locks')->withPivot('lock_name');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by_user_id')->withDefault();
    }
}
