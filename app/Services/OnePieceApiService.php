<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class OnePieceApiService
{
    private $baseUrl = 'https://api.api-onepiece.com/v2';

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Search characters by name
     */
    public function searchCharacters($query)
    {
        return Cache::remember("one_piece_search_{$query}", 86400, function () use ($query) { // Cache for 24 hours
            try {
                $response = Http::timeout(10)->get("{$this->baseUrl}/characters/en/search/?name={$query}");

                if ($response->successful()) {
                    return $response->json();
                }

                return [];
            } catch (\Exception $e) {
                \Log::error("One Piece API error searching characters: " . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Get a specific character by ID
     */
    public function getCharacter($id)
    {
        return Cache::remember("one_piece_character_{$id}", 86400, function () use ($id) { // Cache for 24 hours
            try {
                $response = Http::timeout(10)->get("{$this->baseUrl}/characters/en/{$id}");

                if ($response->successful()) {
                    return $response->json();
                }

                return null;
            } catch (\Exception $e) {
                \Log::error("One Piece API error fetching character {$id}: " . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Get character data from One Piece API by name
     */
    public function getCharacterWithImage($characterName)
    {
        try {
            // Get from One Piece API
            $characters = $this->searchCharacters($characterName);

            if (empty($characters)) {
                throw new \Exception("Character '{$characterName}' not found in One Piece API");
            }

            $character = $characters[0]; // Get first match

            // Validate required fields
            if (!isset($character['id'])) {
                throw new \Exception("Character '{$characterName}' missing ID in API response");
            }

            return [
                'id' => $character['id'],
                'name' => $character['name'] ?? $characterName,
                'image' => $character['image'] ?? $this->fetchGoogleImage($characterName),
                'bounty' => $character['bounty'] ?? 'Unknown',
                'devil_fruit' => $character['devil_fruit'] ?? null,
                'crew' => $character['crew'] ?? null,
                'source' => 'one_piece_api'
            ];

        } catch (\Exception $e) {
            \Log::error("One Piece API error for character '{$characterName}': " . $e->getMessage());
            throw new \Exception("Failed to retrieve character '{$characterName}': " . $e->getMessage());
        }
    }

    /**
     * Get character data from One Piece API by ID
     */
    public function getCharacterWithImageById($characterId)
    {
        try {
            // Get from One Piece API by ID
            $character = $this->getCharacter($characterId);

            if (!$character) {
                throw new \Exception("Character with ID '{$characterId}' not found in One Piece API");
            }

            // Validate required fields
            if (!isset($character['id'])) {
                throw new \Exception("Character with ID '{$characterId}' missing ID in API response");
            }

            return [
                'id' => $character['id'],
                'name' => $character['name'] ?? 'Unknown',
                'image' => $character['image'] ?? $this->fetchGoogleImage($character['name'] ?? 'Character'),
                'bounty' => $character['bounty'] ?? 'Unknown',
                'devil_fruit' => $character['devil_fruit'] ?? null,
                'crew' => $character['crew'] ?? null,
                'source' => 'one_piece_api'
            ];

        } catch (\Exception $e) {
            \Log::error("One Piece API error for character ID '{$characterId}': " . $e->getMessage());
            throw new \Exception("Failed to retrieve character with ID '{$characterId}': " . $e->getMessage());
        }
    }


    /**
     * Fallback method to fetch image from Google with caching
     */
    private function fetchGoogleImage($characterName)
    {
        $cacheKey = "google_image_" . str_replace(' ', '_', strtolower($characterName));

        return Cache::remember($cacheKey, 86400, function () use ($characterName) { // Cache for 24 hours
            $apiKey = config('services.google.api_key');
            $searchEngineId = config('services.google.search_engine_id');
            $query = urlencode($characterName . ' One Piece');

            $url = "https://www.googleapis.com/customsearch/v1?key=$apiKey&cx=$searchEngineId&searchType=image&q=$query";

            try {
                $response = Http::timeout(30)->get($url);

                if ($response->successful()) {
                    $data = $response->json();

                    if (isset($data['items'][0]['link'])) {
                        $imageUrl = $data['items'][0]['link'];

                        // Skip wikia URLs
                        if ($this->isWikiaUrl($imageUrl)) {
                            return $this->findNonWikiaImage($data['items']);
                        }

                        return $imageUrl;
                    }
                }

                // Handle rate limiting
                if ($response->status() === 429) {
                    \Log::warning("Google API rate limited for {$characterName}");
                    return $this->getPlaceholderImage($characterName);
                }

                return $this->getPlaceholderImage($characterName);

            } catch (\Exception $e) {
                \Log::error("Google API error for {$characterName}: " . $e->getMessage());
                return $this->getPlaceholderImage($characterName);
            }
        });
    }

    /**
     * Check if URL is from wikia/fandom
     */
    private function isWikiaUrl($url)
    {
        return strpos($url, 'wikia') !== false ||
            strpos($url, 'fandom') !== false ||
            strpos($url, 'nocookie.net') !== false;
    }

    /**
     * Find first non-wikia image from search results
     */
    private function findNonWikiaImage($items)
    {
        $maxAttempts = min(10, count($items));

        for ($i = 0; $i < $maxAttempts; $i++) {
            if (isset($items[$i]['link'])) {
                $imageUrl = $items[$i]['link'];

                if (!$this->isWikiaUrl($imageUrl)) {
                    return $imageUrl;
                }
            }
        }

        return $this->getPlaceholderImage('Character');
    }

    /**
     * Get placeholder image URL
     */
    private function getPlaceholderImage($characterName)
    {
        return 'https://via.placeholder.com/300x400?text=' . urlencode($characterName);
    }

    /**
     * Clear all cached data for a specific character
     */
    public function clearCharacterCache($characterName)
    {
        $searchCacheKey = "one_piece_search_{$characterName}";
        $imageCacheKey = "google_image_" . str_replace(' ', '_', strtolower($characterName));

        Cache::forget($searchCacheKey);
        Cache::forget($imageCacheKey);

        \Log::info("Cleared cache for character: {$characterName}");
    }

    /**
     * Clear all One Piece API caches
     */
    public function clearAllCaches()
    {
        // This is a simple approach - in production you might want more sophisticated cache clearing
        Cache::flush();
        \Log::info("Cleared all One Piece API caches");
    }
}
