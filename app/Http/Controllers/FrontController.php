<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Page;
use App\Models\Content;
use App\Models\Document;
use App\Models\User;
use App\Models\Email_Template;

class FrontController extends Controller
{
    /**
     * Display the front index page.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {   
    
        if (!session()->has('theme')) {
            session(['theme' => 'light']);
        }
            
      $sessionUser = auth()->user();
      
      if(isset($sessionUser)){
          if($sessionUser->isAdmin() && !$sessionUser->hasPermission('admin_home_page')){
             return redirect('admin/dashboard')->with('error', 'You are not authorized');
          }
      }
    
        // dd($totalContractors);
        $contentModel = new Content();
		$contentModel->setPage('home');
        // return view('landing_page.index',compact('contentModel'));
        return redirect('login');    
    }

    /**
     * Display a specific page based on the slug.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function page(Request $request)
    {
        
        $user = auth()->user();
        $page = Page::where('slug', $request->slug)->firstOrFail();
        if(isset($user) && $user->type == 0){
            return view('front.page_admin', compact('page','user'));
        }else{
            return view('front.page', compact('page'));
        }
    }

    public function email_template(Request $request)
    {
        $email = Email_Template::where('title', $request->title)->firstOrFail();
        return view('front.email_template', compact('email'));
    }

    /**
     * Display the contact page.
     *
     * @return \Illuminate\View\View
     */
    public function contact()
    {
        return view('front.contact');
    }
    
    public function help(Request $request){
        $page = Page::where('slug',$request->slug)->firstOrFail();
        return view('front/contact',compact('page'));
        dd($page);
        
    }

    /**
     * Process the contact form submission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function contactProcess(Request $request)
    {
        return response()->json((new \App\Services\GeneralService())->contactProcess($request->only(['first_name','last_name', 'mobile_number','email', 'subject', 'message'])));
    }
  
}
