<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BookRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Book;
use App\Models\Log;

class BookController extends Controller
{
    public function getBooks(BookRequest $request)
    {
        $number = $request->query('offset');
        $books = Book::where("title", "LIKE", "{$request->query('title_keyword')}%")
                       ->offset($number)->limit(10)->orderBy('id', 'desc')->get();

        $book_data = [];
        foreach ($books as $book) {
            $book_data[] = [
                'id' => $book->id,
                'title' => $book->title,
                'url' => $book->url,
                'detail' => $book->detail,
                'review' => $book->review,
                'reviewer' => $book->reviewer
            ];
        }
    
        return response()->json($book_data, 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function createBooks(Request $request)
    {
        $user_id = Auth::id();
        $user_name = Auth::user()->name;
        $book = Book::create([
                    'title' => $request->input('title'),
                    'user_id' => $user_id,
                    'url' => $request->input('url'),
                    'detail' => $request->input('detail'),
                    'review' => $request->input('review'),
                    'reviewer' => $user_name
                ]);

        return response()->json([
            'title' => $book->title,
            'url' => $book->url,
            'detail' => $book->detail,
            'review' => $book->review,
            'reviewer' => $user_name,
        ], 200, [], JSON_UNESCAPED_UNICODE);    
    }

    public function getBookDatail($id)
    {
        $book_datail = Book::findOrFail($id);
        $isMine = false;

        if($book_datail->user_id === Auth::id()) $isMine = true;

        return response()->json([
            'title' => $book_datail->title,
            'url' => $book_datail->url,
            'detail' => $book_datail->detail,
            'review' => $book_datail->review,
            'reviewer' => $book_datail->reviewer,
            'isMine' => $isMine,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function updateBook(Request $request, $id)
    {
        $book_datail = Book::findOrFail($id);
        $book_datail->update([
                          'title' => $request->input('title'),
                          'url' => $request->input('url'),
                          'detail' => $request->input('detail'),
                          'review' => $request->input('review'),
                    ]);

        return response()->json([
              'id' => $book_datail->id,
              'title' => $book_datail->title,
              'url' => $book_datail->url,
              'detail' => $book_datail->detail,
              'review' => $book_datail->review,
              'reviewer' => $book_datail->reviewer
          ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function setlog(Request $request)
    {
        $user_id = Auth::id();
        $log = Log::create([
                    'user_id' => $user_id,
                    'access_log' => 'http://127.0.0.1:3000/detail/'.$request->input('selectBookId')
                ]);

         return response()->json(['log' => $log->access_log ]);
    }
}
