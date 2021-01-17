<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Models\LockHistory
 *
 * @property int $id
 * @property int $user_id
 * @property int $lock_id
 * @property string $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read User $user
 * @property-read Lock $lock
 * @method static Builder|LockHistory newModelQuery()
 * @method static Builder|LockHistory newQuery()
 * @method static Builder|LockHistory query()
 * @mixin Eloquent
 */
class LockHistory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'lock_id',
        'description',
    ];

    public function scopeWhereTerm(Builder $query, string $term)
    {
        return $query->where(function (Builder $query) use ($term) {
            $query->where('description', 'like', "%$term%")
                ->orWhereHas('user', function (Builder $query) use ($term) {
                    $query->where(function (Builder $query) use ($term) {
                        $query->where('name', 'like', "%$term%")
                            ->orWhere('email', 'like', "%$term%")
                            ->orWhere('login', 'like', "%$term%");
                    });
                })
                ->orWhereHas('lock', function (Builder $query) use ($term) {
                    $query->where('name', 'like', "%$term%");
                });
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withDefault();
    }

    public function lock()
    {
        return $this->belongsTo(Lock::class)->withDefault();
    }
}
