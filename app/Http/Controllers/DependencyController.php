<?php

namespace App\Http\Controllers;

use App\Models\Dependency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DependencyController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $itemsPerPage = $request->input('itemsPerPage', 5);

        $query = DB::table("dependencies as d")->select(
            "d.id",
            "d.name"
        );

        if ($search) {
            $query->where('d.name', 'like', '%' . $search . '%');
        }

        $dependenciesList = $query->paginate($itemsPerPage);

        // Cambio aquí: devolver 'data' en lugar de 'dependencies'
        return response()->json([
            'message' => 'Dependencies List',
            'data' => $dependenciesList
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        // Agregar los campos de auditoría
        $data = $request->only('name');
        $data['created_by_user'] = Auth::id();
        $data['updated_by_user'] = Auth::id();

        $dependency = Dependency::create($data);

        return response()->json([
            'message' => 'Dependency created successfully',
            'data' => $dependency
        ], 201);
    }

    public function show(Dependency $dependency)
    {
        return response()->json([
            'message' => 'Dependency found',
            'data' => $dependency
        ], 200);
    }

    public function update(Request $request, Dependency $dependency)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $data = $request->only('name');
        $data['updated_by_user'] = Auth::id();

        $dependency->update($data);

        return response()->json([
            'message' => 'Dependency updated successfully',
            'data' => $dependency
        ], 200);
    }

    public function destroy(Dependency $dependency)
    {
        $dependency->delete();
        return response()->json([
            'message' => 'Dependency deleted successfully'
        ], 200);
    }
}
