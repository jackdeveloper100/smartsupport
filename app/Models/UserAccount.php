<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Helpers\General;

class UserAccount extends Model
{
    use HasFactory;
    /**
     * The name of the table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_account';
    
    public $timestamps = true;
    protected $dateFormat = 'U';
    
    protected $fillable = ['user_id', 'created_at','updated_at','bank_name','account_number','routing_number'];
    

    public function store($postData){

        $general = new General();
        $id = $postData['id'] ?? null;

        $userId = $postData['user_id'];
        
        $rules = [
            'bank_name' => ['required', 'string', 'regex:/^[a-zA-Z\s]+$/'],
            'account_number' => 'required|numeric|digits_between:1,15',
            'routing_number' => 'required|numeric|digits_between:1,9',
        ];
        
        if(!$id){
            $rules['image'] = 'required||mimes:jpg,png,pdf|max:51200';
        }else{
            $rules['image'] = 'nullable||mimes:jpg,png,pdf|max:51200';
        }
        
        $validator = Validator::make($postData, $rules,[
            'bank_name.required' => 'The bank name field is required',
            'bank_name' => 'The bank name field only allows characters ',
            'image.max' => 'File size is not more than 50 MB'
            ]);
        
        if ($validator->fails()) {
            return [
                'status' => 0,
                'message' => $validator->errors()->first(),
            ];
        }
        if($id){
            $model = UserAccount::find($id);
        }else{
            $model = new UserAccount();
        }
        // dd($model);

        if (isset($postData['image']) && $postData['image']->isValid()) {
            $uploadResult = $general->uploadFile($postData['image'], 'document');
            if (!$uploadResult['status']) {
                return $uploadResult;
            }
            $image=$uploadResult['file_name'];
            if ($image) {
                if ($model->file_name) {
                    $general->deleteFile($model->file_name, 'document');
                }
                $model->file_name = $image;
            }
        }
        $model->user_id = $userId;
        $model->bank_name = $postData['bank_name'];
        $model->account_number = $postData['account_number'];
        $model->routing_number = $postData['routing_number'];
        // dd($model->account_number);
        $model->save();
        
        return ['status'=>1, 'message'=> $id ? 'Document Updated Successfully' : 'Document Upload Successfully','next'=>'refresh'];
        // dd($userId);
    }
}