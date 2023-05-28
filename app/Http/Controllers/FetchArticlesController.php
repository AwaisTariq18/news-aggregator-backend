<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\SavedArticle;
use GuzzleHttp\Client;

class FetchArticlesController extends Controller
{
    public function fetchArticles(Request $request, $userId)
    {
        $newsApiArticles = $this->fetchArticlesFromNewsApi();

        $guardianArticles = $this->fetchArticlesFromGuardian();

        $nytArticles = $this->fetchArticlesFromNewYorkTimes();

        $this->storeNewsArticles($newsApiArticles, $userId);
        $this->storeGuardianArticles($guardianArticles, $userId);
        $this->storeNewYorkArticles($nytArticles, $userId);

        return response()->json(['message' => 'Articles fetched and stored successfully!']);
    }

    protected function fetchArticlesFromNewsApi()
    {
        $apiKey = env('NEWS_API_KEY');
        $url = env('NEWS_API_URL');
        $client = new Client();
        $response = $client->get($url, [
            'query' => [
                'apiKey' => $apiKey,
                'q' => '*',
                'language' => 'en',
                'order-by' => 'newest',
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true)['articles'];
    }

    protected function fetchArticlesFromGuardian()
    {
        $apiKey = env('GUARDIAN_API_KEY');
        $url = env('GUARDIAN_API_URL');

        $client = new Client();
        $response = $client->get($url, [
            'query' => [
                'api-key' => $apiKey,
                'order-by' => 'newest',
                'page-size' => 100,
                'show-fields' => 'all'
            ]
        ]);

        $articles = json_decode($response->getBody()->getContents(), true)['response']['results'];

        return $articles;
    }
    protected function fetchArticlesFromNewYorkTimes()
    {
        $apiKey = env('NYTIMES_API_KEY');
        $url = env('NYTIMES_API_URL');

        $client = new Client();
        $articles = [];

        for ($page = 0; $page < 5; $page++) {
            $response = $client->get($url, [
                'query' => [
                    'api-key' => $apiKey,
                    'order-by' => 'newest',
                    'page' => $page + 1,
                ]
            ]);

            $pageArticles = json_decode($response->getBody()->getContents(), true)['response']['docs'];
            $articles = array_merge($articles, $pageArticles);

            if (count($articles) >= 100) {

                break;
            }
        }

        return array_slice($articles, 0, 100);
    }

    protected function storeNewsArticles($articles, $userId)
    {
        foreach ($articles as $article) {
            $publishedAt = Carbon::parse($article['publishedAt'])->toDateTimeString();
            SavedArticle::create([
                'user_id' => $userId,
                'title' => $article['title'],
                'description' => $article['description'],
                'source' => $article['source']['name'],
                'category' => 'general',
                'author' => $article['author'] ?? "",
                'published_at' => $publishedAt,
                'url' => $article['url'],
                'thumbnail' => $article['urlToImage'] ?? null,
            ]);
        }
    }

    protected function storeGuardianArticles($articles, $userId)
    {
        foreach ($articles as $article) {
            $publishedAt = Carbon::parse($article['webPublicationDate'])->toDateTimeString();
            $author = isset($article['fields']['byline']) ? $article['fields']['byline'] : "";
            SavedArticle::create([
                'user_id' => $userId,
                'title' => $article['webTitle'],
                'description' => $article['blocks']['body'][0]['bodyTextSummary'] ?? "",
                'source' => 'The Guardian',
                'category' => $article['sectionName'],
                'author' => $author,
                'published_at' => $publishedAt,
                'url' => $article['webUrl'],
                'thumbnail' => $article['fields']['thumbnail'] ?? null
            ]);
        }
    }

    protected function storeNewYorkArticles($articles, $userId)
    {

        foreach ($articles as $article) {
            $publishedAt = Carbon::parse($article['pub_date'])->toDateTimeString();
            $thumbnail = null;
            if (isset($article['multimedia'][0]['url'])) {
                $thumbnail = 'https://www.nytimes.com/' . $article['multimedia'][0]['url'];
            }
            SavedArticle::create([
                'user_id' => $userId,
                'title' => $article['headline']['main'],
                'description' => $article['abstract'],
                'source' => $article['source'],
                'category' => $article['section_name'],
                'author' => $article['byline']['original'] ?? "",
                'published_at' => $publishedAt,
                'url' => $article['web_url'],
                'thumbnail' => $thumbnail
            ]);
        }
    }
}