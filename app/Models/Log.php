<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use App\Helpers\Pagination;
use App\Helpers\General;
use Illuminate\Support\Facades\DB;
use App\Models\Device;
/**
 * Class Log
 * 
 * Represents the Log model for tracking user activity logs.
 *
 * @package App\Models
 */
class Log extends Model
{
    use HasUlids;

    /**
     * @var string $table The table associated with the model.
     */
    protected $table = 'log';

    /**
     * @var string $primaryKey The primary key associated with the table.
     */
    protected $primaryKey = 'id';

    /**
     * @var string $keyType The data type of the primary key.
     */
    protected $keyType = 'string';

    /**
     * @var bool $incrementing Indicates if the primary key is auto-incrementing.
     */
    public $incrementing = false;

    /**
     * @var bool $timestamps Indicates if the model should be timestamped.
     */
    public $timestamps = true;

    /**
     * @var string $dateFormat The storage format of the model's date columns.
     */
    protected $dateFormat = 'U';

    /**
     * @var array $fillable The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id', 
        'user_type', 
        'type', 
        'ip', 
        'client', 
        'created_at'
    ];

    /**
     * Add a new log entry.
     *
     * @param int $userId The user ID.
     * @param string $type The type of log entry.
     * @return bool Whether the log was added successfully.
     */
    public function add($userId, $type)
    {
        if (!config('setting.save_user_log')) {
        dd($userId,$type);
            return false;
        }
        $deviceUid = @$_COOKIE[config("setting.app_uid") . '_token'];
        if (!$deviceUid) {
        }
        
        $device = Device::where(['device_uid' => $deviceUid])->first();
        if (!$device) {
            return false;
        }
        
        $client = @$_SERVER['HTTP_USER_AGENT'];
        $general = new General();
        $activity = new Log();
        $ip = $general->getClientIp();
        $activity->ip = $ip;
        $activity->client = $client;

        $activity->user_id = $userId;
        $activity->type = $type;
        $activity->device_id = $device->id;

        $activity->created_at = time();

        $activity->save();
    }

    /**
     * Sends an email notification if a user logs in from a new device or location.
     *
     * @param object $user The user object.
     * @return bool Whether the email was sent successfully.
     */
    public function sendNewDeviceMail(object $user): bool
    {
        if (!config('setting.save_user_log')) {
            return false;
        }

        $general = new General();
        $deviceUid = $_COOKIE[config("setting.app_uid") . '_token'] ?? '';
        $ip = $general->getClientIp();
        $client = request()->header('User-Agent', 'Unknown Client');

        $existingLog = $this->where('user_id', $user->id)
            ->whereRaw('(device_id = ? OR ip = ?)', [$deviceUid, $ip])
            ->first();

        if (!$existingLog) {
            $ipInfo = $general->getIpInfo($ip, 2);
            $general->sendMail(
                $user->email,
                'Logged in With New Device | ' . config('setting.app_name'),
                view('email/login_device',
                [
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'ip' => $ip,
                    'client' => $general->deviceName($client),
                    'ip_info' => $ipInfo
                ])->render()
            );
        }

        return true;
    }

