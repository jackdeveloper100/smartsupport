<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Page;
use Illuminate\Support\Facades\Validator;

class PageController extends Controller
{
    /**
     * Display the admin page index view.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $sessionUser = auth()->user();
        if($sessionUser->hasPermission('admin/page')){
            return view('admin/page/index');
        }else{
            return redirect('admin/dashboard')->with('error', 'You are Not Authorized');
        }
    }

    /**
     * Retrieve a list of pages for admin.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        return response()->json((new Page())->listAdmin($request->all()));
    }
    
    /**
     * Show the form for updating the specified page.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function update(Request $request)
    {
        $model = Page::find($request->input('id'));

        // Redirect if not found
        if (!$model) {
            return redirect()->route('admin.page.index')->withErrors(['error' => 'No data found']);
        }
        

        return view('admin/page/update', compact('model'));
    }
    
    /**
     * Save the page details.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request)
    {
        return response()->json((new Page())->store($request->only(['id','title','body','slug'])));
    }
    
    /**
     * Handle the file upload for the page.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function saveFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'upload' => 'required|'.$this->general->fileRules(),
        ]);
        
        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }
        
        $fileName = $this->general->uploadFile($request->file('upload'), 'content');
        // dd($fileName);  
        return response()->json(['status'=>1,'fileName' => $fileName,'url' => $this->general->getFileUrl($fileName['file_name'], 'content')]);
    }
}
