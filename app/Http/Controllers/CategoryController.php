<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Category;

use App\SelectedCategory;

use Session;

class CategoryController extends Controller
{
   /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories=Category::all();

        return view('categories.index')->withCategories($categories);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    { 
       
            return view('categories.create');
       
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //validate data
        $this -> validate($request ,array(
                'name' => 'required | max:50',
                'description'  => 'required | max:200'
            ));

        //save to database
        
        $category=new Category;

        $category->name = $request->name;
        $category->description = $request->description;
        

        //Category::create([$category]);

        $category->save();
        //redirect to another page

        Session::flash('success','Your category was successfully saved!');

        return redirect()->route('categories.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($name)
    {
        $category=Category::find($name);
        return view ('categories.show')->withCategory($category);
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
        //
    }

    public function seeCategories(){

        $categories=Category::leftJoin('SelectedCategory','CategoryName','=','Name')
            ->where('Username','NOT CHECK IN','Admini')->orWhereNull('Username')->get();

        $userCategories=SelectedCategory::where('Username','=','Admini') ->get();


        return view ('categories.categoriesuser')->with(array('categories'=>$categories,'userCategories'=>$userCategories));



        }

    public function selectCategory($categoryName){
            $selectedCategory=SelectedCategory::where('CategoryName', $categoryName)->first();
        if($selectedCategory==null){
        
        SelectedCategory::create([
             'CategoryName'=>$categoryName,
             'Username'=>'Admini'
        ]);

        }else{
             SelectedCategory::where('CategoryName', $categoryName)->delete();
        }

        return redirect('/Kategorite');
    }
}
