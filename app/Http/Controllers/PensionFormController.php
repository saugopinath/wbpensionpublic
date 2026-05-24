<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\EncryptDecrypt;
use App\Helpers\TokenValidation;

use App\Services\SendSmsService;
use Tymon\JWTAuth\Facades\JWTFactory;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\UniqueAppBenId;
use App\Models\BeneficiaryPersonal;
use App\Models\BeneficiaryAadhaar;
use App\Models\BeneficiaryContact;
use App\Models\BeneficiaryEnclosure;
use App\Models\BeneficiarySelfDeclaration;
use App\Models\District;
use App\Models\Municipality;
use App\Models\Ward;
use App\Models\Block;
use App\Models\Codemaster;
use App\Models\Panchayat;
use App\Models\Ifsccodemaster;
use App\Models\BeneficiaryBank;
use App\Models\SchemeAttachedDocMappings;
use App\Models\AcceptRejectInfo;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use App\Http\Requests\StorePersonalRequest;
use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\StoreBankRequest;
use App\Http\Requests\StoreDeclarationRequest;
use App\Http\Requests\StoreEncloserRequest;
use App\Http\Requests\StoreFamilyRequest;
use App\Models\BeneficiaryFamilyDetail;
use Config;
use Illuminate\Database\Eloquent\Collection;
use App\Jobs\PersonaEntryJob;
use App\Jobs\AadhaarEntryJob;
use App\Jobs\ContactEntryJob;
use App\Jobs\BankEntryJob;
use App\Jobs\DeclarationEntryJob;
use App\Jobs\EnCloserEntryJob;
use App\Jobs\AcceptrejectInfoEntryJob;
use App\Jobs\FamilyEntryJob;
use Illuminate\Support\Facades\Log;

class PensionFormController extends Controller
{
    public function __construct(protected SendSmsService $sendsmsService) {}
    // public function personalEntry(StorePersonalRequest $request)
    // {

    //   try{
    //     $validated = $request->validated();
    //     $request_data = EncryptDecrypt::decrypt($request->data);
    //     $mobile_no=$request_data['extraData']['mobile_no'];
    //     $scheme_id=$request_data['extraData']['scheme_id'];
    //     $add_edit_status=$request_data['formData']['add_edit_status'];

    //          if(!TokenValidation::mobileNoValidation($mobile_no)){
    //              $errorMsg =  __('messages.mobilenoinvalid');
    //             return response()->json(["is_success" => false,'error' => $errorMsg]);
    //          }
    //          $scheme_id=$request_data['extraData']['scheme_id'];
    //          //dd($this->schemeValidation($scheme_id));
    //          if(!TokenValidation::schemeValidation($scheme_id)){
    //              $errorMsg =  __('messages.mobilenoinvalid');
    //             return response()->json(["is_success" => false,'error' => $errorMsg]);
    //          }
    //     $token_valid=TokenValidation::checkTokenMobileScheme($request_data,$request);
    //      if(!$token_valid){
    //              $errorMsg =  __('messages.invalidToken');
    //             return response()->json(["is_success" => false,'error' => $errorMsg]);
    //          }
    //          $token_expire=TokenValidation::checTokenExpireTime($request_data,$request);
    //         //dd($token_expire);
    //          if(!$token_expire){
    //              $errorMsg =  __('messages.invalidOtp');
    //             return response()->json(["is_success" => false,'error' => $errorMsg]);
    //          }
    //     $token= request()->bearerToken();
    //     $claims = JWTAuth::getJWTProvider()->decode(base64_decode($token));
    //     $decrpt_sub=EncryptDecrypt::decrypt(base64_decode($claims['sub']));
    //       $application_id = $request_data['formData']['application_id'] ?? null;
    //       if($add_edit_status==1 || !empty($application_id)){
    //          if (empty($application_id)) {
    //            $errorMsg = __('messages.invaliddata');
    //           return response()->json(["is_success" => false,'error' => $errorMsg]);
    //         }
    //         $pension_details=BeneficiaryPersonal::where('scheme_id',$scheme_id)->where('application_id',$application_id)->first();
    //          if (!$pension_details) {
    //              $pension_details = new BeneficiaryPersonal();
    //              $pension_details->application_id = $application_id;
    //          }
    //         if (config('app.queue_enable')) {
    //             $pension_details->add_edit_status= 1;
    //         }

    //        }
    //        else{
    //         $application_id = Str::uuid()->toString();
    //         $beneficiary_id = Str::uuid()->toString();
    //          $pension_details = new BeneficiaryPersonal();
    //          $pension_details->scheme_id = $scheme_id;
    //          if (config('app.queue_enable')) {
    //              $pension_details->add_edit_status = 0;
    //          }
    //         $unquie_ben_id_obj=new UniqueAppBenId();
    //         $unquie_ben_id_obj->scheme_id= $scheme_id;
    //         $unquie_ben_id_obj->application_id=$application_id;
    //         $unquie_ben_id_obj->beneficiary_id=$beneficiary_id;
    //         $pension_details->application_id=$application_id;
    //        }

    //         $female_code_obj=Codemaster::where('code',52)->first();

    //         DB::beginTransaction();

    //         $is_saved = 0;
    //         try {

    //             if($add_edit_status==1){
    //             $is_saved_unqie = 1;
    //             }
    //             else{
    //                 $is_saved_unqie = $unquie_ben_id_obj->save();
    //             }
    //             if($is_saved_unqie){
    //             $pension_details->scheme_id= $scheme_id;
    //             $pension_details->application_date= Carbon::now()->format('Y-m-d H:i:s');   
    //             $pension_details->beneficiary_name= trim($validated['beneficiary_name']);
    //             $pension_details->gender= $female_code_obj->id;
    //             $pension_details->dob= $request->dob;
    //             $pension_details->father_fname= trim($validated['father_first_name']);
    //             if(!empty($validated['father_middle_name'])){
    //             $pension_details->father_mname= trim($validated['father_middle_name']);
    //             }
    //             if(!empty($validated['father_last_name'])){
    //             $pension_details->father_lname= trim($validated['father_last_name']);
    //             }
    //             $pension_details->mother_fname= trim($validated['mother_first_name']);
    //             if(!empty($validated['mother_middle_name'])){
    //             $pension_details->mother_mname= trim($validated['mother_middle_name']);
    //             }
    //             if(!empty($validated['mother_last_name'])){
    //             $pension_details->mother_lname= trim($validated['mother_last_name']);
    //             }
    //             $pension_details->caste= trim($validated['caste_category']);
    //             if(!empty($validated['caste_certificate_no'])){
    //             $pension_details->caste_certificate_no= trim($validated['caste_certificate_no']);
    //             }
    //             $pension_details->aadhar_no= '********' . substr($validated['aadhar_no'], -4);
    //             $pension_details->mobile_no= $validated['ben_mobile_no'];
    //             if(!empty($validated['email'])){
    //             $pension_details->email= $validated['email'];
    //             }
    //             if(!empty($validated['spouse_first_name'])){
    //             $pension_details->spouse_fname= trim($validated['spouse_first_name']);
    //             }
    //             if(!empty($validated['spouse_middle_name'])){
    //             $pension_details->spouse_mname= trim($validated['spouse_middle_name']);
    //             }
    //             if(!empty($validated['spouse_last_name'])){
    //             $pension_details->spouse_lname= trim($validated['spouse_last_name']);
    //             }
    //             $pension_details->ip_address= $request->ip();
    //             $pension_details->otp_validation_id= $decrpt_sub;
    //             $pension_details->is_clean= 2;
    //             if (config('app.queue_enable')) {
    //               $is_saved = PersonaEntryJob::dispatch($pension_details);
    //            }
    //            else{
    //             $is_saved = $pension_details->save();
    //             }
    //             if($is_saved){
    //                   $pension_details_aadhar = BeneficiaryAadhaar::where('scheme_id',$scheme_id)->where('application_id',$pension_details->application_id)->first();
    //                   if (!$pension_details_aadhar) {
    //                       $pension_details_aadhar = new BeneficiaryAadhaar();
    //                   }
    //               $pension_details_aadhar->scheme_id= $scheme_id;

