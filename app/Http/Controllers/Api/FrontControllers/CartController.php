<?php

namespace App\Http\Controllers\Api\FrontControllers;

use App\Http\Resources\BookResource;
use App\Http\Resources\CartResource;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Cart;


class CartController extends Controller
{

    public function getAllCartBook(Request $request)
    {
        $carts = Cart::where('user_id', auth()->user()->id)->get();
        return CartResource::collection($carts);
    }
    
    public function addBookToCart(Request $request, string $id)
    {
        $book = Book::find($id);
        if (!$book) {
            return response()->json(['message' => 'کتاب وجود ندارد'], Response::HTTP_NOT_FOUND);
        }

        $carts = Cart::where('user_id', auth()->user()->id)->get();
        if ($carts) {
            foreach ($carts as $cart) {
                if ($cart->book_id == $id) {
                    return response()->json(['message' => 'این کتاب قبلا به کارت اضافه شده است']);
                }
            }
        }

        Cart::create([
            'user_id' => auth()->user()->id,
            'book_id' => $book->id,
        ]);

        return response()->json(['message' => 'کتاب موفقانه به کارت اضافه شد']);
    }

    public function deleteCartBook(string $id)
    {
        $book = Book::find($id);
        if (!$book) {
            return response()->json(['message' => 'کتاب پیدا نشد'], Response::HTTP_NOT_FOUND);
        }

        $carts = Cart::where('user_id', auth()->user()->id)->get();
        foreach ($carts as $cart) {
            if ($cart->book_id == $id) {
                $cart->delete();
            }
        }
        return response()->json(['message' => 'کتاب موفقانه از کارت پاک شد']);
    }
}
