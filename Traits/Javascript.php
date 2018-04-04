<?php

namespace Sztyup\Tcpdf\Traits;

trait Javascript
{
	/**
	 * Adds a javascript
	 * @param $script (string) Javascript code
	 * @public
	 * @author Johannes G\FCntert, Nicola Asuni
	 * @since 2.1.002 (2008-02-12)
	 */
	public function IncludeJS($script) {
		$this->javascript .= $script;
	}

	/**
	 * Adds a javascript object and return object ID
	 * @param $script (string) Javascript code
	 * @param $onload (boolean) if true executes this object when opening the document
	 * @return int internal object ID
	 * @public
	 * @author Nicola Asuni
	 * @since 4.8.000 (2009-09-07)
	 */
	public function addJavascriptObject($script, $onload=false) {
		if ($this->pdfa_mode) {
			// javascript is not allowed in PDF/A mode
			return false;
		}
		++$this->n;
		$this->js_objects[$this->n] = array('n' => $this->n, 'js' => $script, 'onload' => $onload);
		return $this->n;
	}

	/**
	 * Create a javascript PDF string.
	 * @protected
	 * @author Johannes G\FCntert, Nicola Asuni
	 * @since 2.1.002 (2008-02-12)
	 */
	protected function _putjavascript() {
		if ($this->pdfa_mode OR (empty($this->javascript) AND empty($this->js_objects))) {
			return;
		}
		if (strpos($this->javascript, 'this.addField') > 0) {
			if (!$this->ur['enabled']) {
				//$this->setUserRights();
			}
			// the following two lines are used to avoid form fields duplication after saving
			// The addField method only works when releasing user rights (UR3)
			$jsa = sprintf("ftcpdfdocsaved=this.addField('%s','%s',%d,[%F,%F,%F,%F]);", 'tcpdfdocsaved', 'text', 0, 0, 1, 0, 1);
			$jsb = "getField('tcpdfdocsaved').value='saved';";
			$this->javascript = $jsa."\n".$this->javascript."\n".$jsb;
		}
		// name tree for javascript
		$this->n_js = '<< /Names [';
		if (!empty($this->javascript)) {
			$this->n_js .= ' (EmbeddedJS) '.($this->n + 1).' 0 R';
		}
		if (!empty($this->js_objects)) {
			foreach ($this->js_objects as $key => $val) {
				if ($val['onload']) {
					$this->n_js .= ' (JS'.$key.') '.$key.' 0 R';
				}
			}
		}
		$this->n_js .= ' ] >>';
		// default Javascript object
		if (!empty($this->javascript)) {
			$obj_id = $this->_newobj();
			$out = '<< /S /JavaScript';
			$out .= ' /JS '.$this->_textstring($this->javascript, $obj_id);
			$out .= ' >>';
			$out .= "\n".'endobj';
			$this->_out($out);
		}
		// additional Javascript objects
		if (!empty($this->js_objects)) {
			foreach ($this->js_objects as $key => $val) {
				$out = $this->_getobj($key)."\n".' << /S /JavaScript /JS '.$this->_textstring($val['js'], $key).' >>'."\n".'endobj';
				$this->_out($out);
			}
		}
	}

	/**
	 * Adds a javascript form field.
	 * @param $type (string) field type
	 * @param $name (string) field name
	 * @param $x (int) horizontal position
	 * @param $y (int) vertical position
	 * @param $w (int) width
	 * @param $h (int) height
	 * @param $prop (array) javascript field properties. Possible values are described on official Javascript for Acrobat API reference.
	 * @protected
	 * @author Denis Van Nuffelen, Nicola Asuni
	 * @since 2.1.002 (2008-02-12)
	 */
	protected function _addfield($type, $name, $x, $y, $w, $h, $prop) {
		if ($this->rtl) {
			$x = $x - $w;
		}
		// the followind avoid fields duplication after saving the document
		$this->javascript .= "if (getField('tcpdfdocsaved').value != 'saved') {";
		$k = $this->k;
		$this->javascript .= sprintf("f".$name."=this.addField('%s','%s',%u,[%F,%F,%F,%F]);", $name, $type, $this->PageNo()-1, $x*$k, ($this->h-$y)*$k+1, ($x+$w)*$k, ($this->h-$y-$h)*$k+1)."\n";
		$this->javascript .= 'f'.$name.'.textSize='.$this->FontSizePt.";\n";
		foreach($prop as $key => $val) {
			if (strcmp(substr($key, -5), 'Color') == 0) {
				$val = TCPDF_COLORS::_JScolor($val);
			} else {
				$val = "'".$val."'";
			}
			$this->javascript .= 'f'.$name.'.'.$key.'='.$val.";\n";
		}
		if ($this->rtl) {
			$this->x -= $w;
		} else {
			$this->x += $w;
		}
		$this->javascript .= '}';
	}

