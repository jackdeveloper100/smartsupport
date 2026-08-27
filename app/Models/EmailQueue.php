<?php 

namespace App\Models;

use App\Helpers\Pagination;
use App\Helpers\General;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EmailQueue extends Model{

    /**
     * The name of the table associated with the model.
     *
     * @var string
     */
    protected $table = 'email_queue';

      /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;
    protected $dateFormat = 'U';
     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['email_to', 'email_subject','email_body', 'is_sent','created_at','updated_at'];

     /**
     * Retrieves paginated list of email_template for admin with search capability.
     *
     * @param array $postData The data passed for pagination and search.
     * @return array The paginated and formatted list of pages.
     */
}