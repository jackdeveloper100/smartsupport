<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Document;
use App\Helpers\General;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Plan extends Model
{
    use HasFactory;
    /**
     * The name of the table associated with the model.
     *
     * @var string
     */
    protected $table = 'plan';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    protected $dateFormat = 'U';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['stripe_price_id', 'title', 'description', 'amount', 'duration' ,'created_at','status','updated_at'];
    
    public function getPlanId($plan){
        $plan = strtolower(trim($plan));

        switch ($plan) {
            case 'basic':
                return 1;
    
            case 'pro':
                return 2;
    
            case 'premium':
                return 3;
    
            default:
                return null;
        }
    }
}