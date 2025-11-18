<?php

namespace App\Http\Controllers;

use App\Models\PeripheralChangeHistory;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class PeripheralChangeHistoryController extends Controller
{
    /**
     * Listado paginado y filtrado de cambios de periféricos.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search');
            $deviceId = $request->input('device_id');
            $changeType = $request->input('change_type');

            $query = PeripheralChangeHistory::with(['device', 'createdByUser', 'updatedByUser']);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('component_name', 'like', "%{$search}%")
                      ->orWhere('technician', 'like', "%{$search}%")
                      ->orWhere('supplier', 'like', "%{$search}%");
                });
            }

            if ($deviceId) {
                $query->where('device_id', $deviceId);
            }

            if ($changeType) {
                $query->where('change_type', $changeType);
            }

            $changes = $query->orderBy('change_date', 'desc')->paginate($perPage);

            return response()->json([
                'message' => 'Peripheral changes retrieved successfully',
                'data' => $changes
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error retrieving peripheral changes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un nuevo registro de cambio de periférico.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'device_id' => 'required|integer|exists:devices,id',
                'change_date' => 'required|date',
                'change_type' => 'required|string|in:ram,hdd,ssd,teclado,mouse,monitor,impresora,escaner,otro',
                'component_name' => 'required|string',
                'old_value' => 'nullable|string',
                'new_value' => 'nullable|string',
                'reason' => 'nullable|string',
                'cost' => 'nullable|numeric|min:0',
                'supplier' => 'nullable|string',
                'technician' => 'nullable|string',
                'notes' => 'nullable|string',
            ]);

            DB::beginTransaction();

            $validated['created_by_user'] = Auth::id();
            $validated['updated_by_user'] = Auth::id();

            $change = PeripheralChangeHistory::create($validated);

            // Actualizar automáticamente el dispositivo si es RAM o HDD/SSD
            $device = Device::find($validated['device_id']);
            if ($validated['change_type'] === 'ram' && $validated['new_value']) {
                $device->update(['ram' => $validated['new_value']]);
            } elseif (in_array($validated['change_type'], ['hdd', 'ssd']) && $validated['new_value']) {
                $device->update(['hdd' => $validated['new_value']]);
            }

            DB::commit();

            $change->load(['device', 'createdByUser']);

            return response()->json([
                'message' => 'Peripheral change created successfully',
                'data' => $change
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error creating peripheral change',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar un cambio específico.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $change = PeripheralChangeHistory::with(['device', 'createdByUser', 'updatedByUser'])
                ->findOrFail($id);

            return response()->json([
                'message' => 'Peripheral change retrieved successfully',
                'data' => $change
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error retrieving peripheral change',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Actualizar un registro de cambio.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $change = PeripheralChangeHistory::findOrFail($id);

            $validated = $request->validate([
                'change_date' => 'nullable|date',
                'change_type' => 'nullable|string|in:ram,hdd,ssd,teclado,mouse,monitor,impresora,escaner,otro',
                'component_name' => 'nullable|string',
                'old_value' => 'nullable|string',
                'new_value' => 'nullable|string',
                'reason' => 'nullable|string',
                'cost' => 'nullable|numeric|min:0',
                'supplier' => 'nullable|string',
                'technician' => 'nullable|string',
                'notes' => 'nullable|string',
            ]);

            $validated['updated_by_user'] = Auth::id();

            $change->update($validated);

            $change->load(['device', 'createdByUser', 'updatedByUser']);

            return response()->json([
                'message' => 'Peripheral change updated successfully',
                'data' => $change
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error updating peripheral change',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un registro de cambio.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $change = PeripheralChangeHistory::findOrFail($id);
            $change->delete();

            return response()->json([
                'message' => 'Peripheral change deleted successfully'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error deleting peripheral change',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener historial de cambios por dispositivo.
     */
    public function getByDevice(string $deviceId): JsonResponse
    {
        try {
            $changes = PeripheralChangeHistory::with(['createdByUser', 'updatedByUser'])
                ->where('device_id', $deviceId)
                ->orderBy('change_date', 'desc')
                ->get();

            return response()->json([
                'message' => 'Device change history retrieved successfully',
                'data' => $changes
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error retrieving device change history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de cambios.
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            $startDate = $request->input('start_date', now()->subMonths(6));
            $endDate = $request->input('end_date', now());

            $changesByType = PeripheralChangeHistory::whereBetween('change_date', [$startDate, $endDate])
                ->select('change_type', DB::raw('count(*) as total'), DB::raw('sum(cost) as total_cost'))
                ->groupBy('change_type')
                ->get();

            $totalCost = PeripheralChangeHistory::whereBetween('change_date', [$startDate, $endDate])
                ->sum('cost');

            $totalChanges = PeripheralChangeHistory::whereBetween('change_date', [$startDate, $endDate])
                ->count();

            return response()->json([
                'message' => 'Statistics retrieved successfully',
                'data' => [
                    'total_changes' => $totalChanges,
                    'total_cost' => $totalCost,
                    'changes_by_type' => $changesByType,
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error retrieving statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
