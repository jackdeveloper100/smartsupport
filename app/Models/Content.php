<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helper\Pagination;
use Illuminate\Support\Facades\DB;
use App\Helpers\General;
use Illuminate\Support\Facades\Validator;

class Content extends Model
{
    protected $table = 'content';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $dateFormat = 'U';

    public $contentList=[];

    protected $fillable = [
        'reference_id', 'page','type', 'key', 'content', 'created_at', 'updated_at'
    ];

    public function setPage($page='home',$referenceId=false){
        if($referenceId===false){
            $this->contentList=Content::where('page', $page)->get();
        }else{
            $this->contentList=Content::where('page', $page)->where('reference_id',$referenceId)->get();
        }
    }

    public function getContent($key){
        
        $contentHtml='';
        foreach($this->contentList as $content){
           
            if($key==$content->key){
                if($content->type=='text'){
                    $contentHtml=nl2br($content->content);
                }else if($content->type=='footer'){
                    $contentHtml=@json_decode($content->content,true);
                }else if($content->type=='services'){
                    $contentHtml=@json_decode($content->content,true);
                }else if($content->type=='header'){
                    $contentHtml=@json_decode($content->content,true);
                }else {
                    $contentHtml=$content->content;
                }
            }
        }
        return $contentHtml;
    }
    
    public function store($postData){
        $general = new General();
         $rules = [
        'id' => 'required',
        // 'content' => 'required'
        ];
        if(isset($postData['text']) && $postData['text']){
            $rules['content'] = 'required';
        }
        
        $validator = Validator::make($postData, $rules);
        if ($validator->fails()) {
            return [
                'status' => 0,
                'message' => $validator->errors()->first()
            ];
        }
        
        $model = Content::where('id',$postData['id'])->first();
        $content = $model->content;
        if($model->type == 'image'){
            $validator = Validator::make($postData, [
                'image' => 'image|mimes:gif,jpg,jpeg,png,svg,webp|max:12400',
                'image.*' => 'not:mimes:pdf,doc,docx,xls,xlsx',
            ]);
            
                if ($validator->fails()) {
                return ['status' => 0, 'message' => $validator->errors()->first()];
            }
            
            if (isset($postData['image']) && $postData['image']->isValid()) {
                $uploadResult = $general->uploadFile($postData['image'], 'content');
                // dd($uploadResult);
                if (!$uploadResult['status']) {
                    return $uploadResult;
                }
                $image=$uploadResult['file_name'];
                if ($image) {
                    if ($content) {
                        $general->deleteFile($model->image, 'content');
                    }
                    $content = $image;
                }
            }
        }elseif ($model->type == 'video') {
            $validator = Validator::make($postData, [
                'video' => 'mimes:mp4|max:51200', // Max 50MB
            ]);
        
            if ($validator->fails()) {
                return ['status' => 0, 'message' => $validator->errors()->first()];
            }
        
            if (isset($postData['video']) && $postData['video']->isValid()) {
                $uploadResult = $general->uploadFile($postData['video'], 'content');
        
                if (!$uploadResult['status']) {
                    return $uploadResult;
                }
        
                $video = $uploadResult['file_name'];
        
                if ($video) {
                    if ($content) {
                        $general->deleteFile($model->video, 'content');
                    }
                    $content = $video;
                }
            }
        }else{
            $content = $postData['content'];
        }   
        $model->content = $content;
        $model->save();
        return ['status' => 1, 'message'=> 'Saved Successfully','next'=> 'reload'];
        // dd($content,$model->type);
    }
}