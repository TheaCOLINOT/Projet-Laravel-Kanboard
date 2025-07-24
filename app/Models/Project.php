<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Project extends Model
{
    use HasFactory, HasSlug;

    protected $table = 'projects';

    protected $fillable = [
        'name',
        'description',
        'slug',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_project')
            ->withPivot('role');
    }


    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function columns()
    {
        return $this->hasMany(Column::class);
    }

    public function invitations()
    {
        return $this->hasMany(\App\Models\Invitation::class);
    }
}
