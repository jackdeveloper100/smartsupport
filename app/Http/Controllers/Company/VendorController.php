<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Helpers\General;
use App\Models\Device;
use App\Models\Log;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Document;
use App\Models\Notification;
use App\Models\UserAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use App\Models\DocumentType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; 
use App\Services\FileEncryptionService;
use setasign\Fpdi\Fpdi;

/**
 * Class ContractorController
 * @package App\Http\Controllers\Company
 *
 * Handles user management functionalities in the admin panel.
 */
class VendorController extends Controller
{
    /**
     * Display the user index view.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $sessionUser = auth()->user();
        $subscriptionData = Subscription::where('user_id',$sessionUser->id)->first();
        $subscriptionStatus = $subscriptionData->status ?? null;
        $isValid = false;
        if($sessionUser->unlimited_conractors == 1){
            $isValid = true;
        }else{
            $companyplan = (new User())->getCompanyPlanInfo($sessionUser->id);
            if ($companyplan == 1 || $companyplan == null || $subscriptionStatus != 'active') {
                $isValid = false;
            }else{
                $isValid = true;
            }
        }
        if($isValid == true){        
           return view('company/vendor/index');    
        }else{
            return redirect('company/dashboard')->with('warning', 'This feature isn’t available on your current plan. Please upgrade to access it.');
        }
    }
    
     /**
     * Get a list of users.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        return response()->json((new Vendor())->list($request->all()));
    }
 
     /**
     * Show the form for creating a new user.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {   
        $user = auth()->user();
        $contractorList = User::where('company_id',$user->id)->get();
        return view('company/vendor/create',compact('user','contractorList'));
    }
    
     /**
     * Show the form for updating a specific user.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
      public function update(Request $request)
    {
        $model = Vendor::find($request->id);
        if (!$model) {
            return redirect()->back()->with('error', 'Vendor not found.');
        }
    
        $user = auth()->user();
        $contractorList = User::where('company_id', $user->id)->get();
    
        return view('company/vendor/update', compact('model', 'user', 'contractorList'));
    }

    
     /**
     * Save or update user data.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request)
    {
        return response()->json((new Vendor())->store($request->all()));
    }
    
     /**
     * Delete a specific contractor.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function delete(Request $request)
    {
        $model = Vendor::find($request->input('id'));
        if (!$model) {
            return response()->json(['status' => 0, 'message' => 'No data found']);
        }
        $model->delete();
        return response()->json(['status' => 1, 'message' => 'W-9 Request delete successfully.', 'next' => 'refresh']);
    }

    public function showW9($token)
    {
        $general = new General();
        $w9Request = Vendor::where('token', $token)->firstOrFail();
        if($w9Request && $w9Request->request_status == 1){
            return view('company/vendor/submit');
        }
        
        if($w9Request){
            $w9Request->email_open_at = date('Y-m-d H:i:s');
            $w9Request->save();
        }
        
        $company = User::find($w9Request->representative_of);
        $user = User::find($w9Request->vendor_contact_person);
        $userName = null;
        if($user){
            $userName = $user->first_name. ' '. $user->last_name;
        }
        $step = request()->get('step', 0);
        
        $sessionKey = 'w9_step_' . $w9Request->token;
        $lastStep = session($sessionKey, -1);
        
        if ($step <= $lastStep) {
            return redirect()->route('w9_form', [
                'token' => $token,
                'step' => $lastStep + 1
            ])->with('error', 'You cannot go back to previous steps.');
        }


        $getUSStates = (new General())->getUSStates();
        $model = auth()->user();
    
        $decrypt = function($value, $key) {
            if (!$value) return null;
            $data = base64_decode($value);
            $iv = substr(hash('sha256', $key), 0, 16);
            return openssl_decrypt(
                $data,
                'aes-256-cbc',
                hash('sha256', $key, true),
                OPENSSL_RAW_DATA,
                $iv
            );
        };
    
        $serviceKey = $w9Request->encryption_key;
    
        $decryptedData = [
            'entity_name'          => $decrypt($w9Request->entity_name, $serviceKey),
            'business_name'        => !empty($w9Request->business_name) ? $decrypt($w9Request->business_name, $serviceKey) : null,
            'address'              => $decrypt($w9Request->address, $serviceKey),
            'city'                 => $decrypt($w9Request->city, $serviceKey),
            'state'                => $decrypt($w9Request->state, $serviceKey),
            'zip_code'             => $decrypt($w9Request->zip_code, $serviceKey),
            'list_account_number'  => $decrypt($w9Request->list_account_number, $serviceKey),
            'entity_type'          => $decrypt($w9Request->entity_type, $serviceKey),
            'other_entity'         => $decrypt($w9Request->other_entity, $serviceKey),
            'vendor_company_name'  => $decrypt($w9Request->vendor_company_name, $serviceKey),
        ];

        $decryptedSignatureBase64 = null;
        if (!empty($w9Request->signature)) {
            $signaturePath = public_path($w9Request->signature);
            if (file_exists($signaturePath)) {
                try {
                    $fileEncryptService = new FileEncryptionService($w9Request->encryption_key);
                    $tempSignature = tempnam(sys_get_temp_dir(), 'sig_') . '.png';
                    $fileEncryptService->decryptFile($signaturePath, $tempSignature);
                    $decryptedSignatureBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($tempSignature));
                    @unlink($tempSignature);
                } catch (\Exception $e) {
                    $decryptedSignatureBase64 = null;
                }
            }
        }
        $decryptedData['signature'] = $decryptedSignatureBase64;
        
        return view('company/vendor/w9_form', [
            'w9Request' => $w9Request,
            'step'      => $step,
            'getUSStates' => $getUSStates,
            'model'     => $model,
            'decrypted' => $decryptedData,
            'company' => $company,
            'userName' => $userName,
            'general'   => $general, 
        ]);
    }

    public function saveStep(Request $request, $token)
    {
        $w9Request = Vendor::where('token', $token)->firstOrFail();
        $step = $request->input('step');
    
        $stepMessages = [
            0 => 'Company selected successfully!',
            1 => 'Step 1 Tax details saved successfully!',
            2 => 'Step 2 Address saved successfully!',
            3 => 'Step 3 Signature saved successfully!',
        ];
        
   

        if (!$w9Request->encryption_key) {
            $encryptionKey = bin2hex(random_bytes(32));
            $w9Request->update(['encryption_key' => $encryptionKey]);
        } else {
            $encryptionKey = $w9Request->encryption_key;
        }
    
        $encrypt = function($value) use ($encryptionKey) {
            if (!$value) return null;
    
            return base64_encode(openssl_encrypt(
                $value,
                'aes-256-cbc',
                hash('sha256', $encryptionKey, true),
                OPENSSL_RAW_DATA,
                substr(hash('sha256', $encryptionKey), 0, 16)
            ));
        };
    
        $fileEncryptService = new FileEncryptionService($encryptionKey);
    
        switch ($step) {
            case 0:
                $w9Request->update([
                    'representative_of' => $request->input('company_id'),
                ]);
                break;
    
            case 1: 
                $request->validate([
                    'entity_name'        => 'required|string|max:255',
                    'other_entity_name'  => 'nullable|regex:/^[A-Za-z0-9 ]+$/',
                    'business_name'      => 'nullable|string|max:255',
                    'entity_type'        => 'required|string',
                    'tax_id_number'      => 'nullable|string|max:50',
                    'list_account_number'=> 'nullable|string|max:50',
                    'entity_specification'=> 'nullable|string|max:50',
                ]);
                $fields = [
                    'entity_name', 'business_name', 'entity_type', 'other_entity_name',
                    'tax_id_number', 'list_account_number','entity_specification'
                ];
        
                $encryptedData = [];
              foreach ($fields as $field) {
                    $encryptedData[$field] = $encrypt($request->input($field));
                }

            // Encrypt entity_type (store actual selected option)
            $encryptedData['entity_type'] = $encrypt($request->input('entity_type'));
        
            if ($request->input('entity_type') === 'Other') {
                $encryptedData['other_entity_name'] = $encrypt($request->input('other_entity_name'));
            }

                $encryptedData['other_entity'] = $request->input('entity_type') === 'Other' 
                    ? $encrypt($request->input('other_entity')) 
                    : null;

                $encryptedData['no_tax_id'] = $request->has('no_tax_id') ? 1 : 0;
                // $encryptedData['entity_type'] = $request->has('entity_type') ? $request->has('entity_type') : $request->has('other_entity');
                
                $encryptedData['no_foreign_beneficiaries'] = $request->has('no_foreign_beneficiaries') ? 1 : 0;
    
                $w9Request->update($encryptedData);
                break;
    
            case 2: 
                $request->validate([
                    'address'   => 'required|string|max:255',
                    'city'      => 'required|string|max:100',
                    'state'     => 'required|string|max:100',
                    'zip_code'  => 'required|string|max:5',
                ]);
                $fields = ['address_lookup', 'address', 'city', 'state', 'zip_code'];
                $encryptedData = [];
                foreach ($fields as $field) {
                    $encryptedData[$field] = $encrypt($request->input($field));
                }
                $w9Request->update($encryptedData);
                break;
    
            case 3: 
                $request->validate([
                    'signature' => 'required|string', 
                    'vendor_phone' => 'nullable|string'
                ]);
                if ($request->filled('signature')) {
            
                    $signatureData = preg_replace('#^data:image/\w+;base64,#i', '', $request->signature);
                    $signatureData = str_replace(' ', '+', $signatureData);
                    $imageBinary = base64_decode($signatureData);
            
                    if ($imageBinary === false) {
                        return back()->with('error', 'Invalid signature data');
                    }
            
                    $tempPath = tempnam(sys_get_temp_dir(), 'sig_') . '.png';
                    file_put_contents($tempPath, $imageBinary);
            
                    $encryptedFolder = public_path('upload/signatures');
                    if (!file_exists($encryptedFolder)) {
                        mkdir($encryptedFolder, 0755, true);
                    }
            
                    $fileName = 'signature_' . $w9Request->id . '_' . time() . '_' . uniqid() . '.enc';
                    $encryptedPath = $encryptedFolder . '/' . $fileName;
            
                    $fileEncryptService = new FileEncryptionService($w9Request->encryption_key);
                    $fileEncryptService->encryptFile($tempPath, $encryptedPath);
            
                    @unlink($tempPath);
            
                    $w9Request->update([
                        'signature' => 'upload/signatures/' . $fileName,
                        'status'     => 'completed',
                        'request_status' => 1
                    ]);
                }
            break;
        }
        
    
        if ($step == 3) {
            $w9Request->update(['request_status' => 0]);
        }
    
        $successMessage = $stepMessages[$step] ?? 'Step completed successfully!';
        return redirect()->route('w9_form', ['token' => $w9Request->token, 'step' => $step + 1])->with('success', $successMessage);
    }


    public function fw9(Request $request, $token)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'You are Not Authorized');
        }
    
        $w9 = Vendor::where('token', $token)->firstOrFail();
    
        if ($w9) {
            $w9->request_status = 1;
            $w9->submited_at = date('Y-m-d H:i:s');
            $w9->save();
        }
    
        $user = User::find($w9->vendor_contact_person);
        $userName = $user ? $user->first_name . ' ' . $user->last_name : null;
         $userPhone = $user->phone ?? '';
        $userEmail = $user->email ?? '';
      
        $device_name = $request->header('User-Agent');
         $botDetected = false;
                
        if (preg_match('/bot|crawl|spider|slurp|curl|wget|python|httpclient/i', $device_name)) {
            $botDetected = true;
        }
            
         if ($botDetected) {
            $botStatus = 'Failed'; 
            return redirect()->back()->with('error', 'Bot detected! PDF generation blocked.');
        } else {
            $botStatus = 'Passed'; 
        }
    
        $isMobile   = preg_match('/Mobile|Android|iPhone|iPad|iPod|Opera Mini|IEMobile/i', $device_name) ? 'Yes' : 'No';
        $browser    = (new General)->deviceName($device_name);
        $os         = (new General)->deviceName($device_name);
        $ipAddress  = (new General)->getClientIp();
        $accessedAt = now()->format('D, M d, Y h:i A');
      
        $verifiedSignedAt = $w9->created_at ? $w9->created_at->format('D, M d, Y h:i A') : '';
    
        $audit = [
            'accessed_at'        => $accessedAt,
            'browser'            => $browser,
            'os'                 => $os,
            'ip_address'         => $ipAddress,
            'verified_signed_at' => $w9->created_at,
        ];
    
        $decrypt = function($value, $key) {
            if (!$value) return null;
            $data = base64_decode($value);
            $iv = substr(hash('sha256', $key), 0, 16);
            return openssl_decrypt(
                $data,
                'aes-256-cbc',
                hash('sha256', $key, true),
                OPENSSL_RAW_DATA,
                $iv
            );
        };
    
        $serviceKey = $w9->encryption_key;
    
        $vendor_company_name      = $w9->vendor_company_name ?? '';
        $fullName      = $decrypt($w9->entity_name, $serviceKey) ?? '';
        $businessName  = !empty($w9->business_name) ? $decrypt($w9->business_name, $serviceKey) : '';
        $address       = $decrypt($w9->address, $serviceKey) ?? '';
        $list_account_number       = $decrypt($w9->list_account_number, $serviceKey) ?? '';
        $entity_type       = $decrypt($w9->entity_type, $serviceKey) ?? '';
        $entity_specification       = $decrypt($w9->entity_specification, $serviceKey) ?? '';
        $city          = $decrypt($w9->city, $serviceKey) ?? '';
        $state         = $decrypt($w9->state, $serviceKey) ?? '';
        $zip           = $decrypt($w9->zip_code, $serviceKey) ?? '';
        $ssn           = $decrypt($w9->tax_id_number, $serviceKey) ?? '';
    
        $nameParts = explode(' ', trim($fullName));
        $firstName = $nameParts[0] ?? '';
        $lastName  = $nameParts[1] ?? '';
    
        $ssnParts = explode('-', $ssn);
        
        $signatureBase64 = null;
        if (!empty($w9->signature)) {
            $signaturePath = public_path($w9->signature);
            if (file_exists($signaturePath)) {
                try {
                    $service = new FileEncryptionService($w9->encryption_key);
                    $tempSignature = tempnam(sys_get_temp_dir(), 'sig_') . '.png';
                    $service->decryptFile($signaturePath, $tempSignature);
                    $signatureBase64 = $tempSignature; 
                } catch (\Exception $e) {
                    $signatureBase64 = null;
                }
            }
        }
    
        $originalPdf  = public_path('upload/pdf/page1-2mixed.pdf');
        $convertedPdf = public_path('upload/pdf/fw9_fixed.pdf');
    
        $cmd = "gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/default "
             . "-dNOPAUSE -dQUIET -dBATCH -sOutputFile=\"$convertedPdf\" \"$originalPdf\"";
        exec($cmd);
    
        if (!file_exists($convertedPdf)) {
            return "PDF conversion failed. Install Ghostscript.";
        }
    
        $pdf = new \setasign\Fpdi\Fpdi();
        $pageCount = $pdf->setSourceFile($convertedPdf);
    
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
    
            $template = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($template);
    
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($template);
    
            if ($pageNo == 1) {
                $pdf->SetFont('Arial', '', 10);
                $pdf->SetTextColor(0, 0, 0);
    
                $pdf->SetXY(28, 40);
                $pdf->Write(8, $firstName . ' ' . $lastName);
    
                $pdf->SetXY(28, 56);
                $pdf->Write(8, $vendor_company_name);
    
                $pdf->SetXY(28, 64);
                $pdf->Write(8, $businessName);
    
                $pdf->SetXY(28, 111);
                $pdf->Write(8, $address);
                
                $pdf->SetXY(28, 127);
                $pdf->Write(8, $list_account_number);
    
                $pdf->SetXY(28, 119);
                $pdf->Write(8, "$city, $state $zip");
               
                if($entity_type == 'Individual/Sole Proprietor')
                {
                    $pdf->SetXY(28, 75);
                    $pdf->SetFont('ZapfDingbats', '', 14);
                    $pdf->Write(8, chr(51));  
                    
                }elseif($entity_type == 'Single-Member LLC'){
                    $pdf->SetXY(28, 75);
                    $pdf->SetFont('ZapfDingbats', '', 14);
                    $pdf->Write(8, chr(51)); 
                    
                }elseif($entity_type == 'Multi-Member LLC'){
                    $pdf->SetXY(28, 80);
                    $pdf->SetFont('ZapfDingbats', '', 14);
                    $pdf->Write(8, chr(51)); 
                      if ($entity_specification == 'C Corporation') {
    
                        $pdf->SetXY(143, 80);
                        $pdf->SetFont('Arial', '', 12);
                        $pdf->Write(8, 'C');
                
                    } elseif ($entity_specification == 'S Corporation') {
                
                        $pdf->SetXY(143, 80);
                        $pdf->SetFont('Arial', '', 12);
                        $pdf->Write(8, 'S');
                
                    } elseif ($entity_specification == 'Partnership') {
                
                        $pdf->SetXY(143, 80);
                        $pdf->SetFont('Arial', '', 12);
                        $pdf->Write(8, 'P');
                
                    }
                }elseif($entity_type == 'C Corporation'){
                     $pdf->SetXY(63, 75);
                    $pdf->SetFont('ZapfDingbats', '', 14);
                    $pdf->Write(8, chr(51)); 
                }elseif($entity_type == 'S Corporation'){
                     $pdf->SetXY(86, 75.5);
                    $pdf->SetFont('ZapfDingbats', '', 14);
                    $pdf->Write(8, chr(51));  
                }elseif($entity_type == 'Partnership'){
                     $pdf->SetXY(110, 75);
                    $pdf->SetFont('ZapfDingbats', '', 14);
                    $pdf->Write(8, chr(51));  
                }elseif($entity_type == 'Trust/Estate'){
                     $pdf->SetXY(131, 75);
                    $pdf->SetFont('ZapfDingbats', '', 14);
                    $pdf->Write(8, chr(51)); 
                }elseif($entity_type == 'other'){
                     $pdf->SetXY(28, 91);
                    $pdf->SetFont('ZapfDingbats', '', 14);
                    $pdf->Write(8, chr(51));  
                }else{
                    
                }
                
                if ($signatureBase64 && file_exists($signatureBase64)) {
                    $pdf->Image($signatureBase64, 50, 209, 20,5); 
                }
                
                $pdf->SetFont('Arial', '', 10);
                $pdf->SetXY(137, 209);
                $pdf->Write(8, $w9->created_at->format('M d, Y h:i A'));
    
                $entity_type = $entity_type ?? '';  
                $taxIdNumber = $ssn ?? ''; 
                
                function writeNumberToPdf($pdf, $number, $positions) {
                    $digits = str_split($number);
                    foreach ($digits as $i => $digit) {
                        if (!isset($positions[$i])) break;
                        $pdf->SetFont('Arial', 'B', 10);
                        $pdf->SetXY($positions[$i]['x'], $positions[$i]['y']);
                        $pdf->Write(8, $digit);
                    }
                }
                
                $entityType = trim($entity_type);
                $taxIdNumber = preg_replace('/[^0-9]/', '', $ssn);
                
                // Only Individual / Sole Proprietor uses SSN
                $isSSN = in_array($entityType, [
                    'Individual / Sole Proprietor',
                    'Individual/Sole Proprietor'
                ]);
                
                if ($isSSN) {
                
                    // =========================
                    // SSN
                    // =========================
                    if (empty($taxIdNumber)) {
                
                        $pdf->SetFont('Arial', '', 20);
                        $pdf->SetXY(140, 140);
                        $pdf->Write(10, 'APPLIED FOR');
                
                    } else {
                
                        $ssnPositions = [
                            ['x' => 141.5, 'y' => 141],
                            ['x' => 146.5, 'y' => 141],
                            ['x' => 151.5, 'y' => 141],
                            ['x' => 160.5, 'y' => 141],
                            ['x' => 165.5, 'y' => 141],
                            ['x' => 175.0, 'y' => 141],
                            ['x' => 179.5, 'y' => 141],
                            ['x' => 184.5, 'y' => 141],
                            ['x' => 188.5, 'y' => 141],
                        ];
                
                        writeNumberToPdf($pdf, $taxIdNumber, $ssnPositions);
                    }
                
                } else {
                
                    // =========================
                    // EIN
                    // =========================
                    if (empty($taxIdNumber)) {
                
                        $pdf->SetFont('Arial', '', 20);
                        $pdf->SetXY(141, 156);
                        $pdf->Write(10, 'APPLIED FOR');
                
                    } else {
                
                        $einPositions = [
                            ['x' => 141.5, 'y' => 156],
                            ['x' => 146.5, 'y' => 156],
                            ['x' => 155.5, 'y' => 156],
                            ['x' => 160.5, 'y' => 156],
                            ['x' => 165.0, 'y' => 156],
                            ['x' => 170.0, 'y' => 156],
                            ['x' => 174.5, 'y' => 156],
                            ['x' => 179.5, 'y' => 156],
                            ['x' => 184.5, 'y' => 156],
                        ];
                
                        $digits = str_split($taxIdNumber);
                
                        foreach ($digits as $i => $digit) {
                
                            if (!isset($einPositions[$i])) {
                                break;
                            }
                
                            $pdf->SetFont('Arial', 'B', 10);
                            $pdf->SetXY($einPositions[$i]['x'], $einPositions[$i]['y']);
                            $pdf->Write(8, $digit);
                
                            // Insert dash after first two digits (XX-XXXXXXX)
                            if ($i == 1) {
                                $pdf->SetXY($einPositions[$i]['x'] + 5, $einPositions[$i]['y']);
                                $pdf->Write(8, '-');
                            }
                        }
                    }
                }
    
            }
           
            if ($pageNo == 2) {
                $pdf->SetFont('Arial', '', 10);
                $pdf->SetTextColor(0, 0, 0);
    
                $pdf->SetXY(55, 40.5);
                $pdf->Write(30, "$accessedAt");
    
                $pdf->SetXY(31.5, 47.7);
                $pdf->Write(34, "$browser");
    
                $pdf->SetXY(40, 56.4);
                $pdf->Write(8, "$os");
                
                $pdf->SetXY(40, 54.8);
                $pdf->Write(30, "$botStatus");
                
                $pdf->SetXY(41, 59.3);
                $pdf->Write(30, "$isMobile");
    
                $pdf->SetXY(44.6, 64);
                $pdf->Write(30, "$ipAddress");
                
                $pdf->SetXY(43, 64.3);
                $pdf->Write(38, "w9-$w9->id");
                
                $pdf->SetXY(51, 69);
                $pdf->Write(38, "$verifiedSignedAt");
                
                $pdf->SetXY(52, 73.6);
                $pdf->Write(38, "$userName");
                
                $pdf->SetXY(40.5, 82.6);
                $pdf->Write(30, "$w9->vendor_phone");
    
                $pdf->SetXY(39.5, 87);
                $pdf->Write(30, "$userEmail");
            }
        }
    
        $editedPdf = public_path('upload/pdf/edited_w9.pdf');
        $pdf->Output($editedPdf, 'F');
    
        return response()->file($editedPdf, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}