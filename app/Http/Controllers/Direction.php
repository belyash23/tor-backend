<?php

namespace App\Http\Controllers;

use App\Models\DirectionImage;
use App\Models\DirectionKeyword;
use Faker\Provider\Image;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
            $data = $request->get('icon');
            $path = null;
            if($data) {
                $extension = explode('/', explode(':', substr($data, 0, strpos($data, ';')))[1])[1];
                $name = "direction-icon-".time().'.'.$extension;
                $path = public_path().'/imgs/'.$name;
                \Intervention\Image\Facades\Image::make(file_get_contents($data))->save($path);
                $path = '/imgs/'.$name;
            }

            $direction = \App\Models\Direction::create([
                'name' => $request->get('name'),
                'icon' => $path,
                'description' => $request->get('description'),
                'status' => $request->get('status'),
                'color' => $request->get('color')
            ]);

            $keywords = $request->get('keywords');
            if($keywords) {
                foreach($keywords as $keyword) {
                    $direction->keywords()->create([
                        'word' => $keyword['word']
                    ]);
                }
            }

            $images = $request->get('images');
            if($images) {
                foreach($images as $image) {
                    $data = $image['src'];
                    $extension = explode('/', explode(':', substr($data, 0, strpos($data, ';')))[1])[1];
                    $name = "direction-image-".time().'.'.$extension;
                    $path = public_path().'/imgs/'.$name;
                    \Intervention\Image\Facades\Image::make(file_get_contents($data))->save($path);
                    $path = '/imgs/'.$name;

                    $direction->images()->create([
                        'src' => $path
                    ]);
                }
            }

            return response(null, 201);
        }

    }

    public function edit(Request $request, $id) {
        $validator = \Illuminate\Support\Facades\Validator::make([
            'status' => $request->get('status'),
            'id' => $id
        ],[
            'status' => [Rule::in(['editing', 'published'])],
            'id' => 'integer|required'
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

        $data = \App\Models\Direction::query();
        $data = $data->where('id', $id);
        $data = $data->with('images', 'keywords')->get();

        if($data->isEmpty()) {
            return response([
                'error' => [
                    'code' => 404,
                    'message' => 'Not Found'
                ]
            ], 404);
        }

        if($data[0]['status'] == 'published' && $request->get('status') != 'editing') {
            return response([
                'error' => [
                    'code' => 422,
                    'message' => 'Данные можно менять только в том случае, если статус принимает значение "editing"'
                ]
            ]);
        }

        $data = $request->get('icon');
        $path = null;
        if($data) {
            $extension = explode('/', explode(':', substr($data, 0, strpos($data, ';')))[1])[1];
            $name = "direction-icon-".time().'.'.$extension;
            $path = public_path().'/imgs/'.$name;
            \Intervention\Image\Facades\Image::make(file_get_contents($data))->save($path);
            $path = '/imgs/'.$name;
        }

        \App\Models\Direction::where('id', $id)->update([
            'name' => $request->get('name'),
            'icon' => $path,
            'description' => $request->get('description'),
            'status' => $request->get('status'),
            'color' => $request->get('color')
        ]);

        DirectionKeyword::where('direction_id', $id)->delete();
        $direction = \App\Models\Direction::find($id);
        $keywords = $request->get('keywords');
        if($keywords) {
            foreach($keywords as $keyword) {
                $direction->keywords()->create([
                    'word' => $keyword['word']
                ]);
            }
        }

        DirectionImage::where('direction_id', $id)->delete();
        $images = $request->get('images');
        if($images) {
            foreach($images as $image) {
                $data = $image['src'];
                $extension = explode('/', explode(':', substr($data, 0, strpos($data, ';')))[1])[1];
                $name = "direction-image-".time().'.'.$extension;
                $path = public_path().'/imgs/'.$name;
                \Intervention\Image\Facades\Image::make(file_get_contents($data))->save($path);
                $path = '/imgs/'.$name;

                $direction->images()->create([
                    'src' => $path
                ]);
            }
        }

        return response(null, 201);


    }
}
