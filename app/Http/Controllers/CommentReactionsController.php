<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentReaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentReactionsController extends Controller
{
    public function store(Request $request, Comment $comment)
    {
        $business = Auth::user()->getCurrentBusiness();

        if (! $business || $comment->business_id !== $business->id) {
            abort(403, 'You do not have permission to react to this comment.');
        }

        $validated = $request->validate([
            'reaction' => ['required', 'string', 'max:32'],
        ]);

        $user = Auth::user();

        $existing = CommentReaction::where('comment_id', $comment->id)
            ->where('user_id', $user->id)
            ->where('reaction', $validated['reaction'])
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            CommentReaction::create([
                'comment_id' => $comment->id,
                'user_id' => $user->id,
                'reaction' => $validated['reaction'],
            ]);
        }

        $comment->load('reactions');

        $summary = $comment->reactions
            ->groupBy('reaction')
            ->map(fn ($group) => [
                'reaction' => $group->first()->reaction,
                'count' => $group->count(),
                'reacted_by_current_user' => $group->contains('user_id', $user->id),
            ])
            ->values()
            ->all();

        return response()->json([
            'reactions' => $summary,
        ]);
    }
}
