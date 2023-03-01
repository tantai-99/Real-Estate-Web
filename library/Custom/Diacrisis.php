<?php
namespace Library\Custom;

use Library\Custom\Analysis;

class Diacrisis
{

	private $analytics;
	
    public function init($companyId)  {
		
		$this->companyId = $companyId;
		

	}

    public function getSummary($baseMonth) {

		$analysis = new Analysis\Summary();
		$analysis->init($this->companyId);
		return $analysis->getData($baseMonth);
	}

    public function getAccess($baseMonth) {

		$analysis = new Analysis\Access();
		$analysis->init($this->companyId);
		return $analysis->getData($baseMonth);
	}


    public function apiGetAccessByDevice($baseMonth) {

		$analysis = new Analysis\AccessByDevice();
		$analysis->init($this->companyId);
		return $analysis->getData($baseMonth);

	}

    public function apiGetAnalysisAccessByMedia($baseMonth) {

		$analysis = new Analysis\AccessByMedia();
		$analysis->init($this->companyId);
		return $analysis->getData($baseMonth);

	}

    public function apiGetAnalysisAccessKeywordTop($baseMonth, $limit) {

		$analysis = new Analysis\AccessKeywordTop();
		$analysis->init($this->companyId);
		return $analysis->getData($baseMonth, $limit);
	}

    public function apiGetAnalysisAccessPageTop($baseMonth, $limit) {

		$analysis = new Analysis\AccessPageTop();
		$analysis->init($this->companyId);
		return $analysis->getData($baseMonth, $limit);
	}

    public function apiGetAnalysisAccessPageView($baseMonth, $limit) {

		$analysis = new Analysis\AccessPageView();
		$analysis->init($this->companyId);
		return $analysis->getData($baseMonth, $limit);
	}


}