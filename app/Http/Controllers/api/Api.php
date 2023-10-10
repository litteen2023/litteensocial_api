<?php

namespace App\Http\Controllers\api;

use App\Models\Usermodel;

use App\Models\Interestsmodel;
use App\Models\Friendrequestmodel;
use App\Models\Likemodel;
use App\Models\Newsfeedmodel;
use App\Models\Storiesmodel;
use App\Models\Transactionsmodel;

use App\Models\NewsfeedCommentmodel;
use App\Models\Likeprofilemodel;
use App\Models\Profileview;
use App\Models\StoriesCommentmodel;
use App\Models\StoriesLikemodel;
use App\Models\NewsfeedMutemodel;
use App\Models\Albummodel;

use App\Models\Subscribemodel;
use App\Models\Usersubscribemodel;
use App\Models\Countrymodel;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;
use Mail;
use App\Mail\DemoMail;
use DB;
use VideoThumbnail;
use Stripe;
use App\Models\Advertisment;
use App\Models\CustomEmoji;
use App\Models\Settingsmodel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Api extends Controller
{

    public function index(Type $var = null)
    {
        echo "hello";
    }

    public function createUser(Request $request)
    {
        try {
            //Validated
            $validateUser = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email|unique:user_managements,email',
                    'mobile' => 'required|unique:user_managements,mobile',
                    'password' => 'required',
                    'country' => 'nullable|integer|exists:country,id',
                ]
            );

            if ($validateUser->fails()) {

                return response()->json([
                    'status' => false,
                    'error' => 'validation error',
                    'message' => $validateUser->errors()->first()
                ], 401);
            }



            $user = Usermodel::create([
                'firstname' => !empty($request->firstname) ? $request->firstname : NULL,
                'lastname' => !empty($request->lastname) ? $request->lastname : NULL,
                'email' => $request->email,
                'birthday' => !empty($request->birthday) ? $request->birthday : NULL,
                'password' => Hash::make($request->password),
                'country' => !empty($request->country) ? $request->country : NULL,
                'mobile' => !empty($request->mobile) ? $request->mobile : NULL,
                'registration_type' => !empty($request->registration_type) ? $request->registration_type : 1,
                'qb_id' => rand(10000000, 99999999),
                // random character number
                'qb_password' => Str::random(30),
                'status' => 0
            ]);
            $token = $user->createToken("API TOKEN")->plainTextToken;
            Usermodel::where('email', $request->email)->update(['remember_token' => $token]);
            return response()->json([
                'status' => true,
                'message' => 'Account Created Successfully',
                'token' => $token
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }
    public function loginuser(Request $request)
    {
        try {
            if ($request->registration_type == 1) {
                $validuser = validator::make($request->all(), [
                    'email' => 'required|email',
                    'password' => 'required'

                ]);

                if ($validuser->fails()) {

                    return response()->json(['status' => false, 'message' => 'validator error', 'errors' => $validuser->errors()], 401);
                }
                if (!Auth::attempt($request->only(['email', 'password']))) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Email & Password does not match with our record.',
                    ], 401);
                }
                Usermodel::where('email', $request->email)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
                $user = Usermodel::where('email', $request->email)->where('status', 0)->first();
                $token = $user->createToken("API TOKEN")->plainTextToken;
                if (Usermodel::where('email', $request->email)->where('status', 0)->where('is_first_login', true)->count() > 0) {
                    $is_first_login = false;
                    Usermodel::where('email', $request->email)->update(['remember_token' => $token, 'fcm_token' => $request->fcm_token, 'online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
                } else {
                    $is_first_login = true;
                    Usermodel::where('email', $request->email)->update(['remember_token' => $token, 'fcm_token' => $request->fcm_token, 'is_first_login' => true, 'online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
                }
                $qb = Usermodel::where('email', $request->email)->first();
                return response()->json(['status' => true, 'is_first_login' => $is_first_login, 'message' => 'User Logged Successfully', 'token' => $token, 'qb_id' => $qb->qb_id, 'qb_password' => $qb->qb_password]);
            } else {
                Usermodel::where('email', $request->email)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
                if (Usermodel::where('email', $request->email)->where('status', 0)->count() > 0) {
                    $user = Usermodel::where('email', $request->email)->where('status', 0)->first();
                    $token = $user->createToken("API TOKEN")->plainTextToken;
                    if (Usermodel::where('email', $request->email)->where('is_first_login', true)->where('status', 0)->count() > 0) {
                        $is_first_login = false;
                        Usermodel::where('email', $request->email)->update(['remember_token' => $token, 'fcm_token' => $request->fcm_token, 'online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
                    } else {
                        $is_first_login = true;
                        Usermodel::where('email', $request->user_id)->update(['remember_token' => $token, 'fcm_token' => $request->fcm_token, 'is_first_login' => true, 'online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
                    }
                    $qb = Usermodel::where('email', $request->email)->first();
                    return response()->json(['status' => true, 'is_first_login' => $is_first_login, 'message' => 'User Logged Successfully', 'token' => $token, 'qb_id' => $qb->qb_id, 'qb_password' => $qb->qb_password]);
                } else {
                    $user = Usermodel::create([
                        'firstname' => !empty($request->firstname) ? $request->firstname : NULL,
                        'lastname' => !empty($request->lastname) ? $request->lastname : NULL,
                        'email' => $request->email,
                        'birthday' => !empty($request->birthday) ? $request->birthday : NULL,
                        'country' => !empty($request->country) ? $request->country : NULL,
                        'mobile' => !empty($request->mobile) ? $request->mobile : NULL,
                        'registration_type' => !empty($request->registration_type) ? $request->registration_type : 2,
                        'user_id' => $request->user_id,
                        'premium' => !empty($request->premium) ? $request->premium : 0,
                        'qb_id' => rand(10000000, 99999999),
                        'online_status' => 1,
                        'last_status_update' => Carbon::now()->toDateTimeString(),
                        // random character number
                        'qb_password' => Str::random(30),
                        'status' => 0
                    ]);
                    $token = $user->createToken("API TOKEN")->plainTextToken;
                    Usermodel::where('email', $request->email)->update(['remember_token' => $token]);
                    return response()->json([
                        'status' => true,
                        'message' => 'Account Created Successfully',
                        'token' => $token,
                        'qb_id' => $user->qb_id,
                        'qb_password' => $user->qb_password
                    ], 200);
                    // return response()->json(['status' => false, 'message' => 'Invalid User ID']);
                }
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage()], 500);
        }
    }











    public function forgetpassword(Request $request)
    {
        try {
            if (Usermodel::where('email', $request->email)->count() > 0) {
                $user = Usermodel::where('email', $request->email)->first();
                $to_name = $request->firstname;
                $to_email = $request->email;
                $otp = rand(1000, 9999);
                $data = array('name' => '', 'message' => 'Your OTP for forgot password is' . $otp);
                /* Mail::send('mail', $data, function($message) use ($to_name, $to_email) {
        $message->to($to_email, $to_name)
        ->subject('One time password for LIT');
        $message->from('test@stdev.in','LIT');
        }); */

                $mailData = [
                    'title' => 'One time password for LIT',
                    'body' => 'Your OTP to reset password is ' . $otp
                ];

                Mail::to($to_email)->send(new DemoMail($mailData));
                Usermodel::where('email', $request->email)->update(['otp' => $otp, 'otp_time' => date('Y-m-d H:i:s')]);
                return response()->json(['status' => true, 'message' => 'OTP send', 'temp_token' => $user->remember_token]);
            } else {
                return response()->json(['status' => false, 'message' => 'E-mail address not found'], 404);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage()], 500);
        }
    }
    public function matchotp(Request $request)
    {
        try {
            if (Usermodel::where('remember_token', $request->temp_token)->where('otp', $request->otp)->count() > 0) {
                $user = Usermodel::where('email', $request->email)->first();
                return response()->json(['status' => true, 'message' => 'OTP validated'], 200);
            } else {
                return response()->json(['status' => false, 'message' => 'Invalid OTP'], 200);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage()], 500);
        }
    }

    public function resetpassword(Request $request)
    {
        try {
            if (Usermodel::where('remember_token', $request->temp_token)->count() > 0) {
                Usermodel::where('remember_token', $request->temp_token)->update(['password' => Hash::make($request->password)]);
                return response()->json(['status' => true, 'message' => 'Password successfully changed'], 200);
            } else {
                return response()->json(['status' => false, 'message' => 'Invalid User'], 200);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage()], 500);
        }
    }



    public function getprofilebyid(Request $request)
    {
        $token = auth('sanctum')->user();
        Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
        if (Usermodel::where('remember_token', $token->remember_token)->where('status', 0)->count() > 0) {
            $getprofile =  Usermodel::where('remember_token', $token->remember_token)->select('id as id', 'background_image as background_image', 'profile_picture as profile_picture', 'mobile as mobile', 'email as email', 'firstname as firstname', 'lastname as lastname', 'username as username', 'address as address', 'pincode as pincode', 'city as city', 'birthday as birthday', 'interest  as interest', 'country  as country', 'is_dob_disable  as dob_visible', 'bgc_verified as bgc_verified', 'story as story', 'premium as premium', 'fb_link  as fb_link', 'youtube_link  as youtube_link', 'instagram_link as instagram_link', 'tiktok_link as tiktok_link', 'password as password', 'registration_type as registration_type', 'school as school', 'status as status', 'remember_token as remember_token', 'email_verified_at as email_verified_at', 'create_user as create_user', 'update_user as update_user', 'is_first_login as is_first_login', 'created_at as created_at', 'updated_at as updated_at')->get();
            // return$getprofile[0];
            $settings = Settingsmodel::first();
            if(!$getprofile[0]['background_image'])
            {
                $getprofile[0]['background_image'] = $settings['background_image'];
            }
            $getid =  Usermodel::select('school as school')->where('remember_token', $token->remember_token)->where('status', 0)->first();
            // $getid =  Usermodel::select('school  as school')->first();
            // $getprofile[0]['matchprofile'] = Usermodel::select('id as Id', 'background_image as BackgroundImage','profile_picture as ProfilePicture', 'mobile as Mobile','email as Email', 'firstname as Firstname', 'lastname as Lastname','username as Username', 'address as Address', 'pincode as Pincode', 'city as City', 'birthday as Birthday', 'interest  as Interest', 'country  as Country', 'is_dob_disable  as DobVisible','bgc_verified as bgc_verified','story as Story','premium as premium', 'fb_link  as fb_link', 'youtube_link  as youtube_link','instagram_link as instagram_link','tiktok_link as tiktok_link','password as password','registration_type as registration_type','user_id as user_id','school as school','status as status','remember_token as remember_token','email_verified_at as email_verified_at','create_user as create_user','update_user as update_user','is_first_login as is_first_login','created_at as created_at','updated_at as updated_at')->where('school',$getid->school)->get();
            $id[] = Auth::user()->id;
            $matchingProfiles = Usermodel::whereNotIn('id',$id)->select('id as id', 'background_image as background_image', 'profile_picture as profile_picture', 'mobile as mobile', 'email as email', 'firstname as firstname', 'lastname as lastname', 'username as username', 'address as address', 'pincode as pincode', 'city as city', 'birthday as birthday', 'interest  as interest', 'country  as country', 'is_dob_disable  as dob_visible', 'bgc_verified as bgc_verified', 'story as story', 'premium as premium', 'fb_link  as fb_link', 'youtube_link  as youtube_link', 'instagram_link as instagram_link', 'tiktok_link as tiktok_link', 'password as password', 'registration_type as registration_type', 'school as school', 'status as status', 'remember_token as remember_token', 'email_verified_at as email_verified_at', 'create_user as create_user', 'update_user as update_user', 'is_first_login as is_first_login', 'created_at as created_at', 'updated_at as updated_at')->where('school', $getid->school)->where('status', 0)->get();
            $matchingProfiless = array();
            $i = 0;
            foreach ($matchingProfiles as $matchingProfiles) {
                $matchingProfiless[$i] = $matchingProfiles;
                if (!empty($matchingProfiless[$i]['interest'])) {
                    $matchingProfiless[$i]['interest'] = array_map('intval', explode(',', substr($matchingProfiless[$i]['interest'], 1, -1)));
                } else {
                    $matchingProfiless[$i]['interest'] = null;
                }
                $i++;
            }
            $getprofile[0]['matchprofile'] = $matchingProfiless;
            $getprofile[0]['friend'] = Friendrequestmodel::where('friend_id', $getprofile[0]['id'])->where('friend_request_type', 2)->count();
            $getprofile[0]['like'] = Likeprofilemodel::where('user_id', $getprofile[0]['id'])->where('like_type', 1)->count();
            $getprofile[0]['profile_viewed'] = Profileview::join('user_managements', 'user_managements.id', '=', 'profile_view.user_id')->where('profile_view.profile_id', $getprofile[0]['id'])->whereNotIn('profile_view.user_id',$id)->get(['user_managements.id as id', 'user_managements.background_image as background_image', 'user_managements.profile_picture as profile_picture', 'user_managements.mobile as mobile', 'user_managements.email as email', 'user_managements.firstname as firstname', 'user_managements.lastname as lastname', 'user_managements.username as username']);

            //Profileview::where('profile_id',$getprofile[0]['id'])->count();
            $sett = DB::table('settings')->first();
            if (empty($getprofile[0]['nav_bar_background'])) {
                $getprofile[0]['nav_bar_background'] = $sett->nav_bar_background;
            }


            if (empty($getprofile[0]['background_image'])) {
                $getprofile[0]['background_image'] = $sett->background_image;
            }

            if (empty($getprofile[0]['profile_picture'])) {
                $getprofile[0]['profile_picture'] = 'http://lt-admin.mynewsystem.net/public/images/settings/84428.jpg';
            }
            if (!empty($getprofile[0]['interest'])) {
                $getprofile[0]['interest'] = array_map('intval', explode(',', substr($getprofile[0]['interest'], 1, -1)));
            } else {
                $getprofile[0]['interest'] = null;
            }
            $getprofile[0]['friend_list'] = Friendrequestmodel::where('friend_request_type', 2)->join('user_managements', 'user_managements.id', '=', 'friend_tbl.friend_for_id')->where(['friend_tbl.friend_id'=> $getprofile[0]['id']])->get(['user_managements.id as id', 'user_managements.background_image as background_image', 'user_managements.profile_picture as profile_picture', 'user_managements.mobile as mobile', 'user_managements.email as email', 'user_managements.firstname as firstname', 'user_managements.lastname as lastname', 'user_managements.username as username']);


            return  response()->json(['status' => true, 'data' => $getprofile], 200);
        } else {
            return  response()->json(['token' => $token, 'status' => false, 'message' => 'No available data'], 404);
        }
    }


    public function getinterestlist()
    {

        Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
        if (Interestsmodel::count() > 0) {
            $getinterest =  Interestsmodel::select('interest_id as interest_id', 'interest as interest')->get();
            return  response()->json(['status' => true, 'data' => $getinterest], 200);
        } else {
            return  response()->json(['status' => false, 'message' => 'No available interest data'], 200);
        }
    }


    public function getcountrylist()
    {

        Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
        if (Countrymodel::count() > 0) {
            $getcountry =  Countrymodel::select('country_id  as country_id', 'country as country', 'flag as flag')->orderBy('country', 'asc')->get();
            return  response()->json(['status' => true, 'data' => $getcountry], 200);
        } else {
            return  response()->json(['status' => false, 'message' => 'No available country'], 200);
        }
    }


    public function profileupdate(Request $request)
    {
        try {
            $token = auth('sanctum')->user();
            Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
            if (!empty($token)) {
                /*	if(!empty($request->username)){
				$user['username']=$request->username;
				}
				if(!empty($request->email)){
				$user['email']=$request->email;
				}
				if(!empty($request->background_image)){
				$user['background_image']=$request->background_image;
				}
				if(!empty($request->profile_picture)){
				$user['profile_picture']=$request->profile_picture;
				}
				if(!empty($request->firstname)){
				$user['firstname']=$request->firstname;
				}
				if(!empty($request->lastname)){
				$user['lastname']=$request->lastname;
				}
				if(!empty($request->firstname)){
				$user['firstname']=$request->firstname;
				}
				if(!empty($request->birthday)){
				$user['birthday']=$request->birthday;
				}
				if(!empty($request->country)){
				$user['country']=$request->country;
				}
				if(!empty($request->mobile)){
				$user['mobile']=$request->mobile;
				}
                if(!empty($request->fb_link)){
				$user['fb_link']=$request->fb_link;
				}
				if(!empty($request->youtube_link)){
				$user['youtube_link']=$request->youtube_link;
				}
				if(!empty($request->instagram_link)){
				$user['instagram_link']=$request->instagram_link;
				}
				if(!empty($request->tiktok_link)){
				$user['tiktok_link']=$request->tiktok_link;
				}
				if(!empty($request->pincode)){
				$user['pincode']=$request->pincode;
				}
				if(!empty($request->city)){
				$user['city']=$request->city;
				}
				if(!empty($request->school)){
				$user['school']=$request->school;
				}
				if(!empty($request->interest)){
				$user['interest']=$request->interest;
				}
				if(!empty($request->address)){
				$user['address']=$request->address;
				}
				if(!empty($request->dob_visible)){
				$user['is_dob_disable']=$request->dob_visible;
				}
				else{
					$user['is_dob_disable']=0;
				}
				if(!empty($request->bgc_verified)){
				$user['bgc_verified']=$request->bgc_verified;
				}
				if(!empty($request->premium)){
				$user['premium']=$request->premium;
				}
				if(!empty($request->story)){
				$user['story']=$request->story;
				}
				if(!empty($request->interest)){
				$user['interest']=$request->interest;
				}*/
                $user['username'] = $request->username;
                if (!empty($request->email)) {
                    $user['email'] = $request->email;
                }
                if (!empty($request->background_image)) {
                    $user['background_image'] = $request->background_image;
                }
                if (!empty($request->profile_picture)) {
                    $user['profile_picture'] = $request->profile_picture;
                }
                if (!empty($request->firstname)) {
                    $user['firstname'] = $request->firstname;
                }
                if (!empty($request->lastname)) {
                    $user['lastname'] = $request->lastname;
                }
                $user['birthday'] = $request->birthday;
                $user['country'] = $request->country;
                if (!empty($request->mobile)) {
                    $user['mobile'] = $request->mobile;
                }
                $user['fb_link'] = $request->fb_link;
                $user['youtube_link'] = $request->youtube_link;
                $user['instagram_link'] = $request->instagram_link;
                $user['tiktok_link'] = $request->tiktok_link;
                if (!empty($request->pincode)) {
                    $user['pincode'] = $request->pincode;
                }
                if (!empty($request->city)) {
                    $user['city'] = $request->city;
                }
                $user['school'] = $request->school;
                $user['interest'] = $request->interest;
                if (!empty($request->address)) {
                    $user['address'] = $request->address;
                }
                if (!empty($request->dob_visible)) {
                    $user['is_dob_disable'] = $request->dob_visible;
                } else {
                    $user['is_dob_disable'] = 0;
                }
                if (!empty($request->bgc_verified)) {
                    $user['bgc_verified'] = $request->bgc_verified;
                }
                if (!empty($request->premium)) {
                    $user['premium'] = $request->premium;
                }

                $user['story'] = $request->story ?? '';

                $user['interest'] = $request->interest;

                /*   $user = array(
            	'background_image' => !empty($request->background_image)?$request->background_image:NULL,
                'profile_picture' => !empty($request->profile_picture)?$request->profile_picture:NULL,
                'firstname' => !empty($request->firstname)?$request->firstname:NULL,
                'lastname' => !empty($request->lastname)?$request->lastname:NULL,
                'birthday' => !empty($request->birthday)?$request->birthday:NULL,
                'password' => Hash::make($request->password),
				'country' => !empty($request->country)?$request->country:NULL,
				'mobile' => !empty($request->mobile)?$request->mobile:NULL,
			    'username' => !empty($request->username)?$request->username:NULL,
                'fb_link' => !empty($request->fb_link)?$request->fb_link:NULL,
				'youtube_link' => !empty($request->youtube_link)?$request->youtube_link:NULL,
				'instagram_link' => !empty($request->instagram_link)?$request->instagram_link:NULL,
				'tiktok_link' => !empty($request->tiktok_link)?$request->tiktok_link:NULL,
                'pincode' => !empty($request->pincode)?$request->pincode:NULL,
				'city' => !empty($request->city)?$request->city:NULL,
				'school' => !empty($request->school)?$request->school:NULL,
				'interest' => !empty($request->interest)?$request->interest:NULL,
                'country' => !empty($request->country)?$request->country:NULL,
                'address' => !empty($request->address)?$request->address:NULL


            );*/




                $id = Usermodel::where('remember_token', $token->remember_token)->update($user);

                return  response()->json(['status' => true,  'message' => 'Profile data has been updated successfully!'], 200);
            } else {
                return  response()->json(['status' => false,  'message' => 'An error occurred, please try again later '], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function save_background_image(Request $request)
    {
        try {

            $token = auth('sanctum')->user();
            Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
            $file = $request->file('background_image');
            $imageName = time() . '.' . $file->extension();
            $imagePath = public_path() . '/background_image';

            $file->move($imagePath, $imageName);
            // $image_path = $request->file('background_image')->move('background_image', 'public');

            $url = 'http://lt-api.mynewsystem.net/public/background_image/' . $imageName;
            $user['background_image'] = $url;
            $id = Usermodel::where('remember_token', $token->remember_token)->update($user);

            return  response()->json(['status' => true, 'url' => $url,  'message' => 'Background picture Store'], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!'

            ], 500);
        }
    }


    public function save_profile_picture(Request $request)
    {
        try {

            $token = auth('sanctum')->user();
            Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
            $file = $request->file('profile_picture');
            $imageName = time() . '.' . $file->extension();
            $imagePath = public_path() . '/profile_picture';

            $file->move($imagePath, $imageName);
            // $image_path = $request->file('profile_picture')->move('profile_picture', 'public');


            $url = 'http://lt-api.mynewsystem.net/public/profile_picture/' . $imageName;
            $user['profile_picture'] = $url;
            $id = Usermodel::where('remember_token', $token->remember_token)->update($user);

            return  response()->json(['status' => true, 'url' => $url,  'message' => 'Profile picture Store'], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!'

            ], 500);
        }
    }


    public function getuserlistfilter(Request $request)
    {
        Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
        if (Usermodel::where('firstname', 'LIKE', '%' . $request->src . '%')->orWhere('lastname', 'LIKE', '%' . $request->src . '%')->orWhere('username', 'LIKE', '%' . $request->src . '%')->count() > 0) {
            $matchingProfiles = Usermodel::where('firstname', 'LIKE', '%' . $request->src . '%')->orWhere('lastname', 'LIKE', '%' . $request->src . '%')->orWhere('username', 'LIKE', '%' . $request->src . '%')->get();
            $i = 0;
            $matchingProfiless = array();
            foreach ($matchingProfiles as $matchingProfiles) {
                if (!empty($matchingProfiles['birthday']) && !empty($matchingProfiles['school']) && !empty($matchingProfiles['interest'])) {
                    $matchingProfiless[$i] = $matchingProfiles;
                    $matchingProfiless[$i]['interest'] = array_map('intval', explode(',', substr($matchingProfiless[$i]['interest'], 1, -1)));
                    $i++;
                }
            }

            return  response()->json(['status' => true, 'data' => $matchingProfiless], 200);
        } else {
            return  response()->json(['status' => false, 'message' => 'No available data'], 200);
        }
    }

    public function getprofileid(Request $request)
    {
        Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
        // return "df";
        if (Usermodel::where('id', $request->id)->count() > 0) {
            $getprofile =  Usermodel::where('id', $request->id)->select('qb_id as qb_id', 'background_image as background_image', 'profile_picture as profile_picture', 'mobile as mobile', 'email as email', 'firstname as firstname', 'lastname as lastname', 'username as username', 'address as address', 'pincode as pincode', 'city as city', 'birthday as birthday', 'interest  as interest', 'country  as country', 'is_dob_disable  as dob_visible', 'bgc_verified as bgc_verified', 'story as story', 'premium as premium', 'fb_link  as fb_link', 'youtube_link  as youtube_link', 'instagram_link as instagram_link', 'tiktok_link as tiktok_link', 'password as password', 'registration_type as registration_type', 'school as school', 'status as status', 'remember_token as remember_token', 'email_verified_at as email_verified_at', 'create_user as create_user', 'update_user as update_user', 'is_first_login as is_first_login', 'created_at as created_at', 'updated_at as updated_at')->get();
            $settings = Settingsmodel::first();

            // return$getprofile[0];
            $getprofile[0]['interest'] = array_map('intval', explode(',', substr($getprofile[0]['interest'], 1, -1)));
            $getprofile[0]['mutual_friend'] = Usermodel::select('qb_id as qb_id', 'id as id', 'background_image as background_image', 'profile_picture as profile_picture', 'mobile as mobile', 'email as email', 'firstname as firstname', 'lastname as lastname', 'username as username', 'address as address', 'pincode as pincode', 'city as city', 'birthday as birthday', 'interest  as interest', 'country  as country', 'is_dob_disable  as dob_visible', 'bgc_verified as bgc_verified', 'story as story', 'premium as premium', 'fb_link  as fb_link', 'youtube_link  as youtube_link', 'instagram_link as instagram_link', 'tiktok_link as tiktok_link', 'password as password', 'registration_type as registration_type', 'school as school', 'status as status', 'remember_token as remember_token', 'email_verified_at as email_verified_at', 'create_user as create_user', 'update_user as update_user', 'is_first_login as is_first_login', 'created_at as created_at', 'updated_at as updated_at')->get();
            if (empty($getprofile[0]['background_image'])) {
                $getprofile[0]['background_image'] = $settings['background_image'];
            }

            if (empty($getprofile[0]['profile_picture'])) {
                $getprofile[0]['profile_picture'] = 'http://lt-admin.mynewsystem.net/public/images/settings/84428.jpg';
            }
            $token = auth('sanctum')->user();
            $getid =  Usermodel::where('remember_token', $token->remember_token)->first();
            $fdrequest = Friendrequestmodel::where('friend_for_id', $request->id)->where('friend_id', $getid->id)->first();
            if (!empty($fdrequest)) {
                $getprofile[0]['is_friend'] = $fdrequest->friend_request_type;
            } else {
                $getprofile[0]['is_friend'] = 0;
            }
            $like = Likeprofilemodel::where('user_id', $getid->id)->where('profile_id', $request->id)->first();
            if (!empty($like)) {
                if ($like->like_type == 1) {
                    $getprofile[0]['is_like'] = true;
                } else {
                    $getprofile[0]['is_like'] = false;
                }
            } else {
                $getprofile[0]['is_like'] = false;
            }
            //$getprofile[0]['Interest']=array_map('intval',explode(',',substr($getprofile[0]['Interest'],1,-1)));
            return  response()->json(['status' => true, 'data' => $getprofile], 200);
        } else {
            return  response()->json(['status' => false, 'message' => 'No available data'], 404);
        }
    }



    public function addnewsfeedlike(Request $request)
    {


        try {
            //Validated
            $validateUser = Validator::make(
                $request->all(),
                [

                    'news_feed_id' => 'required'
                ]
            );

            if ($validateUser->fails()) {

                return response()->json([
                    'status' => false,
                    'error' => 'validation error',
                    'message' => $validateUser->errors()->first()
                ], 401);
            }

            $token = auth('sanctum')->user();
            $getid =  Usermodel::where('remember_token', $token->remember_token)->first();
            Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
            if (Likemodel::where('user_id', $getid->id)->where('news_feed_id', $request->news_feed_id)->count() <= 0) {
                $user = Likemodel::create([
                    'user_id' =>  $getid->id,
                    'like_type' => 1,
                    'news_feed_id' => !empty($request->news_feed_id) ? $request->news_feed_id : NULL,

                ]);
                $is_like = true;
                return response()->json([
                    'status' => true,
                    'is_like' => $is_like,
                    'message' => 'News Feed Like Successfully',

                ], 200);
            } else {
                if (Likemodel::where('user_id', $getid->id)->where('news_feed_id', $request->news_feed_id)->where('like_type', 1)->count() > 0) {
                    $user = Likemodel::where('user_id', $getid->id)->where('news_feed_id', $request->news_feed_id)->update([
                        'user_id' =>  $getid->id,
                        'like_type' => 0,
                        'news_feed_id' => !empty($request->news_feed_id) ? $request->news_feed_id : NULL,

                    ]);
                    $is_like = false;
                    return response()->json([
                        'status' => true,
                        'is_like' => $is_like,
                        'message' => 'News Feed UnLike Successfully',

                    ], 200);
                } else {
                    $user = Likemodel::where('user_id', $getid->id)->where('news_feed_id', $request->news_feed_id)->update([
                        'user_id' =>  $getid->id,
                        'like_type' => 1,
                        'news_feed_id' => !empty($request->news_feed_id) ? $request->news_feed_id : NULL,

                    ]);
                    $is_like = true;
                    return response()->json([
                        'status' => true,
                        'is_like' => $is_like,
                        'message' => 'News Feed Like Successfully',

                    ], 200);
                }
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }


    public function getnewsfeed(Request $request)
    {
        try {
            Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
            $data1 = array();
            $token = auth('sanctum')->user();
            $getid =  Usermodel::where('remember_token', $token->remember_token)->first();
            $data = Newsfeedmodel::join('user_managements', 'user_managements.id', '=', 'news_feed_tbl.user_id')->orderBy('news_feed_tbl.id', 'DESC')->get(['news_feed_tbl.id as id', 'news_feed_tbl.thumbnail as thumbnail', 'news_feed_tbl.user_id as user_id', 'news_feed_tbl.news_type as news_type', 'news_feed_tbl.file as file', 'news_feed_tbl.new_description', 'news_feed_tbl.description as description', 'news_feed_tbl.visible_type as visible_type', 'news_feed_tbl.parent_id as parent_id', 'news_feed_tbl.tag as tag', 'news_feed_tbl.file_type as file_type', 'news_feed_tbl.created_at', 'user_managements.background_image as background_image', 'user_managements.profile_picture as profile_picture', 'user_managements.mobile as mobile', 'user_managements.email as email', 'user_managements.firstname as firstname', 'user_managements.lastname as lastname', 'user_managements.username as username']);


            if (!empty($data)) {
                $i = 0;
                $j = 0;

                foreach ($data as $key => $data) {
                    $isf = Friendrequestmodel::where('friend_for_id', $data['user_id'])->where('friend_id', $getid->id)->first();
                    $ism = NewsfeedMutemodel::where('user_id', $getid->id)->where('user_for_id', $data['user_id'])->first();

                    // $userAgent = $request->server('HTTP_USER_AGENT');



                    if ($data['visible_type'] == 0 && empty($ism)) {
                    if (($key)% 10 == 0 && $key!=0) {
                            $add = Advertisment::orderByRaw('RAND()')->where('plan_expiry_date', '>=', date('Y-m-d'))->where(['status'=> 1, 'type'=>'banner'])->select('id','image','amount')->first();
                             $data1[$j]['type'] = true;
                             $data1[$j]['type'] = true;
                             $data1[$j]['file'] = $add['image'];
                             $data1[$j]['amount'] = $add['amount'];
                        }else{
                        $enc_id = base64_encode($data['id']);
                        $data1[$j] = $data;
                        $data1[$j]['type'] = false;
                        $tag = array_map('intval', explode(',', $data['tag']));
                        $data1[$j]['tag'] = Usermodel::select('user_managements.id as id', 'user_managements.profile_picture as profile_picture', 'user_managements.firstname as firstname', 'user_managements.lastname as lastname', 'user_managements.username as username')->whereIn('id', $tag)->get();
                        $data1[$j]['comment_count'] = NewsfeedCommentmodel::where('news_feed_id', $data['id'])->count();
                        $data1[$j]['like_count'] = Likemodel::where('news_feed_id', $data['id'])->where('like_type', 1)->count();
                        $data1[$j]['is_liked'] = Likemodel::where('news_feed_id', $data['id'])->where('like_type', 1)->where('user_id', $getid->id)->count();
                        $data1[$j]['url'] = route('redirect-app', $enc_id);
                        }
                        $j++;
                    } else if (($data['visible_type'] == 1 && !empty($isf)) && empty($ism)) {
                     if (($key)% 10 == 0 && $key!=0) {
                            $add = Advertisment::orderByRaw('RAND()')->where('plan_expiry_date', '>=', date('Y-m-d'))->where(['status'=> 1, 'type'=>'banner'])->select('id','image','amount')->first();
                             $data1[$j]['type'] = true;
                             $data1[$j]['file'] = $add['image'];
                             $data1[$j]['amount'] = $add['amount'];
                        }else{
                        $enc_id = base64_encode($data['id']);
                        $data1[$j] = $data;
                         $data1[$j]['type'] = false;
                        $tag = array_map('intval', explode(',', $data['tag']));
                        $data1[$j]['tag'] = Usermodel::select('user_managements.id as id', 'user_managements.profile_picture as profile_picture', 'user_managements.firstname as firstname', 'user_managements.lastname as lastname', 'user_managements.username as username')->whereIn('id', $tag)->get();
                        $data1[$j]['comment_count'] = NewsfeedCommentmodel::where('news_feed_id', $data['id'])->count();
                        $data1[$j]['like_count'] = Likemodel::where('news_feed_id', $data['id'])->where('like_type', 1)->count();
                        $data1[$j]['is_liked'] = Likemodel::where('news_feed_id', $data['id'])->where('like_type', 1)->where('user_id', $getid->id)->count();
                        $data1[$j]['url'] = route('redirect-app', $enc_id);
                       }
                        $j++;
                    }

                    $i++;
                }

                return  response()->json(['status' => true, 'data' => $data1], 200);
            } else {
                return  response()->json(['status' => false, 'message' => 'No data found'], 404);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function redirectAppUrl($id)
    {
        // return $id;
        $id = base64_decode($id);
        $userAgent = request()->server('HTTP_USER_AGENT');
        if (strpos(strtolower($userAgent), 'iphone') !== false) {
            $url = env('ANDROID_URL') . '?$id=' . $id;
            return redirect()->away($url);
        } else if (strpos(strtolower($userAgent), 'android') !== false) {
            $url = env('ANDROID_URL') . '?$id=' . $id;
            return redirect()->away($url);
        } else {
            $url = env('ANDROID_URL') . '?$id=' . $id;
            return redirect()->away($url);
        }
    }
    public function getnewsfeedbyid(Request $request)
    {
        try {
            Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
            $token = auth('sanctum')->user();
            $getid =  Usermodel::where('remember_token', $token->remember_token)->first();
            $data = Newsfeedmodel::join('user_managements', 'user_managements.id', '=', 'news_feed_tbl.user_id')->where('news_feed_tbl.id', $request->news_feed_id)->orderBy('news_feed_tbl.id', 'DESC')->get(['news_feed_tbl.id as id', 'news_feed_tbl.thumbnail', 'news_feed_tbl.user_id as user_id', 'news_feed_tbl.news_type as news_type', 'news_feed_tbl.file as file', 'news_feed_tbl.description as description', 'news_feed_tbl.new_description', 'news_feed_tbl.created_at', 'news_feed_tbl.visible_type as visible_type', 'news_feed_tbl.parent_id as parent_id', 'news_feed_tbl.tag as tag', 'news_feed_tbl.file_type as file_type', 'user_managements.background_image as background_image', 'user_managements.profile_picture as profile_picture', 'user_managements.mobile as mobile', 'user_managements.email as email', 'user_managements.firstname as firstname', 'user_managements.lastname as lastname', 'user_managements.username as username']);

            $tag = array_map('intval', explode(',', $data[0]['tag']));

            $data[0]['tag'] = Usermodel::select('user_managements.id as id', 'user_managements.profile_picture as profile_picture', 'user_managements.firstname as firstname', 'user_managements.lastname as lastname', 'user_managements.username as username')->whereIn('id', $tag)->get();
            $data1[0]['comment_count'] = NewsfeedCommentmodel::where('user_id', $getid->id)->where('news_feed_id', $data[0]['id'])->count();
            $data1[0]['like_count'] = Likemodel::where('news_feed_id', $data[0]['id'])->where('like_type', 1)->count();
            $data1[0]['is_liked'] = Likemodel::where('news_feed_id', $data[0]['id'])->where('like_type', 1)->where('user_id', $getid->id)->count();
            return  response()->json(['status' => true, 'data' => $data], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }
    public function deletenewsfeedbyid(Request $request)
    {
        try {
            Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
            $token = auth('sanctum')->user();
            $getid =  Usermodel::where('remember_token', $token->remember_token)->first();
            if (Newsfeedmodel::where('id', $request->news_feed_id)->where('user_id', $getid->id)->count() > 0) {
                $data = Newsfeedmodel::where('id', $request->news_feed_id)->where('user_id', $getid->id)->delete();

                return  response()->json(['status' => true, 'message' => 'News Feed Deleted'], 200);
            } else {
                return  response()->json(['status' => false, 'message' => 'You are not authorize to delete News Feed'], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }
    public function mutenewsfeedbyid(Request $request)
    {
        try {
            $token = auth('sanctum')->user();
            Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
            $getid =  Usermodel::where('remember_token', $token->remember_token)->first();
            $data = Newsfeedmodel::where('id', $request->news_feed_id)->first();
            if (!empty($data)) {
                NewsfeedMutemodel::create(
                    [
                        'user_id' => $getid->id,
                        'user_for_id' => $data->user_id,
                    ]
                );
                return  response()->json(['status' => true, 'message' => 'News Feed Muted'], 200);
            } else {
                return  response()->json(['status' => false, 'message' => 'No news feed found'], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }
    public function addnewsfeedcomment(Request $request)
    {
        try {
            Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
            $token = auth('sanctum')->user();
            $getid =  Usermodel::where('remember_token', $token->remember_token)->first();

            $user = NewsfeedCommentmodel::create([
                'user_id' => $getid->id,
                'news_feed_id' => !empty($request->news_feed_id) ? $request->news_feed_id : 0,
                'description' => !empty($request->description) ? $request->description : NULL,
                'parent_id' => !empty($request->parent_id) ? $request->parent_id : 0,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'News Feed comment successfully added',

            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }
    public function getnewsfeedcommandbyid($news_feed_id)
    {
        try {
            Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
            $token = auth('sanctum')->user();
            $getid =  Usermodel::where('remember_token', $token->remember_token)->first();
            $data = NewsfeedCommentmodel::join('user_managements', 'user_managements.id', '=', 'news_feed_comment.user_id')->where('news_feed_comment.parent_id', 0)->where('news_feed_comment.news_feed_id', $news_feed_id)->orderBy('news_feed_comment.id', 'asc')->get(['news_feed_comment.id as id', 'news_feed_comment.user_id as user_id', 'news_feed_comment.news_feed_id as news_feed_id', 'news_feed_comment.description as description', 'news_feed_comment.parent_id as parent_id', 'news_feed_comment.created_at', 'user_managements.background_image as background_image', 'user_managements.profile_picture as profile_picture', 'user_managements.mobile as mobile', 'user_managements.email as email', 'user_managements.firstname as firstname', 'user_managements.lastname as lastname', 'user_managements.username as username']);
            $i = 0;
            $data1 = array();
            foreach ($data as $data) {
                $data1[$i] = $data;
                $data3 = NewsfeedCommentmodel::join('user_managements', 'user_managements.id', '=', 'news_feed_comment.user_id')->where('news_feed_comment.parent_id', $data['id'])->orderBy('news_feed_comment.id', 'asc')->get(['news_feed_comment.id as id', 'news_feed_comment.user_id as user_id', 'news_feed_comment.news_feed_id as news_feed_id', 'news_feed_comment.description as description', 'news_feed_comment.parent_id as parent_id', 'news_feed_comment.created_at', 'user_managements.background_image as background_image', 'user_managements.profile_picture as profile_picture', 'user_managements.mobile as mobile', 'user_managements.email as email', 'user_managements.firstname as firstname', 'user_managements.lastname as lastname', 'user_managements.username as username']);

                $data1[$i]['reply'] = $data3;

                $i++;
            }
            return  response()->json(['status' => true, 'data' => $data1], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function add_news_feed(Request $request)
    {


        try {
            $token = auth('sanctum')->user();
            Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
            $getid =  Usermodel::where('remember_token', $token->remember_token)->first();

            $url = '';
            $th = '';
            $imageName = '';
            if (!empty($request->file('file'))) {
                $file = $request->file('file');
                $imageName = time() . '.' . $file->extension();
                $imagePath = public_path() . '/file';
                $url = 'http://lt-api.mynewsystem.net/public/file/' . $imageName;
                $file->move($imagePath, $imageName);
                //if($request->file_type=='video'){
                if (strpos($request->file_type, 'video') !== false) {
                    $th = date('Ymdhi') . '.png';
                    VideoThumbnail::createThumbnail(
                        public_path('file/' . $imageName),
                        public_path('file/'),
                        $th,
                        2,
                        1920,
                        1080
                    );
                    $th = 'http://lt-api.mynewsystem.net/public/file/' . $th;
                } else {
                    $th = '';
                }
            }
            if (!empty($request->news_feed_id)) {
                $data = Newsfeedmodel::where('id', $request->news_feed_id)->first();
                $arr = array(
                    'user_id' => $getid->id,
                    'news_type' => 0,
                    'description' => !empty($request->description) ? $request->description : NULL,
                    'visible_type' => !empty($request->visible_type) ? $request->visible_type : 0,
                    'parent_id' => !empty($request->parent_id) ? $request->parent_id : 0,
                    'thumbnail' => $th,
                    'tag' => !empty($request->tag) ? $request->tag : 0

                );
                if (!empty($imageName)) {
                    $arr['file'] = $url;
                    $arr['file_type'] = !empty($request->file_type) ? $request->file_type : NULL;
                } else if ($request->is_file_deleted == true) {
                    $arr['file'] = '';
                    $arr['file_type'] = NUll;
                } else if ($request->is_file_deleted == false) {
                    //$arr['file']='';
                }
                $user = Newsfeedmodel::where('id', $request->news_feed_id)->update($arr);

                return response()->json([
                    'status' => true,
                    'message' => 'News Feed Updated Successfully',

                ], 200);
            } else  if (!empty($request->parent_id)) {
                $data = Newsfeedmodel::where('id', $request->parent_id)->first();
                $arr = array(
                    'user_id' => $getid->id,
                    'news_type' => 0,
                    'description' => !empty($data->description) ? $data->description : NULL,
                    'new_description' => !empty($request->new_description) ? $request->new_description : NULL,
                    'visible_type' => !empty($request->visible_type) ? $request->visible_type : 0,
                    'parent_id' => !empty($request->parent_id) ? $request->parent_id : 0,
                    'file' => !empty($data->file) ? $data->file : NULL,
                    'thumbnail' => !empty($data->thumbnail) ? $data->thumbnail : NULL,
                    'file_type' => !empty($data->file_type) ? $data->file_type : NULL,
                    'tag' => !empty($data->tag) ? $data->tag : 0

                );
                Newsfeedmodel::create($arr);
                return response()->json([
                    'status' => true,
                    'message' => 'News Feed Successfully shared',

                ], 200);
            } else {
                $user = Newsfeedmodel::create([
                    'user_id' => $getid->id,
                    'news_type' => 0,
                    'description' => !empty($request->description) ? $request->description : NULL,
                    'visible_type' => !empty($request->visible_type) ? $request->visible_type : 0,
                    'parent_id' => !empty($request->parent_id) ? $request->parent_id : 0,
                    'file' => $url,
                    'thumbnail' => $th,
                    'file_type' => !empty($request->file_type) ? $request->file_type : NULL,
                    'tag' => !empty($request->tag) ? $request->tag : 0

                ]);
                return response()->json([
                    'status' => true,
                    'message' => 'News Feed Created Successfully',

                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }


    public function add_stories(Request $request)
    {


        try {
            $token = auth('sanctum')->user();
            Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
            $getid =  Usermodel::where('remember_token', $token->remember_token)->first();

            $url = '';
            if (!empty($request->file('file'))) {
                $file = $request->file('file');
                $imageName = time() . '.' . $file->extension();
                $imagePath = public_path() . '/file';
                $url = 'http://lt-api.mynewsystem.net/public/file/' . $imageName;
                $file->move($imagePath, $imageName);
            }
            if (!empty($request->parent_id)) {
                $data = Storiesmodel::where('id', $request->parent_id)->first();
                $user = Storiesmodel::create([
                    'user_id' => $getid->id,
                    'stories_type' => 0,
                    'description' => !empty($request->description) ? $request->description : NULL,
                    'visible_type' => !empty($request->visible_type) ? $request->visible_type : 0,
                    'parent_id' => !empty($request->parent_id) ? $request->parent_id : 0,
                    'file' => !empty($data->file) ? $data->file : NULL,
                    'tag' => !empty($request->tag) ? $request->tag : 0,
                    'file_type' => !empty($data->file_type) ? $data->file_type : NULL
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Stories created successfully',

                ], 200);
            } else if (!empty($request->parent_id)) {
                $data = Storiesmodel::where('id', $request->parent_id)->first();
                $user = Storiesmodel::create([
                    'user_id' => $getid->id,
                    'stories_type' => 0,
                    'description' => !empty($request->description) ? $request->description : NULL,
                    'visible_type' => !empty($request->visible_type) ? $request->visible_type : 0,
                    'parent_id' => !empty($request->parent_id) ? $request->parent_id : 0,
                    'file' => !empty($data->file) ? $data->file : NULL,
                    'tag' => !empty($request->tag) ? $request->tag : 0,
                    'file_type' => !empty($data->file_type) ? $data->file_type : NULL
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Stories created successfully',

                ], 200);
            } else {
                $user = Storiesmodel::create([
                    'user_id' => $getid->id,
                    'stories_type' => 0,
                    'description' => !empty($request->description) ? $request->description : NULL,
                    'visible_type' => !empty($request->visible_type) ? $request->visible_type : 0,
                    'parent_id' => !empty($request->parent_id) ? $request->parent_id : 0,
                    'file' => $url,
                    'tag' => !empty($request->tag) ? $request->tag : 0,
                    'file_type' => !empty($request->file_type) ? $request->file_type : NULL

                ]);
                return response()->json([
                    'status' => true,
                    'message' => 'Stories created successfully',

                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }



    public function add_friend_request(Request $request)
    {
        try {

            //Validated
            $validateUser = Validator::make(
                $request->all(),
                [
                    'friend_for_id' => 'required'
                ]
            );

            if ($validateUser->fails()) {

                return response()->json([
                    'status' => false,
                    'error' => 'validation error',
                    'message' => $validateUser->errors()->first()
                ], 401);
            }

            Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
            $token = auth('sanctum')->user();
            $getid =  Usermodel::where('remember_token', $token->remember_token)->first();

            $url = '';
            $msg = '';
            if ($request->friend_request_type == 1) {
                $msg = 'Friend Request Successfully send';
                $type = 6;
                $type1 = 1;
            } else if ($request->friend_request_type == 2) {
                $msg = 'Friend Request Accepted';
                $type = 2;
                $type1 = 2;
            } else if ($request->friend_request_type == 3) {
                $msg = 'Friend Request Rejected';
                $type = 0;
                $type1 = 0;
            } else if ($request->friend_request_type == 4) {
                $msg = 'Friend Unfriend';
                $type = 0;
                $type1 = 0;
            } else if ($request->friend_request_type == 5) {
                $msg = 'Friend Request Cancel';
                $type = 0;
                $type1 = 0;
            }
            if (Friendrequestmodel::where('friend_id', $getid->id)->where('friend_for_id', $request->friend_for_id)->count() <= 0) {
                $user = Friendrequestmodel::create([
                    //1 request send,2= friend accept,3=Reject,4=unfriend ,5=Cancel
                    'friend_request_type' => $type1,
                    'friend_id' => $getid->id,
                    'friend_for_id' => !empty($request->friend_for_id) ? $request->friend_for_id : 0,
                ]);
                $user = Friendrequestmodel::create([
                    //1/6 request send,2= friend accept,3/8=Reject,4/9=unfriend ,5/10=Cancel
                    'friend_request_type' => $type,
                    'friend_id' => !empty($request->friend_for_id) ? $request->friend_for_id : 0,
                    'friend_for_id' => $getid->id,
                ]);
                return response()->json([
                    'status' => true,
                    'friend_request_type' => $request->friend_request_type,
                    'message' => $msg,

                ], 200);
            } else {
                $user = Friendrequestmodel::where('friend_id', $getid->id)->where('friend_for_id', $request->friend_for_id)->update([
                    //1 request send,2= friend accept,3=Reject,4=unfriend
                    'friend_request_type' => $type1
                ]);
                $user = Friendrequestmodel::where('friend_id', $request->friend_for_id)->where('friend_for_id', $getid->id)->update([
                    //1 request send,2= friend accept,3=Reject,4=unfriend
                    'friend_request_type' => $type
                ]);
                return response()->json([
                    'status' => true,
                    'friend_request_type' => $request->friend_request_type,
                    'message' => $msg,

                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }


    public function add_like_profile(Request $request)
    {
        try {

            //Validated
            $validateUser = Validator::make(
                $request->all(),
                [
                    'profile_id' => 'required'
                ]
            );

            if ($validateUser->fails()) {

                return response()->json([
                    'status' => false,
                    'error' => 'validation error',
                    'message' => $validateUser->errors()->first()
                ], 401);
            }

            Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
            $token = auth('sanctum')->user();
            $getid =  Usermodel::where('remember_token', $token->remember_token)->first();

            $url = '';
            if (Likeprofilemodel::where('user_id', $getid->id)->where('profile_id', $request->profile_id)->count() <= 0) {
                $user = Likeprofilemodel::create([
                    'user_id' => $getid->id,
                    'profile_id' => !empty($request->profile_id) ? $request->profile_id : 0,
                    'like_type' => 1,
                ]);
                return response()->json([
                    'status' => true,
                    'is_like' => true,
                    'message' => 'Profile Successfully liked',

                ], 200);
            } else {
                $dt = Likeprofilemodel::where('user_id', $getid->id)->where('profile_id', $request->profile_id)->first();
                if ($dt->like_type == 1) {
                    $user = Likeprofilemodel::where('user_id', $getid->id)->where('profile_id', $request->profile_id)->update([
                        'like_type' => 0,
                    ]);
                    return response()->json([
                        'status' => true,
                        'is_like' => false,
                        'message' => 'Profile Successfully Unliked',

                    ], 200);
                } else {
                    $user = Likeprofilemodel::where('user_id', $getid->id)->where('profile_id', $request->profile_id)->update([
                        'like_type' => 1,
                    ]);
                    return response()->json([
                        'status' => true,
                        'is_like' => true,
                        'message' => 'Profile Successfully liked',

                    ], 200);
                }
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }


    public function add_profile_view(Request $request)
    {
        try {

            //Validated
            $validateUser = Validator::make(
                $request->all(),
                [
                    'profile_id' => 'required'
                ]
            );

            if ($validateUser->fails()) {

                return response()->json([
                    'status' => false,
                    'error' => 'validation error',
                    'message' => $validateUser->errors()->first()
                ], 401);
            }


            $token = auth('sanctum')->user();
            Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
            $getid =  Usermodel::where('remember_token', $token->remember_token)->first();

            $url = '';
            if (Profileview::where('user_id', $getid->id)->where('profile_id', $request->profile_id)->count() <= 0) {
                $user = Profileview::create([
                    'user_id' => $getid->id,
                    'profile_id' => !empty($request->profile_id) ? $request->profile_id : Null,

                    'like_type' => 1,
                ]);
                return response()->json([
                    'status' => true,
                    'is_view' => true,
                    'message' => 'Profile Successfully viewed',

                ], 200);
            } else {
                return response()->json([
                    'status' => true,
                    'is_view' => true,
                    'message' => 'Profile Already viewed',

                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }
    public function get_friend_list(Request $request)
    {
        try {
            if (!empty($request->page)) {
                if ($request->page == 1) {
                    $start = 0;
                } else {
                    $start = $request->page * 20;
                }
            } else {
                $start = 0;
            }
            $limit = 20;
            $token = auth('sanctum')->user();
            Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
            $getid =  Usermodel::where('remember_token', $token->remember_token)->first();
            $friend_list = Friendrequestmodel::join('user_managements', 'user_managements.id', '=', 'friend_tbl.friend_for_id')->where('friend_tbl.friend_id', $getid->id)->where('friend_tbl.friend_request_type', 2)->limit(20)->offset($start)->get(['user_managements.id as id', 'user_managements.background_image as background_image', 'user_managements.profile_picture as profile_picture', 'user_managements.mobile as mobile', 'user_managements.email as email', 'user_managements.firstname as firstname', 'user_managements.lastname as lastname', 'user_managements.username as username']);
            return response()->json([
                'status' => true,
                'data' => $friend_list


            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function meet_new_friend(Request $request)
    {
        try {
            if (!empty($request->page)) {
                if ($request->page == 1) {
                    $start = 0;
                } else {
                    $start = $request->page * 12;
                }
            } else {
                $start = 0;
            }
            $limit = 12;
            $token = auth('sanctum')->user();
            Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
            $getid =  Usermodel::where('remember_token', $token->remember_token)->first();
            $arr = Friendrequestmodel::select('friend_for_id')->where('friend_id', $getid->id)->get();
            // add current user id id $arr
            $arr[] = $getid->id;
            $friend_list = Usermodel::whereNotIn('id', $arr)->limit(12)->offset($start)->get(['user_managements.id as id', 'user_managements.background_image as background_image', 'user_managements.profile_picture as profile_picture', 'user_managements.mobile as mobile', 'user_managements.email as email', 'user_managements.firstname as firstname', 'user_managements.lastname as lastname', 'user_managements.username as username']);
            // do not show data if relationship is null
            $friends = [];
            foreach ($friend_list as $key => $value) {
                $like = Likeprofilemodel::where('user_id', $token->id)->where('profile_id', $value->id)->first();
                if ($like) {
                    // $array = ['is_like' => $like->like_type];
                    $value->is_like = $like->like_type;
                } else {
                    $value->is_like = 0;
                }
                $friends[] = $value;
            }
            return response()->json([
                'status' => true,
                'data' => $friends


            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }


    public function deletestoriesbyid(Request $request)
    {
        try {
            $token = auth('sanctum')->user();
            Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
            $getid =  Usermodel::where('remember_token', $token->remember_token)->first();
            if (Storiesmodel::where('id', $request->stories_id)->where('user_id', $getid->id)->count() > 0) {
                $data = Storiesmodel::where('id', $request->stories_id)->where('user_id', $getid->id)->delete();

                return  response()->json(['status' => true, 'message' => 'Stories Deleted'], 200);
            } else {
                return  response()->json(['status' => true, 'message' => 'You Can not delete this Stories'], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }


    public function getstories(Request $request)
    {
        try {
            $data1 = array();
            $token = auth('sanctum')->user();
            Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
            $getid =  Usermodel::where('remember_token', $token->remember_token)->first();
            $data = Storiesmodel::join('user_managements', 'user_managements.id', '=', 'stories.user_id')->orderBy('stories.id', 'DESC')->get(['stories.id as id', 'stories.user_id as user_id', 'stories.stories_type as stories_type', 'stories.file as file', 'stories.description as description', 'stories.visible_type as visible_type', 'stories.parent_id as parent_id', 'stories.tag as tag', 'stories.file_type as file_type', 'stories.created_at', 'user_managements.background_image as background_image', 'user_managements.profile_picture as profile_picture', 'user_managements.mobile as mobile', 'user_managements.email as email', 'user_managements.firstname as firstname', 'user_managements.lastname as lastname', 'user_managements.username as username']);


            if (!empty($data)) {
                $i = 0;
                foreach ($data as $data) {

                    $data1[$i] = $data;
                    $tag = array_map('intval', explode(',', $data['tag']));
                    $data1[$i]['tag'] = Usermodel::select('user_managements.id as id', 'user_managements.profile_picture as profile_picture', 'user_managements.firstname as firstname', 'user_managements.lastname as lastname', 'user_managements.username as username')->whereIn('id', $tag)->get();
                    $data1[$i]['comment_count'] = StoriesCommentmodel::where('user_id', $getid->id)->where('stories_id', $data['id'])->count();
                    $data1[$i]['like_count'] = 0; //Likemodel::where('user_id',$getid->id)->where('stories_id',$data['id'])->count();

                    $i++;
                }
                return  response()->json(['status' => true, 'data' => $data1], 200);
            } else {
                return  response()->json(['status' => false, 'message' => 'No data found'], 404);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }


    public function getstoriescommandbyid($stories_id)
    {
        try {
            //return $request->stories_id;
            $token = auth('sanctum')->user();
            Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
            $getid =  Usermodel::where('remember_token', $token->remember_token)->first();
            $data = StoriesCommentmodel::join('user_managements', 'user_managements.id', '=', 'stories_comment.user_id')->where('stories_comment.parent_id', 0)->where('stories_comment.stories_id', $stories_id)->orderBy('stories_comment.id', 'asc')->get(['stories_comment.id as id', 'stories_comment.user_id as user_id', 'stories_comment.stories_id as stories_id', 'stories_comment.description as description', 'stories_comment.parent_id as parent_id', 'stories_comment.created_at', 'user_managements.background_image as background_image', 'user_managements.profile_picture as profile_picture', 'user_managements.mobile as mobile', 'user_managements.email as email', 'user_managements.firstname as firstname', 'user_managements.lastname as lastname', 'user_managements.username as username']);
            $i = 0;
            $data1 = array();
            foreach ($data as $data) {
                $data1[$i] = $data;
                $data3 = StoriesCommentmodel::join('user_managements', 'user_managements.id', '=', 'stories_comment.user_id')->where('stories_comment.parent_id', $data['id'])->orderBy('stories_comment.id', 'asc')->get(['stories_comment.id as id', 'stories_comment.user_id as user_id', 'stories_comment.stories_id as stories_id', 'stories_comment.description as description', 'stories_comment.parent_id as parent_id', 'stories_comment.created_at', 'user_managements.background_image as background_image', 'user_managements.profile_picture as profile_picture', 'user_managements.mobile as mobile', 'user_managements.email as email', 'user_managements.firstname as firstname', 'user_managements.lastname as lastname', 'user_managements.username as username']);

                $data1[$i]['reply'] = $data3;

                $i++;
            }
            return  response()->json(['status' => true, 'data' => $data1], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }


    public function getstoriesbyid(Request $request)
    {
        try {
            $token = auth('sanctum')->user();
            Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
            $getid =  Usermodel::where('remember_token', $token->remember_token)->first();
            $data = Storiesmodel::join('user_managements', 'user_managements.id', '=', 'stories.user_id')->where('stories.id', $request->stories_id)->orderBy('stories.id', 'DESC')->get(['stories.id as id', 'stories.user_id as user_id', 'stories.stories_type as stories_type', 'stories.file as file', 'stories.description as description', 'stories.created_at', 'stories.visible_type as visible_type', 'stories.parent_id as parent_id', 'stories.tag as tag', 'stories.file_type as file_type', 'user_managements.background_image as background_image', 'user_managements.profile_picture as profile_picture', 'user_managements.mobile as mobile', 'user_managements.email as email', 'user_managements.firstname as firstname', 'user_managements.lastname as lastname', 'user_managements.username as username']);


            $tag = array_map('intval', explode(',', $data[0]['tag']));

            $data[0]['tag'] = Usermodel::select('user_managements.id as id', 'user_managements.profile_picture as profile_picture', 'user_managements.firstname as firstname', 'user_managements.lastname as lastname', 'user_managements.username as username')->whereIn('id', $tag)->get();
            $data1[0]['comment_count'] = StoriesCommentmodel::where('user_id', $getid->id)->where('stories_id', $data[0]['id'])->count();
            $data1[0]['like_count'] = Likemodel::where('user_id', $getid->id)->where('stories_id', $data[0]['id'])->count();
            return  response()->json(['status' => true, 'data' => $data], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }



    public function addstoriescomment(Request $request)
    {
        try {
            $token = auth('sanctum')->user();
            Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
            $getid =  Usermodel::where('remember_token', $token->remember_token)->first();

            $user = StoriesCommentmodel::create([
                'user_id' => $getid->id,
                'stories_id' => !empty($request->stories_id) ? $request->stories_id : 0,
                'description' => !empty($request->description) ? $request->description : NULL,
                'parent_id' => !empty($request->parent_id) ? $request->parent_id : 0,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Stories comment successfully added',

            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }



    public function getsubscribelist()
    {
        try {
            Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
            $data = Subscribemodel::get(['id', 'subscribe_title', 'description', 'days', 'subscribe_price']);

            return response()->json([
                'status' => true,
                'data' => $data,

            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }
    public function getsubscribebyid($id)
    {
        try {
            Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
            $data = Subscribemodel::where('id', $id)->get(['id', 'subscribe_title', 'description', 'days', 'subscribe_price']);

            return response()->json([
                'status' => true,
                'data' => $data,

            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }


    public function addsubscribtion(Request $request)
    {
        try {
            $token = auth('sanctum')->user();
            Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
            $getid =  Usermodel::where('remember_token', $token->remember_token)->first();
            $getsub =  Subscribemodel::where('id', $request->subscribe_id)->first();
            $date = date('Y-m-d');
            $exp_date = date('Y-m-d', strtotime($date . ' + ' . $getsub->days . ' days'));
            $user = Usersubscribemodel::create([
                'user_id' => $getid->id,
                'subscribe_id' => !empty($request->subscribe_id) ? $request->subscribe_id : 0,
                'description' => $getsub->description,
                'subscribe_price' => $getsub->subscribe_price,
                'exp_date' => $exp_date,
                'transaction_id' => !empty($request->transaction_id) ? $request->transaction_id : 0,
            ]);
            Usermodel::where('id', $getid->id)->update(['premium' => 1]);
            return response()->json([
                'status' => true,
                'message' => 'Subscription successfully added',

            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function getsubscriptiondetails()
    {
        try {
            //return $request->stories_id;
            $token = auth('sanctum')->user();
            Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
            $getid =  Usermodel::where('remember_token', $token->remember_token)->first();
            $data = Usersubscribemodel::join('user_managements', 'user_managements.id', '=', 'user_subscribe.user_id')->join('subscribe', 'subscribe.id', '=', 'user_subscribe.subscribe_id')->where('user_subscribe.user_id', $getid->id)->orderBy('user_subscribe.id', 'desc')->get(['user_subscribe.id as id', 'user_subscribe.subscribe_title as subscribe_title', 'user_subscribe.description as description', 'user_subscribe.subscribe_price as subscribe_price', 'user_subscribe.exp_date as exp_date', 'user_subscribe.transaction_id', 'user_managements.background_image as background_image', 'user_managements.profile_picture as profile_picture', 'user_managements.mobile as mobile', 'user_managements.email as email', 'user_managements.firstname as firstname', 'user_managements.lastname as lastname', 'user_managements.username as username']);

            return  response()->json(['status' => true, 'data' => $data], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }


    public function getonlineuser()
    {
        //                $token = auth('sanctum')->user();
        //                Usermodel::where('id', Auth::user()->id)->update(['online_status'=> 1, 'last_status_update'=>Carbon::now()->toDateTimeString()]);
        //         if (Usermodel::where('id', Auth::user()->id)->count() > 0) {
        //             $getprofile =  Usermodel::where('id', Auth::user()->id)->select('id as id', 'background_image as background_image','profile_picture as profile_picture', 'mobile as mobile','email as email', 'firstname as firstname', 'lastname as lastname','username as username', 'address as address', 'pincode as pincode', 'city as city', 'birthday as birthday', 'interest  as interest', 'country  as country', 'is_dob_disable  as dob_visible','bgc_verified as bgc_verified','story as story','premium as premium', 'fb_link  as fb_link', 'youtube_link  as youtube_link','instagram_link as instagram_link','tiktok_link as tiktok_link','password as password','registration_type as registration_type','school as school','status as status','remember_token as remember_token','email_verified_at as email_verified_at','create_user as create_user','update_user as update_user','is_first_login as is_first_login','created_at as created_at','updated_at as updated_at')->get();
        //        // return$getprofile[0];
        // 	//   $getid =  Usermodel::select('id  as id')->where('id', Auth::user()->id)->first();

        //  $data['friends'] =Friendrequestmodel::join('user_managements','user_managements.id','=','friend_tbl.friend_id')->where(['friend_tbl.friend_id'=>Auth::user()->id, 'user_managements.online_status'=> 1])->get(['user_managements.online_status as online_status','user_managements.qb_id as qb_id','user_managements.id as id','user_managements.background_image as background_image','user_managements.profile_picture as profile_picture', 'user_managements.mobile as mobile','user_managements.email as email', 'user_managements.firstname as firstname', 'user_managements.lastname as lastname','user_managements.username as username']);
        //   $arr=Friendrequestmodel::select('friend_for_id')->where('friend_id', Auth::user()->id)->get();
        // $data['others'] =Usermodel::whereNotIn('id', $arr)->where('online_status', 1)->limit(20)->offset(0)->get(['user_managements.online_status as online_status','user_managements.qb_id as qb_id','user_managements.id as id','user_managements.background_image as background_image','user_managements.profile_picture as profile_picture', 'user_managements.mobile as mobile','user_managements.email as email', 'user_managements.firstname as firstname', 'user_managements.lastname as lastname','user_managements.username as username']);

        // 		   return  response()->json(['status' => true, 'data' =>$data], 200);
        //         } else {
        //             return  response()->json(['token'=>$token,'status' => false, 'message' => 'No available data'], 404);
        //         }
        // ->select('id','online_status','qb_id','id','background_image','profile_picture', 'mobile','email', 'firstname', 'lastname','username')
        Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
        $user = Usermodel::where('id', Auth::user()->id)->first();
        $data['friends'] = [];
        $arr = [];

        foreach ($user->friends as $key => $value) {
            $arr[] = $value['id'];
            if ($value['online_status'] == 1) {
                $data['friends'][] = $value;
            }
        }
        $user_id[] = Auth::user()->id;
        $array = array_merge($arr, $user_id);
        $data['others'] = Usermodel::whereNotIn('id', $array)->where('online_status', 1)->limit(20)->get();
        return  response()->json(['status' => true, 'data' => $data], 200);
    }







    function add_album(Request $request)
    {


        try {
            $token = auth('sanctum')->user();
            Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
            $getid =  Usermodel::where('remember_token', $token->remember_token)->first();

            $url = '';
            $th = '';
            $imageName = '';
            if (!empty($request->file('file'))) {
                $file = $request->file('file');
                $imageName = time() . '.' . $file->extension();
                $imagePath = public_path() . '/file';
                $url = 'http://lt-api.mynewsystem.net/public/file/' . $imageName;
                $file->move($imagePath, $imageName);
                //if($request->file_type=='video'){

                if (strpos($request->file_type, 'video') !== false) {
                    $th = date('Ymdhi') . '.png';
                    VideoThumbnail::createThumbnail(
                        public_path('file/' . $imageName),
                        public_path('file/'),
                        $th,
                        2,
                        1920,
                        1080
                    );
                    $th = 'http://lt-api.mynewsystem.net/public/file/' . $th;
                } else {
                    $th = '';
                }
            }
            if (!empty($request->album_id)) {
                $data = Albummodel::where('id', $request->album_id)->first();
                $arr = array(
                    'user_id' => $getid->id,
                    'album_type' => 0,
                    'description' => !empty($request->description) ? $request->description : NULL,
                    'visible_type' => !empty($request->visible_type) ? $request->visible_type : 0,
                    'parent_id' => !empty($request->parent_id) ? $request->parent_id : 0,
                    'thumbnail' => $th,
                    'tag' => !empty($request->tag) ? $request->tag : 0

                );
                if (!empty($imageName)) {
                    $arr['file'] = $url;
                    $arr['file_type'] = !empty($request->file_type) ? $request->file_type : NULL;
                } else if ($request->is_file_deleted == true) {
                    $arr['file'] = '';
                    $arr['file_type'] = NUll;
                } else if ($request->is_file_deleted == false) {
                    //$arr['file']='';
                }
                $user = Albummodel::where('id', $request->album_id)->update($arr);

                return response()->json([
                    'status' => true,
                    'message' => 'Album Updated Successfully',

                ], 200);
            } else  if (!empty($request->parent_id)) {
                $data = Albummodel::where('id', $request->parent_id)->first();
                $arr = array(
                    'user_id' => $getid->id,
                    'album_type' => 0,
                    'description' => !empty($data->description) ? $data->description : NULL,
                    'new_description' => !empty($request->new_description) ? $request->new_description : NULL,
                    'visible_type' => !empty($request->visible_type) ? $request->visible_type : 0,
                    'parent_id' => !empty($request->parent_id) ? $request->parent_id : 0,
                    'file' => !empty($data->file) ? $data->file : NULL,
                    'thumbnail' => !empty($data->thumbnail) ? $data->thumbnail : NULL,
                    'file_type' => !empty($data->file_type) ? $data->file_type : NULL,
                    'tag' => !empty($data->tag) ? $data->tag : 0

                );
                Albummodel::create($arr);
                return response()->json([
                    'status' => true,
                    'message' => 'Album Successfully shared',

                ], 200);
            } else {
                $user = Albummodel::create([
                    'user_id' => $getid->id,
                    'album_type' => 0,
                    'description' => !empty($request->description) ? $request->description : NULL,
                    'visible_type' => !empty($request->visible_type) ? $request->visible_type : 0,
                    'parent_id' => !empty($request->parent_id) ? $request->parent_id : 0,
                    'file' => $url,
                    'thumbnail' => $th,
                    'file_type' => !empty($request->file_type) ? $request->file_type : NULL,
                    'tag' => !empty($request->tag) ? $request->tag : 0

                ]);
                return response()->json([
                    'status' => true,
                    'message' => 'Album Created Successfully',

                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function deletealbumbyid(Request $request)
    {
        try {
            $token = auth('sanctum')->user();
            Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
            $getid =  Usermodel::where('remember_token', $token->remember_token)->first();
            if (Albummodel::where('id', $request->albam_id)->where('user_id', $getid->id)->count() > 0) {
                $data = Albummodel::where('id', $request->news_feed_id)->where('user_id', $getid->id)->delete();

                return  response()->json(['status' => true, 'message' => 'Album Deleted'], 200);
            } else {
                return  response()->json(['status' => false, 'message' => 'You are not authorize to delete Album'], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }
    public function getalbum(Request $request)
    {
        try {
            $data1 = array();
            $token = auth('sanctum')->user();
            Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
            $getid =  Usermodel::where('remember_token', $token->remember_token)->first();
            $data['images'] = Albummodel::join('user_managements', 'user_managements.id', '=', 'album_tbl.user_id')->where('album_tbl.file_type', 'LIKE', '%image%')->orderBy('album_tbl.id', 'DESC')->get(['album_tbl.id as id', 'album_tbl.thumbnail as thumbnail', 'album_tbl.user_id as user_id', 'album_tbl.file as file', 'album_tbl.file_type as file_type', 'album_tbl.created_at', 'user_managements.background_image as background_image', 'user_managements.profile_picture as profile_picture', 'user_managements.mobile as mobile', 'user_managements.email as email', 'user_managements.firstname as firstname', 'user_managements.lastname as lastname', 'user_managements.username as username']);
            $data['videos'] = Albummodel::join('user_managements', 'user_managements.id', '=', 'album_tbl.user_id')->where('album_tbl.file_type', 'LIKE', '%video%')->orderBy('album_tbl.id', 'DESC')->get(['album_tbl.id as id', 'album_tbl.thumbnail as thumbnail', 'album_tbl.user_id as user_id', 'album_tbl.file as file', 'album_tbl.file_type as file_type', 'album_tbl.created_at', 'user_managements.background_image as background_image', 'user_managements.profile_picture as profile_picture', 'user_managements.mobile as mobile', 'user_managements.email as email', 'user_managements.firstname as firstname', 'user_managements.lastname as lastname', 'user_managements.username as username']);


            return  response()->json(['status' => true, 'data' => $data], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function createstripecustomer(Request $request)
    {
        try {

            $url = 'https://api.stripe.com/v1/customers';
            $token = auth('sanctum')->user();
            Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
            $getid =  Usermodel::where('remember_token', $token->remember_token)->first();
            if (empty($getid->stripe_customer_id)) {
                /* Init cURL resource */
                $ch = curl_init($url);

                /* Array Parameter Data */
                $data = array('email' => $getid->email, 'name' => $getid->firstname);

                /* pass encoded JSON string to the POST fields */
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

                /* set the content type json */
                $headers = [];
                $headers[] = 'Content-Type:application/x-www-form-urlencoded';
                $token = "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";
                $headers[] = "Authorization: Bearer " . $token;
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                /* set return type json */
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                /* execute request */
                $result = curl_exec($ch);
                $dt = json_decode($result);
                $id = $dt->id;
                Usermodel::where('id', $getid->id)->update(['stripe_customer_id' => $id]);
            } else {
                $id = $getid->stripe_customer_id;
            }
            /* close cURL resource */
            curl_close($ch);
            return response()->json([
                'status' => true,
                'data' => ['stripe_customer_id' => $id],

            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }
    public function createstripeephemeral_keys(Request $request)
    {
        try {

            $url = 'https://api.stripe.com/v1/ephemeral_keys';
            $token = auth('sanctum')->user();
            Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
            $getid =  Usermodel::where('remember_token', $token->remember_token)->first();
            if (!empty($getid->stripe_customer_id)) {
                /* Init cURL resource */
                $ch = curl_init($url);

                /* Array Parameter Data */
                $data = array('customer' => $getid->stripe_customer_id, 'Stripe-Version' => '2020-08-27');

                //  curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

                /* set the content type json */
                $headers = [];
                $headers[] = 'Content-Type:application/x-www-form-urlencoded';
                $headers[] = 'Stripe-Version:application/x-www-form-urlencoded';
                $token = "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";
                $headers[] = "Authorization: Bearer " . $token;
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                /* set return type json */
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                /* execute request */
                $result = curl_exec($ch);
                $dt = json_decode($result);
                /* close cURL resource */

                curl_close($ch);
                return response()->json([
                    'status' => true,
                    'data' => $dt,

                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'No customer find!',

                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }
    public function createstripepayment_intents(Request $request)
    {
        try {

            $url = 'https://api.stripe.com/v1/payment_intents';
            $token = auth('sanctum')->user();
            Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
            $getid =  Usermodel::where('id', Auth::user()->id)->first();
            if (!empty($getid->stripe_customer_id)) {
                /* Init cURL resource */
                //     $ch = curl_init($url);

                //     /* Array Parameter Data */
                //     $data = array('amount'=>$request->amount, 'currency'=>$request->currency,'customer'=>$getid->stripe_customer_id,'automatic_payment_methods[enabled]'=>true);

                //   //  curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

                //     /* set the content type json */
                //     $headers = [];
                //     $headers[] = 'Content-Type:application/x-www-form-urlencoded';
                //     $token = "sk_test_51H2k6ILI9TDc8QKmhkEu2xr3tkITff6C5UlpMQI9DNCeYnHzi0rtLT7KOFlsv2uJYMKEPmDK9YrD7iukGwaW3axX00UfQnTvKG";
                //     $headers[] = "Authorization: Bearer ".$token;
                //     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                //     /* set return type json */
                //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                //     /* execute request */
                //     $result = curl_exec($ch);
                //     //$dt=json_decode($result);
                //     /* close cURL resource */

                //     curl_close($ch);
                \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

                $intent = \Stripe\PaymentIntent::create([
                    'amount' => $request->amount * 100,
                    'currency' => $request->currency,
                    // 'customer' => $getid->stripe_customer_id,
                ]);
                $client_secret = $intent;

                return response()->json([
                    'status' => true,
                    'data' => $client_secret,

                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'No customer found!',

                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function updatetransactions(Request $request)
    {
        try {
            $token = auth('sanctum')->user();
            Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
            $getid =  Usermodel::where('remember_token', $token->remember_token)->first();

            $user = Transactionsmodel::create([
                'user_id' => $getid->id,
                'item_name' => !empty($request->item_name) ? $request->item_name : NULL,
                'item_price' => !empty($request->item_price) ? $request->item_price : NULL,
                'paid_amount' => !empty($request->paid_amount) ? $request->paid_amount : NULL,

                'item_price_currency' => !empty($request->item_price_currency) ? $request->item_price_currency : NULL,
                'paid_amount_currency' => !empty($request->paid_amount_currency) ? $request->paid_amount_currency : NULL,
                'txn_id' => !empty($request->txn_id) ? $request->txn_id : NULL,

                'payment_status' => !empty($request->payment_status) ? $request->payment_status : NULL,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Transactions successfully Saved',

            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function advertismentDeals(Request $request)
    {
        try {
            Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
            $advertisment = Advertisment::orderBy('id', 'desc')->where('plan_expiry_date', '>=', date('Y-m-d'))->where(['status'=> 1, 'type'=>'deals'])->get();
            if ($advertisment->count() > 0) {
                $paginate = Advertisment::with('advertiser')->orderBy('id', 'desc')->where('plan_expiry_date', '>=', date('Y-m-d'))->where(['status'=> 1, 'type'=>'deals'])->paginate(10);
                return response()->json([
                    'status' => true,
                    'message' => 'Advertisment found!',
                    'data' => $paginate,
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'No advertisment found!',
                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function advertismentBanners(Request $request)
    {
        try {
            Usermodel::where('id', Auth::user()->id)->update(['online_status' => 1, 'last_status_update' => Carbon::now()->toDateTimeString()]);
            $advertisment = Advertisment::orderBy('id', 'desc')->where('plan_expiry_date', '>=', date('Y-m-d'))->where(['status'=> 1, 'type'=>'banner'])->get();
            if ($advertisment->count() > 0) {
                $paginate = Advertisment::with('advertiser')->orderBy('id', 'desc')->where('plan_expiry_date', '>=', date('Y-m-d'))->where(['status'=> 1, 'type'=>'banner'])->paginate(12);
                return response()->json([
                    'status' => true,
                    'message' => 'Advertisment found!',
                    'data' => $paginate,
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'No advertisment found!',
                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function quickBloxIdentity(Request $request)
    {
        try {
            $user = Usermodel::where(['qb_id' => $request->IdP_user_ID, 'qb_password' => $request->IdP_token])->select('qb_id AS id', 'email AS login', DB::raw("CONCAT(firstname,' ',lastname) AS full_name"))->first();
            if ($user != null) {
                $message = "api hit perfeectly";
                Log::notice($user);
                return response()->json([
                    'user' => $user,
                ], 200);
            } else {
                $message = "No user found";
                Log::notice($message);
                return response()->json([
                    'status' => false,
                    'message' => 'No user found!',
                ], 401);
            }
        } catch (\Throwable $th) {
            $message = $th->getMessage();
            Log::notice($message);
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 422);
        }
    }

    public function logout(Request $request)
    {
        try {
            Usermodel::where('id', Auth::user()->id)->update(['online_status' => 0, 'last_status_update' => Carbon::now()->toDateTimeString()]);

            return response()->json([
                'status' => true,
                'message' => 'logged out'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function customEmoji(Request $request)
    {
        try {
            $customEmoji = CustomEmoji::get();

            return response()->json([
                'data' => $customEmoji,
                'status' => true,
                'message' => 'Data was found succesfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function deleteAccount(Request $request)
    {
        try {
            Usermodel::where('id', Auth::user()->id)->delete();

            return response()->json([
                'status' => true,
                'message' => 'Your account has been deleted successfully.'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function chatVideoThumbnail(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'file'  => 'required|mimes:mp4,mov,ogg,qt | max:20000'
            ]
        );
        if ($validator->fails()) {

            return response()->json([
                'status' => false,
                'error' => 'validation error',
                'message' => $validator->errors()->first()
            ], 401);
        }

        try {
            if (!empty($request->file('file'))) {
                $file = $request->file('file');
                $imageName = time() . '.' . $file->extension();
                $imagePath = public_path() . '/chat_thumbnail';
                $file->move($imagePath, $imageName);
                    $th = date('Ymdhi') . '.jpg';
                   $video = VideoThumbnail::createThumbnail(
                        public_path('chat_thumbnail/' . $imageName),
                        public_path('chat_thumbnail/thumb/'),
                        $th,
                        2,
                        1920,
                        1080
                    );
                    dd($video);
                    $th = 'http://lt-api.mynewsystem.net/public/chat_thumbnail/thumb/' . $th;
                    return response()->json([
                        'data' => $th,
                        'status' => true,
                        'message' => 'Thumbnail is ready.'
                    ], 200);

            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Thumbnail is not ready.'
                ], 500);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
