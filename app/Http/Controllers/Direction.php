<?php

namespace App\Http\Controllers;

use App\Models\DirectionImage;
use App\Models\DirectionKeyword;
use Illuminate\Http\Request;
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
        $id = $request->get('id');

        $data = \App\Models\Direction::query();
        if($query) {
            $data = $data
                ->where('name', 'like', '%'.$query.'%')
                ->orWhere('description', 'like', '%'.$query.'%');
        }
        if($status) {
            $data = $data->where('status', $status);
        }
        if($id) {
            $data = $data->where('id', $id);
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
        return response([
            'data' => $data
        ], 200);
    }

    public function add(Request $request){
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'name' => 'required',
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
        $data = $request->get('icon');
        $path = null;
        if($data) {
            $extension = explode('/', explode(':', substr($data, 0, strpos($data, ';')))[1])[1];
            $name = "direction-icon-".time().'.'.$extension;
            $path = public_path().'/imgs/'.$name;
            \Intervention\Image\Facades\Image::make(file_get_contents($data))->save($path);
            $path = '/imgs/'.$name;
        }

        $status = $request->get('status') ? $request->get('status'): 'editing';
        $direction = \App\Models\Direction::create([
            'name' => $request->get('name'),
            'icon' => $path,
            'description' => $request->get('description'),
            'status' => $status,
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

        return response(null, 204);

    }

    public function edit(Request $request, $id) {
        $validator = \Illuminate\Support\Facades\Validator::make([
            'status' => $request->get('status'),
            'id' => $id
        ],[
            'status' => [Rule::in(['editing', 'published', null])],
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

        if(!empty($data)) {
            $currentIcon = \App\Models\Direction::where('id', $id)->pluck('icon')->first();
            if($currentIcon) unlink(public_path($currentIcon));

            $extension = explode('/', explode(':', substr($data, 0, strpos($data, ';')))[1])[1];
            $name = "direction-icon-".time().'.'.$extension;
            $path = public_path().'/imgs/'.$name;
            \Intervention\Image\Facades\Image::make(file_get_contents($data))->save($path);
            $path = '/imgs/'.$name;
        }
        elseif(!is_null($data)) {
            $path = "";
            $currentIcon = \App\Models\Direction::where('id', $id)->pluck('icon')->first();
            if($currentIcon) unlink(public_path($currentIcon));
        }

        $input = collect([
            'name' => $request->get('name'),
            'icon' => $path,
            'description' => $request->get('description'),
            'status' => $request->get('status'),
            'color' => $request->get('color')
        ])->filter(function($value) {
            return ! is_null($value);
        })->all();

        if($input) {
            \App\Models\Direction::where('id', $id)->update($input);
        }

        $direction = \App\Models\Direction::find($id);

        $keywords = $request->get('keywords');
        if($keywords) {
            DirectionKeyword::where('direction_id', $id)->delete();
            foreach($keywords as $keyword) {
                $direction->keywords()->create([
                    'word' => $keyword['word']
                ]);
            }
        }

        $images = $request->get('images');
        if($images) {
            $image = DirectionImage::where('direction_id', $id);
            if(!$image->get()->isEmpty()) unlink(public_path($image->pluck('src')->first()));

            $image->delete();
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

        return response(null, 204);


    }

    public function delete($id) {
        $validator = \Illuminate\Support\Facades\Validator::make([
            'id' => $id
        ],[
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
        $data = $data->where('id', $id)->get();

        if($data->isEmpty()) {
            return response([
                'error' => [
                    'code' => 404,
                    'message' => 'Not Found'
                ]
            ], 404);
        }

        $direction = \App\Models\Direction::find($id);
        $direction->delete();
        $direction->images()->delete();
        $direction->keywords()->delete();
        $direction->resources()->detach();
        return response(null, 204);
    }
}
