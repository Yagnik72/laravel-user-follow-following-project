<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Post;

class PostController extends Controller
{
    public function search($term){
        $posts = Post::search($term)->get();
        $posts->load('user:id,username,avatar');
        return $posts;
    }

    public function showEditForm(Post $post){
        return view('edit-post', ['post'=>$post]);
    }

    public function actuallyUpdate(Post $post, Request $request ){
        $incomingFields = $request->validate( [
            'title' => [ 'required'],
            'body' => [ 'required']
        ] );

        $incomingFields['title'] = strip_tags($incomingFields['title']);
        $incomingFields['body'] = strip_tags($incomingFields['body']);


        $post->update($incomingFields);

        return back()->with('success', 'Post successfully Updated.');
    }

    public function deleteApi(Post $post){

        $postTitle = $post->title;

        $post->delete();

        return 'true';
    }
    public function delete(Post $post){
        // dd($post);
        // if(auth()->user()->cannot('delete', $post)){
        //     return redirect("/post/{$post->id}")->with('failure', 'You cannot delete.');
        // }

        $postTitle = $post->title;

        $post->delete();

        return redirect("/profile/".auth()->user()->username)->with('success', '<b>'.$postTitle. '</b> post success fully deleted.');
    }

    public function ViewSinglePost(Post $post){

        // if($post->user_id === auth()->user)

        $post['body'] = strip_tags(Str::markdown($post->body), '<p><ul><h3><br><strong><a>');
        return view('single-post', ['post'=>$post]);
    }

    public function ShowCreateForm(){
        // if(!auth()->check()){
        //     return redirect( '/' );
        // }
        return view( 'create-post' );
    }

    public function storeNewPostApi(Request $request){
        $incomingFields = $request->validate( [
            'title' => [ 'required'],
            'body' => [ 'required']
        ] );

        $incomingFields['title'] = strip_tags($incomingFields['title']);
        $incomingFields['body'] = strip_tags($incomingFields['body']);
        $incomingFields['user_id'] = auth()->id();

        $newPost = Post::create($incomingFields);

        return response()->json($newPost, 200);
    }

    public function StoreNewPost(Request $request){
        $incomingFields = $request->validate( [
            'title' => [ 'required'],
            'body' => [ 'required']
        ] );

        $incomingFields['title'] = strip_tags($incomingFields['title']);
        $incomingFields['body'] = strip_tags($incomingFields['body']);
        $incomingFields['user_id'] = auth()->id();

        $newPost = Post::create($incomingFields);

        return redirect("/post/{$newPost->id}")->with('success', 'New post successfully created');

    }

}
