<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function index()
    {
        return Device::with(['dependency', 'printer', 'scanner'])->get();
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

        return Device::create($request->all());
    }

    public function show(Device $device)
    {
        return $device->load(['dependency', 'printer', 'scanner']);
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

        $device->update($request->all());
        return $device->load(['dependency', 'printer', 'scanner']);
    }

    public function destroy(Device $device)
    {
        $device->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
