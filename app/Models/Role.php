<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes;

    protected $table = 'roles';

    protected $fillable = [
        'name',
        'slug',
        'display_name',
        'description',
        'permissions_json',
        'is_active'
    ];

    protected $casts = [
        'permissions_json' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Relationship: A Role has many Users (Employees)
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }
}
