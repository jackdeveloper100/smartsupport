<?php

namespace App\Helpers;

/**
 * Class General
 * This class contains helper functions commonly used across the application.
 */
class General
{
    /**
     * Returns a CSS class if the current route matches the given route.
     *
     * @param array|string $r Route or array of routes to check against.
     * @param string $class CSS class to return if the route matches.
     * @return string
     */
    public function routeMatchClass($r, $class = 'active')
    {
        return $this->checkRoute($r) ? $class : '';
    }

    /**
     * Checks if the current route matches a given route or array of routes.
     *
     * @param array|string $r Route or array of routes to check.
     * @return bool
     */
    public function checkRoute($r)
    {
        $currentRoute = \Illuminate\Support\Facades\Route::currentRouteName();
        if (is_array($r)) {
            return in_array($currentRoute, $r);
        } else {
            return $currentRoute == $r;
        }
    }

    /**
     * Returns the auth redirect URL from the session if it exists, otherwise returns the default URL.
     *
     * @param string $defaultUrl Default URL to return if no redirect URL is set in session.
     * @return string
     */
    public function authRedirectUrl($redirectUrl)
    {
        $session_auth_redirect_url = session('auth_redirect_url');
        if ($session_auth_redirect_url) {
            $redirectUrl = $session_auth_redirect_url;
            session('auth_redirect_url', '');
        }
        return $redirectUrl;
    }


     public function slugify($title)
    {
        $slug = strtolower($title);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }
    
    /**
     * Retrieves SEO meta tags from the cache or database.
     *
     * @return array|null
     */
    public function getMetaTags()
    {
        return (new \App\Models\SeoMeta())->getMetaTags();
    }

    /**
     * Retrieves all settings from the cache or database.
     *
     * @return array
     */
    public function getAllSettings()
    {
        return (new \App\Models\Setting())->getAllSettings();
    }

    /**
     * Checks if the rate limit for a given key has been exceeded.
     *
     * @param string $key Unique rate limiting key.
     * @param int $limit Maximum number of attempts allowed.
     * @return bool
     */
    public function rateLimit($key, $limit = 10)
    {
        return !\Illuminate\Support\Facades\RateLimiter::attempt($key . '_' . $this->getClientIp(), $limit, function () {});
    }   

    /**
     * Checks if Google reCAPTCHA verification fails.
     *
     * @return bool
     */
    public function recaptchaFails()
    {
        if (config('setting.google_recaptcha')) {
            $recaptcha = @$_REQUEST['g-recaptcha-response'];
            $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . config('setting.google_recaptcha_secret_key') . '&response=' . $recaptcha;
            $response = @file_get_contents($url);
            $response = @json_decode($response);
            return @$response->success ? false : true;
        } else {
            return false;
        }
    }

    /**
     * Retrieves the client's IP address.
     *
     * @return string
     */
    public function getClientIp()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if (isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = '';
        return $ipaddress;
    }

    /**
     * Fetches information about an IP address using a third-party API and caches the result.
     *
     * @param string $ip The IP address to look up. Defaults to the client's IP if empty.
     * @param int $decode Whether to decode the response (1: JSON, 2: location string).
     * @return mixed
     */
    public function getIpInfo($ip = '', $decode = 1)
    {
        if ($ip == '') {
            $ip = $this->getClientIp();
        }
        $key = 'ip_info:' . $ip;
        $result = \Illuminate\Support\Facades\Cache::get($key);
        if (!$result) {
            $result = @file_get_contents('https://api.tribital.com/ipinfo/index.php?ip=' . $ip);
            \Illuminate\Support\Facades\Cache::add($key, $result, 86400);
        }
        if ($result && $decode) {
            $result = @json_decode($result);
            if ($decode == 2) {
                if (@$result->country_name) {
                    $result = @$result->city . ' , ' . @$result->region . ' , ' . @$result->country_name;
                } else {
                    $result = '';
                }
            }
        }
        return $result;
    }

    /**
     * Fetches only the country information for a given IP address.
     *
     * @param string $ip The IP address to look up. Defaults to the client's IP if empty.
     * @return string
     */
    public function getIpInfoCountry($ip = '')
    {
        if ($ip = '') {
            $ip = $this->getClientIp();
        }
        $key = 'ip_info:' . $ip;
        $data = \Illuminate\Support\Facades\Cache::get($key);
        if ($data) {
            return $data;
        }
        $result = @file_get_contents('https://api.tribital.com/ipinfo_country/index.php?ip=' . $ip);
        \Illuminate\Support\Facades\Cache::add($key, $result, 86400);
        return $result;
    }

    /**
     * Retrieves the device name and operating system from the user agent string.
     *
     * @param string $userAgent User agent string to parse.
     * @return string
     */
    public function deviceName($device_name)
    {
        $result = (new \WhichBrowser\Parser($device_name));
        if ($result && isset($result->browser->name)) {
            return @$result->browser->name . ' on ' . @$result->os->name;
        } else {
            return '';
        }
    }

    /**
     * Retrieves the URL of a stored file, or returns the default "no file" URL if it doesn't exist.
     *
     * @param string $type The type of file (profile, setting, etc.).
     * @return string
     */
    public function getNoFile($type = 'profile')
    {
        return \Illuminate\Support\Facades\Storage::url('no_image.jpg');
    }

    /**
     * Returns the file path for a given type.
     *
     * @param string $type The type of file (profile, setting, blog, etc.).
     * @return string
     */
    public function getfilePath($type = 'profile')
    {
        $path = 'temp/';
        switch ($type) {
            case 'profile':
                $path = 'profile/';
                break;
            case 'document':
                $path = 'document/';
                break;    
            case 'page':
                $path = 'page/';
                break;
            case 'setting':
                $path = 'setting/';
                break;
            case 'content':
                $path = 'content/';
                break;
        }
        return $path;
    }

