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

}
