<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, int $productId)
    {
        $product = Product::findOrFail($productId);

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:5|max:1000',
        ]);

        // Check if user already reviewed
        $existing = Review::where('user_id', auth()->id())->where('product_id', $product->id)->first();
        if ($existing) {
            return back()->with('error', 'Ya has publicado una valoración para este producto.');
        }

        Review::create([
            'user_id' => auth()->id(),
            'product_id' => $product->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_approved' => true,
        ]);

        // Update product average rating & count
        $reviews = Review::where('product_id', $product->id)->where('is_approved', true)->get();
        $avgRating = $reviews->avg('rating') ?: 5.0;
        $product->update([
            'rating' => round($avgRating, 2),
            'reviews_count' => $reviews->count(),
        ]);

        return back()->with('success', '¡Gracias! Tu valoración ha sido publicada con éxito.');
    }
}
