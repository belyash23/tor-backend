<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use function PHPUnit\Framework\isEmpty;

class Resource extends Controller
{
    public function get(Request $request) {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'status' => [Rule::in(['parsed', 'editing', 'published'])]
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

        $data = \App\Models\Resource::query();
        if($query) {
            $data = $data
                ->where('name', 'like', '%'.$query.'%')
                ->orWhere('short_description', 'like', '%'.$query.'%')
                ->orWhere('description', 'like', '%'.$query.'%');
        }
        if($status) {
            $data = $data->where('status', $status);
        }
        $data = $data->get();

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
            'name' => 'required',
            'status' => [Rule::in(['editing', 'published', 'parsed'])]
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

        $data = $request->get('image');
        $path = null;
        if($data) {
            $extension = explode('/', explode(':', substr($data, 0, strpos($data, ';')))[1])[1];
            $name = "resource-".time().'.'.$extension;
            $path = public_path().'/imgs/'.$name;
            \Intervention\Image\Facades\Image::make(file_get_contents($data))->save($path);
            $path = '/imgs/'.$name;
        }
        elseif($request->get('category_id')) {
            $categoryId = $request->get('category_id');
            $path = \App\Models\Category::where('id', $categoryId)->pluck('icon')->first();
            if(empty($path)) {
                $path = null;
            }
        }

        $shortDescription = $request->get('short_description');
        $directionId = null;
        if($request->get('direction_id')) {
            $directionId = join(' ', $request->get('direction_id'));

        }

        $status = $request->get('status') ? $request->get('status'): 'editing';
        $dateStart = $request->get('date_start') ? date('Y-m-d', strtotime($request->get('date_start'))): null;
        $dateEnd = $request->get('date_end') ? date('Y-m-d', strtotime($request->get('date_end'))): null;

        \App\Models\Resource::create([
            'name' => $request->get('name'),
            'short_description' => $shortDescription,
            'description' => $request->get('description'),
            'date_start' => $dateStart,
            'date_end' => $dateEnd,
            'age_min' => $request->get('age_min'),
            'age_max' => $request->get('age_max'),
            'location' => $request->get('location'),
            'website' => $request->get('website'),
            'image' => $path,
            'status' => $status,
            'category_id' => $request->get('category_id'),
            'direction_id' => $directionId
        ]);
        
        return response(null, 204);
    }

    public function edit(Request $request, $id) {
        $validator = \Illuminate\Support\Facades\Validator::make([
            'status' => $request->get('status'),
            'id' => $id
        ],[
            'status' => [Rule::in(['editing', 'published', 'parsed', null])],
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

        $data = \App\Models\Resource::query();
        $data = $data->where('id', $id)->get();

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

        $data = $request->get('image');
        $path = null;
        if(!empty($data)) {
            $extension = explode('/', explode(':', substr($data, 0, strpos($data, ';')))[1])[1];
            $name = "resource-".time().'.'.$extension;
            $path = public_path().'/imgs/'.$name;
            \Intervention\Image\Facades\Image::make(file_get_contents($data))->save($path);
            $path = '/imgs/'.$name;
        }
        elseif(!is_null($data)) {
            $categoryId = \App\Models\Resource::query()->where('id', $id)->pluck('category_id')->first();
            $currentImage = \App\Models\Resource::where('id', $id)->pluck('image')->first();
            if($currentImage) unlink(public_path($currentImage));

            $path = \App\Models\Category::where('id', $categoryId)->pluck('icon')->first();
            if(empty($path)) {
                $path = "";
            }
        }

        $shortDescription = $request->get('short_description');
        $directionId = null;
        if($request->get('direction_id')) {
            $directionId = join(' ', $request->get('direction_id'));

        }

        $status = $request->get('status') ? $request->get('status'): 'editing';
        $dateStart = $request->get('date_start') ? date('Y-m-d', strtotime($request->get('date_start'))): null;
        $dateEnd = $request->get('date_end') ? date('Y-m-d', strtotime($request->get('date_end'))): null;

        $input = collect([
            'name' => $request->get('name'),
            'short_description' => $shortDescription,
            'description' => $request->get('description'),
            'date_start' => $dateStart,
            'date_end' => $dateEnd,
            'age_min' => $request->get('age_min'),
            'age_max' => $request->get('age_max'),
            'location' => $request->get('location'),
            'website' => $request->get('website'),
            'image' => $path,
            'status' => $status,
            'category_id' => $request->get('category_id'),
            'direction_id' => $directionId
        ])->filter(function($value) {
            return ! is_null($value);
        })->map(function($item) {
            if(empty($item)) {
                return null;
            }
            else {
                return $item;
            }
        })->all();

        if($input) {
            \App\Models\Resource::where('id', $id)->update($input);
        }

        return response(null, 204);
    }

    public function delete($id) {
        $validator = \Illuminate\Support\Facades\Validator::make([
            'id' => $id
        ],[
            'id' => 'integer|required'
        ]);

        $data = \App\Models\Resource::query();
        $data = $data->where('id', $id)->get();

        if($data->isEmpty()) {
            return response([
                'error' => [
                    'code' => 404,
                    'message' => 'Not Found'
                ]
            ], 404);
        }

        if($validator->fails()) {
            return response([
                'error' => [
                    'code' => 422,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors()
                ]
            ], 422);
        }

        $resource = \App\Models\Resource::find($id);
        $resource->delete();
        return response(null, 204);
    }
}
