<?php

namespace Sztyup\Tcpdf\Traits;

trait Form
{

	/**
	 * Set default properties for form fields.
	 * @param $prop (array) javascript field properties. Possible values are described on official Javascript for Acrobat API reference.
	 * @public
	 * @author Nicola Asuni
	 * @since 4.8.000 (2009-09-06)
	 */
	public function setFormDefaultProp($prop=array()) {
		$this->default_form_prop = $prop;
	}

	/**
	 * Return the default properties for form fields.
	 * @return array $prop javascript field properties. Possible values are described on official Javascript for Acrobat API reference.
	 * @public
	 * @author Nicola Asuni
	 * @since 4.8.000 (2009-09-06)
	 */
	public function getFormDefaultProp() {
		return $this->default_form_prop;
	}

	/**
	 * Creates a text field
	 * @param $name (string) field name
	 * @param $w (float) Width of the rectangle
	 * @param $h (float) Height of the rectangle
	 * @param $prop (array) javascript field properties. Possible values are described on official Javascript for Acrobat API reference.
	 * @param $opt (array) annotation parameters. Possible values are described on official PDF32000_2008 reference.
	 * @param $x (float) Abscissa of the upper-left corner of the rectangle
	 * @param $y (float) Ordinate of the upper-left corner of the rectangle
	 * @param $js (boolean) if true put the field using JavaScript (requires Acrobat Writer to be rendered).
	 * @public
	 * @author Nicola Asuni
	 * @since 4.8.000 (2009-09-07)
	 */
	public function TextField($name, $w, $h, $prop=array(), $opt=array(), $x='', $y='', $js=false) {
		if ($x === '') {
			$x = $this->x;
		}
		if ($y === '') {
			$y = $this->y;
		}
		// check page for no-write regions and adapt page margins if necessary
		list($x, $y) = $this->checkPageRegions($h, $x, $y);
		if ($js) {
			$this->_addfield('text', $name, $x, $y, $w, $h, $prop);
			return;
		}
		// get default style
		$prop = array_merge($this->getFormDefaultProp(), $prop);
		// get annotation data
		$popt = TCPDF_STATIC::getAnnotOptFromJSProp($prop, $this->spot_colors, $this->rtl);
		// set default appearance stream
		$this->annotation_fonts[$this->CurrentFont['fontkey']] = $this->CurrentFont['i'];
		$fontstyle = sprintf('/F%d %F Tf %s', $this->CurrentFont['i'], $this->FontSizePt, $this->TextColor);
		$popt['da'] = $fontstyle;
		// build appearance stream
		$popt['ap'] = array();
		$popt['ap']['n'] = '/Tx BMC q '.$fontstyle.' ';
		$text = '';
		if (isset($prop['value']) AND !empty($prop['value'])) {
			$text = $prop['value'];
		} elseif (isset($opt['v']) AND !empty($opt['v'])) {
			$text = $opt['v'];
		}
		$tmpid = $this->startTemplate($w, $h, false);
		$align = '';
		if (isset($popt['q'])) {
			switch ($popt['q']) {
				case 0: {
					$align = 'L';
					break;
				}
				case 1: {
					$align = 'C';
					break;
				}
				case 2: {
					$align = 'R';
					break;
				}
				default: {
					$align = '';
					break;
				}
			}
		}
		$this->MultiCell($w, $h, $text, 0, $align, false, 0, 0, 0, true, 0, false, true, 0, 'T', false);
		$this->endTemplate();
		--$this->n;
		$popt['ap']['n'] .= $this->xobjects[$tmpid]['outdata'];
		unset($this->xobjects[$tmpid]);
		$popt['ap']['n'] .= 'Q EMC';
		// merge options
		$opt = array_merge($popt, $opt);
		// remove some conflicting options
		unset($opt['bs']);
		// set remaining annotation data
		$opt['Subtype'] = 'Widget';
		$opt['ft'] = 'Tx';
		$opt['t'] = $name;
		// Additional annotation's parameters (check _putannotsobj() method):
		//$opt['f']
		//$opt['as']
		//$opt['bs']
		//$opt['be']
		//$opt['c']
		//$opt['border']
		//$opt['h']
		//$opt['mk'];
		//$opt['mk']['r']
		//$opt['mk']['bc'];
		//$opt['mk']['bg'];
		unset($opt['mk']['ca']);
		unset($opt['mk']['rc']);
		unset($opt['mk']['ac']);
		unset($opt['mk']['i']);
		unset($opt['mk']['ri']);
		unset($opt['mk']['ix']);
		unset($opt['mk']['if']);
		//$opt['mk']['if']['sw'];
		//$opt['mk']['if']['s'];
		//$opt['mk']['if']['a'];
		//$opt['mk']['if']['fb'];
		unset($opt['mk']['tp']);
		//$opt['tu']
		//$opt['tm']
		//$opt['ff']
		//$opt['v']
		//$opt['dv']
		//$opt['a']
		//$opt['aa']
		//$opt['q']
		$this->Annotation($x, $y, $w, $h, $name, $opt, 0);
		if ($this->rtl) {
			$this->x -= $w;
		} else {
			$this->x += $w;
		}
	}