    //               $pension_details_aadhar->encoded_aadhar = Crypt::encryptString($validated['aadhar_no']);
    //               $pension_details_aadhar->aadhar_hash = md5($validated['aadhar_no']);
    //               $pension_details_aadhar->application_id =   $pension_details->application_id;
    //               $pension_details_aadhar->otp_validation_id= $decrpt_sub;
    //               $pension_details_aadhar->is_clean= 2;
    //               if (config('app.queue_enable')) {
    //                   $is_saved_aadhar = AadhaarEntryJob::dispatch($pension_details_aadhar);
    //               }else{
    //               $is_saved_aadhar = $pension_details_aadhar->save();
    //               }
    //                if (config('app.queue_enable')) {
    //                  $AcceptRejectInfo = new AcceptRejectInfo();
    //                }
    //               else{
    //               $AcceptRejectInfo = new AcceptRejectInfo;
    //               }
    //               $AcceptRejectInfo->application_id = $pension_details->application_id;
    //               $AcceptRejectInfo->ip_address = request()->ip();
    //               $AcceptRejectInfo->browser = request()->header('User-Agent');
    //               $AcceptRejectInfo->model_name = null;
    //               $AcceptRejectInfo->op_type = Codemaster::getIdByCode('21101');
    //               if (config('app.queue_enable')) {
    //                 $accpt_reject_save = AcceptrejectInfoEntryJob::dispatch($AcceptRejectInfo);
    //               }
    //               else{
    //               $accpt_reject_save = $AcceptRejectInfo->save();
    //               }
    //               if($is_saved_aadhar &&  $accpt_reject_save){
    //                  DB::commit();
    //                 return response()->json(["is_success" => true,'temp_application_id' => base64_encode(EncryptDecrypt::encrypt((string) $pension_details->application_id))]);

    //               }
    //               else{
    //                  DB::rollBack();
    //                  $errorMsg = __('messages.dbroolback');
    //                  return response()->json(["is_success" => false,'error' => $errorMsg]);
    //               }
    //             }
    //             else{
    //                  DB::rollBack();
    //                  $errorMsg = __('messages.dbroolback');
    //                  return response()->json(["is_success" => false,'error' => $errorMsg]);
    //             }
    //             }
    //             else{
    //                  DB::rollBack();
    //                  $errorMsg = __('messages.dbroolback');
    //                  return response()->json(["is_success" => false,'error' => $errorMsg]);
    //             }
    //         }
    //         catch (\Exception $e) {
    //         dd($e);
    //             $errorMsg = __('messages.dbroolback');
    //             return response()->json(["is_success" => false,'error' => $errorMsg]);
    //     }
    //    }
    //   catch (\Exception $e) {
    //         dd($e);
    //             $errorMsg = __('messages.dbroolback');
    //             return response()->json(["is_success" => false,'error' => $errorMsg]);
    //     }
    //     //return response()->json(['return_status' => $return_status, 'application_id' => $application_id, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code,'session_lb_lifecertificate' => $session_lb_lifecertificate,'session_lb_castecertificate' => $session_lb_castecertificate,'session_lb_aadhaar_no'=> $session_lb_aadhaar_no]);
    // }


