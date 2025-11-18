<?php

namespace App\Http\Controllers;

use App\Models\Maintenance;
use App\Models\MaintenanceImage;
use App\Models\Device;
use App\Models\Printer;
use App\Models\Scanner;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Exception;

class MaintenanceController extends Controller
{
    /**
     * Listado paginado y filtrado de mantenimientos.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search');
            $type = $request->input('type'); // device, printer, scanner
            $entityId = $request->input('entity_id');
            $status = $request->input('status');

            $query = Maintenance::with(['maintainable', 'images', 'createdByUser', 'updatedByUser']);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                      ->orWhere('technician', 'like', "%{$search}%")
                      ->orWhere('maintenance_type', 'like', "%{$search}%");
                });
            }

            if ($type) {
                $typeMap = [
                    'device' => Device::class,
                    'printer' => Printer::class,
                    'scanner' => Scanner::class,
                ];
                if (isset($typeMap[$type])) {
                    $query->where('maintainable_type', $typeMap[$type]);
                }
            }

            if ($entityId) {
                $query->where('maintainable_id', $entityId);
            }

            if ($status) {
                $query->where('status', $status);
            }

            $maintenances = $query->orderBy('maintenance_date', 'desc')->paginate($perPage);

            return response()->json([
                'message' => 'Maintenances retrieved successfully',
                'data' => $maintenances
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error retrieving maintenances',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un nuevo mantenimiento.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'maintainable_type' => 'required|string|in:device,printer,scanner',
                'maintainable_id' => 'required|integer',
                'maintenance_date' => 'required|date',
                'next_maintenance_date' => 'nullable|date|after:maintenance_date',
                'maintenance_type' => 'required|string',
                'description' => 'nullable|string',
                'performed_tasks' => 'nullable|string',
                'technician' => 'nullable|string',
                'cost' => 'nullable|numeric|min:0',
                'status' => 'nullable|string|in:programado,en_proceso,completado,cancelado',
                'notes' => 'nullable|string',
                'physical_format' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:51200',
                'images.*' => 'nullable|file|mimes:jpg,jpeg,png|max:10240',
                'image_types.*' => 'nullable|string|in:equipo,antes,despues,formato',
                'image_descriptions.*' => 'nullable|string',
            ]);

            DB::beginTransaction();

            $typeMap = [
                'device' => Device::class,
                'printer' => Printer::class,
                'scanner' => Scanner::class,
            ];

            $maintenanceData = [
                'maintainable_type' => $typeMap[$validated['maintainable_type']],
                'maintainable_id' => $validated['maintainable_id'],
                'maintenance_date' => $validated['maintenance_date'],
                'next_maintenance_date' => $validated['next_maintenance_date'] ?? null,
                'maintenance_type' => $validated['maintenance_type'],
                'description' => $validated['description'] ?? null,
                'performed_tasks' => $validated['performed_tasks'] ?? null,
                'technician' => $validated['technician'] ?? null,
                'cost' => $validated['cost'] ?? null,
                'status' => $validated['status'] ?? 'completado',
                'notes' => $validated['notes'] ?? null,
                'created_by_user' => Auth::id(),
                'updated_by_user' => Auth::id(),
            ];

            if ($request->hasFile('physical_format')) {
                $path = $request->file('physical_format')->store('maintenances/formats', 'public');
                $maintenanceData['physical_format_path'] = $path;
            }

            $maintenance = Maintenance::create($maintenanceData);

            if ($request->hasFile('images')) {
                $images = $request->file('images');
                $imageTypes = $request->input('image_types', []);
                $imageDescriptions = $request->input('image_descriptions', []);

                foreach ($images as $index => $imageFile) {
                    if ($imageFile && $imageFile->isValid()) {
                        $path = $imageFile->store('maintenances/images', 'public');

                        MaintenanceImage::create([
                            'maintenance_id' => $maintenance->id,
                            'image_path' => $path,
                            'image_type' => $imageTypes[$index] ?? 'equipo',
                            'description' => $imageDescriptions[$index] ?? null,
                            'order' => $index,
                            'created_by_user' => Auth::id(),
                        ]);
                    }
                }
            }

            DB::commit();

            $maintenance->load(['maintainable', 'images', 'createdByUser']);

            return response()->json([
                'message' => 'Maintenance created successfully',
                'data' => $maintenance
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error creating maintenance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar un mantenimiento específico.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $maintenance = Maintenance::with(['maintainable', 'images', 'createdByUser', 'updatedByUser'])
                ->findOrFail($id);

            return response()->json([
                'message' => 'Maintenance retrieved successfully',
                'data' => $maintenance
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error retrieving maintenance',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Actualizar un mantenimiento.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $maintenance = Maintenance::findOrFail($id);

            $validated = $request->validate([
                'maintenance_date' => 'nullable|date',
                'next_maintenance_date' => 'nullable|date',
                'maintenance_type' => 'nullable|string',
                'description' => 'nullable|string',
                'performed_tasks' => 'nullable|string',
                'technician' => 'nullable|string',
                'cost' => 'nullable|numeric|min:0',
                'status' => 'nullable|string|in:programado,en_proceso,completado,cancelado',
                'notes' => 'nullable|string',
                'physical_format' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:51200',
            ]);

            DB::beginTransaction();

            if ($request->hasFile('physical_format')) {
                if ($maintenance->physical_format_path) {
                    Storage::disk('public')->delete($maintenance->physical_format_path);
                }
                $path = $request->file('physical_format')->store('maintenances/formats', 'public');
                $validated['physical_format_path'] = $path;
            }

            $validated['updated_by_user'] = Auth::id();

            $maintenance->update($validated);

            DB::commit();

            $maintenance->load(['maintainable', 'images', 'createdByUser', 'updatedByUser']);

            return response()->json([
                'message' => 'Maintenance updated successfully',
                'data' => $maintenance
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error updating maintenance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un mantenimiento.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $maintenance = Maintenance::findOrFail($id);

            DB::beginTransaction();

            if ($maintenance->physical_format_path) {
                Storage::disk('public')->delete($maintenance->physical_format_path);
            }

            foreach ($maintenance->images as $image) {
                Storage::disk('public')->delete($image->image_path);
            }

            $maintenance->delete();

            DB::commit();

            return response()->json([
                'message' => 'Maintenance deleted successfully'
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error deleting maintenance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Agregar imagen a un mantenimiento existente.
     */
    public function addImage(Request $request, string $id): JsonResponse
    {
        try {
            $maintenance = Maintenance::findOrFail($id);

            $validated = $request->validate([
                'file' => 'required|file|mimes:jpg,jpeg,png|max:10240',
                'type' => 'required|string|in:equipo,antes,despues,formato',
                'description' => 'nullable|string',
            ]);

            $path = $request->file('file')->store('maintenances/images', 'public');

            $lastOrder = $maintenance->images()->max('order') ?? -1;

            $image = MaintenanceImage::create([
                'maintenance_id' => $maintenance->id,
                'image_path' => $path,
                'image_type' => $validated['type'],
                'description' => $validated['description'] ?? null,
                'order' => $lastOrder + 1,
                'created_by_user' => Auth::id(),
            ]);

            return response()->json([
                'message' => 'Image added successfully',
                'data' => $image
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error adding image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar una imagen de un mantenimiento.
     */
    public function deleteImage(string $maintenanceId, string $imageId): JsonResponse
    {
        try {
            $image = MaintenanceImage::where('maintenance_id', $maintenanceId)
                ->where('id', $imageId)
                ->firstOrFail();

            Storage::disk('public')->delete($image->image_path);
            $image->delete();

            return response()->json([
                'message' => 'Image deleted successfully'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error deleting image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener próximos mantenimientos programados.
     */
    public function upcomingMaintenances(Request $request): JsonResponse
    {
        try {
            $days = $request->input('days', 30);

            $upcomingMaintenances = Maintenance::with(['maintainable'])
                ->where('next_maintenance_date', '<=', now()->addDays($days))
                ->where('next_maintenance_date', '>=', now())
                ->orderBy('next_maintenance_date', 'asc')
                ->get();

            return response()->json([
                'message' => 'Upcoming maintenances retrieved successfully',
                'data' => $upcomingMaintenances
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error retrieving upcoming maintenances',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
