<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\PublishController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\DiacrisisController;
use App\Http\Controllers\SiteSettingController;
use App\Http\Controllers\SeoAdviceController;
use App\Http\Controllers\ApiUploadController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\DesignSampleController;
use App\Http\Controllers\EstateSearchSettingController;
use App\Http\Controllers\DataLinkController;
use App\Http\Controllers\SecondEstateSearchSettingController;
use App\Http\Controllers\ApiEstateController;
use App\Http\Controllers\InitializeController;
use App\Http\Controllers\SecondEstateExclusionController;
use App\Http\Controllers\UtilityController;
use App\Http\Controllers\SiteMapController;
use App\Http\Controllers\EstateSpecialController;
use App\Http\Controllers\CreatorController;
use App\Http\Controllers\SourceController;
use App\Http\Controllers\PlainfileController;
use App\Http\Controllers\InformationController;
use App\Http\Controllers\TopController;
use App\Http\Controllers\FileController;

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
    'middleware' => 'auth.check',
    'prefix' => '/'

], function () {
    Route::get('/', IndexController::class . '@index')->name('default.index.index');

    Route::get('information', InformationController::class . '@index')->name('default.information.index');
    Route::get('information/detail', InformationController::class . '@detail')->name('default.information.detail');
    Route::get('default/information/download', InformationController::class . '@download')->name('default.information.download');
    Route::get('information/download', InformationController::class . '@download')->name('default.information.download');

    route::get('/index/confirm-capacity',IndexController::class . '@confirmCapacity')->name('default.index.confirmCapacity');

    Route::get('auth/login/', AuthController::class . '@login')->name('default.auth.login');
    Route::post('auth/login/', AuthController::class . '@login')->name('default.auth.login.post');
    Route::get('auth/logout/', AuthController::class . '@logout')->name('default.auth.logout');
    Route::get('auth/list', AuthController::class . '@list')->name('default.auth.list');
    Route::get('auth/detail', AuthController::class . '@detail')->name('default.auth.detail');
    Route::get('auth/download', AuthController::class . '@download')->name('default.auth.download');

    Route::get('password', PasswordController::class . '@index')->name('default.password.index');
    Route::post('default/password', PasswordController::class . '@index')->name('default.password.index');
    Route::get('default/password', PasswordController::class . '@index')->name('default.password.index');

    Route::get('publish/simple', PublishController::class . '@simple')->name('default.publish.simple');
    Route::get('publish/detail', PublishController::class . '@detail')->name('default.publish.detail');
    Route::get('publish/testsite', PublishController::class . '@testsite')->name('default.publish.testsite');
    Route::post('publish/api-publish', PublishController::class . '@apiPublish')->name('default.publish.api_publish');
    Route::get('publish/progress', PublishController::class . '@progress')->name('default.publish.progress');
    Route::get('publish/site-delete', PublishController::class . '@siteDelete')->name('default.publish.site_delete');
    Route::post('publish/preview-page/id/{id}/parent_id/{parent_id?}/device/{device}', PublishController::class . '@previewPage')->name('default.publish.preview_page');
    Route::get('publish/preview-page/id/{id}/parent_id/{parent_id?}/device/{device}', PublishController::class . '@previewPage')->name('default.publish.preview_page.get');
    
    Route::get('source/src/id/{id}/parent_id/{parent_id?}/device/{device}/path/{path}', SourceController::class . '@src')->name('default.source.src');
    Route::get('source/src/id/{id}/parent_id/{parent_id?}/device/{device}/imgs/{imgs}', SourceController::class . '@src');
    Route::get('source/src/id/{id}/parent_id/{parent_id?}/device/{device}/imgs/{color}/{imgs}', SourceController::class . '@src');
    
    Route::get('/diacrisis',    DiacrisisController::class . '@index')->name('default.diacrisis.index');
    Route::get('diacrisis/rating',    DiacrisisController::class . '@rating')->name('default.diacrisis.rating');
    Route::get('diacrisis/analysis',    DiacrisisController::class . '@analysis')->name('default.diacrisis.analysis');
    Route::post('diacrisis/api-get-analysis-summary',    DiacrisisController::class . '@apiGetAnalysisSummary');
    Route::post('diacrisis/api-get-analysis-access',    DiacrisisController::class . '@apiGetAnalysisAccess');
    Route::post('diacrisis/api-get-analysis-access-device',    DiacrisisController::class . '@apiGetAnalysisAccessDevice');
    Route::post('diacrisis/api-get-analysis-access-media',    DiacrisisController::class . '@apiGetAnalysisAccessMedia');
    Route::post('diacrisis/api-get-analysis-access-page-ranking',    DiacrisisController::class . '@apiGetAnalysisAccessPageRanking');
    Route::post('diacrisis/api-get-analysis-access-page-view',    DiacrisisController::class . '@apiGetAnalysisAccessPageView');

    Route::get('/utility',    UtilityController::class . '@index')->name('default.utility.index');
    Route::get('/utility/manual',    UtilityController::class . '@manual');
    Route::get('/utility/manual_toppageoriginal',    UtilityController::class . '@manualToppageoriginal');
    Route::get('/utility/main-image-guideline',    UtilityController::class . '@mainImageGuideline')->name('utility.main-image-guideline');
    Route::get('/utility/main-image',    UtilityController::class . '@mainImage');
    Route::get('/utility/favicon',    UtilityController::class . '@favicon');
    Route::get('/utility/decoration-guideline',    UtilityController::class . '@decorationGuideline');
    Route::get('/utility/decoration',    UtilityController::class . '@decoration');
    Route::post('/utility/api-decoration',    UtilityController::class . '@apiDecoration');
    Route::get('/utility/illustration-guideline',    UtilityController::class . '@illustrationGuideline');
    Route::get('/utility/illustration',    UtilityController::class . '@illustration');
    Route::get('/utility/banner',    UtilityController::class . '@banner');
    Route::get('/utility/customer',    UtilityController::class . '@customer');
    Route::get('/utility/athome-banner',    UtilityController::class . '@athomeBanner');
    Route::get('/utility/smart-application-guideline',    UtilityController::class . '@smartApplicationGuideline');
    Route::get('/utility/smart-application',    UtilityController::class . '@smartApplication');
    Route::get('/utility/seo',    UtilityController::class . '@seo');
    Route::get('/utility/seo/pdf/seo_blog.pdf',    UtilityController::class . '@seo');
    Route::get('/utility/seo/pdf/seo_campaign.pdf',    UtilityController::class . '@seo');
    Route::get('/utility/seo/pdf/seo_backnumber.pdf',    UtilityController::class . '@seo');
    Route::get('/utility/seo/pdf/seo_newpage.pdf',    UtilityController::class . '@seo');
    Route::get('/utility/seo/pdf/seo_update.pdf',    UtilityController::class . '@seo');
    Route::get('/utility/seo/pdf/seo_originalpage.pdf',    UtilityController::class . '@seo');
    Route::get('/utility/seo/pdf/seo_pagevolume.pdf',    UtilityController::class . '@seo');
    Route::get('/utility/seo/pdf/seo_othersites.pdf',    UtilityController::class . '@seo');
    Route::get('/utility/seo/pdf/seo_pageadd.pdf',    UtilityController::class . '@seo');
    Route::get('/utility/seo/pdf/seo_buttonlink.pdf',    UtilityController::class . '@seo');
    Route::get('/utility/seo/pdf/seo_searchengine.pdf',    UtilityController::class . '@seo');
    Route::get('/utility/seo/pdf/seo_originaltext.pdf',    UtilityController::class . '@seo');
    Route::get('/utility/seo/pdf/seo_cleanup.pdf',    UtilityController::class . '@seo');
    Route::get('/utility/seo/pdf/seo_category.pdf',    UtilityController::class . '@seo');
    Route::get('/utility/seo/pdf/seo_pagelink.pdf',    UtilityController::class . '@seo');
    Route::get('/utility/seo/pdf/seo_sitename.pdf',    UtilityController::class . '@seo');

    Route::get('/utility/usepoint',    UtilityController::class . '@usepoint');
    Route::get('/utility/decoration-image',    UtilityController::class . '@decorationImage');

    Route::get('site-setting', SiteSettingController::class . '@index')->name('default.sitesetting.index');
    Route::post('site-setting/api-save-index', SiteSettingController::class . '@apiSaveIndex')->name('api-save-index');
    Route::get('/seo-advice/content-common', SeoAdviceController::class . '@contentCommon')->name('default.seo-advice.content-common');
    Route::get('/seo-advice/tdk-common', SeoAdviceController::class . '@tdkCommon')->name('default.seo-advice.tdk-common');
    Route::post('/api-upload/favicon', ApiUploadController::class . '@favicon')->name('default.api-upload.favicon');
    Route::get('/image/favicon', ImageController::class . '@favicon')->name('default.image.favicon');
    Route::post('/api-upload/webclip', ApiUploadController::class . '@webclip')->name('default.api-upload.webclip');
    Route::get('/image/webclip', ImageController::class . '@webclip')->name('default.image.webclip');
    Route::post('/api-upload/site-logo-pc', ApiUploadController::class . '@siteLogoPc')->name('default.api-upload.site-logo-pc');
    Route::get('/image/site-logo-pc', ImageController::class . '@siteLogoPc')->name('default.image.site-logo-pc');
    Route::post('/api-upload/site-logo-sp', ApiUploadController::class . '@siteLogoSp')->name('default.api-upload.site-logo-sp');
    Route::get('/image/site-logo-sp', ImageController::class . '@siteLogoSp')->name('default.image.site-logo-sp');
    Route::get('/image/company-qr', ImageController::class . '@companyQr')->name('default.image.company_qr');

    // design template
    route::get('site-setting/design', SiteSettingController::class . '@design')->name('default.sitesetting.design');
    route::post('site-setting/api-save-design', SiteSettingController::class . '@apiSaveDesign')->name('default.site-setting.api-save-design');
    route::get('design-sample', DesignSampleController::class . '@index')->name('design-sample.index');

    //Site-setting/image
    Route::get('site-setting/image', SiteSettingController::class . '@image')->name('default.sitesetting.image');
    Route::post('site-setting/api-save-image', SiteSettingController::class . '@apiSaveImage')->name('api-save-image');
    Route::post('api-upload/hp-image', ApiUploadController::class . '@hpImage')->name('api-upload/hp-image');
    Route::get('image/hp-image', ImageController::class . '@hpImage')->name('image/hp-image');
    Route::post('site-setting/api-save-image-category', SiteSettingController::class . '@apiSaveImageCategory');
    Route::post('site-setting/api-get-hppages-by-useimage', SiteSettingController::class . '@apiGetHppagesByUseimage');
    Route::get('site-setting/download-image', SiteSettingController::class . '@downloadImage');
    Route::post('site-setting/api-remove-image', SiteSettingController::class . '@apiRemoveImage');
    Route::post('site-setting/api-edit-image-category', SiteSettingController::class . '@apiEditImageCategory');
    //site-setting/file2
    Route::get('site-setting/file2', SiteSettingController::class . '@file2')->name('default.sitesetting.file2');
    Route::post('api-upload/hp-file2', ApiUploadController::class . '@hpFile2')->name('api-upload/hp-file2');
    Route::post('api-upload/hp-file2-info', ApiUploadController::class . '@hpFile2Info')->name('api-upload/hp-file2-info');
   
    Route::get('file/hp-file2', FileController::class . '@hpFile2')->name('file/hp-file2');
    Route::get('file/hp-file', FileController::class . '@hpFile')->name('file/hp-file');

    Route::post('site-setting/api-save-file2', SiteSettingController::class . '@apiSaveFile2')->name('api-save-file2');
    Route::post('site-setting/api-save-file2-category', SiteSettingController::class . '@apiSaveFile2Category');
    Route::post('site-setting/api-get-hppages-by-usefile2', SiteSettingController::class . '@apiGetHppagesByUsefile2');
    Route::get('site-setting/download-file2', SiteSettingController::class . '@downloadFile2');
    Route::post('site-setting/api-remove-file2', SiteSettingController::class . '@apiRemoveFile2');
    Route::post('site-setting/api-edit-file2-category', SiteSettingController::class . '@apiEditFile2Category');
    //second-estate-search-setting
    Route::get('second-estate-search-setting', SecondEstateSearchSettingController::class . '@index')->name('default.secondestatesearchsetting.index');
    Route::get('second-estate-search-setting/edit', SecondEstateSearchSettingController::class . '@edit')->name('edit');
    Route::get('second-estate-search-setting/detail', SecondEstateSearchSettingController::class . '@detail')->name('detail');
    Route::get('second-estate-exclusion', SecondEstateExclusionController::class . '@index')->name('default.secondestateexclusion.index');
    Route::get('second-estate-exclusion/search', SecondEstateExclusionController::class . '@search')->name('default.secondestateexclusion.search');
    Route::post('second-estate-exclusion/search', SecondEstateExclusionController::class . '@search')->name('default.secondestateexclusion.search.post');
    Route::post('second-estate-exclusion/detail', SecondEstateExclusionController::class . '@detail')->name('default.secondestateexclusion.detail');
    Route::post('second-estate-exclusion/regist', SecondEstateExclusionController::class . '@regist')->name('default.secondestateexclusion.regist');
    Route::post('second-estate-exclusion/delete', SecondEstateExclusionController::class . '@delete')->name('default.secondestateexclusion.delete');

    Route::post('api-estate/shikugun', ApiEstateController::class . '@shikugun')->name('default.api-estate.shikugun');
    Route::post('api-estate/choson', ApiEstateController::class . '@choson')->name('default.api-estate.choson');
    Route::post('api-estate/ensen', ApiEstateController::class . '@ensen')->name('default.api-estate.ensen');
    Route::post('api-estate/eki', ApiEstateController::class . '@eki')->name('default.api-estate.eki');
    Route::post('api-estate/second-search-filter', ApiEstateController::class . '@secondSearchFilter')->name('api-estate/second-search-filter');
    Route::post('second-estate-search-setting/api-save', SecondEstateSearchSettingController::class . '@apiSave')->name('api-save');

    //estate-search-setting
    Route::get('estate-search-setting', EstateSearchSettingController::class . '@index')->name('default.estate-search-setting.index');
    Route::get('estate-search-setting/detail', EstateSearchSettingController::class . '@detail')->name('default.estate-search-setting.detail');

    Route::get('estate-search-setting/edit', EstateSearchSettingController::class . '@edit')->name('default.estate-search-setting.edit');
    Route::post('estate-search-setting/api-save', EstateSearchSettingController::class . '@apiSave')->name('api-save');
    Route::post('estate-search-setting/api-delete', EstateSearchSettingController::class . '@apiDelete')->name('api-delete');

    // Initialize index
    Route::get('initialize', InitializeController::class . '@index')->name('default.initialize.index');
    Route::post('initialize/api-save-index', InitializeController::class . '@apiSaveIndex')->name('initialize.api-save-index');
    // Initialize design
    Route::get('initialize/design', InitializeController::class . '@design')->name('default.initialize.design');
    Route::post('initialize/api-save-design', InitializeController::class . '@apiSaveDesign')->name('api-save-design');
    // Initialize top-page
    Route::get('initialize/top-page', InitializeController::class . '@topPage')->name('default.initialize.top-page');
    Route::post('initialize/api-save-top-page', InitializeController::class . '@apiSaveTopPage')->name('api-save-top-page');
    // Initialize company-profile
    Route::get('initialize/company-profile', InitializeController::class . '@companyProfile')->name('default.initialize.company-profile');
    Route::post('initialize/api-save-company-profile', InitializeController::class . '@apiSaveCompanyProfile')->name('api-save-company-profile');
    // Initialize privacy-policy
    Route::get('initialize/privacy-policy', InitializeController::class . '@privacyPolicy')->name('default.initialize.privacy-policy');
    Route::post('initialize/api-save-privacy-policy', InitializeController::class . '@apiSavePrivacyPolicy')->name('api-save-privacy-policy');
    // Initialize site-policy
    Route::get('initialize/site-policy', InitializeController::class . '@sitePolicy')->name('default.initialize.site-policy');
    Route::post('initialize/api-save-site-policy', InitializeController::class . '@apiSaveSitePolicy')->name('api-save-site-policy');
    // Initialize contact
    Route::get('initialize/contact', InitializeController::class . '@contact')->name('default.initialize.contact');
    Route::post('initialize/api-save-contact', InitializeController::class . '@apiSaveContact')->name('api-save-contact');
    // Initialize complete
    Route::get('initialize/complete', InitializeController::class . '@complete')->name('default.initialize.complete');
    Route::post('initialize/api-save-complete', InitializeController::class . '@complete')->name('api-save-complete');

    Route::get('/seo-advice/content', SeoAdviceController::class . '@content')->name('default.seo-advice.content');
    Route::get('/seo-advice/tdk', SeoAdviceController::class . '@tdk')->name('default.seo-advice.tdk');

    //Site-Map
    Route::get('site-map', SiteMapController::class . '@index')->name('default.site-map.index');
    Route::post('site-map/api-create-page', SiteMapController::class . '@apiCreatePage')->name('api-create-page');
    Route::post('site-map/api-sort', SiteMapController::class . '@apiSort')->name('api-sort');
    Route::post('site-map/api-remove-from-menu', SiteMapController::class . '@apiRemoveFromMenu')->name('api-remove-from-menu');
    Route::post('site-map/api-add-page', SiteMapController::class . '@apiAddPage')->name('api-add-page');
    Route::post('site-map/api-create-alias', SiteMapController::class . '@apiCreateAlias')->name('api-create-alias');
    Route::post('site-map/api-update-alias', SiteMapController::class . '@apiUpdateAlias')->name('api-update-alias');
    Route::post('site-map/api-create-link', SiteMapController::class . '@apiCreateLink')->name('api-create-link');
    Route::post('site-map/api-update-link', SiteMapController::class . '@apiUpdateLink')->name('api-update-link');

    Route::get('site-map/article', SiteMapController::class . '@article')->name('default.site-map.article');
    Route::post('site-map/api-create-page-article', SiteMapController::class . '@apiCreatePageArticle')->name('api-create-page-article');
    Route::post('site-map/api-delete-page-article', SiteMapController::class . '@apiDeletePageArticle')->name('api-delete-page-article');
    Route::post('site-map/api-save-set-link-article', SiteMapController::class . '@apiSaveSetLinkArticle')->name('api-save-set-link-article');

    // estate special
    route::get('/estate-special', EstateSpecialController::class . '@index')->name('default.estatespecial.index');
    route::get('/estate-special/new', EstateSpecialController::class . '@new')->name('default.estatespecial.new');
    route::get('/estate-special/detail', EstateSpecialController::class . '@detail')->name('default.estatespecial.detail');

    route::post('estate-special/api-validate-basic', EstateSpecialController::class . '@apiValidateBasic')->name('default.estatespecial.apiValidateBasic');
    route::post('estate-special/api-validate-method', EstateSpecialController::class . '@apiValidateMethod')->name('default.estatespecial.apiValidateMethod');
    route::post('estate-special/api-save', EstateSpecialController::class . '@apiSave')->name('default.estatespecial.apiSave');
    route::post('estate-special/api-delete', EstateSpecialController::class . '@apiDelete')->name('default.estatespecial.apiDelete');
    route::post('estate-special/api-copy', EstateSpecialController::class . '@apiCopy')->name('default.estatespecial.apiCopy');
    route::post('api-estate/special-search-filter', ApiEstateController::class . '@specialSearchFilter')->name('default.api-estate.specialSearchFilter');
    route::get('estate-special/edit', EstateSpecialController::class . '@new')->name('default.estatespecial.edit');

    Route::get('page/edit', PageController::class . '@edit')->name('default.page.edit');
    Route::post('page/api-save', PageController::class . '@apiSave')->name('page.api-save');
    Route::post('data-link/api-get-update-page-parts', DataLinkController::class . '@apiGetUpdatePageParts')->name('api.get.update.parts');
    Route::post('/page/api-validate', PageController::class . '@apiValidate')->name('page.api-validate');
    Route::get('/page/api-validate', PageController::class . '@apiValidate')->name('page.api-validate.get');
    Route::post('/page/api-validate-terminology', PageController::class . '@apiValidateTerminology')->name('page.api-validate-terminology');
    Route::post('/page/api-delete', PageController::class . '@apiDelete')->name('page.api-delete');
    Route::post('/page/api-test-mail', PageController::class . '@apiTestMail')->name('page.api-test-mail');
    Route::post('site-setting/api-get-images', SiteSettingController::class . '@apiGetImages')->name('site-setting.api-get-images');
    Route::post('site-setting/api-get-file2', SiteSettingController::class . '@apiGetFile2')->name('site-setting.api-get-file2');

    Route::post('api-upload/hp-file', ApiUploadController::class . '@hpFile')->name('api-upload/hp-file');
    Route::post('api-upload/hp-file-info', ApiUploadController::class . '@hpFileInfo')->name('api-upload/hp-file-info');
    Route::post('site-setting/api-get-file2', SiteSettingController::class . '@apiGetFile2')->name('default.sitesetting.apiGetFile2');

    Route::post('data-link/api-get-update-notification', DataLinkController::class . '@apiGetUpdateNotification')->name('api.get.update.notification');
    Route::post('data-link/api-get-update-navigation', DataLinkController::class . '@apiGetUpdateNavigation')->name('api.get.update.navigation');
    Route::post('data-link/api-get-update-estate-koma', DataLinkController::class . '@apiGetUpdateEstateKoma')->name('api.get.update.estate.koma');

    Route::get('plainFile/ckeditorConfig', PlainfileController::class . '@ckeditorconfig')->name('plain-file');
    Route::post('plainFile/decoration-files', PlainfileController::class . '@decorationFiles')->name('decoration-files');
    Route::get('top/js/{file}/', TopController::class . '@js');
    Route::get('top/css/{file}/', TopController::class . '@css');
    Route::get('top/images/{file}/', TopController::class . '@images');

    Route::post('api-estate/house-all', ApiEstateController::class . '@houseAll');
}); 
Route::group([
    'middleware' => 'creator.check',
], function () {
    Route::get('agency', CreatorController::class . '@index');
    Route::get('creator', CreatorController::class . '@index');
    
    Route::get('creator/login/', CreatorController::class . '@login')->name('creator.login');

    Route::post('creator/login/', CreatorController::class . '@login')->name('creator.login.post');
    Route::get('creator/select-company', CreatorController::class . '@selectCompany')->name('creator.select-company');
    Route::get('creator/publish', CreatorController::class . '@publish')->name('creator.publish');
    Route::post('creator/select-company', CreatorController::class . '@selectCompany')->name('creator.select-company');
    Route::get('creator/re-select-company', CreatorController::class . '@reSelectCompany')->name('creator.re-select-company');

    Route::get('creator/rollback', CreatorController::class . '@rollback')->name('default.creator.rollback');
    Route::post('creator/api-rollback', CreatorController::class . '@apiRollback')->name('api-rollback');

    Route::get('creator/copy-to-company', CreatorController::class . '@copyToCompany')->name('creator.copy-to-company');
    Route::post('creator/api-copy-to-company', CreatorController::class . '@apiCopyToCompany')->name('creator.api-copy-to-company.post');
    Route::get('creator/delete-hp', CreatorController::class . '@deleteHp')->name('default.creator.delete-hp');
    Route::post('creator/api-delete-hp', CreatorController::class . '@apiDeleteHp')->name('default.creator.api-delete-hp');

    Route::get('initialize/copy', InitializeController::class . '@copy')->name('default.copy');
    Route::post('initialize/copy', InitializeController::class . '@copy')->name('default.copy.post');
    Route::get('initialize/copy-complete', InitializeController::class . '@copyComplete')->name('initialize.copy-complete');
});

    