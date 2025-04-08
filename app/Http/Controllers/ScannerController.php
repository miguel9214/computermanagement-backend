<?php

namespace App\Http\Controllers;

use App\Models\Scanner;
use Illuminate\Http\Request;

class ScannerController extends Controller
{
    public function index()
    {
        return Scanner::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'brand'          => 'nullable|string|max:255',
            'model'          => 'nullable|string|max:255',
            'connection_type' => 'required|in:usb,network',
        ]);

        return Scanner::create($request->all());
    }

    public function show(Scanner $scanner)
    {
        return $scanner;
    }

    public function update(Request $request, Scanner $scanner)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'brand'          => 'nullable|string|max:255',
            'model'          => 'nullable|string|max:255',
            'connection_type' => 'required|in:usb,network',
        ]);

        $scanner->update($request->all());
        return $scanner;
    }

    public function destroy(Scanner $scanner)
    {
        $scanner->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
