<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Collection;

/**
 * @property int id
 * @property string name
 * @property string email
 * @property string password
 * @property string ws_token
 *
 * @property Collection chats
 * @property Collection messages
 * @property Collection contacts
 * @property Collection validatedContacts
 * @property Collection unvalidatedContacts
 *
 * @method static User|Builder getUserByEmail(string $email)
 */
class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'ws_token',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = ['name_initial'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function chats()
    {
        return $this->belongsToMany(Chat::class)->orderByDesc('created_at');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function contacts()
    {
        return $this->belongsToMany(self::class, 'contact_user', 'user_id', 'contact_id')->withPivot('is_validated');
    }

    /**
     * @param Builder $query
     * @param $contactEmail
     * @return Builder
     */
    public function scopeGetUserByEmail(Builder $query, $contactEmail)
    {
        return $query->where('email', '=', $contactEmail);
    }

    /**
     * @return string
     */
    public function getNameInitialAttribute()
    {
        $words = explode(' ', $this->name);

        $words = array_map(function ($word) {
            return strtoupper(substr($word, 0, 1));
        }, $words);

        return implode('', $words);
    }
}