	/**
	 * Creates a RadioButton field.
	 * @param $name (string) Field name.
	 * @param $w (int) Width of the radio button.
	 * @param $prop (array) Javascript field properties. Possible values are described on official Javascript for Acrobat API reference.
	 * @param $opt (array) Annotation parameters. Possible values are described on official PDF32000_2008 reference.
	 * @param $onvalue (string) Value to be returned if selected.
	 * @param $checked (boolean) Define the initial state.
	 * @param $x (float) Abscissa of the upper-left corner of the rectangle
	 * @param $y (float) Ordinate of the upper-left corner of the rectangle
	 * @param $js (boolean) If true put the field using JavaScript (requires Acrobat Writer to be rendered).
	 * @public
	 * @author Nicola Asuni
	 * @since 4.8.000 (2009-09-07)
	 */
	public function RadioButton($name, $w, $prop=array(), $opt=array(), $onvalue='On', $checked=false, $x='', $y='', $js=false) {
		if ($x === '') {
			$x = $this->x;
		}
		if ($y === '') {
			$y = $this->y;
		}
		// check page for no-write regions and adapt page margins if necessary
		list($x, $y) = $this->checkPageRegions($w, $x, $y);
		if ($js) {
			$this->_addfield('radiobutton', $name, $x, $y, $w, $w, $prop);
			return;
		}
		if (TCPDF_STATIC::empty_string($onvalue)) {
			$onvalue = 'On';
		}
		if ($checked) {
			$defval = $onvalue;
		} else {
			$defval = 'Off';
		}
		// set font
		$font = 'zapfdingbats';
		if ($this->pdfa_mode) {
			// all fonts must be embedded
			$font = 'pdfa'.$font;
		}
		$this->AddFont($font);
		$tmpfont = $this->getFontBuffer($font);
		// set data for parent group
		if (!isset($this->radiobutton_groups[$this->page])) {
			$this->radiobutton_groups[$this->page] = array();
		}
		if (!isset($this->radiobutton_groups[$this->page][$name])) {
			$this->radiobutton_groups[$this->page][$name] = array();
			++$this->n;
			$this->radiobutton_groups[$this->page][$name]['n'] = $this->n;
			$this->radio_groups[] = $this->n;
		}
		$kid = ($this->n + 1);
		// save object ID to be added on Kids entry on parent object
		$this->radiobutton_groups[$this->page][$name][] = array('kid' => $kid, 'def' => $defval);
		// get default style
		$prop = array_merge($this->getFormDefaultProp(), $prop);
		$prop['NoToggleToOff'] = 'true';
		$prop['Radio'] = 'true';
		$prop['borderStyle'] = 'inset';
		// get annotation data
		$popt = TCPDF_STATIC::getAnnotOptFromJSProp($prop, $this->spot_colors, $this->rtl);
		// set additional default options
		$this->annotation_fonts[$tmpfont['fontkey']] = $tmpfont['i'];
		$fontstyle = sprintf('/F%d %F Tf %s', $tmpfont['i'], $this->FontSizePt, $this->TextColor);
		$popt['da'] = $fontstyle;
		// build appearance stream
		$popt['ap'] = array();
		$popt['ap']['n'] = array();
		$fx = ((($w - $this->getAbsFontMeasure($tmpfont['cw'][108])) / 2) * $this->k);
		$fy = (($w - ((($tmpfont['desc']['Ascent'] - $tmpfont['desc']['Descent']) * $this->FontSizePt / 1000) / $this->k)) * $this->k);
		$popt['ap']['n'][$onvalue] = sprintf('q %s BT /F%d %F Tf %F %F Td ('.chr(108).') Tj ET Q', $this->TextColor, $tmpfont['i'], $this->FontSizePt, $fx, $fy);
		$popt['ap']['n']['Off'] = sprintf('q %s BT /F%d %F Tf %F %F Td ('.chr(109).') Tj ET Q', $this->TextColor, $tmpfont['i'], $this->FontSizePt, $fx, $fy);
		if (!isset($popt['mk'])) {
			$popt['mk'] = array();
		}
		$popt['mk']['ca'] = '(l)';
		// merge options
		$opt = array_merge($popt, $opt);
		// set remaining annotation data
		$opt['Subtype'] = 'Widget';
		$opt['ft'] = 'Btn';
		if ($checked) {
			$opt['v'] = array('/'.$onvalue);
			$opt['as'] = $onvalue;
		} else {
			$opt['as'] = 'Off';
		}
		// store readonly flag
		if (!isset($this->radiobutton_groups[$this->page][$name]['#readonly#'])) {
			$this->radiobutton_groups[$this->page][$name]['#readonly#'] = false;
		}
		$this->radiobutton_groups[$this->page][$name]['#readonly#'] |= ($opt['f'] & 64);
		$this->Annotation($x, $y, $w, $w, $name, $opt, 0);
		if ($this->rtl) {
			$this->x -= $w;
		} else {
			$this->x += $w;
		}
	}

