<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Manager\ManagerRepository;
use App\Repositories\Manager\ManagerRepositoryInterface;
use App\Repositories\Company\CompanyRepository;
use App\Repositories\Company\CompanyRepositoryInterface;
use App\Repositories\Mpref\MprefRepository;
use App\Repositories\Mpref\MprefRepositoryInterface;
use App\Repositories\MTheme\MThemeRepository;
use App\Repositories\MTheme\MThemeRepositoryInterface;
use App\Repositories\MLayout\MLayoutRepository;
use App\Repositories\MLayout\MLayoutRepositoryInterface;
use App\Repositories\MColor\MColorRepository;
use App\Repositories\MColor\MColorRepositoryInterface;
use App\Repositories\MAreaCategory\MAreaCategoryRepository;
use App\Repositories\MAreaCategory\MAreaCategoryRepositoryInterface;
use App\Repositories\Information\InformationRepository;
use App\Repositories\Information\InformationRepositoryInterface;
use App\Repositories\InformationFiles\InformationFilesRepository;
use App\Repositories\InformationFiles\InformationFilesRepositoryInterface;
use App\Repositories\CompanyAccount\CompanyAccountRepository;
use App\Repositories\CompanyAccount\CompanyAccountRepositoryInterface;
use App\Repositories\AssociatedCompanyHp\AssociatedCompanyHpRepository;
use App\Repositories\AssociatedCompanyHp\AssociatedCompanyHpRepositoryInterface;
use App\Repositories\AssociatedCompanyFdp\AssociatedCompanyFdpRepository;
use App\Repositories\AssociatedCompanyFdp\AssociatedCompanyFdpRepositoryInterface;
use App\Repositories\AssociatedCompany\AssociatedCompanyRepository;
use App\Repositories\AssociatedCompany\AssociatedCompanyRepositoryInterface;
use App\Repositories\LogDelete\LogDeleteRepository;
use App\Repositories\LogDelete\LogDeleteRepositoryInterface;
use App\Repositories\Hp\HpRepository;
use App\Repositories\Hp\HpRepositoryInterface;
use App\Repositories\HpPage\HpPageRepository;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\HpImage\HpImageRepository;
use App\Repositories\HpImage\HpImageRepositoryInterface;
use App\Repositories\HpImageCategory\HpImageCategoryRepository;
use App\Repositories\HpImageCategory\HpImageCategoryRepositoryInterface;
use App\Repositories\HpImageContent\HpImageContentRepository;
use App\Repositories\HpImageContent\HpImageContentRepositoryInterface;
use App\Repositories\HpFile2Content\HpFile2ContentRepository;
use App\Repositories\HpFile2Content\HpFile2ContentRepositoryInterface;
use App\Repositories\HpFile2Category\HpFile2CategoryRepository;
use App\Repositories\HpFile2Category\HpFile2CategoryRepositoryInterface;
use App\Repositories\HpFileContent\HpFileContentRepository;
use App\Repositories\HpFileContent\HpFileContentRepositoryInterface;
use App\Repositories\ReleaseSchedule\ReleaseScheduleRepository;
use App\Repositories\ReleaseSchedule\ReleaseScheduleRepositoryInterface;
use App\Repositories\ReleaseScheduleSpecial\ReleaseScheduleSpecialRepository;
use App\Repositories\ReleaseScheduleSpecial\ReleaseScheduleSpecialRepositoryInterface;
use App\Repositories\SpecialEstate\SpecialEstateRepository;
use App\Repositories\SpecialEstate\SpecialEstateRepositoryInterface;
use App\Repositories\HpEstateSetting\HpEstateSettingRepository;
use App\Repositories\HpEstateSetting\HpEstateSettingRepositoryInterface;
use App\Repositories\EstateClassSearch\EstateClassSearchRepository;
use App\Repositories\EstateClassSearch\EstateClassSearchRepositoryInterface;
use App\Repositories\OriginalSetting\OriginalSettingRepository;
use App\Repositories\OriginalSetting\OriginalSettingRepositoryInterface;
use App\Repositories\SecondEstate\SecondEstateRepository;
use App\Repositories\SecondEstate\SecondEstateRepositoryInterface;
use App\Repositories\HpArea\HpAreaRepository;
use App\Repositories\HpArea\HpAreaRepositoryInterface;
use App\Repositories\HpMainParts\HpMainPartsRepository;
use App\Repositories\HpMainParts\HpMainPartsRepositoryInterface;
use App\Repositories\Tag\TagRepository;
use App\Repositories\Tag\TagRepositoryInterface;
use App\Repositories\EstateTag\EstateTagRepository;
use App\Repositories\EstateTag\EstateTagRepositoryInterface;
use App\Repositories\EstateRequestTag\EstateRequestTagRepository;
use App\Repositories\EstateRequestTag\EstateRequestTagRepositoryInterface;
use App\Repositories\LogInitializeCms\LogInitializeCmsRepository;
use App\Repositories\LogInitializeCms\LogInitializeCmsRepositoryInterface;
use App\Repositories\EstateAssociatedCompany\EstateAssociatedCompanyRepository;
use App\Repositories\EstateAssociatedCompany\EstateAssociatedCompanyRepositoryInterface;
use App\Repositories\SpamBlock\SpamBlockRepository;
use App\Repositories\SpamBlock\SpamBlockRepositoryInterface;
use App\Repositories\CompanySpamBlock\CompanySpamBlockRepository;
use App\Repositories\CompanySpamBlock\CompanySpamBlockRepositoryInterface;
use App\Repositories\LogEdit\LogEditRepository;
use App\Repositories\LogEdit\LogEditRepositoryInterface;
use App\Repositories\PublishProgress\PublishProgressRepository;
use App\Repositories\PublishProgress\PublishProgressRepositoryInterface;
use App\Repositories\HpSiteImage\HpSiteImageRepository;
use App\Repositories\HpSiteImage\HpSiteImageRepositoryInterface;
use App\Repositories\HpContact\HpContactRepository;
use App\Repositories\HpContact\HpContactRepositoryInterface;
use App\Repositories\HpSideParts\HpSidePartsRepository;
use App\Repositories\HpSideParts\HpSidePartsRepositoryInterface;
use App\Repositories\HpTopImage\HpTopImageRepository;
use App\Repositories\HpTopImage\HpTopImageRepositoryInterface;
use App\Repositories\HpMainElement\HpMainElementRepository;
use App\Repositories\HpMainElement\HpMainElementRepositoryInterface;
use App\Repositories\HpMainElementElement\HpMainElementElementRepository;
use App\Repositories\HpMainElementElement\HpMainElementElementRepositoryInterface;
use App\Repositories\HpSideElements\HpSideElementsRepository;
use App\Repositories\HpSideElements\HpSideElementsRepositoryInterface;
use App\Repositories\HpAssessment\HpAssessmentRepositoryInterface;
use App\Repositories\HpAssessment\HpAssessmentRepository;
use App\Repositories\ContactCount\ContactCountRepositoryInterface;
use App\Repositories\ContactCount\ContactCountRepository;
use App\Repositories\EstateContactCount\EstateContactCountRepositoryInterface;
use App\Repositories\EstateContactCount\EstateContactCountRepository;
use App\Repositories\HpImageUsed\HpImageUsedRepository;
use App\Repositories\HpImageUsed\HpImageUsedRepositoryInterface;
use App\Repositories\HpFile2\HpFile2Repository;
use App\Repositories\HpFile2\HpFile2RepositoryInterface;
use App\Repositories\HpFile2ContentLength\HpFile2ContentLengthRepository;
use App\Repositories\HpFile2ContentLength\HpFile2ContentLengthRepositoryInterface;
use App\Repositories\HpContactParts\HpContactPartsRepository;
use App\Repositories\HpContactParts\HpContactPartsRepositoryInterface;
use App\Repositories\SecondEstateClassSearch\SecondEstateClassSearchRepository;
use App\Repositories\SecondEstateClassSearch\SecondEstateClassSearchRepositoryInterface;
use App\Repositories\HpInfoDetailLink\HpInfoDetailLinkRepository;
use App\Repositories\HpInfoDetailLink\HpInfoDetailLinkRepositoryInterface;
use App\Repositories\HpFile2Used\HpFile2UsedRepository;
use App\Repositories\HpFile2Used\HpFile2UsedRepositoryInterface;
use App\Repositories\AssociatedHpPageAttribute\AssociatedHpPageAttributeRepository;
use App\Repositories\AssociatedHpPageAttribute\AssociatedHpPageAttributeRepositoryInterface;
use App\Repositories\SecondEstateExclusion\SecondEstateExclusionRepository;
use App\Repositories\SecondEstateExclusion\SecondEstateExclusionRepositoryInterface;
use App\Repositories\HpFileContentLength\HpFileContentLengthRepository;
use App\Repositories\HpFileContentLength\HpFileContentLengthRepositoryInterface;
use App\Repositories\HpHtmlContent\HpHtmlContentRepository;
use App\Repositories\HpHtmlContent\HpHtmlContentRepositoryInterface;
use App\Repositories\ContactLog\ContactLogRepository;
use App\Repositories\ContactLog\ContactLogRepositoryInterface;
use App\Repositories\ViewBukken\ViewBukkenRepository;
use App\Repositories\ViewBukken\ViewBukkenRepositoryInterface;
use App\Repositories\FavoriteBukken\FavoriteBukkenRepository;
use App\Repositories\FavoriteBukken\FavoriteBukkenRepositoryInterface;
use App\Repositories\Conversion\ConversionRepository;
use App\Repositories\Conversion\ConversionRepositoryInterface;
use App\Repositories\HankyoPlusLog\HankyoPlusLogRepository;
use App\Repositories\HankyoPlusLog\HankyoPlusLogRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ManagerRepositoryInterface::class, function () {
            return new ManagerRepository();
        });
        $this->app->singleton(CompanyRepositoryInterface::class, function () {
            return new CompanyRepository();
        });
        $this->app->singleton(MprefRepositoryInterface::class, function () {
            return new MprefRepository();
        });
        $this->app->singleton(MThemeRepositoryInterface::class, function () {
            return new MThemeRepository();
        });
        $this->app->singleton(MAreaCategoryRepositoryInterface::class, function () {
            return new MAreaCategoryRepository();
        });
        $this->app->singleton(InformationRepositoryInterface::class, function () {
            return new InformationRepository();
        });
        $this->app->singleton(InformationFilesRepositoryInterface::class, function () {
            return new InformationFilesRepository();
        });
        $this->app->singleton(CompanyAccountRepositoryInterface::class, function () {
            return new CompanyAccountRepository();
        });
        $this->app->singleton(AssociatedCompanyHpRepositoryInterface::class, function () {
            return new AssociatedCompanyHpRepository();
        });
        $this->app->singleton(LogDeleteRepositoryInterface::class, function () {
            return new LogDeleteRepository();
        });
        $this->app->singleton(HpRepositoryInterface::class, function () {
            return new HpRepository();
        });
        $this->app->singleton(HpPageRepositoryInterface::class, function () {
            return new HpPageRepository();
        });
        $this->app->singleton(HpImageRepositoryInterface::class, function () {
            return new HpImageRepository();
        });
        $this->app->singleton(HpImageCategoryRepositoryInterface::class, function () {
            return new HpImageCategoryRepository();
        });
        $this->app->singleton(HpImageContentRepositoryInterface::class, function () {
            return new HpImageContentRepository();
        });
        $this->app->singleton(ReleaseScheduleRepositoryInterface::class, function () {
            return new ReleaseScheduleRepository();
        });
        $this->app->singleton(ReleaseScheduleSpecialRepositoryInterface::class, function () {
            return new ReleaseScheduleSpecialRepository();
        });
        $this->app->singleton(SpecialEstateRepositoryInterface::class, function () {
            return new SpecialEstateRepository();
        });
        $this->app->singleton(HpEstateSettingRepositoryInterface::class, function () {
            return new HpEstateSettingRepository();
        });
        $this->app->singleton(EstateClassSearchRepositoryInterface::class, function () {
            return new EstateClassSearchRepository();
        });
        $this->app->singleton(OriginalSettingRepositoryInterface::class, function () {
            return new OriginalSettingRepository();
        });
        $this->app->singleton(SecondEstateRepositoryInterface::class, function () {
            return new SecondEstateRepository();
        });
		$this->app->singleton(AssociatedCompanyFdpRepositoryInterface::class, function () {
            return new AssociatedCompanyFdpRepository();
        });
        $this->app->singleton(AssociatedCompanyRepositoryInterface::class, function () {
            return new AssociatedCompanyRepository();
        });
        $this->app->singleton(HpAreaRepositoryInterface::class, function () {
            return new HpAreaRepository();
        });
        $this->app->singleton(HpMainPartsRepositoryInterface::class, function () {
            return new HpMainPartsRepository();
		});
        $this->app->singleton(LogInitializeCmsRepositoryInterface::class, function () {
            return new LogInitializeCmsRepository();
        });
        $this->app->singleton(TagRepositoryInterface::class, function () {
            return new TagRepository();
        });
        $this->app->singleton(EstateTagRepositoryInterface::class, function () {
            return new EstateTagRepository();
        });
        $this->app->singleton(EstateRequestTagRepositoryInterface::class, function () {
            return new EstateRequestTagRepository();
        });
        $this->app->singleton(EstateAssociatedCompanyRepositoryInterface::class, function () {
            return new EstateAssociatedCompanyRepository();
        });
        $this->app->singleton(SpamBlockRepositoryInterface::class, function () {
            return new SpamBlockRepository();
        });
        $this->app->singleton(CompanySpamBlockRepositoryInterface::class, function () {
            return new CompanySpamBlockRepository();
        });
        $this->app->singleton(LogEditRepositoryInterface::class, function () {
            return new LogEditRepository();
        });
        $this->app->singleton(PublishProgressRepositoryInterface::class, function () {
            return new PublishProgressRepository();
        });
        $this->app->singleton(ReleaseScheduleSpecialRepositoryInterface::class, function () {
            return new ReleaseScheduleSpecialRepository();
        });
        $this->app->singleton(HpContactRepositoryInterface::class, function () {
            return new HpContactRepository();
        });
        $this->app->singleton(HpSidePartsRepositoryInterface::class, function () {
            return new HpSidePartsRepository();
        });
        $this->app->singleton(HpTopImageRepositoryInterface::class, function () {
            return new HpTopImageRepository();
        });
        $this->app->singleton(HpMainElementRepositoryInterface::class, function () {
            return new HpMainElementRepository();
        });
        $this->app->singleton(HpMainElementElementRepositoryInterface::class, function () {
            return new HpMainElementElementRepository();
        });
        $this->app->singleton(HpSideElementsRepositoryInterface::class, function () {
            return new HpSideElementsRepository();
        });
        $this->app->singleton(HpAssessmentRepositoryInterface::class, function () {
            return new HpAssessmentRepository();
        });
        $this->app->singleton(EstateContactCountRepositoryInterface::class, function () {
            return new EstateContactCountRepository();
        });
        $this->app->singleton(ContactCountRepositoryInterface::class, function () {
            return new ContactCountRepository();
        });
        $this->app->singleton(HpSiteImageRepositoryInterface::class, function () {
            return new HpSiteImageRepository();
        });
        $this->app->singleton(HpImageUsedRepositoryInterface::class, function () {
            return new HpImageUsedRepository();
        });
        $this->app->singleton(HpFile2ContentRepositoryInterface::class, function () {
            return new HpFile2ContentRepository();
        });
        $this->app->singleton(HpFile2CategoryRepositoryInterface::class, function () {
            return new HpFile2CategoryRepository();
        });
        $this->app->singleton(HpFile2RepositoryInterface::class, function () {
            return new HpFile2Repository();
        });
        $this->app->singleton(HpFile2ContentLengthRepositoryInterface::class, function () {
            return new HpFile2ContentLengthRepository();
        });
        $this->app->singleton(HpFileContentRepositoryInterface::class, function () {
            return new HpFileContentRepository();
        });
        $this->app->singleton(HpImageRepositoryInterface::class, function () {
            return new HpImageRepository();
        });
        $this->app->singleton(HpImageCategoryRepositoryInterface::class, function () {
            return new HpImageCategoryRepository();
        });
        $this->app->singleton(HpImageContentRepositoryInterface::class, function () {
            return new HpImageContentRepository();
        });
        $this->app->singleton(MColorRepositoryInterface::class, function () {
            return new MColorRepository();
        });
        $this->app->singleton(MLayoutRepositoryInterface::class, function () {
            return new MLayoutRepository();
        });
        $this->app->singleton(HpContactPartsRepositoryInterface::class, function () {
            return new HpContactPartsRepository();
        });
        $this->app->singleton(SecondEstateClassSearchRepositoryInterface::class, function () {
            return new SecondEstateClassSearchRepository();
        });
        $this->app->singleton(HpInfoDetailLinkRepositoryInterface::class, function () {
            return new HpInfoDetailLinkRepository();
        });
        $this->app->singleton(HpFile2UsedRepositoryInterface::class, function () {
            return new HpFile2UsedRepository();
        });
        $this->app->singleton(AssociatedHpPageAttributeRepositoryInterface::class, function () {
            return new AssociatedHpPageAttributeRepository();
        });
        $this->app->singleton(SecondEstateExclusionRepositoryInterface::class, function () {
            return new SecondEstateExclusionRepository();
        });
        $this->app->singleton(HpFileContentLengthRepositoryInterface::class, function () {
            return new HpFileContentLengthRepository();
        });
        $this->app->singleton(HpHtmlContentRepositoryInterface::class, function () {
            return new HpHtmlContentRepository();
        });
        $this->app->singleton(ContactLogRepositoryInterface::class, function () {
            return new ContactLogRepository();
        });
        $this->app->singleton(ViewBukkenRepositoryInterface::class, function () {
            return new ViewBukkenRepository();
        });
        $this->app->singleton(FavoriteBukkenRepositoryInterface::class, function () {
            return new FavoriteBukkenRepository();
        });
        $this->app->singleton(ConversionRepositoryInterface::class, function () {
            return new ConversionRepository();
        });
        $this->app->singleton(HankyoPlusLogRepositoryInterface::class, function () {
            return new HankyoPlusLogRepository();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