    public function personalEntry(StorePersonalRequest $request)
    {
        DB::beginTransaction();

        try {
            // VALIDATION
            $validated = $request->validated();
            $request_data = EncryptDecrypt::decrypt($request->data);
            $mobile_no = $request_data['extraData']['mobile_no'];
            $scheme_id = $request_data['extraData']['scheme_id'];
            $add_edit_status = $request_data['formData']['add_edit_status'] ?? 0;

            // MOBILE VALIDATION
            if (!TokenValidation::mobileNoValidation($mobile_no)) {
                return response()->json([
                    "is_success" => false,
                    'error' => __('messages.mobilenoinvalid')
                ]);
            }
            // SCHEME VALIDATION
            if (!TokenValidation::schemeValidation($scheme_id)) {

                return response()->json([
                    "is_success" => false,
                    'error' => __('messages.invalidscheme')
                ]);
            }
            // TOKEN VALIDATION
            $token_valid = TokenValidation::checkTokenMobileScheme(
                $request_data,
                $request
            );
            if (!$token_valid) {
                return response()->json([
                    "is_success" => false,
                    'error' => __('messages.invalidToken')
                ]);
            }
            // TOKEN EXPIRE CHECK
            $token_expire = TokenValidation::checTokenExpireTime(
                $request_data,
                $request
            );
            if (!$token_expire) {
                return response()->json([
                    "is_success" => false,
                    'error' => __('messages.invalidOtp')
                ]);
            }
            // JWT DATA
            $token = request()->bearerToken();
            $claims = JWTAuth::getJWTProvider()->decode(base64_decode($token));
            $decrpt_sub = EncryptDecrypt::decrypt(base64_decode($claims['sub']));
            // APPLICATION ID
            $application_id = $request_data['formData']['application_id'] ?? null;
            // EDIT CHECK
            if ($add_edit_status == 1 && empty($application_id)) {
                return response()->json([
                    "is_success" => false,
                    'error' => __('messages.invaliddata')
                ]);
            }
            // NEW APPLICATION
            if (empty($application_id)) {
                $application_id = Str::uuid()->toString();
                $beneficiary_id = Str::uuid()->toString();
                $uniqueSaved = UniqueAppBenId::create([
                    'scheme_id' => $scheme_id,
                    'application_id' => $application_id,
                    'beneficiary_id' => $beneficiary_id,
                ]);
                if (!$uniqueSaved) {
                    DB::rollBack();
                    return response()->json([
                        "is_success" => false,
                        'error' => 'Application creation failed'
                    ]);
                }
            }
            // COMMON DATA
            $female_code_obj = Codemaster::where('code', 52)->first();
            // PERSONAL PAYLOAD
            $personalPayload = [
                'scheme_id' => $scheme_id,
                'application_id' => $application_id,
                'application_date' => Carbon::now()->format('Y-m-d H:i:s'),
                'beneficiary_name' => trim($validated['beneficiary_name']),
                'gender' => $female_code_obj->id,
                'dob' => $validated['dob'],
                'father_fname' => trim($validated['father_first_name']),
                'father_mname' => trim($validated['father_middle_name'] ?? ''),
                'father_lname' => trim($validated['father_last_name'] ?? ''),
                'mother_fname' => trim($validated['mother_first_name']),
                'mother_mname' => trim($validated['mother_middle_name'] ?? ''),
                'mother_lname' => trim($validated['mother_last_name'] ?? ''),
                'caste' => trim($validated['caste_category']),
                'caste_certificate_no' => $validated['caste_certificate_no'] ?? null,
                'aadhar_no' => '********' . substr($validated['aadhar_no'], -4),
                'mobile_no' => $validated['ben_mobile_no'],
                'email' => $validated['email'] ?? null,
                'spouse_fname' => trim($validated['spouse_first_name'] ?? ''),
                'spouse_mname' => trim($validated['spouse_middle_name'] ?? ''),
                'spouse_lname' => trim($validated['spouse_last_name'] ?? ''),
                'ip_address' => $request->ip(),
                'otp_validation_id' => $decrpt_sub,
                'is_clean' => 2,
                'ration_card_no' => !empty($validated['ration_card_no']) ? trim($validated['ration_card_no']) : null,
                'epic_card_no' => trim($validated['epic_card_no']),
                'pan_no' => trim($validated['pan_no']),
                'is_taxpayer' => trim($validated['is_taxpayer']),
                'has_pan_card' => $validated['has_pan_card'],
                'marital_status' => $validated['marital_status'] ?? null,
            ];
            // AADHAAR PAYLOAD
            $aadhaarPayload = [
                'scheme_id' => $scheme_id,
                'application_id' => $application_id,
                'encoded_aadhar' => Crypt::encryptString($validated['aadhar_no']),
                'aadhar_hash' => md5($validated['aadhar_no']),
                'otp_validation_id' => $decrpt_sub,
                'is_clean' => 2,
            ];
            // ACCEPT REJECT PAYLOAD
            $acceptRejectPayload = [
                'application_id' => $application_id,
                'ip_address' => request()->ip(),
                'browser' => request()->header('User-Agent'),
                'model_name' => null,
                'op_type' => Codemaster::getIdByCode('21101'),
            ];
            // PERSONAL SAVE
            if (config('app.queue_enable')) {
                try {
                    PersonaEntryJob::dispatch($personalPayload);
                    $personalSaved = true;
                } catch (\Exception $e) {
                    Log::error('Personal details save failed: ' . $e->getMessage());
                    DB::rollBack();
                    return response()->json([
                        "is_success" => false,
                        'error' => 'Personal details save failed'
                    ]);
                }
            } else {
                $personalSaved = BeneficiaryPersonal::updateOrCreate(
                    [
                        'scheme_id' => $scheme_id,
                        'application_id' => $application_id,
                    ],
                    $personalPayload
                );
            }
            if (!$personalSaved) {
                Log::error('Personal details save failed');
                DB::rollBack();
                return response()->json([
                    "is_success" => false,
                    'error' => 'Personal details save failed'
                ]);
            }
            // AADHAAR SAVE
            if (config('app.queue_enable')) {
                try {
                    AadhaarEntryJob::dispatch($aadhaarPayload);
                    $aadhaarSaved = true;
                } catch (\Exception $e) {
                    Log::error('Aadhaar save failed: ' . $e->getMessage());
                    DB::rollBack();
                    return response()->json([
                        "is_success" => false,
                        'error' => 'Aadhaar save failed'
                    ]);
                }
            } else {
                $aadhaarSaved =
                    BeneficiaryAadhaar::updateOrCreate(
                        [
                            'scheme_id' => $scheme_id,
                            'application_id' => $application_id,
                        ],
                        $aadhaarPayload
                    );
            }
            if (!$aadhaarSaved) {
                DB::rollBack();
                return response()->json([
                    "is_success" => false,
                    'error' => 'Aadhaar save failed'
                ]);
            }
            // ACCEPT REJECT INSERT
            if (config('app.queue_enable')) {
                try {
                    AcceptrejectInfoEntryJob::dispatch($acceptRejectPayload);
                    $acceptSaved = true;
                } catch (\Exception $e) {
                    Log::error('Accept reject save failed: ' . $e->getMessage());
                    DB::rollBack();
                    return response()->json([
                        "is_success" => false,
                        'error' => 'Accept reject save failed'
                    ]);
                }
            } else {
                $acceptSaved =
                    AcceptRejectInfo::create(
                        $acceptRejectPayload
                    );
            }
            if (!$acceptSaved) {
                DB::rollBack();
                return response()->json([
                    "is_success" => false,
                    'error' => 'Accept reject save failed'
                ]);
            }
            // COMMIT
            DB::commit();
            return response()->json([
                "is_success" => true,
                'temp_application_id' => base64_encode(EncryptDecrypt::encrypt((string)$application_id))
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "is_success" => false,
                'error' => $e->getMessage()
            ]);
        }
    }



