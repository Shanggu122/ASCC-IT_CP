<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;

trait HasProfilePhoto
{
    public function getProfilePhotoUrlAttribute(): string
    {
        $path = $this->profile_picture;
        if (!$path) {
            return asset('images/dprof.jpg');
        }
        // Always use public disk for profile photos
        $disk = 'public';
        if (Storage::disk($disk)->exists($path)) {
            return Storage::url($path);
        }
        // If the file check fails (maybe link not created yet) still attempt to build a URL
        return asset('storage/'.$path);
    }

    public function setProfilePictureAttribute($file)
    {
        // Allow assigning an UploadedFile directly
        if ($file instanceof \Illuminate\Http\UploadedFile) {
            $stored = $file->store('profile_pictures', 'public');
            $this->attributes['profile_picture'] = $stored; // stored as relative path
        } else {
            $this->attributes['profile_picture'] = $file; // assume relative path already
        }
    }
}
