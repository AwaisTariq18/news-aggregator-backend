<?php

namespace App\Http\Controllers;

use App\Models\SavedArticle;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Http\Request;

class ArticleController extends Controller
{

    public function index(Request $request)
    {
        $user_id = $request->input('user_id');
        $query = SavedArticle::query()->where('user_id', $user_id);

        // Filter by category
        if ($request->has('category')) {
            $category = $request->input('category');
            $query->whereIn('category', $category);
        }

        // Filter by source
        if ($request->has('source')) {
            $source = $request->input('source');
            $query->whereIn('source', $source);
        }

        // Filter by authors
        if ($request->has('authors')) {
            $authors = $request->input('authors');
            $query->whereIn('author', $authors);
        }

        // Filter by published_at
        if ($request->has('published_at')) {
            $query->where('published_at', $request->published_at);
        }

        // Search filter
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%$searchTerm%")
                    ->orWhere('description', 'like', "%$searchTerm%")
                    ->orWhere('author', 'like', "%$searchTerm%");
            });
        }

        // Apply user preferences filtering
        $user = User::findOrFail($user_id);
        $preferences = $user->preferences ?? new UserPreference();

        if ($preferences->preferred_sources) {
            $sources = explode(',', $preferences->preferred_sources);
            $query->whereIn('source', $sources);
        }

        if ($preferences->preferred_categories) {
            $categories = explode(',', $preferences->preferred_categories);
            $query->whereIn('category', $categories);
        }

        if ($preferences->preferred_authors) {
            $authors = explode(',', $preferences->preferred_authors);
            $query->whereIn('author', $authors);
        }

        $articles = $query->get();

        return response()->json($articles);
    }

    public function updatePreferences(Request $request)
    {
        $user_id = $request->input('user_id');
        $user = User::findOrFail($user_id);
        $preferences = $user->preferences ?? new UserPreference();
        $preferences->user_id = $user_id;
        $preferences->preferred_sources = $request->input('preferred_sources', []);
        $preferences->preferred_categories = $request->input('preferred_categories', []);
        $preferences->preferred_authors = $request->input('preferred_authors', []);
        $preferences->save();

        return response()->json(['message' => 'User preferences updated successfully']);
    }

    public function getOptions()
    {
        $authors = SavedArticle::distinct('author')->pluck('author');
        $sources = SavedArticle::distinct('source')->pluck('source');
        $categories = SavedArticle::distinct('category')->pluck('category');

        return response()->json([
            'authors' => $authors,
            'sources' => $sources,
            'categories' => $categories,
        ]);
    }
    public function getUserPreferences($user_id)
    {
        $preferences = UserPreference::where('user_id', $user_id)->first();

        if ($preferences) {
            return response()->json([
                'preferred_authors' => $preferences->preferred_authors,
                'preferred_sources' => $preferences->preferred_sources,
                'preferred_categories' => $preferences->preferred_categories,
            ]);
        }

        // If no preferences found, return empty values
        return response()->json([
            'preferred_authors' => null,
            'preferred_sources' => null,
            'preferred_categories' => null,
        ]);
    }
}