	/**
	 * Creates a List-box field
	 * @param $name (string) field name
	 * @param $w (int) width
	 * @param $h (int) height
	 * @param $values (array) array containing the list of values.
	 * @param $prop (array) javascript field properties. Possible values are described on official Javascript for Acrobat API reference.
	 * @param $opt (array) annotation parameters. Possible values are described on official PDF32000_2008 reference.
	 * @param $x (float) Abscissa of the upper-left corner of the rectangle
	 * @param $y (float) Ordinate of the upper-left corner of the rectangle
	 * @param $js (boolean) if true put the field using JavaScript (requires Acrobat Writer to be rendered).
	 * @public
	 * @author Nicola Asuni
	 * @since 4.8.000 (2009-09-07)
	 */
	public function ListBox($name, $w, $h, $values, $prop=array(), $opt=array(), $x='', $y='', $js=false) {
		if ($x === '') {
			$x = $this->x;
		}
		if ($y === '') {
			$y = $this->y;
		}
		// check page for no-write regions and adapt page margins if necessary
		list($x, $y) = $this->checkPageRegions($h, $x, $y);
		if ($js) {
			$this->_addfield('listbox', $name, $x, $y, $w, $h, $prop);
			$s = '';
			foreach ($values as $value) {
				if (is_array($value)) {
					$s .= ',[\''.addslashes($value[1]).'\',\''.addslashes($value[0]).'\']';
				} else {
					$s .= ',[\''.addslashes($value).'\',\''.addslashes($value).'\']';
				}
			}
			$this->javascript .= 'f'.$name.'.setItems('.substr($s, 1).');'."\n";
			return;
		}
		// get default style
		$prop = array_merge($this->getFormDefaultProp(), $prop);
		// get annotation data
		$popt = TCPDF_STATIC::getAnnotOptFromJSProp($prop, $this->spot_colors, $this->rtl);
		// set additional default values
		$this->annotation_fonts[$this->CurrentFont['fontkey']] = $this->CurrentFont['i'];
		$fontstyle = sprintf('/F%d %F Tf %s', $this->CurrentFont['i'], $this->FontSizePt, $this->TextColor);
		$popt['da'] = $fontstyle;
		// build appearance stream
		$popt['ap'] = array();
		$popt['ap']['n'] = '/Tx BMC q '.$fontstyle.' ';
		$text = '';
		foreach($values as $item) {
			if (is_array($item)) {
				$text .= $item[1]."\n";
			} else {
				$text .= $item."\n";
			}
		}
		$tmpid = $this->startTemplate($w, $h, false);
		$this->MultiCell($w, $h, $text, 0, '', false, 0, 0, 0, true, 0, false, true, 0, 'T', false);
		$this->endTemplate();
		--$this->n;
		$popt['ap']['n'] .= $this->xobjects[$tmpid]['outdata'];
		unset($this->xobjects[$tmpid]);
		$popt['ap']['n'] .= 'Q EMC';
		// merge options
		$opt = array_merge($popt, $opt);
		// set remaining annotation data
		$opt['Subtype'] = 'Widget';
		$opt['ft'] = 'Ch';
		$opt['t'] = $name;
		$opt['opt'] = $values;
		unset($opt['mk']['ca']);
		unset($opt['mk']['rc']);
		unset($opt['mk']['ac']);
		unset($opt['mk']['i']);
		unset($opt['mk']['ri']);
		unset($opt['mk']['ix']);
		unset($opt['mk']['if']);
		unset($opt['mk']['tp']);
		$this->Annotation($x, $y, $w, $h, $name, $opt, 0);
		if ($this->rtl) {
			$this->x -= $w;
		} else {
			$this->x += $w;
		}
	}

