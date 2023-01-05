<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::orderBy('created_at', 'desc')->with('user:id,name,image')->withCount('comments','likes')->with('likes', function($likeid){
            $likeid->where('user_id', auth()->user()->id)->select('id', 'post_id', 'user_id')->get();
        })->get();
        return response()->json($posts, 200);
    }

    public function store(Request $request)
    {
        $attrs = $request->validate([
            'title' => 'required|string',
            'body' => 'required|string',
        ]);

        $image = $this->saveImage($request->image, 'posts');

        $post = Post::create([
            'title' => $attrs['title'],
            'body' => $attrs['body'],
            'user_id' => auth()->user()->id,
            'image' => $image
        ]);

        // TODO : implement image uploading

        return response([
            'message' => 'Post created',
            'post' => $post
        ], 200);
    }

    public function show($id)
    {
        $post = Post::find($id)->withCount('comments','likes')->get();
        return response()->json($post, 200);
    }

    public function update(Request $request, $id)
    {
        $post = Post::find($id);
        
        if(!$post) {
            return response([
                'message' => 'Post not found'
            ], 403);
        }

        if($post->user_id != auth()->user()->id){
            return response([
                'message' => 'Permission denied'
            ], 403);
        }

        $attrs = $request->validate([
            'title' => 'required|string',
            'body' => 'required|string',
        ]);

        $post->update([
            'title' => $attrs['title'],
            'body' => $attrs['body'],
        ]);

        return response([
            'message' => 'Post updated',
            'post' => $post
        ], 200);
    }

    public function destroy($id)
    {
       $post = Post::find($id);
        
        if(!$post) {
            return response([
                'message' => 'Post not found'
            ], 403);
        }

        if($post->user_id != auth()->user()->id){
            return response([
                'message' => 'Permission denied'
            ], 403);
        }

        $post->comments()->delete();
        $post->likes()->delete();
        $post->delete();

        return response([
            'message' => 'Post deleted',
        ], 200);
    }
}
