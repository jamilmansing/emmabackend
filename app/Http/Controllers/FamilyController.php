<?php

namespace App\Http\Controllers;

use App\Models\Family;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

class FamilyController extends Controller
{

    /**
     * Display a listing of all families
     */
    public function index()
    {
        $families = Family::all();
        return view('families.index', ['families' => $families]);
    }

    /**
     * Show the form for creating a new family
     */
    public function create()
    {
        return view('families.create');
    }

    /**
     * Store a newly created family in storage
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $family = Family::create([
            'name' => $validatedData['name'],
            'description' => $validatedData['description'],
        ]);

        return redirect()->route('families.index')->with('success', 'Family created successfully!');
    }

    /**
     * Display a specific family
     */
    public function show(Family $family)
    {
        return view('families.show', ['family' => $family]);
    }

    /**
     * Show the form for editing a family
     */
    public function edit(Family $family)
    {
        return view('families.edit', ['family' => $family]);
    }

    /**
     * Update a family's information
     */
    public function update(Request $request, Family $family)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $family->update($validatedData);

        return redirect()->route('families.index')->with('success', 'Family updated successfully!');
    }

    /**
     * Delete a family
     */
    public function destroy(Family $family)
    {
        $family->delete();
        return redirect()->route('families.index')->with('success', 'Family deleted successfully!');
    }

    /**
     * Display QR code for a family
     */
    public function qr(Family $family)
    {
    $builder = new Builder(
        writer: new PngWriter(),
        writerOptions: [],
        validateResult: false,
        data: route('api.family.qr', ['familyId' => $family->id]),
        encoding: new Encoding('UTF-8'),
        errorCorrectionLevel: ErrorCorrectionLevel::High,
        size: 300,
        margin: 10,
        roundBlockSizeMode: RoundBlockSizeMode::Margin
    );

    $result = $builder->build();
    
    // Return the image directly with proper headers
    return response($result->getString())
        ->header('Content-Type', $result->getMimeType());
    }
}