	/**
	 * Creates a Combo-box field
	 * @param $name (string) field name
	 * @param $w (int) width
	 * @param $h (int) height
	 * @param $values (array) array containing the list of values.
	 * @param $prop (array) javascript field properties. Possible values are described on official Javascript for Acrobat API reference.
	 * @param $opt (array) annotation parameters. Possible values are described on official PDF32000_2008 reference.
	 * @param $x (float) Abscissa of the upper-left corner of the rectangle
	 * @param $y (float) Ordinate of the upper-left corner of the rectangle
	 * @param $js (boolean) if true put the field using JavaScript (requires Acrobat Writer to be rendered).
	 * @public
	 * @author Nicola Asuni
	 * @since 4.8.000 (2009-09-07)
	 */
	public function ComboBox($name, $w, $h, $values, $prop=array(), $opt=array(), $x='', $y='', $js=false) {
		if ($x === '') {
			$x = $this->x;
		}
		if ($y === '') {
			$y = $this->y;
		}
		// check page for no-write regions and adapt page margins if necessary
		list($x, $y) = $this->checkPageRegions($h, $x, $y);
		if ($js) {
			$this->_addfield('combobox', $name, $x, $y, $w, $h, $prop);
			$s = '';
			foreach ($values as $value) {
				if (is_array($value)) {
					$s .= ',[\''.addslashes($value[1]).'\',\''.addslashes($value[0]).'\']';
				} else {
					$s .= ',[\''.addslashes($value).'\',\''.addslashes($value).'\']';
				}
			}
			$this->javascript .= 'f'.$name.'.setItems('.substr($s, 1).');'."\n";
			return;
		}
		// get default style
		$prop = array_merge($this->getFormDefaultProp(), $prop);
		$prop['Combo'] = true;
		// get annotation data
		$popt = TCPDF_STATIC::getAnnotOptFromJSProp($prop, $this->spot_colors, $this->rtl);
		// set additional default options
		$this->annotation_fonts[$this->CurrentFont['fontkey']] = $this->CurrentFont['i'];
		$fontstyle = sprintf('/F%d %F Tf %s', $this->CurrentFont['i'], $this->FontSizePt, $this->TextColor);
		$popt['da'] = $fontstyle;
		// build appearance stream
		$popt['ap'] = array();
		$popt['ap']['n'] = '/Tx BMC q '.$fontstyle.' ';
		$text = '';
		foreach($values as $item) {
			if (is_array($item)) {
				$text .= $item[1]."\n";
			} else {
				$text .= $item."\n";
			}
		}
		$tmpid = $this->startTemplate($w, $h, false);
		$this->MultiCell($w, $h, $text, 0, '', false, 0, 0, 0, true, 0, false, true, 0, 'T', false);
		$this->endTemplate();
		--$this->n;
		$popt['ap']['n'] .= $this->xobjects[$tmpid]['outdata'];
		unset($this->xobjects[$tmpid]);
		$popt['ap']['n'] .= 'Q EMC';
		// merge options
		$opt = array_merge($popt, $opt);
		// set remaining annotation data
		$opt['Subtype'] = 'Widget';
		$opt['ft'] = 'Ch';
		$opt['t'] = $name;
		$opt['opt'] = $values;
		unset($opt['mk']['ca']);
		unset($opt['mk']['rc']);
		unset($opt['mk']['ac']);
		unset($opt['mk']['i']);
		unset($opt['mk']['ri']);
		unset($opt['mk']['ix']);
		unset($opt['mk']['if']);
		unset($opt['mk']['tp']);
		$this->Annotation($x, $y, $w, $h, $name, $opt, 0);
		if ($this->rtl) {
			$this->x -= $w;
		} else {
			$this->x += $w;
		}
	}

