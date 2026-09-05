<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\RatingLike;
use App\Models\RatingComment;
use Illuminate\Http\Request;

class RatingInteractionController extends Controller
{
    public function like(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'status' => false,
                'message' => 'Please login first'
            ], 401);
        }

        $request->validate([
            'rating_id' => 'required|exists:ratings,id',
        ]);

        $rating = Rating::findOrFail($request->rating_id);

       

        $like = RatingLike::where('rating_id', $rating->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($like) {
            $like->delete();

            $liked = false;
        } else {
            RatingLike::create([
                'rating_id' => $rating->id,
                'user_id' => auth()->id(),
            ]);

            $liked = true;
        }

        // The `ratings` table has no likes_count column — that used to make
        // every like/unlike 500 (increment()/decrement() on a missing
        // column), silently failing the button after already writing the
        // RatingLike row. Count the real relation instead of caching it.
        return response()->json([
            'status' => true,
            'liked' => $liked,
            'count' => $rating->likes()->count(),
        ]);
    }

    public function comment(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'status' => false,
                'message' => 'Please login first'
            ], 401);
        }

        $request->validate([
            'rating_id' => 'required|exists:ratings,id',
            'comment' => 'required|string|max:1000',
        ]);

        $rating = Rating::findOrFail($request->rating_id);

        $comment = RatingComment::create([
            'rating_id' => $rating->id,
            'user_id' => auth()->id(),
            'comment' => $request->comment,
        ]);

        $user = auth()->user();

        // Same as likes above: `ratings` has no comments_count column, so
        // increment() would 500 here. Count the real relation instead.
        return response()->json([
            'status' => true,
            'id' => $comment->id,
            'rating_id' => $rating->id,
            'count' => $rating->comments()->count(),
            'name' => $user->fullname ?? $user->name ?? 'User',
            'comment' => $comment->comment,
            'time' => 'Just now',
            'image' => asset('assets/images/user82.avif'),
            'can_delete' => true,
        ]);
    }

    public function deleteComment($id)
    {
        if (!auth()->check()) {
            return response()->json([
                'status' => false,
                'message' => 'Please login first'
            ], 401);
        }

        $comment = RatingComment::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $rating = Rating::find($comment->rating_id);

        $comment->delete();

        return response()->json([
            'status' => true,
            'rating_id' => $rating ? $rating->id : null,
            'count' => $rating ? $rating->comments()->count() : 0,
        ]);
    }
}