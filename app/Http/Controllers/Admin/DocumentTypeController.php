<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class DocumentTypeController extends Controller
{
    public function index(){
        return view('admin/setting/document_type/index');
    }
    
    public function list(Request $request){
        return response()->json((new DocumentType())->list($request->all()));
    }
    
    public function create(){
        return view('admin/setting/document_type/create');
    }
    
    public function update(Request $request){
        $id = $request->id;
        $model = DocumentType::find($id);
        if (!$model) {
            return response()->json(['status' => 0, 'message' => 'No data found']);
        }
        return view('admin/setting/document_type/update',compact('model'));
    }
    
    public function save(Request $request){
        return response()->json((new DocumentType())->store($request->all()));
    }
    
    public function delete(Request $request){
        $id = $request->id;
        $model = DocumentType::find($id);
        
        if (!$model) {
            return response()->json(['status' => 0, 'message' => 'No data found']);
        }
        
        $model->is_hidden = 1;
        $model->save();
        
        return response()->json(['status'=>1,'message'=>'Document Type Removed Successfully','next'=>'table_refresh']);

    }
}