<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class lesson extends Model
{


    protected $fillable = [
        'title',
        'description',
        'course_id',
        'videoPath',
    ];
    public function course() {
    return $this->belongsTo(Course::class);
}
    protected static function booted()
    {
        static::deleting(function ($lesson) {
            if ($lesson->videoPath && Storage::disk('public')->exists($lesson->videoPath)) {
                Storage::disk('public')->delete($lesson->videoPath);
            }
        });
    }

}
