<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    public function courses() {
    return $this->hasMany(Course::class, 'teacher_id');
}

public function enrollments() {
    return $this->hasMany(Enrollment::class);
}

public function reviews() {
    return $this->hasMany(Review::class);
}

public function quizResults() {
    return $this->hasMany(QuizResult::class);
}

public function certificates() {
    return $this->hasMany(Certificate::class);
}

}
