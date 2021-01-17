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
 * @property string $name
 * @property string $mac_address
 * @property string $state
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read Collection|User[] $users
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User query()
 * @mixin Eloquent
 */
class Lock extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'mac_address',
        'name',
        'state',
    ];

    public function scopeWhereTerm(Builder $query, string $term)
    {
        return $query->where(function (Builder $query) use ($term) {
            $query->where('name', 'like', "%$term%")
                ->orWhereHas('users', function ($query) use ($term) {
                    $query->where(function (Builder $query) use ($term) {
                        $query->where('name', 'like', "%$term%")
                            ->orWhere('email', 'like', "%$term%")
                            ->orWhere('login', 'like', "%$term%");
                    });
                });
        });
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_has_locks');
    }
}
