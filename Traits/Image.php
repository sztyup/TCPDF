<?php


	/**
	 * Puts an image in the page.
	 * The upper-left corner must be given.
	 * The dimensions can be specified in different ways:<ul>
	 * <li>explicit width and height (expressed in user unit)</li>
	 * <li>one explicit dimension, the other being calculated automatically in order to keep the original proportions</li>
	 * <li>no explicit dimension, in which case the image is put at 72 dpi</li></ul>
	 * Supported formats are JPEG and PNG images whitout GD library and all images supported by GD: GD, GD2, GD2PART, GIF, JPEG, PNG, BMP, XBM, XPM;
	 * The format can be specified explicitly or inferred from the file extension.<br />
	 * It is possible to put a link on the image.<br />
	 * Remark: if an image is used several times, only one copy will be embedded in the file.<br />
	 * @param $file (string) Name of the file containing the image or a '@' character followed by the image data string. To link an image without embedding it on the document, set an asterisk character before the URL (i.e.: '*http://www.example.com/image.jpg').
	 * @param $x (float) Abscissa of the upper-left corner (LTR) or upper-right corner (RTL).
	 * @param $y (float) Ordinate of the upper-left corner (LTR) or upper-right corner (RTL).
	 * @param $w (float) Width of the image in the page. If not specified or equal to zero, it is automatically calculated.
	 * @param $h (float) Height of the image in the page. If not specified or equal to zero, it is automatically calculated.
	 * @param $type (string) Image format. Possible values are (case insensitive): JPEG and PNG (whitout GD library) and all images supported by GD: GD, GD2, GD2PART, GIF, JPEG, PNG, BMP, XBM, XPM;. If not specified, the type is inferred from the file extension.
	 * @param $link (mixed) URL or identifier returned by AddLink().
	 * @param $align (string) Indicates the alignment of the pointer next to image insertion relative to image height. The value can be:<ul><li>T: top-right for LTR or top-left for RTL</li><li>M: middle-right for LTR or middle-left for RTL</li><li>B: bottom-right for LTR or bottom-left for RTL</li><li>N: next line</li></ul>
	 * @param $resize (mixed) If true resize (reduce) the image to fit $w and $h (requires GD or ImageMagick library); if false do not resize; if 2 force resize in all cases (upscaling and downscaling).
	 * @param $dpi (int) dot-per-inch resolution used on resize
	 * @param $palign (string) Allows to center or align the image on the current line. Possible values are:<ul><li>L : left align</li><li>C : center</li><li>R : right align</li><li>'' : empty string : left for LTR or right for RTL</li></ul>
	 * @param $ismask (boolean) true if this image is a mask, false otherwise
	 * @param $imgmask (mixed) image object returned by this function or false
	 * @param $border (mixed) Indicates if borders must be drawn around the cell. The value can be a number:<ul><li>0: no border (default)</li><li>1: frame</li></ul> or a string containing some or all of the following characters (in any order):<ul><li>L: left</li><li>T: top</li><li>R: right</li><li>B: bottom</li></ul> or an array of line styles for each border group - for example: array('LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)))
	 * @param $fitbox (mixed) If not false scale image dimensions proportionally to fit within the ($w, $h) box. $fitbox can be true or a 2 characters string indicating the image alignment inside the box. The first character indicate the horizontal alignment (L = left, C = center, R = right) the second character indicate the vertical algnment (T = top, M = middle, B = bottom).
	 * @param $hidden (boolean) If true do not display the image.
	 * @param $fitonpage (boolean) If true the image is resized to not exceed page dimensions.
	 * @param $alt (boolean) If true the image will be added as alternative and not directly printed (the ID of the image will be returned).
	 * @param $altimgs (array) Array of alternate images IDs. Each alternative image must be an array with two values: an integer representing the image ID (the value returned by the Image method) and a boolean value to indicate if the image is the default for printing.
	 * @return image information
	 * @public
	 * @since 1.1
	 */
	public function Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false, $alt=false, $altimgs=array()) {
		if ($this->state != 2) {
			return;
		}
		if (strcmp($x, '') === 0) {
			$x = $this->x;
		}
		if (strcmp($y, '') === 0) {
			$y = $this->y;
		}
		// check page for no-write regions and adapt page margins if necessary
		list($x, $y) = $this->checkPageRegions($h, $x, $y);
		$exurl = ''; // external streams
		$imsize = FALSE;
		// check if we are passing an image as file or string
		if ($file[0] === '@') {
			// image from string
			$imgdata = substr($file, 1);
		} else { // image file
			if ($file[0] === '*') {
				// image as external stream
				$file = substr($file, 1);
				$exurl = $file;
			}
			// check if is a local file
			if (!@file_exists($file)) {
				// try to encode spaces on filename
				$tfile = str_replace(' ', '%20', $file);
				if (@file_exists($tfile)) {
					$file = $tfile;
				}
			}
			if (($imsize = @getimagesize($file)) === FALSE) {
				if (in_array($file, $this->imagekeys)) {
					// get existing image data
					$info = $this->getImageBuffer($file);
					$imsize = array($info['w'], $info['h']);
				} elseif (strpos($file, '__tcpdf_'.$this->file_id.'_img') === FALSE) {
					$imgdata = TCPDF_STATIC::fileGetContents($file);
				}
			}
		}
		if (!empty($imgdata)) {
			// copy image to cache
			$original_file = $file;
			$file = TCPDF_STATIC::getObjFilename('img', $this->file_id);
			$fp = TCPDF_STATIC::fopenLocal($file, 'w');
			if (!$fp) {
				$this->Error('Unable to write file: '.$file);
			}
			fwrite($fp, $imgdata);
			fclose($fp);
			unset($imgdata);
			$imsize = @getimagesize($file);
			if ($imsize === FALSE) {
				unlink($file);
				$file = $original_file;
			}
		}
		if ($imsize === FALSE) {
			if (($w > 0) AND ($h > 0)) {
				// get measures from specified data
				$pw = $this->getHTMLUnitToUnits($w, 0, $this->pdfunit, true) * $this->imgscale * $this->k;
				$ph = $this->getHTMLUnitToUnits($h, 0, $this->pdfunit, true) * $this->imgscale * $this->k;
				$imsize = array($pw, $ph);
			} else {
				$this->Error('[Image] Unable to get the size of the image: '.$file);
			}
		}
		// file hash
		$filehash = md5($file);
		// get original image width and height in pixels
		list($pixw, $pixh) = $imsize;
		// calculate image width and height on document
		if (($w <= 0) AND ($h <= 0)) {
			// convert image size to document unit
			$w = $this->pixelsToUnits($pixw);
			$h = $this->pixelsToUnits($pixh);
		} elseif ($w <= 0) {
			$w = $h * $pixw / $pixh;
		} elseif ($h <= 0) {
			$h = $w * $pixh / $pixw;
		} elseif (($fitbox !== false) AND ($w > 0) AND ($h > 0)) {
			if (strlen($fitbox) !== 2) {
				// set default alignment
				$fitbox = '--';
			}
			// scale image dimensions proportionally to fit within the ($w, $h) box
			if ((($w * $pixh) / ($h * $pixw)) < 1) {
				// store current height
				$oldh = $h;
				// calculate new height
				$h = $w * $pixh / $pixw;
				// height difference
				$hdiff = ($oldh - $h);
				// vertical alignment
				switch (strtoupper($fitbox[1])) {
					case 'T': {
						break;
					}
					case 'M': {
						$y += ($hdiff / 2);
						break;
					}
					case 'B': {
						$y += $hdiff;
						break;
					}
				}
			} else {
				// store current width
				$oldw = $w;
				// calculate new width
				$w = $h * $pixw / $pixh;
				// width difference
				$wdiff = ($oldw - $w);
				// horizontal alignment
				switch (strtoupper($fitbox[0])) {
					case 'L': {
						if ($this->rtl) {
							$x -= $wdiff;
						}
						break;
					}
					case 'C': {
						if ($this->rtl) {
							$x -= ($wdiff / 2);
						} else {
							$x += ($wdiff / 2);
						}
						break;
					}
					case 'R': {
						if (!$this->rtl) {
							$x += $wdiff;
						}
						break;
					}
				}
			}
		}
		// fit the image on available space
		list($w, $h, $x, $y) = $this->fitBlock($w, $h, $x, $y, $fitonpage);
		// calculate new minimum dimensions in pixels
		$neww = round($w * $this->k * $dpi / $this->dpi);
		$newh = round($h * $this->k * $dpi / $this->dpi);
		// check if resize is necessary (resize is used only to reduce the image)
		$newsize = ($neww * $newh);
		$pixsize = ($pixw * $pixh);
		if (intval($resize) == 2) {
			$resize = true;
		} elseif ($newsize >= $pixsize) {
			$resize = false;
		}
		// check if image has been already added on document
		$newimage = true;
		if (in_array($file, $this->imagekeys)) {
			$newimage = false;
			// get existing image data
			$info = $this->getImageBuffer($file);
			if (strpos($file, '__tcpdf_'.$this->file_id.'_imgmask_') === FALSE) {
				// check if the newer image is larger
				$oldsize = ($info['w'] * $info['h']);
				if ((($oldsize < $newsize) AND ($resize)) OR (($oldsize < $pixsize) AND (!$resize))) {
					$newimage = true;
				}
			}
		} elseif (($ismask === false) AND ($imgmask === false) AND (strpos($file, '__tcpdf_'.$this->file_id.'_imgmask_') === FALSE)) {
			// create temp image file (without alpha channel)
			$tempfile_plain = K_PATH_CACHE.'__tcpdf_'.$this->file_id.'_imgmask_plain_'.$filehash;
			// create temp alpha file
			$tempfile_alpha = K_PATH_CACHE.'__tcpdf_'.$this->file_id.'_imgmask_alpha_'.$filehash;
			// check for cached images
			if (in_array($tempfile_plain, $this->imagekeys)) {
				// get existing image data
				$info = $this->getImageBuffer($tempfile_plain);
				// check if the newer image is larger
				$oldsize = ($info['w'] * $info['h']);
				if ((($oldsize < $newsize) AND ($resize)) OR (($oldsize < $pixsize) AND (!$resize))) {
					$newimage = true;
				} else {
					$newimage = false;
					// embed mask image
					$imgmask = $this->Image($tempfile_alpha, $x, $y, $w, $h, 'PNG', '', '', $resize, $dpi, '', true, false);
					// embed image, masked with previously embedded mask
					return $this->Image($tempfile_plain, $x, $y, $w, $h, $type, $link, $align, $resize, $dpi, $palign, false, $imgmask);
				}
			}
		}
		if ($newimage) {
			//First use of image, get info
			$type = strtolower($type);
			if ($type == '') {
				$type = TCPDF_IMAGES::getImageFileType($file, $imsize);
			} elseif ($type == 'jpg') {
				$type = 'jpeg';
			}
			$mqr = TCPDF_STATIC::get_mqr();
			TCPDF_STATIC::set_mqr(false);
			// Specific image handlers (defined on TCPDF_IMAGES CLASS)
			$mtd = '_parse'.$type;
			// GD image handler function
			$gdfunction = 'imagecreatefrom'.$type;
			$info = false;
			if ((method_exists('TCPDF_IMAGES', $mtd)) AND (!($resize AND (function_exists($gdfunction) OR extension_loaded('imagick'))))) {
				// TCPDF image functions
				$info = TCPDF_IMAGES::$mtd($file);
				if (($ismask === false) AND ($imgmask === false) AND (strpos($file, '__tcpdf_'.$this->file_id.'_imgmask_') === FALSE)
					AND (($info === 'pngalpha') OR (isset($info['trns']) AND !empty($info['trns'])))) {
					return $this->ImagePngAlpha($file, $x, $y, $pixw, $pixh, $w, $h, 'PNG', $link, $align, $resize, $dpi, $palign, $filehash);
				}
			}
			if (($info === false) AND function_exists($gdfunction)) {
				try {
					// GD library
					$img = $gdfunction($file);
					if ($img !== false) {
						if ($resize) {
							$imgr = imagecreatetruecolor($neww, $newh);
							if (($type == 'gif') OR ($type == 'png')) {
								$imgr = TCPDF_IMAGES::setGDImageTransparency($imgr, $img);
							}
							imagecopyresampled($imgr, $img, 0, 0, 0, 0, $neww, $newh, $pixw, $pixh);
							$img = $imgr;
						}
						if (($type == 'gif') OR ($type == 'png')) {
							$info = TCPDF_IMAGES::_toPNG($img, TCPDF_STATIC::getObjFilename('img', $this->file_id));
						} else {
							$info = TCPDF_IMAGES::_toJPEG($img, $this->jpeg_quality, TCPDF_STATIC::getObjFilename('img', $this->file_id));
						}
					}
				} catch(Exception $e) {
					$info = false;
				}
			}
			if (($info === false) AND extension_loaded('imagick')) {
				try {
					// ImageMagick library
					$img = new Imagick();
					if ($type == 'svg') {
						if ($file[0] === '@') {
							// image from string
							$svgimg = substr($file, 1);
						} else {
							// get SVG file content
							$svgimg = TCPDF_STATIC::fileGetContents($file);
						}
						if ($svgimg !== FALSE) {
							// get width and height
							$regs = array();
							if (preg_match('/<svg([^\>]*)>/si', $svgimg, $regs)) {
								$svgtag = $regs[1];
								$tmp = array();
								if (preg_match('/[\s]+width[\s]*=[\s]*"([^"]*)"/si', $svgtag, $tmp)) {
									$ow = $this->getHTMLUnitToUnits($tmp[1], 1, $this->svgunit, false);
									$owu = sprintf('%F', ($ow * $dpi / 72)).$this->pdfunit;
									$svgtag = preg_replace('/[\s]+width[\s]*=[\s]*"[^"]*"/si', ' width="'.$owu.'"', $svgtag, 1);
								} else {
									$ow = $w;
								}
								$tmp = array();
								if (preg_match('/[\s]+height[\s]*=[\s]*"([^"]*)"/si', $svgtag, $tmp)) {
									$oh = $this->getHTMLUnitToUnits($tmp[1], 1, $this->svgunit, false);
									$ohu = sprintf('%F', ($oh * $dpi / 72)).$this->pdfunit;
									$svgtag = preg_replace('/[\s]+height[\s]*=[\s]*"[^"]*"/si', ' height="'.$ohu.'"', $svgtag, 1);
								} else {
									$oh = $h;
								}
								$tmp = array();
								if (!preg_match('/[\s]+viewBox[\s]*=[\s]*"[\s]*([0-9\.]+)[\s]+([0-9\.]+)[\s]+([0-9\.]+)[\s]+([0-9\.]+)[\s]*"/si', $svgtag, $tmp)) {
									$vbw = ($ow * $this->imgscale * $this->k);
									$vbh = ($oh * $this->imgscale * $this->k);
									$vbox = sprintf(' viewBox="0 0 %F %F" ', $vbw, $vbh);
									$svgtag = $vbox.$svgtag;
								}
								$svgimg = preg_replace('/<svg([^\>]*)>/si', '<svg'.$svgtag.'>', $svgimg, 1);
							}
							$img->readImageBlob($svgimg);
						}
					} else {
						$img->readImage($file);
					}
					if ($resize) {
						$img->resizeImage($neww, $newh, 10, 1, false);
					}
					$img->setCompressionQuality($this->jpeg_quality);
					$img->setImageFormat('jpeg');
					$tempname = TCPDF_STATIC::getObjFilename('img', $this->file_id);
					$img->writeImage($tempname);
					$info = TCPDF_IMAGES::_parsejpeg($tempname);
					unlink($tempname);
					$img->destroy();
				} catch(Exception $e) {
					$info = false;
				}
			}
			if ($info === false) {
				// unable to process image
				return;
			}
			TCPDF_STATIC::set_mqr($mqr);
			if ($ismask) {
				// force grayscale
				$info['cs'] = 'DeviceGray';
			}
			if ($imgmask !== false) {
				$info['masked'] = $imgmask;
			}
			if (!empty($exurl)) {
				$info['exurl'] = $exurl;
			}
			// array of alternative images
			$info['altimgs'] = $altimgs;
			// add image to document
			$info['i'] = $this->setImageBuffer($file, $info);
		}
		// set alignment
		$this->img_rb_y = $y + $h;
		// set alignment
		if ($this->rtl) {
			if ($palign == 'L') {
				$ximg = $this->lMargin;
			} elseif ($palign == 'C') {
				$ximg = ($this->w + $this->lMargin - $this->rMargin - $w) / 2;
			} elseif ($palign == 'R') {
				$ximg = $this->w - $this->rMargin - $w;
			} else {
				$ximg = $x - $w;
			}
			$this->img_rb_x = $ximg;
		} else {
			if ($palign == 'L') {
				$ximg = $this->lMargin;
			} elseif ($palign == 'C') {
				$ximg = ($this->w + $this->lMargin - $this->rMargin - $w) / 2;
			} elseif ($palign == 'R') {
				$ximg = $this->w - $this->rMargin - $w;
			} else {
				$ximg = $x;
			}
			$this->img_rb_x = $ximg + $w;
		}
		if ($ismask OR $hidden) {
			// image is not displayed
			return $info['i'];
		}
		$xkimg = $ximg * $this->k;
		if (!$alt) {
			// only non-alternative immages will be set
			$this->_out(sprintf('q %F 0 0 %F %F %F cm /I%u Do Q', ($w * $this->k), ($h * $this->k), $xkimg, (($this->h - ($y + $h)) * $this->k), $info['i']));
		}
		if (!empty($border)) {
			$bx = $this->x;
			$by = $this->y;
			$this->x = $ximg;
			if ($this->rtl) {
				$this->x += $w;
			}
			$this->y = $y;
			$this->Cell($w, $h, '', $border, 0, '', 0, '', 0, true);
			$this->x = $bx;
			$this->y = $by;
		}
		if ($link) {
			$this->Link($ximg, $y, $w, $h, $link, 0);
		}
		// set pointer to align the next text/objects
		switch($align) {
			case 'T': {
				$this->y = $y;
				$this->x = $this->img_rb_x;
				break;
			}
			case 'M': {
				$this->y = $y + round($h/2);
				$this->x = $this->img_rb_x;
				break;
			}
			case 'B': {
				$this->y = $this->img_rb_y;
				$this->x = $this->img_rb_x;
				break;
			}
			case 'N': {
				$this->SetY($this->img_rb_y);
				break;
			}
			default:{
				break;
			}
		}
		$this->endlinex = $this->img_rb_x;
		if ($this->inxobj) {
			// we are inside an XObject template
			$this->xobjects[$this->xobjid]['images'][] = $info['i'];
		}
		return $info['i'];
	}

	/**
	 * Extract info from a PNG image with alpha channel using the Imagick or GD library.
	 * @param $file (string) Name of the file containing the image.
	 * @param $x (float) Abscissa of the upper-left corner.
	 * @param $y (float) Ordinate of the upper-left corner.
	 * @param $wpx (float) Original width of the image in pixels.
	 * @param $hpx (float) original height of the image in pixels.
	 * @param $w (float) Width of the image in the page. If not specified or equal to zero, it is automatically calculated.
	 * @param $h (float) Height of the image in the page. If not specified or equal to zero, it is automatically calculated.
	 * @param $type (string) Image format. Possible values are (case insensitive): JPEG and PNG (whitout GD library) and all images supported by GD: GD, GD2, GD2PART, GIF, JPEG, PNG, BMP, XBM, XPM;. If not specified, the type is inferred from the file extension.
	 * @param $link (mixed) URL or identifier returned by AddLink().
	 * @param $align (string) Indicates the alignment of the pointer next to image insertion relative to image height. The value can be:<ul><li>T: top-right for LTR or top-left for RTL</li><li>M: middle-right for LTR or middle-left for RTL</li><li>B: bottom-right for LTR or bottom-left for RTL</li><li>N: next line</li></ul>
	 * @param $resize (boolean) If true resize (reduce) the image to fit $w and $h (requires GD library).
	 * @param $dpi (int) dot-per-inch resolution used on resize
	 * @param $palign (string) Allows to center or align the image on the current line. Possible values are:<ul><li>L : left align</li><li>C : center</li><li>R : right align</li><li>'' : empty string : left for LTR or right for RTL</li></ul>
	 * @param $filehash (string) File hash used to build unique file names.
	 * @author Nicola Asuni
	 * @protected
	 * @since 4.3.007 (2008-12-04)
	 * @see Image()
	 */
	protected function ImagePngAlpha($file, $x, $y, $wpx, $hpx, $w, $h, $type, $link, $align, $resize, $dpi, $palign, $filehash='') {
		// create temp images
		if (empty($filehash)) {
			$filehash = md5($file);
		}
		// create temp image file (without alpha channel)
		$tempfile_plain = K_PATH_CACHE.'__tcpdf_'.$this->file_id.'_imgmask_plain_'.$filehash;
		// create temp alpha file
		$tempfile_alpha = K_PATH_CACHE.'__tcpdf_'.$this->file_id.'_imgmask_alpha_'.$filehash;
		$parsed = false;
		$parse_error = '';
		// ImageMagick extension
		if (($parsed === false) AND extension_loaded('imagick')) {
			try {
				// ImageMagick library
				$img = new Imagick();
				$img->readImage($file);
				// clone image object
				$imga = TCPDF_STATIC::objclone($img);
				// extract alpha channel
				if (method_exists($img, 'setImageAlphaChannel') AND defined('Imagick::ALPHACHANNEL_EXTRACT')) {
					$img->setImageAlphaChannel(Imagick::ALPHACHANNEL_EXTRACT);
				} else {
					$img->separateImageChannel(8); // 8 = (imagick::CHANNEL_ALPHA | imagick::CHANNEL_OPACITY | imagick::CHANNEL_MATTE);
					$img->negateImage(true);
				}
				$img->setImageFormat('png');
				$img->writeImage($tempfile_alpha);
				// remove alpha channel
				if (method_exists($imga, 'setImageMatte')) {
					$imga->setImageMatte(false);
				} else {
					$imga->separateImageChannel(39); // 39 = (imagick::CHANNEL_ALL & ~(imagick::CHANNEL_ALPHA | imagick::CHANNEL_OPACITY | imagick::CHANNEL_MATTE));
				}
				$imga->setImageFormat('png');
				$imga->writeImage($tempfile_plain);
				$parsed = true;
			} catch (Exception $e) {
				// Imagemagick fails, try with GD
				$parse_error = 'Imagick library error: '.$e->getMessage();
			}
		}
		// GD extension
		if (($parsed === false) AND function_exists('imagecreatefrompng')) {
			try {
				// generate images
				$img = imagecreatefrompng($file);
				$imgalpha = imagecreate($wpx, $hpx);
				// generate gray scale palette (0 -> 255)
				for ($c = 0; $c < 256; ++$c) {
					ImageColorAllocate($imgalpha, $c, $c, $c);
				}
				// extract alpha channel
				for ($xpx = 0; $xpx < $wpx; ++$xpx) {
					for ($ypx = 0; $ypx < $hpx; ++$ypx) {
						$color = imagecolorat($img, $xpx, $ypx);
						// get and correct gamma color
						$alpha = $this->getGDgamma($img, $color);
						imagesetpixel($imgalpha, $xpx, $ypx, $alpha);
					}
				}
				imagepng($imgalpha, $tempfile_alpha);
				imagedestroy($imgalpha);
				// extract image without alpha channel
				$imgplain = imagecreatetruecolor($wpx, $hpx);
				imagecopy($imgplain, $img, 0, 0, 0, 0, $wpx, $hpx);
				imagepng($imgplain, $tempfile_plain);
				imagedestroy($imgplain);
				$parsed = true;
			} catch (Exception $e) {
				// GD fails
				$parse_error = 'GD library error: '.$e->getMessage();
			}
		}
		if ($parsed === false) {
			if (empty($parse_error)) {
				$this->Error('TCPDF requires the Imagick or GD extension to handle PNG images with alpha channel.');
			} else {
				$this->Error($parse_error);
			}
		}
		// embed mask image
		$imgmask = $this->Image($tempfile_alpha, $x, $y, $w, $h, 'PNG', '', '', $resize, $dpi, '', true, false);
		// embed image, masked with previously embedded mask
		$this->Image($tempfile_plain, $x, $y, $w, $h, $type, $link, $align, $resize, $dpi, $palign, false, $imgmask);
	}

	/**
	 * Get the GD-corrected PNG gamma value from alpha color
	 * @param $img (int) GD image Resource ID.
	 * @param $c (int) alpha color
	 * @protected
	 * @since 4.3.007 (2008-12-04)
	 */
	protected function getGDgamma($img, $c) {
		if (!isset($this->gdgammacache['#'.$c])) {
			$colors = imagecolorsforindex($img, $c);
			// GD alpha is only 7 bit (0 -> 127)
			$this->gdgammacache['#'.$c] = (((127 - $colors['alpha']) / 127) * 255);
			// correct gamma
			$this->gdgammacache['#'.$c] = (pow(($this->gdgammacache['#'.$c] / 255), 2.2) * 255);
			// store the latest values on cache to improve performances
			if (count($this->gdgammacache) > 8) {
				// remove one element from the cache array
				array_shift($this->gdgammacache);
			}
		}
		return $this->gdgammacache['#'.$c];
	}


	/**
	 * Embed vector-based Adobe Illustrator (AI) or AI-compatible EPS files.
	 * NOTE: EPS is not yet fully implemented, use the setRasterizeVectorImages() method to enable/disable rasterization of vector images using ImageMagick library.
	 * Only vector drawing is supported, not text or bitmap.
	 * Although the script was successfully tested with various AI format versions, best results are probably achieved with files that were exported in the AI3 format (tested with Illustrator CS2, Freehand MX and Photoshop CS2).
	 * @param $file (string) Name of the file containing the image or a '@' character followed by the EPS/AI data string.
	 * @param $x (float) Abscissa of the upper-left corner.
	 * @param $y (float) Ordinate of the upper-left corner.
	 * @param $w (float) Width of the image in the page. If not specified or equal to zero, it is automatically calculated.
	 * @param $h (float) Height of the image in the page. If not specified or equal to zero, it is automatically calculated.
	 * @param $link (mixed) URL or identifier returned by AddLink().
	 * @param $useBoundingBox (boolean) specifies whether to position the bounding box (true) or the complete canvas (false) at location (x,y). Default value is true.
	 * @param $align (string) Indicates the alignment of the pointer next to image insertion relative to image height. The value can be:<ul><li>T: top-right for LTR or top-left for RTL</li><li>M: middle-right for LTR or middle-left for RTL</li><li>B: bottom-right for LTR or bottom-left for RTL</li><li>N: next line</li></ul>
	 * @param $palign (string) Allows to center or align the image on the current line. Possible values are:<ul><li>L : left align</li><li>C : center</li><li>R : right align</li><li>'' : empty string : left for LTR or right for RTL</li></ul>
	 * @param $border (mixed) Indicates if borders must be drawn around the cell. The value can be a number:<ul><li>0: no border (default)</li><li>1: frame</li></ul> or a string containing some or all of the following characters (in any order):<ul><li>L: left</li><li>T: top</li><li>R: right</li><li>B: bottom</li></ul> or an array of line styles for each border group - for example: array('LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)))
	 * @param $fitonpage (boolean) if true the image is resized to not exceed page dimensions.
	 * @param $fixoutvals (boolean) if true remove values outside the bounding box.
	 * @author Valentin Schmidt, Nicola Asuni
	 * @since 3.1.000 (2008-06-09)
	 * @public
	 */
	public function ImageEps($file, $x='', $y='', $w=0, $h=0, $link='', $useBoundingBox=true, $align='', $palign='', $border=0, $fitonpage=false, $fixoutvals=false) {
		if ($this->state != 2) {
			 return;
		}
		if ($this->rasterize_vector_images AND ($w > 0) AND ($h > 0)) {
			// convert EPS to raster image using GD or ImageMagick libraries
			return $this->Image($file, $x, $y, $w, $h, 'EPS', $link, $align, true, 300, $palign, false, false, $border, false, false, $fitonpage);
		}
		if ($x === '') {
			$x = $this->x;
		}
		if ($y === '') {
			$y = $this->y;
		}
		// check page for no-write regions and adapt page margins if necessary
		list($x, $y) = $this->checkPageRegions($h, $x, $y);
		$k = $this->k;
		if ($file[0] === '@') { // image from string
			$data = substr($file, 1);
		} else { // EPS/AI file
			$data = TCPDF_STATIC::fileGetContents($file);
		}
		if ($data === FALSE) {
			$this->Error('EPS file not found: '.$file);
		}
		$regs = array();
		// EPS/AI compatibility check (only checks files created by Adobe Illustrator!)
		preg_match("/%%Creator:([^\r\n]+)/", $data, $regs); # find Creator
		if (count($regs) > 1) {
			$version_str = trim($regs[1]); # e.g. "Adobe Illustrator(R) 8.0"
			if (strpos($version_str, 'Adobe Illustrator') !== false) {
				$versexp = explode(' ', $version_str);
				$version = (float)array_pop($versexp);
				if ($version >= 9) {
					$this->Error('This version of Adobe Illustrator file is not supported: '.$file);
				}
			}
		}
		// strip binary bytes in front of PS-header
		$start = strpos($data, '%!PS-Adobe');
		if ($start > 0) {
			$data = substr($data, $start);
		}
		// find BoundingBox params
		preg_match("/%%BoundingBox:([^\r\n]+)/", $data, $regs);
		if (count($regs) > 1) {
			list($x1, $y1, $x2, $y2) = explode(' ', trim($regs[1]));
		} else {
			$this->Error('No BoundingBox found in EPS/AI file: '.$file);
		}
		$start = strpos($data, '%%EndSetup');
		if ($start === false) {
			$start = strpos($data, '%%EndProlog');
		}
		if ($start === false) {
			$start = strpos($data, '%%BoundingBox');
		}
		$data = substr($data, $start);
		$end = strpos($data, '%%PageTrailer');
		if ($end===false) {
			$end = strpos($data, 'showpage');
		}
		if ($end) {
			$data = substr($data, 0, $end);
		}
		// calculate image width and height on document
		if (($w <= 0) AND ($h <= 0)) {
			$w = ($x2 - $x1) / $k;
			$h = ($y2 - $y1) / $k;
		} elseif ($w <= 0) {
			$w = ($x2-$x1) / $k * ($h / (($y2 - $y1) / $k));
		} elseif ($h <= 0) {
			$h = ($y2 - $y1) / $k * ($w / (($x2 - $x1) / $k));
		}
		// fit the image on available space
		list($w, $h, $x, $y) = $this->fitBlock($w, $h, $x, $y, $fitonpage);
		if ($this->rasterize_vector_images) {
			// convert EPS to raster image using GD or ImageMagick libraries
			return $this->Image($file, $x, $y, $w, $h, 'EPS', $link, $align, true, 300, $palign, false, false, $border, false, false, $fitonpage);
		}
		// set scaling factors
		$scale_x = $w / (($x2 - $x1) / $k);
		$scale_y = $h / (($y2 - $y1) / $k);
		// set alignment
		$this->img_rb_y = $y + $h;
		// set alignment
		if ($this->rtl) {
			if ($palign == 'L') {
				$ximg = $this->lMargin;
			} elseif ($palign == 'C') {
				$ximg = ($this->w + $this->lMargin - $this->rMargin - $w) / 2;
			} elseif ($palign == 'R') {
				$ximg = $this->w - $this->rMargin - $w;
			} else {
				$ximg = $x - $w;
			}
			$this->img_rb_x = $ximg;
		} else {
			if ($palign == 'L') {
				$ximg = $this->lMargin;
			} elseif ($palign == 'C') {
				$ximg = ($this->w + $this->lMargin - $this->rMargin - $w) / 2;
			} elseif ($palign == 'R') {
				$ximg = $this->w - $this->rMargin - $w;
			} else {
				$ximg = $x;
			}
			$this->img_rb_x = $ximg + $w;
		}
		if ($useBoundingBox) {
			$dx = $ximg * $k - $x1;
			$dy = $y * $k - $y1;
		} else {
			$dx = $ximg * $k;
			$dy = $y * $k;
		}
		// save the current graphic state
		$this->_out('q'.$this->epsmarker);
		// translate
		$this->_out(sprintf('%F %F %F %F %F %F cm', 1, 0, 0, 1, $dx, $dy + ($this->hPt - (2 * $y * $k) - ($y2 - $y1))));
		// scale
		if (isset($scale_x)) {
			$this->_out(sprintf('%F %F %F %F %F %F cm', $scale_x, 0, 0, $scale_y, $x1 * (1 - $scale_x), $y2 * (1 - $scale_y)));
		}
		// handle pc/unix/mac line endings
		$lines = preg_split('/[\r\n]+/si', $data, -1, PREG_SPLIT_NO_EMPTY);
		$u=0;
		$cnt = count($lines);
		for ($i=0; $i < $cnt; ++$i) {
			$line = $lines[$i];
			if (($line == '') OR ($line[0] == '%')) {
				continue;
			}
			$len = strlen($line);
			// check for spot color names
			$color_name = '';
			if (strcasecmp('x', substr(trim($line), -1)) == 0) {
				if (preg_match('/\([^\)]*\)/', $line, $matches) > 0) {
					// extract spot color name
					$color_name = $matches[0];
					// remove color name from string
					$line = str_replace(' '.$color_name, '', $line);
					// remove pharentesis from color name
					$color_name = substr($color_name, 1, -1);
				}
			}
			$chunks = explode(' ', $line);
			$cmd = trim(array_pop($chunks));
			// RGB
			if (($cmd == 'Xa') OR ($cmd == 'XA')) {
				$b = array_pop($chunks);
				$g = array_pop($chunks);
				$r = array_pop($chunks);
				$this->_out(''.$r.' '.$g.' '.$b.' '.($cmd=='Xa'?'rg':'RG')); //substr($line, 0, -2).'rg' -> in EPS (AI8): c m y k r g b rg!
				continue;
			}
			$skip = false;
			if ($fixoutvals) {
				// check for values outside the bounding box
				switch ($cmd) {
					case 'm':
					case 'l':
					case 'L': {
						// skip values outside bounding box
						foreach ($chunks as $key => $val) {
							if ((($key % 2) == 0) AND (($val < $x1) OR ($val > $x2))) {
								$skip = true;
							} elseif ((($key % 2) != 0) AND (($val < $y1) OR ($val > $y2))) {
								$skip = true;
							}
						}
					}
				}
			}
			switch ($cmd) {
				case 'm':
				case 'l':
				case 'v':
				case 'y':
				case 'c':
				case 'k':
				case 'K':
				case 'g':
				case 'G':
				case 's':
				case 'S':
				case 'J':
				case 'j':
				case 'w':
				case 'M':
				case 'd':
				case 'n': {
					if ($skip) {
						break;
					}
					$this->_out($line);
					break;
				}
				case 'x': {// custom fill color
					if (empty($color_name)) {
						// CMYK color
						list($col_c, $col_m, $col_y, $col_k) = $chunks;
						$this->_out(''.$col_c.' '.$col_m.' '.$col_y.' '.$col_k.' k');
					} else {
						// Spot Color (CMYK + tint)
						list($col_c, $col_m, $col_y, $col_k, $col_t) = $chunks;
						$this->AddSpotColor($color_name, ($col_c * 100), ($col_m * 100), ($col_y * 100), ($col_k * 100));
						$color_cmd = sprintf('/CS%d cs %F scn', $this->spot_colors[$color_name]['i'], (1 - $col_t));
						$this->_out($color_cmd);
					}
					break;
				}
				case 'X': { // custom stroke color
					if (empty($color_name)) {
						// CMYK color
						list($col_c, $col_m, $col_y, $col_k) = $chunks;
						$this->_out(''.$col_c.' '.$col_m.' '.$col_y.' '.$col_k.' K');
					} else {
						// Spot Color (CMYK + tint)
						list($col_c, $col_m, $col_y, $col_k, $col_t) = $chunks;
						$this->AddSpotColor($color_name, ($col_c * 100), ($col_m * 100), ($col_y * 100), ($col_k * 100));
						$color_cmd = sprintf('/CS%d CS %F SCN', $this->spot_colors[$color_name]['i'], (1 - $col_t));
						$this->_out($color_cmd);
					}
					break;
				}
				case 'Y':
				case 'N':
				case 'V':
				case 'L':
				case 'C': {
					if ($skip) {
						break;
					}
					$line[($len - 1)] = strtolower($cmd);
					$this->_out($line);
					break;
				}
				case 'b':
				case 'B': {
					$this->_out($cmd . '*');
					break;
				}
				case 'f':
				case 'F': {
					if ($u > 0) {
						$isU = false;
						$max = min(($i + 5), $cnt);
						for ($j = ($i + 1); $j < $max; ++$j) {
							$isU = ($isU OR (($lines[$j] == 'U') OR ($lines[$j] == '*U')));
						}
						if ($isU) {
							$this->_out('f*');
						}
					} else {
						$this->_out('f*');
					}
					break;
				}
				case '*u': {
					++$u;
					break;
				}
				case '*U': {
					--$u;
					break;
				}
			}
		}
		// restore previous graphic state
		$this->_out($this->epsmarker.'Q');
		if (!empty($border)) {
			$bx = $this->x;
			$by = $this->y;
			$this->x = $ximg;
			if ($this->rtl) {
				$this->x += $w;
			}
			$this->y = $y;
			$this->Cell($w, $h, '', $border, 0, '', 0, '', 0, true);
			$this->x = $bx;
			$this->y = $by;
		}
		if ($link) {
			$this->Link($ximg, $y, $w, $h, $link, 0);
		}
		// set pointer to align the next text/objects
		switch($align) {
			case 'T':{
				$this->y = $y;
				$this->x = $this->img_rb_x;
				break;
			}
			case 'M':{
				$this->y = $y + round($h/2);
				$this->x = $this->img_rb_x;
				break;
			}
			case 'B':{
				$this->y = $this->img_rb_y;
				$this->x = $this->img_rb_x;
				break;
			}
			case 'N':{
				$this->SetY($this->img_rb_y);
				break;
			}
			default:{
				break;
			}
		}
		$this->endlinex = $this->img_rb_x;
	}

	/**
	 * Embed vector-based Adobe Illustrator (AI) or AI-compatible EPS files.
	 * NOTE: EPS is not yet fully implemented, use the setRasterizeVectorImages() method to enable/disable rasterization of vector images using ImageMagick library.
	 * Only vector drawing is supported, not text or bitmap.
	 * Although the script was successfully tested with various AI format versions, best results are probably achieved with files that were exported in the AI3 format (tested with Illustrator CS2, Freehand MX and Photoshop CS2).
	 * @param $file (string) Name of the file containing the image or a '@' character followed by the EPS/AI data string.
	 * @param $x (float) Abscissa of the upper-left corner.
	 * @param $y (float) Ordinate of the upper-left corner.
	 * @param $w (float) Width of the image in the page. If not specified or equal to zero, it is automatically calculated.
	 * @param $h (float) Height of the image in the page. If not specified or equal to zero, it is automatically calculated.
	 * @param $link (mixed) URL or identifier returned by AddLink().
	 * @param $useBoundingBox (boolean) specifies whether to position the bounding box (true) or the complete canvas (false) at location (x,y). Default value is true.
	 * @param $align (string) Indicates the alignment of the pointer next to image insertion relative to image height. The value can be:<ul><li>T: top-right for LTR or top-left for RTL</li><li>M: middle-right for LTR or middle-left for RTL</li><li>B: bottom-right for LTR or bottom-left for RTL</li><li>N: next line</li></ul>
	 * @param $palign (string) Allows to center or align the image on the current line. Possible values are:<ul><li>L : left align</li><li>C : center</li><li>R : right align</li><li>'' : empty string : left for LTR or right for RTL</li></ul>
	 * @param $border (mixed) Indicates if borders must be drawn around the cell. The value can be a number:<ul><li>0: no border (default)</li><li>1: frame</li></ul> or a string containing some or all of the following characters (in any order):<ul><li>L: left</li><li>T: top</li><li>R: right</li><li>B: bottom</li></ul> or an array of line styles for each border group - for example: array('LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)))
	 * @param $fitonpage (boolean) if true the image is resized to not exceed page dimensions.
	 * @param $fixoutvals (boolean) if true remove values outside the bounding box.
	 * @author Valentin Schmidt, Nicola Asuni
	 * @since 3.1.000 (2008-06-09)
	 * @public
	 */
	public function ImageEps($file, $x='', $y='', $w=0, $h=0, $link='', $useBoundingBox=true, $align='', $palign='', $border=0, $fitonpage=false, $fixoutvals=false) {
		if ($this->state != 2) {
			 return;
		}
		if ($this->rasterize_vector_images AND ($w > 0) AND ($h > 0)) {
			// convert EPS to raster image using GD or ImageMagick libraries
			return $this->Image($file, $x, $y, $w, $h, 'EPS', $link, $align, true, 300, $palign, false, false, $border, false, false, $fitonpage);
		}
		if ($x === '') {
			$x = $this->x;
		}
		if ($y === '') {
			$y = $this->y;
		}
		// check page for no-write regions and adapt page margins if necessary
		list($x, $y) = $this->checkPageRegions($h, $x, $y);
		$k = $this->k;
		if ($file[0] === '@') { // image from string
			$data = substr($file, 1);
		} else { // EPS/AI file
			$data = TCPDF_STATIC::fileGetContents($file);
		}
		if ($data === FALSE) {
			$this->Error('EPS file not found: '.$file);
		}
		$regs = array();
		// EPS/AI compatibility check (only checks files created by Adobe Illustrator!)
		preg_match("/%%Creator:([^\r\n]+)/", $data, $regs); # find Creator
		if (count($regs) > 1) {
			$version_str = trim($regs[1]); # e.g. "Adobe Illustrator(R) 8.0"
			if (strpos($version_str, 'Adobe Illustrator') !== false) {
				$versexp = explode(' ', $version_str);
				$version = (float)array_pop($versexp);
				if ($version >= 9) {
					$this->Error('This version of Adobe Illustrator file is not supported: '.$file);
				}
			}
		}
		// strip binary bytes in front of PS-header
		$start = strpos($data, '%!PS-Adobe');
		if ($start > 0) {
			$data = substr($data, $start);
		}
		// find BoundingBox params
		preg_match("/%%BoundingBox:([^\r\n]+)/", $data, $regs);
		if (count($regs) > 1) {
			list($x1, $y1, $x2, $y2) = explode(' ', trim($regs[1]));
		} else {
			$this->Error('No BoundingBox found in EPS/AI file: '.$file);
		}
		$start = strpos($data, '%%EndSetup');
		if ($start === false) {
			$start = strpos($data, '%%EndProlog');
		}
		if ($start === false) {
			$start = strpos($data, '%%BoundingBox');
		}
		$data = substr($data, $start);
		$end = strpos($data, '%%PageTrailer');
		if ($end===false) {
			$end = strpos($data, 'showpage');
		}
		if ($end) {
			$data = substr($data, 0, $end);
		}
		// calculate image width and height on document
		if (($w <= 0) AND ($h <= 0)) {
			$w = ($x2 - $x1) / $k;
			$h = ($y2 - $y1) / $k;
		} elseif ($w <= 0) {
			$w = ($x2-$x1) / $k * ($h / (($y2 - $y1) / $k));
		} elseif ($h <= 0) {
			$h = ($y2 - $y1) / $k * ($w / (($x2 - $x1) / $k));
		}
		// fit the image on available space
		list($w, $h, $x, $y) = $this->fitBlock($w, $h, $x, $y, $fitonpage);
		if ($this->rasterize_vector_images) {
			// convert EPS to raster image using GD or ImageMagick libraries
			return $this->Image($file, $x, $y, $w, $h, 'EPS', $link, $align, true, 300, $palign, false, false, $border, false, false, $fitonpage);
		}
		// set scaling factors
		$scale_x = $w / (($x2 - $x1) / $k);
		$scale_y = $h / (($y2 - $y1) / $k);
		// set alignment
		$this->img_rb_y = $y + $h;
		// set alignment
		if ($this->rtl) {
			if ($palign == 'L') {
				$ximg = $this->lMargin;
			} elseif ($palign == 'C') {
				$ximg = ($this->w + $this->lMargin - $this->rMargin - $w) / 2;
			} elseif ($palign == 'R') {
				$ximg = $this->w - $this->rMargin - $w;
			} else {
				$ximg = $x - $w;
			}
			$this->img_rb_x = $ximg;
		} else {
			if ($palign == 'L') {
				$ximg = $this->lMargin;
			} elseif ($palign == 'C') {
				$ximg = ($this->w + $this->lMargin - $this->rMargin - $w) / 2;
			} elseif ($palign == 'R') {
				$ximg = $this->w - $this->rMargin - $w;
			} else {
				$ximg = $x;
			}
			$this->img_rb_x = $ximg + $w;
		}
		if ($useBoundingBox) {
			$dx = $ximg * $k - $x1;
			$dy = $y * $k - $y1;
		} else {
			$dx = $ximg * $k;
			$dy = $y * $k;
		}
		// save the current graphic state
		$this->_out('q'.$this->epsmarker);
		// translate
		$this->_out(sprintf('%F %F %F %F %F %F cm', 1, 0, 0, 1, $dx, $dy + ($this->hPt - (2 * $y * $k) - ($y2 - $y1))));
		// scale
		if (isset($scale_x)) {
			$this->_out(sprintf('%F %F %F %F %F %F cm', $scale_x, 0, 0, $scale_y, $x1 * (1 - $scale_x), $y2 * (1 - $scale_y)));
		}
		// handle pc/unix/mac line endings
		$lines = preg_split('/[\r\n]+/si', $data, -1, PREG_SPLIT_NO_EMPTY);
		$u=0;
		$cnt = count($lines);
		for ($i=0; $i < $cnt; ++$i) {
			$line = $lines[$i];
			if (($line == '') OR ($line[0] == '%')) {
				continue;
			}
			$len = strlen($line);
			// check for spot color names
			$color_name = '';
			if (strcasecmp('x', substr(trim($line), -1)) == 0) {
				if (preg_match('/\([^\)]*\)/', $line, $matches) > 0) {
					// extract spot color name
					$color_name = $matches[0];
					// remove color name from string
					$line = str_replace(' '.$color_name, '', $line);
					// remove pharentesis from color name
					$color_name = substr($color_name, 1, -1);
				}
			}
			$chunks = explode(' ', $line);
			$cmd = trim(array_pop($chunks));
			// RGB
			if (($cmd == 'Xa') OR ($cmd == 'XA')) {
				$b = array_pop($chunks);
				$g = array_pop($chunks);
				$r = array_pop($chunks);
				$this->_out(''.$r.' '.$g.' '.$b.' '.($cmd=='Xa'?'rg':'RG')); //substr($line, 0, -2).'rg' -> in EPS (AI8): c m y k r g b rg!
				continue;
			}
			$skip = false;
			if ($fixoutvals) {
				// check for values outside the bounding box
				switch ($cmd) {
					case 'm':
					case 'l':
					case 'L': {
						// skip values outside bounding box
						foreach ($chunks as $key => $val) {
							if ((($key % 2) == 0) AND (($val < $x1) OR ($val > $x2))) {
								$skip = true;
							} elseif ((($key % 2) != 0) AND (($val < $y1) OR ($val > $y2))) {
								$skip = true;
							}
						}
					}
				}
			}
			switch ($cmd) {
				case 'm':
				case 'l':
				case 'v':
				case 'y':
				case 'c':
				case 'k':
				case 'K':
				case 'g':
				case 'G':
				case 's':
				case 'S':
				case 'J':
				case 'j':
				case 'w':
				case 'M':
				case 'd':
				case 'n': {
					if ($skip) {
						break;
					}
					$this->_out($line);
					break;
				}
				case 'x': {// custom fill color
					if (empty($color_name)) {
						// CMYK color
						list($col_c, $col_m, $col_y, $col_k) = $chunks;
						$this->_out(''.$col_c.' '.$col_m.' '.$col_y.' '.$col_k.' k');
					} else {
						// Spot Color (CMYK + tint)
						list($col_c, $col_m, $col_y, $col_k, $col_t) = $chunks;
						$this->AddSpotColor($color_name, ($col_c * 100), ($col_m * 100), ($col_y * 100), ($col_k * 100));
						$color_cmd = sprintf('/CS%d cs %F scn', $this->spot_colors[$color_name]['i'], (1 - $col_t));
						$this->_out($color_cmd);
					}
					break;
				}
				case 'X': { // custom stroke color
					if (empty($color_name)) {
						// CMYK color
						list($col_c, $col_m, $col_y, $col_k) = $chunks;
						$this->_out(''.$col_c.' '.$col_m.' '.$col_y.' '.$col_k.' K');
					} else {
						// Spot Color (CMYK + tint)
						list($col_c, $col_m, $col_y, $col_k, $col_t) = $chunks;
						$this->AddSpotColor($color_name, ($col_c * 100), ($col_m * 100), ($col_y * 100), ($col_k * 100));
						$color_cmd = sprintf('/CS%d CS %F SCN', $this->spot_colors[$color_name]['i'], (1 - $col_t));
						$this->_out($color_cmd);
					}
					break;
				}
				case 'Y':
				case 'N':
				case 'V':
				case 'L':
				case 'C': {
					if ($skip) {
						break;
					}
					$line[($len - 1)] = strtolower($cmd);
					$this->_out($line);
					break;
				}
				case 'b':
				case 'B': {
					$this->_out($cmd . '*');
					break;
				}
				case 'f':
				case 'F': {
					if ($u > 0) {
						$isU = false;
						$max = min(($i + 5), $cnt);
						for ($j = ($i + 1); $j < $max; ++$j) {
							$isU = ($isU OR (($lines[$j] == 'U') OR ($lines[$j] == '*U')));
						}
						if ($isU) {
							$this->_out('f*');
						}
					} else {
						$this->_out('f*');
					}
					break;
				}
				case '*u': {
					++$u;
					break;
				}
				case '*U': {
					--$u;
					break;
				}
			}
		}
		// restore previous graphic state
		$this->_out($this->epsmarker.'Q');
		if (!empty($border)) {
			$bx = $this->x;
			$by = $this->y;
			$this->x = $ximg;
			if ($this->rtl) {
				$this->x += $w;
			}
			$this->y = $y;
			$this->Cell($w, $h, '', $border, 0, '', 0, '', 0, true);
			$this->x = $bx;
			$this->y = $by;
		}
		if ($link) {
			$this->Link($ximg, $y, $w, $h, $link, 0);
		}
		// set pointer to align the next text/objects
		switch($align) {
			case 'T':{
				$this->y = $y;
				$this->x = $this->img_rb_x;
				break;
			}
			case 'M':{
				$this->y = $y + round($h/2);
				$this->x = $this->img_rb_x;
				break;
			}
			case 'B':{
				$this->y = $this->img_rb_y;
				$this->x = $this->img_rb_x;
				break;
			}
			case 'N':{
				$this->SetY($this->img_rb_y);
				break;
			}
			default:{
				break;
			}
		}
		$this->endlinex = $this->img_rb_x;
	}

