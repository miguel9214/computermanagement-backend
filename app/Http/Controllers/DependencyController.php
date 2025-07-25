<?php

namespace App\Http\Controllers;

use App\Models\Dependency;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Exception;

class DependencyController extends Controller
{
    /**
     * Listado paginado de dependencias con filtro opcional.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->input('per_page', 10);
            $search  = $request->input('search', '');

            // Construye la consulta
            $query = Dependency::select(['id', 'name']);
            if ($search !== '') {
                $query->where('name', 'like', "%{$search}%");
            }

            $paginated = $query->paginate($perPage);

            return response()->json([
                'message' => 'Dependencies retrieved successfully',
                'data'    => $paginated,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error retrieving dependencies',
                'errors'  => [$e->getMessage()],
            ], 500);
        }
    }

    /**
     * Crea una nueva dependencia.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $validated['created_by_user'] = Auth::id();
            $validated['updated_by_user'] = Auth::id();

            $dependency = Dependency::create($validated);

            return response()->json([
                'message' => 'Dependency created successfully',
                'data'    => $dependency,
            ], 201);
        } catch (Exception $e) {
            // Si viene de validación, Laravel ya retorna JSON 422
            $status = $e instanceof \Illuminate\Validation\ValidationException ? 422 : 500;
            return response()->json([
                'message' => 'Error creating dependency',
                'errors'  => [$e->getMessage()],
            ], $status);
        }
    }

    /**
     * Muestra una dependencia.
     */
    public function show(Dependency $dependency): JsonResponse
    {
        try {
            return response()->json([
                'message' => 'Dependency retrieved successfully',
                'data'    => $dependency,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error retrieving dependency',
                'errors'  => [$e->getMessage()],
            ], 500);
        }
    }

    /**
     * Actualiza una dependencia.
     */
    public function update(Request $request, Dependency $dependency): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $validated['updated_by_user'] = Auth::id();
            $dependency->update($validated);

            return response()->json([
                'message' => 'Dependency updated successfully',
                'data'    => $dependency,
            ], 200);
        } catch (Exception $e) {
            $status = $e instanceof \Illuminate\Validation\ValidationException ? 422 : 500;
            return response()->json([
                'message' => 'Error updating dependency',
                'errors'  => [$e->getMessage()],
            ], $status);
        }
    }

    /**
     * Elimina una dependencia.
     */
    public function destroy(Dependency $dependency): JsonResponse
    {
        try {
            $dependency->delete();
            return response()->json([
                'message' => 'Dependency deleted successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error deleting dependency',
                'errors'  => [$e->getMessage()],
            ], 500);
        }
    }
}
