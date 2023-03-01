<?php
namespace Library\Custom\View\Helper;

class TopicPath extends  HelperAbstract {
	
	protected $_topics;
	
	public function __construct() {
		$this->_topics = array();
	}
	
	public function topicPath($title = null, $action = null, $controller = null, $params = array(), $escape = true) {
		if ($title !== null) {
			$this->addLink($title, $action, $controller, $params, $escape);
		}
		
		return $this;
	}
	
	protected function _add($title, $link = null, $escape = true) {
		$this->_topics[] = array(
				'title' => $title,
				'link' => $link,
				'escape' => $escape,
		);
		return $this;
	}
	
	public function addText($title, $escape = true) {
		return $this->_add($title, null, $escape);
	}
	
	public function addLink($title, $action = null, $controller = null, $params = array(), $escape = true) {
		
		return $this->_add(
				$title,
				array(
					'action' => $action,
					'controller' => $controller,
					'module' => getRequestInfo()['module'],
					'params' => is_array($params) ? $params : array(),
				),
				$escape);
	}
	
	public function getTopics() {
		$ret = array();
		$last = count($this->_topics) - 1;
		foreach ($this->_topics as $i => $_topic) {
			$topic = array();
			
			$topic['title'] = $_topic['escape'] ? h($_topic['title']) : $_topic['title'];
			
			$topic['link'] = $_topic['link'];
			$topic['is_current'] = false;
			if (is_array($topic['link'])) {
				
				$link = $topic['link'];
				$params = $link['params'];
				
				$topic['is_current'] = isCurrent($link['action'], $link['controller'], null, $params);
			}
			
			$topic['is_last'] = $i == $last;
			$topic['is_link'] = $topic['link'] && !$topic['is_last'] && !$topic['is_current'];
			$topic['link'] = null;
			if ($topic['is_link']) {
				$topic['link'] = urlSimple($link['action'], $link['controller'], $link['module'] );
				if ($params) {
					$topic['link'] .= '?' . http_build_query($params);
				}
			}
			
			$ret[] = $topic;
		}
		
		return $ret;
	}
	
	public function clear() {
		$this->_topics = array();
		return $this;
	}
}