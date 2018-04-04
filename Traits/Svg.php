<?php

namespace Sztyup\Tcpdf\Traits;

trait Svg
{
	/**
	 * Embedd a Scalable Vector Graphics (SVG) image.
	 * NOTE: SVG standard is not yet fully implemented, use the setRasterizeVectorImages() method to enable/disable rasterization of vector images using ImageMagick library.
	 * @param $file (string) Name of the SVG file or a '@' character followed by the SVG data string.
	 * @param $x (float) Abscissa of the upper-left corner.
	 * @param $y (float) Ordinate of the upper-left corner.
	 * @param $w (float) Width of the image in the page. If not specified or equal to zero, it is automatically calculated.
	 * @param $h (float) Height of the image in the page. If not specified or equal to zero, it is automatically calculated.
	 * @param $link (mixed) URL or identifier returned by AddLink().
	 * @param $align (string) Indicates the alignment of the pointer next to image insertion relative to image height. The value can be:<ul><li>T: top-right for LTR or top-left for RTL</li><li>M: middle-right for LTR or middle-left for RTL</li><li>B: bottom-right for LTR or bottom-left for RTL</li><li>N: next line</li></ul> If the alignment is an empty string, then the pointer will be restored on the starting SVG position.
	 * @param $palign (string) Allows to center or align the image on the current line. Possible values are:<ul><li>L : left align</li><li>C : center</li><li>R : right align</li><li>'' : empty string : left for LTR or right for RTL</li></ul>
	 * @param $border (mixed) Indicates if borders must be drawn around the cell. The value can be a number:<ul><li>0: no border (default)</li><li>1: frame</li></ul> or a string containing some or all of the following characters (in any order):<ul><li>L: left</li><li>T: top</li><li>R: right</li><li>B: bottom</li></ul> or an array of line styles for each border group - for example: array('LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)))
	 * @param $fitonpage (boolean) if true the image is resized to not exceed page dimensions.
	 * @author Nicola Asuni
	 * @since 5.0.000 (2010-05-02)
	 * @public
	 */
	public function ImageSVG($file, $x='', $y='', $w=0, $h=0, $link='', $align='', $palign='', $border=0, $fitonpage=false) {
		if ($this->state != 2) {
			 return;
		}
		// reset SVG vars
		$this->svggradients = array();
		$this->svggradientid = 0;
		$this->svgdefsmode = false;
		$this->svgdefs = array();
		$this->svgclipmode = false;
		$this->svgclippaths = array();
		$this->svgcliptm = array();
		$this->svgclipid = 0;
		$this->svgtext = '';
		$this->svgtextmode = array();
		if ($this->rasterize_vector_images AND ($w > 0) AND ($h > 0)) {
			// convert SVG to raster image using GD or ImageMagick libraries
			return $this->Image($file, $x, $y, $w, $h, 'SVG', $link, $align, true, 300, $palign, false, false, $border, false, false, false);
		}
		if ($file[0] === '@') { // image from string
			$this->svgdir = '';
			$svgdata = substr($file, 1);
		} else { // SVG file
			$this->svgdir = dirname($file);
			$svgdata = TCPDF_STATIC::fileGetContents($file);
		}
		if ($svgdata === FALSE) {
			$this->Error('SVG file not found: '.$file);
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
		$ox = 0;
		$oy = 0;
		$ow = $w;
		$oh = $h;
		$aspect_ratio_align = 'xMidYMid';
		$aspect_ratio_ms = 'meet';
		$regs = array();
		// get original image width and height
		preg_match('/<svg([^\>]*)>/si', $svgdata, $regs);
		if (isset($regs[1]) AND !empty($regs[1])) {
			$tmp = array();
			if (preg_match('/[\s]+x[\s]*=[\s]*"([^"]*)"/si', $regs[1], $tmp)) {
				$ox = $this->getHTMLUnitToUnits($tmp[1], 0, $this->svgunit, false);
			}
			$tmp = array();
			if (preg_match('/[\s]+y[\s]*=[\s]*"([^"]*)"/si', $regs[1], $tmp)) {
				$oy = $this->getHTMLUnitToUnits($tmp[1], 0, $this->svgunit, false);
			}
			$tmp = array();
			if (preg_match('/[\s]+width[\s]*=[\s]*"([^"]*)"/si', $regs[1], $tmp)) {
				$ow = $this->getHTMLUnitToUnits($tmp[1], 1, $this->svgunit, false);
			}
			$tmp = array();
			if (preg_match('/[\s]+height[\s]*=[\s]*"([^"]*)"/si', $regs[1], $tmp)) {
				$oh = $this->getHTMLUnitToUnits($tmp[1], 1, $this->svgunit, false);
			}
			$tmp = array();
			$view_box = array();
			if (preg_match('/[\s]+viewBox[\s]*=[\s]*"[\s]*([0-9\.\-]+)[\s]+([0-9\.\-]+)[\s]+([0-9\.]+)[\s]+([0-9\.]+)[\s]*"/si', $regs[1], $tmp)) {
				if (count($tmp) == 5) {
					array_shift($tmp);
					foreach ($tmp as $key => $val) {
						$view_box[$key] = $this->getHTMLUnitToUnits($val, 0, $this->svgunit, false);
					}
					$ox = $view_box[0];
					$oy = $view_box[1];
				}
				// get aspect ratio
				$tmp = array();
				if (preg_match('/[\s]+preserveAspectRatio[\s]*=[\s]*"([^"]*)"/si', $regs[1], $tmp)) {
					$aspect_ratio = preg_split('/[\s]+/si', $tmp[1]);
					switch (count($aspect_ratio)) {
						case 3: {
							$aspect_ratio_align = $aspect_ratio[1];
							$aspect_ratio_ms = $aspect_ratio[2];
							break;
						}
						case 2: {
							$aspect_ratio_align = $aspect_ratio[0];
							$aspect_ratio_ms = $aspect_ratio[1];
							break;
						}
						case 1: {
							$aspect_ratio_align = $aspect_ratio[0];
							$aspect_ratio_ms = 'meet';
							break;
						}
					}
				}
			}
		}
		if ($ow <= 0) {
			$ow = 1;
		}
		if ($oh <= 0) {
			$oh = 1;
		}
		// calculate image width and height on document
		if (($w <= 0) AND ($h <= 0)) {
			// convert image size to document unit
			$w = $ow;
			$h = $oh;
		} elseif ($w <= 0) {
			$w = $h * $ow / $oh;
		} elseif ($h <= 0) {
			$h = $w * $oh / $ow;
		}
		// fit the image on available space
		list($w, $h, $x, $y) = $this->fitBlock($w, $h, $x, $y, $fitonpage);
		if ($this->rasterize_vector_images) {
			// convert SVG to raster image using GD or ImageMagick libraries
			return $this->Image($file, $x, $y, $w, $h, 'SVG', $link, $align, true, 300, $palign, false, false, $border, false, false, false);
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
		// store current graphic vars
		$gvars = $this->getGraphicVars();
		// store SVG position and scale factors
		$svgoffset_x = ($ximg - $ox) * $this->k;
		$svgoffset_y = -($y - $oy) * $this->k;
		if (isset($view_box[2]) AND ($view_box[2] > 0) AND ($view_box[3] > 0)) {
			$ow = $view_box[2];
			$oh = $view_box[3];
		} else {
			if ($ow <= 0) {
				$ow = $w;
			}
			if ($oh <= 0) {
				$oh = $h;
			}
		}
		$svgscale_x = $w / $ow;
		$svgscale_y = $h / $oh;
		// scaling and alignment
		if ($aspect_ratio_align != 'none') {
			// store current scaling values
			$svgscale_old_x = $svgscale_x;
			$svgscale_old_y = $svgscale_y;
			// force uniform scaling
			if ($aspect_ratio_ms == 'slice') {
				// the entire viewport is covered by the viewBox
				if ($svgscale_x > $svgscale_y) {
					$svgscale_y = $svgscale_x;
				} elseif ($svgscale_x < $svgscale_y) {
					$svgscale_x = $svgscale_y;
				}
			} else { // meet
				// the entire viewBox is visible within the viewport
				if ($svgscale_x < $svgscale_y) {
					$svgscale_y = $svgscale_x;
				} elseif ($svgscale_x > $svgscale_y) {
					$svgscale_x = $svgscale_y;
				}
			}
			// correct X alignment
			switch (substr($aspect_ratio_align, 1, 3)) {
				case 'Min': {
					// do nothing
					break;
				}
				case 'Max': {
					$svgoffset_x += (($w * $this->k) - ($ow * $this->k * $svgscale_x));
					break;
				}
				default:
				case 'Mid': {
					$svgoffset_x += ((($w * $this->k) - ($ow * $this->k * $svgscale_x)) / 2);
					break;
				}
			}
			// correct Y alignment
			switch (substr($aspect_ratio_align, 5)) {
				case 'Min': {
					// do nothing
					break;
				}
				case 'Max': {
					$svgoffset_y -= (($h * $this->k) - ($oh * $this->k * $svgscale_y));
					break;
				}
				default:
				case 'Mid': {
					$svgoffset_y -= ((($h * $this->k) - ($oh * $this->k * $svgscale_y)) / 2);
					break;
				}
			}
		}
		// store current page break mode
		$page_break_mode = $this->AutoPageBreak;
		$page_break_margin = $this->getBreakMargin();
		$cell_padding = $this->cell_padding;
		$this->SetCellPadding(0);
		$this->SetAutoPageBreak(false);
		// save the current graphic state
		$this->_out('q'.$this->epsmarker);
		// set initial clipping mask
		$this->Rect($ximg, $y, $w, $h, 'CNZ', array(), array());
		// scale and translate
		$e = $ox * $this->k * (1 - $svgscale_x);
		$f = ($this->h - $oy) * $this->k * (1 - $svgscale_y);
		$this->_out(sprintf('%F %F %F %F %F %F cm', $svgscale_x, 0, 0, $svgscale_y, ($e + $svgoffset_x), ($f + $svgoffset_y)));
		// creates a new XML parser to be used by the other XML functions
		$this->parser = xml_parser_create('UTF-8');
		// the following function allows to use parser inside object
		xml_set_object($this->parser, $this);
		// disable case-folding for this XML parser
		xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0);
		// sets the element handler functions for the XML parser
		xml_set_element_handler($this->parser, 'startSVGElementHandler', 'endSVGElementHandler');
		// sets the character data handler function for the XML parser
		xml_set_character_data_handler($this->parser, 'segSVGContentHandler');
		// start parsing an XML document
		if (!xml_parse($this->parser, $svgdata)) {
			$error_message = sprintf('SVG Error: %s at line %d', xml_error_string(xml_get_error_code($this->parser)), xml_get_current_line_number($this->parser));
			$this->Error($error_message);
		}
		// free this XML parser
		xml_parser_free($this->parser);
		// restore previous graphic state
		$this->_out($this->epsmarker.'Q');
		// restore graphic vars
		$this->setGraphicVars($gvars);
		$this->lasth = $gvars['lasth'];
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
				// restore pointer to starting position
				$this->x = $gvars['x'];
				$this->y = $gvars['y'];
				$this->page = $gvars['page'];
				$this->current_column = $gvars['current_column'];
				$this->tMargin = $gvars['tMargin'];
				$this->bMargin = $gvars['bMargin'];
				$this->w = $gvars['w'];
				$this->h = $gvars['h'];
				$this->wPt = $gvars['wPt'];
				$this->hPt = $gvars['hPt'];
				$this->fwPt = $gvars['fwPt'];
				$this->fhPt = $gvars['fhPt'];
				break;
			}
		}
		$this->endlinex = $this->img_rb_x;
		// restore page break
		$this->SetAutoPageBreak($page_break_mode, $page_break_margin);
		$this->cell_padding = $cell_padding;
	}

	/**
	 * Convert SVG transformation matrix to PDF.
	 * @param $tm (array) original SVG transformation matrix
	 * @return array transformation matrix
	 * @protected
	 * @since 5.0.000 (2010-05-02)
	 */
	protected function convertSVGtMatrix($tm) {
		$a = $tm[0];
		$b = -$tm[1];
		$c = -$tm[2];
		$d = $tm[3];
		$e = $this->getHTMLUnitToUnits($tm[4], 1, $this->svgunit, false) * $this->k;
		$f = -$this->getHTMLUnitToUnits($tm[5], 1, $this->svgunit, false) * $this->k;
		$x = 0;
		$y = $this->h * $this->k;
		$e = ($x * (1 - $a)) - ($y * $c) + $e;
		$f = ($y * (1 - $d)) - ($x * $b) + $f;
		return array($a, $b, $c, $d, $e, $f);
	}

	/**
	 * Apply SVG graphic transformation matrix.
	 * @param $tm (array) original SVG transformation matrix
	 * @protected
	 * @since 5.0.000 (2010-05-02)
	 */
	protected function SVGTransform($tm) {
		$this->Transform($this->convertSVGtMatrix($tm));
	}

	/**
	 * Apply the requested SVG styles (*** TO BE COMPLETED ***)
	 * @param $svgstyle (array) array of SVG styles to apply
	 * @param $prevsvgstyle (array) array of previous SVG style
	 * @param $x (int) X origin of the bounding box
	 * @param $y (int) Y origin of the bounding box
	 * @param $w (int) width of the bounding box
	 * @param $h (int) height of the bounding box
	 * @param $clip_function (string) clip function
	 * @param $clip_params (array) array of parameters for clipping function
	 * @return object style
	 * @author Nicola Asuni
	 * @since 5.0.000 (2010-05-02)
	 * @protected
	 */
	protected function setSVGStyles($svgstyle, $prevsvgstyle, $x=0, $y=0, $w=1, $h=1, $clip_function='', $clip_params=array()) {
		if ($this->state != 2) {
			 return;
		}
		$objstyle = '';
		$minlen = (0.01 / $this->k); // minimum acceptable length
		if (!isset($svgstyle['opacity'])) {
			return $objstyle;
		}
		// clip-path
		$regs = array();
		if (preg_match('/url\([\s]*\#([^\)]*)\)/si', $svgstyle['clip-path'], $regs)) {
			$clip_path = $this->svgclippaths[$regs[1]];
			foreach ($clip_path as $cp) {
				$this->startSVGElementHandler('clip-path', $cp['name'], $cp['attribs'], $cp['tm']);
			}
		}
		// opacity
		if ($svgstyle['opacity'] != 1) {
			$this->setAlpha($svgstyle['opacity'], 'Normal', $svgstyle['opacity'], false);
		}
		// color
		$fill_color = TCPDF_COLORS::convertHTMLColorToDec($svgstyle['color'], $this->spot_colors);
		$this->SetFillColorArray($fill_color);
		// text color
		$text_color = TCPDF_COLORS::convertHTMLColorToDec($svgstyle['text-color'], $this->spot_colors);
		$this->SetTextColorArray($text_color);
		// clip
		if (preg_match('/rect\(([a-z0-9\-\.]*)[\s]*([a-z0-9\-\.]*)[\s]*([a-z0-9\-\.]*)[\s]*([a-z0-9\-\.]*)\)/si', $svgstyle['clip'], $regs)) {
			$top = (isset($regs[1])?$this->getHTMLUnitToUnits($regs[1], 0, $this->svgunit, false):0);
			$right = (isset($regs[2])?$this->getHTMLUnitToUnits($regs[2], 0, $this->svgunit, false):0);
			$bottom = (isset($regs[3])?$this->getHTMLUnitToUnits($regs[3], 0, $this->svgunit, false):0);
			$left = (isset($regs[4])?$this->getHTMLUnitToUnits($regs[4], 0, $this->svgunit, false):0);
			$cx = $x + $left;
			$cy = $y + $top;
			$cw = $w - $left - $right;
			$ch = $h - $top - $bottom;
			if ($svgstyle['clip-rule'] == 'evenodd') {
				$clip_rule = 'CNZ';
			} else {
				$clip_rule = 'CEO';
			}
			$this->Rect($cx, $cy, $cw, $ch, $clip_rule, array(), array());
		}
		// fill
		$regs = array();
		if (preg_match('/url\([\s]*\#([^\)]*)\)/si', $svgstyle['fill'], $regs)) {
			// gradient
			$gradient = $this->svggradients[$regs[1]];
			if (isset($gradient['xref'])) {
				// reference to another gradient definition
				$newgradient = $this->svggradients[$gradient['xref']];
				$newgradient['coords'] = $gradient['coords'];
				$newgradient['mode'] = $gradient['mode'];
				$newgradient['type'] = $gradient['type'];
				$newgradient['gradientUnits'] = $gradient['gradientUnits'];
				if (isset($gradient['gradientTransform'])) {
					$newgradient['gradientTransform'] = $gradient['gradientTransform'];
				}
				$gradient = $newgradient;
			}
			//save current Graphic State
			$this->_outSaveGraphicsState();
			//set clipping area
			if (!empty($clip_function) AND method_exists($this, $clip_function)) {
				$bbox = call_user_func_array(array($this, $clip_function), $clip_params);
				if ((!isset($gradient['type']) OR ($gradient['type'] != 3)) AND is_array($bbox) AND (count($bbox) == 4)) {
					list($x, $y, $w, $h) = $bbox;
				}
			}
			if ($gradient['mode'] == 'measure') {
				if (!isset($gradient['coords'][4])) {
					$gradient['coords'][4] = 0.5;
				}
				if (isset($gradient['gradientTransform']) AND !empty($gradient['gradientTransform'])) {
					$gtm = $gradient['gradientTransform'];
					// apply transformation matrix
					$xa = ($gtm[0] * $gradient['coords'][0]) + ($gtm[2] * $gradient['coords'][1]) + $gtm[4];
					$ya = ($gtm[1] * $gradient['coords'][0]) + ($gtm[3] * $gradient['coords'][1]) + $gtm[5];
					$xb = ($gtm[0] * $gradient['coords'][2]) + ($gtm[2] * $gradient['coords'][3]) + $gtm[4];
					$yb = ($gtm[1] * $gradient['coords'][2]) + ($gtm[3] * $gradient['coords'][3]) + $gtm[5];
					$r = sqrt(pow(($gtm[0] * $gradient['coords'][4]), 2) + pow(($gtm[1] * $gradient['coords'][4]), 2));
					$gradient['coords'][0] = $xa;
					$gradient['coords'][1] = $ya;
					$gradient['coords'][2] = $xb;
					$gradient['coords'][3] = $yb;
					$gradient['coords'][4] = $r;
				}
				// convert SVG coordinates to user units
				$gradient['coords'][0] = $this->getHTMLUnitToUnits($gradient['coords'][0], 0, $this->svgunit, false);
				$gradient['coords'][1] = $this->getHTMLUnitToUnits($gradient['coords'][1], 0, $this->svgunit, false);
				$gradient['coords'][2] = $this->getHTMLUnitToUnits($gradient['coords'][2], 0, $this->svgunit, false);
				$gradient['coords'][3] = $this->getHTMLUnitToUnits($gradient['coords'][3], 0, $this->svgunit, false);
				$gradient['coords'][4] = $this->getHTMLUnitToUnits($gradient['coords'][4], 0, $this->svgunit, false);
				if ($w <= $minlen) {
					$w = $minlen;
				}
				if ($h <= $minlen) {
					$h = $minlen;
				}
				// shift units
				if ($gradient['gradientUnits'] == 'objectBoundingBox') {
					// convert to SVG coordinate system
					$gradient['coords'][0] += $x;
					$gradient['coords'][1] += $y;
					$gradient['coords'][2] += $x;
					$gradient['coords'][3] += $y;
				}
				// calculate percentages
				$gradient['coords'][0] = (($gradient['coords'][0] - $x) / $w);
				$gradient['coords'][1] = (($gradient['coords'][1] - $y) / $h);
				$gradient['coords'][2] = (($gradient['coords'][2] - $x) / $w);
				$gradient['coords'][3] = (($gradient['coords'][3] - $y) / $h);
				$gradient['coords'][4] /= $w;
			} elseif ($gradient['mode'] == 'percentage') {
				foreach($gradient['coords'] as $key => $val) {
					$gradient['coords'][$key] = (intval($val) / 100);
					if ($val < 0) {
						$gradient['coords'][$key] = 0;
					} elseif ($val > 1) {
						$gradient['coords'][$key] = 1;
					}
				}
			}
			if (($gradient['type'] == 2) AND ($gradient['coords'][0] == $gradient['coords'][2]) AND ($gradient['coords'][1] == $gradient['coords'][3])) {
				// single color (no shading)
				$gradient['coords'][0] = 1;
				$gradient['coords'][1] = 0;
				$gradient['coords'][2] = 0.999;
				$gradient['coords'][3] = 0;
			}
			// swap Y coordinates
			$tmp = $gradient['coords'][1];
			$gradient['coords'][1] = $gradient['coords'][3];
			$gradient['coords'][3] = $tmp;
			// set transformation map for gradient
			$cy = ($this->h - $y);
			if ($gradient['type'] == 3) {
				// circular gradient
				$cy -= ($gradient['coords'][1] * ($w + $h));
				$h = $w = max($w, $h);
			} else {
				$cy -= $h;
			}
			$this->_out(sprintf('%F 0 0 %F %F %F cm', ($w * $this->k), ($h * $this->k), ($x * $this->k), ($cy * $this->k)));
			if (count($gradient['stops']) > 1) {
				$this->Gradient($gradient['type'], $gradient['coords'], $gradient['stops'], array(), false);
			}
		} elseif ($svgstyle['fill'] != 'none') {
			$fill_color = TCPDF_COLORS::convertHTMLColorToDec($svgstyle['fill'], $this->spot_colors);
			if ($svgstyle['fill-opacity'] != 1) {
				$this->setAlpha($this->alpha['CA'], 'Normal', $svgstyle['fill-opacity'], false);
			}
			$this->SetFillColorArray($fill_color);
			if ($svgstyle['fill-rule'] == 'evenodd') {
				$objstyle .= 'F*';
			} else {
				$objstyle .= 'F';
			}
		}
		// stroke
		if ($svgstyle['stroke'] != 'none') {
			if ($svgstyle['stroke-opacity'] != 1) {
				$this->setAlpha($svgstyle['stroke-opacity'], 'Normal', $this->alpha['ca'], false);
			} elseif (preg_match('/rgba\(\d+%?,\s*\d+%?,\s*\d+%?,\s*(\d+(?:\.\d+)?)\)/i', $svgstyle['stroke'], $rgba_matches)) {
				$this->setAlpha($rgba_matches[1], 'Normal', $this->alpha['ca'], false);
			}
			$stroke_style = array(
				'color' => TCPDF_COLORS::convertHTMLColorToDec($svgstyle['stroke'], $this->spot_colors),
				'width' => $this->getHTMLUnitToUnits($svgstyle['stroke-width'], 0, $this->svgunit, false),
				'cap' => $svgstyle['stroke-linecap'],
				'join' => $svgstyle['stroke-linejoin']
				);
			if (isset($svgstyle['stroke-dasharray']) AND !empty($svgstyle['stroke-dasharray']) AND ($svgstyle['stroke-dasharray'] != 'none')) {
				$stroke_style['dash'] = $svgstyle['stroke-dasharray'];
			}
			$this->SetLineStyle($stroke_style);
			$objstyle .= 'D';
		}
		// font
		$regs = array();
		if (!empty($svgstyle['font'])) {
			if (preg_match('/font-family[\s]*:[\s]*([^\;\"]*)/si', $svgstyle['font'], $regs)) {
				$font_family = $this->getFontFamilyName($regs[1]);
			} else {
				$font_family = $svgstyle['font-family'];
			}
			if (preg_match('/font-size[\s]*:[\s]*([^\s\;\"]*)/si', $svgstyle['font'], $regs)) {
				$font_size = trim($regs[1]);
			} else {
				$font_size = $svgstyle['font-size'];
			}
			if (preg_match('/font-style[\s]*:[\s]*([^\s\;\"]*)/si', $svgstyle['font'], $regs)) {
				$font_style = trim($regs[1]);
			} else {
				$font_style = $svgstyle['font-style'];
			}
			if (preg_match('/font-weight[\s]*:[\s]*([^\s\;\"]*)/si', $svgstyle['font'], $regs)) {
				$font_weight = trim($regs[1]);
			} else {
				$font_weight = $svgstyle['font-weight'];
			}
			if (preg_match('/font-stretch[\s]*:[\s]*([^\s\;\"]*)/si', $svgstyle['font'], $regs)) {
				$font_stretch = trim($regs[1]);
			} else {
				$font_stretch = $svgstyle['font-stretch'];
			}
			if (preg_match('/letter-spacing[\s]*:[\s]*([^\s\;\"]*)/si', $svgstyle['font'], $regs)) {
				$font_spacing = trim($regs[1]);
			} else {
				$font_spacing = $svgstyle['letter-spacing'];
			}
		} else {
			$font_family = $this->getFontFamilyName($svgstyle['font-family']);
			$font_size = $svgstyle['font-size'];
			$font_style = $svgstyle['font-style'];
			$font_weight = $svgstyle['font-weight'];
			$font_stretch = $svgstyle['font-stretch'];
			$font_spacing = $svgstyle['letter-spacing'];
		}
		$font_size = $this->getHTMLFontUnits($font_size, $this->svgstyles[0]['font-size'], $prevsvgstyle['font-size'], $this->svgunit);
		$font_stretch = $this->getCSSFontStretching($font_stretch, $svgstyle['font-stretch']);
		$font_spacing = $this->getCSSFontSpacing($font_spacing, $svgstyle['letter-spacing']);
		switch ($font_style) {
			case 'italic': {
				$font_style = 'I';
				break;
			}
			case 'oblique': {
				$font_style = 'I';
				break;
			}
			default:
			case 'normal': {
				$font_style = '';
				break;
			}
		}
		switch ($font_weight) {
			case 'bold':
			case 'bolder': {
				$font_style .= 'B';
				break;
			}
			case 'normal': {
				if ((substr($font_family, -1) == 'I') AND (substr($font_family, -2, 1) == 'B')) {
					$font_family = substr($font_family, 0, -2).'I';
				} elseif (substr($font_family, -1) == 'B') {
					$font_family = substr($font_family, 0, -1);
				}
				break;
			}
		}
		switch ($svgstyle['text-decoration']) {
			case 'underline': {
				$font_style .= 'U';
				break;
			}
			case 'overline': {
				$font_style .= 'O';
				break;
			}
			case 'line-through': {
				$font_style .= 'D';
				break;
			}
			default:
			case 'none': {
				break;
			}
		}
		$this->SetFont($font_family, $font_style, $font_size);
		$this->setFontStretching($font_stretch);
		$this->setFontSpacing($font_spacing);
		return $objstyle;
	}

	/**
	 * Draws an SVG path
	 * @param $d (string) attribute d of the path SVG element
	 * @param $style (string) Style of rendering. Possible values are:
	 * <ul>
	 *	 <li>D or empty string: Draw (default).</li>
	 *	 <li>F: Fill.</li>
	 *	 <li>F*: Fill using the even-odd rule to determine which regions lie inside the clipping path.</li>
	 *	 <li>DF or FD: Draw and fill.</li>
	 *	 <li>DF* or FD*: Draw and fill using the even-odd rule to determine which regions lie inside the clipping path.</li>
	 *	 <li>CNZ: Clipping mode (using the even-odd rule to determine which regions lie inside the clipping path).</li>
	 *	 <li>CEO: Clipping mode (using the nonzero winding number rule to determine which regions lie inside the clipping path).</li>
	 * </ul>
	 * @return array of container box measures (x, y, w, h)
	 * @author Nicola Asuni
	 * @since 5.0.000 (2010-05-02)
	 * @protected
	 */
	protected function SVGPath($d, $style='') {
		if ($this->state != 2) {
			 return;
		}
		// set fill/stroke style
		$op = TCPDF_STATIC::getPathPaintOperator($style, '');
		if (empty($op)) {
			return;
		}
		$paths = array();
		$d = preg_replace('/([0-9ACHLMQSTVZ])([\-\+])/si', '\\1 \\2', $d);
		preg_match_all('/([ACHLMQSTVZ])[\s]*([^ACHLMQSTVZ\"]*)/si', $d, $paths, PREG_SET_ORDER);
		$x = 0;
		$y = 0;
		$x1 = 0;
		$y1 = 0;
		$x2 = 0;
		$y2 = 0;
		$xmin = 2147483647;
		$xmax = 0;
		$ymin = 2147483647;
		$ymax = 0;
		$relcoord = false;
		$minlen = (0.01 / $this->k); // minimum acceptable length (3 point)
		$firstcmd = true; // used to print first point
		// draw curve pieces
		foreach ($paths as $key => $val) {
			// get curve type
			$cmd = trim($val[1]);
			if (strtolower($cmd) == $cmd) {
				// use relative coordinated instead of absolute
				$relcoord = true;
				$xoffset = $x;
				$yoffset = $y;
			} else {
				$relcoord = false;
				$xoffset = 0;
				$yoffset = 0;
			}
			$params = array();
			if (isset($val[2])) {
				// get curve parameters
				$rawparams = preg_split('/([\,\s]+)/si', trim($val[2]));
				$params = array();
				foreach ($rawparams as $ck => $cp) {
					$params[$ck] = $this->getHTMLUnitToUnits($cp, 0, $this->svgunit, false);
					if (abs($params[$ck]) < $minlen) {
						// approximate little values to zero
						$params[$ck] = 0;
					}
				}
			}
			// store current origin point
			$x0 = $x;
			$y0 = $y;
			switch (strtoupper($cmd)) {
				case 'M': { // moveto
					foreach ($params as $ck => $cp) {
						if (($ck % 2) == 0) {
							$x = $cp + $xoffset;
						} else {
							$y = $cp + $yoffset;
							if ($firstcmd OR (abs($x0 - $x) >= $minlen) OR (abs($y0 - $y) >= $minlen)) {
								if ($ck == 1) {
									$this->_outPoint($x, $y);
									$firstcmd = false;
								} else {
									$this->_outLine($x, $y);
								}
								$x0 = $x;
								$y0 = $y;
							}
							$xmin = min($xmin, $x);
							$ymin = min($ymin, $y);
							$xmax = max($xmax, $x);
							$ymax = max($ymax, $y);
							if ($relcoord) {
								$xoffset = $x;
								$yoffset = $y;
							}
						}
					}
					break;
				}
				case 'L': { // lineto
					foreach ($params as $ck => $cp) {
						if (($ck % 2) == 0) {
							$x = $cp + $xoffset;
						} else {
							$y = $cp + $yoffset;
							if ((abs($x0 - $x) >= $minlen) OR (abs($y0 - $y) >= $minlen)) {
								$this->_outLine($x, $y);
								$x0 = $x;
								$y0 = $y;
							}
							$xmin = min($xmin, $x);
							$ymin = min($ymin, $y);
							$xmax = max($xmax, $x);
							$ymax = max($ymax, $y);
							if ($relcoord) {
								$xoffset = $x;
								$yoffset = $y;
							}
						}
					}
					break;
				}
				case 'H': { // horizontal lineto
					foreach ($params as $ck => $cp) {
						$x = $cp + $xoffset;
						if ((abs($x0 - $x) >= $minlen) OR (abs($y0 - $y) >= $minlen)) {
							$this->_outLine($x, $y);
							$x0 = $x;
							$y0 = $y;
						}
						$xmin = min($xmin, $x);
						$xmax = max($xmax, $x);
						if ($relcoord) {
							$xoffset = $x;
						}
					}
					break;
				}
				case 'V': { // vertical lineto
					foreach ($params as $ck => $cp) {
						$y = $cp + $yoffset;
						if ((abs($x0 - $x) >= $minlen) OR (abs($y0 - $y) >= $minlen)) {
							$this->_outLine($x, $y);
							$x0 = $x;
							$y0 = $y;
						}
						$ymin = min($ymin, $y);
						$ymax = max($ymax, $y);
						if ($relcoord) {
							$yoffset = $y;
						}
					}
					break;
				}
				case 'C': { // curveto
					foreach ($params as $ck => $cp) {
						$params[$ck] = $cp;
						if ((($ck + 1) % 6) == 0) {
							$x1 = $params[($ck - 5)] + $xoffset;
							$y1 = $params[($ck - 4)] + $yoffset;
							$x2 = $params[($ck - 3)] + $xoffset;
							$y2 = $params[($ck - 2)] + $yoffset;
							$x = $params[($ck - 1)] + $xoffset;
							$y = $params[($ck)] + $yoffset;
							$this->_outCurve($x1, $y1, $x2, $y2, $x, $y);
							$xmin = min($xmin, $x, $x1, $x2);
							$ymin = min($ymin, $y, $y1, $y2);
							$xmax = max($xmax, $x, $x1, $x2);
							$ymax = max($ymax, $y, $y1, $y2);
							if ($relcoord) {
								$xoffset = $x;
								$yoffset = $y;
							}
						}
					}
					break;
				}
				case 'S': { // shorthand/smooth curveto
					foreach ($params as $ck => $cp) {
						$params[$ck] = $cp;
						if ((($ck + 1) % 4) == 0) {
							if (($key > 0) AND ((strtoupper($paths[($key - 1)][1]) == 'C') OR (strtoupper($paths[($key - 1)][1]) == 'S'))) {
								$x1 = (2 * $x) - $x2;
								$y1 = (2 * $y) - $y2;
							} else {
								$x1 = $x;
								$y1 = $y;
							}
							$x2 = $params[($ck - 3)] + $xoffset;
							$y2 = $params[($ck - 2)] + $yoffset;
							$x = $params[($ck - 1)] + $xoffset;
							$y = $params[($ck)] + $yoffset;
							$this->_outCurve($x1, $y1, $x2, $y2, $x, $y);
							$xmin = min($xmin, $x, $x1, $x2);
							$ymin = min($ymin, $y, $y1, $y2);
							$xmax = max($xmax, $x, $x1, $x2);
							$ymax = max($ymax, $y, $y1, $y2);
							if ($relcoord) {
								$xoffset = $x;
								$yoffset = $y;
							}
						}
					}
					break;
				}
				case 'Q': { // quadratic Bezier curveto
					foreach ($params as $ck => $cp) {
						$params[$ck] = $cp;
						if ((($ck + 1) % 4) == 0) {
							// convert quadratic points to cubic points
							$x1 = $params[($ck - 3)] + $xoffset;
							$y1 = $params[($ck - 2)] + $yoffset;
							$xa = ($x + (2 * $x1)) / 3;
							$ya = ($y + (2 * $y1)) / 3;
							$x = $params[($ck - 1)] + $xoffset;
							$y = $params[($ck)] + $yoffset;
							$xb = ($x + (2 * $x1)) / 3;
							$yb = ($y + (2 * $y1)) / 3;
							$this->_outCurve($xa, $ya, $xb, $yb, $x, $y);
							$xmin = min($xmin, $x, $xa, $xb);
							$ymin = min($ymin, $y, $ya, $yb);
							$xmax = max($xmax, $x, $xa, $xb);
							$ymax = max($ymax, $y, $ya, $yb);
							if ($relcoord) {
								$xoffset = $x;
								$yoffset = $y;
							}
						}
					}
					break;
				}
				case 'T': { // shorthand/smooth quadratic Bezier curveto
					foreach ($params as $ck => $cp) {
						$params[$ck] = $cp;
						if (($ck % 2) != 0) {
							if (($key > 0) AND ((strtoupper($paths[($key - 1)][1]) == 'Q') OR (strtoupper($paths[($key - 1)][1]) == 'T'))) {
								$x1 = (2 * $x) - $x1;
								$y1 = (2 * $y) - $y1;
							} else {
								$x1 = $x;
								$y1 = $y;
							}
							// convert quadratic points to cubic points
							$xa = ($x + (2 * $x1)) / 3;
							$ya = ($y + (2 * $y1)) / 3;
							$x = $params[($ck - 1)] + $xoffset;
							$y = $params[($ck)] + $yoffset;
							$xb = ($x + (2 * $x1)) / 3;
							$yb = ($y + (2 * $y1)) / 3;
							$this->_outCurve($xa, $ya, $xb, $yb, $x, $y);
							$xmin = min($xmin, $x, $xa, $xb);
							$ymin = min($ymin, $y, $ya, $yb);
							$xmax = max($xmax, $x, $xa, $xb);
							$ymax = max($ymax, $y, $ya, $yb);
							if ($relcoord) {
								$xoffset = $x;
								$yoffset = $y;
							}
						}
					}
					break;
				}
				case 'A': { // elliptical arc
					foreach ($params as $ck => $cp) {
						$params[$ck] = $cp;
						if ((($ck + 1) % 7) == 0) {
							$x0 = $x;
							$y0 = $y;
							$rx = abs($params[($ck - 6)]);
							$ry = abs($params[($ck - 5)]);
							$ang = -$rawparams[($ck - 4)];
							$angle = deg2rad($ang);
							$fa = $rawparams[($ck - 3)]; // large-arc-flag
							$fs = $rawparams[($ck - 2)]; // sweep-flag
							$x = $params[($ck - 1)] + $xoffset;
							$y = $params[$ck] + $yoffset;
							if ((abs($x0 - $x) < $minlen) AND (abs($y0 - $y) < $minlen)) {
								// endpoints are almost identical
								$xmin = min($xmin, $x);
								$ymin = min($ymin, $y);
								$xmax = max($xmax, $x);
								$ymax = max($ymax, $y);
							} else {
								$cos_ang = cos($angle);
								$sin_ang = sin($angle);
								$a = (($x0 - $x) / 2);
								$b = (($y0 - $y) / 2);
								$xa = ($a * $cos_ang) - ($b * $sin_ang);
								$ya = ($a * $sin_ang) + ($b * $cos_ang);
								$rx2 = $rx * $rx;
								$ry2 = $ry * $ry;
								$xa2 = $xa * $xa;
								$ya2 = $ya * $ya;
								$delta = ($xa2 / $rx2) + ($ya2 / $ry2);
								if ($delta > 1) {
									$rx *= sqrt($delta);
									$ry *= sqrt($delta);
									$rx2 = $rx * $rx;
									$ry2 = $ry * $ry;
								}
								$numerator = (($rx2 * $ry2) - ($rx2 * $ya2) - ($ry2 * $xa2));
								if ($numerator < 0) {
									$root = 0;
								} else {
									$root = sqrt($numerator / (($rx2 * $ya2) + ($ry2 * $xa2)));
								}
								if ($fa == $fs){
									$root *= -1;
								}
								$cax = $root * (($rx * $ya) / $ry);
								$cay = -$root * (($ry * $xa) / $rx);
								// coordinates of ellipse center
								$cx = ($cax * $cos_ang) - ($cay * $sin_ang) + (($x0 + $x) / 2);
								$cy = ($cax * $sin_ang) + ($cay * $cos_ang) + (($y0 + $y) / 2);
								// get angles
								$angs = TCPDF_STATIC::getVectorsAngle(1, 0, (($xa - $cax) / $rx), (($cay - $ya) / $ry));
								$dang = TCPDF_STATIC::getVectorsAngle((($xa - $cax) / $rx), (($ya - $cay) / $ry), ((-$xa - $cax) / $rx), ((-$ya - $cay) / $ry));
								if (($fs == 0) AND ($dang > 0)) {
									$dang -= (2 * M_PI);
								} elseif (($fs == 1) AND ($dang < 0)) {
									$dang += (2 * M_PI);
								}
								$angf = $angs - $dang;
								if ((($fs == 0) AND ($angs > $angf)) OR (($fs == 1) AND ($angs < $angf))) {
									// reverse angles
									$tmp = $angs;
									$angs = $angf;
									$angf = $tmp;
								}
								$angs = round(rad2deg($angs), 6);
								$angf = round(rad2deg($angf), 6);
								// covent angles to positive values
								if (($angs < 0) AND ($angf < 0)) {
									$angs += 360;
									$angf += 360;
								}
								$pie = false;
								if (($key == 0) AND (isset($paths[($key + 1)][1])) AND (trim($paths[($key + 1)][1]) == 'z')) {
									$pie = true;
								}
								list($axmin, $aymin, $axmax, $aymax) = $this->_outellipticalarc($cx, $cy, $rx, $ry, $ang, $angs, $angf, $pie, 2, false, ($fs == 0), true);
								$xmin = min($xmin, $x, $axmin);
								$ymin = min($ymin, $y, $aymin);
								$xmax = max($xmax, $x, $axmax);
								$ymax = max($ymax, $y, $aymax);
							}
							if ($relcoord) {
								$xoffset = $x;
								$yoffset = $y;
							}
						}
					}
					break;
				}
				case 'Z': {
					$this->_out('h');
					break;
				}
			}
			$firstcmd = false;
		} // end foreach
		if (!empty($op)) {
			$this->_out($op);
		}
		return array($xmin, $ymin, ($xmax - $xmin), ($ymax - $ymin));
	}

	/**
	 * Return the tag name without the namespace
	 * @param $name (string) Tag name
	 * @protected
	 */
	protected function removeTagNamespace($name) {
		if(strpos($name, ':') !== false) {
			$parts = explode(':', $name);
			return $parts[(sizeof($parts) - 1)];
		}
		return $name;
	}

	/**
	 * Sets the opening SVG element handler function for the XML parser. (*** TO BE COMPLETED ***)
	 * @param $parser (resource) The first parameter, parser, is a reference to the XML parser calling the handler.
	 * @param $name (string) The second parameter, name, contains the name of the element for which this handler is called. If case-folding is in effect for this parser, the element name will be in uppercase letters.
	 * @param $attribs (array) The third parameter, attribs, contains an associative array with the element's attributes (if any). The keys of this array are the attribute names, the values are the attribute values. Attribute names are case-folded on the same criteria as element names. Attribute values are not case-folded. The original order of the attributes can be retrieved by walking through attribs the normal way, using each(). The first key in the array was the first attribute, and so on.
	 * @param $ctm (array) tranformation matrix for clipping mode (starting transformation matrix).
	 * @author Nicola Asuni
	 * @since 5.0.000 (2010-05-02)
	 * @protected
	 */
	protected function startSVGElementHandler($parser, $name, $attribs, $ctm=array()) {
		$name = $this->removeTagNamespace($name);
		// check if we are in clip mode
		if ($this->svgclipmode) {
			$this->svgclippaths[$this->svgclipid][] = array('name' => $name, 'attribs' => $attribs, 'tm' => $this->svgcliptm[$this->svgclipid]);
			return;
		}
		if ($this->svgdefsmode AND !in_array($name, array('clipPath', 'linearGradient', 'radialGradient', 'stop'))) {
			if (isset($attribs['id'])) {
				$attribs['child_elements'] = array();
				$this->svgdefs[$attribs['id']] = array('name' => $name, 'attribs' => $attribs);
				return;
			}
			if (end($this->svgdefs) !== FALSE) {
				$last_svgdefs_id = key($this->svgdefs);
				if (isset($this->svgdefs[$last_svgdefs_id]['attribs']['child_elements'])) {
					$attribs['id'] = 'DF_'.(count($this->svgdefs[$last_svgdefs_id]['attribs']['child_elements']) + 1);
					$this->svgdefs[$last_svgdefs_id]['attribs']['child_elements'][$attribs['id']] = array('name' => $name, 'attribs' => $attribs);
					return;
				}
			}
			return;
		}
		$clipping = false;
		if ($parser == 'clip-path') {
			// set clipping mode
			$clipping = true;
		}
		// get styling properties
		$prev_svgstyle = $this->svgstyles[max(0,(count($this->svgstyles) - 1))]; // previous style
		$svgstyle = $this->svgstyles[0]; // set default style
		if ($clipping AND !isset($attribs['fill']) AND (!isset($attribs['style']) OR (!preg_match('/[;\"\s]{1}fill[\s]*:[\s]*([^;\"]*)/si', $attribs['style'], $attrval)))) {
			// default fill attribute for clipping
			$attribs['fill'] = 'none';
		}
		if (isset($attribs['style']) AND !TCPDF_STATIC::empty_string($attribs['style']) AND ($attribs['style'][0] != ';')) {
			// fix style for regular expression
			$attribs['style'] = ';'.$attribs['style'];
		}
		foreach ($prev_svgstyle as $key => $val) {
			if (in_array($key, TCPDF_IMAGES::$svginheritprop)) {
				// inherit previous value
				$svgstyle[$key] = $val;
			}
			if (isset($attribs[$key]) AND !TCPDF_STATIC::empty_string($attribs[$key])) {
				// specific attribute settings
				if ($attribs[$key] == 'inherit') {
					$svgstyle[$key] = $val;
				} else {
					$svgstyle[$key] = $attribs[$key];
				}
			} elseif (isset($attribs['style']) AND !TCPDF_STATIC::empty_string($attribs['style'])) {
				// CSS style syntax
				$attrval = array();
				if (preg_match('/[;\"\s]{1}'.$key.'[\s]*:[\s]*([^;\"]*)/si', $attribs['style'], $attrval) AND isset($attrval[1])) {
					if ($attrval[1] == 'inherit') {
						$svgstyle[$key] = $val;
					} else {
						$svgstyle[$key] = $attrval[1];
					}
				}
			}
		}
		// transformation matrix
		if (!empty($ctm)) {
			$tm = $ctm;
		} else {
			$tm = array(1,0,0,1,0,0);
		}
		if (isset($attribs['transform']) AND !empty($attribs['transform'])) {
			$tm = TCPDF_STATIC::getTransformationMatrixProduct($tm, TCPDF_STATIC::getSVGTransformMatrix($attribs['transform']));
		}
		$svgstyle['transfmatrix'] = $tm;
		$invisible = false;
		if (($svgstyle['visibility'] == 'hidden') OR ($svgstyle['visibility'] == 'collapse') OR ($svgstyle['display'] == 'none')) {
			// the current graphics element is invisible (nothing is painted)
			$invisible = true;
		}
		// process tag
		switch($name) {
			case 'defs': {
				$this->svgdefsmode = true;
				break;
			}
			// clipPath
			case 'clipPath': {
				if ($invisible) {
					break;
				}
				$this->svgclipmode = true;
				if (!isset($attribs['id'])) {
					$attribs['id'] = 'CP_'.(count($this->svgcliptm) + 1);
				}
				$this->svgclipid = $attribs['id'];
				$this->svgclippaths[$this->svgclipid] = array();
				$this->svgcliptm[$this->svgclipid] = $tm;
				break;
			}
			case 'svg': {
				// start of SVG object
				if(++$this->svg_tag_depth <= 1) {
					break;
				}
				// inner SVG
				array_push($this->svgstyles, $svgstyle);
				$this->StartTransform();
				$svgX = (isset($attribs['x'])?$attribs['x']:0);
				$svgY = (isset($attribs['y'])?$attribs['y']:0);
				$svgW = (isset($attribs['width'])?$attribs['width']:0);
				$svgH = (isset($attribs['height'])?$attribs['height']:0);
				// set x, y position using transform matrix
				$tm = TCPDF_STATIC::getTransformationMatrixProduct($tm, array( 1, 0, 0, 1, $svgX, $svgY));
				$this->SVGTransform($tm);
				// set clipping for width and height
				$x = 0;
				$y = 0;
				$w = (isset($attribs['width'])?$this->getHTMLUnitToUnits($attribs['width'], 0, $this->svgunit, false):$this->w);
				$h = (isset($attribs['height'])?$this->getHTMLUnitToUnits($attribs['height'], 0, $this->svgunit, false):$this->h);
				// draw clipping rect
				$this->Rect($x, $y, $w, $h, 'CNZ', array(), array());
				// parse viewbox, calculate extra transformation matrix
				if (isset($attribs['viewBox'])) {
					$tmp = array();
					preg_match_all("/[0-9]+/", $attribs['viewBox'], $tmp);
					$tmp = $tmp[0];
					if (sizeof($tmp) == 4) {
						$vx = $tmp[0];
						$vy = $tmp[1];
						$vw = $tmp[2];
						$vh = $tmp[3];
						// get aspect ratio
						$tmp = array();
						$aspectX = 'xMid';
						$aspectY = 'YMid';
						$fit = 'meet';
						if (isset($attribs['preserveAspectRatio'])) {
							if($attribs['preserveAspectRatio'] == 'none') {
								$fit = 'none';
							} else {
								preg_match_all('/[a-zA-Z]+/', $attribs['preserveAspectRatio'], $tmp);
								$tmp = $tmp[0];
								if ((sizeof($tmp) == 2) AND (strlen($tmp[0]) == 8) AND (in_array($tmp[1], array('meet', 'slice', 'none')))) {
									$aspectX = substr($tmp[0], 0, 4);
									$aspectY = substr($tmp[0], 4, 4);
									$fit = $tmp[1];
								}
							}
						}
						$wr = ($svgW / $vw);
						$hr = ($svgH / $vh);
						$ax = $ay = 0;
						if ((($fit == 'meet') AND ($hr < $wr)) OR (($fit == 'slice') AND ($hr > $wr))) {
							if ($aspectX == 'xMax') {
								$ax = (($vw * ($wr / $hr)) - $vw);
							}
							if ($aspectX == 'xMid') {
								$ax = ((($vw * ($wr / $hr)) - $vw) / 2);
							}
							$wr = $hr;
						} elseif ((($fit == 'meet') AND ($hr > $wr)) OR (($fit == 'slice') AND ($hr < $wr))) {
							if ($aspectY == 'YMax') {
								$ay = (($vh * ($hr / $wr)) - $vh);
							}
							if ($aspectY == 'YMid') {
								$ay = ((($vh * ($hr / $wr)) - $vh) / 2);
							}
							$hr = $wr;
						}
						$newtm = array($wr, 0, 0, $hr, (($wr * ($ax - $vx)) - $svgX), (($hr * ($ay - $vy)) - $svgY));
						$tm = TCPDF_STATIC::getTransformationMatrixProduct($tm, $newtm);
						$this->SVGTransform($tm);
					}
				}
				$this->setSVGStyles($svgstyle, $prev_svgstyle);
				break;
			}
			case 'g': {
				// group together related graphics elements
				array_push($this->svgstyles, $svgstyle);
				$this->StartTransform();
				$x = (isset($attribs['x'])?$attribs['x']:0);
				$y = (isset($attribs['y'])?$attribs['y']:0);
				$w = 1;//(isset($attribs['width'])?$attribs['width']:1);
				$h = 1;//(isset($attribs['height'])?$attribs['height']:1);
				$tm = TCPDF_STATIC::getTransformationMatrixProduct($tm, array($w, 0, 0, $h, $x, $y));
				$this->SVGTransform($tm);
				$this->setSVGStyles($svgstyle, $prev_svgstyle);
				break;
			}
			case 'linearGradient': {
				if ($this->pdfa_mode) {
					break;
				}
				if (!isset($attribs['id'])) {
					$attribs['id'] = 'GR_'.(count($this->svggradients) + 1);
				}
				$this->svggradientid = $attribs['id'];
				$this->svggradients[$this->svggradientid] = array();
				$this->svggradients[$this->svggradientid]['type'] = 2;
				$this->svggradients[$this->svggradientid]['stops'] = array();
				if (isset($attribs['gradientUnits'])) {
					$this->svggradients[$this->svggradientid]['gradientUnits'] = $attribs['gradientUnits'];
				} else {
					$this->svggradients[$this->svggradientid]['gradientUnits'] = 'objectBoundingBox';
				}
				//$attribs['spreadMethod']
				if (((!isset($attribs['x1'])) AND (!isset($attribs['y1'])) AND (!isset($attribs['x2'])) AND (!isset($attribs['y2'])))
					OR ((isset($attribs['x1']) AND (substr($attribs['x1'], -1) == '%'))
						OR (isset($attribs['y1']) AND (substr($attribs['y1'], -1) == '%'))
						OR (isset($attribs['x2']) AND (substr($attribs['x2'], -1) == '%'))
						OR (isset($attribs['y2']) AND (substr($attribs['y2'], -1) == '%')))) {
					$this->svggradients[$this->svggradientid]['mode'] = 'percentage';
				} else {
					$this->svggradients[$this->svggradientid]['mode'] = 'measure';
				}
				$x1 = (isset($attribs['x1'])?$attribs['x1']:'0');
				$y1 = (isset($attribs['y1'])?$attribs['y1']:'0');
				$x2 = (isset($attribs['x2'])?$attribs['x2']:'100');
				$y2 = (isset($attribs['y2'])?$attribs['y2']:'0');
				if (isset($attribs['gradientTransform'])) {
					$this->svggradients[$this->svggradientid]['gradientTransform'] = TCPDF_STATIC::getSVGTransformMatrix($attribs['gradientTransform']);
				}
				$this->svggradients[$this->svggradientid]['coords'] = array($x1, $y1, $x2, $y2);
				if (isset($attribs['xlink:href']) AND !empty($attribs['xlink:href'])) {
					// gradient is defined on another place
					$this->svggradients[$this->svggradientid]['xref'] = substr($attribs['xlink:href'], 1);
				}
				break;
			}
			case 'radialGradient': {
				if ($this->pdfa_mode) {
					break;
				}
				if (!isset($attribs['id'])) {
					$attribs['id'] = 'GR_'.(count($this->svggradients) + 1);
				}
				$this->svggradientid = $attribs['id'];
				$this->svggradients[$this->svggradientid] = array();
				$this->svggradients[$this->svggradientid]['type'] = 3;
				$this->svggradients[$this->svggradientid]['stops'] = array();
				if (isset($attribs['gradientUnits'])) {
					$this->svggradients[$this->svggradientid]['gradientUnits'] = $attribs['gradientUnits'];
				} else {
					$this->svggradients[$this->svggradientid]['gradientUnits'] = 'objectBoundingBox';
				}
				//$attribs['spreadMethod']
				if (((!isset($attribs['cx'])) AND (!isset($attribs['cy'])))
					OR ((isset($attribs['cx']) AND (substr($attribs['cx'], -1) == '%'))
					OR (isset($attribs['cy']) AND (substr($attribs['cy'], -1) == '%')))) {
					$this->svggradients[$this->svggradientid]['mode'] = 'percentage';
				} elseif (isset($attribs['r']) AND is_numeric($attribs['r']) AND ($attribs['r']) <= 1) {
					$this->svggradients[$this->svggradientid]['mode'] = 'ratio';
				} else {
					$this->svggradients[$this->svggradientid]['mode'] = 'measure';
				}
				$cx = (isset($attribs['cx']) ? $attribs['cx'] : 0.5);
				$cy = (isset($attribs['cy']) ? $attribs['cy'] : 0.5);
				$fx = (isset($attribs['fx']) ? $attribs['fx'] : $cx);
				$fy = (isset($attribs['fy']) ? $attribs['fy'] : $cy);
				$r = (isset($attribs['r']) ? $attribs['r'] : 0.5);
				if (isset($attribs['gradientTransform'])) {
					$this->svggradients[$this->svggradientid]['gradientTransform'] = TCPDF_STATIC::getSVGTransformMatrix($attribs['gradientTransform']);
				}
				$this->svggradients[$this->svggradientid]['coords'] = array($cx, $cy, $fx, $fy, $r);
				if (isset($attribs['xlink:href']) AND !empty($attribs['xlink:href'])) {
					// gradient is defined on another place
					$this->svggradients[$this->svggradientid]['xref'] = substr($attribs['xlink:href'], 1);
				}
				break;
			}
			case 'stop': {
				// gradient stops
				if (substr($attribs['offset'], -1) == '%') {
					$offset = floatval(substr($attribs['offset'], -1)) / 100;
				} else {
					$offset = floatval($attribs['offset']);
					if ($offset > 1) {
						$offset /= 100;
					}
				}
				$stop_color = isset($svgstyle['stop-color'])?TCPDF_COLORS::convertHTMLColorToDec($svgstyle['stop-color'], $this->spot_colors):'black';
				$opacity = isset($svgstyle['stop-opacity'])?$svgstyle['stop-opacity']:1;
				$this->svggradients[$this->svggradientid]['stops'][] = array('offset' => $offset, 'color' => $stop_color, 'opacity' => $opacity);
				break;
			}
			// paths
			case 'path': {
				if ($invisible) {
					break;
				}
				if (isset($attribs['d'])) {
					$d = trim($attribs['d']);
					if (!empty($d)) {
						$x = (isset($attribs['x'])?$attribs['x']:0);
						$y = (isset($attribs['y'])?$attribs['y']:0);
						$w = (isset($attribs['width'])?$attribs['width']:1);
						$h = (isset($attribs['height'])?$attribs['height']:1);
						$tm = TCPDF_STATIC::getTransformationMatrixProduct($tm, array($w, 0, 0, $h, $x, $y));
						if ($clipping) {
							$this->SVGTransform($tm);
							$this->SVGPath($d, 'CNZ');
						} else {
							$this->StartTransform();
							$this->SVGTransform($tm);
							$obstyle = $this->setSVGStyles($svgstyle, $prev_svgstyle, $x, $y, $w, $h, 'SVGPath', array($d, 'CNZ'));
							if (!empty($obstyle)) {
								$this->SVGPath($d, $obstyle);
							}
							$this->StopTransform();
						}
					}
				}
				break;
			}
			// shapes
			case 'rect': {
				if ($invisible) {
					break;
				}
				$x = (isset($attribs['x'])?$this->getHTMLUnitToUnits($attribs['x'], 0, $this->svgunit, false):0);
				$y = (isset($attribs['y'])?$this->getHTMLUnitToUnits($attribs['y'], 0, $this->svgunit, false):0);
				$w = (isset($attribs['width'])?$this->getHTMLUnitToUnits($attribs['width'], 0, $this->svgunit, false):0);
				$h = (isset($attribs['height'])?$this->getHTMLUnitToUnits($attribs['height'], 0, $this->svgunit, false):0);
				$rx = (isset($attribs['rx'])?$this->getHTMLUnitToUnits($attribs['rx'], 0, $this->svgunit, false):0);
				$ry = (isset($attribs['ry'])?$this->getHTMLUnitToUnits($attribs['ry'], 0, $this->svgunit, false):$rx);
				if ($clipping) {
					$this->SVGTransform($tm);
					$this->RoundedRectXY($x, $y, $w, $h, $rx, $ry, '1111', 'CNZ', array(), array());
				} else {
					$this->StartTransform();
					$this->SVGTransform($tm);
					$obstyle = $this->setSVGStyles($svgstyle, $prev_svgstyle, $x, $y, $w, $h, 'RoundedRectXY', array($x, $y, $w, $h, $rx, $ry, '1111', 'CNZ'));
					if (!empty($obstyle)) {
						$this->RoundedRectXY($x, $y, $w, $h, $rx, $ry, '1111', $obstyle, array(), array());
					}
					$this->StopTransform();
				}
				break;
			}
			case 'circle': {
				if ($invisible) {
					break;
				}
				$r = (isset($attribs['r']) ? $this->getHTMLUnitToUnits($attribs['r'], 0, $this->svgunit, false) : 0);
				$cx = (isset($attribs['cx']) ? $this->getHTMLUnitToUnits($attribs['cx'], 0, $this->svgunit, false) : (isset($attribs['x']) ? $this->getHTMLUnitToUnits($attribs['x'], 0, $this->svgunit, false) : 0));
				$cy = (isset($attribs['cy']) ? $this->getHTMLUnitToUnits($attribs['cy'], 0, $this->svgunit, false) : (isset($attribs['y']) ? $this->getHTMLUnitToUnits($attribs['y'], 0, $this->svgunit, false) : 0));
				$x = ($cx - $r);
				$y = ($cy - $r);
				$w = (2 * $r);
				$h = $w;
				if ($clipping) {
					$this->SVGTransform($tm);
					$this->Circle($cx, $cy, $r, 0, 360, 'CNZ', array(), array(), 8);
				} else {
					$this->StartTransform();
					$this->SVGTransform($tm);
					$obstyle = $this->setSVGStyles($svgstyle, $prev_svgstyle, $x, $y, $w, $h, 'Circle', array($cx, $cy, $r, 0, 360, 'CNZ'));
					if (!empty($obstyle)) {
						$this->Circle($cx, $cy, $r, 0, 360, $obstyle, array(), array(), 8);
					}
					$this->StopTransform();
				}
				break;
			}
			case 'ellipse': {
				if ($invisible) {
					break;
				}
				$rx = (isset($attribs['rx']) ? $this->getHTMLUnitToUnits($attribs['rx'], 0, $this->svgunit, false) : 0);
				$ry = (isset($attribs['ry']) ? $this->getHTMLUnitToUnits($attribs['ry'], 0, $this->svgunit, false) : 0);
				$cx = (isset($attribs['cx']) ? $this->getHTMLUnitToUnits($attribs['cx'], 0, $this->svgunit, false) : (isset($attribs['x']) ? $this->getHTMLUnitToUnits($attribs['x'], 0, $this->svgunit, false) : 0));
				$cy = (isset($attribs['cy']) ? $this->getHTMLUnitToUnits($attribs['cy'], 0, $this->svgunit, false) : (isset($attribs['y']) ? $this->getHTMLUnitToUnits($attribs['y'], 0, $this->svgunit, false) : 0));
				$x = ($cx - $rx);
				$y = ($cy - $ry);
				$w = (2 * $rx);
				$h = (2 * $ry);
				if ($clipping) {
					$this->SVGTransform($tm);
					$this->Ellipse($cx, $cy, $rx, $ry, 0, 0, 360, 'CNZ', array(), array(), 8);
				} else {
					$this->StartTransform();
					$this->SVGTransform($tm);
					$obstyle = $this->setSVGStyles($svgstyle, $prev_svgstyle, $x, $y, $w, $h, 'Ellipse', array($cx, $cy, $rx, $ry, 0, 0, 360, 'CNZ'));
					if (!empty($obstyle)) {
						$this->Ellipse($cx, $cy, $rx, $ry, 0, 0, 360, $obstyle, array(), array(), 8);
					}
					$this->StopTransform();
				}
				break;
			}
			case 'line': {
				if ($invisible) {
					break;
				}
				$x1 = (isset($attribs['x1'])?$this->getHTMLUnitToUnits($attribs['x1'], 0, $this->svgunit, false):0);
				$y1 = (isset($attribs['y1'])?$this->getHTMLUnitToUnits($attribs['y1'], 0, $this->svgunit, false):0);
				$x2 = (isset($attribs['x2'])?$this->getHTMLUnitToUnits($attribs['x2'], 0, $this->svgunit, false):0);
				$y2 = (isset($attribs['y2'])?$this->getHTMLUnitToUnits($attribs['y2'], 0, $this->svgunit, false):0);
				$x = $x1;
				$y = $y1;
				$w = abs($x2 - $x1);
				$h = abs($y2 - $y1);
				if (!$clipping) {
					$this->StartTransform();
					$this->SVGTransform($tm);
					$obstyle = $this->setSVGStyles($svgstyle, $prev_svgstyle, $x, $y, $w, $h, 'Line', array($x1, $y1, $x2, $y2));
					$this->Line($x1, $y1, $x2, $y2);
					$this->StopTransform();
				}
				break;
			}
			case 'polyline':
			case 'polygon': {
				if ($invisible) {
					break;
				}
				$points = (isset($attribs['points'])?$attribs['points']:'0 0');
				$points = trim($points);
				// note that point may use a complex syntax not covered here
				$points = preg_split('/[\,\s]+/si', $points);
				if (count($points) < 4) {
					break;
				}
				$p = array();
				$xmin = 2147483647;
				$xmax = 0;
				$ymin = 2147483647;
				$ymax = 0;
				foreach ($points as $key => $val) {
					$p[$key] = $this->getHTMLUnitToUnits($val, 0, $this->svgunit, false);
					if (($key % 2) == 0) {
						// X coordinate
						$xmin = min($xmin, $p[$key]);
						$xmax = max($xmax, $p[$key]);
					} else {
						// Y coordinate
						$ymin = min($ymin, $p[$key]);
						$ymax = max($ymax, $p[$key]);
					}
				}
				$x = $xmin;
				$y = $ymin;
				$w = ($xmax - $xmin);
				$h = ($ymax - $ymin);
				if ($name == 'polyline') {
					$this->StartTransform();
					$this->SVGTransform($tm);
					$obstyle = $this->setSVGStyles($svgstyle, $prev_svgstyle, $x, $y, $w, $h, 'PolyLine', array($p, 'CNZ'));
					if (!empty($obstyle)) {
						$this->PolyLine($p, $obstyle, array(), array());
					}
					$this->StopTransform();
				} else { // polygon
					if ($clipping) {
						$this->SVGTransform($tm);
						$this->Polygon($p, 'CNZ', array(), array(), true);
					} else {
						$this->StartTransform();
						$this->SVGTransform($tm);
						$obstyle = $this->setSVGStyles($svgstyle, $prev_svgstyle, $x, $y, $w, $h, 'Polygon', array($p, 'CNZ'));
						if (!empty($obstyle)) {
							$this->Polygon($p, $obstyle, array(), array(), true);
						}
						$this->StopTransform();
					}
				}
				break;
			}
			// image
			case 'image': {
				if ($invisible) {
					break;
				}
				if (!isset($attribs['xlink:href']) OR empty($attribs['xlink:href'])) {
					break;
				}
				$x = (isset($attribs['x'])?$this->getHTMLUnitToUnits($attribs['x'], 0, $this->svgunit, false):0);
				$y = (isset($attribs['y'])?$this->getHTMLUnitToUnits($attribs['y'], 0, $this->svgunit, false):0);
				$w = (isset($attribs['width'])?$this->getHTMLUnitToUnits($attribs['width'], 0, $this->svgunit, false):0);
				$h = (isset($attribs['height'])?$this->getHTMLUnitToUnits($attribs['height'], 0, $this->svgunit, false):0);
				$img = $attribs['xlink:href'];
				if (!$clipping) {
					$this->StartTransform();
					$this->SVGTransform($tm);
					$obstyle = $this->setSVGStyles($svgstyle, $prev_svgstyle, $x, $y, $w, $h);
					if (preg_match('/^data:image\/[^;]+;base64,/', $img, $m) > 0) {
						// embedded image encoded as base64
						$img = '@'.base64_decode(substr($img, strlen($m[0])));
					} else {
						// fix image path
						if (!TCPDF_STATIC::empty_string($this->svgdir) AND (($img[0] == '.') OR (basename($img) == $img))) {
							// replace relative path with full server path
							$img = $this->svgdir.'/'.$img;
						}
						if (($img[0] == '/') AND !empty($_SERVER['DOCUMENT_ROOT']) AND ($_SERVER['DOCUMENT_ROOT'] != '/')) {
							$findroot = strpos($img, $_SERVER['DOCUMENT_ROOT']);
							if (($findroot === false) OR ($findroot > 1)) {
								if (substr($_SERVER['DOCUMENT_ROOT'], -1) == '/') {
									$img = substr($_SERVER['DOCUMENT_ROOT'], 0, -1).$img;
								} else {
									$img = $_SERVER['DOCUMENT_ROOT'].$img;
								}
							}
						}
						$img = urldecode($img);
						$testscrtype = @parse_url($img);
						if (!isset($testscrtype['query']) OR empty($testscrtype['query'])) {
							// convert URL to server path
							$img = str_replace(K_PATH_URL, K_PATH_MAIN, $img);
						}
					}
					// get image type
					$imgtype = TCPDF_IMAGES::getImageFileType($img);
					if (($imgtype == 'eps') OR ($imgtype == 'ai')) {
						$this->ImageEps($img, $x, $y, $w, $h);
					} elseif ($imgtype == 'svg') {
						// store SVG vars
						$svggradients = $this->svggradients;
						$svggradientid = $this->svggradientid;
						$svgdefsmode = $this->svgdefsmode;
						$svgdefs = $this->svgdefs;
						$svgclipmode = $this->svgclipmode;
						$svgclippaths = $this->svgclippaths;
						$svgcliptm = $this->svgcliptm;
						$svgclipid = $this->svgclipid;
						$svgtext = $this->svgtext;
						$svgtextmode = $this->svgtextmode;
						$this->ImageSVG($img, $x, $y, $w, $h);
						// restore SVG vars
						$this->svggradients = $svggradients;
						$this->svggradientid = $svggradientid;
						$this->svgdefsmode = $svgdefsmode;
						$this->svgdefs = $svgdefs;
						$this->svgclipmode = $svgclipmode;
						$this->svgclippaths = $svgclippaths;
						$this->svgcliptm = $svgcliptm;
						$this->svgclipid = $svgclipid;
						$this->svgtext = $svgtext;
						$this->svgtextmode = $svgtextmode;
					} else {
						$this->Image($img, $x, $y, $w, $h);
					}
					$this->StopTransform();
				}
				break;
			}
			// text
			case 'text':
			case 'tspan': {
				if (isset($this->svgtextmode['text-anchor']) AND !empty($this->svgtext)) {
					// @TODO: unsupported feature
				}
				// only basic support - advanced features must be implemented
				$this->svgtextmode['invisible'] = $invisible;
				if ($invisible) {
					break;
				}
				array_push($this->svgstyles, $svgstyle);
				if (isset($attribs['x'])) {
					$x = $this->getHTMLUnitToUnits($attribs['x'], 0, $this->svgunit, false);
				} elseif ($name == 'tspan') {
					$x = $this->x;
				} else {
					$x = 0;
				}
				if (isset($attribs['dx'])) {
					$x += $this->getHTMLUnitToUnits($attribs['dx'], 0, $this->svgunit, false);
				}
				if (isset($attribs['y'])) {
					$y = $this->getHTMLUnitToUnits($attribs['y'], 0, $this->svgunit, false);
				} elseif ($name == 'tspan') {
					$y = $this->y;
				} else {
					$y = 0;
				}
				if (isset($attribs['dy'])) {
					$y += $this->getHTMLUnitToUnits($attribs['dy'], 0, $this->svgunit, false);
				}
				$svgstyle['text-color'] = $svgstyle['fill'];
				$this->svgtext = '';
				if (isset($svgstyle['text-anchor'])) {
					$this->svgtextmode['text-anchor'] = $svgstyle['text-anchor'];
				} else {
					$this->svgtextmode['text-anchor'] = 'start';
				}
				if (isset($svgstyle['direction'])) {
					if ($svgstyle['direction'] == 'rtl') {
						$this->svgtextmode['rtl'] = true;
					} else {
						$this->svgtextmode['rtl'] = false;
					}
				} else {
					$this->svgtextmode['rtl'] = false;
				}
				if (isset($svgstyle['stroke']) AND ($svgstyle['stroke'] != 'none') AND isset($svgstyle['stroke-width']) AND ($svgstyle['stroke-width'] > 0)) {
					$this->svgtextmode['stroke'] = $this->getHTMLUnitToUnits($svgstyle['stroke-width'], 0, $this->svgunit, false);
				} else {
					$this->svgtextmode['stroke'] = false;
				}
				$this->StartTransform();
				$this->SVGTransform($tm);
				$obstyle = $this->setSVGStyles($svgstyle, $prev_svgstyle, $x, $y, 1, 1);
				$this->x = $x;
				$this->y = $y;
				break;
			}
			// use
			case 'use': {
				if (isset($attribs['xlink:href']) AND !empty($attribs['xlink:href'])) {
					$svgdefid = substr($attribs['xlink:href'], 1);
					if (isset($this->svgdefs[$svgdefid])) {
						$use = $this->svgdefs[$svgdefid];
						if (isset($attribs['xlink:href'])) {
							unset($attribs['xlink:href']);
						}
						if (isset($attribs['id'])) {
							unset($attribs['id']);
						}
						if (isset($use['attribs']['x']) AND isset($attribs['x'])) {
							$attribs['x'] += $use['attribs']['x'];
						}
						if (isset($use['attribs']['y']) AND isset($attribs['y'])) {
							$attribs['y'] += $use['attribs']['y'];
						}
						if (empty($attribs['style'])) {
							$attribs['style'] = '';
						}
						if (!empty($use['attribs']['style'])) {
							// merge styles
							$attribs['style'] = str_replace(';;',';',';'.$use['attribs']['style'].$attribs['style']);
						}
						$attribs = array_merge($use['attribs'], $attribs);
						$this->startSVGElementHandler($parser, $use['name'], $attribs);
						return;
					}
				}
				break;
			}
			default: {
				break;
			}
		} // end of switch
		// process child elements
		if (!empty($attribs['child_elements'])) {
			$child_elements = $attribs['child_elements'];
			unset($attribs['child_elements']);
			foreach($child_elements as $child_element) {
				if (empty($child_element['attribs']['closing_tag'])) {
					$this->startSVGElementHandler('child-tag', $child_element['name'], $child_element['attribs']);
				} else {
					if (isset($child_element['attribs']['content'])) {
						$this->svgtext = $child_element['attribs']['content'];
					}
					$this->endSVGElementHandler('child-tag', $child_element['name']);
				}
			}
		}
	}

	/**
	 * Sets the closing SVG element handler function for the XML parser.
	 * @param $parser (resource) The first parameter, parser, is a reference to the XML parser calling the handler.
	 * @param $name (string) The second parameter, name, contains the name of the element for which this handler is called. If case-folding is in effect for this parser, the element name will be in uppercase letters.
	 * @author Nicola Asuni
	 * @since 5.0.000 (2010-05-02)
	 * @protected
	 */
	protected function endSVGElementHandler($parser, $name) {
		$name = $this->removeTagNamespace($name);
		if ($this->svgdefsmode AND !in_array($name, array('defs', 'clipPath', 'linearGradient', 'radialGradient', 'stop'))) {;
			if (end($this->svgdefs) !== FALSE) {
				$last_svgdefs_id = key($this->svgdefs);
				if (isset($this->svgdefs[$last_svgdefs_id]['attribs']['child_elements'])) {
					foreach($this->svgdefs[$last_svgdefs_id]['attribs']['child_elements'] as $child_element) {
						if (isset($child_element['attribs']['id']) AND ($child_element['name'] == $name)) {
							$this->svgdefs[$last_svgdefs_id]['attribs']['child_elements'][$child_element['attribs']['id'].'_CLOSE'] = array('name' => $name, 'attribs' => array('closing_tag' => TRUE, 'content' => $this->svgtext));
							return;
						}
					}
					if ($this->svgdefs[$last_svgdefs_id]['name'] == $name) {
						$this->svgdefs[$last_svgdefs_id]['attribs']['child_elements'][$last_svgdefs_id.'_CLOSE'] = array('name' => $name, 'attribs' => array('closing_tag' => TRUE, 'content' => $this->svgtext));
						return;
					}
				}
			}
			return;
		}
		switch($name) {
			case 'defs': {
				$this->svgdefsmode = false;
				break;
			}
			// clipPath
			case 'clipPath': {
				$this->svgclipmode = false;
				break;
			}
			case 'svg': {
				if (--$this->svg_tag_depth <= 0) {
					break;
				}
			}
			case 'g': {
				// ungroup: remove last style from array
				array_pop($this->svgstyles);
				$this->StopTransform();
				break;
			}
			case 'text':
			case 'tspan': {
				if ($this->svgtextmode['invisible']) {
					// This implementation must be fixed to following the rule:
					// If the 'visibility' property is set to hidden on a 'tspan', 'tref' or 'altGlyph' element, then the text is invisible but still takes up space in text layout calculations.
					break;
				}
				// print text
				$text = $this->svgtext;
				//$text = $this->stringTrim($text);
				$textlen = $this->GetStringWidth($text);
				if ($this->svgtextmode['text-anchor'] != 'start') {
					// check if string is RTL text
					if ($this->svgtextmode['text-anchor'] == 'end') {
						if ($this->svgtextmode['rtl']) {
							$this->x += $textlen;
						} else {
							$this->x -= $textlen;
						}
					} elseif ($this->svgtextmode['text-anchor'] == 'middle') {
						if ($this->svgtextmode['rtl']) {
							$this->x += ($textlen / 2);
						} else {
							$this->x -= ($textlen / 2);
						}
					}
				}
				$textrendermode = $this->textrendermode;
				$textstrokewidth = $this->textstrokewidth;
				$this->setTextRenderingMode($this->svgtextmode['stroke'], true, false);
				if ($name == 'text') {
					// store current coordinates
					$tmpx = $this->x;
					$tmpy = $this->y;
				}
				// print the text
				$this->Cell($textlen, 0, $text, 0, 0, '', false, '', 0, false, 'L', 'T');
				if ($name == 'text') {
					// restore coordinates
					$this->x = $tmpx;
					$this->y = $tmpy;
				}
				// restore previous rendering mode
				$this->textrendermode = $textrendermode;
				$this->textstrokewidth = $textstrokewidth;
				$this->svgtext = '';
				$this->StopTransform();
				if (!$this->svgdefsmode) {
					array_pop($this->svgstyles);
				}
				break;
			}
			default: {
				break;
			}
		}
	}

	/**
	 * Sets the character data handler function for the XML parser.
	 * @param $parser (resource) The first parameter, parser, is a reference to the XML parser calling the handler.
	 * @param $data (string) The second parameter, data, contains the character data as a string.
	 * @author Nicola Asuni
	 * @since 5.0.000 (2010-05-02)
	 * @protected
	 */
	protected function segSVGContentHandler($parser, $data) {
		$this->svgtext .= $data;
	}
}

