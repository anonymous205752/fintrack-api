<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Income extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'amount',
        'date',
        'source',
        'category',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($income) {
            $baseSlug = Str::slug($income->title);
            $slug = $baseSlug;
            $count = 1;

            // Check for existing slugs and make it unique
            while (self::where('slug', $slug)->exists()) {
                $slug = "{$baseSlug}-{$count}";
                $count++;
            }

            $income->slug = $slug;
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
