<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class course extends Model
{
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

public function quizzes() {
    return $this->hasMany(Quiz::class);
}

public function stats() {
    return $this->hasOne(CourseStat::class);
}

}
