<?php
namespace App\Http\Controllers;

use App\Models\Dependency;
use Illuminate\Http\Request;

class DependencyController extends Controller
{
    public function index()
    {
        return Dependency::all();
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        return Dependency::create($request->only('name'));
    }

    public function show(Dependency $dependency)
    {
        return $dependency;
    }

    public function update(Request $request, Dependency $dependency)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $dependency->update($request->only('name'));
        return $dependency;
    }

    public function destroy(Dependency $dependency)
    {
        $dependency->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
