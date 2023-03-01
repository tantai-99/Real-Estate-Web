<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group([
    'middleware' => ['auth.check', 'admin.ipCheck'],
    'prefix' => 'admin'
], function () {
    Route::get('/', IndexController::class . '@index')->name('admin.index.index');
    Route::get('/company', CompanyController::class . '@index')->name('admin.company.index');
    Route::post('/company', CompanyController::class . '@index')->name('admin.company.search');

    Route::get('/auth/login/', AuthController::class . '@login')->name('admin.auth.login');
    Route::post('/auth/login/', AuthController::class . '@login')->name('admin.auth.login');
    Route::get('/auth/logout/', AuthController::class . '@logout')->name('admin.auth.logout');

    Route::get('/company/detail', CompanyController::class . '@detail')->name('admin.company.detail');
    Route::get('/company/delete', CompanyController::class . '@delete')->name('admin.company.delete');

    Route::get('/company/second-estate', CompanyController::class . '@secondEstate')->name('admin.company.second-estate');
    Route::post('/company/second-estate', CompanyController::class . '@secondEstate')->name('admin.company.second-estate.post');
    Route::get('/company/second-estate-confirm', CompanyController::class . '@secondEstateConfirm')->name('admin.company.second-estate-confirm');
    Route::post('/company/second-estate-confirm', CompanyController::class . '@secondEstateConfirm')->name('admin.company.second-estate-confirm.post');
    Route::get('/company/second-estate-complete', CompanyController::class . '@secondEstateComplete')->name('admin.company.second-estate-complete');

    Route::get('/company/group', CompanyController::class . '@group')->name('admin.company.group');
    Route::post('/company/group', CompanyController::class . '@group')->name('admin.company.postGroup');
    Route::post('/company/group-del', CompanyController::class . '@groupDel')->name('admin.company.groupDel');

    Route::get('/company/tag', CompanyController::class . '@tag');
    Route::get('/company/tag/company_id/{company_id}', CompanyController::class . '@tag')->name('admin.company.tag');
    Route::post('/company/tag/company_id/{company_id}', CompanyController::class . '@tag')->name('admin.company.tag.post');
    Route::get('/company/tag-cnf/company_id/{company_id}', CompanyController::class . '@tagCnf')->name('admin.company.tag-cnf');
    Route::post('/company/tag-cnf/company_id/{company_id}', CompanyController::class . '@tagCnf')->name('admin.company.tag-cnf.post');

    Route::get('/company/other-tag/company_id/{company_id}', CompanyController::class . '@otherTag')->name('admin.company.other-tag');
    Route::post('/company/other-tag/company_id/{company_id}', CompanyController::class . '@otherTag')->name('admin.company.other-tag.post');
    Route::get('/company/other-tag-cnf/company_id/{company_id}', CompanyController::class . '@otherTagCnf')->name('admin.company.other-tag-cnf');
    Route::post('/company/other-tag-cnf/company_id/{company_id}', CompanyController::class . '@otherTagCnf')->name('admin.company.other-tag-cnf.post');

    Route::get('/company/other-estate-tag/company_id/{company_id}', CompanyController::class . '@otherEstateTag')->name('admin.company.other-estate-tag');
    Route::post('/company/other-estate-tag/company_id/{company_id}', CompanyController::class . '@otherEstateTag')->name('admin.company.other-estate-tag.post');
    Route::get('/company/other-estate-tag-cnf/company_id/{company_id}', CompanyController::class . '@otherEstateTagCnf')->name('admin.company.other-estate-tag-cnf');
    Route::post('/company/other-estate-tag-cnf/company_id/{company_id}', CompanyController::class . '@otherEstateTagCnf')->name('admin.company.other-estate-tag-cnf.post');

    Route::get('/company/other-estate-request-tag/company_id/{company_id}', CompanyController::class . '@otherEstateRequestTag')->name('admin.company.other-estate-request-tag');
    Route::post('/company/other-estate-request-tag/company_id/{company_id}', CompanyController::class . '@otherEstateRequestTag')->name('admin.company.other-estate-request-tag.post');
    Route::get('/company/other-estate-request-tag-cnf/company_id/{company_id}', CompanyController::class . '@otherEstateRequestTagCnf')->name('admin.company.other-estate-request-tag-cnf');
    Route::post('/company/other-estate-request-tag-cnf/company_id/{company_id}', CompanyController::class . '@otherEstateRequestTagCnf')->name('admin.company.other-estate-request-tag-cnf.post');

    Route::get('/company/tag-cmp/company_id/{company_id}', CompanyController::class . '@tagCmp')->name('admin.company.tag-cmp');

    Route::get('/company/edit', CompanyController::class . '@edit')->name('admin.company.edit');
    Route::post('/company/edit', CompanyController::class . '@edit')->name('admin.company.postEdit');
    Route::get('/company/conf', CompanyController::class . '@conf')->name('admin.company.conf');
    Route::post('/company/conf', CompanyController::class . '@edit')->name('admin.company.postConf');
    Route::get('/company/comp', CompanyController::class . '@comp')->name('admin.company.comp');

    Route::get('/company/private', CompanyController::class . '@getPrivate')->name('admin.company.private');
    Route::post('/company/private', CompanyController::class . '@getPrivate')->name('admin.company.private');
    Route::get('/company/private-cmp', CompanyController::class . '@PrivateCmp')->name('admin.company.privateCmp');

    Route::get('/company/initialize-cms', CompanyController::class . '@initializeCms')->name('admin.company.initialize-cms');
    Route::post('/company/initialize-cms/', CompanyController::class . '@initializeCms')->name('admin.company.initialize-cms');
    Route::get('/company/initialize-cms-cmp/company_id/{id}', CompanyController::class . '@initializeCmsCmp')->name('admin.company.initialize-cms-cmp');

    Route::get('/company/estate-group', CompanyController::class . '@estateGroup')->name('admin.company.estateGroup');
    Route::post('/company/estate-group', CompanyController::class . '@estateGroup')->name('admin.company.postEstateGroup');

    Route::post('/api/get-estate-group-sub-companies-by-member-no-for-add', ApiController::class . '@getEstateGroupSubCompaniesByMemberNoForAdd')->name('admin.api.get-estate-group-sub-companies-by-member-no-for-add');
    Route::post('/api/get-estate-group-sub-companies-by-member-no', ApiController::class . '@getEstateGroupSubCompaniesByMemberNo')->name('admin.api.get-estate-group-sub-companies-by-member-no');

    Route::post('/company/estate-group-del', CompanyController::class . '@estateGroupDel')->name('admin.company.postEstateGroupDel');

    Route::get('/map-option/edit', MapOptionController::class . '@edit')->name('admin.map-option.edit');
    Route::post('/map-option/edit', MapOptionController::class . '@edit')->name('admin.map-option.edit.post');

    Route::get('/map-option/conf', MapOptionController::class . '@conf')->name('admin.map-option.conf');
    Route::post('/map-option/conf', MapOptionController::class . '@conf')->name('admin.map-option.conf.post');
    Route::get('/map-option/comp/id/{id}', MapOptionController::class . '@comp')->name('admin.map-option.comp');

    Route::get('/log',LogController::class.'@index')->name('admin.log.index');
    Route::post('/log',LogController::class.'@index')->name('admin.log.postIndex');
    Route::post('/log/enable-output',LogController::class.'@enableOutput')->name('admin.log.enable_output');

    Route::post('/api/get-company-for-memberno-check',ApiController::class.'@getCompanyForMembernoCheck')->name('admin.api.getCompanyForMembernoCheck');
    Route::post('/api/get-company-for-memberno',ApiController::class.'@getCompanyForMemberno')->name('admin.api.getCompanyForMemberno');

    Route::post('/api/get-at-member-for-no', ApiController::class . '@getAtMemberForNo')->name('admin.api.get-at-member-for-no');
    Route::post('/api/get-at-staff-for-cd', ApiController::class . '@getAtStaffForCd')->name('admin.api.get-at-staff-for-cd');
    Route::get('/api/make-demo-add-user', ApiController::class . '@makeDemoAddUser')->name('admin.api.make-demo-add-user');

    Route::get('/account', AccountController::class . '@index')->name('admin.account.index');
    Route::post('/account', AccountController::class . '@index')->name('admin.account.search');
    Route::get('/account/edit', AccountController::class . '@edit')->name('admin.account.edit');
    Route::post('/account/edit', AccountController::class . '@edit')->name('admin.account.edit.post');

    Route::get('/account/conf', AccountController::class . '@conf')->name('admin.account.conf');
    Route::post('/account/conf', AccountController::class . '@conf')->name('admin.account.conf.post');
    Route::get('/account/comp', AccountController::class . '@comp')->name('admin.account.comp');

    Route::get('/company/original-setting', CompanyController::class . '@originalSetting')->name('admin.company.original-setting');
    Route::post('/company/original-setting', CompanyController::class . '@originalSetting')->name('admin.company.original-setting.post');
    Route::get('/company/original-setting-confirm', CompanyController::class . '@originalSettingConfirm')->name('admin.company.original-setting-confirm');
    Route::post('/company/original-setting-confirm', CompanyController::class . '@originalSettingConfirm')->name('admin.company.original-setting-confirm.post');

    Route::get('/company/original-edit', CompanyController::class. '@originalEdit')->name('admin.company.original-edit');
    Route::get('/company/navigation-tag-list', CompanyController::class. '@navigationTagList')->name('admin.company.navigation-tag-list');
    Route::post('/company/navigation-tag-list', CompanyController::class. '@navigationTagList')->name('admin.company.navigation-tag-list.post');
    Route::get('/company/top-notification', CompanyController::class. '@topNotification')->name('admin.company.top-notification');
    Route::post('/company/top-notification', CompanyController::class. '@topNotification')->name('admin.company.top-notification.top');
    Route::get('/company/top-list-file-edit', CompanyController::class. '@topListFileEdit')->name('admin.company.top-list-file-edit');
    Route::get('/company/top-housing-block', CompanyController::class. '@topHousingBlock')->name('admin.company.top-housing-block');
    Route::post('/company/top-housing-block', CompanyController::class. '@topHousingBlock')->name('admin.company.top-housing-block.post');
    Route::post('/company/api-read-top-housing-block', CompanyController::class . '@apiReadTopHousingBlock')->name('admin.company.api-read-top-housing-block');

    Route::post('/company/api-upload-file', CompanyController::class . '@apiUploadFile')->name('admin.company.api-upload-file');
    Route::post('/company/api-synchronize-upload-progress', CompanyController::class . '@apiSynchronizeUploadProgress')->name('admin.company.api-synchronize-upload-progress');
    Route::post('/company/api-save-file', CompanyController::class . '@apiSaveFile')->name('admin.company.api-save-file');
    Route::get('/company/dl-top-search-js', CompanyController::class . '@dlTopSearchJs')->name('admin.company.dl-top-search-js');

    Route::get('/company/csv', CompanyController::class . '@csv')->name('admin.company.csv');
    Route::get('/company/pdf', CompanyController::class . '@pdf')->name('admin.company.pdf');

    Route::get('/information', InformationController::class . '@index')->name('admin.information.index');
    Route::post('/information', InformationController::class . '@index')->name('admin.information.search');
    Route::get('/information/edit', InformationController::class . '@edit')->name('admin.information.edit');
    Route::post('/information/edit', InformationController::class . '@edit')->name('admin.information.edit.post');
    Route::post('/api/set-file-upload', ApiController::class . '@setFileUpload')->name('admin.api.set-file-upload');
    Route::post('/api/set-p12-file-upload/company_id/{company_id}', ApiController::class . '@setP12FileUpload')->name('admin.api.sset-p12-file-upload');

    Route::get('/information/conf', InformationController::class . '@conf')->name('admin.information.conf');
    Route::post('/information/conf', InformationController::class . '@conf')->name('admin.information.conf.post');
    Route::get('/information/comp', InformationController::class . '@comp')->name('admin.information.comp');

    Route::get('password', PasswordController::class . '@index')->name('admin.password.index');
    Route::post('password', PasswordController::class . '@index')->name('admin.postPassword');
    Route::get('spamblock', SpamBlockController::class . '@index')->name('admin.spamblock.index');
    Route::post('spamblock', SpamBlockController::class . '@index')->name('admin.postspamblock');
    Route::get('spamblock/edit', SpamBlockController::class . '@edit')->name('admin.spamblock.edit');
    Route::post('spamblock/edit', SpamBlockController::class . '@edit')->name('admin.spamblock.editpost');
    Route::get('spamblock/delete', SpamBlockController::class . '@delete')->name('admin.spamblock.delete');
    Route::post('spamblock/delete', SpamBlockController::class . '@delete')->name('admin.spamblock.deletepost');
    Route::post('company/get-params-preview', CompanyController::class . '@getParamsPreview')->name('admin.company.getParamsPreview');
});
