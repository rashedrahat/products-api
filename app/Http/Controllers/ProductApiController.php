<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Product;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

class ProductApiController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->user
            ->products()
            ->orderBy('id', 'DESC')
            ->get(['id', 'title', 'description', 'price', 'image_name'])
            ->toArray();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'price' => 'required|numeric|between:0,999999999999.99',
            'image' => 'required|mimes:jpeg,png,jpg,gif,svg'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        } else {
            $result = $this->storeImage($request);

            if ($result['success']) {
                $product = new Product();
                $product->title = $request->title;
                $product->description = $request->description;
                $product->price = $request->price;
                $product->image_name = $result['filename'];

                if ($this->user->products()->save($product)) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Added successfully',
                        'data' => $product
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sorry, product could not be added'
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Sorry, something went wrong'
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }

    /**
     * Prepares a image for storing.
     *
     * @param mixed $request
     * @return bool
     */
    public function storeImage($request)
    {
        // Get file from request
        $file = $request->file('image');

        // Get filename with extension
        $filenameWithExt = $file->getClientOriginalName();

        // Get file path
        $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);

        // Remove unwanted characters
        $filename = preg_replace("/[^A-Za-z0-9 ]/", '', $filename);
        $filename = preg_replace("/\s+/", '-', $filename);

        // Get the original image extension
        $extension = $file->getClientOriginalExtension();

        // Create unique file name
        $fileNameToStore = $filename . '_' . time() . '.' . $extension;

        // Refer image to method resizeImage
        $save = $this->resizeImage($file, $fileNameToStore);

        if ($save) {
            return [
                'success' => true,
                'filename' => $fileNameToStore
            ];
        }
        return [
            'success' => false,
            'filename' => null
        ];
    }

    /**
     * Resizes a image using the InterventionImage package.
     *
     * @param object $file
     * @param string $fileNameToStore
     * @return bool
     */
    public function resizeImage($file, $fileNameToStore)
    {
        // Resize image
        $resize = Image::make($file)->resize(600, null, function ($constraint) {
            $constraint->aspectRatio();
        })->encode('jpg');

        // Put image to storage
        $save = Storage::put("public/images/{$fileNameToStore}", $resize->__toString());

        if ($save) {
            return true;
        }
        return false;
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = $this->user->products()->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, product with id ' . $id . ' cannot be found'
            ], RESPONSE::HTTP_BAD_REQUEST);
        } else {
            return response()->json([
                'success' => true,
                'data' => $product
            ], RESPONSE::HTTP_OK);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->show($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validation_fields = [
            'title' => 'required',
            'description' => 'required',
            'price' => 'required|numeric|between:0,999999999999.99'
        ];

        if ($request->hasFile('image')) {
            $validation_fields['image'] = 'required|mimes:jpeg,png,jpg,gif,svg';
        }

        $validator = Validator::make($request->all(), $validation_fields);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        } else {
            $product = $this->user->products()->find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sorry, product with id ' . $id . ' cannot be found'
                ], RESPONSE::HTTP_BAD_REQUEST);
            } else {
                $updated_data = [
                    'title' => $request->title,
                    'description' => $request->description,
                    'price' => $request->price,
                ];

                if ($request->hasFile('image')) {
                    $result = $this->storeImage($request);

                    if ($result['success']) {
                        $updated_data['image_name'] = $result['filename'];
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'Sorry, something went wrong'
                        ], RESPONSE::HTTP_INTERNAL_SERVER_ERROR);
                    }
                }

                $updated = $product->fill($updated_data)
                    ->save();

                if ($updated) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Updated successfully',
                        'data' => $updated_data
                    ], RESPONSE::HTTP_OK);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sorry, product could not be updated'
                    ], RESPONSE::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = $this->user->products()->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, product with id ' . $id . ' cannot be found'
            ], RESPONSE::HTTP_BAD_REQUEST);
        }

        if ($product->delete()) {
            unlink(storage_path('app/public/images/' . $product->image_name));
            return response()->json([
                'success' => true,
                'message' => 'Deleted successfully'
            ], RESPONSE::HTTP_OK);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Product could not be deleted'
            ], RESPONSE::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
