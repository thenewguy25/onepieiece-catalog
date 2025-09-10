<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Services\OnePieceApiService;
use Illuminate\Http\Request;

class CharacterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $onePieceApi = new OnePieceApiService();
        $characters = [];
        $searchQuery = '';

        // Check if this is a search request
        if ($request->isMethod('post') && $request->has('name')) {
            $searchQuery = $request->input('name');

            try {
                $characterData = $onePieceApi->getCharacterWithImage($searchQuery);
                $characters[] = $characterData;
            } catch (\Exception $e) {
                \Log::error("Failed to fetch character '{$searchQuery}': " . $e->getMessage());
                // Return empty array for failed search
                $characters = [];
            }
        } else {
            // Default: Show Straw Hat crew members
            $strawHatCrew = [
                'Monkey D Luffy',
                'Roronoa Zoro',
                'Nami',
                'Usopp',
                'Sanji',
                'Chopper',
                'Nico Robin',
                'Franky',
                'Brook',
                'Jinbe'
            ];

            // Fetch each crew member using the One Piece API service
            foreach ($strawHatCrew as $crewMember) {
                try {
                    $characterData = $onePieceApi->getCharacterWithImage($crewMember);
                    $characters[] = $characterData;
                } catch (\Exception $e) {
                    \Log::error("Failed to fetch character '{$crewMember}': " . $e->getMessage());
                    // You can choose to skip this character or handle the error differently
                    // For now, we'll skip and continue with other characters
                    continue;
                }
            }
        }

        return view('characters.index', compact('characters', 'searchQuery'));
    }



    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $onePieceApi = new OnePieceApiService();



        // Try to get character by ID from One Piece API
        $character = $onePieceApi->getCharacterWithImageById($id);
        // dd($character);
        if (!$character) {
            abort(404, 'Character not found');
        }

        return view('characters.show', compact('character'));
    }

}
