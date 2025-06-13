<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\FamilyMember;
use Illuminate\Http\Request;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\ErrorCorrectionLevel;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
        try {
            $validated = $request->validate([
                'qrCode' => 'required|exists:families,id',
                'userId' => 'required|exists:users,id'
            ]);

            // Check if user is already a member
            $existingMember = FamilyMember::where('family_id', $validated['qrCode'])
                ->where('user_id', $validated['userId'])
                ->first();

            if ($existingMember) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is already a member of this family'
                ], 400);
            }

            // Create new family member
            FamilyMember::create([
                'family_id' => $validated['qrCode'],
                'user_id' => $validated['userId'],
                'is_head' => false // New members are not heads by default
            ]);

            // Return updated family info
            $family = Family::with(['members.user'])->findOrFail($validated['qrCode']);
            
            return response()->json([
                'success' => true,
                'familyName' => $family->name,
                'members' => $family->members->map(function ($member) {
                    return [
                        'name' => $member->user->name,
                        'type' => $member->is_head ? 'Head of Family' : 'Member'
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to join family: ' . $e->getMessage()
            ], 500);
        }
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

    public function show($familyId)
    {
        try {
            // Find the family
            $family = Family::with(['members.user'])->findOrFail($familyId);
            
            // Format members data
            $members = $family->members->map(function ($member) {
                return [
                    'name' => $member->user->name,
                    'type' => $member->is_head ? 'Head of Family' : 'Member',
                    // Add any other user info you need
                ];
            });

            return response()->json([
                'success' => true,
                'familyName' => $family->name,
                'description' => $family->description,
                'members' => $members
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Family not found'
            ], 404);
        }
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
