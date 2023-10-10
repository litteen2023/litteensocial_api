<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\Api;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('/auth/login', [Api::class, 'loginuser']);
Route::post('/auth/register', [Api::class, 'createUser']);
Route::group(['middleware' => ['auth:sanctum']], function () {
Route::post('/profileupdate', [Api::class, 'profileupdate']);

Route::post('/auth/forgetpassword', [Api::class, 'forgetpassword']);
Route::post('/auth/matchotp', [Api::class, 'matchotp']);
Route::post('/auth/resetpassword', [Api::class, 'resetpassword']);
Route::post('/getprofilebyid', [Api::class, 'getprofilebyid']);
Route::get('/getinterestlist', [Api::class, 'getinterestlist']);
Route::get('/getcountrylist', [Api::class, 'getcountrylist']);

Route::post('/save_background_image', [Api::class, 'save_background_image']);
Route::post('/updatetransactions', [Api::class, 'updatetransactions']);

Route::post('/save_profile_picture', [Api::class, 'save_profile_picture']);
Route::post('/getuserlistfilter', [Api::class, 'getuserlistfilter']);
Route::post('/getprofile', [Api::class, 'getprofileid']);
Route::post('/addnewsfeedlike', [Api::class, 'addnewsfeedlike']);
Route::post('/getlikecount', [Api::class, 'getlikecount']);
Route::post('/savefile', [Api::class, 'savefile']);
Route::post('/add_friend_request', [Api::class, 'add_friend_request']);
Route::post('/add_like_profile', [Api::class, 'add_like_profile']);
Route::post('/add_profile_view', [Api::class, 'add_profile_view']);

Route::get('/getnewsfeed', [Api::class, 'getnewsfeed']);
Route::get('/getnewsfeedbyid/{news_feed_id}', [Api::class, 'getnewsfeedbyid']);

Route::post('/add_news_feed', [Api::class, 'add_news_feed']);
Route::post('/add_album', [Api::class, 'add_album']);
Route::post('/add_stories', [Api::class, 'add_stories']);
Route::post('/addnewsfeedcomment', [Api::class, 'addnewsfeedcomment']);
Route::get('/getnewsfeedcommandbyid/{news_feed_id}', [Api::class, 'getnewsfeedcommandbyid']);
Route::post('/deletenewsfeedbyid', [Api::class, 'deletenewsfeedbyid']);
Route::post('/mutenewsfeedbyid', [Api::class, 'mutenewsfeedbyid']);
Route::get('/get_friend_list', [Api::class, 'get_friend_list']);
Route::get('/meet_new_friend', [Api::class, 'meet_new_friend']);

Route::get('/getstoriescommandbyid/{stories_id}', [Api::class, 'getstoriescommandbyid']);
Route::post('/deletestoriesbyid', [Api::class, 'deletestoriesbyid']);
Route::get('/getstories', [Api::class, 'getstories']);
Route::get('/getstoriesbyid/{stories_id}', [Api::class, 'getstoriesbyid']);
Route::post('/addstoriescomment', [Api::class, 'addstoriescomment']);

Route::get('/getsubscribebyid/{subscribe_id}', [Api::class, 'getsubscribebyid']);
Route::get('/getsubscribelist', [Api::class, 'getsubscribelist']);
Route::post('/addsubscribtion', [Api::class, 'addsubscribtion']);
Route::get('/getsubscriptiondetails', [Api::class, 'getsubscriptiondetails']);
Route::get('/getonlineuser', [Api::class, 'getonlineuser']);
Route::post('/deletealbumbyid', [Api::class, 'deletealbumbyid']);
Route::post('/getalbum', [Api::class, 'getalbum']);
Route::post('/createstripepayment_intents', [Api::class, 'createstripepayment_intents']);
Route::post('/createstripecustomer', [Api::class, 'createstripecustomer']);
Route::post('/createstripeephemeral_keys', [Api::class, 'createstripeephemeral_keys']);
// new api
Route::post('/advertisment-deals', [Api::class, 'advertismentDeals']);
Route::post('/advertisment-banners', [Api::class, 'advertismentBanners']);
Route::post('/delete-account', [Api::class, 'deleteAccount']);
Route::post('/logout', [Api::class, 'logout']);
Route::post('/chat-video-thumbnail', [Api::class, 'chatVideoThumbnail']);

});
// quick blox
Route::get('/quick-blox-identity', [Api::class, 'quickBloxIdentity']);
// redirect app url
Route::get('/redirect-app/{id}', [Api::class, 'redirectAppUrl'])->name('redirect-app');
Route::get('/custom-emoji', [Api::class, 'customEmoji']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
