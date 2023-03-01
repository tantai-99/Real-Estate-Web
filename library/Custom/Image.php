<?php

namespace Library\Custom;

class Image
{

	protected $_file;
	protected $_info;
	protected $_image;

	public function __construct($filename)
	{
		$this->_file = $filename;
		$this->_info = getimagesize($filename);

		$image = null;
		switch ($this->getType()) {
			case IMAGETYPE_JPEG:
				$image = @ImageCreateFromJPEG($filename);
				break;
			case IMAGETYPE_GIF:
				$image = @ImageCreateFromGIF($filename);
				break;
			case IMAGETYPE_PNG:
				$image = @ImageCreateFromPNG($filename);
				break;
		}

		if (!$image) {
			throw new \Exception('画像読み込みに失敗しました。');
		}

		$this->_image = $image;
	}

	public function getWidth()
	{
		return $this->_info[0];
	}

	public function getHeight()
	{
		return $this->_info[1];
	}

	public function getType()
	{
		return $this->_info[2];
	}

	public function imageIco($dest = null, $sizes = array(32, 32))
	{
		if (!is_array($sizes[0])) {
			$sizes = array($sizes);
		}

		$images = array();
		foreach ($sizes as $size) {
			list($width, $height) = $size;
			$images[] = $this->_imageIco($width, $height);
		}

		$data = pack('vvv', 0, 1, count($images));
		$pixel_data = '';

		$icon_dir_entry_size = 16;

		$offset = 6 + ($icon_dir_entry_size * count($images));

		foreach ($images as $image) {
			$data .= pack('CCCCvvVV', $image['width'], $image['height'], $image['color_palette_colors'], 0, 1, $image['bits_per_pixel'], $image['size'], $offset);
			$pixel_data .= $image['data'];

			$offset += $image['size'];
		}

		$data .= $pixel_data;
		unset($pixel_data);

		if ($dest) {
			return file_put_contents($dest, $data);
		}
		return $data;
	}

	protected function _imageIco($width, $height)
	{
		$image = $this->createTrueColor($width, $height, IMAGETYPE_PNG);
		if ($this->getWidth() > $this->getHeight()) {
			$destWidth = $width;
			$destHeight = $width / $this->getWidth() * $this->getHeight();
		} else {
			$destWidth = $height / $this->getHeight() * $this->getWidth();
			$destHeight = $height;
		}
		$ret = imagecopyresampled(
			$image,
			$this->_image,
			round(($width - $destWidth) / 2),
			round(($height - $destHeight) / 2),
			0,
			0,
			round($destWidth),
			round($destHeight),
			$this->getWidth(),
			$this->getHeight()
		);

		if ($ret === false) {
			throw new \Exception('画像作成に失敗しました。');
		}

		$pixel_data = array();
		$opacity_data = array();
		$current_opacity_val = 0;

		for ($y = $height - 1; $y >= 0; $y--) {
			for ($x = 0; $x < $width; $x++) {
				$color = imagecolorat($image, $x, $y);

				$alpha = ($color & 0x7F000000) >> 24;
				$alpha = (1 - ($alpha / 127)) * 255;

				$color &= 0xFFFFFF;
				$color |= 0xFF000000 & ($alpha << 24);

				$pixel_data[] = $color;


				$opacity = ($alpha <= 127) ? 1 : 0;

				$current_opacity_val = ($current_opacity_val << 1) | $opacity;

				if ((($x + 1) % 32) == 0) {
					$opacity_data[] = $current_opacity_val;
					$current_opacity_val = 0;
				}
			}

			if (($x % 32) > 0) {
				while (($x++ % 32) > 0)
					$current_opacity_val = $current_opacity_val << 1;

				$opacity_data[] = $current_opacity_val;
				$current_opacity_val = 0;
			}
		}

		$image_header_size = 40;
		$color_mask_size = $width * $height * 4;
		$opacity_mask_size = (ceil($width / 32) * 4) * $height;


		$data = pack('VVVvvVVVVVV', 40, $width, ($height * 2), 1, 32, 0, 0, 0, 0, 0, 0);

		foreach ($pixel_data as $color)
			$data .= pack('V', $color);

		foreach ($opacity_data as $opacity)
			$data .= pack('N', $opacity);


		return array(
			'width'                => $width,
			'height'               => $height,
			'color_palette_colors' => 0,
			'bits_per_pixel'       => 32,
			'size'                 => $image_header_size + $color_mask_size + $opacity_mask_size,
			'data'                 => $data,
		);
	}

