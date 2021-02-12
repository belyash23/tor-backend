<?php

namespace App\Http\Controllers;

use Faker\Provider\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class Direction extends Controller
{

    public function get(Request $request) {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'status' => [Rule::in(['editing', 'published'])]
        ]);

        if($validator->fails()) {
            return response([
                'error' => [
                    'code' => 422,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors()
                ]
            ], 422);
        }

        $query = $request->get('query');
        $status = $request->get('status');

        $data = \App\Models\Direction::query();
        if($query) {
            $data = $data
                ->where('name', 'like', '%'.$query.'%')
                ->orWhere('description', 'like', '%'.$query.'%');
        }
        if($status) {
            $data = $data->where('status', $status);
        }
        $data = $data->with('images', 'keywords')->get();

        if($data->isEmpty()) {
            return response([
                'error' => [
                    'code' => 404,
                    'message' => 'Not Found'
                ]
            ], 404);
        }
        else {
            return response([
                'data' => $data
            ], 200);
        }
    }

    public function add(Request $request){
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'status' => [Rule::in(['editing', 'published'])]
        ]);

        if($validator->fails()) {
            return response([
                'error' => [
                    'code' => 422,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors()
                ]
            ], 422);
        }
        else {
//            $image_64 = $request->get('icon'); //your base64 encoded data
//            $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
//            $replace = substr($image_64, 0, strpos($image_64, ',')+1);
//            $image = str_replace($replace, '', $image_64);
//            $image = str_replace(' ', '+', $image);
//            $path = Str::random(10).'.'.$extension;
//            Storage::disk('public')->put($path, base64_decode($image));
//
//            $storagePath  = Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix();
//            $path = $storagePath.$path;


//            $file = $request->get('icon');
//            $folderName = 'public/uploads/';
//            $safeName = STR::random(10).'.'.'png';
//            $path = public_path() . $folderName;
//
//            file_put_contents(public_path().'/uploads/'.$safeName, $file);

            $data = $request->get('icon');
            $extension = explode('/', explode(':', substr($data, 0, strpos($data, ';')))[1])[1];
            $path = "direction-".time().'.'.$extension;
            $path = public_path().'/imgs/' . $path;

            \Intervention\Image\Facades\Image::make(file_get_contents($data))->save($path);



            \App\Models\Direction::query()->insert([
                'name' => $request->get('name'),
                'icon' => $path,
                'description' => $request->get('description'),
                'status' => $request->get('status'),
                'color' => $request->get('color')
            ]);

            return response(null, 201);
        }

    }
}