    /**
     * Retrieves the URL of a stored file, or returns the default "no file" URL if it doesn't exist.
     *
     * @param string|null $file The file name.
     * @param string $type The type of file (profile, setting, etc.).
     * @return string
     */
    public function getFileUrl($file, $type = 'profile')
    {
        if ($file) {
            $path = $this->getfilePath($type);
            $storage = new \Illuminate\Support\Facades\Storage();
            if ($storage::has($path . $file)) {
                return $storage::url($path . $file);
            }
        }
        return $this->getNoFile($type);
    }
    
   

    /**
     * Deletes a file from the storage.
     *
     * @param string|null $file The file name.
     * @param string $type The type of file (profile, setting, etc.).
     * @return void
     */
    
    public function deleteFile($file, $type = 'setting')
    {
        if ($file) {
            $path = $this->getfilePath($type);
            $storage = new \Illuminate\Support\Facades\Storage();
            if ($storage::has($path . $file)) {
                $storage::delete($path . $file);
            }
        }
    }

    /**
     * Uploads a file to storage and returns its path.
     *
     * @param \Illuminate\Http\UploadedFile $file The file to upload.
     * @param int $type The file type identifier.
     * @return string
     */
     
    public function uploadFile($file, $type = 'profile',$fileName='')
    {
        $filePath = $this->getfilePath($type);
        try {
            if($fileName){
                $filePath.='/'.$fileName.'.'.$file->getClientOriginalExtension();
            }
            $fileResult = \Illuminate\Support\Facades\Storage::put($filePath, $file);

            if ($fileResult) {
                return ['status'=>1,'file_name'=>str_replace($filePath . '/', '', $fileResult)];
            } else {
                return ['status'=>0,'message'=>'File upload failed'];
            }
        }catch(\Exception $e){
            return ['status'=>0,'message'=>$e->getMessage()];
        }
    }

    

    /**
     * Returns the password policy string.
     *
     * @param string $type Default URL to return if no redirect URL is set in session.
     * @return string
     */
    public function passwordType()
    {
        $type = config('setting.password_types');
        $policy = 'min:6';
        if ($type) {
            return \Illuminate\Validation\Rules\Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised(3);
        } else {
            return \Illuminate\Validation\Rules\Password::min(6);
        }
        return $policy;
    }
    /**
     * Returns the file url for a given type.
     *
     * @param string $type The type of file (profile, setting, blog, etc.).
     * @return string
     */
    public function fileRules($type = 'image', $size = 10240)
    {
        $rule = 'file|mimetypes:image/*|max:' . $size;
        switch ($type) {
            case 'image':
                $rule = 'file|mimes:jpeg,jpg,png,gif,webp,bmp,svg|max:' . $size;
                break;
            case 'pdf':
                $rule = 'file|mimes:pdf|max:10240';
                break;
            case 'doc':
                $rule = 'file|mimes:pdf,xlsx,doc,docx|max:1024';
                break;
            case 'all':
                $rule = 'file|mimes:pdf,xlsx,doc,docx,jpeg,jpg,png,gif,webp,bmp,svg|max:10240';
                break;
        }
        return $rule;
    }

