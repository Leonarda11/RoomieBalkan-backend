<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use Illuminate\Http\Request;

class AdsController extends Controller
{
    // GET /api/ads - svi oglasi s filterima
    public function index(Request $request)
    {
        $query = Ad::query();

        // Filter po državi
        if ($request->has('country') && $request->country) {
            $query->where('country', $request->country);
        }

        // Filter po gradu
        if ($request->has('city') && $request->city) {
            $query->where('city', $request->city);
        }

        // Filter po maksimalnoj cijeni
        if ($request->has('maxPrice') && $request->maxPrice) {
            $query->where('price', '<=', $request->maxPrice);
        }

        // Search po državi ili gradu
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('country', 'like', "%$search%")
                  ->orWhere('city', 'like', "%$search%");
            });
        }

        // Parsiranje polja images i formatiranje price/city/country
        $ads = $query->get();
        $ads->transform(function ($ad) {
            $ad->images = $ad->images ? json_decode($ad->images) : [];
            $ad->price = number_format($ad->price, 2, '.', '');
            $ad->city = ucwords(strtolower($ad->city));
            $ad->country = ucwords(strtolower($ad->country));
            return $ad;
        });

        return response()->json($ads);
    }

    // GET /api/ads/filters - jedinstvene države i gradovi
    public function filters(Request $request)
    {
        $countries = Ad::select('country')
            ->whereNotNull('country')
            ->distinct()
            ->pluck('country')
            ->map(fn($c) => ucwords(strtolower($c))); // Capitalize

        $citiesQuery = Ad::select('city')
            ->whereNotNull('city');

        if ($request->has('country') && $request->country) {
            $citiesQuery->where('country', $request->country);
        }

        $cities = $citiesQuery->distinct()->pluck('city')
            ->map(fn($c) => ucwords(strtolower($c))); // Capitalize

        return response()->json([
            'countries' => $countries,
            'cities' => $cities
        ]);
    }

    // POST /api/ads - admin i super_admin
    public function store(Request $request)
    {
        $auth = auth()->user();

        if (!$auth || !in_array($auth->role, ['admin', 'super_admin'])) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'city' => 'required|string',
            'country' => 'required|string',
            'price' => 'required|numeric',
            'image_url' => 'nullable|string',
            'images' => 'nullable|array', 
            'images.*' => 'string|nullable'
        ]);

        // Formatiranje za frontend
        $city = ucwords(strtolower($data['city']));
        $country = ucwords(strtolower($data['country']));
        $price = round($data['price'], 2);

        $ad = Ad::create([
            'title' => $data['title'],
            'description' => $data['description'],
            'city' => $city,
            'country' => $country,
            'price' => $price,
            'image_url' => $data['image_url'] ?? null,
            'images' => isset($data['images']) ? json_encode($data['images']) : null,
            'user_id' => $auth->id
        ]);

        // Transformacija za frontend odmah
        $ad->images = $ad->images ? json_decode($ad->images) : [];
        $ad->price = number_format($ad->price, 2, '.', '');
        $ad->city = $city;
        $ad->country = $country;

        return response()->json($ad, 201);
    }

    // DELETE /api/ads/{id} - vlasnik ili super_admin
    public function destroy(Ad $ad)
    {
        $auth = auth()->user();

        if (!$auth || ($auth->role !== 'super_admin' && $auth->id !== $ad->user_id)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $ad->delete();

        return response()->json(['message' => 'Ad deleted']);
    }
}
