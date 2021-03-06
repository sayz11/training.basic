<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Todo;
use File;
use Storage;

class TodoController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index(Request $request)
    { 
        if($request->keyword){
            $user = auth()->user();
            $todos = $user->todos()
                            ->where('title','LIKE','%'.$request->keyword.'%')
                            ->paginate(3);
        }else{
            $user = auth()->user();
            $todos = $user->todos()->paginate(3);
        }
       

       // return to view - resources/views/todos/index.blade.php
       return view('todos.index', compact('todos'));
    }
        //query list of todos from db
        //$todos = Todo::all(); - show all todos from all user
        //$todos = Todo::paginate(3);
        //yang bawah ni untuk user yang tengah login sahaja
       
        
      // dd($user); untuk tengok user yang tengah online
    public function create()
    {
        //show create form
        return view('todos.create');

    }

    public function store(Request $request)
    {
        //store todos table using model
        $todo = new Todo();
        $todo->title = $request->title;
        $todo->description= $request->description;
        $todo->user_id = auth()->user()->id;
        $todo->save();

        if($request->hasFile('attachment')){
            //rename
            $filename = $todo->id.'-'.date("Y-m-d").'.'.$request->attachment->getClientOriginalExtension(); 

            //store at file storage
            Storage::disk('public')->put($filename,File::get($request->attachment));

            //update row on db
              $todo->attachment = $filename;
              $todo->save();

        }  

        //return todos index
        return redirect()->to('/todos')->with([
            'type' => 'alert-primary',
            'message' => 'Tahniah, anda berjaya simpan todo baharu!'
        ]);
    }
    public function show(Todo $todo)
    {
        return view('todos.show', compact('todo'));

    }
    public function edit(Todo $todo)
    {
        return view('todos.edit', compact ('todo'));
    }
    
    public function update(Todo $todo, Request $request)
    {
        $todo->title = $request->title;
        $todo->description= $request->description;
        $todo->save();

        return redirect()->to('/todos')->with([
            'type' => 'alert-success',
            'message' => 'Tahniah, anda berjaya ubah todo!'
        ]);
    }
    
    public function delete(Todo $todo)
    {
        // delete attachment kalau ada
        if($todo->attachment){
            Storage::disk('public')->delete($todo->attachment);
        }
       // delete from table using model
       $todo->delete();

       // return to todos index
       return redirect()->to('/todos')->with([
        'type' => 'alert-danger',
        'message' => 'Anda telah padam todo!'
        ]);
    }
}



