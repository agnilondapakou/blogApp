<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;

class LikeController extends Controller
{
    public function likeOrUnlike($id)
    {
        $post = Post::find($id);

        if(!$post) {
            return response([
                'message' => 'Post not found'
            ], 403);
        }

        $like = Like::where('user_id', auth()->user()->id)->where('post_id', $id)->first();

        if($like) {
            $like->delete();
            return response([
                'message' => 'Post unliked'
            ], 200);
        }

        Like::create([
            'user_id' => auth()->user()->id,
            'post_id' => $id,
        ]);

        return response([
            'message' => 'Post liked'
        ], 200);
    }
}
