<?php

	// START GRAPHIC FUNCTIONS SECTION ---------------------
	// The following section is based on the code provided by David Hernandez Sanz

	/**
	 * Defines the line width. By default, the value equals 0.2 mm. The method can be called before the first page is created and the value is retained from page to page.
	 * @param $width (float) The width.
	 * @public
	 * @since 1.0
	 * @see Line(), Rect(), Cell(), MultiCell()
	 */
	public function SetLineWidth($width) {
		//Set line width
		$this->LineWidth = $width;
		$this->linestyleWidth = sprintf('%F w', ($width * $this->k));
		if ($this->state == 2) {
			$this->_out($this->linestyleWidth);
		}
	}

	/**
	 * Returns the current the line width.
	 * @return int Line width
	 * @public
	 * @since 2.1.000 (2008-01-07)
	 * @see Line(), SetLineWidth()
	 */
	public function GetLineWidth() {
		return $this->LineWidth;
	}

	/**
	 * Set line style.
	 * @param $style (array) Line style. Array with keys among the following:
	 * <ul>
	 *	 <li>width (float): Width of the line in user units.</li>
	 *	 <li>cap (string): Type of cap to put on the line. Possible values are:
	 * butt, round, square. The difference between "square" and "butt" is that
	 * "square" projects a flat end past the end of the line.</li>
	 *	 <li>join (string): Type of join. Possible values are: miter, round,
	 * bevel.</li>
	 *	 <li>dash (mixed): Dash pattern. Is 0 (without dash) or string with
	 * series of length values, which are the lengths of the on and off dashes.
	 * For example: "2" represents 2 on, 2 off, 2 on, 2 off, ...; "2,1" is 2 on,
	 * 1 off, 2 on, 1 off, ...</li>
	 *	 <li>phase (integer): Modifier on the dash pattern which is used to shift
	 * the point at which the pattern starts.</li>
	 *	 <li>color (array): Draw color. Format: array(GREY) or array(R,G,B) or array(C,M,Y,K) or array(C,M,Y,K,SpotColorName).</li>
	 * </ul>
	 * @param $ret (boolean) if true do not send the command.
	 * @return string the PDF command
	 * @public
	 * @since 2.1.000 (2008-01-08)
	 */
	public function SetLineStyle($style, $ret=false) {
		$s = ''; // string to be returned
		if (!is_array($style)) {
			return;
		}
		if (isset($style['width'])) {
			$this->LineWidth = $style['width'];
			$this->linestyleWidth = sprintf('%F w', ($style['width'] * $this->k));
			$s .= $this->linestyleWidth.' ';
		}
		if (isset($style['cap'])) {
			$ca = array('butt' => 0, 'round'=> 1, 'square' => 2);
			if (isset($ca[$style['cap']])) {
				$this->linestyleCap = $ca[$style['cap']].' J';
				$s .= $this->linestyleCap.' ';
			}
		}
		if (isset($style['join'])) {
			$ja = array('miter' => 0, 'round' => 1, 'bevel' => 2);
			if (isset($ja[$style['join']])) {
				$this->linestyleJoin = $ja[$style['join']].' j';
				$s .= $this->linestyleJoin.' ';
			}
		}
		if (isset($style['dash'])) {
			$dash_string = '';
			if ($style['dash']) {
				if (preg_match('/^.+,/', $style['dash']) > 0) {
					$tab = explode(',', $style['dash']);
				} else {
					$tab = array($style['dash']);
				}
				$dash_string = '';
				foreach ($tab as $i => $v) {
					if ($i) {
						$dash_string .= ' ';
					}
					$dash_string .= sprintf('%F', $v);
				}
			}
			if (!isset($style['phase']) OR !$style['dash']) {
				$style['phase'] = 0;
			}
			$this->linestyleDash = sprintf('[%s] %F d', $dash_string, $style['phase']);
			$s .= $this->linestyleDash.' ';
		}
		if (isset($style['color'])) {
			$s .= $this->SetDrawColorArray($style['color'], true).' ';
		}
		if (!$ret AND ($this->state == 2)) {
			$this->_out($s);
		}
		return $s;
	}

	/**
	 * Begin a new subpath by moving the current point to coordinates (x, y), omitting any connecting line segment.
	 * @param $x (float) Abscissa of point.
	 * @param $y (float) Ordinate of point.
	 * @protected
	 * @since 2.1.000 (2008-01-08)
	 */
	protected function _outPoint($x, $y) {
		if ($this->state == 2) {
			$this->_out(sprintf('%F %F m', ($x * $this->k), (($this->h - $y) * $this->k)));
		}
	}

	/**
	 * Append a straight line segment from the current point to the point (x, y).
	 * The new current point shall be (x, y).
	 * @param $x (float) Abscissa of end point.
	 * @param $y (float) Ordinate of end point.
	 * @protected
	 * @since 2.1.000 (2008-01-08)
	 */
	protected function _outLine($x, $y) {
		if ($this->state == 2) {
			$this->_out(sprintf('%F %F l', ($x * $this->k), (($this->h - $y) * $this->k)));
		}
	}

	/**
	 * Append a rectangle to the current path as a complete subpath, with lower-left corner (x, y) and dimensions widthand height in user space.
	 * @param $x (float) Abscissa of upper-left corner.
	 * @param $y (float) Ordinate of upper-left corner.
	 * @param $w (float) Width.
	 * @param $h (float) Height.
	 * @param $op (string) options
	 * @protected
	 * @since 2.1.000 (2008-01-08)
	 */
	protected function _outRect($x, $y, $w, $h, $op) {
		if ($this->state == 2) {
			$this->_out(sprintf('%F %F %F %F re %s', ($x * $this->k), (($this->h - $y) * $this->k), ($w * $this->k), (-$h * $this->k), $op));
		}
	}

	/**
	 * Append a cubic Bezier curve to the current path. The curve shall extend from the current point to the point (x3, y3), using (x1, y1) and (x2, y2) as the Bezier control points.
	 * The new current point shall be (x3, y3).
	 * @param $x1 (float) Abscissa of control point 1.
	 * @param $y1 (float) Ordinate of control point 1.
	 * @param $x2 (float) Abscissa of control point 2.
	 * @param $y2 (float) Ordinate of control point 2.
	 * @param $x3 (float) Abscissa of end point.
	 * @param $y3 (float) Ordinate of end point.
	 * @protected
	 * @since 2.1.000 (2008-01-08)
	 */
	protected function _outCurve($x1, $y1, $x2, $y2, $x3, $y3) {
		if ($this->state == 2) {
			$this->_out(sprintf('%F %F %F %F %F %F c', ($x1 * $this->k), (($this->h - $y1) * $this->k), ($x2 * $this->k), (($this->h - $y2) * $this->k), ($x3 * $this->k), (($this->h - $y3) * $this->k)));
		}
	}

	/**
	 * Append a cubic Bezier curve to the current path. The curve shall extend from the current point to the point (x3, y3), using the current point and (x2, y2) as the Bezier control points.
	 * The new current point shall be (x3, y3).
	 * @param $x2 (float) Abscissa of control point 2.
	 * @param $y2 (float) Ordinate of control point 2.
	 * @param $x3 (float) Abscissa of end point.
	 * @param $y3 (float) Ordinate of end point.
	 * @protected
	 * @since 4.9.019 (2010-04-26)
	 */
	protected function _outCurveV($x2, $y2, $x3, $y3) {
		if ($this->state == 2) {
			$this->_out(sprintf('%F %F %F %F v', ($x2 * $this->k), (($this->h - $y2) * $this->k), ($x3 * $this->k), (($this->h - $y3) * $this->k)));
		}
	}

	/**
	 * Append a cubic Bezier curve to the current path. The curve shall extend from the current point to the point (x3, y3), using (x1, y1) and (x3, y3) as the Bezier control points.
	 * The new current point shall be (x3, y3).
	 * @param $x1 (float) Abscissa of control point 1.
	 * @param $y1 (float) Ordinate of control point 1.
	 * @param $x3 (float) Abscissa of end point.
	 * @param $y3 (float) Ordinate of end point.
	 * @protected
	 * @since 2.1.000 (2008-01-08)
	 */
	protected function _outCurveY($x1, $y1, $x3, $y3) {
		if ($this->state == 2) {
			$this->_out(sprintf('%F %F %F %F y', ($x1 * $this->k), (($this->h - $y1) * $this->k), ($x3 * $this->k), (($this->h - $y3) * $this->k)));
		}
	}

	/**
	 * Draws a line between two points.
	 * @param $x1 (float) Abscissa of first point.
	 * @param $y1 (float) Ordinate of first point.
	 * @param $x2 (float) Abscissa of second point.
	 * @param $y2 (float) Ordinate of second point.
	 * @param $style (array) Line style. Array like for SetLineStyle(). Default value: default line style (empty array).
	 * @public
	 * @since 1.0
	 * @see SetLineWidth(), SetDrawColor(), SetLineStyle()
	 */
	public function Line($x1, $y1, $x2, $y2, $style=array()) {
		if ($this->state != 2) {
			return;
		}
		if (is_array($style)) {
			$this->SetLineStyle($style);
		}
		$this->_outPoint($x1, $y1);
		$this->_outLine($x2, $y2);
		$this->_out('S');
	}

	/**
	 * Draws a rectangle.
	 * @param $x (float) Abscissa of upper-left corner.
	 * @param $y (float) Ordinate of upper-left corner.
	 * @param $w (float) Width.
	 * @param $h (float) Height.
	 * @param $style (string) Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param $border_style (array) Border style of rectangle. Array with keys among the following:
	 * <ul>
	 *	 <li>all: Line style of all borders. Array like for SetLineStyle().</li>
	 *	 <li>L, T, R, B or combinations: Line style of left, top, right or bottom border. Array like for SetLineStyle().</li>
	 * </ul>
	 * If a key is not present or is null, the correspondent border is not drawn. Default value: default line style (empty array).
	 * @param $fill_color (array) Fill color. Format: array(GREY) or array(R,G,B) or array(C,M,Y,K) or array(C,M,Y,K,SpotColorName). Default value: default color (empty array).
	 * @public
	 * @since 1.0
	 * @see SetLineStyle()
	 */
	public function Rect($x, $y, $w, $h, $style='', $border_style=array(), $fill_color=array()) {
		if ($this->state != 2) {
			return;
		}
		if (empty($style)) {
			$style = 'S';
		}
		if (!(strpos($style, 'F') === false) AND !empty($fill_color)) {
			// set background color
			$this->SetFillColorArray($fill_color);
		}
		if (!empty($border_style)) {
			if (isset($border_style['all']) AND !empty($border_style['all'])) {
				//set global style for border
				$this->SetLineStyle($border_style['all']);
				$border_style = array();
			} else {
				// remove stroke operator from style
				$opnostroke = array('S' => '', 'D' => '', 's' => '', 'd' => '', 'B' => 'F', 'FD' => 'F', 'DF' => 'F', 'B*' => 'F*', 'F*D' => 'F*', 'DF*' => 'F*', 'b' => 'f', 'fd' => 'f', 'df' => 'f', 'b*' => 'f*', 'f*d' => 'f*', 'df*' => 'f*' );
				if (isset($opnostroke[$style])) {
					$style = $opnostroke[$style];
				}
			}
		}
		if (!empty($style)) {
			$op = TCPDF_STATIC::getPathPaintOperator($style);
			$this->_outRect($x, $y, $w, $h, $op);
		}
		if (!empty($border_style)) {
			$border_style2 = array();
			foreach ($border_style as $line => $value) {
				$length = strlen($line);
				for ($i = 0; $i < $length; ++$i) {
					$border_style2[$line[$i]] = $value;
				}
			}
			$border_style = $border_style2;
			if (isset($border_style['L']) AND $border_style['L']) {
				$this->Line($x, $y, $x, $y + $h, $border_style['L']);
			}
			if (isset($border_style['T']) AND $border_style['T']) {
				$this->Line($x, $y, $x + $w, $y, $border_style['T']);
			}
			if (isset($border_style['R']) AND $border_style['R']) {
				$this->Line($x + $w, $y, $x + $w, $y + $h, $border_style['R']);
			}
			if (isset($border_style['B']) AND $border_style['B']) {
				$this->Line($x, $y + $h, $x + $w, $y + $h, $border_style['B']);
			}
		}
	}

	/**
	 * Draws a Bezier curve.
	 * The Bezier curve is a tangent to the line between the control points at
	 * either end of the curve.
	 * @param $x0 (float) Abscissa of start point.
	 * @param $y0 (float) Ordinate of start point.
	 * @param $x1 (float) Abscissa of control point 1.
	 * @param $y1 (float) Ordinate of control point 1.
	 * @param $x2 (float) Abscissa of control point 2.
	 * @param $y2 (float) Ordinate of control point 2.
	 * @param $x3 (float) Abscissa of end point.
	 * @param $y3 (float) Ordinate of end point.
	 * @param $style (string) Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param $line_style (array) Line style of curve. Array like for SetLineStyle(). Default value: default line style (empty array).
	 * @param $fill_color (array) Fill color. Format: array(GREY) or array(R,G,B) or array(C,M,Y,K) or array(C,M,Y,K,SpotColorName). Default value: default color (empty array).
	 * @public
	 * @see SetLineStyle()
	 * @since 2.1.000 (2008-01-08)
	 */
	public function Curve($x0, $y0, $x1, $y1, $x2, $y2, $x3, $y3, $style='', $line_style=array(), $fill_color=array()) {
		if ($this->state != 2) {
			return;
		}
		if (!(false === strpos($style, 'F')) AND isset($fill_color)) {
			$this->SetFillColorArray($fill_color);
		}
		$op = TCPDF_STATIC::getPathPaintOperator($style);
		if ($line_style) {
			$this->SetLineStyle($line_style);
		}
		$this->_outPoint($x0, $y0);
		$this->_outCurve($x1, $y1, $x2, $y2, $x3, $y3);
		$this->_out($op);
	}

	/**
	 * Draws a poly-Bezier curve.
	 * Each Bezier curve segment is a tangent to the line between the control points at
	 * either end of the curve.
	 * @param $x0 (float) Abscissa of start point.
	 * @param $y0 (float) Ordinate of start point.
	 * @param $segments (float) An array of bezier descriptions. Format: array(x1, y1, x2, y2, x3, y3).
	 * @param $style (string) Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param $line_style (array) Line style of curve. Array like for SetLineStyle(). Default value: default line style (empty array).
	 * @param $fill_color (array) Fill color. Format: array(GREY) or array(R,G,B) or array(C,M,Y,K) or array(C,M,Y,K,SpotColorName). Default value: default color (empty array).
	 * @public
	 * @see SetLineStyle()
	 * @since 3.0008 (2008-05-12)
	 */
	public function Polycurve($x0, $y0, $segments, $style='', $line_style=array(), $fill_color=array()) {
		if ($this->state != 2) {
			return;
		}
		if (!(false === strpos($style, 'F')) AND isset($fill_color)) {
			$this->SetFillColorArray($fill_color);
		}
		$op = TCPDF_STATIC::getPathPaintOperator($style);
		if ($op == 'f') {
			$line_style = array();
		}
		if ($line_style) {
			$this->SetLineStyle($line_style);
		}
		$this->_outPoint($x0, $y0);
		foreach ($segments as $segment) {
			list($x1, $y1, $x2, $y2, $x3, $y3) = $segment;
			$this->_outCurve($x1, $y1, $x2, $y2, $x3, $y3);
		}
		$this->_out($op);
	}

	/**
	 * Draws an ellipse.
	 * An ellipse is formed from n Bezier curves.
	 * @param $x0 (float) Abscissa of center point.
	 * @param $y0 (float) Ordinate of center point.
	 * @param $rx (float) Horizontal radius.
	 * @param $ry (float) Vertical radius (if ry = 0 then is a circle, see Circle()). Default value: 0.
	 * @param $angle: (float) Angle oriented (anti-clockwise). Default value: 0.
	 * @param $astart: (float) Angle start of draw line. Default value: 0.
	 * @param $afinish: (float) Angle finish of draw line. Default value: 360.
	 * @param $style (string) Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param $line_style (array) Line style of ellipse. Array like for SetLineStyle(). Default value: default line style (empty array).
	 * @param $fill_color (array) Fill color. Format: array(GREY) or array(R,G,B) or array(C,M,Y,K) or array(C,M,Y,K,SpotColorName). Default value: default color (empty array).
	 * @param $nc (integer) Number of curves used to draw a 90 degrees portion of ellipse.
	 * @author Nicola Asuni
	 * @public
	 * @since 2.1.000 (2008-01-08)
	 */
	public function Ellipse($x0, $y0, $rx, $ry='', $angle=0, $astart=0, $afinish=360, $style='', $line_style=array(), $fill_color=array(), $nc=2) {
		if ($this->state != 2) {
			return;
		}
		if (TCPDF_STATIC::empty_string($ry) OR ($ry == 0)) {
			$ry = $rx;
		}
		if (!(false === strpos($style, 'F')) AND isset($fill_color)) {
			$this->SetFillColorArray($fill_color);
		}
		$op = TCPDF_STATIC::getPathPaintOperator($style);
		if ($op == 'f') {
			$line_style = array();
		}
		if ($line_style) {
			$this->SetLineStyle($line_style);
		}
		$this->_outellipticalarc($x0, $y0, $rx, $ry, $angle, $astart, $afinish, false, $nc, true, true, false);
		$this->_out($op);
	}

	/**
	 * Append an elliptical arc to the current path.
	 * An ellipse is formed from n Bezier curves.
	 * @param $xc (float) Abscissa of center point.
	 * @param $yc (float) Ordinate of center point.
	 * @param $rx (float) Horizontal radius.
	 * @param $ry (float) Vertical radius (if ry = 0 then is a circle, see Circle()). Default value: 0.
	 * @param $xang: (float) Angle between the X-axis and the major axis of the ellipse. Default value: 0.
	 * @param $angs: (float) Angle start of draw line. Default value: 0.
	 * @param $angf: (float) Angle finish of draw line. Default value: 360.
	 * @param $pie (boolean) if true do not mark the border point (used to draw pie sectors).
	 * @param $nc (integer) Number of curves used to draw a 90 degrees portion of ellipse.
	 * @param $startpoint (boolean) if true output a starting point.
	 * @param $ccw (boolean) if true draws in counter-clockwise.
	 * @param $svg (boolean) if true the angles are in svg mode (already calculated).
	 * @return array bounding box coordinates (x min, y min, x max, y max)
	 * @author Nicola Asuni
	 * @protected
	 * @since 4.9.019 (2010-04-26)
	 */
	protected function _outellipticalarc($xc, $yc, $rx, $ry, $xang=0, $angs=0, $angf=360, $pie=false, $nc=2, $startpoint=true, $ccw=true, $svg=false) {
		if (($rx <= 0) OR ($ry < 0)) {
			return;
		}
		$k = $this->k;
		if ($nc < 2) {
			$nc = 2;
		}
		$xmin = 2147483647;
		$ymin = 2147483647;
		$xmax = 0;
		$ymax = 0;
		if ($pie) {
			// center of the arc
			$this->_outPoint($xc, $yc);
		}
		$xang = deg2rad((float) $xang);
		$angs = deg2rad((float) $angs);
		$angf = deg2rad((float) $angf);
		if ($svg) {
			$as = $angs;
			$af = $angf;
		} else {
			$as = atan2((sin($angs) / $ry), (cos($angs) / $rx));
			$af = atan2((sin($angf) / $ry), (cos($angf) / $rx));
		}
		if ($as < 0) {
			$as += (2 * M_PI);
		}
		if ($af < 0) {
			$af += (2 * M_PI);
		}
		if ($ccw AND ($as > $af)) {
			// reverse rotation
			$as -= (2 * M_PI);
		} elseif (!$ccw AND ($as < $af)) {
			// reverse rotation
			$af -= (2 * M_PI);
		}
		$total_angle = ($af - $as);
		if ($nc < 2) {
			$nc = 2;
		}
		// total arcs to draw
		$nc *= (2 * abs($total_angle) / M_PI);
		$nc = round($nc) + 1;
		// angle of each arc
		$arcang = ($total_angle / $nc);
		// center point in PDF coordinates
		$x0 = $xc;
		$y0 = ($this->h - $yc);
		// starting angle
		$ang = $as;
		$alpha = sin($arcang) * ((sqrt(4 + (3 * pow(tan(($arcang) / 2), 2))) - 1) / 3);
		$cos_xang = cos($xang);
		$sin_xang = sin($xang);
		$cos_ang = cos($ang);
		$sin_ang = sin($ang);
		// first arc point
		$px1 = $x0 + ($rx * $cos_xang * $cos_ang) - ($ry * $sin_xang * $sin_ang);
		$py1 = $y0 + ($rx * $sin_xang * $cos_ang) + ($ry * $cos_xang * $sin_ang);
		// first Bezier control point
		$qx1 = ($alpha * ((-$rx * $cos_xang * $sin_ang) - ($ry * $sin_xang * $cos_ang)));
		$qy1 = ($alpha * ((-$rx * $sin_xang * $sin_ang) + ($ry * $cos_xang * $cos_ang)));
		if ($pie) {
			// line from center to arc starting point
			$this->_outLine($px1, $this->h - $py1);
		} elseif ($startpoint) {
			// arc starting point
			$this->_outPoint($px1, $this->h - $py1);
		}
		// draw arcs
		for ($i = 1; $i <= $nc; ++$i) {
			// starting angle
			$ang = $as + ($i * $arcang);
			if ($i == $nc) {
				$ang = $af;
			}
			$cos_ang = cos($ang);
			$sin_ang = sin($ang);
			// second arc point
			$px2 = $x0 + ($rx * $cos_xang * $cos_ang) - ($ry * $sin_xang * $sin_ang);
			$py2 = $y0 + ($rx * $sin_xang * $cos_ang) + ($ry * $cos_xang * $sin_ang);
			// second Bezier control point
			$qx2 = ($alpha * ((-$rx * $cos_xang * $sin_ang) - ($ry * $sin_xang * $cos_ang)));
			$qy2 = ($alpha * ((-$rx * $sin_xang * $sin_ang) + ($ry * $cos_xang * $cos_ang)));
			// draw arc
			$cx1 = ($px1 + $qx1);
			$cy1 = ($this->h - ($py1 + $qy1));
			$cx2 = ($px2 - $qx2);
			$cy2 = ($this->h - ($py2 - $qy2));
			$cx3 = $px2;
			$cy3 = ($this->h - $py2);
			$this->_outCurve($cx1, $cy1, $cx2, $cy2, $cx3, $cy3);
			// get bounding box coordinates
			$xmin = min($xmin, $cx1, $cx2, $cx3);
			$ymin = min($ymin, $cy1, $cy2, $cy3);
			$xmax = max($xmax, $cx1, $cx2, $cx3);
			$ymax = max($ymax, $cy1, $cy2, $cy3);
			// move to next point
			$px1 = $px2;
			$py1 = $py2;
			$qx1 = $qx2;
			$qy1 = $qy2;
		}
		if ($pie) {
			$this->_outLine($xc, $yc);
			// get bounding box coordinates
			$xmin = min($xmin, $xc);
			$ymin = min($ymin, $yc);
			$xmax = max($xmax, $xc);
			$ymax = max($ymax, $yc);
		}
		return array($xmin, $ymin, $xmax, $ymax);
	}

	/**
	 * Draws a circle.
	 * A circle is formed from n Bezier curves.
	 * @param $x0 (float) Abscissa of center point.
	 * @param $y0 (float) Ordinate of center point.
	 * @param $r (float) Radius.
	 * @param $angstr: (float) Angle start of draw line. Default value: 0.
	 * @param $angend: (float) Angle finish of draw line. Default value: 360.
	 * @param $style (string) Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param $line_style (array) Line style of circle. Array like for SetLineStyle(). Default value: default line style (empty array).
	 * @param $fill_color (array) Fill color. Format: array(red, green, blue). Default value: default color (empty array).
	 * @param $nc (integer) Number of curves used to draw a 90 degrees portion of circle.
	 * @public
	 * @since 2.1.000 (2008-01-08)
	 */
	public function Circle($x0, $y0, $r, $angstr=0, $angend=360, $style='', $line_style=array(), $fill_color=array(), $nc=2) {
		$this->Ellipse($x0, $y0, $r, $r, 0, $angstr, $angend, $style, $line_style, $fill_color, $nc);
	}

	/**
	 * Draws a polygonal line
	 * @param $p (array) Points 0 to ($np - 1). Array with values (x0, y0, x1, y1,..., x(np-1), y(np - 1))
	 * @param $style (string) Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param $line_style (array) Line style of polygon. Array with keys among the following:
	 * <ul>
	 *	 <li>all: Line style of all lines. Array like for SetLineStyle().</li>
	 *	 <li>0 to ($np - 1): Line style of each line. Array like for SetLineStyle().</li>
	 * </ul>
	 * If a key is not present or is null, not draws the line. Default value is default line style (empty array).
	 * @param $fill_color (array) Fill color. Format: array(GREY) or array(R,G,B) or array(C,M,Y,K) or array(C,M,Y,K,SpotColorName). Default value: default color (empty array).
	 * @since 4.8.003 (2009-09-15)
	 * @public
	 */
	public function PolyLine($p, $style='', $line_style=array(), $fill_color=array()) {
		$this->Polygon($p, $style, $line_style, $fill_color, false);
	}

	/**
	 * Draws a polygon.
	 * @param $p (array) Points 0 to ($np - 1). Array with values (x0, y0, x1, y1,..., x(np-1), y(np - 1))
	 * @param $style (string) Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param $line_style (array) Line style of polygon. Array with keys among the following:
	 * <ul>
	 *	 <li>all: Line style of all lines. Array like for SetLineStyle().</li>
	 *	 <li>0 to ($np - 1): Line style of each line. Array like for SetLineStyle().</li>
	 * </ul>
	 * If a key is not present or is null, not draws the line. Default value is default line style (empty array).
	 * @param $fill_color (array) Fill color. Format: array(GREY) or array(R,G,B) or array(C,M,Y,K) or array(C,M,Y,K,SpotColorName). Default value: default color (empty array).
	 * @param $closed (boolean) if true the polygon is closes, otherwise will remain open
	 * @public
	 * @since 2.1.000 (2008-01-08)
	 */
	public function Polygon($p, $style='', $line_style=array(), $fill_color=array(), $closed=true) {
		if ($this->state != 2) {
			return;
		}
		$nc = count($p); // number of coordinates
		$np = $nc / 2; // number of points
		if ($closed) {
			// close polygon by adding the first 2 points at the end (one line)
			for ($i = 0; $i < 4; ++$i) {
				$p[$nc + $i] = $p[$i];
			}
			// copy style for the last added line
			if (isset($line_style[0])) {
				$line_style[$np] = $line_style[0];
			}
			$nc += 4;
		}
		if (!(false === strpos($style, 'F')) AND isset($fill_color)) {
			$this->SetFillColorArray($fill_color);
		}
		$op = TCPDF_STATIC::getPathPaintOperator($style);
		if ($op == 'f') {
			$line_style = array();
		}
		$draw = true;
		if ($line_style) {
			if (isset($line_style['all'])) {
				$this->SetLineStyle($line_style['all']);
			} else {
				$draw = false;
				if ($op == 'B') {
					// draw fill
					$op = 'f';
					$this->_outPoint($p[0], $p[1]);
					for ($i = 2; $i < $nc; $i = $i + 2) {
						$this->_outLine($p[$i], $p[$i + 1]);
					}
					$this->_out($op);
				}
				// draw outline
				$this->_outPoint($p[0], $p[1]);
				for ($i = 2; $i < $nc; $i = $i + 2) {
					$line_num = ($i / 2) - 1;
					if (isset($line_style[$line_num])) {
						if ($line_style[$line_num] != 0) {
							if (is_array($line_style[$line_num])) {
								$this->_out('S');
								$this->SetLineStyle($line_style[$line_num]);
								$this->_outPoint($p[$i - 2], $p[$i - 1]);
								$this->_outLine($p[$i], $p[$i + 1]);
								$this->_out('S');
								$this->_outPoint($p[$i], $p[$i + 1]);
							} else {
								$this->_outLine($p[$i], $p[$i + 1]);
							}
						}
					} else {
						$this->_outLine($p[$i], $p[$i + 1]);
					}
				}
				$this->_out($op);
			}
		}
		if ($draw) {
			$this->_outPoint($p[0], $p[1]);
			for ($i = 2; $i < $nc; $i = $i + 2) {
				$this->_outLine($p[$i], $p[$i + 1]);
			}
			$this->_out($op);
		}
	}

	/**
	 * Draws a regular polygon.
	 * @param $x0 (float) Abscissa of center point.
	 * @param $y0 (float) Ordinate of center point.
	 * @param $r: (float) Radius of inscribed circle.
	 * @param $ns (integer) Number of sides.
	 * @param $angle (float) Angle oriented (anti-clockwise). Default value: 0.
	 * @param $draw_circle (boolean) Draw inscribed circle or not. Default value: false.
	 * @param $style (string) Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param $line_style (array) Line style of polygon sides. Array with keys among the following:
	 * <ul>
	 *	 <li>all: Line style of all sides. Array like for SetLineStyle().</li>
	 *	 <li>0 to ($ns - 1): Line style of each side. Array like for SetLineStyle().</li>
	 * </ul>
	 * If a key is not present or is null, not draws the side. Default value is default line style (empty array).
	 * @param $fill_color (array) Fill color. Format: array(red, green, blue). Default value: default color (empty array).
	 * @param $circle_style (string) Style of rendering of inscribed circle (if draws). Possible values are:
	 * <ul>
	 *	 <li>D or empty string: Draw (default).</li>
	 *	 <li>F: Fill.</li>
	 *	 <li>DF or FD: Draw and fill.</li>
	 *	 <li>CNZ: Clipping mode (using the even-odd rule to determine which regions lie inside the clipping path).</li>
	 *	 <li>CEO: Clipping mode (using the nonzero winding number rule to determine which regions lie inside the clipping path).</li>
	 * </ul>
	 * @param $circle_outLine_style (array) Line style of inscribed circle (if draws). Array like for SetLineStyle(). Default value: default line style (empty array).
	 * @param $circle_fill_color (array) Fill color of inscribed circle (if draws). Format: array(red, green, blue). Default value: default color (empty array).
	 * @public
	 * @since 2.1.000 (2008-01-08)
	 */
	public function RegularPolygon($x0, $y0, $r, $ns, $angle=0, $draw_circle=false, $style='', $line_style=array(), $fill_color=array(), $circle_style='', $circle_outLine_style=array(), $circle_fill_color=array()) {
		if (3 > $ns) {
			$ns = 3;
		}
		if ($draw_circle) {
			$this->Circle($x0, $y0, $r, 0, 360, $circle_style, $circle_outLine_style, $circle_fill_color);
		}
		$p = array();
		for ($i = 0; $i < $ns; ++$i) {
			$a = $angle + ($i * 360 / $ns);
			$a_rad = deg2rad((float) $a);
			$p[] = $x0 + ($r * sin($a_rad));
			$p[] = $y0 + ($r * cos($a_rad));
		}
		$this->Polygon($p, $style, $line_style, $fill_color);
	}

	/**
	 * Draws a star polygon
	 * @param $x0 (float) Abscissa of center point.
	 * @param $y0 (float) Ordinate of center point.
	 * @param $r (float) Radius of inscribed circle.
	 * @param $nv (integer) Number of vertices.
	 * @param $ng (integer) Number of gap (if ($ng % $nv = 1) then is a regular polygon).
	 * @param $angle: (float) Angle oriented (anti-clockwise). Default value: 0.
	 * @param $draw_circle: (boolean) Draw inscribed circle or not. Default value is false.
	 * @param $style (string) Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param $line_style (array) Line style of polygon sides. Array with keys among the following:
	 * <ul>
	 *	 <li>all: Line style of all sides. Array like for
	 * SetLineStyle().</li>
	 *	 <li>0 to (n - 1): Line style of each side. Array like for SetLineStyle().</li>
	 * </ul>
	 * If a key is not present or is null, not draws the side. Default value is default line style (empty array).
	 * @param $fill_color (array) Fill color. Format: array(red, green, blue). Default value: default color (empty array).
	 * @param $circle_style (string) Style of rendering of inscribed circle (if draws). Possible values are:
	 * <ul>
	 *	 <li>D or empty string: Draw (default).</li>
	 *	 <li>F: Fill.</li>
	 *	 <li>DF or FD: Draw and fill.</li>
	 *	 <li>CNZ: Clipping mode (using the even-odd rule to determine which regions lie inside the clipping path).</li>
	 *	 <li>CEO: Clipping mode (using the nonzero winding number rule to determine which regions lie inside the clipping path).</li>
	 * </ul>
	 * @param $circle_outLine_style (array) Line style of inscribed circle (if draws). Array like for SetLineStyle(). Default value: default line style (empty array).
	 * @param $circle_fill_color (array) Fill color of inscribed circle (if draws). Format: array(red, green, blue). Default value: default color (empty array).
	 * @public
	 * @since 2.1.000 (2008-01-08)
	 */
	public function StarPolygon($x0, $y0, $r, $nv, $ng, $angle=0, $draw_circle=false, $style='', $line_style=array(), $fill_color=array(), $circle_style='', $circle_outLine_style=array(), $circle_fill_color=array()) {
		if ($nv < 2) {
			$nv = 2;
		}
		if ($draw_circle) {
			$this->Circle($x0, $y0, $r, 0, 360, $circle_style, $circle_outLine_style, $circle_fill_color);
		}
		$p2 = array();
		$visited = array();
		for ($i = 0; $i < $nv; ++$i) {
			$a = $angle + ($i * 360 / $nv);
			$a_rad = deg2rad((float) $a);
			$p2[] = $x0 + ($r * sin($a_rad));
			$p2[] = $y0 + ($r * cos($a_rad));
			$visited[] = false;
		}
		$p = array();
		$i = 0;
		do {
			$p[] = $p2[$i * 2];
			$p[] = $p2[($i * 2) + 1];
			$visited[$i] = true;
			$i += $ng;
			$i %= $nv;
		} while (!$visited[$i]);
		$this->Polygon($p, $style, $line_style, $fill_color);
	}

	/**
	 * Draws a rounded rectangle.
	 * @param $x (float) Abscissa of upper-left corner.
	 * @param $y (float) Ordinate of upper-left corner.
	 * @param $w (float) Width.
	 * @param $h (float) Height.
	 * @param $r (float) the radius of the circle used to round off the corners of the rectangle.
	 * @param $round_corner (string) Draws rounded corner or not. String with a 0 (not rounded i-corner) or 1 (rounded i-corner) in i-position. Positions are, in order and begin to 0: top right, bottom right, bottom left and top left. Default value: all rounded corner ("1111").
	 * @param $style (string) Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param $border_style (array) Border style of rectangle. Array like for SetLineStyle(). Default value: default line style (empty array).
	 * @param $fill_color (array) Fill color. Format: array(GREY) or array(R,G,B) or array(C,M,Y,K) or array(C,M,Y,K,SpotColorName). Default value: default color (empty array).
	 * @public
	 * @since 2.1.000 (2008-01-08)
	 */
	public function RoundedRect($x, $y, $w, $h, $r, $round_corner='1111', $style='', $border_style=array(), $fill_color=array()) {
		$this->RoundedRectXY($x, $y, $w, $h, $r, $r, $round_corner, $style, $border_style, $fill_color);
	}

	/**
	 * Draws a rounded rectangle.
	 * @param $x (float) Abscissa of upper-left corner.
	 * @param $y (float) Ordinate of upper-left corner.
	 * @param $w (float) Width.
	 * @param $h (float) Height.
	 * @param $rx (float) the x-axis radius of the ellipse used to round off the corners of the rectangle.
	 * @param $ry (float) the y-axis radius of the ellipse used to round off the corners of the rectangle.
	 * @param $round_corner (string) Draws rounded corner or not. String with a 0 (not rounded i-corner) or 1 (rounded i-corner) in i-position. Positions are, in order and begin to 0: top right, bottom right, bottom left and top left. Default value: all rounded corner ("1111").
	 * @param $style (string) Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param $border_style (array) Border style of rectangle. Array like for SetLineStyle(). Default value: default line style (empty array).
	 * @param $fill_color (array) Fill color. Format: array(GREY) or array(R,G,B) or array(C,M,Y,K) or array(C,M,Y,K,SpotColorName). Default value: default color (empty array).
	 * @public
	 * @since 4.9.019 (2010-04-22)
	 */
	public function RoundedRectXY($x, $y, $w, $h, $rx, $ry, $round_corner='1111', $style='', $border_style=array(), $fill_color=array()) {
		if ($this->state != 2) {
			return;
		}
		if (($round_corner == '0000') OR (($rx == $ry) AND ($rx == 0))) {
			// Not rounded
			$this->Rect($x, $y, $w, $h, $style, $border_style, $fill_color);
			return;
		}
		// Rounded
		if (!(false === strpos($style, 'F')) AND isset($fill_color)) {
			$this->SetFillColorArray($fill_color);
		}
		$op = TCPDF_STATIC::getPathPaintOperator($style);
		if ($op == 'f') {
			$border_style = array();
		}
		if ($border_style) {
			$this->SetLineStyle($border_style);
		}
		$MyArc = 4 / 3 * (sqrt(2) - 1);
		$this->_outPoint($x + $rx, $y);
		$xc = $x + $w - $rx;
		$yc = $y + $ry;
		$this->_outLine($xc, $y);
		if ($round_corner[0]) {
			$this->_outCurve($xc + ($rx * $MyArc), $yc - $ry, $xc + $rx, $yc - ($ry * $MyArc), $xc + $rx, $yc);
		} else {
			$this->_outLine($x + $w, $y);
		}
		$xc = $x + $w - $rx;
		$yc = $y + $h - $ry;
		$this->_outLine($x + $w, $yc);
		if ($round_corner[1]) {
			$this->_outCurve($xc + $rx, $yc + ($ry * $MyArc), $xc + ($rx * $MyArc), $yc + $ry, $xc, $yc + $ry);
		} else {
			$this->_outLine($x + $w, $y + $h);
		}
		$xc = $x + $rx;
		$yc = $y + $h - $ry;
		$this->_outLine($xc, $y + $h);
		if ($round_corner[2]) {
			$this->_outCurve($xc - ($rx * $MyArc), $yc + $ry, $xc - $rx, $yc + ($ry * $MyArc), $xc - $rx, $yc);
		} else {
			$this->_outLine($x, $y + $h);
		}
		$xc = $x + $rx;
		$yc = $y + $ry;
		$this->_outLine($x, $yc);
		if ($round_corner[3]) {
			$this->_outCurve($xc - $rx, $yc - ($ry * $MyArc), $xc - ($rx * $MyArc), $yc - $ry, $xc, $yc - $ry);
		} else {
			$this->_outLine($x, $y);
			$this->_outLine($x + $rx, $y);
		}
		$this->_out($op);
	}

	/**
	 * Draws a grahic arrow.
	 * @param $x0 (float) Abscissa of first point.
	 * @param $y0 (float) Ordinate of first point.
	 * @param $x1 (float) Abscissa of second point.
	 * @param $y1 (float) Ordinate of second point.
	 * @param $head_style (int) (0 = draw only arrowhead arms, 1 = draw closed arrowhead, but no fill, 2 = closed and filled arrowhead, 3 = filled arrowhead)
	 * @param $arm_size (float) length of arrowhead arms
	 * @param $arm_angle (int) angle between an arm and the shaft
	 * @author Piotr Galecki, Nicola Asuni, Andy Meier
	 * @since 4.6.018 (2009-07-10)
	 */
	public function Arrow($x0, $y0, $x1, $y1, $head_style=0, $arm_size=5, $arm_angle=15) {
		// getting arrow direction angle
		// 0 deg angle is when both arms go along X axis. angle grows clockwise.
		$dir_angle = atan2(($y0 - $y1), ($x0 - $x1));
		if ($dir_angle < 0) {
			$dir_angle += (2 * M_PI);
		}
		$arm_angle = deg2rad($arm_angle);
		$sx1 = $x1;
		$sy1 = $y1;
		if ($head_style > 0) {
			// calculate the stopping point for the arrow shaft
			$sx1 = $x1 + (($arm_size - $this->LineWidth) * cos($dir_angle));
			$sy1 = $y1 + (($arm_size - $this->LineWidth) * sin($dir_angle));
		}
		// main arrow line / shaft
		$this->Line($x0, $y0, $sx1, $sy1);
		// left arrowhead arm tip
		$x2L = $x1 + ($arm_size * cos($dir_angle + $arm_angle));
		$y2L = $y1 + ($arm_size * sin($dir_angle + $arm_angle));
		// right arrowhead arm tip
		$x2R = $x1 + ($arm_size * cos($dir_angle - $arm_angle));
		$y2R = $y1 + ($arm_size * sin($dir_angle - $arm_angle));
		$mode = 'D';
		$style = array();
		switch ($head_style) {
			case 0: {
				// draw only arrowhead arms
				$mode = 'D';
				$style = array(1, 1, 0);
				break;
			}
			case 1: {
				// draw closed arrowhead, but no fill
				$mode = 'D';
				break;
			}
			case 2: {
				// closed and filled arrowhead
				$mode = 'DF';
				break;
			}
			case 3: {
				// filled arrowhead
				$mode = 'F';
				break;
			}
		}
		$this->Polygon(array($x2L, $y2L, $x1, $y1, $x2R, $y2R), $mode, $style, array());
	}

	// END GRAPHIC FUNCTIONS SECTION -----------------------

