<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

/**
 * @property int id
 * @property int from_id
 * @property int to_id
 *
 * @method static Invitation|Builder getUserInvitations(User $user)
 * @method static Invitation|Builder getSpecificInvitation(User $user, User $contact)
 */
class Invitation extends Model
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'from_id',
        'to_id',
    ];

    /**
     * @param Builder $query
     * @param User $user
     * @return Builder
     */
    public function scopeGetUserInvitations(Builder $query, User $user)
    {
        return $query->where('to_id', '=', $user->id);
    }

    /**
     * @param Builder $query
     * @param User $user
     * @param User $contact
     * @return Builder
     */
    public function scopeGetSpecificInvitation(Builder $query, User $user, User $contact)
    {
        return $query->where('from_id', '=', $contact->id)->where('to_id', '=', $user->id);
    }
}
