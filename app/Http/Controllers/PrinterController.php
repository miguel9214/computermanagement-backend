<?php

namespace App\Http\Controllers;

use App\Models\Printer;
use Illuminate\Http\Request;

class PrinterController extends Controller
{
    public function index()
    {
        return Printer::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'brand'        => 'nullable|string|max:255',
            'model'        => 'nullable|string|max:255',
            'connection_type' => 'required|in:usb,network',
        ]);

        return Printer::create($request->all());
    }

    public function show(Printer $printer)
    {
        return $printer;
    }

    public function update(Request $request, Printer $printer)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'brand'        => 'nullable|string|max:255',
            'model'        => 'nullable|string|max:255',
            'connection_type' => 'required|in:usb,network',
        ]);

        $printer->update($request->all());
        return $printer;
    }

    public function destroy(Printer $printer)
    {
        $printer->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
