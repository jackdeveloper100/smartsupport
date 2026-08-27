<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Helpers\General;

/**
 * Class Setting
 * 
 * Represents application settings stored in the database.
 * Provides methods to retrieve, update, and cache settings.
 */
class Setting extends Model
{
    /**
     * @var string The table associated with the model.
     */
    protected $table = 'setting';

    /**
     * @var string The primary key associated with the table.
     */
    protected $primaryKey = 'id';

    /**
     * @var array The attributes that are mass assignable.
     */
    protected $fillable = ['key', 'value'];

    /**
     * @var bool Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    
    /**
     * Updates the value of an existing setting by key if it differs from the current value.
     *
     * @param string $key The key of the setting.
     * @param string $value The new value to update.
     * @return void
     */
    public function updateOne(string $key, string $value): void
    {
        $setting = $this->where('key', $key)->first();

        if ($setting && $setting->value !== $value) {
            $setting->update(['value' => $value]);
        }
    }

    /**
     * Updates multiple settings based on an associative array of key-value pairs.
     * Clears the cache after updating all settings.
     *
     * @param array $data An associative array of key-value pairs to update.
     * @return void
     */
    public function updateAll(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->updateOne($key, $value);
        }
        $this->clearCache();
    }

    /**
     * Clears the settings cache by re-caching all settings for one day.
     *
     * @return void
     */
    public function clearCache(): void
    {
        Cache::put('setting', $this->allSettings(), now()->addDay());
    }

    /**
     * Retrieves all settings with a file attribute and organizes them by file name.
     *
     * @return array An associative array of settings, keyed by 'file.key'.
     */
    public function allSettings(): array
    {
        $data = [];
        $options = $this->whereNotNull('file')->get();

        foreach ($options as $row) {
            $data[$row['file'] . '.' . $row['key']] = $row['value'];
        }

        return $data;
    }

    /**
     * Retrieves all settings from cache if available; otherwise, fetches and caches them.
     *
     * @return array An associative array of all settings.
     */
    public function getAllSettings(): array
    {
        return Cache::remember('setting', now()->addDay(), function () {
            return $this->allSettings();
        });
    }

    /**
     * Stores settings provided in the post data after validation.
     *
     * @param array $postData The associative array containing setting data.
     * @return array An associative array indicating the status and message.
     * @throws ValidationException
     */
    public function store(array $postData): array
    {
        $settingData=$this->allSettings();
        $postData=array_merge($settingData,$postData);

        $validator = Validator::make($postData, [
            'admin_email' => 'required|email',
            'admin_phone' => 'required|regex:/^\+?[0-9]{10,15}$/',
            'date_format' => 'required|string',
            'date_time_format' => 'required|string',
            'timezone' => 'required|string',
            // 'user_email_verify' => 'required|boolean',
            // 'user_login_with_otp' => 'required|boolean',
            // 'cookie_consent' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return ['status' => 0,'message' => $validator->errors()->first()];
        }

        $this->updateAll($postData);

        return [
            'status' => 1,
            'message' => 'Data Saved Successfully',
            'next' => 'refresh'
        ];
    }
}
