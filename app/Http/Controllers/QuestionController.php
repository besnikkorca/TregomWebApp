<?php

namespace App\Http\Controllers;

use App\Achievement;
use App\UserAchievement;
use Illuminate\Http\Request;

use App\Question;
use App\Category;
use App\User;

use Auth;
use Session;


class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()

    {   


        $questions=Question::orderBy('id', 'DESC')->get();

        $topquestion=new Question;

        
        foreach($questions as $testquestion){
            if( empty($topquestion) OR ($testquestion->upVotes->count()>$topquestion->upVotes->count()) )
            {
                $topquestion=$testquestion;
            }
            
        }
        

        return view('questions.index')->with('questions',$questions)->with('topquestion',$topquestion);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(auth::guest())
            return view('auth.login');
        
        $categories=Category::all();
        return view('questions.create2')->withCategories($categories);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(auth::guest())
            return view('auth.login');
         //validate data
        $this -> validate($request ,array(
                'title' => 'required | max:50|unique:questions',
                'content'  => 'required | max:500'
            ));

        //save to database
        
        $question=new Question;

        

        $question->title = $request->title;
        $question->content = $request->content;
        $question->category_id = $request->category_id;
        $question->user_id = Auth::user()->id;

        

        //Category::create([$category]);

        $question->save();
        //redirect to another page

        if(Question::where('questions.user_id','=',Auth::user()->id)->count()==1){



            if(UserAchievement::where([['user_id','=',Auth::user()->id],['achievement_id','=','1']])->get()->count()==0){
                UserAchievement::create([
                    'achievement_id'=>'1',
                    'user_id'=>Auth::user()->id
                ]);

                if(Auth::user()->reputation==null){
                    Auth::user()->reputation=0;
                }
                Auth::user()->reputation+=Achievement::find('1')->reputationaward;
                Auth::user()->save();

                Session::flash('success','You have posted your first question! Congrats you won 10 reputation!');
            }
        } else if(Question::where('questions.user_id','=',Auth::user()->id)->count()==5){
            
            if(UserAchievement::where([['user_id','=',Auth::user()->id],['achievement_id','=','3']])->get()->count()==0){
                UserAchievement::create([
                    'achievement_id'=>'3',
                    'user_id'=>Auth::user()->id
                ]);
                
                if(Auth::user()->reputation==null){
                    Auth::user()->reputation=0;
                }
                Auth::user()->reputation+=Achievement::find('3')->reputationaward;
                Auth::user()->save();
                Session::flash('success','You have posted five questions! Congrats you won 25 reputation!');
            }
        }



        else
        {
            Session::flash('success','Your question was successfully saved!');
        }   

        return redirect()->route('questions.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $question=Question::find($id);
        return view ('questions.show')->withQuestion($question);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    public function filter($orderBy){
        if($orderBy == "Newest"){

            $questions = Question::orderBy('created_at', 'desc')->get();
        }
        elseif ($orderBy == "A-Z" ){
            $questions = Question::orderBy('title')->get();

        }
        elseif ($orderBy == "Z-A"){
            $questions = Question::orderBy('title' ,'desc')->get();


        }
        return view ('questions.index')->withQuestions($questions);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(auth::guest())
            return view('auth.login');
        $question=Question::find($id);

        $evaluations=$question->allEvaluations;

        $answers=$question->allAnswers;

        foreach($evaluations as $eval){
            $eval->delete();
        }

        foreach($answers as $answer){
            $answer->delete();
        }
        
        $question->delete();

        return redirect()->route('questions.index');
        
    }
    
}
