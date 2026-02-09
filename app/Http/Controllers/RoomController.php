<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * Prikaz svih soba
     * GET /api/rooms
     */
    public function index()
    {
        $rooms = Room::all();
        return response()->json($rooms, 200);
    }

    /**
     * Kreiranje nove sobe
     * POST /api/rooms
     */
    public function store(Request $request)
    {
        // Validacija podataka
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
        ]);

        $room = Room::create($validated);

        return response()->json($room, 201);
    }

    /**
     * Prikaz jedne sobe
     * GET /api/rooms/{id}
     */
    public function show($id)
    {
        $room = Room::findOrFail($id);
        return response()->json($room, 200);
    }

    /**
     * Ažuriranje sobe
     * PUT/PATCH /api/rooms/{id}
     */
    public function update(Request $request, $id)
    {
        $room = Room::findOrFail($id);

        // Validacija podataka, "sometimes" znači da polje nije obavezno
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'capacity' => 'sometimes|required|integer|min:1',
        ]);

        $room->update($validated);

        return response()->json($room, 200);
    }

    /**
     * Brisanje sobe
     * DELETE /api/rooms/{id}
     */
    public function destroy($id)
    {
        $room = Room::findOrFail($id);
        $room->delete();

        // 204 = No Content, znači uspješno obrisano
        return response()->json(null, 204);
    }
}
