<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Repositories\HpSiteImage\HpSiteImageRepositoryInterface;
use App\Repositories\HpImageContent\HpImageContentRepositoryInterface;
use App\Repositories\HpImage\HpImageRepositoryInterface;
use App\Repositories\HpPage\HpPageRepositoryInterface;

class ImageController extends Controller
{

	public function favicon(Request $request)
	{
		$id = $request->id;
		if (!$id) {
			$this->_forward404();
		}

        $hp = getInstanceUser('cms')->getCurrentHp();
		if (!$hp) {
			$this->_forward404();
		}

        $table = App::make(HpSiteImageRepositoryInterface::class);
		$row = $table->fetchFaviconRow($id, $hp->id);
		if (!$row) {
			$this->_forward404();
		}

		header('Accept-Ranges: bytes');
		$this->_output($row->content, 'image/x-icon');
	}

	public function siteLogoPc(Request $request)
    {
        $this->_siteLogo(config('constants.hp_site_image.TYPE_SITELOGO_PC'), $request);
    }

	public function siteLogoSp(Request $request)
    {
        $this->_siteLogo(config('constants.hp_site_image.TYPE_SITELOGO_SP'), $request);
    }

	public function webclip(Request $request)
    {
        $this->_siteLogo(config('constants.hp_site_image.TYPE_WEBCLIP'), $request);
    }

    protected function _siteLogo($type, Request $request)
	{
        $id = $request->id;
		if (!$id) {
			$this->_forward404();
		}

        $hp = getInstanceUser('cms')->getCurrentHp();
		if (!$hp) {
			$this->_forward404();
		}

        $table = App::make(HpSiteImageRepositoryInterface::class);
		$row = $table->fetchRowByType($id, $hp->id, $type);
		if (!$row) {
			$this->_forward404();
		}

		$this->_output($row->content, $this->_contentType($row->extension));
	}
	
	public function hpImage(Request $request) {
		$hp = getInstanceUser('cms')->getCurrentHp();
		if (!$hp) {
			$this->_forward404();
		}
		
		$id = $request->id;
		if (!$id) {
			$imageId = $request->image_id;
			if (!$imageId) {
				$this->_forward404();
			}
			
			$table = App::make(HpImageRepositoryInterface::class);
			$row = $table->fetchRow([['id', (int)$imageId], ['hp_id', $hp->id]]);
			if (!$row) {
				$this->_forward404();
			}

			$id = $row->hp_image_content_id;
		}
		
		$table = App::make(HpImageContentRepositoryInterface::class);
		$row = $table->fetchRow([['id', $id], ['hp_id', $hp->id]]);
		if (!$row) {
			$this->_forward404();
		}

		$this->_output($row->content, $this->_contentType($row->extension), true);
	}

	public function hpImageResize()
	{
		$hp = getUser()->getCurrentHp();
		if (!$hp) {
			$this->_forward404();
		}
		$id = $this->_request->id;
		if (!$id) {
			$imageId = $this->_request->image_id;
			if (!$imageId) {
				$this->_forward404();
			}

			$table = \App::make(HpImageRepositoryInterface::class);
			$row = $table->fetchRow(array(['id', (int)$imageId], ['hp_id', $hp->id]));
			if (!$row) {
				$this->_forward404();
			}

			$id = $row->hp_image_content_id;
		}

		$table = \App::make(HpImageContentRepositoryInterface::class);
		$row = $table->fetchRow(array(['id', $id], ['hp_id', $hp->id]));
		if (!$row) {
			$this->_forward404();
		}
		// $image_type=
		$dWidth  = $this->_request->width;
		$dHeight = $this->_request->height;
		$sHeight = $this->_request->sheight;
		$sWidth  = $this->_request->swidth;
		$image_type = IMAGETYPE_PNG;
		$image = imagecreatefromstring($row->content);
		$new_image = imagecreatetruecolor($dWidth, $dHeight);
		$rHeight = $dHeight;
		$rWidth  = $dWidth;
		$dRate =  $dWidth / $dHeight;
		$sRate =  $sWidth / $sHeight;
		$dx = 0;
		$dy = 0;
		$rate = $dWidth / $sWidth;
		$dHeight = $sHeight * $rate;

		imagealphablending($new_image, false);
		imagesavealpha($new_image, true);
		$background = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
		$background = imagecolortransparent($new_image, $background);
		imagefilledrectangle($new_image, 0, 0, $rWidth, $rHeight, $background);
		imagecopyresampled($new_image, $image, $dx, $dy, 0, 0,  $dWidth, $dHeight,  $sWidth, $sHeight);
		header('Content-type: image/png');
		imagepng($new_image);

		exit;
	}

	public function companyQr()
	{

		$url = getUser()->getProfile()->getSiteUrl();

		$table = \App::make(HpPageRepositoryInterface::class);
		$pageId = $this->_request->page_id;
		if ((!is_numeric($pageId)) || !($row = $table->fetchRowById((int)$pageId))) {
			$this->_output(\Library\Custom\Qr::pngBinary($url), 'png');
		}

		$filenames = array($row->filename);
		while ($row->parent_page_id !== 0 && $row->parent_page_id !== NULL) {
			$row = $table->fetchRowById($row->parent_page_id);
			if (!$row) {
				break;
			}
			$filenames[] = $row->filename;
		}

		foreach (array_reverse($filenames) as $dir) {
			$url .= '/' . $dir;
		}

		return $this->_output(\Library\Custom\Qr::pngBinary($url), 'png');
	}

	protected function _contentType($ext)
	{
		switch ($ext) {
			case 'jpg':
				$type = 'image/jpeg';
				break;
			default:
				$type = 'image/' . $ext;
				break;
		}
		return $type;
	}

	protected function _output($content, $type, $cache = false)
	{
		if ($cache) {
			// 10���ԃL���b�V������
			$expires = (60 * 60 * 24) * 10;
			header("Cache-Control: max-age=" . $expires);
			header('Expires: ' . gmdate('D, d M Y H:i:s T', time() + $expires));
			header('Pragma: cache');
		}
		header('Content-Type: ' . $type);
		header('Content-Length: ' . strlen($content));
		echo $content;
		exit();
	}
}
