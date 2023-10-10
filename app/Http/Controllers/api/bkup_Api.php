<?php

namespace App\Http\Controllers\api;

use App\Models\Usermodel;
use App\Models\Interestsmodel;
use App\Models\Countrymodel;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Mail;
use DB;

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
                    'password' => 'required'
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
                'firstname' => !empty($request->firstname)?$request->firstname:NULL,
                'lastname' => !empty($request->lastname)?$request->lastname:NULL,
				'email' => $request->email,
                'birthday' => !empty($request->birthday)?$request->birthday:NULL,
                'password' => Hash::make($request->password),
				'country' => !empty($request->country)?$request->country:NULL,
				'mobile' => !empty($request->mobile)?$request->mobile:NULL,
                'registration_type' => !empty($request->registration_type)?$request->registration_type:1
            ]);
            $token=$user->createToken("API TOKEN")->plainTextToken;
            Usermodel::where('email',$request->email)->update(['remember_token'=>$token]);
            return response()->json([
                'status' => true,
                'message' => 'Account Created Successfully',
                'token' => $token
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message'=>'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }
    public function loginuser(Request $request)
    {
        try {
            if($request->registration_type==1){
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

            $user = Usermodel::where('email', $request->email)->first();
            $token=$user->createToken("API TOKEN")->plainTextToken;
            if(Usermodel::where('email', $request->email)->where('is_first_login', true)->count()>0){
                $is_first_login=false;
            Usermodel::where('email',$request->email)->update(['remember_token'=>$token,'fcm_token'=>$request->fcm_token]);
            }
            else{
                $is_first_login=true;
                Usermodel::where('email',$request->email)->update(['remember_token'=>$token,'fcm_token'=>$request->fcm_token,'is_first_login'=>true]);
            }
            return response()->json(['status' => true, 'is_first_login'=>$is_first_login,'message' => 'User Logged Successfully', 'token' =>$token]); 
           
        }
     
        else{
            if(Usermodel::where('user_id', $request->user_id)->count()>0){
            $user = Usermodel::where('user_id', $request->user_id)->first();
            $token=$user->createToken("API TOKEN")->plainTextToken;
            if(Usermodel::where('email', $request->email)->where('is_first_login', true)->count()>0){
                $is_first_login=false;
                Usermodel::where('email',$request->user_id)->update(['remember_token'=>$token,'fcm_token'=>$request->fcm_token]);
         
            }
            else{
                $is_first_login=true;
            Usermodel::where('email',$request->user_id)->update(['remember_token'=>$token,'fcm_token'=>$request->fcm_token,'is_first_login'=>true]);
             } 
             return response()->json(['status' => true, 'is_first_login'=>$is_first_login,'message' => 'User Logged Successfully', 'token' =>$token]); 
           
            }
            else{
                $user = Usermodel::create([
                    'firstname' => !empty($request->firstname)?$request->firstname:NULL,
                    'lastname' => !empty($request->lastname)?$request->lastname:NULL,
                    'email' => $request->email,
                    'birthday' => !empty($request->birthday)?$request->birthday:NULL,
                    'country' => !empty($request->country)?$request->country:NULL,
                    'mobile' => !empty($request->mobile)?$request->mobile:NULL,
                    'registration_type' => !empty($request->registration_type)?$request->registration_type:2,
                    'user_id'=>$request->user_id
                ]);
                $token=$user->createToken("API TOKEN")->plainTextToken;
                Usermodel::where('user_id',$request->user_id)->update(['remember_token'=>$token]);
                return response()->json([
                    'status' => true,
                    'message' => 'Account Created Successfully',
                    'token' => $token
                ], 200);
               // return response()->json(['status' => false, 'message' => 'Invalid User ID']);  
            }
        }
    }catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage()], 500);
        }
    }










    
    public function forgetpassword(Request $request)
    {
       try{
        if(Usermodel::where('email', $request->email)->count()>0){
            $user = Usermodel::where('email', $request->email)->first();
        $to_name = $request->firstname;
        $to_email = $request->email;
        $otp=rand(1000,9999);
        $data = array('name'=>'', 'message' => 'Your OTP for forgot password is'.$otp);
        Mail::send('mail', $data, function($message) use ($to_name, $to_email) {
        $message->to($to_email, $to_name)
        ->subject('One time password for LIT');
        $message->from('test@stdev.in','LIT');
        });  
        Usermodel::where('email', $request->email)->update(['otp'=>$otp,'otp_time'=>date('Y-m-d H:i:s')]);
        return response()->json(['status' => true, 'message' => 'OTP send','temp_token' => $user->remember_token]);
     
    }  
    else{
        return response()->json(['status' => false, 'message' => 'E-mail address not found'], 404);
    }  
}  catch (\Throwable $th) {
    return response()->json(['status' => false, 'message' => $th->getMessage()], 500);
}  
    }
    public function matchotp(Request $request)
    {
        try{
        if(Usermodel::where('remember_token', $request->temp_token)->where('otp',$request->otp)->count()>0){
            $user = Usermodel::where('email', $request->email)->first();
            return response()->json(['status' => true, 'message' => 'OTP validated'], 200); 
        }
        else{
            return response()->json(['status' => false, 'message' => 'Invalid OTP'], 200); 
        }
    }
    catch (\Throwable $th) {
        return response()->json(['status' => false, 'message' => $th->getMessage()], 500);
    } 
    }

    public function resetpassword(Request $request)
    {
        try{
        if(Usermodel::where('remember_token', $request->temp_token)->count()>0){
            Usermodel::where('remember_token', $request->temp_token)->update(['password'=>Hash::make($request->password)]);
            return response()->json(['status' => true, 'message' => 'Password successfully changed'], 200); 
        }
        else{
            return response()->json(['status' => false, 'message' => 'Invalid User'], 200); 
        }
    }
    catch (\Throwable $th) {
        return response()->json(['status' => false, 'message' => $th->getMessage()], 500);
    } 
    }



    public function getprofilebyid(Request $request)
    {
  
        if ( Usermodel::where('remember_token', $request->token)->count() > 0) {
            $getprofile =  Usermodel::where('remember_token', $request->token)->select('id as Id', 'background_image as BackgroundImage','profile_picture as ProfilePicture', 'mobile as Mobile','email as Email', 'firstname as Firstname', 'lastname as Lastname','username as Username', 'address as Address', 'pincode as Pincode', 'city as City', 'birthday as Birthday', 'interest  as Interest ', 'country  as Country')->get();
            return  response()->json(['status' => true, 'data' => $getprofile], 200);
        } else {
            return  response()->json(['status' => false, 'message' => 'No available data'], 404);
        }
    }


    public function getinterestlist()
    {


        if ( Interestsmodel::count() > 0) {
            $getinterest =  Interestsmodel::select('interest_id as InterestId', 'interest as Interest')->get();
            return  response()->json(['status' => true, 'data' => $getinterest], 200);
        } else {
            return  response()->json(['status' => false, 'message' => 'No available interest data'], 200);
        }
    }


    public function getcountrylist()
    {


        if ( Countrymodel::count() > 0) {
            $getcountry =  Countrymodel::select('country_id  as CountryId ', 'country as Country')->get();
            return  response()->json(['status' => true, 'data' => $getcountry], 200);
        } else {
            return  response()->json(['status' => false, 'message' => 'No available country'], 200);
        }
    }


    public function profileupdate(Request $request)
    {
        try {
          
            if (!empty($request->token)){
            $user = array(
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
				
                                  
            );
        

        
       
        $id = Usermodel::where('remember_token', $request->token)->update($user);
        
            return  response()->json(['status' => true,  'message' => 'Profile data has been updated successfully!'], 200);
        } else {
            return  response()->json(['status' => false,  'message' => 'An error occurred, please try again later '], 200);
        }
          
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message'=>'Internal server error!',
                'error' => $th->getMessage()
            ], 500);
        }
    }

  public function save_background_image(Request $request)
    {
    	try{
           
   
        $image_path = $request->file('background_image')->store('background_image', 'public');

      $url='http://lt-api.mynewsystem.net/'.$image_path;
            return  response()->json(['status' => true, 'url'=>$url,  'message' => 'Background picture Store'], 200);
       
          
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message'=>'Internal server error!'
                
            ], 500);
        }
    }


     public function save_profile_picture(Request $request)
    {
    	try{
     
      
        $image_path = $request->file('profile_picture')->store('profile_picture', 'public');

       
      $url='http://lt-api.mynewsystem.net/'.$image_path;
            return  response()->json(['status' => true,'url'=>$url,  'message' => 'Profile picture Store'], 200);
      
          
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message'=>'Internal server error!'
                
            ], 500);
        }
    }
}
?>