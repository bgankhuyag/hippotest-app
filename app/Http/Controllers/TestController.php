<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Questions;
use App\Models\Answers;

class TestController extends Controller
{
    public function questions() {
      $questions = Questions::with('answers')->get();
      return response()->json($question);
    }
}
