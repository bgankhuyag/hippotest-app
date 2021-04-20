<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Questions;
use App\Models\Answers;

class TestController extends Controller
{
    public function questions(Request $request) {
      // $questions = Questions::with('answers:id,questions_id,answer,correct')->inRandomOrder()->limit(2)->get(['id', 'question']);
      $questions = Questions::with('answers:id,questions_id,answer,correct')->orderBy(DB::raw('RAND()'))->take(2)->get(['id', 'question']);
      return response()->json($questions);
    }
}
