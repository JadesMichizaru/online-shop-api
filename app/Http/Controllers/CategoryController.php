<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Categories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;

class CategoryController extends Controller
{
    /**
     * 
     * Show all the category
     */

     public function index() {
        $categories = Categories::all();

        if($categories->isEmpty()) {
            return response()->json([
                'message' => 'Data is Not Found!'
            ], 404);
        }
        return response()->json(CategoryResource::collection($categories), 200);
     }

     /**
      * 
      * Saving new category Data to DB
      */

      public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid Field',
                'errors' => $validator->errors()
            ], 404);
        }

        $categories = Categories::create([
            'name' => $request->name,
            'description' => $request->description
        ]);

        return response()->json(new CategoryResource($categories), 201);
      }

      public function show ($id) {
        $categories = Categories::find($id);

        if($categories == null) {
            return response()->json([
                'message' => 'Resource Not Found'
            ], 404);
        }
        
        return response()->json(new CategoryResource($categories), 200);
      }

      public function update(Request $request, $id) {
        $categories = Categories::findOrFail($id);

        if (!$categories) {
            return response()->json([
                'message' => 'Data is Not Found!'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string',
            'description' => 'sometimes|string'
        ]);

        $categories->update([
            'name' => $request->name,
            'description' => $request->description
        ]);

        return response()->json(new CategoryResource($categories), 200);
      }

      public function delete($id) {
        $categories = Categories::findOrFail($id);

        if(!$categories) {
            return response()->json([
                'message' => 'Data is Not Found!'
            ], 404);
        }

        $categories->delete();

        return response()->json([
            'message' => 'Data deleted successfully'
        ], 200);
      }

}
