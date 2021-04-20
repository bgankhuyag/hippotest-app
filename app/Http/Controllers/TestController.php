<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Questions;
use App\Models\Answers;

class TestController extends Controller
{
  // get the questions in specified category
  public function questions(Request $request, $category) {
    // dd($category);
    $questions = Questions::where('category', $category)->with('answers:id,questions_id,answer,correct')->inRandomOrder()->limit(10)->get(['id', 'question']);
    // $questions = Questions::where('category', $category)->with('answers:questions_id,answer,correct')->orderBy(DB::raw('RAND()'))->take(2)->get(['id', 'question', 'category']);
    return response()->json($questions);
  }

  // return all distinct categories
  public function categories(Request $request) {
    $categories = Questions::select('category')->distinct()->get();
    return response()->json($categories);
  }

  // gets the points that the user has scored and add it to the total.
  public function submit(Request $request) {
    dd(auth()->user());
  }


}
