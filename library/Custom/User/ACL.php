<?php
namespace Library\Custom\User;

use Library\Custom\User\ACL\ACLAbstract;

class ACL extends ACLAbstract {
	
	/**
	 * 管理者
	 */
	const PRIV_ADMIN		= 'admin';
	
	/**
	 * 管理者 修正権限
	 */
	const PRIV_ADMIN_MANAGE	= 'admin_manage';
	
	/**
	 * 管理者 管理権限
	 */
	const PRIV_ADMIN_EDIT	= 'admin_edit';
	
	/**
	 * 管理者 代行作成権限
	 */
	const PRIV_ADMIN_CREATE	= 'admin_create';
	
	/**
	 * 管理者 代行更新権限
	 */
	const PRIV_ADMIN_OPEN	= 'admin_open';
	
	/**
	 * 代行ログイン権限
	 */
	const PRIV_AGENT	= 'admin_agent';
	
	/**
	 * 加盟店権限
	 */
	const PRIV_COMPANY		= 'company';
	
	/**
	 * 加盟店診断閲覧権限
	 */
	const PRIV_COMPANY_ANALYZE	 = 'company_analyze';
	
	/**
	 * 加盟店グループ権限
	 */
	const PRIV_COMPANY_GROUP	= 'company_group';
	
	const PRIV_AGENCY	= 'admin_agency';

	protected function __construct() {
		$this->_list = array(
			// -------------------------------
			// defaultモジュール
			'default' => array(
				self::PRIV_COMPANY,
				self::PRIV_ADMIN_CREATE,
				self::PRIV_AGENT,
			),
			'default.error' => array(),
			'default.auth' => array(),
			
			'default.creator' => array(
				self::PRIV_ADMIN_CREATE,
				self::PRIV_ADMIN_OPEN,
			),
			'default.creator.login' => array(),
			'default.creator.select-company' => array(),
			'default.creator.delete-hp' => array(self::PRIV_ADMIN_CREATE),
			'default.creator.api-delete-hp' => array(self::PRIV_ADMIN_CREATE),
			'default.creator.copy-to-company' => array(self::PRIV_ADMIN_OPEN),
			'default.creator.api-copy-to-company' => array(self::PRIV_ADMIN_OPEN),
			'default.creator.rollback' => array(self::PRIV_ADMIN_OPEN),
			'default.creator.api-rollback' => array(self::PRIV_ADMIN_OPEN),
            'default.creator.publish' => array(self::PRIV_ADMIN_CREATE),
			
			'default.publish' => array(
				self::PRIV_COMPANY,
                self::PRIV_ADMIN_CREATE,
                self::PRIV_AGENT,
			),
			//'default.publish.preview-page' => array(
			//	self::PRIV_COMPANY,
			//	self::PRIV_ADMIN_CREATE,
			//	self::PRIV_AGENT,
			//),
			
			'default.index' => array(
				self::PRIV_COMPANY,
				self::PRIV_COMPANY_ANALYZE,
				self::PRIV_ADMIN_CREATE,
				self::PRIV_ADMIN_OPEN,
				self::PRIV_AGENT,
			),
			
			'default.diacrisis' => array(
				self::PRIV_COMPANY,
				self::PRIV_COMPANY_ANALYZE,
				self::PRIV_ADMIN_CREATE,
				self::PRIV_ADMIN_OPEN,
				self::PRIV_AGENT,
			),
			
			'default.password' => array(
				self::PRIV_COMPANY,
				self::PRIV_COMPANY_ANALYZE,
			),
			
			'default.image.company-qr' => array(
				self::PRIV_COMPANY,
				self::PRIV_ADMIN_CREATE,
				self::PRIV_ADMIN_OPEN,
				self::PRIV_AGENT,
			),
			
			// -------------------------------
			// adminモジュール
			'admin' => array(
				self::PRIV_ADMIN,
				self::PRIV_AGENCY,
			),
			'admin.error' => array(),
			'admin.auth' => array(),
			'admin.log' => array(
				self::PRIV_ADMIN,
				self::PRIV_AGENCY,
			),
			'admin.information' => array(
				self::PRIV_ADMIN,
				self::PRIV_AGENCY,
			),
            // agency
            'agency' => array(
                self::PRIV_COMPANY,
                self::PRIV_ADMIN_CREATE,
                self::PRIV_AGENCY,
                self::PRIV_ADMIN_OPEN,
            ),
            'agency.error' => array(),
            'agency.auth' => array(),
            'agency.member.select-company' => array(),
		);
	}
}