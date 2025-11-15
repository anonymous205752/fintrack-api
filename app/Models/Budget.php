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

    protected $casts = [
        'month' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($budget) {
            $slug = Str::slug($budget->category);
            $originalSlug = $slug;
            $counter = 2;

            while (Budget::where('slug', $slug)->where('user_id', $budget->user_id)->exists()) {
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

                while (Budget::where('slug', $slug)
                    ->where('user_id', $budget->user_id)
                    ->where('id', '!=', $budget->id)
                    ->exists()
                ) {
                    $slug = $originalSlug . '-' . $counter;
                    $counter++;
                }

                $budget->slug = $slug;
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
