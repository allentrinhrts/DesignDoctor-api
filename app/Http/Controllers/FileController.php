<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use OpenAI\Contracts\Resources\CompletionsContract;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return File::orderBy('created_at', 'desc')->get();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'file' => 'required|file|mimes:jpeg,png|max:4096',
        ]);

        $date = date('Y-m-d_H:i:s');

        // Store the file
        $file = $request->file('file');
        $originalFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $filename = $originalFileName . '_' . $date . '.' . $extension;
        $path = $file->storeAs('uploads', $filename, 'public'); // 'local' is the disk name

        $model = File::create([
            'name' => $filename,
            'location' => $path,
        ]);

        return response()->json(['message' => 'File uploaded successfully', 'file' => $model], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $file = File::findOrFail($id);
        return response()->json(['file' => $file], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(File $file)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, File $file)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(File $file)
    {
        //
    }
}
