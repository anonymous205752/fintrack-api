<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'amount',
        'date',
        'category',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($expense) {
            $baseSlug = Str::slug($expense->title);
            $slug = $baseSlug;
            $count = 1;

            // Keep checking until we find a unique slug
            while (self::where('slug', $slug)->exists()) {
                $slug = "{$baseSlug}-{$count}";
                $count++;
            }

            $expense->slug = $slug;
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