	/**
	 * 指定のサイズに収まるようにリサイズする
	 * 指定のサイズに収まっている且つ$force==falseの場合はリサイズしない
	 * @param int $width
	 * @param int$height
	 * @param boolean$force
	 * @throws Exception
	 */
	public function fit($width, $height, $force = false)
	{
		$diffWidth = $width - $this->getWidth();
		$diffHeight = $height - $this->getHeight();

		if ($diffWidth >= 0 && $diffHeight >= 0 && !$force) {
			if ($this->getType() === IMAGETYPE_PNG) {
				imageAlphaBlending($this->_image, true);
				imageSaveAlpha($this->_image, true);
			}
			return $this;
		}

		$ratioWidth = $width / $this->getWidth();
		$ratioHeight = $height / $this->getHeight();


		if ($diffHeight > $diffWidth) {
			$destWidth = $width;
			$destHeight = $width / $this->getWidth() * $this->getHeight();
		} else {
			$destWidth = $height / $this->getHeight() * $this->getWidth();
			$destHeight = $height;
		}

		$destWidth = round($destWidth);
		$destHeight = round($destHeight);

		$image = $this->createTrueColor($destWidth, $destHeight);
		$ret = imagecopyresampled(
			$image,
			$this->_image,
			0,
			0,
			0,
			0,
			$destWidth,
			$destHeight,
			$this->getWidth(),
			$this->getHeight()
		);

		if ($ret === false) {
			throw new \Exception('画像作成に失敗しました。');
		}

		$this->_image = $image;

		return $this;
	}

	public function getContent()
	{
		ob_start();
		switch ($this->getType()) {
			case IMAGETYPE_JPEG:
				imagejpeg($this->_image);
				break;
			case IMAGETYPE_PNG:
				imagepng($this->_image);
				break;
			case IMAGETYPE_GIF:
				imagegif($this->_image);
				break;
		}

		return ob_get_clean();
	}

	public function createTrueColor($width, $height, $image_type = null)
	{
		$image = @ImageCreateTrueColor($width, $height);
		if (false === $image) {
			throw new \Exception('画像作成に失敗しました。');
		}

		if (!$image_type) {
			$image_type = $this->getType();
		}

		switch ($image_type) {
			case IMAGETYPE_PNG:
			case IMAGETYPE_GIF:
				$index = imagecolortransparent($this->_image);
				$palletsize = imagecolorstotal($this->_image);
				if ($index >= 0 && $index < $palletsize) {
					$color = imagecolorsforindex($this->_image, $index);
					$alpha = imagecolorallocate($image, $color['red'], $color['green'], $color['blue']);
					imagefill($image, 0, 0, $alpha);
					imagecolortransparent($image, $alpha);
				} else {
					imagealphablending($image, false);
					$color = imagecolorallocatealpha($image, 0, 0, 0, 127);
					imagecolortransparent($image, $color);
					imagesavealpha($image, true);
					imagefill($image, 0, 0, $color);
				}
				break;
			case IMAGETYPE_JPEG:
				$color = imagecolorallocate($image, 255, 255, 255);
				imagefill($image, 0, 0, $color);
				break;
			default:
				throw new \Exception('指定の画像タイプは未対応です。');
				break;
		}

		return $image;
	}
}