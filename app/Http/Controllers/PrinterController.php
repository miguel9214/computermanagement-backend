<?php

namespace App\Http\Controllers;

use App\Models\Printer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Exception;

class PrinterController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search');

        $query = Printer::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%");
        }

        $printers = $query->paginate($perPage);

        return response()->json([
            'message' => 'Printers retrieved successfully',
            'data'    => $printers
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'brand'      => 'nullable|string|max:255',
            'model'      => 'nullable|string|max:255',
            'connection' => 'required|in:USB,IP,NONE',
            'ip'         => 'nullable|ip',
            'mac'        => 'nullable|string|max:255',
        ]);

        $data = $request->only(['name','brand','model','connection','ip','mac']);
        $data['created_by_user'] = Auth::id();
        $data['updated_by_user'] = Auth::id();

        $printer = Printer::create($data);

        return response()->json([
            'message' => 'Printer created successfully',
            'data'    => $printer
        ], 201);
    }

    public function show(Printer $printer): JsonResponse
    {
        return response()->json([
            'message' => 'Printer retrieved successfully',
            'data'    => $printer
        ], 200);
    }

    public function update(Request $request, Printer $printer): JsonResponse
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'brand'      => 'nullable|string|max:255',
            'model'      => 'nullable|string|max:255',
            'connection' => 'required|in:USB,IP,NONE',
            'ip'         => 'nullable|ip',
            'mac'        => 'nullable|string|max:255',
        ]);

        $data = $request->only(['name','brand','model','connection','ip','mac']);
        $data['updated_by_user'] = Auth::id();

        $printer->update($data);

        return response()->json([
            'message' => 'Printer updated successfully',
            'data'    => $printer
        ], 200);
    }

    public function destroy(Printer $printer): JsonResponse
    {
        $printer->delete();

        return response()->json([
            'message' => 'Printer deleted successfully'
        ], 200);
    }
}
