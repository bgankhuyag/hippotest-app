<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Questions;
use App\Models\Answers;
use App\Models\DummyQuestions;
use App\Models\DummyAnswers;
use App\Models\User;
use Validator;

class TestController extends Controller
{
  // get the questions in specified category
  public function questions(Request $request, $category) {
    // dd($category);
    // $questions = Questions::where('category', $category)->with('answers:id,questions_id,answer,correct')->inRandomOrder()->limit(10)->get(['id', 'question']);
    $questions = DummyQuestions::where('category', $category)->with('answers:id,questions_id,answer,correct')->inRandomOrder()->limit(10)->get(['id', 'question', 'category']);
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

  // create a leaderboard of users with the higest scores and the lowest scores.
  // get the user with their rank and the user above and below them.
  public function leaderboard(Request $request) {
    DB::statement(DB::raw('set @row:=0'));
    $users = User::orderByDesc('points')->selectRaw('id, name, email, points, @row:=@row+1 as rank')->get();
    $json = json_encode($users);
    $users = json_decode($json, true);
    $position = array_search(auth()->id(), array_column($users, 'id'));
    $top = 5;
    $data = ['top' => array_slice($users, 0, $top)];
    if (sizeof($users)-1 > $top) {
      $data['last'] = array_slice($users, -2);
    } else if (sizeof($users) > $top) {
      $data['last'] = array_slice($users, -1);
    }
    if ($position >= $top && $position < sizeof($users)-2) {
      $start = $position-1;
      $size = 3;
      if ($position == $top) {
        $start++;
        $size--;
      }
      if ($position+1 == sizeof($users)-2) {
        $size--;
      }
      $data['user_rank'] = array_slice($users, $start, $size);
    }
    $data['user'] = ['id' => auth()->id(), 'name' => auth()->user()->name];
    if ($position < $top) {
      $data['user']['array'] = 'top';
    } else if ($position >= sizeof($users)-2) {
      $data['user']['array'] = 'last';
    } else {
      $data['user']['array'] = 'user_rank';
    }
    return response()->json($data);
  }

}
