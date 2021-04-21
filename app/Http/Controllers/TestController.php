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
    $validator = Validator::make($request->all(), [
      'points' => 'required|int|min: 0',
    ]);
    if($validator->fails()){
      return response()->json(['errors' => $validator->errors(), 'success' => false]);
    }
    $user = User::firstWhere('id', auth()->id());
    // dd($user);
    $user->increment('points', $request->points);
  }

  public function leaderboard(Request $request) {
    // $users = User::orderBy('points', 'desc')->get(['name', 'email', 'points']);
    DB::statement(DB::raw('set @row:=0'));
    $users = User::orderBy('points', 'desc')->selectRaw('name, email, points, @row:=@row+1 as rank')->get();
    DB::statement(DB::raw('set @row:=0'));
    $user_rank = User::orderBy('points', 'desc')->selectRaw('id, name, points, @row:=@row+1 as rank')->get();
    // $position = $user_rank->search(function ($user, $key) {
    //   return $user->id == auth()->id();
    // });
    $json = json_encode($user_rank);
    $user_rank = json_decode($json);
    $position = array_search(auth()->id(), array_column($user_rank, 'id'));
    return response()->json(['data' => $users, 'user' => $user_rank[$position]]);
  }

}
