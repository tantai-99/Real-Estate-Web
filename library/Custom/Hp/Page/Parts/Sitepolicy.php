<?php
namespace Library\Custom\Hp\Page\Parts;
use Library\Custom\Hp\Page\Parts\AbstractParts\Policy;

class Sitepolicy extends Policy {

	protected $_title = 'サイトポリシー';

	protected $_sample_filename = 'site-policy.txt';
	
	protected $_required_force = array(
			'value',
	);

}