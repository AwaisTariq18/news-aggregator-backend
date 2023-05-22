<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\SavedArticle;
use GuzzleHttp\Client;

class FetchArticlesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:news-api-articles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'fetch news articles from different news resources';

    public function handle(int $userId)
    {
        // Fetch articles from NewsAPI
        $newsApiArticles = $this->fetchArticlesFromNewsApi();

        // Fetch articles from The Guardian
        $guardianArticles = $this->fetchArticlesFromGuardian();

        // Fetch articles from New York Times
        $nytArticles = $this->fetchArticlesFromNewYorkTimes();

        // Store the articles in the database
        $this->storeNewsArticles($newsApiArticles, $userId);
        $this->storeGuardianArticles($guardianArticles, $userId);
        $this->storeNewYorkArticles($nytArticles, $userId);

        $this->info('Articles fetched and stored successfully!');

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
                // Add any additional query parameters as needed
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true)['articles'];
    }

    protected function fetchArticlesFromGuardian()
    {
        // Fetch articles from The Guardian using Guzzle
        $apiKey = env('GUARDIAN_API_KEY');
        $url = env('GUARDIAN_API_URL');

        $client = new Client();
        $response = $client->get($url, [
            'query' => [
                'api-key' => $apiKey,
                'order-by' => 'newest',
                'page-size' => 100,
                // Add any additional query parameters as needed
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
                // Stop fetching articles once we have 100 or more
                break;
            }
        }

        return array_slice($articles, 0, 100); // Return the first 100 articles
    }


    protected function storeNewsArticles($articles, $userId)
    {
        foreach ($articles as $article) {
            // Store the article in the database
            SavedArticle::create([
                'user_id' => $userId,
                // TODO: Change this to the user id of the user who is currently logged in
                'title' => $article['title'],
                'description' => $article['description'],
                'source' => $article['source']['name'],
                'category' => $article['category'] ?? "",
                'author' => $article['author'] ?? "",
                'published_at' => now(),
                'url' => $article['url']
            ]);
        }
    }
    protected function storeGuardianArticles($articles, $userId)
    {
        foreach ($articles as $article) {
            // Store the article in the database
            SavedArticle::create([
                'user_id' => $userId,
                'title' => $article['webTitle'],
                'description' => $article['blocks']['body'][0]['bodyTextSummary'] ?? "",
                'source' => $article['sectionName'],
                'category' => $article['sectionName'],
                'author' => $article['webTitle'],
                'published_at' => now(),
                'url' => $article['webUrl']
            ]);
        }
    }
    protected function storeNewYorkArticles($articles, $userId)
    {
        foreach ($articles as $article) {
            // Store the article in the database
            SavedArticle::create([
                'user_id' => $userId,
                'title' => $article['headline']['main'],
                'description' => $article['abstract'],
                'source' => $article['source'],
                'category' => $article['section_name'],
                'author' => $article['byline']['original'] ?? "",
                'published_at' => now(),
                'url' => $article['web_url']
            ]);
        }
    }
}