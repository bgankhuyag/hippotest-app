<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DummyQuestions extends Model
{
    use HasFactory;

    protected $table = 'dummy_questions';

    public function answers() {
      return $this->hasMany(DummyAnswers::class, 'questions_id');
    }
}
