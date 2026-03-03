<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\Ai\ImagenProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileAvatarController extends Controller
{
    public function __construct(
        protected ImagenProfileService $imagenProfileService
    ) {}

    /**
     * Upload a profile photo.
     */
    public function upload(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'],
        ]);

        $user = $request->user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store("avatars/{$user->id}", 'public');
        $user->avatar = $path;
        $user->save();

        if ($request->wantsJson()) {
            return response()->json([
                'avatar_url' => Storage::disk('public')->url($path),
            ]);
        }

        return back();
    }

    /**
     * Enhance the current profile photo with AI (professional headshot style).
     * Requires the user to have an avatar already uploaded.
     */
    public function enhance(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();

        if (! $user->avatar || ! Storage::disk('public')->exists($user->avatar)) {
            $message = 'Upload a photo first, then enhance it with AI.';
            if ($request->wantsJson()) {
                return response()->json(['error' => $message], 422);
            }

            return back()->with('error', $message);
        }

        $result = $this->imagenProfileService->enhanceProfilePicture(
            $user->avatar,
            $user->id
        );

        if (isset($result['error'])) {
            $code = $result['code'] ?? 500;
            if ($request->wantsJson()) {
                return response()->json(['error' => $result['error']], $code);
            }

            return back()->with('error', $result['error']);
        }

        Storage::disk('public')->delete($user->avatar);

        $user->avatar = $result['path'];
        $user->save();

        $avatarUrl = Storage::disk('public')->url($result['path']);

        if ($request->wantsJson()) {
            return response()->json(['avatar_url' => $avatarUrl]);
        }

        return back();
    }

    /**
     * Remove the profile photo.
     */
    public function destroy(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->avatar = null;
        $user->save();

        if ($request->wantsJson()) {
            return response()->json(['avatar_url' => null]);
        }

        return back();
    }
}
