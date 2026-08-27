<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Content;
/**
 * Class ContractorController
 * @package App\Http\Controllers\Admin
 *
 * Handles user management functionalities in the admin panel.
 */
class ContentController extends Controller
{
    public function update(Request $request){
        if($request->id){
            $model = Content::find($request->id);
            if(!$model){
                $model = Content::where('key',$request->id)->first();
            }
        }
        return view('admin/content/update', compact('model'));
    }
    
    public function save(Request $request){
        return response()->json((new Content())->store($request->all()));
    }
}