    public function contactEntry(StoreContactRequest $request)
    {
        try {
            $validated = $request->validated();
            $request_data = EncryptDecrypt::decrypt($request->data);
            $mobile_no = $request_data['extraData']['mobile_no'];
            $scheme_id = $request_data['extraData']['scheme_id'];
            $application_id = $request_data['formData']['application_id'];
            $add_edit_status = $request_data['formData']['add_edit_status'];

            if (!TokenValidation::mobileNoValidation($mobile_no)) {
                $errorMsg =  __('messages.mobilenoinvalid');
                return response()->json(["is_success" => false, 'error' => $errorMsg]);
            }
            $scheme_id = $request_data['extraData']['scheme_id'];
            if (!TokenValidation::schemeValidation($scheme_id)) {
                $errorMsg =  __('messages.mobilenoinvalid');
                return response()->json(["is_success" => false, 'error' => $errorMsg]);
            }
            $token_valid = TokenValidation::checkTokenMobileScheme($request_data, $request);
            if (!$token_valid) {
                $errorMsg =  __('messages.invalidToken');
                return response()->json(["is_success" => false, 'error' => $errorMsg]);
            }
            $token_expire = TokenValidation::checTokenExpireTime($request_data, $request);
            // dd($token_expire);
            if (!$token_expire) {
                $errorMsg =  __('messages.invalidOtp');
                return response()->json(["is_success" => false, 'error' => $errorMsg]);
            }
            $district_list = District::all();
            $token = request()->bearerToken();
            $claims = JWTAuth::getJWTProvider()->decode(base64_decode($token));
            $decrpt_sub = EncryptDecrypt::decrypt(base64_decode($claims['sub']));
            $sel_district = $validated['district'];
            $cnt = $district_list->where('id', $sel_district)->count();
            if ($cnt == 0) {
                $return_text = __('messages.districtinvalid');
                return response()->json(["is_success" => false, 'error' => $return_text]);
            }
            $sel_urban_code =  $validated['urban_code'];
            $sel_block =  $validated['block_muncipality'];
            $sel_gp_ward =  $validated['gp_ward'];
            if ($sel_urban_code == 1) {
                $cnt1 = Municipality::where('district_id', $sel_district)->where('id', $sel_block)->count();
                if ($cnt1 == 0) {
                    $return_text = __('messages.contactinvalid');
                    return response()->json(["is_success" => false, 'error' => $return_text]);
                }
                $cnt2 = Ward::where('municipality_id', $sel_block)->where('id', $sel_gp_ward)->count();
                if ($cnt2 == 0) {
                    $return_status = 0;
                    $return_text = __('messages.gpwardinvalid');
                    return response()->json(["is_success" => false, 'error' => $return_text]);
                }
            } else if ($sel_urban_code == 2) {
                $cnt1 = Block::where('district_id', $sel_district)->where('id', $sel_block)->count();
                if ($cnt1 == 0) {
                    $return_text = __('messages.contactinvalid');
                    return response()->json(["is_success" => false, 'error' => $return_text]);
                }
                $cnt2 = Panchayat::where('block_id', $sel_block)->where('id', $sel_gp_ward)->count();
                if ($cnt2 == 0) {
                    $return_text = __('messages.gpwardinvalid');
                    return response()->json(["is_success" => false, 'error' => $return_text]);
                }
            } else {
                $return_text = __('messages.invaliddata');
                return response()->json(["is_success" => false, 'error' => $return_text]);
            }

            if ($validated['urban_code'] == 1) {
                $block_ulb = Municipality::where('id', $validated['block_muncipality'])->first();
                $blockulbCode = $block_ulb->subdivision_id;
            } else {
                $block_ulb = Block::where('id', $validated['block_muncipality'])->first();
                $blockulbCode = $block_ulb->block_ulb;
            }

            $contactPayload = [
                'scheme_id' => $scheme_id,
                'application_id' => $application_id,
                'is_clean' => 2,
                'otp_validation_id' => $decrpt_sub,
                'district_id' => $validated['district'],
                'rural_urban' => $validated['urban_code'],
                'policestation' => trim($validated['police_station']),
                'blockurban' => $validated['block_muncipality'],
                'gpward' => $validated['gp_ward'],
                'assembly' => $validated['assembly'],
                'village_town_city' => trim($validated['village_town_city']),
                'house_premise_no' => trim($validated['house_premise_no']),
                'post_office' => trim($validated['post_office']),
                'pincode' => trim($validated['pin_code']),
                'ip_address' => $request->ip(),
                'created_by_dist_code' => $validated['district'],
                'created_by_local_body_code' => $blockulbCode,
            ];

            $acceptRejectPayload = [
                'application_id' => $application_id,
                'ip_address' => request()->ip(),
                'browser' => request()->header('User-Agent'),
                'model_name' => null,
                'op_type' => Codemaster::getIdByCode('21102'),
            ];

            DB::beginTransaction();
            $is_saved = 0;
            try {
                if (config('app.queue_enable')) {
                    try {
                        ContactEntryJob::dispatch($contactPayload);
                        $is_saved = true;
                    } catch (\Exception $e) {
                        Log::error('Contact details queue failed: ' . $e->getMessage());
                        $is_saved = false;
                    }
                } else {
                    $is_saved = BeneficiaryContact::updateOrCreate(
                        [
                            'scheme_id' => $scheme_id,
                            'application_id' => $application_id,
                        ],
                        $contactPayload
                    );
                }

                if (config('app.queue_enable')) {
                    try {
                        AcceptrejectInfoEntryJob::dispatch($acceptRejectPayload);
                        $accpt_reject_save = true;
                    } catch (\Exception $e) {
                        Log::error('Accept reject queue failed: ' . $e->getMessage());
                        $accpt_reject_save = false;
                    }
                } else {
                    $accpt_reject_save = AcceptRejectInfo::create($acceptRejectPayload);
                }
                if ($is_saved &&  $accpt_reject_save) {

                    DB::commit();
                    return response()->json(["is_success" => true, 'temp_application_id' => base64_encode(EncryptDecrypt::encrypt((string) $application_id))]);
                } else {
                    DB::rollback();
                    $errorMsg = __('messages.dbroolback');
                    return response()->json(["is_success" => false, 'error' => $errorMsg]);
                }
            } catch (\Exception $e) {
                dd($e);
                DB::rollback();
                $errorMsg = __('messages.dbroolback');
                return response()->json(["is_success" => false, 'error' => $errorMsg]);
            }
        } catch (\Exception $e) {
            dd($e);
            $errorMsg = __('messages.invaliddata');
            return response()->json(["is_success" => false, 'error' => $errorMsg]);
        }
    }
    public function bankEntry(StoreBankRequest $request)
    {
        try {
            $validated = $request->validated();
            //dd($validated);
            $request_data = EncryptDecrypt::decrypt($request->data);
            $mobile_no = $request_data['extraData']['mobile_no'];
            $scheme_id = $request_data['extraData']['scheme_id'];
            $application_id = $request_data['formData']['application_id'];
            $add_edit_status = $request_data['formData']['add_edit_status'];
            if (!TokenValidation::mobileNoValidation($mobile_no)) {
                $errorMsg =  __('messages.mobilenoinvalid');
                return response()->json(["is_success" => false, 'error' => $errorMsg]);
            }
            $scheme_id = $request_data['extraData']['scheme_id'];
            //dd($this->schemeValidation($scheme_id));
            if (!TokenValidation::schemeValidation($scheme_id)) {
                $errorMsg =  __('messages.mobilenoinvalid');
                return response()->json(["is_success" => false, 'error' => $errorMsg]);
            }
            $token_valid = TokenValidation::checkTokenMobileScheme($request_data, $request);
            if (!$token_valid) {
                $errorMsg =  __('messages.invalidToken');
                return response()->json(["is_success" => false, 'error' => $errorMsg]);
            }
            $token_expire = TokenValidation::checTokenExpireTime($request_data, $request);
            // dd($token_expire);
            if (!$token_expire) {
                $errorMsg =  __('messages.invalidOtp');
                return response()->json(["is_success" => false, 'error' => $errorMsg]);
            }
            $district_list = District::all();
            $token = request()->bearerToken();
            $claims = JWTAuth::getJWTProvider()->decode(base64_decode($token));
            $decrpt_sub = EncryptDecrypt::decrypt(base64_decode($claims['sub']));
            $ifsc = trim($request->bank_ifsc_code);

            $row_count1 = Ifsccodemaster::where('is_active', 1)->where('code', $ifsc)->count();
            if ($row_count1 == 0) {

                $errorMsg = __('messages.ifscnotvalid');
                return response()->json(["is_success" => false, 'error' => $errorMsg]);
            }


            $bankPayload = [
                'scheme_id' => $scheme_id,
                'application_id' => $application_id,
                'is_clean' => 2,
                'otp_validation_id' => $decrpt_sub,
                'bankaccountnumber' => trim($request->bank_account_number),
                'ifscode' => trim($request->bank_ifsc_code),
                'ip_address' => $request->ip(),
            ];

            $acceptRejectPayload = [
                'application_id' => $application_id,
                'ip_address' => request()->ip(),
                'browser' => request()->header('User-Agent'),
                'model_name' => null,
                'op_type' => Codemaster::getIdByCode('21103'),
            ];

            DB::beginTransaction();
            $is_saved = 0;
            try {
                if (config('app.queue_enable')) {
                    try {
                        BankEntryJob::dispatch($bankPayload);
                        $is_saved = true;
                    } catch (\Exception $e) {
                        Log::error('Bank details queue failed: ' . $e->getMessage());
                        $is_saved = false;
                    }
                } else {
                    $is_saved = BeneficiaryBank::updateOrCreate(
                        [
                            'scheme_id' => $scheme_id,
                            'application_id' => $application_id,
                        ],
                        $bankPayload
                    );
                }

                if (config('app.queue_enable')) {
                    try {
                        AcceptrejectInfoEntryJob::dispatch($acceptRejectPayload);
                        $accpt_reject_save = true;
                    } catch (\Exception $e) {
                        Log::error('Accept reject queue failed: ' . $e->getMessage());
                        $accpt_reject_save = false;
                    }
                } else {
                    $accpt_reject_save = AcceptRejectInfo::create($acceptRejectPayload);
                }
                if ($is_saved && $accpt_reject_save) {
                    DB::commit();
                    return response()->json(["is_success" => true, 'temp_application_id' => base64_encode(EncryptDecrypt::encrypt((string) $application_id))]);
                }
            } catch (\Exception $e) {
                dd($e);
                DB::rollback();
                $errorMsg = __('messages.dbroolback');
                return response()->json(["is_success" => false, 'error' => $errorMsg]);
            }
        } catch (\Exception $e) {
            dd($e);
            $errorMsg = __('messages.invaliddata');
            return response()->json(["is_success" => false, 'error' => $errorMsg]);
        }
    }
    public function familyEntry(StoreFamilyRequest $request)
    {
        try {
            $validated = $request->validated();
            $request_data = EncryptDecrypt::decrypt($request->data);
            $mobile_no = $request_data['extraData']['mobile_no'];
            $scheme_id = $request_data['extraData']['scheme_id'];
            $application_id = $request_data['formData']['application_id'];
            $family_members = $request_data['formData']['family_members'];

            if (!TokenValidation::mobileNoValidation($mobile_no)) {
                $errorMsg =  __('messages.mobilenoinvalid');
                return response()->json(["is_success" => false, 'error' => $errorMsg]);
            }
            if (!TokenValidation::schemeValidation($scheme_id)) {
                $errorMsg =  __('messages.mobilenoinvalid');
                return response()->json(["is_success" => false, 'error' => $errorMsg]);
            }
            $token_valid = TokenValidation::checkTokenMobileScheme($request_data, $request);
            if (!$token_valid) {
                $errorMsg =  __('messages.invalidToken');
                return response()->json(["is_success" => false, 'error' => $errorMsg]);
            }
            $token_expire = TokenValidation::checTokenExpireTime($request_data, $request);
            if (!$token_expire) {
                $errorMsg =  __('messages.invalidOtp');
                return response()->json(["is_success" => false, 'error' => $errorMsg]);
            }

            $personal = BeneficiaryPersonal::where('scheme_id', $scheme_id)
                ->where('application_id', $application_id)
                ->first();

            if (!$personal) {
                return response()->json(["is_success" => false, 'error' => 'Personal details not found for this application. Please complete Step 1 first.']);
            }

            // Get JWT data for otp_validation_id
            $token = request()->bearerToken();
            $claims = JWTAuth::getJWTProvider()->decode(base64_decode($token));
            $decrpt_sub = EncryptDecrypt::decrypt(base64_decode($claims['sub']));

            $familyPayload = [
                'scheme_id' => $scheme_id,
                'application_id' => $application_id,
                'family_members' => $family_members,
                'otp_validation_id' => $decrpt_sub,
                'ip_address' => $request->ip(),
                'is_clean' => 2,
            ];

            $acceptRejectPayload = [
                'application_id' => $application_id,
                'ip_address' => request()->ip(),
                'browser' => request()->header('User-Agent'),
                'model_name' => null,
                'op_type' => Codemaster::getIdByCode('21106') ?? 21106,
            ];

            DB::beginTransaction();
            $is_saved = 0;
            try {
                if (config('app.queue_enable')) {
                    try {
                        FamilyEntryJob::dispatch($familyPayload);
                        $is_saved = true;
                    } catch (\Exception $e) {
                        Log::error('Family details queue failed: ' . $e->getMessage());
                        $is_saved = false;
                    }
                } else {
                    $is_saved = BeneficiaryFamilyDetail::updateOrCreate(
                        [
                            'scheme_id' => $scheme_id,
                            'application_id' => $application_id,
                        ],
                        $familyPayload
                    );
                }

                if (config('app.queue_enable')) {
                    try {
                        AcceptrejectInfoEntryJob::dispatch($acceptRejectPayload);
                        $accpt_reject_save = true;
                    } catch (\Exception $e) {
                        Log::error('Accept reject info queue failed: ' . $e->getMessage());
                        $accpt_reject_save = false;
                    }
                } else {
                    $accpt_reject_save = AcceptRejectInfo::create($acceptRejectPayload);
                }

                if ($is_saved && $accpt_reject_save) {
                    DB::commit();
                    return response()->json(["is_success" => true, 'temp_application_id' => base64_encode(EncryptDecrypt::encrypt((string) $application_id))]);
                } else {
                    DB::rollback();
                    $errorMsg = __('messages.dbroolback');
                    return response()->json(["is_success" => false, 'error' => $errorMsg]);
                }
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json(["is_success" => false, 'error' => $e->getMessage()]);
            }
        } catch (\Exception $e) {
            return response()->json(["is_success" => false, 'error' => $e->getMessage()]);
        }
    }
    public function declarationEntry(StoreDeclarationRequest $request)
    {
        try {
            $validated = $request->validated();
            //dd($validated);
            $request_data = EncryptDecrypt::decrypt($request->data);
            $mobile_no = $request_data['extraData']['mobile_no'];
            $scheme_id = $request_data['extraData']['scheme_id'];
            $application_id = $request_data['formData']['application_id'];
            $add_edit_status = $request_data['formData']['add_edit_status'];

            if (!TokenValidation::mobileNoValidation($mobile_no)) {
                $errorMsg =  __('messages.mobilenoinvalid');
                return response()->json(["is_success" => false, 'error' => $errorMsg]);
            }
            $scheme_id = $request_data['extraData']['scheme_id'];
            //dd($this->schemeValidation($scheme_id));
            if (!TokenValidation::schemeValidation($scheme_id)) {
                $errorMsg =  __('messages.mobilenoinvalid');
                return response()->json(["is_success" => false, 'error' => $errorMsg]);
            }
            $token_valid = TokenValidation::checkTokenMobileScheme($request_data, $request);
            if (!$token_valid) {
                $errorMsg =  __('messages.invalidToken');
                return response()->json(["is_success" => false, 'error' => $errorMsg]);
            }
            $token_expire = TokenValidation::checTokenExpireTime($request_data, $request);
            // dd($token_expire);
            if (!$token_expire) {
                $errorMsg =  __('messages.invalidOtp');
                return response()->json(["is_success" => false, 'error' => $errorMsg]);
            }
            $token = request()->bearerToken();
            $claims = JWTAuth::getJWTProvider()->decode(base64_decode($token));
            $decrpt_sub = EncryptDecrypt::decrypt(base64_decode($claims['sub']));
            $declarationPayload = [
                'scheme_id' => $scheme_id,
                'application_id' => $application_id,
                'is_clean' => 2,
                'otp_validation_id' => $decrpt_sub,
                'is_resident' => trim($request->is_resident),
                'earn_monthly_remuneration' => trim($request->earn_monthly_remuneration),
                'info_genuine_decl' => trim($request->info_genuine_decl),
                'no_financial_assistance' => trim($request->no_financial_assistance),
                'no_income_tax' => trim($request->no_income_tax),
                'aadhaar_consent' => trim($request->aadhaar_consent),
                'ip_address' => $request->ip(),
            ];

            $acceptRejectPayload = [
                'application_id' => $application_id,
                'ip_address' => request()->ip(),
                'browser' => request()->header('User-Agent'),
                'model_name' => null,
                'op_type' => Codemaster::getIdByCode('21104'),
            ];

            DB::beginTransaction();
            $pension_details_declaration_save = 0;
            try {
                if (config('app.queue_enable')) {
                    try {
                        DeclarationEntryJob::dispatch($declarationPayload);
                        $pension_details_declaration_save = true;
                    } catch (\Exception $e) {
                        Log::error('Declaration details queue failed: ' . $e->getMessage());
                        $pension_details_declaration_save = false;
                    }
                } else {
                    $pension_details_declaration_save = BeneficiarySelfDeclaration::updateOrCreate(
                        [
                            'scheme_id' => $scheme_id,
                            'application_id' => $application_id,
                        ],
                        $declarationPayload
                    );
                }

                if (config('app.queue_enable')) {
                    try {
                        AcceptrejectInfoEntryJob::dispatch($acceptRejectPayload);
                        $accpt_reject_save = true;
                    } catch (\Exception $e) {
                        Log::error('Accept reject queue failed: ' . $e->getMessage());
                        $accpt_reject_save = false;
                    }
                } else {
                    $accpt_reject_save = AcceptRejectInfo::create($acceptRejectPayload);
                }
                if ($pension_details_declaration_save && $accpt_reject_save) {
                    DB::commit();
                    return response()->json(["is_success" => true, 'temp_application_id' => base64_encode(EncryptDecrypt::encrypt((string) $application_id))]);
                } else {
                    DB::rollback();
                    $errorMsg = __('messages.dbroolback');
                    return response()->json(["is_success" => false, 'error' => $errorMsg]);
                }
            } catch (\Exception $e) {
                dd($e);
                DB::rollback();
                $errorMsg = __('messages.dbroolback');
                return response()->json(["is_success" => false, 'error' => $errorMsg]);
            }
        } catch (\Exception $e) {
            dd($e);
            $errorMsg = __('messages.invaliddata');
            return response()->json(["is_success" => false, 'error' => $errorMsg]);
        }
    }

    
    //  Check if an existing application exists for the verified mobile number.
    //  Returns existing application data if found, so the frontend can pre-fill the form.
 