    function generatePdf($html){
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://pdf.tribital.com/index.php',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => array('api_key'=>'MhjNq9FymfAHVXZkPgJxevUnQsSau3z8','html' => $html),
        ));
        
        $response = curl_exec($curl);
        curl_close($curl);
        return @json_decode($response,true);
    }
    
    public function sendEmail(string $to, string $template , array $data)
    {
        $user = \App\Models\User::where('email',$to)->first();
        
        $company = '';
        if($user !== '' && $user->type == 1 ){
            $company = \App\Models\User::where('id',$user->company_id)->first();
        }
        
        $templateData= (new \App\Models\EmailTemplate())->getEmailTemplate($template,$data);
        if($template == 'contractor_invite'|| $template == 'user_invite' || $template == 'company_invite' || $template == 'company_invite_with_free_contractor' ){
           $body=view('email/invite_template',['body'=>$templateData['body'],'company'=>$company])->render(); 
        }else{
            $body=view('email/template',['body'=>$templateData['body'], 'company'=>$company])->render();
        }
        return $this->sendMail($to, $templateData['subject'], $body);
    }
    /**
     * Sends a confirmation email for a new registration.
     *
     * @param string $email User's email address.
     * @param string $subject The email subject.
     * @param string $view The view for the email body.
     * @param array $data Data to be passed to the view.    
     * @return void
     */
    public static function sendMail(string $to, string $subject, string $body): void
    {      
            $user = \App\Models\User::where('email',$to)->first();
            
            if($user !== null && $user->type == 1 && $user->type != ''){
                $company = \App\Models\User::where('id',$user->company_id)->first();
                
                self::sendMailApi([
                    'from' => config('setting.mail_from_address'),
                    'from_name' => isset($company->company_name) ? $company->company_name : config('setting.mail_from_name') ,
                    'to' => $to, // Changed from $email to $to
                    'to_name' => '',
                    'subject' => $subject,
                    'body' => $body, // Corrected from $view and $data
                ]);
               
            }else{
                self::sendMailApi([
                    'from' => config('setting.mail_from_address'),
                    'from_name' => config('setting.mail_from_name'),
                    'to' => $to, // Changed from $email to $to
                    'to_name' => '',
                    'subject' => $subject,
                    'body' => $body, // Corrected from $view and $data
                ]);
            }
     
    }

    /**
     * Sends an email using the external mail API.
     *
     * This method sends an email by making a POST request to the specified mailer API endpoint.
     * It merges the provided data with additional SMTP configuration settings and an API key.
     *
     * @param array $data An associative array containing the email details:
     *                    - 'from' (string): The sender's email address.
     *                    - 'from_name' (string): The sender's name.
     *                    - 'to' (string): The recipient's email address.
     *                    - 'to_name' (string): The recipient's name.
     *                    - 'subject' (string): The subject of the email.
     *                    - 'body' (string): The body content of the email.
     *
     * @return array|null The response from the mailer API, decoded from JSON to an associative array,
     *                    or null if the response could not be decoded.
     */
    public static function sendMailApi($data)
    {
        // self::sendMailApi([
        //     'from' => config('setting.mail_from_address'),
        //     'from_name' => config('setting.mail_from_name'),
        //     'to' => $to, // Changed from $email to $to
        //     'to_name' => '',
        //     'subject' => $subject,
        //     'body' => $body, // Corrected from $view and $data
        // ]);

        $data = array_merge($data, [
            'api_key' => 'LhBuEz7wGEwv3AxmnBSX3QUwVsyjqr8qKj6jPjV7NuHkAFKnJR8',
            'smtp_host' => config('mail.mailers.smtp.host'),
            'smtp_port' => config('mail.mailers.smtp.port'),
            'smtp_encryption' => config('mail.mailers.smtp.encryption'),
            'smtp_username' => config('mail.mailers.smtp.username'),
            'smtp_password' => config('mail.mailers.smtp.password'),
        ]);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.tribital.com/mailer/send.php',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return @json_decode($response, true);
    }

    public function verifyEmail($email)
    {
        $result = ['status' => 1, 'message' => 'Email is valid'];
        try {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://verify.maileroo.net/check',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{
                "api_key":"375df02c16af6b78a9131cf6ba190d9444423a101843232680ba3434e0c4d9c1",
                "email_address":"erick.cevallos@afdasfasdfasdfsd.com"
            }',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            // echo $response;
            $response = json_decode($response, true);
            if ($response['success']) {
                if (!$response['success']['data']['format_valid']) {
                    $result = ['status' => 0, 'message' => 'Email is format is not valid'];
                }
                if (!$response['success']['data']['mx_found']) {
                    $result = ['status' => 0, 'message' => 'Email is is not valid'];
                }
                if (!$response['success']['data']['disposable']) {
                    $result = ['status' => 0, 'message' => 'Email is not allowed'];
                }
            }
        } catch (\Exception $e) {
        }
        return $result;
    }

    public function getTimezooneList()
    {
        return json_decode('{"Pacific\/Midway":"(UTC -11:00) Pacific\/Midway","Pacific\/Niue":"(UTC -11:00) Pacific\/Niue","Pacific\/Pago_Pago":"(UTC -11:00) Pacific\/Pago_Pago","America\/Adak":"(UTC -10:00) America\/Adak","Pacific\/Honolulu":"(UTC -10:00) Pacific\/Honolulu","Pacific\/Rarotonga":"(UTC -10:00) Pacific\/Rarotonga","Pacific\/Tahiti":"(UTC -10:00) Pacific\/Tahiti","Pacific\/Marquesas":"(UTC -09:30) Pacific\/Marquesas","America\/Anchorage":"(UTC -09:00) America\/Anchorage","America\/Juneau":"(UTC -09:00) America\/Juneau","America\/Metlakatla":"(UTC -09:00) America\/Metlakatla","America\/Nome":"(UTC -09:00) America\/Nome","America\/Sitka":"(UTC -09:00) America\/Sitka","America\/Yakutat":"(UTC -09:00) America\/Yakutat","Pacific\/Gambier":"(UTC -09:00) Pacific\/Gambier","America\/Los_Angeles":"(UTC -08:00) America\/Los_Angeles","America\/Tijuana":"(UTC -08:00) America\/Tijuana","America\/Vancouver":"(UTC -08:00) America\/Vancouver","Pacific\/Pitcairn":"(UTC -08:00) Pacific\/Pitcairn","America\/Boise":"(UTC -07:00) America\/Boise","America\/Cambridge_Bay":"(UTC -07:00) America\/Cambridge_Bay","America\/Ciudad_Juarez":"(UTC -07:00) America\/Ciudad_Juarez","America\/Creston":"(UTC -07:00) America\/Creston","America\/Dawson":"(UTC -07:00) America\/Dawson","America\/Dawson_Creek":"(UTC -07:00) America\/Dawson_Creek","America\/Denver":"(UTC -07:00) America\/Denver","America\/Edmonton":"(UTC -07:00) America\/Edmonton","America\/Fort_Nelson":"(UTC -07:00) America\/Fort_Nelson","America\/Hermosillo":"(UTC -07:00) America\/Hermosillo","America\/Inuvik":"(UTC -07:00) America\/Inuvik","America\/Mazatlan":"(UTC -07:00) America\/Mazatlan","America\/Phoenix":"(UTC -07:00) America\/Phoenix","America\/Whitehorse":"(UTC -07:00) America\/Whitehorse","America\/Bahia_Banderas":"(UTC -06:00) America\/Bahia_Banderas","America\/Belize":"(UTC -06:00) America\/Belize","America\/Chicago":"(UTC -06:00) America\/Chicago","America\/Chihuahua":"(UTC -06:00) America\/Chihuahua","America\/Costa_Rica":"(UTC -06:00) America\/Costa_Rica","America\/El_Salvador":"(UTC -06:00) America\/El_Salvador","America\/Guatemala":"(UTC -06:00) America\/Guatemala","America\/Indiana\/Knox":"(UTC -06:00) America\/Indiana\/Knox","America\/Indiana\/Tell_City":"(UTC -06:00) America\/Indiana\/Tell_City","America\/Managua":"(UTC -06:00) America\/Managua","America\/Matamoros":"(UTC -06:00) America\/Matamoros","America\/Menominee":"(UTC -06:00) America\/Menominee","America\/Merida":"(UTC -06:00) America\/Merida","America\/Mexico_City":"(UTC -06:00) America\/Mexico_City","America\/Monterrey":"(UTC -06:00) America\/Monterrey","America\/North_Dakota\/Beulah":"(UTC -06:00) America\/North_Dakota\/Beulah","America\/North_Dakota\/Center":"(UTC -06:00) America\/North_Dakota\/Center","America\/North_Dakota\/New_Salem":"(UTC -06:00) America\/North_Dakota\/New_Salem","America\/Ojinaga":"(UTC -06:00) America\/Ojinaga","America\/Rankin_Inlet":"(UTC -06:00) America\/Rankin_Inlet","America\/Regina":"(UTC -06:00) America\/Regina","America\/Resolute":"(UTC -06:00) America\/Resolute","America\/Swift_Current":"(UTC -06:00) America\/Swift_Current","America\/Tegucigalpa":"(UTC -06:00) America\/Tegucigalpa","America\/Winnipeg":"(UTC -06:00) America\/Winnipeg","Pacific\/Galapagos":"(UTC -06:00) Pacific\/Galapagos","America\/Atikokan":"(UTC -05:00) America\/Atikokan","America\/Bogota":"(UTC -05:00) America\/Bogota","America\/Cancun":"(UTC -05:00) America\/Cancun","America\/Cayman":"(UTC -05:00) America\/Cayman","America\/Detroit":"(UTC -05:00) America\/Detroit","America\/Eirunepe":"(UTC -05:00) America\/Eirunepe","America\/Grand_Turk":"(UTC -05:00) America\/Grand_Turk","America\/Guayaquil":"(UTC -05:00) America\/Guayaquil","America\/Havana":"(UTC -05:00) America\/Havana","America\/Indiana\/Indianapolis":"(UTC -05:00) America\/Indiana\/Indianapolis","America\/Indiana\/Marengo":"(UTC -05:00) America\/Indiana\/Marengo","America\/Indiana\/Petersburg":"(UTC -05:00) America\/Indiana\/Petersburg","America\/Indiana\/Vevay":"(UTC -05:00) America\/Indiana\/Vevay","America\/Indiana\/Vincennes":"(UTC -05:00) America\/Indiana\/Vincennes","America\/Indiana\/Winamac":"(UTC -05:00) America\/Indiana\/Winamac","America\/Iqaluit":"(UTC -05:00) America\/Iqaluit","America\/Jamaica":"(UTC -05:00) America\/Jamaica","America\/Kentucky\/Louisville":"(UTC -05:00) America\/Kentucky\/Louisville","America\/Kentucky\/Monticello":"(UTC -05:00) America\/Kentucky\/Monticello","America\/Lima":"(UTC -05:00) America\/Lima","America\/Nassau":"(UTC -05:00) America\/Nassau","America\/New_York":"(UTC -05:00) America\/New_York","America\/Panama":"(UTC -05:00) America\/Panama","America\/Port-au-Prince":"(UTC -05:00) America\/Port-au-Prince","America\/Rio_Branco":"(UTC -05:00) America\/Rio_Branco","America\/Toronto":"(UTC -05:00) America\/Toronto","Pacific\/Easter":"(UTC -05:00) Pacific\/Easter","America\/Anguilla":"(UTC -04:00) America\/Anguilla","America\/Antigua":"(UTC -04:00) America\/Antigua","America\/Aruba":"(UTC -04:00) America\/Aruba","America\/Barbados":"(UTC -04:00) America\/Barbados","America\/Blanc-Sablon":"(UTC -04:00) America\/Blanc-Sablon","America\/Boa_Vista":"(UTC -04:00) America\/Boa_Vista","America\/Campo_Grande":"(UTC -04:00) America\/Campo_Grande","America\/Caracas":"(UTC -04:00) America\/Caracas","America\/Cuiaba":"(UTC -04:00) America\/Cuiaba","America\/Curacao":"(UTC -04:00) America\/Curacao","America\/Dominica":"(UTC -04:00) America\/Dominica","America\/Glace_Bay":"(UTC -04:00) America\/Glace_Bay","America\/Goose_Bay":"(UTC -04:00) America\/Goose_Bay","America\/Grenada":"(UTC -04:00) America\/Grenada","America\/Guadeloupe":"(UTC -04:00) America\/Guadeloupe","America\/Guyana":"(UTC -04:00) America\/Guyana","America\/Halifax":"(UTC -04:00) America\/Halifax","America\/Kralendijk":"(UTC -04:00) America\/Kralendijk","America\/La_Paz":"(UTC -04:00) America\/La_Paz","America\/Lower_Princes":"(UTC -04:00) America\/Lower_Princes","America\/Manaus":"(UTC -04:00) America\/Manaus","America\/Marigot":"(UTC -04:00) America\/Marigot","America\/Martinique":"(UTC -04:00) America\/Martinique","America\/Moncton":"(UTC -04:00) America\/Moncton","America\/Montserrat":"(UTC -04:00) America\/Montserrat","America\/Port_of_Spain":"(UTC -04:00) America\/Port_of_Spain","America\/Porto_Velho":"(UTC -04:00) America\/Porto_Velho","America\/Puerto_Rico":"(UTC -04:00) America\/Puerto_Rico","America\/Santo_Domingo":"(UTC -04:00) America\/Santo_Domingo","America\/St_Barthelemy":"(UTC -04:00) America\/St_Barthelemy","America\/St_Kitts":"(UTC -04:00) America\/St_Kitts","America\/St_Lucia":"(UTC -04:00) America\/St_Lucia","America\/St_Thomas":"(UTC -04:00) America\/St_Thomas","America\/St_Vincent":"(UTC -04:00) America\/St_Vincent","America\/Thule":"(UTC -04:00) America\/Thule","America\/Tortola":"(UTC -04:00) America\/Tortola","Atlantic\/Bermuda":"(UTC -04:00) Atlantic\/Bermuda","America\/St_Johns":"(UTC -03:30) America\/St_Johns","America\/Araguaina":"(UTC -03:00) America\/Araguaina","America\/Argentina\/Buenos_Aires":"(UTC -03:00) America\/Argentina\/Buenos_Aires","America\/Argentina\/Catamarca":"(UTC -03:00) America\/Argentina\/Catamarca","America\/Argentina\/Cordoba":"(UTC -03:00) America\/Argentina\/Cordoba","America\/Argentina\/Jujuy":"(UTC -03:00) America\/Argentina\/Jujuy","America\/Argentina\/La_Rioja":"(UTC -03:00) America\/Argentina\/La_Rioja","America\/Argentina\/Mendoza":"(UTC -03:00) America\/Argentina\/Mendoza","America\/Argentina\/Rio_Gallegos":"(UTC -03:00) America\/Argentina\/Rio_Gallegos","America\/Argentina\/Salta":"(UTC -03:00) America\/Argentina\/Salta","America\/Argentina\/San_Juan":"(UTC -03:00) America\/Argentina\/San_Juan","America\/Argentina\/San_Luis":"(UTC -03:00) America\/Argentina\/San_Luis","America\/Argentina\/Tucuman":"(UTC -03:00) America\/Argentina\/Tucuman","America\/Argentina\/Ushuaia":"(UTC -03:00) America\/Argentina\/Ushuaia","America\/Asuncion":"(UTC -03:00) America\/Asuncion","America\/Bahia":"(UTC -03:00) America\/Bahia","America\/Belem":"(UTC -03:00) America\/Belem","America\/Cayenne":"(UTC -03:00) America\/Cayenne","America\/Fortaleza":"(UTC -03:00) America\/Fortaleza","America\/Maceio":"(UTC -03:00) America\/Maceio","America\/Miquelon":"(UTC -03:00) America\/Miquelon","America\/Montevideo":"(UTC -03:00) America\/Montevideo","America\/Paramaribo":"(UTC -03:00) America\/Paramaribo","America\/Punta_Arenas":"(UTC -03:00) America\/Punta_Arenas","America\/Recife":"(UTC -03:00) America\/Recife","America\/Santarem":"(UTC -03:00) America\/Santarem","America\/Santiago":"(UTC -03:00) America\/Santiago","America\/Sao_Paulo":"(UTC -03:00) America\/Sao_Paulo","Antarctica\/Palmer":"(UTC -03:00) Antarctica\/Palmer","Antarctica\/Rothera":"(UTC -03:00) Antarctica\/Rothera","Atlantic\/Stanley":"(UTC -03:00) Atlantic\/Stanley","America\/Noronha":"(UTC -02:00) America\/Noronha","America\/Nuuk":"(UTC -02:00) America\/Nuuk","America\/Scoresbysund":"(UTC -02:00) America\/Scoresbysund","Atlantic\/South_Georgia":"(UTC -02:00) Atlantic\/South_Georgia","Atlantic\/Azores":"(UTC -01:00) Atlantic\/Azores","Atlantic\/Cape_Verde":"(UTC -01:00) Atlantic\/Cape_Verde","Africa\/Abidjan":"(UTC -00:00) Africa\/Abidjan","Africa\/Accra":"(UTC -00:00) Africa\/Accra","Africa\/Bamako":"(UTC -00:00) Africa\/Bamako","Africa\/Banjul":"(UTC -00:00) Africa\/Banjul","Africa\/Bissau":"(UTC -00:00) Africa\/Bissau","Africa\/Conakry":"(UTC -00:00) Africa\/Conakry","Africa\/Dakar":"(UTC -00:00) Africa\/Dakar","Africa\/Freetown":"(UTC -00:00) Africa\/Freetown","Africa\/Lome":"(UTC -00:00) Africa\/Lome","Africa\/Monrovia":"(UTC -00:00) Africa\/Monrovia","Africa\/Nouakchott":"(UTC -00:00) Africa\/Nouakchott","Africa\/Ouagadougou":"(UTC -00:00) Africa\/Ouagadougou","Africa\/Sao_Tome":"(UTC -00:00) Africa\/Sao_Tome","America\/Danmarkshavn":"(UTC -00:00) America\/Danmarkshavn","Antarctica\/Troll":"(UTC -00:00) Antarctica\/Troll","Atlantic\/Canary":"(UTC -00:00) Atlantic\/Canary","Atlantic\/Faroe":"(UTC -00:00) Atlantic\/Faroe","Atlantic\/Madeira":"(UTC -00:00) Atlantic\/Madeira","Atlantic\/Reykjavik":"(UTC -00:00) Atlantic\/Reykjavik","Atlantic\/St_Helena":"(UTC -00:00) Atlantic\/St_Helena","Europe\/Dublin":"(UTC -00:00) Europe\/Dublin","Europe\/Guernsey":"(UTC -00:00) Europe\/Guernsey","Europe\/Isle_of_Man":"(UTC -00:00) Europe\/Isle_of_Man","Europe\/Jersey":"(UTC -00:00) Europe\/Jersey","Europe\/Lisbon":"(UTC -00:00) Europe\/Lisbon","Europe\/London":"(UTC -00:00) Europe\/London","UTC":"(UTC -00:00) UTC","Africa\/Algiers":"(UTC +01:00) Africa\/Algiers","Africa\/Bangui":"(UTC +01:00) Africa\/Bangui","Africa\/Brazzaville":"(UTC +01:00) Africa\/Brazzaville","Africa\/Casablanca":"(UTC +01:00) Africa\/Casablanca","Africa\/Ceuta":"(UTC +01:00) Africa\/Ceuta","Africa\/Douala":"(UTC +01:00) Africa\/Douala","Africa\/El_Aaiun":"(UTC +01:00) Africa\/El_Aaiun","Africa\/Kinshasa":"(UTC +01:00) Africa\/Kinshasa","Africa\/Lagos":"(UTC +01:00) Africa\/Lagos","Africa\/Libreville":"(UTC +01:00) Africa\/Libreville","Africa\/Luanda":"(UTC +01:00) Africa\/Luanda","Africa\/Malabo":"(UTC +01:00) Africa\/Malabo","Africa\/Ndjamena":"(UTC +01:00) Africa\/Ndjamena","Africa\/Niamey":"(UTC +01:00) Africa\/Niamey","Africa\/Porto-Novo":"(UTC +01:00) Africa\/Porto-Novo","Africa\/Tunis":"(UTC +01:00) Africa\/Tunis","Arctic\/Longyearbyen":"(UTC +01:00) Arctic\/Longyearbyen","Europe\/Amsterdam":"(UTC +01:00) Europe\/Amsterdam","Europe\/Andorra":"(UTC +01:00) Europe\/Andorra","Europe\/Belgrade":"(UTC +01:00) Europe\/Belgrade","Europe\/Berlin":"(UTC +01:00) Europe\/Berlin","Europe\/Bratislava":"(UTC +01:00) Europe\/Bratislava","Europe\/Brussels":"(UTC +01:00) Europe\/Brussels","Europe\/Budapest":"(UTC +01:00) Europe\/Budapest","Europe\/Busingen":"(UTC +01:00) Europe\/Busingen","Europe\/Copenhagen":"(UTC +01:00) Europe\/Copenhagen","Europe\/Gibraltar":"(UTC +01:00) Europe\/Gibraltar","Europe\/Ljubljana":"(UTC +01:00) Europe\/Ljubljana","Europe\/Luxembourg":"(UTC +01:00) Europe\/Luxembourg","Europe\/Madrid":"(UTC +01:00) Europe\/Madrid","Europe\/Malta":"(UTC +01:00) Europe\/Malta","Europe\/Monaco":"(UTC +01:00) Europe\/Monaco","Europe\/Oslo":"(UTC +01:00) Europe\/Oslo","Europe\/Paris":"(UTC +01:00) Europe\/Paris","Europe\/Podgorica":"(UTC +01:00) Europe\/Podgorica","Europe\/Prague":"(UTC +01:00) Europe\/Prague","Europe\/Rome":"(UTC +01:00) Europe\/Rome","Europe\/San_Marino":"(UTC +01:00) Europe\/San_Marino","Europe\/Sarajevo":"(UTC +01:00) Europe\/Sarajevo","Europe\/Skopje":"(UTC +01:00) Europe\/Skopje","Europe\/Stockholm":"(UTC +01:00) Europe\/Stockholm","Europe\/Tirane":"(UTC +01:00) Europe\/Tirane","Europe\/Vaduz":"(UTC +01:00) Europe\/Vaduz","Europe\/Vatican":"(UTC +01:00) Europe\/Vatican","Europe\/Vienna":"(UTC +01:00) Europe\/Vienna","Europe\/Warsaw":"(UTC +01:00) Europe\/Warsaw","Europe\/Zagreb":"(UTC +01:00) Europe\/Zagreb","Europe\/Zurich":"(UTC +01:00) Europe\/Zurich","Africa\/Blantyre":"(UTC +02:00) Africa\/Blantyre","Africa\/Bujumbura":"(UTC +02:00) Africa\/Bujumbura","Africa\/Cairo":"(UTC +02:00) Africa\/Cairo","Africa\/Gaborone":"(UTC +02:00) Africa\/Gaborone","Africa\/Harare":"(UTC +02:00) Africa\/Harare","Africa\/Johannesburg":"(UTC +02:00) Africa\/Johannesburg","Africa\/Juba":"(UTC +02:00) Africa\/Juba","Africa\/Khartoum":"(UTC +02:00) Africa\/Khartoum","Africa\/Kigali":"(UTC +02:00) Africa\/Kigali","Africa\/Lubumbashi":"(UTC +02:00) Africa\/Lubumbashi","Africa\/Lusaka":"(UTC +02:00) Africa\/Lusaka","Africa\/Maputo":"(UTC +02:00) Africa\/Maputo","Africa\/Maseru":"(UTC +02:00) Africa\/Maseru","Africa\/Mbabane":"(UTC +02:00) Africa\/Mbabane","Africa\/Tripoli":"(UTC +02:00) Africa\/Tripoli","Africa\/Windhoek":"(UTC +02:00) Africa\/Windhoek","Asia\/Beirut":"(UTC +02:00) Asia\/Beirut","Asia\/Famagusta":"(UTC +02:00) Asia\/Famagusta","Asia\/Gaza":"(UTC +02:00) Asia\/Gaza","Asia\/Hebron":"(UTC +02:00) Asia\/Hebron","Asia\/Jerusalem":"(UTC +02:00) Asia\/Jerusalem","Asia\/Nicosia":"(UTC +02:00) Asia\/Nicosia","Europe\/Athens":"(UTC +02:00) Europe\/Athens","Europe\/Bucharest":"(UTC +02:00) Europe\/Bucharest","Europe\/Chisinau":"(UTC +02:00) Europe\/Chisinau","Europe\/Helsinki":"(UTC +02:00) Europe\/Helsinki","Europe\/Kaliningrad":"(UTC +02:00) Europe\/Kaliningrad","Europe\/Kyiv":"(UTC +02:00) Europe\/Kyiv","Europe\/Mariehamn":"(UTC +02:00) Europe\/Mariehamn","Europe\/Riga":"(UTC +02:00) Europe\/Riga","Europe\/Sofia":"(UTC +02:00) Europe\/Sofia","Europe\/Tallinn":"(UTC +02:00) Europe\/Tallinn","Europe\/Vilnius":"(UTC +02:00) Europe\/Vilnius","Africa\/Addis_Ababa":"(UTC +03:00) Africa\/Addis_Ababa","Africa\/Asmara":"(UTC +03:00) Africa\/Asmara","Africa\/Dar_es_Salaam":"(UTC +03:00) Africa\/Dar_es_Salaam","Africa\/Djibouti":"(UTC +03:00) Africa\/Djibouti","Africa\/Kampala":"(UTC +03:00) Africa\/Kampala","Africa\/Mogadishu":"(UTC +03:00) Africa\/Mogadishu","Africa\/Nairobi":"(UTC +03:00) Africa\/Nairobi","Antarctica\/Syowa":"(UTC +03:00) Antarctica\/Syowa","Asia\/Aden":"(UTC +03:00) Asia\/Aden","Asia\/Amman":"(UTC +03:00) Asia\/Amman","Asia\/Baghdad":"(UTC +03:00) Asia\/Baghdad","Asia\/Bahrain":"(UTC +03:00) Asia\/Bahrain","Asia\/Damascus":"(UTC +03:00) Asia\/Damascus","Asia\/Kuwait":"(UTC +03:00) Asia\/Kuwait","Asia\/Qatar":"(UTC +03:00) Asia\/Qatar","Asia\/Riyadh":"(UTC +03:00) Asia\/Riyadh","Europe\/Istanbul":"(UTC +03:00) Europe\/Istanbul","Europe\/Kirov":"(UTC +03:00) Europe\/Kirov","Europe\/Minsk":"(UTC +03:00) Europe\/Minsk","Europe\/Moscow":"(UTC +03:00) Europe\/Moscow","Europe\/Simferopol":"(UTC +03:00) Europe\/Simferopol","Europe\/Volgograd":"(UTC +03:00) Europe\/Volgograd","Indian\/Antananarivo":"(UTC +03:00) Indian\/Antananarivo","Indian\/Comoro":"(UTC +03:00) Indian\/Comoro","Indian\/Mayotte":"(UTC +03:00) Indian\/Mayotte","Asia\/Tehran":"(UTC +03:30) Asia\/Tehran","Asia\/Baku":"(UTC +04:00) Asia\/Baku","Asia\/Dubai":"(UTC +04:00) Asia\/Dubai","Asia\/Muscat":"(UTC +04:00) Asia\/Muscat","Asia\/Tbilisi":"(UTC +04:00) Asia\/Tbilisi","Asia\/Yerevan":"(UTC +04:00) Asia\/Yerevan","Europe\/Astrakhan":"(UTC +04:00) Europe\/Astrakhan","Europe\/Samara":"(UTC +04:00) Europe\/Samara","Europe\/Saratov":"(UTC +04:00) Europe\/Saratov","Europe\/Ulyanovsk":"(UTC +04:00) Europe\/Ulyanovsk","Indian\/Mahe":"(UTC +04:00) Indian\/Mahe","Indian\/Mauritius":"(UTC +04:00) Indian\/Mauritius","Indian\/Reunion":"(UTC +04:00) Indian\/Reunion","Asia\/Kabul":"(UTC +04:30) Asia\/Kabul","Antarctica\/Mawson":"(UTC +05:00) Antarctica\/Mawson","Antarctica\/Vostok":"(UTC +05:00) Antarctica\/Vostok","Asia\/Almaty":"(UTC +05:00) Asia\/Almaty","Asia\/Aqtau":"(UTC +05:00) Asia\/Aqtau","Asia\/Aqtobe":"(UTC +05:00) Asia\/Aqtobe","Asia\/Ashgabat":"(UTC +05:00) Asia\/Ashgabat","Asia\/Atyrau":"(UTC +05:00) Asia\/Atyrau","Asia\/Dushanbe":"(UTC +05:00) Asia\/Dushanbe","Asia\/Karachi":"(UTC +05:00) Asia\/Karachi","Asia\/Oral":"(UTC +05:00) Asia\/Oral","Asia\/Qostanay":"(UTC +05:00) Asia\/Qostanay","Asia\/Qyzylorda":"(UTC +05:00) Asia\/Qyzylorda","Asia\/Samarkand":"(UTC +05:00) Asia\/Samarkand","Asia\/Tashkent":"(UTC +05:00) Asia\/Tashkent","Asia\/Yekaterinburg":"(UTC +05:00) Asia\/Yekaterinburg","Indian\/Kerguelen":"(UTC +05:00) Indian\/Kerguelen","Indian\/Maldives":"(UTC +05:00) Indian\/Maldives","Asia\/Colombo":"(UTC +05:30) Asia\/Colombo","Asia\/Kolkata":"(UTC +05:30) Asia\/Kolkata","Asia\/Kathmandu":"(UTC +05:45) Asia\/Kathmandu","Asia\/Bishkek":"(UTC +06:00) Asia\/Bishkek","Asia\/Dhaka":"(UTC +06:00) Asia\/Dhaka","Asia\/Omsk":"(UTC +06:00) Asia\/Omsk","Asia\/Thimphu":"(UTC +06:00) Asia\/Thimphu","Asia\/Urumqi":"(UTC +06:00) Asia\/Urumqi","Indian\/Chagos":"(UTC +06:00) Indian\/Chagos","Asia\/Yangon":"(UTC +06:30) Asia\/Yangon","Indian\/Cocos":"(UTC +06:30) Indian\/Cocos","Antarctica\/Davis":"(UTC +07:00) Antarctica\/Davis","Asia\/Bangkok":"(UTC +07:00) Asia\/Bangkok","Asia\/Barnaul":"(UTC +07:00) Asia\/Barnaul","Asia\/Ho_Chi_Minh":"(UTC +07:00) Asia\/Ho_Chi_Minh","Asia\/Hovd":"(UTC +07:00) Asia\/Hovd","Asia\/Jakarta":"(UTC +07:00) Asia\/Jakarta","Asia\/Krasnoyarsk":"(UTC +07:00) Asia\/Krasnoyarsk","Asia\/Novokuznetsk":"(UTC +07:00) Asia\/Novokuznetsk","Asia\/Novosibirsk":"(UTC +07:00) Asia\/Novosibirsk","Asia\/Phnom_Penh":"(UTC +07:00) Asia\/Phnom_Penh","Asia\/Pontianak":"(UTC +07:00) Asia\/Pontianak","Asia\/Tomsk":"(UTC +07:00) Asia\/Tomsk","Asia\/Vientiane":"(UTC +07:00) Asia\/Vientiane","Indian\/Christmas":"(UTC +07:00) Indian\/Christmas","Antarctica\/Casey":"(UTC +08:00) Antarctica\/Casey","Asia\/Brunei":"(UTC +08:00) Asia\/Brunei","Asia\/Hong_Kong":"(UTC +08:00) Asia\/Hong_Kong","Asia\/Irkutsk":"(UTC +08:00) Asia\/Irkutsk","Asia\/Kuala_Lumpur":"(UTC +08:00) Asia\/Kuala_Lumpur","Asia\/Kuching":"(UTC +08:00) Asia\/Kuching","Asia\/Macau":"(UTC +08:00) Asia\/Macau","Asia\/Makassar":"(UTC +08:00) Asia\/Makassar","Asia\/Manila":"(UTC +08:00) Asia\/Manila","Asia\/Shanghai":"(UTC +08:00) Asia\/Shanghai","Asia\/Singapore":"(UTC +08:00) Asia\/Singapore","Asia\/Taipei":"(UTC +08:00) Asia\/Taipei","Asia\/Ulaanbaatar":"(UTC +08:00) Asia\/Ulaanbaatar","Australia\/Perth":"(UTC +08:00) Australia\/Perth","Australia\/Eucla":"(UTC +08:45) Australia\/Eucla","Asia\/Chita":"(UTC +09:00) Asia\/Chita","Asia\/Dili":"(UTC +09:00) Asia\/Dili","Asia\/Jayapura":"(UTC +09:00) Asia\/Jayapura","Asia\/Khandyga":"(UTC +09:00) Asia\/Khandyga","Asia\/Pyongyang":"(UTC +09:00) Asia\/Pyongyang","Asia\/Seoul":"(UTC +09:00) Asia\/Seoul","Asia\/Tokyo":"(UTC +09:00) Asia\/Tokyo","Asia\/Yakutsk":"(UTC +09:00) Asia\/Yakutsk","Pacific\/Palau":"(UTC +09:00) Pacific\/Palau","Australia\/Darwin":"(UTC +09:30) Australia\/Darwin","Antarctica\/DumontDUrville":"(UTC +10:00) Antarctica\/DumontDUrville","Asia\/Ust-Nera":"(UTC +10:00) Asia\/Ust-Nera","Asia\/Vladivostok":"(UTC +10:00) Asia\/Vladivostok","Australia\/Brisbane":"(UTC +10:00) Australia\/Brisbane","Australia\/Lindeman":"(UTC +10:00) Australia\/Lindeman","Pacific\/Chuuk":"(UTC +10:00) Pacific\/Chuuk","Pacific\/Guam":"(UTC +10:00) Pacific\/Guam","Pacific\/Port_Moresby":"(UTC +10:00) Pacific\/Port_Moresby","Pacific\/Saipan":"(UTC +10:00) Pacific\/Saipan","Australia\/Adelaide":"(UTC +10:30) Australia\/Adelaide","Australia\/Broken_Hill":"(UTC +10:30) Australia\/Broken_Hill","Antarctica\/Macquarie":"(UTC +11:00) Antarctica\/Macquarie","Asia\/Magadan":"(UTC +11:00) Asia\/Magadan","Asia\/Sakhalin":"(UTC +11:00) Asia\/Sakhalin","Asia\/Srednekolymsk":"(UTC +11:00) Asia\/Srednekolymsk","Australia\/Hobart":"(UTC +11:00) Australia\/Hobart","Australia\/Lord_Howe":"(UTC +11:00) Australia\/Lord_Howe","Australia\/Melbourne":"(UTC +11:00) Australia\/Melbourne","Australia\/Sydney":"(UTC +11:00) Australia\/Sydney","Pacific\/Bougainville":"(UTC +11:00) Pacific\/Bougainville","Pacific\/Efate":"(UTC +11:00) Pacific\/Efate","Pacific\/Guadalcanal":"(UTC +11:00) Pacific\/Guadalcanal","Pacific\/Kosrae":"(UTC +11:00) Pacific\/Kosrae","Pacific\/Noumea":"(UTC +11:00) Pacific\/Noumea","Pacific\/Pohnpei":"(UTC +11:00) Pacific\/Pohnpei","Asia\/Anadyr":"(UTC +12:00) Asia\/Anadyr","Asia\/Kamchatka":"(UTC +12:00) Asia\/Kamchatka","Pacific\/Fiji":"(UTC +12:00) Pacific\/Fiji","Pacific\/Funafuti":"(UTC +12:00) Pacific\/Funafuti","Pacific\/Kwajalein":"(UTC +12:00) Pacific\/Kwajalein","Pacific\/Majuro":"(UTC +12:00) Pacific\/Majuro","Pacific\/Nauru":"(UTC +12:00) Pacific\/Nauru","Pacific\/Norfolk":"(UTC +12:00) Pacific\/Norfolk","Pacific\/Tarawa":"(UTC +12:00) Pacific\/Tarawa","Pacific\/Wake":"(UTC +12:00) Pacific\/Wake","Pacific\/Wallis":"(UTC +12:00) Pacific\/Wallis","Antarctica\/McMurdo":"(UTC +13:00) Antarctica\/McMurdo","Pacific\/Apia":"(UTC +13:00) Pacific\/Apia","Pacific\/Auckland":"(UTC +13:00) Pacific\/Auckland","Pacific\/Fakaofo":"(UTC +13:00) Pacific\/Fakaofo","Pacific\/Kanton":"(UTC +13:00) Pacific\/Kanton","Pacific\/Tongatapu":"(UTC +13:00) Pacific\/Tongatapu","Pacific\/Chatham":"(UTC +13:45) Pacific\/Chatham","Pacific\/Kiritimati":"(UTC +14:00) Pacific\/Kiritimati"}',true);
    }
    
    public function getUSStates()
    {
        return [
            'AL'=>'Alabama','AK'=>'Alaska','AZ'=>'Arizona','AR'=>'Arkansas','CA'=>'California','CO'=>'Colorado',
            'CT'=>'Connecticut','DE'=>'Delaware','FL'=>'Florida','GA'=>'Georgia','HI'=>'Hawaii','ID'=>'Idaho',
            'IL'=>'Illinois','IN'=>'Indiana','IA'=>'Iowa','KS'=>'Kansas','KY'=>'Kentucky','LA'=>'Louisiana',
            'ME'=>'Maine','MD'=>'Maryland','MA'=>'Massachusetts','MI'=>'Michigan','MN'=>'Minnesota','MS'=>'Mississippi',
            'MO'=>'Missouri','MT'=>'Montana','NE'=>'Nebraska','NV'=>'Nevada','NH'=>'New Hampshire','NJ'=>'New Jersey',
            'NM'=>'New Mexico','NY'=>'New York','NC'=>'North Carolina','ND'=>'North Dakota','OH'=>'Ohio','OK'=>'Oklahoma',
            'OR'=>'Oregon','PA'=>'Pennsylvania','RI'=>'Rhode Island','SC'=>'South Carolina','SD'=>'South Dakota',
            'TN'=>'Tennessee','TX'=>'Texas','UT'=>'Utah','VT'=>'Vermont','VA'=>'Virginia','WA'=>'Washington',
            'WV'=>'West Virginia','WI'=>'Wisconsin','WY'=>'Wyoming'
        ];
    }
}
