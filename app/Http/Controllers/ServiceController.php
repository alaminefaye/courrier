<?php

namespace App\Http\Controllers;

use App\Models\Direction;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Service::class);
        
        $services = Service::with(['direction', 'responsable'])->paginate(15);
        return view('services.index', compact('services'));
    }

    public function create()
    {
        $this->authorize('create', Service::class);
        
        $directions = Direction::all();
        $users = User::all();
        return view('services.create', compact('directions', 'users'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Service::class);
        
        $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:services,code',
            'description' => 'nullable|string',
            'direction_id' => 'required|exists:directions,id',
            'responsable_id' => 'nullable|exists:users,id',
        ]);

        Service::create($request->all());

        return redirect()->route('services.index')
            ->with('success', 'Service créé avec succès.');
    }

    public function show(string $id)
    {
        $service = Service::with(['direction', 'responsable', 'users'])->findOrFail($id);
        $this->authorize('view', $service);
        
        return view('services.show', compact('service'));
    }

    public function edit(string $id)
    {
        $service = Service::findOrFail($id);
        $this->authorize('update', $service);
        $directions = Direction::all();
        $users = User::all();
        return view('services.edit', compact('service', 'directions', 'users'));
    }

    public function update(Request $request, string $id)
    {
        $service = Service::findOrFail($id);
        $this->authorize('update', $service);
        
        $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:services,code,' . $id,
            'description' => 'nullable|string',
            'direction_id' => 'required|exists:directions,id',
            'responsable_id' => 'nullable|exists:users,id',
        ]);

        $service->update($request->all());

        return redirect()->route('services.index')
            ->with('success', 'Service modifié avec succès.');
    }

    public function destroy(string $id)
    {
        $service = Service::findOrFail($id);
        $this->authorize('delete', $service);
        
        if ($service->users()->count() > 0 || $service->courriersEntrants()->count() > 0 || $service->courriersSortants()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer un service qui contient des utilisateurs ou des courriers.');
        }

        $service->delete();

        return redirect()->route('services.index')
            ->with('success', 'Service supprimé avec succès.');
    }
}