	/**
	 * Adds a javascript
	 * @param $script (string) Javascript code
	 * @public
	 * @author Johannes G\FCntert, Nicola Asuni
	 * @since 2.1.002 (2008-02-12)
	 */
	public function IncludeJS($script) {
		$this->javascript .= $script;
	}

	/**
	 * Adds a javascript object and return object ID
	 * @param $script (string) Javascript code
	 * @param $onload (boolean) if true executes this object when opening the document
	 * @return int internal object ID
	 * @public
	 * @author Nicola Asuni
	 * @since 4.8.000 (2009-09-07)
	 */
	public function addJavascriptObject($script, $onload=false) {
		if ($this->pdfa_mode) {
			// javascript is not allowed in PDF/A mode
			return false;
		}
		++$this->n;
		$this->js_objects[$this->n] = array('n' => $this->n, 'js' => $script, 'onload' => $onload);
		return $this->n;
	}

	/**
	 * Create a javascript PDF string.
	 * @protected
	 * @author Johannes G\FCntert, Nicola Asuni
	 * @since 2.1.002 (2008-02-12)
	 */
	protected function _putjavascript() {
		if ($this->pdfa_mode OR (empty($this->javascript) AND empty($this->js_objects))) {
			return;
		}
		if (strpos($this->javascript, 'this.addField') > 0) {
			if (!$this->ur['enabled']) {
				//$this->setUserRights();
			}
			// the following two lines are used to avoid form fields duplication after saving
			// The addField method only works when releasing user rights (UR3)
			$jsa = sprintf("ftcpdfdocsaved=this.addField('%s','%s',%d,[%F,%F,%F,%F]);", 'tcpdfdocsaved', 'text', 0, 0, 1, 0, 1);
			$jsb = "getField('tcpdfdocsaved').value='saved';";
			$this->javascript = $jsa."\n".$this->javascript."\n".$jsb;
		}
		// name tree for javascript
		$this->n_js = '<< /Names [';
		if (!empty($this->javascript)) {
			$this->n_js .= ' (EmbeddedJS) '.($this->n + 1).' 0 R';
		}
		if (!empty($this->js_objects)) {
			foreach ($this->js_objects as $key => $val) {
				if ($val['onload']) {
					$this->n_js .= ' (JS'.$key.') '.$key.' 0 R';
				}
			}
		}
		$this->n_js .= ' ] >>';
		// default Javascript object
		if (!empty($this->javascript)) {
			$obj_id = $this->_newobj();
			$out = '<< /S /JavaScript';
			$out .= ' /JS '.$this->_textstring($this->javascript, $obj_id);
			$out .= ' >>';
			$out .= "\n".'endobj';
			$this->_out($out);
		}
		// additional Javascript objects
		if (!empty($this->js_objects)) {
			foreach ($this->js_objects as $key => $val) {
				$out = $this->_getobj($key)."\n".' << /S /JavaScript /JS '.$this->_textstring($val['js'], $key).' >>'."\n".'endobj';
				$this->_out($out);
			}
		}
	}

	/**
	 * Adds a javascript form field.
	 * @param $type (string) field type
	 * @param $name (string) field name
	 * @param $x (int) horizontal position
	 * @param $y (int) vertical position
	 * @param $w (int) width
	 * @param $h (int) height
	 * @param $prop (array) javascript field properties. Possible values are described on official Javascript for Acrobat API reference.
	 * @protected
	 * @author Denis Van Nuffelen, Nicola Asuni
	 * @since 2.1.002 (2008-02-12)
	 */
	protected function _addfield($type, $name, $x, $y, $w, $h, $prop) {
		if ($this->rtl) {
			$x = $x - $w;
		}
		// the followind avoid fields duplication after saving the document
		$this->javascript .= "if (getField('tcpdfdocsaved').value != 'saved') {";
		$k = $this->k;
		$this->javascript .= sprintf("f".$name."=this.addField('%s','%s',%u,[%F,%F,%F,%F]);", $name, $type, $this->PageNo()-1, $x*$k, ($this->h-$y)*$k+1, ($x+$w)*$k, ($this->h-$y-$h)*$k+1)."\n";
		$this->javascript .= 'f'.$name.'.textSize='.$this->FontSizePt.";\n";
		foreach($prop as $key => $val) {
			if (strcmp(substr($key, -5), 'Color') == 0) {
				$val = TCPDF_COLORS::_JScolor($val);
			} else {
				$val = "'".$val."'";
			}
			$this->javascript .= 'f'.$name.'.'.$key.'='.$val.";\n";
		}
		if ($this->rtl) {
			$this->x -= $w;
		} else {
			$this->x += $w;
		}
		$this->javascript .= '}';
	}
}
