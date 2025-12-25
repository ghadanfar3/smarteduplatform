<?php

namespace App\Models;
use App\Models\Opition ;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'quiz_id',
        'text',
        ];
    public function quiz() {
    return $this->belongsTo(Quiz::class);
}
    public function options()
    {
        return $this->hasMany(Option::class);
    }

}
