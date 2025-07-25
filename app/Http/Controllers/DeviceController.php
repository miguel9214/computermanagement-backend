<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Dependency;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Exception;

class DeviceController extends Controller
{
    /**
     * Listado paginado y filtrado de dispositivos.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search  = $request->input('search');

            $query = Device::with(['dependency', 'printer', 'scanner']);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('device_name', 'like', "%{$search}%")
                      ->orWhere('ip', 'like', "%{$search}%")
                      ->orWhere('mac', 'like', "%{$search}%");
                });
            }

            $devices = $query->paginate($perPage);

            return response()->json([
                'message' => 'Devices retrieved successfully',
                'data'    => $devices
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error retrieving devices',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un nuevo dispositivo, guardando también su historial.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'dependency_id'     => 'required|exists:dependencies,id',
                'printer_id'        => 'nullable|exists:printers,id',
                'scanner_id'        => 'nullable|exists:scanners,id',
                'device_name'       => 'required|string|max:255',
                'property'          => 'nullable|string|max:255',
                'status'            => 'nullable|string|max:255',
                'os'                => 'nullable|string|max:255',
                'brand'             => 'nullable|string|max:255',
                'model'             => 'nullable|string|max:255',
                'cpu'               => 'nullable|string|max:255',
                'office_package'    => 'nullable|string|max:255',
                'asset_tag'         => 'nullable|string|max:255',
                'printer_asset'     => 'nullable|string|max:255',
                'scanner_asset'     => 'nullable|string|max:255',
                'ram'               => 'nullable|integer',
                'hdd'               => 'nullable|integer',
                'ip'                => 'nullable|ip',
                'mac'               => 'nullable|string|max:255',
                'serial'            => 'nullable|string|max:255',
                'anydesk'           => 'nullable|string|max:255',
                'operator'          => 'nullable|string|max:255',
                'notes'             => 'nullable|string|max:1000',
                'history'           => 'nullable|string', // <-- nuevo campo
            ]);

            $data = $request->all();
            $data['created_by_user'] = Auth::id();
            $data['updated_by_user'] = Auth::id();

            $device = Device::create($data);
            $device->load(['dependency', 'printer', 'scanner']);

            return response()->json([
                'message' => 'Device created successfully',
                'data'    => $device
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error creating device',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener un dispositivo en particular.
     */
    public function show(Device $device): JsonResponse
    {
        try {
            $device->load(['dependency', 'printer', 'scanner']);

            return response()->json([
                'message' => 'Device retrieved successfully',
                'data'    => $device
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error retrieving device',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar un dispositivo (incluye history).
     */
    public function update(Request $request, Device $device): JsonResponse
    {
        try {
            $request->validate([
                'dependency_id'     => 'required|exists:dependencies,id',
                'printer_id'        => 'nullable|exists:printers,id',
                'scanner_id'        => 'nullable|exists:scanners,id',
                'device_name'       => 'required|string|max:255',
                'property'          => 'nullable|string|max:255',
                'status'            => 'nullable|string|max:255',
                'os'                => 'nullable|string|max:255',
                'brand'             => 'nullable|string|max:255',
                'model'             => 'nullable|string|max:255',
                'cpu'               => 'nullable|string|max:255',
                'office_package'    => 'nullable|string|max:255',
                'asset_tag'         => 'nullable|string|max:255',
                'printer_asset'     => 'nullable|string|max:255',
                'scanner_asset'     => 'nullable|string|max:255',
                'ram'               => 'nullable|integer',
                'hdd'               => 'nullable|integer',
                'ip'                => 'nullable|ip',
                'mac'               => 'nullable|string|max:255',
                'serial'            => 'nullable|string|max:255',
                'anydesk'           => 'nullable|string|max:255',
                'operator'          => 'nullable|string|max:255',
                'notes'             => 'nullable|string|max:1000',
                'history'           => 'nullable|string', // <-- nuevo campo
            ]);

            $data = $request->all();
            $data['updated_by_user'] = Auth::id();

            $device->update($data);
            $device->load(['dependency', 'printer', 'scanner']);

            return response()->json([
                'message' => 'Device updated successfully',
                'data'    => $device
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error updating device',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un dispositivo.
     */
    public function destroy(Device $device): JsonResponse
    {
        try {
            $device->delete();

            return response()->json([
                'message' => 'Device deleted successfully'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error deleting device',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener lista de dependencias para selects.
     */
    public function getDependencies(): JsonResponse
    {
        $dependencies = Dependency::select('id', 'name')->get();

        return response()->json([
            'message' => 'Dependencies retrieved successfully',
            'data'    => $dependencies
        ], 200);
    }
}
