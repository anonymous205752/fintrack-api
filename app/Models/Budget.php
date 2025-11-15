<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category',
        'limit',
        'month',
        'slug',
    ];

    // Convert month to Carbon instance automatically
    protected $casts = [
        'month' => 'date',
    ];

    /**
     * Auto-generate slug if not provided
     */
    protected static function boot()
{
    parent::boot();

    static::creating(function ($budget) {
        $slug = Str::slug($budget->category);
        $originalSlug = $slug;
        $counter = 2;

        while (Budget::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $budget->slug = $slug;
    });

    static::updating(function ($budget) {
        if ($budget->isDirty('category')) {
            $slug = Str::slug($budget->category);
            $originalSlug = $slug;
            $counter = 2;

            while (Budget::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $budget->slug = $slug;
        }
    });
}

    /**
     * Budget belongs to a User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function category()
{
    return $this->belongsTo(Category::class);
}
}