	/**
	 * Creates a CheckBox field
	 * @param $name (string) field name
	 * @param $w (int) width
	 * @param $checked (boolean) define the initial state.
	 * @param $prop (array) javascript field properties. Possible values are described on official Javascript for Acrobat API reference.
	 * @param $opt (array) annotation parameters. Possible values are described on official PDF32000_2008 reference.
	 * @param $onvalue (string) value to be returned if selected.
	 * @param $x (float) Abscissa of the upper-left corner of the rectangle
	 * @param $y (float) Ordinate of the upper-left corner of the rectangle
	 * @param $js (boolean) if true put the field using JavaScript (requires Acrobat Writer to be rendered).
	 * @public
	 * @author Nicola Asuni
	 * @since 4.8.000 (2009-09-07)
	 */
	public function CheckBox($name, $w, $checked=false, $prop=array(), $opt=array(), $onvalue='Yes', $x='', $y='', $js=false) {
		if ($x === '') {
			$x = $this->x;
		}
		if ($y === '') {
			$y = $this->y;
		}
		// check page for no-write regions and adapt page margins if necessary
		list($x, $y) = $this->checkPageRegions($w, $x, $y);
		if ($js) {
			$this->_addfield('checkbox', $name, $x, $y, $w, $w, $prop);
			return;
		}
		if (!isset($prop['value'])) {
			$prop['value'] = array('Yes');
		}
		// get default style
		$prop = array_merge($this->getFormDefaultProp(), $prop);
		$prop['borderStyle'] = 'inset';
		// get annotation data
		$popt = TCPDF_STATIC::getAnnotOptFromJSProp($prop, $this->spot_colors, $this->rtl);
		// set additional default options
		$font = 'zapfdingbats';
		if ($this->pdfa_mode) {
			// all fonts must be embedded
			$font = 'pdfa'.$font;
		}
		$this->AddFont($font);
		$tmpfont = $this->getFontBuffer($font);
		$this->annotation_fonts[$tmpfont['fontkey']] = $tmpfont['i'];
		$fontstyle = sprintf('/F%d %F Tf %s', $tmpfont['i'], $this->FontSizePt, $this->TextColor);
		$popt['da'] = $fontstyle;
		// build appearance stream
		$popt['ap'] = array();
		$popt['ap']['n'] = array();
		$fx = ((($w - $this->getAbsFontMeasure($tmpfont['cw'][110])) / 2) * $this->k);
		$fy = (($w - ((($tmpfont['desc']['Ascent'] - $tmpfont['desc']['Descent']) * $this->FontSizePt / 1000) / $this->k)) * $this->k);
		$popt['ap']['n']['Yes'] = sprintf('q %s BT /F%d %F Tf %F %F Td ('.chr(110).') Tj ET Q', $this->TextColor, $tmpfont['i'], $this->FontSizePt, $fx, $fy);
		$popt['ap']['n']['Off'] = sprintf('q %s BT /F%d %F Tf %F %F Td ('.chr(111).') Tj ET Q', $this->TextColor, $tmpfont['i'], $this->FontSizePt, $fx, $fy);
		// merge options
		$opt = array_merge($popt, $opt);
		// set remaining annotation data
		$opt['Subtype'] = 'Widget';
		$opt['ft'] = 'Btn';
		$opt['t'] = $name;
		if (TCPDF_STATIC::empty_string($onvalue)) {
			$onvalue = 'Yes';
		}
		$opt['opt'] = array($onvalue);
		if ($checked) {
			$opt['v'] = array('/Yes');
			$opt['as'] = 'Yes';
		} else {
			$opt['v'] = array('/Off');
			$opt['as'] = 'Off';
		}
		$this->Annotation($x, $y, $w, $w, $name, $opt, 0);
		if ($this->rtl) {
			$this->x -= $w;
		} else {
			$this->x += $w;
		}
	}

