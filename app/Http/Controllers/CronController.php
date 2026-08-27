<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\EmailQueue;
use App\Helpers\General;
use Illuminate\Http\Request;

class CronController extends Controller
{
    public function sendDocumentStatusMail(){
        $emailQueues = EmailQueue::where('is_sent',0)->get();
        
        if($emailQueues->isEmpty()){
            return response()->json(['status'=>0, 'message'=>'no Pending Mail found']);
        }
        if($emailQueues){
            foreach($emailQueues as $emailQueue){
                $emailQueue = EmailQueue::find($emailQueue->id);
                if(!$emailQueue){
                    return response()->json(['status'=>0, 'message'=>'no Pending Mail found']);
                }
                if($emailQueue){
                    (new General())->sendMail($emailQueue->email_to,  $emailQueue->email_subject, $emailQueue->email_body);
                    $emailQueue->is_sent = 1;
                    $emailQueue->delete();
                }
            }
            return "ok";
        }
    }
}