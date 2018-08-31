<?php

use Illuminate\Http\Request;

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

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('logout', 'Auth\LoginController@logout');

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::patch('settings/profile', 'Settings\ProfileController@update');
    Route::patch('settings/password', 'Settings\PasswordController@update');

    Route::get('jobs/all', 'Jobs\JobsController@getJobs');
    Route::post('jobs/create', 'Jobs\JobsController@create');
    Route::post('jobs/{id}/update', 'Jobs\JobsController@update');
    Route::get('jobs/{id}/process', 'Jobs\JobsController@getJobProcessById');
    Route::patch('jobs/{id}/cancel', 'Jobs\JobsController@cancelJobProcess');
    Route::patch('jobs/{id}/resume', 'Jobs\JobsController@resumeJobProcess');

    Route::get('jobs/refseq', 'Jobs\PhyloController@getRefseqJobs');
    Route::get('jobs/refseq/{refseq_id}', 'Jobs\PhyloController@getJobsByRefseq');
    Route::post('jobs/phylo/construct', 'Jobs\PhyloController@constructPhylo');
    Route::get('jobs/phylo/{id}/view', 'Jobs\PhyloController@viewPhylo');
    Route::get('jobs/phylo/all', 'Jobs\PhyloController@getAllPhylo');

    Route::get('data/sequences', 'Jobs\JobsController@getSequences');
    Route::get('data/snpeff', 'Jobs\JobsController@snpEffDB');

    Route::get('db-snp/{job_id}', 'ExploreController@getSnpInfo');
    Route::get('db-snp/detail/{id}', 'ExploreController@getSnpDetail');

    Route::get('test', 'Jobs\JobsController@coba');

});

Route::group(['middleware' => 'guest:api'], function () {
    Route::post('login', 'Auth\LoginController@login');
    Route::post('register', 'Auth\RegisterController@register');
    Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
    Route::post('password/reset', 'Auth\ResetPasswordController@reset');

    Route::post('oauth/{driver}', 'Auth\OAuthController@redirectToProvider');
    Route::get('oauth/{driver}/callback', 'Auth\OAuthController@handleProviderCallback')->name('oauth.callback');
    
    Route::get('testaja', function(){
	if(app()->isLocal()) echo "Local";
	else echo "Prod";
	echo config('app.env');
    });
});

Route::get('login', function () {
    return redirect('login');
})->name('login');
