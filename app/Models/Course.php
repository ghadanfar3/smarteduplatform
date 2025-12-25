<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable=["title", "imgpath", "description"];
    public function teacher() {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function lessons() {
        return $this->hasMany(Lesson::class);
    }

    public function enrollments() {
        return $this->hasMany(Enrollment::class);
    }

    public function reviews() {
        return $this->hasMany(Review::class);
    }

    public function comments() {
        return $this->hasMany(Comment::class);
    }

    public function ratings() {
        return $this->hasMany(Rating::class);
    }

    public function quizzes() {
        return $this->hasMany(Quiz::class);
    }

    public function stats() {
        return $this->hasOne(CourseStat::class);
    }
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($course) {
            // حذف الدروس
            $course->lessons()->each(function ($lesson) {
                // حذف الفيديو
                if ($lesson->video_path) {
                    \Storage::disk('public')->delete($lesson->video_path);
                }
                $lesson->delete();
            });

            // حذف الاشتراكات
            $course->enrollments()->delete();

            // حذف الاختبار مع الأسئلة والخيارات
            if ($course->quiz) {
                foreach ($course->quiz->questions as $question) {
                    $question->options()->delete();
                }
                $course->quiz->questions()->delete();
                $course->quiz()->delete();
            }

            // حذف التعليقات أو التقييمات
            $course->reviews()->delete();
        });
    }



}