    public function checkExistingApplication(Request $request)
    {
        try {
            $request_data = EncryptDecrypt::decrypt($request->data);
            $mobile_no    = $request_data['extraData']['mobile_no'] ?? null;
            $scheme_id    = $request_data['extraData']['scheme_id'] ?? null;

            //  mobile validation
            if (!TokenValidation::mobileNoValidation($mobile_no)) {
                return response()->json(['is_success' => false, 'error' => __('messages.mobilenoinvalid')]);
            }

            // Token validation
            $token_valid = TokenValidation::checkTokenMobileScheme($request_data, $request);
            if (!$token_valid) {
                return response()->json(['is_success' => false, 'error' => __('messages.invalidToken')]);
            }

            $token_expire = TokenValidation::checTokenExpireTime($request_data, $request);
            if (!$token_expire) {
                return response()->json(['is_success' => false, 'error' => __('messages.invalidOtp')]);
            }

            //  for the latest application by mobile number for this scheme
            $personal = BeneficiaryPersonal::where('scheme_id', $scheme_id)
                ->where('mobile_no', $mobile_no)
                
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$personal) {
                $responseData = ['has_application' => false];
                return response()->json([
                    'is_success' => true, 
                    'data' => base64_encode(EncryptDecrypt::encrypt(json_encode($responseData)))
                ]);
            }
            $application_id = $personal->application_id;
            //  related step data
            $contact     = BeneficiaryContact::where('application_id', $application_id)->first();
            $bank        = BeneficiaryBank::where('application_id', $application_id)->first();
            $declaration = BeneficiarySelfDeclaration::where('application_id', $application_id)->first();
            $aadhaar     = BeneficiaryAadhaar::where('application_id', $application_id)->first();
            $familyDetail = BeneficiaryFamilyDetail::where('application_id', $application_id)->first();
            $enclosures  = BeneficiaryEnclosure::with('documentType')
                ->where('application_id', $application_id)
                ->get();
            $enclosure_count = $enclosures->count();

            // Decrypt full aadhaar number
            $aadhaar_no_plain = '';
            if ($aadhaar && $aadhaar->encoded_aadhar) {
                try {
                    $aadhaar_no_plain = \Illuminate\Support\Facades\Crypt::decryptString($aadhaar->encoded_aadhar);
                } catch (\Throwable $e) {
                    $aadhaar_no_plain = '';
                }
            }
            $inferred_marital_status = !empty($personal->marital_status)
                ? $personal->marital_status
                : (!empty(trim($personal->spouse_fname ?? '')) ? 'married' : '');

            $completed_steps = [1];
            if ($contact)             $completed_steps[] = 2;
            if ($bank)                $completed_steps[] = 3;
            $has_family = !empty($familyDetail);
            if ($has_family)          $completed_steps[] = 4;
            if ($enclosure_count > 0) $completed_steps[] = 5;
            if ($declaration)         $completed_steps[] = 6;

            // Build personal data response
            $personal_data = [
                'application_id'     => base64_encode(EncryptDecrypt::encrypt((string) $application_id)),
                'beneficiary_name'   => $personal->beneficiary_name,
                'dob'                => $personal->dob,
                'mobile_no'          => $personal->mobile_no,
                'aadhar_no'          => $aadhaar_no_plain,
                'caste'              => (string) ($personal->caste ?? ''),
                'caste_cert_no'      => $personal->caste_certificate_no ?? '',
                'father_fname'       => $personal->father_fname ?? '',
                'father_mname'       => $personal->father_mname ?? '',
                'father_lname'       => $personal->father_lname ?? '',
                'mother_fname'       => $personal->mother_fname ?? '',
                'mother_mname'       => $personal->mother_mname ?? '',
                'mother_lname'       => $personal->mother_lname ?? '',
                'spouse_fname'       => $personal->spouse_fname ?? '',
                'spouse_mname'       => $personal->spouse_mname ?? '',
                'spouse_lname'       => $personal->spouse_lname ?? '',
                'marital_status'     => $inferred_marital_status,
                'email'              => $personal->email ?? '',
                'ration_card_no'     => $personal->ration_card_no ?? '',
                'epic_card_no'       => $personal->epic_card_no ?? '',
                'pan_no'             => $personal->pan_no ?? '',
                'is_taxpayer'        => $personal->is_taxpayer ?? '0',
                'has_pan_card'       => $personal->has_pan_card ?? '0',
                'is_final'           => (int)($personal->is_final ?? 0),
                'application_date'   => $personal->application_date,
            ];

            // Build contact data response
            $contact_data = null;
            if ($contact) {
                $contact_data = [
                    'district'           => (string) ($contact->district_id ?? ''),
                    'assembly'           => (string) ($contact->assembly ?? ''),
                    'rural_urban'        => (string) ($contact->rural_urban ?? ''),
                    'block_muncipality'  => (string) ($contact->blockurban ?? ''),
                    'gp_ward'            => (string) ($contact->gpward ?? ''),
                    'police_station'     => $contact->policestation ?? '',
                    'village_town_city'  => $contact->village_town_city ?? '',
                    'house_premise_no'   => $contact->house_premise_no ?? '',
                    'post_office'        => $contact->post_office ?? '',
                    'pin_code'           => $contact->pincode ?? '',
                ];
            }

            // Build bank data response
            $bank_data = null;
            if ($bank) {
                $bank_data = [
                    'bank_account_number' => $bank->bankaccountnumber ?? '',
                    'bank_ifsc_code'      => $bank->ifscode ?? '',
                ];
            }

            // Build declaration data response
            $declaration_data = null;
            if ($declaration) {
                $declaration_data = [
                    'is_resident'              => (bool) ($declaration->is_resident ?? false),
                    'earn_monthly_remuneration'=> (bool) ($declaration->earn_monthly_remuneration ?? false),
                    'no_financial_assistance'  => (bool) ($declaration->no_financial_assistance ?? false),
                    'no_income_tax'            => (bool) ($declaration->no_income_tax ?? false),
                    'info_genuine_decl'        => (bool) ($declaration->info_genuine_decl ?? false),
                    'aadhaar_consent'          => (bool) ($declaration->aadhaar_consent ?? false),
                ];
            }

            // Build enclosure list for response
            $enclosure_list = $enclosures->map(function ($enc) {
                return [
                    'document_type_id'   => $enc->document_type,
                    'document_type_name' => optional($enc->documentType)->name ?? 'Document',
                    'document_extension' => $enc->document_extension ?? '',
                    'document_mime_type' => $enc->document_mime_type ?? '',
                    'attched_document'   => $enc->attched_document ?? '',
                ];
            })->values()->toArray();

            // Build the full response data
            $responseData = [
                'has_application' => true,
                'completed_steps' => $completed_steps,
                'personal'        => $personal_data,
                'contact'         => $contact_data,
                'bank'            => $bank_data,
                'family_members'  => $familyDetail ? ($familyDetail->family_members ?? []) : [],
                'declaration'     => $declaration_data,
                'enclosure_count' => $enclosure_count,
                'enclosure_list'  => $enclosure_list,
            ];

            return response()->json([
                'is_success' => true,
                'data' => base64_encode(EncryptDecrypt::encrypt(json_encode($responseData)))
            ]);

        } catch (\Throwable $e) {
            Log::error('checkExistingApplication error: ' . $e->getMessage());
            return response()->json([
                'is_success' => false,
                'error'      => __('messages.unexpectederror'),
                'message'    => $e->getMessage(),
            ], 500);
        }
    }

    public function encloserEntry(StoreEncloserRequest $request)
    {
        try {
            $request_data = EncryptDecrypt::decrypt($request->data);
            $application_id = $request_data['formData']['application_id'];
            $scheme_id = $request_data['extraData']['scheme_id'];
            $mobile_no = $request_data['extraData']['mobile_no'];
            $enclosures = $request_data['enclosures'] ?? [];

            if (!TokenValidation::schemeValidation($scheme_id)) {
                $errorMsg =  __('messages.mobilenoinvalid');
                return response()->json(["is_success" => false, 'error' => $errorMsg]);
            }
            $token_valid = TokenValidation::checkTokenMobileScheme($request_data, $request);
            if (!$token_valid) {
                $errorMsg =  __('messages.invalidToken');
                return response()->json(["is_success" => false, 'error' => $errorMsg]);
            }
            $token_expire = TokenValidation::checTokenExpireTime($request_data, $request);
            if (!$token_expire) {
                $errorMsg =  __('messages.invalidOtp');
                return response()->json(["is_success" => false, 'error' => $errorMsg]);
            }
            $token = request()->bearerToken();
            $claims = JWTAuth::getJWTProvider()->decode(base64_decode($token));
            $decrpt_sub = EncryptDecrypt::decrypt(base64_decode($claims['sub']));

            if (empty($decrpt_sub) || !is_int($decrpt_sub)) {
                $errorMsg = __('messages.invaliddata');
                return response()->json(["is_success" => false, 'error' => $errorMsg]);
            }
            if (empty($application_id)) {
                $errorMsg = __('messages.invaliddata');
                return response()->json(["is_success" => false, 'error' => $errorMsg]);
            }

            $rules = [];
            $messages = [];
            $doc_mappings = [];

            foreach ($enclosures as $enc) {
                $document_type = $enc['document_type'];
                $query = SchemeAttachedDocMappings::with('docType')->where('doc_type_id', $document_type)->where('scheme_id', $scheme_id);
                $doc_arr = $query->first();
                if ($doc_arr) {
                    $doc_mappings[$document_type] = $doc_arr;
                    $max_size = (int) str_replace('KB', '', $doc_arr->max_file_size);
                    if ($max_size <= 0) {
                        $max_size = 500;
                    }
                    $rules["files.{$document_type}"] = 'required|file|max:' . $max_size;
                    $messages["files.{$document_type}.max"] = "The file uploaded for " . $doc_arr->docType->name . " size must be less than " . $max_size . " KB";
                    $messages["files.{$document_type}.required"] = "Document for " . $doc_arr->docType->name . " must be uploaded";
                }
            }

            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                $return_msg = $validator->errors()->all();
                return response()->json(["is_success" => false, 'errors' => $return_msg]);
            }

            DB::beginTransaction();
            DB::connection('pgsql_encwrite')->beginTransaction();

            try {
                $is_saved = 1;
                foreach ($enclosures as $enc) {
                    $document_type = $enc['document_type'];
                    $doc_arr = $doc_mappings[$document_type];

                    $image_file = $request->file('files')[$document_type];
                    $img_data = file_get_contents($image_file);
                    $extension = $image_file->getClientOriginalExtension();
                    $mime_type = $image_file->getMimeType();
                    $base64 = base64_encode($img_data);

                    $enclosurePayload = [
                        'scheme_id' => $scheme_id,
                        'application_id' => $application_id,
                        'document_type' => $document_type,
                        'attched_document' => $base64,
                        'document_extension' => $extension,
                        'document_mime_type' => $mime_type,
                        'ip_address' => $request->ip(),
                        'otp_validation_id' => $decrpt_sub,
                    ];

                    if (config('app.queue_enable')) {
                        try {
                            EnCloserEntryJob::dispatch($enclosurePayload);
                            $is_saved_current = true;
                        } catch (\Exception $e) {
                            Log::error('Enclosure queue failed: ' . $e->getMessage());
                            $is_saved_current = false;
                        }
                    } else {
                        $is_saved_current = BeneficiaryEnclosure::updateOrCreate(
                            [
                                'scheme_id' => $scheme_id,
                                'application_id' => $application_id,
                                'document_type' => $document_type,
                            ],
                            $enclosurePayload
                        );
                    }

                    if (!$is_saved_current) {
                        $is_saved = 0;
                        break;
                    }
                }

                $acceptRejectPayload = [
                    'application_id' => $application_id,
                    'ip_address' => request()->ip(),
                    'browser' => request()->header('User-Agent'),
                    'model_name' => null,
                    'op_type' => Codemaster::getIdByCode('21105'),
                ];

                if (config('app.queue_enable')) {
                    try {
                        AcceptrejectInfoEntryJob::dispatch($acceptRejectPayload);
                        $accpt_reject_save = true;
                    } catch (\Exception $e) {
                        Log::error('Accept reject queue failed: ' . $e->getMessage());
                        $accpt_reject_save = false;
                    }
                } else {
                    $accpt_reject_save = AcceptRejectInfo::create($acceptRejectPayload);
                }

                if ($is_saved && $accpt_reject_save) {
                    DB::commit();
                    DB::connection('pgsql_encwrite')->commit();
                    return response()->json(["is_success" => true, 'temp_application_id' => base64_encode(EncryptDecrypt::encrypt((string) $application_id))]);
                } else {
                    DB::rollback();
                    DB::connection('pgsql_encwrite')->rollBack();
                    $errorMsg = __('messages.dbroolback');
                    return response()->json(["is_success" => false, 'error' => $errorMsg]);
                }
            } catch (\Exception $e) {
                DB::rollback();
                DB::connection('pgsql_encwrite')->rollBack();
                $errorMsg = __('messages.dbroolback') . ': ' . $e->getMessage();
                return response()->json(["is_success" => false, 'error' => $errorMsg]);
            }
        } catch (\Exception $e) {
            $errorMsg = __('messages.invaliddata') . ': ' . $e->getMessage();
            return response()->json(["is_success" => false, 'error' => $errorMsg]);
        }
    }

    public function finalSubmit(Request $request)
    {
        try {
            $request_data = EncryptDecrypt::decrypt($request->data);

            $application_id = $request_data['formData']['application_id'] ?? null;
            $mobile_no      = $request_data['extraData']['mobile_no'] ?? null;

            if (!$application_id) {
                return response()->json(["is_success" => false, "error" => "Application ID missing"]);
            }

            if (!TokenValidation::mobileNoValidation($mobile_no)) {
                $errorMsg =  __('messages.mobilenoinvalid');
                return response()->json(["is_success" => false, 'error' => $errorMsg]);
            }
            $scheme_id = $request_data['extraData']['scheme_id'];
            //dd($this->schemeValidation($scheme_id));
            if (!TokenValidation::schemeValidation($scheme_id)) {
                $errorMsg =  __('messages.mobilenoinvalid');
                return response()->json(["is_success" => false, 'error' => $errorMsg]);
            }
            $token_valid = TokenValidation::checkTokenMobileScheme($request_data, $request);
            if (!$token_valid) {
                $errorMsg =  __('messages.invalidToken');
                return response()->json(["is_success" => false, 'error' => $errorMsg]);
            }
            $token_expire = TokenValidation::checTokenExpireTime($request_data, $request);
            // dd($token_expire);
            if (!$token_expire) {
                $errorMsg =  __('messages.invalidOtp');
                return response()->json(["is_success" => false, 'error' => $errorMsg]);
            }

            DB::beginTransaction();
            DB::connection('pgsql_encwrite')->beginTransaction();
            try {
                $affected = BeneficiaryPersonal::where('application_id', $application_id)
                    ->update(['is_final' => 1]);

                if ($affected) {
                    DB::commit();
                    DB::connection('pgsql_encwrite')->commit();
                    return response()->json(["is_success" => true]);
                } else {
                    DB::rollback();
                    DB::connection('pgsql_encwrite')->rollBack();
                    return response()->json(["is_success" => false, "error" => "Application not found or could not be updated"]);
                }
            } catch (\Exception $e) {
                DB::rollback();
                DB::connection('pgsql_encwrite')->rollBack();
                return response()->json(["is_success" => false, 'error' => $e->getMessage()]);
            }
        } catch (\Exception $e) {
            return response()->json(["is_success" => false, 'error' => $e->getMessage()]);
        }
    }
}
