<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public $timestamps = true;

    // User Roles
    const ROLE_OFFICER = 0;
    const ROLE_CHIEF = 1;
    const ROLE_ADMIN = 2;

    // Relationships
    public function assignedTask() {
        return $this->hasMany(AssignedTask::class, 'officer_id');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        // 'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // 'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function scopeSearch(Builder $query, Request $request)
    {
        // Account Type Filter
        if ($request->filled('account_type')) {
            $query->where('role', $request->account_type);
        };
        

        // Name Email Added At Search
        return $query->when($request->search, function ($query) use ($request) {
            $query->where(function ($query) use ($request) {
                $query
                    ->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%') 
                    ->orWhere('created_at', 'like', '%' . $request->search . '%'); 
            });
        });
    }
}
