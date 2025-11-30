<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Comment;
use App\Models\Customer;
use App\Models\User;
use App\Notifications\CommentMentionNotification;
use App\Notifications\CustomerCommentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class CustomerCommentsController extends Controller
{
    public function index(Customer $customer)
    {
        $business = Auth::user()->getCurrentBusiness();

        if (! $business || $customer->business_id !== $business->id) {
            abort(403, 'You do not have permission to view this customer.');
        }

        if (! user_can('customer.view', $business)) {
            abort(403, 'You do not have permission to view customers.');
        }

        $comments = Comment::query()
            ->where('business_id', $business->id)
            ->where('commentable_type', Customer::class)
            ->where('commentable_id', $customer->id)
            ->with([
                'user:id,name,email',
                'reactions.user:id,name',
            ])
            ->orderBy('created_at', 'asc')
            ->get();

        $currentUserId = Auth::id();

        $formatted = $comments->map(function (Comment $comment) use ($currentUserId) {
            return [
                'id' => $comment->id,
                'body' => $comment->body,
                'created_at' => $comment->created_at->toIso8601String(),
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                    'email' => $comment->user->email,
                ],
                'parent_id' => $comment->parent_id,
                'reactions' => $comment->reactions
                    ->groupBy('reaction')
                    ->map(fn ($group) => [
                        'reaction' => $group->first()->reaction,
                        'count' => $group->count(),
                        'reacted_by_current_user' => $group->contains('user_id', $currentUserId),
                    ])
                    ->values()
                    ->all(),
                'mentions_meta' => $comment->mentions_meta ?? [],
            ];
        });

        return response()->json([
            'comments' => $formatted,
        ]);
    }

    public function store(Request $request, Customer $customer)
    {
        $business = Auth::user()->getCurrentBusiness();

        if (! $business || $customer->business_id !== $business->id) {
            abort(403, 'You do not have permission to view this customer.');
        }

        if (! user_can('customer.view', $business)) {
            abort(403, 'You do not have permission to comment on customers.');
        }

        $validated = $request->validate([
            'body' => ['required', 'string'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
            'mentions' => ['nullable', 'array'],
            'mentions.*' => ['integer', 'exists:users,id'],
        ]);

        $author = Auth::user();

        $rawBody = $validated['body'];

        $comment = new Comment([
            'business_id' => $business->id,
            'user_id' => $author->id,
            'body' => $rawBody,
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        $comment->commentable()->associate($customer);
        $comment->save();

        $mentionIds = collect($validated['mentions'] ?? [])
            ->filter()
            ->unique()
            ->values();

        $usersQuery = $business->users();

        if ($mentionIds->isNotEmpty()) {
            $usersQuery->whereIn('users.id', $mentionIds);
        }

        $usersToScan = $usersQuery
            ->get(['users.id', 'users.name'])
            ->values();

        $matches = [];

        foreach ($usersToScan as $user) {
            $needle = '@' . $user->name;
            $offset = 0;

            while (($pos = mb_strpos($rawBody, $needle, $offset)) !== false) {
                $matches[] = [
                    'position' => $pos,
                    'length' => mb_strlen($needle),
                    'user_id' => $user->id,
                    'name' => $user->name,
                ];

                $offset = $pos + mb_strlen($needle);
            }
        }

        // Sort by position so replacements happen from left to right.
        usort($matches, fn ($a, $b) => $a['position'] <=> $b['position']);

        $placeholdersBody = $rawBody;
        $mentionsMeta = [];
        $index = 1;

        if (! empty($matches)) {
            $placeholdersBody = '';
            $cursor = 0;

            foreach ($matches as $match) {
                if ($match['position'] < $cursor) {
                    continue;
                }

                $placeholdersBody .= mb_substr(
                    $rawBody,
                    $cursor,
                    $match['position'] - $cursor
                );

                $placeholder = '{{' . $index . '}}';
                $placeholdersBody .= $placeholder;

                $mentionsMeta[] = [
                    'index' => $index,
                    'user_id' => $match['user_id'],
                    'name' => $match['name'],
                    'position' => $match['position'],
                ];

                $index++;
                $cursor = $match['position'] + $match['length'];
            }

            $placeholdersBody .= mb_substr($rawBody, $cursor);
        }

        if (! empty($matches)) {
            $comment->body = $placeholdersBody;
            $comment->mentions_meta = $mentionsMeta;
            $comment->save();

            $mentionedUserIds = collect($mentionsMeta)
                ->pluck('user_id')
                ->filter()
                ->unique()
                ->values();

            if ($mentionedUserIds->isNotEmpty()) {
                $mentionedUsers = User::whereIn('id', $mentionedUserIds)->get();
                Notification::send($mentionedUsers, new CommentMentionNotification($comment));
            }
        }

        $businessUsers = $business->users()
            ->where('users.id', '!=', $author->id)
            ->get();

        Notification::send($businessUsers, new CustomerCommentNotification($comment));

        $comment->load(['user', 'reactions.user']);

        $formatted = [
            'id' => $comment->id,
            'body' => $comment->body,
            'created_at' => $comment->created_at->toIso8601String(),
            'user' => [
                'id' => $comment->user->id,
                'name' => $comment->user->name,
                'email' => $comment->user->email,
            ],
            'parent_id' => $comment->parent_id,
            'reactions' => [],
            'mentions_meta' => $comment->mentions_meta ?? [],
        ];

        return response()->json([
            'comment' => $formatted,
        ], 201);
    }

    public function mentionableUsers(Request $request, Customer $customer)
    {
        $business = Auth::user()->getCurrentBusiness();

        if (! $business || $customer->business_id !== $business->id) {
            abort(403, 'You do not have permission to view this customer.');
        }

        $query = $request->string('q')->toString();

        $users = $business->users()
            ->when($query, function ($q) use ($query) {
                $q->where(function ($inner) use ($query) {
                    $inner->where('name', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%");
                });
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['users.id', 'users.name', 'users.email']);

        return response()->json([
            'users' => $users,
        ]);
    }
}
