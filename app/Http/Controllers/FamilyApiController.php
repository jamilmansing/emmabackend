<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\FamilyMember;
use Illuminate\Http\Request;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

class FamilyApiController extends Controller
{
    protected function getJoinUrl($familyId)
    {
        return env('APP_URL', 'http://localhost:8000') . "/api/join-family/{$familyId}";
    }

    /**
     * Generate QR code for a family
     */
     public function generateQRCode($familyId)
    {
        $family = Family::findOrFail($familyId);
        
        $builder = new Builder(
            writer: new PngWriter(),
            writerOptions: [],
            validateResult: false,
            data: $this->getJoinUrl($family->id),
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin
        );

        $result = $builder->build();
        
        return response()->json([
            'qrCodeImage' => base64_encode($result->getString()),
            'familyName' => $family->name,
            'familyId' => $family->id
        ]);
    }

    /**
     * Join a family using QR code
     */
    public function joinFamily(Request $request)
    {
        $validatedData = $request->validate([
            'qrCode' => 'required|string',
            'userId' => 'required|string',
        ]);
        
        // Validate if QR code contains a numeric family ID
        if (!ctype_digit($validatedData['qrCode'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid family ID format'
            ], 400);
        }

        $familyId = $validatedData['qrCode'];
        
        // Check if user is already in this family
        $existingMember = FamilyMember::where('family_id', $familyId)
            ->where('user_id', $validatedData['userId'])
            ->first();
            
        if ($existingMember) {
            return response()->json([
                'success' => true,
                'message' => 'You are already a member of this family',
                'alreadyInFamily' => true,
                'familyName' => $existingMember->family->name,
                'qrCodeUrl' => $this->generateQRCode($familyId)->original['qrCode']
            ], 200);
        }
        
        // Create new family member
        $family = Family::findOrFail($familyId);
        
        FamilyMember::create([
            'family_id' => $familyId,
            'user_id' => $validatedData['userId'],
            'is_head' => false,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Successfully joined family',
            'familyName' => $family->name,
            'familyId' => $familyId,
            'qrCodeUrl' => $this->generateQRCode($familyId)->original['qrCode']
        ]);
    }

    public function index()
    {
        $families = Family::all()->map(function ($family) {
            return [
                'id' => $family->id,
                'name' => $family->name,
                'description' => $family->description,
                'qrCodeUrl' => $this->generateQRCode($family->id)->original['qrCode']
            ];
        });
        
        return response()->json(['families' => $families]);
    }

      public function generateQRCodeImage($familyId)
    {
        $family = Family::findOrFail($familyId);
        
        $builder = new Builder(
            writer: new PngWriter(),
            writerOptions: [],
            validateResult: false,
            data: $this->getJoinUrl($family->id),
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin
        );

        $result = $builder->build();
        
        return response($result->getString())
            ->header('Content-Type', $result->getMimeType());
    }
}
