<?php
namespace Library\Custom\Hp\Page\Parts;
use Library\Custom\Hp\Page\Parts\AbstractParts\Policy;

class Privacypolicy extends Policy {

	protected $_title = 'プライバシーポリシー';

	protected $_sample_filename = 'privacy-policy.txt';

	protected $_required_force = array(
			'value',
	);

}