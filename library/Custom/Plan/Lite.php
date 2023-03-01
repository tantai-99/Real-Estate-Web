<?php
namespace Library\Custom\Plan;
use Library\Custom\Plan;
use App\Repositories\HpPage\HpPageRepository;
/**
 *	ライト・プランの情報クラス
 */
class Lite	extends Plan
{
	public 	$initialPages	=	array(										// 初期のページ構成
		'main'	=> array(														// メインメニュー
			HpPageRepository::TYPE_TOP					=> array(),			//TOP
			HpPageRepository::TYPE_COMPANY				=> array(),			// 会社紹介
			HpPageRepository::TYPE_SHOP_INDEX			=> array(			// 店舗案内
				HpPageRepository::TYPE_SHOP_DETAIL			=> array(			// 店舗詳細
				),
			),
            HpPageRepository::TYPE_TERMINOLOGY			=> array(),			// 不動産用語集
            // 5352 ページの作成/更新に旧ひな形を追加できないようにする
            // HpPageRepository::TYPE_PREVIEW				=> array(),			// 内見時のチェックポイント
			// HpPageRepository::TYPE_MOVING				=> array(),			// 引越しのチェックポイント
		),
		'fix'	=> array(														// 固定メニュー
			HpPageRepository::TYPE_INFO_INDEX			=> array(),			// お知らせ
			HpPageRepository::TYPE_PRIVACYPOLICY		=> array(),			// プライバシーポリシー
			HpPageRepository::TYPE_SITEPOLICY			=> array(),			// サイトポリシー
            // HpPageRepository::TYPE_USEFUL_REAL_ESTATE_INFORMATION => array(),
			HpPageRepository::TYPE_FORM_CONTACT			=> array(),			// 会社問い合わせ
		),
	) ;
	
	public	$categoryMap	=	array(
		HpPageRepository::CATEGORY_TOP			=> array( HpPageRepository::TYPE_TOP			),
		HpPageRepository::CATEGORY_COMPANY 		=> array(
			HpPageRepository::TYPE_COMPANY					,
			HpPageRepository::TYPE_SHOP_INDEX				,
			HpPageRepository::TYPE_SHOP_DETAIL				,
		),
		HpPageRepository::CATEGORY_STRUCTURE	=> array(),
		HpPageRepository::CATEGORY_FOR			=> array()	,
		HpPageRepository::CATEGORY_BLOG			=> array(),
		HpPageRepository::CATEGORY_COLUMN        => array(),
		HpPageRepository::CATEGORY_FREE			=> array( HpPageRepository::TYPE_FREE			),
		HpPageRepository::CATEGORY_MEMBER_ONLY	=> array()	,
		HpPageRepository::CATEGORY_OTHER		=> array(
            HpPageRepository::TYPE_TERMINOLOGY              ,
            HpPageRepository::TYPE_PREVIEW                  ,
            HpPageRepository::TYPE_MOVING                   ,
        ),
		HpPageRepository::CATEGORY_INFO			=> array(
			HpPageRepository::TYPE_INFO_INDEX				,
			HpPageRepository::TYPE_INFO_DETAIL				,
		),
		HpPageRepository::CATEGORY_POLICY		=> array(
			HpPageRepository::TYPE_PRIVACYPOLICY			,
			HpPageRepository::TYPE_SITEPOLICY				,
		),
		HpPageRepository::CATEGORY_SITEMAP		=> array(),
		HpPageRepository::CATEGORY_FORM			=> array(
			HpPageRepository::TYPE_FORM_CONTACT				,
		),
		HpPageRepository::CATEGORY_LINK			=> array(
			HpPageRepository::TYPE_LINK						,
            HpPageRepository::TYPE_ALIAS                    ,
		),
	) ;
	
    public	$pageMapArticle = array(
        HpPageRepository::TYPE_USEFUL_REAL_ESTATE_INFORMATION => array(
            HpPageRepository::TYPE_BUSINESS_LEASE,
        ),
        HpPageRepository::TYPE_BUSINESS_LEASE => array(
            HpPageRepository::TYPE_LEARN_VISITS_COMPANIES_AND_SITE,
            HpPageRepository::TYPE_KNOW_MOVING
        ),
        HpPageRepository::TYPE_LEARN_VISITS_COMPANIES_AND_SITE => array(
            HpPageRepository::TYPE_PREVIEW
        ),
        HpPageRepository::TYPE_KNOW_MOVING => array(
            HpPageRepository::TYPE_MOVING
        )
    );

	public	$pageIndexNumbers	= array(
		HpPageRepository::TYPE_TOP									=> [ 'importance' => 10, 'limit' =>  1 ],
		HpPageRepository::TYPE_COMPANY								=> [ 'importance' => 10, 'limit' =>  1 ],
		HpPageRepository::TYPE_SHOP_DETAIL							=> [ 'importance' =>  5, 'limit' =>  1 ],
		HpPageRepository::TYPE_FREE									=> [ 'importance' =>  3, 'limit' => 10 ],
		HpPageRepository::TYPE_INFO_DETAIL							=> [ 'importance' =>  3, 'limit' =>  3 ],
        HpPageRepository::TYPE_PREVIEW								=> [ 'importance' =>  5, 'limit' =>  1 ],
		HpPageRepository::TYPE_MOVING								=> [ 'importance' =>  5, 'limit' =>  1 ],
		HpPageRepository::TYPE_TERMINOLOGY							=> [ 'importance' =>  7, 'limit' =>  1 ],
		HpPageRepository::TYPE_PRIVACYPOLICY						=> [ 'importance' => 10, 'limit' =>  1 ],
		HpPageRepository::TYPE_SITEPOLICY							=> [ 'importance' => 10, 'limit' =>  1 ],
		HpPageRepository::TYPE_SITEMAP								=> [ 'importance' => 10, 'limit' =>  1 ],
		HpPageRepository::TYPE_FORM_CONTACT							=> [ 'importance' => 10, 'limit' =>  1 ],
	) ;
}
