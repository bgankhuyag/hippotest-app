<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Questions;
use App\Models\Answers;
use App\Models\User;
use Validator;

class TestController extends Controller
{
  // get the questions in specified category
  public function questions(Request $request, $category) {
    // dd($category);
    $questions = Questions::where('category', $category)->with('answers:id,questions_id,answer,correct')->inRandomOrder()->limit(10)->get(['id', 'question']);
    // $questions = Questions::where('category', $category)->with('answers:questions_id,answer,correct')->orderBy(DB::raw('RAND()'))->take(10)->get(['id', 'question', 'category']);
    return response()->json($questions);
  }

  // return all distinct categories
  public function categories(Request $request) {
    $categories = Questions::select('category')->distinct()->get();
    return response()->json($categories);
  }

  // gets the points that the user has scored and add it to the total.
  public function submit(Request $request) {
    $validator = Validator::make($request->all(), [
      'points' => 'required|int|min: 0',
    ]);
    if($validator->fails()){
      return response()->json(['errors' => $validator->errors(), 'success' => false]);
    }
    $user = User::firstWhere('id', auth()->id());
    $user->increment('points', $request->points);
  }

  public function leaderboard(Request $request) {
    DB::statement(DB::raw('set @row:=0'));
    $users = User::orderBy('points', 'desc')->selectRaw('id, name, email, points, @row:=@row+1 as rank')->take(2)->get();
    // DB::statement(DB::raw('set @row:=0'));
    // $user_rank = User::orderBy('points', 'desc')->selectRaw('id, name, points, @row:=@row+1 as rank')->get();
    $json = json_encode($users);
    $users = json_decode($json);
    $position = array_search(auth()->id(), array_column($users, 'id'));
    if ($position == 0) {

    }
    return response()->json(['data' => array_slice($users, 0, 10), 'user' => array_slice($users, $position-1, $position+2)]);
  }

}
