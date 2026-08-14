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

            if ($rating->likes_count > 0) {
                $rating->decrement('likes_count');
            }

            $liked = false;
        } else {
            RatingLike::create([
                'rating_id' => $rating->id,
                'user_id' => auth()->id(),
            ]);

            $rating->increment('likes_count');

            $liked = true;
        }

        return response()->json([
            'status' => true,
            'liked' => $liked,
            'count' => $rating->fresh()->likes_count,
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

        $rating->increment('comments_count');

        $user = auth()->user();

        return response()->json([
            'status' => true,
            'id' => $comment->id,
            'rating_id' => $rating->id,
            'count' => $rating->fresh()->comments_count,
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

        if ($rating && $rating->comments_count > 0) {
            $rating->decrement('comments_count');
        }

        return response()->json([
            'status' => true,
            'rating_id' => $rating ? $rating->id : null,
            'count' => $rating ? $rating->fresh()->comments_count : 0,
        ]);
    }
}