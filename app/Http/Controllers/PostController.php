<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Requests\PostCreateRequest;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {   
        $posts = Post::all()->sortDesc();
        return view('post.index', ['posts' => $posts]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(!session()->has('user')) {
            session()->flash('message', 'To do this you have to be logged in');
            return redirect('post');
        }
        
        return view('post.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PostCreateRequest $request)
    {
        try {
            $post = new Post($request->all());
            $post->iduser = $request->session()->get('user')->id;
            $post->save();
            return redirect('post');
        } catch(\Exception $e) {
            return back()->withInput($request->all())->withErrors(
                ['default' => 'Something went wrong']);
        }
        
    }

    public function edit(Post $post)
    {
        if(!session()->has('user') || !$post->getMinutes($post)) {
            session()->flash('message', 'Sorry but you cannot do this');
            return redirect('post');
        }
        return view('post.edit', ['post' => $post]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(PostCreateRequest $request, Post $post)
    {
        try {
            if(session()->get('user')->id != $post->user->id) {
                session()->flash('message', 'You cannot access to foreign data');
            } else if(!$post->getMinutes($post)) {
                session()->flash('message', 'Time to edit expired');
            } else {
                $post->update($request->all());
            }
            return redirect('post');
        } catch(\Exception) {
            return back()->withInput()->withErrors(
                ['default' =>
                'Something went wrong']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        try {
            if(session()->get('user')->id != $post->user->id) {
                session()->flash('message', 'You cannot access to foreign data');
            } else if(!$post->getMinutes($post)) {
                session()->flash('message', 'Time to delete expired');
            } else {
                $post->delete();
            }
            return redirect('post');
        } catch(\Exception $e) {
            return back()->withErrors(
                ['default' => 'Error when deleting']);
        }
    }
}
