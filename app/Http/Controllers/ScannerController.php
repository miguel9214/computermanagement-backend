<?php

namespace App\Http\Controllers;

use App\Models\Scanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScannerController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search');

        $query = Scanner::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $scanners = $query->paginate($perPage);

        return response()->json([
            'message' => 'Scanners retrieved successfully',
            'data'    => $scanners
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'brand'      => 'nullable|string|max:255',
            'model'      => 'nullable|string|max:255',
            'connection' => 'required|in:USB,IP,NONE',
        ]);

        $scanner = Scanner::create(array_merge(
            $request->only(['name','brand','model','connection']),
            ['created_by_user' => Auth::id(), 'updated_by_user' => Auth::id()]
        ));

        return response()->json([
            'message' => 'Scanner created successfully',
            'data'    => $scanner
        ], 201);
    }

    public function show(Scanner $scanner)
    {
        return response()->json([
            'message' => 'Scanner retrieved successfully',
            'data'    => $scanner
        ], 200);
    }

    public function update(Request $request, Scanner $scanner)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'brand'      => 'nullable|string|max:255',
            'model'      => 'nullable|string|max:255',
            'connection' => 'required|in:USB,IP,NONE',
        ]);

        $scanner->update(array_merge(
            $request->only(['name','brand','model','connection']),
            ['updated_by_user' => Auth::id()]
        ));

        return response()->json([
            'message' => 'Scanner updated successfully',
            'data'    => $scanner
        ], 200);
    }

    public function destroy(Scanner $scanner)
    {
        $scanner->delete();

        return response()->json([
            'message' => 'Scanner deleted successfully'
        ], 200);
    }
}
