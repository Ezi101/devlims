<?php

namespace App\Http\Controllers;

use App\Methods;
use App\Product;
use App\Helpers\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MethodsController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('methods.view') && !auth()->user()->can('methods.create')) {
            abort(403, 'Unauthorized action.');
        }


        $methods = Methods::where('business_id', Auth::user()->business_id)->get();
        return view('methods.index', compact('methods'));
    }
    public function create()
    {
        if (!auth()->user()->can('methods.create')) {
            abort(403, 'Unauthorized action.');
        }
        $samples = Product::where('business_id', Auth::user()->business_id)->where('product_type', 'sample') ->groupBy('name')->get();

        return view('methods.create', compact('samples'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('methods.create')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $validatedData = $request->validate([
                'sample_id' => 'required|exists:products,id',
                'method_name' => 'required|string|max:255',
                'method_description' => 'nullable|string',
                'method_files.*' => 'nullable|file|max:10240',
                'picture' => 'nullable|file|image|max:10240'
            ]);

            $method = new Methods();
            $method->business_id = Auth::user()->business_id;
            $method->created_by = Auth::user()->id;
            $method->sample_id = $validatedData['sample_id'];
            $method->method_name = $validatedData['method_name'];
            $method->method_description = $validatedData['method_description'];

            $files = [];

            if ($request->hasFile('method_files')) {
                foreach ($request->file('method_files') as $file) {
                    // Check if the file is an image
                    if ($this->isImage($file)) {
                        $fileName = time() . '_' . $file->getClientOriginalName();
                        // Compress and store the method file
                        $this->compressImage($file, public_path('uploads/img/' . $fileName));
                        $files[] = $fileName;
                    } else {
                        // Store the file as is
                        $fileName = time() . '_' . $file->getClientOriginalName();
                        $file->move(public_path('uploads/img/'), $fileName); // Save to a different directory
                        $files[] = $fileName;
                    }
                }
            }

            if ($request->hasFile('picture')) {
                $picture = $request->file('picture');
                $pictureName = time() . '_' . $picture->getClientOriginalName();
                // Compress and store the picture
                $this->compressImage($picture, public_path('uploads/img/' . $pictureName));
                $files[] = $pictureName;
            }

            $method->files = json_encode($files);
            $method->save();

            $randomNumber = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $methodNumber = 'MN-' . ($request->sample_id ?? $randomNumber) . '-' . $method->id;
            $method->method_no = $methodNumber;
            $method->save();

            $sample_name = Product::where('id', $method->sample_id)->pluck('name')->first();

            AuditLogger::log('created', 'Method', 'Method ID: ' . $method->id . ' & Method Name: ' . $method->method_name);
            AuditLogger::log('sampleused', 'Method', 'Sample ID: ' . $method->sample_id . ' (' . $sample_name . ') was linked to a method having method ID: ' . $method->id);

            return redirect()->route('methods.index')->with('status', ['success' => 1, 'msg' => __('method.method_created')]);
        } catch (\Exception $e) {
            return redirect()->route('methods.index')->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }



    public function edit(Methods $method)
    {
        if (!auth()->user()->can('methods.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $samples = Product::where('business_id', Auth::user()->business_id)->where('product_type', 'sample') ->groupBy('name')->get();

        return view('methods.edit', compact('method', 'samples'));
    }
    public function update(Request $request, Methods $method)
    {
        if (!auth()->user()->can('methods.edit')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $validatedData = $request->validate([
                'sample_id' => 'required|exists:products,id',
                'method_name' => 'required|string|max:255',
                'method_description' => 'nullable|string',
                'method_files.*' => 'nullable|file|max:10240',
            ]);

            // Capture the old values
            $oldValues = $method->only(['sample_id', 'method_name', 'method_description']);

            // Update the method details
            $method->update([
                'sample_id' => $validatedData['sample_id'],
                'method_name' => $validatedData['method_name'],
                'method_description' => $validatedData['method_description'],
            ]);

            // Initialize the array for new files
            $files = [];
            $existingFiles = json_decode($method->files, true) ?? [];

            // Handle method files
            if ($request->hasFile('method_files')) {
                foreach ($request->file('method_files') as $file) {
                    // Generate a unique file name
                    $fileName = time() . '_' . $file->getClientOriginalName();

                    // Check if the file is an image
                    if ($this->isImage($file)) {
                        // Compress and store the method file
                        $this->compressImage($file, public_path('uploads/img/' . $fileName));
                    } else {
                        // Store the file as is
                        $file->move(public_path('uploads/img/'), $fileName); // Save to a different directory
                    }

                    // Add the new file to the array
                    $files[] = $fileName;
                }
            }

            // Handle picture upload
            if ($request->hasFile('picture')) {
                $picture = $request->file('picture');
                $pictureName = time() . '_' . $picture->getClientOriginalName();
                // Compress and store the picture
                $this->compressImage($picture, public_path('uploads/img/' . $pictureName));
                $files[] = $pictureName; // Add the picture to the files array
            }

            // Merge the new files with the existing files, avoiding duplicates
            $files = array_merge($existingFiles, $files);
            $method->files = json_encode($files);
            $method->save();

            // Capture the new values
            $newValues = $method->only(['sample_id', 'method_name', 'method_description']);
            $fieldNames = [
                'sample_id' => 'Sample ID',
                'method_name' => 'Name',
                'method_description' => 'Description',
            ];

            // Prepare the log details
            $changes = [];
            foreach ($newValues as $key => $newValue) {
                if ($oldValues[$key] != $newValue) {
                    $fieldName = $fieldNames[$key] ?? ucfirst($key); // Use friendly name or default to key
                    $changes[] = " <b>{$fieldName}:</b> from <b> '{$oldValues[$key]}' </b>to <b>'{$newValue}'</b>";
                }
            }
            $changesDetails = implode(' | ', $changes);
            $logMessage = "<b>Method ID: " . $method->id . "</b> was <b>updated:</b><br>" . $changesDetails;

            // Log the update action
            AuditLogger::log('updated', 'Method', $logMessage);

            return redirect()->route('methods.index')->with('status', ['success' => 1, 'msg' => __('method.method_updated')]);
        } catch (\Exception $e) {
            // dd($e);
            return redirect()->route('methods.index')->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }



    /**
     * Check if the uploaded file is an image.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return bool
     */
    protected function isImage($file)
    {
        return in_array($file->getClientMimeType(), [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/bmp',
            'image/svg+xml'
        ]);
    }

    /**
     * Compress an image and save it to the specified path.
     *
     * @param \Illuminate\Http\UploadedFile $image
     * @param string $destinationPath
     * @return void
     */
    protected function compressImage($image, $destinationPath)
    {
        // Load the image
        $sourceImage = imagecreatefromstring(file_get_contents($image->getPathname()));
        if (!$sourceImage) {
            throw new \Exception('Could not create image from file.');
        }

        // Get original dimensions
        $width = imagesx($sourceImage);
        $height = imagesy($sourceImage);

        // Set new dimensions
        $newWidth = 850; // Desired width
        $newHeight = 750; // Desired height

        // Create a new true color image
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

        // Resize the image
        imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Save the compressed image as a JPEG file with specified quality
        imagejpeg($resizedImage, $destinationPath, 90); // 50% quality

        // Free up memory
        imagedestroy($sourceImage);
        imagedestroy($resizedImage);
    }



    public function show(Methods $method)
    {
        return view('methods.show', compact('method'));
    }
}
