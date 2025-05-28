<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Dependency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceController extends Controller
{
    public function index()
    {
        $devices = Device::with(['dependency', 'printer', 'scanner'])->get();

        return response()->json([
            'message' => 'Devices retrieved successfully',
            'data' => $devices
        ], 200);
    }

    public function store(Request $request)
    {
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
        ]);

        $data = $request->all();
        $data['created_by_user'] = Auth::id();
        $data['updated_by_user'] = Auth::id();

        $device = Device::create($data);
        $device->load(['dependency', 'printer', 'scanner']);

        return response()->json([
            'message' => 'Device created successfully',
            'data' => $device
        ], 201);
    }

    public function show(Device $device)
    {
        $device->load(['dependency', 'printer', 'scanner']);

        return response()->json([
            'message' => 'Device retrieved successfully',
            'data' => $device
        ], 200);
    }

    public function update(Request $request, Device $device)
    {
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
        ]);

        $data = $request->all();
        $data['updated_by_user'] = Auth::id();

        $device->update($data);
        $device->load(['dependency', 'printer', 'scanner']);

        return response()->json([
            'message' => 'Device updated successfully',
            'data' => $device
        ], 200);
    }

    public function destroy(Device $device)
    {
        $device->delete();

        return response()->json([
            'message' => 'Device deleted successfully'
        ], 200);
    }

    // Método adicional para obtener dependencias para el select
    public function getDependencies()
    {
        $dependencies = Dependency::select('id', 'name')->get();

        return response()->json([
            'message' => 'Dependencies retrieved successfully',
            'data' => $dependencies
        ], 200);
    }
}
