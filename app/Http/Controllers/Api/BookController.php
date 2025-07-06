<?php
// app/Http/Controllers/Api/BookController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::all()->map(function ($book) {
            return [
                'id'          => $book->id,
                'title'       => $book->title,
                'description' => $book->description,
                'pdf_url'     => asset('storage/' . $book->pdf_path),
                'image_url'   => $book->image_path ? asset('storage/' . $book->image_path) : null,
            ];
        });

        return response()->json([
            'status' => true,
            'data'   => $books,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'pdf'         => 'required|file|mimes:pdf|max:10240', // 10 MB
            'image'       => 'required|image|mimes:jpg,jpeg,png,svg|max:2048', // 2 MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $pdfPath = $request->file('pdf')->store('books/pdfs', 'public');

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('books/images', 'public');
        }

        $book = Book::create([
            'title'       => $request->title,
            'description' => $request->description,
            'pdf_path'    => $pdfPath,
            'image_path'  => $imagePath,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Book created successfully.',
            'data'    => [
                'id'          => $book->id,
                'title'       => $book->title,
                'description' => $book->description,
                'pdf_url'     => asset('storage/' . $book->pdf_path),
                'image_url'   => $book->image_path ? asset('storage/' . $book->image_path) : null,
            ]
        ], 201);
    }
}