	/**
	 * Creates a button field
	 * @param $name (string) field name
	 * @param $w (int) width
	 * @param $h (int) height
	 * @param $caption (string) caption.
	 * @param $action (mixed) action triggered by pressing the button. Use a string to specify a javascript action. Use an array to specify a form action options as on section 12.7.5 of PDF32000_2008.
	 * @param $prop (array) javascript field properties. Possible values are described on official Javascript for Acrobat API reference.
	 * @param $opt (array) annotation parameters. Possible values are described on official PDF32000_2008 reference.
	 * @param $x (float) Abscissa of the upper-left corner of the rectangle
	 * @param $y (float) Ordinate of the upper-left corner of the rectangle
	 * @param $js (boolean) if true put the field using JavaScript (requires Acrobat Writer to be rendered).
	 * @public
	 * @author Nicola Asuni
	 * @since 4.8.000 (2009-09-07)
	 */
	public function Button($name, $w, $h, $caption, $action, $prop=array(), $opt=array(), $x='', $y='', $js=false) {
		if ($x === '') {
			$x = $this->x;
		}
		if ($y === '') {
			$y = $this->y;
		}
		// check page for no-write regions and adapt page margins if necessary
		list($x, $y) = $this->checkPageRegions($h, $x, $y);
		if ($js) {
			$this->_addfield('button', $name, $this->x, $this->y, $w, $h, $prop);
			$this->javascript .= 'f'.$name.".buttonSetCaption('".addslashes($caption)."');\n";
			$this->javascript .= 'f'.$name.".setAction('MouseUp','".addslashes($action)."');\n";
			$this->javascript .= 'f'.$name.".highlight='push';\n";
			$this->javascript .= 'f'.$name.".print=false;\n";
			return;
		}
		// get default style
		$prop = array_merge($this->getFormDefaultProp(), $prop);
		$prop['Pushbutton'] = 'true';
		$prop['highlight'] = 'push';
		$prop['display'] = 'display.noPrint';
		// get annotation data
		$popt = TCPDF_STATIC::getAnnotOptFromJSProp($prop, $this->spot_colors, $this->rtl);
		$this->annotation_fonts[$this->CurrentFont['fontkey']] = $this->CurrentFont['i'];
		$fontstyle = sprintf('/F%d %F Tf %s', $this->CurrentFont['i'], $this->FontSizePt, $this->TextColor);
		$popt['da'] = $fontstyle;
		// build appearance stream
		$popt['ap'] = array();
		$popt['ap']['n'] = '/Tx BMC q '.$fontstyle.' ';
		$tmpid = $this->startTemplate($w, $h, false);
		$bw = (2 / $this->k); // border width
		$border = array(
			'L' => array('width' => $bw, 'cap' => 'square', 'join' => 'miter', 'dash' => 0, 'color' => array(231)),
			'R' => array('width' => $bw, 'cap' => 'square', 'join' => 'miter', 'dash' => 0, 'color' => array(51)),
			'T' => array('width' => $bw, 'cap' => 'square', 'join' => 'miter', 'dash' => 0, 'color' => array(231)),
			'B' => array('width' => $bw, 'cap' => 'square', 'join' => 'miter', 'dash' => 0, 'color' => array(51)));
		$this->SetFillColor(204);
		$this->Cell($w, $h, $caption, $border, 0, 'C', true, '', 1, false, 'T', 'M');
		$this->endTemplate();
		--$this->n;
		$popt['ap']['n'] .= $this->xobjects[$tmpid]['outdata'];
		unset($this->xobjects[$tmpid]);
		$popt['ap']['n'] .= 'Q EMC';
		// set additional default options
		if (!isset($popt['mk'])) {
			$popt['mk'] = array();
		}
		$ann_obj_id = ($this->n + 1);
		if (!empty($action) AND !is_array($action)) {
			$ann_obj_id = ($this->n + 2);
		}
		$popt['mk']['ca'] = $this->_textstring($caption, $ann_obj_id);
		$popt['mk']['rc'] = $this->_textstring($caption, $ann_obj_id);
		$popt['mk']['ac'] = $this->_textstring($caption, $ann_obj_id);
		// merge options
		$opt = array_merge($popt, $opt);
		// set remaining annotation data
		$opt['Subtype'] = 'Widget';
		$opt['ft'] = 'Btn';
		$opt['t'] = $caption;
		$opt['v'] = $name;
		if (!empty($action)) {
			if (is_array($action)) {
				// form action options as on section 12.7.5 of PDF32000_2008.
				$opt['aa'] = '/D <<';
				$bmode = array('SubmitForm', 'ResetForm', 'ImportData');
				foreach ($action AS $key => $val) {
					if (($key == 'S') AND in_array($val, $bmode)) {
						$opt['aa'] .= ' /S /'.$val;
					} elseif (($key == 'F') AND (!empty($val))) {
						$opt['aa'] .= ' /F '.$this->_datastring($val, $ann_obj_id);
					} elseif (($key == 'Fields') AND is_array($val) AND !empty($val)) {
						$opt['aa'] .= ' /Fields [';
						foreach ($val AS $field) {
							$opt['aa'] .= ' '.$this->_textstring($field, $ann_obj_id);
						}
						$opt['aa'] .= ']';
					} elseif (($key == 'Flags')) {
						$ff = 0;
						if (is_array($val)) {
							foreach ($val AS $flag) {
								switch ($flag) {
									case 'Include/Exclude': {
										$ff += 1 << 0;
										break;
									}
									case 'IncludeNoValueFields': {
										$ff += 1 << 1;
										break;
									}
									case 'ExportFormat': {
										$ff += 1 << 2;
										break;
									}
									case 'GetMethod': {
										$ff += 1 << 3;
										break;
									}
									case 'SubmitCoordinates': {
										$ff += 1 << 4;
										break;
									}
									case 'XFDF': {
										$ff += 1 << 5;
										break;
									}
									case 'IncludeAppendSaves': {
										$ff += 1 << 6;
										break;
									}
									case 'IncludeAnnotations': {
										$ff += 1 << 7;
										break;
									}
									case 'SubmitPDF': {
										$ff += 1 << 8;
										break;
									}
									case 'CanonicalFormat': {
										$ff += 1 << 9;
										break;
									}
									case 'ExclNonUserAnnots': {
										$ff += 1 << 10;
										break;
									}
									case 'ExclFKey': {
										$ff += 1 << 11;
										break;
									}
									case 'EmbedForm': {
										$ff += 1 << 13;
										break;
									}
								}
							}
						} else {
							$ff = intval($val);
						}
						$opt['aa'] .= ' /Flags '.$ff;
					}
				}
				$opt['aa'] .= ' >>';
			} else {
				// Javascript action or raw action command
				$js_obj_id = $this->addJavascriptObject($action);
				$opt['aa'] = '/D '.$js_obj_id.' 0 R';
			}
		}
		$this->Annotation($x, $y, $w, $h, $name, $opt, 0);
		if ($this->rtl) {
			$this->x -= $w;
		} else {
			$this->x += $w;
		}
	}
}