    /**
     * Retrieves logs for the admin with search and pagination.
     *
     * @param array $postData The data for filtering and pagination.
     * @return array The paginated log data.
     */
    public function listAdmin($postData)
{
    
    $query = DB::table('log')
        ->select(['log.created_at as created_at', 'log.type As type', 'log.ip', 'log.client', 'user.first_name', 'user.email', 'user.last_name'])
        ->join('user', 'user.id', '=', 'log.user_id');

    $searchText = isset($postData['search']['value']) ? $postData['search']['value'] : '';

    if (strlen($searchText) > 2) {
        $searchText = trim($searchText);

        $searchWords = explode(' ', $searchText);

        if (count($searchWords) > 1) {
            $query->where(function ($query) use ($searchWords) {
                foreach ($searchWords as $word) {
                    $query->orWhere('client', 'like', '%' . trim($word) . '%')
                        ->orWhere(DB::raw("concat(first_name, ' ', last_name)"), 'like', '%' . trim($word) . '%')
                        ->orWhere('email', 'like', '%' . trim($word) . '%')
                        ->orWhere(DB::raw("FROM_UNIXTIME(log.created_at, '%d-%m-%Y')"), 'LIKE', '%' . trim($word) . '%')
                        ->orWhere(function ($query) use ($word) {
                            if (stripos($word, 'fai') !== false) {
                                $query->where('log.type', '=', 0);
                            } elseif (stripos($word, 'succ') !== false) {
                                $query->where('log.type', '=', 1);
                            } elseif (stripos($word, 'reme') !== false) {
                                $query->where('log.type', '=', 2);
                            } elseif (stripos($word, 'Regi') !== false) {
                                $query->where('log.type', '=', 3);
                            } elseif (stripos($word, 'otp') !== false) {
                                $query->where('log.type', '=', 4);
                            } elseif (stripos($word, 'Login with social media') !== false) {
                                $query->where('log.type', '=', 5);
                            } elseif (stripos($word, 'Register with social media') !== false) {
                                $query->where('log.type', '=', 6);
                            }
                        });
                }
            });
        } else {
            $query->where(function ($query) use ($searchText) {
                $query->where('client', 'like', '%' . $searchText . '%')
                    ->orWhere(DB::raw("concat(first_name, ' ', last_name)"), 'like', '%' . $searchText . '%')
                    ->orWhere('email', 'like', '%' . $searchText . '%')
                    ->orWhere(DB::raw("FROM_UNIXTIME(log.created_at, '%d-%m-%Y')"), 'LIKE', '%' . $searchText . '%')
                    ->orWhere(function ($query) use ($searchText) {
                        if (stripos($searchText, 'fai') !== false) {
                            $query->where('log.type', '=', 0);
                        } elseif (stripos($searchText, 'succ') !== false) {
                            $query->where('log.type', '=', 1);
                        } elseif (stripos($searchText, 'reme') !== false) {
                            $query->where('log.type', '=', 2);
                        } elseif (stripos($searchText, 'Regi') !== false) {
                            $query->where('log.type', '=', 3);
                        } elseif (stripos($searchText, 'otp') !== false) {
                            $query->where('log.type', '=', 4);
                        } elseif (stripos($searchText, 'Login with social media') !== false) {
                            $query->where('log.type', '=', 5);
                        } elseif (stripos($searchText, 'Register with social media') !== false) {
                            $query->where('log.type', '=', 6);
                        }
                    });
            });
        }
    }

    // Get paginated result using the Pagination class
    $result = (new Pagination())->getDataTable($query, $postData);

    // Process the result data
    $general = new General();
    foreach ($result['data'] as $key => $row) {
        $deviceName = $general->deviceName($row->client);
        $result['data'][$key]->first_name = $row->first_name . ' ' . $row->last_name;
        $result['data'][$key]->location = $general->getIpInfo($row->ip, 2);
        $result['data'][$key]->device = $deviceName;
        $result['data'][$key]->type = $this->getType($row->type);
        $result['data'][$key]->created_at = date(config('setting.date_time_format'), $row->created_at);
        $result['data'][$key]->action = '<button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="fa fa-dots-vertical"></i></button>
            <div class="dropdown-menu">
                <label class="dropdown-item">Ip: ' . $row->ip . '</label>
                <label class="dropdown-item">Created At: ' . $row->created_at . '</label>
            </div>';
    }

    return $result;
}


    /**
     * Retrieves logs for a specific user with search and pagination.
     *
     * @param array $postData The data for filtering and pagination.
     * @param int $userId The user ID.
     * @return array The paginated log data.
     */
  public function list(array $postData, int $userId): array
{
    $query = DB::table('log')
        ->select('*')
        ->where('user_id', $userId);

    $searchText = $postData['search']['value'] ?? '';

    if (strlen($searchText) > 2) {
        $searchText = trim($searchText);
        $searchTerms = explode(' on ', $searchText);

        if (count($searchTerms) === 2) {
            $browser = $searchTerms[0];
            $os = $searchTerms[1];
            $query->where(function ($query) use ($browser, $os) {
                $query->where('client', 'like', '%' . $browser . '%')
                      ->where('client', 'like', '%' . $os . '%');
            });
        } else {
            $query->where('client', 'like', '%' . $searchText . '%')
            ->orWhere('ip', 'like', '%' . $searchText . '%');
        }
    }

    $result = (new Pagination())->getDataTable($query, $postData);

    $general = new General();
    foreach ($result['data'] as $key => $row) {
        $deviceName = $general->deviceName($row->client);
        $row->client = $deviceName; 
        $row->location = $general->getIpInfo($row->ip, 2);
        $row->type = $this->getType($row->type);
        $row->created_at = date(config('setting.date_format'), $row->created_at);
    }

    return $result;
}


    /**
     * Returns the user type in a formatted string.
     *
     * @param int $type The user type.
     * @return string The formatted user type.
     */
    public function getUserType(int $type): string
    {
        return $type === 1 ? '<span class="">Admin</span>' : '<span class="">User</span>';
    }

    /**
     * Returns the log type in a formatted string.
     *
     * @param int $type The log type.
     * @return string The formatted log type.
     */
    public function getType(int $type): string
    {
        return match ($type) {
            0 => '<span class="">Login failed</span>',
            1 => '<span class="">Login success</span>',
            3 => '<span class="">Register</span>',
            4 => '<span class="">Login with OTP</span>',
            5 => '<span class="">Login with social media</span>',
            default => '<span class="">Login with remember</span>',
        };
    }

    /**
     * Applies search filters to a query.
     *
     * @param \Illuminate\Database\Query\Builder $query The query builder.
     * @param string $searchText The search text.
     * @return void
     */
    private function applySearchFilter($query, string $searchText): void
    {
        if (strlen($searchText) > 2) {
            $searchText = '%' . $searchText . '%';
            $query->where(function ($query) use ($searchText) {
                $query->where('client', 'like', $searchText)
                    ->orWhere('ip', 'like', $searchText)
                    ->orWhereRaw("FROM_UNIXTIME(log.created_at, '%d-%m-%Y') LIKE ?", [$searchText]);
            });
        }
    }
}
