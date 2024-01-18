<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookCommentController extends Controller
{
    public function createComment(Request $request, int $id)
    {
        $user_id = Auth::id();
        $book = Book::findOrFail($id);

        //is_reviewer_commentはtrueかfalseをセットしているが、MySQLの使用上tinyint(1)として扱われるのでデータベースには１か０がセットされる
        $books_review_comment = BookComment::create([
                                    'user_id' => $user_id,
                                    'book_id' => $id,
                                    'comment' => $request->input('comment'),
                                    'is_reviewer_comment' => $book->user->id === $user_id ? 1 : 0,
                                    'comment_likes' => 0
                                ]);

        return response()->json([
                    'user_name' => $books_review_comment->user->name,
                    'user_image_url' => $books_review_comment->user->image_url,
                    'comment' => $books_review_comment->comment,
                    'comment_likes' => $books_review_comment->comment_likes
                ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function editComment(Request $request, int $id)
    {
        $book_review_comment = BookComment::findOrFail($id);

        $book_review_comment->update([ 'comment' => $request->input('comment') ]);

        return response()->json([
            'user_name' => $book_review_comment->user->name,
            'user_image_url' => $book_review_comment->user->image_url,
            'comment' => $book_review_comment->comment,
            'comment_likes' => $book_review_comment->comment_likes
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function updateLikes(Request $request)
    {
        $likes_count_change = $request->input('likes');
        $comment = BookComment::findOrFail($request->input('comment_id'));
        if($comment->comment_likes === 0 && $likes_count_change === -1) { //いいねが０を下回らないようにする
            return response()->json([
                'comment_likes' => 0
            ], 200);
        }
        $comment_likes_count = $comment->comment_likes + $likes_count_change;
        $comment->update(['comment_likes' => $comment_likes_count ]);

        return response()->json([
            'comment_likes' => $comment->comment_likes
        ], 200);
    }

    public function getComment(Request $request, int $id)
    {
        $number = $request->query('comment_offset', $default = 0);

        $books_review_comment = BookComment::where('book_id', $id)->offset($number)->limit(10)->orderBy('id', 'desc')->get();

        $review_comment_array = [];
        foreach ($books_review_comment as $review_comment) {
            $is_your_comment = false;
            if($review_comment->user_id === Auth::id()) { //認証ユーザーが書いたコメントかを判定
                $is_your_comment = true;
            }

            $review_comment_array[] = [
                'id' => $review_comment->id,
                'user_name' => $review_comment->user->name,
                'user_image_url' => $review_comment->user->image_url,
                'comment' => $review_comment->comment,
                'comment_likes' => $review_comment->comment_likes,
                'is_reviewer' => $review_comment->is_reviewer_comment, //MySQLのboolean型からデータを引っ張ってきているのでレスポンスが１または０になる
                'is_your_comment' => $is_your_comment
            ];
        }

        return response()->json($review_comment_array, 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function deleteComment(int $id)
    {
        BookComment::findOrFail($id)->delete();

        return 'delete success!!';
    }
}
