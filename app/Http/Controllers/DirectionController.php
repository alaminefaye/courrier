<?php

namespace App\Http\Controllers;

use App\Models\Direction;
use Illuminate\Http\Request;

class DirectionController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Direction::class);
        
        $directions = Direction::withCount('services')->paginate(15);
        return view('directions.index', compact('directions'));
    }

    public function create()
    {
        $this->authorize('create', Direction::class);
        
        return view('directions.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Direction::class);
        
        $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:directions,code',
            'description' => 'nullable|string',
        ]);

        Direction::create($request->all());

        return redirect()->route('directions.index')
            ->with('success', 'Direction créée avec succès.');
    }

    public function show(string $id)
    {
        $direction = Direction::with('services')->findOrFail($id);
        $this->authorize('view', $direction);
        
        return view('directions.show', compact('direction'));
    }

    public function edit(string $id)
    {
        $direction = Direction::findOrFail($id);
        $this->authorize('update', $direction);
        
        return view('directions.edit', compact('direction'));
    }

    public function update(Request $request, string $id)
    {
        $direction = Direction::findOrFail($id);
        $this->authorize('update', $direction);
        
        $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:directions,code,' . $id,
            'description' => 'nullable|string',
        ]);

        $direction->update($request->all());

        return redirect()->route('directions.index')
            ->with('success', 'Direction modifiée avec succès.');
    }

    public function destroy(string $id)
    {
        $direction = Direction::findOrFail($id);
        $this->authorize('delete', $direction);
        
        if ($direction->services()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer une direction qui contient des services.');
        }

        $direction->delete();

        return redirect()->route('directions.index')
            ->with('success', 'Direction supprimée avec succès.');
    }
}
