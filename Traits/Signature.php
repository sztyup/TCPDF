<?php

namespace Sztyup\Tcpdf\Traits;

trait Signature
{
/**
	 * Add certification signature (DocMDP or UR3)
	 * You can set only one signature type
	 * @protected
	 * @author Nicola Asuni
	 * @since 4.6.008 (2009-05-07)
	 */
	protected function _putsignature() {
		if ((!$this->sign) OR (!isset($this->signature_data['cert_type']))) {
			return;
		}
		$sigobjid = ($this->sig_obj_id + 1);
		$out = $this->_getobj($sigobjid)."\n";
		$out .= '<< /Type /Sig';
		$out .= ' /Filter /Adobe.PPKLite';
		$out .= ' /SubFilter /adbe.pkcs7.detached';
		$out .= ' '.TCPDF_STATIC::$byterange_string;
		$out .= ' /Contents<'.str_repeat('0', $this->signature_max_length).'>';
		if (empty($this->signature_data['approval']) OR ($this->signature_data['approval'] != 'A')) {
			$out .= ' /Reference ['; // array of signature reference dictionaries
			$out .= ' << /Type /SigRef';
			if ($this->signature_data['cert_type'] > 0) {
				$out .= ' /TransformMethod /DocMDP';
				$out .= ' /TransformParams <<';
				$out .= ' /Type /TransformParams';
				$out .= ' /P '.$this->signature_data['cert_type'];
				$out .= ' /V /1.2';
			} else {
				$out .= ' /TransformMethod /UR3';
				$out .= ' /TransformParams <<';
				$out .= ' /Type /TransformParams';
				$out .= ' /V /2.2';
				if (!TCPDF_STATIC::empty_string($this->ur['document'])) {
					$out .= ' /Document['.$this->ur['document'].']';
				}
				if (!TCPDF_STATIC::empty_string($this->ur['form'])) {
					$out .= ' /Form['.$this->ur['form'].']';
				}
				if (!TCPDF_STATIC::empty_string($this->ur['signature'])) {
					$out .= ' /Signature['.$this->ur['signature'].']';
				}
				if (!TCPDF_STATIC::empty_string($this->ur['annots'])) {
					$out .= ' /Annots['.$this->ur['annots'].']';
				}
				if (!TCPDF_STATIC::empty_string($this->ur['ef'])) {
					$out .= ' /EF['.$this->ur['ef'].']';
				}
				if (!TCPDF_STATIC::empty_string($this->ur['formex'])) {
					$out .= ' /FormEX['.$this->ur['formex'].']';
				}
			}
			$out .= ' >>'; // close TransformParams
			// optional digest data (values must be calculated and replaced later)
			//$out .= ' /Data ********** 0 R';
			//$out .= ' /DigestMethod/MD5';
			//$out .= ' /DigestLocation[********** 34]';
			//$out .= ' /DigestValue<********************************>';
			$out .= ' >>';
			$out .= ' ]'; // end of reference
		}
		if (isset($this->signature_data['info']['Name']) AND !TCPDF_STATIC::empty_string($this->signature_data['info']['Name'])) {
			$out .= ' /Name '.$this->_textstring($this->signature_data['info']['Name'], $sigobjid);
		}
		if (isset($this->signature_data['info']['Location']) AND !TCPDF_STATIC::empty_string($this->signature_data['info']['Location'])) {
			$out .= ' /Location '.$this->_textstring($this->signature_data['info']['Location'], $sigobjid);
		}
		if (isset($this->signature_data['info']['Reason']) AND !TCPDF_STATIC::empty_string($this->signature_data['info']['Reason'])) {
			$out .= ' /Reason '.$this->_textstring($this->signature_data['info']['Reason'], $sigobjid);
		}
		if (isset($this->signature_data['info']['ContactInfo']) AND !TCPDF_STATIC::empty_string($this->signature_data['info']['ContactInfo'])) {
			$out .= ' /ContactInfo '.$this->_textstring($this->signature_data['info']['ContactInfo'], $sigobjid);
		}
		$out .= ' /M '.$this->_datestring($sigobjid, $this->doc_modification_timestamp);
		$out .= ' >>';
		$out .= "\n".'endobj';
		$this->_out($out);
	}

	/**
	 * Set User's Rights for PDF Reader
	 * WARNING: This is experimental and currently do not work.
	 * Check the PDF Reference 8.7.1 Transform Methods,
	 * Table 8.105 Entries in the UR transform parameters dictionary
	 * @param $enable (boolean) if true enable user's rights on PDF reader
	 * @param $document (string) Names specifying additional document-wide usage rights for the document. The only defined value is "/FullSave", which permits a user to save the document along with modified form and/or annotation data.
	 * @param $annots (string) Names specifying additional annotation-related usage rights for the document. Valid names in PDF 1.5 and later are /Create/Delete/Modify/Copy/Import/Export, which permit the user to perform the named operation on annotations.
	 * @param $form (string) Names specifying additional form-field-related usage rights for the document. Valid names are: /Add/Delete/FillIn/Import/Export/SubmitStandalone/SpawnTemplate
	 * @param $signature (string) Names specifying additional signature-related usage rights for the document. The only defined value is /Modify, which permits a user to apply a digital signature to an existing signature form field or clear a signed signature form field.
	 * @param $ef (string) Names specifying additional usage rights for named embedded files in the document. Valid names are /Create/Delete/Modify/Import, which permit the user to perform the named operation on named embedded files
	 Names specifying additional embedded-files-related usage rights for the document.
	 * @param $formex (string) Names specifying additional form-field-related usage rights. The only valid name is BarcodePlaintext, which permits text form field data to be encoded as a plaintext two-dimensional barcode.
	 * @public
	 * @author Nicola Asuni
	 * @since 2.9.000 (2008-03-26)
	 */
	public function setUserRights(
			$enable=true,
			$document='/FullSave',
			$annots='/Create/Delete/Modify/Copy/Import/Export',
			$form='/Add/Delete/FillIn/Import/Export/SubmitStandalone/SpawnTemplate',
			$signature='/Modify',
			$ef='/Create/Delete/Modify/Import',
			$formex='') {
		$this->ur['enabled'] = $enable;
		$this->ur['document'] = $document;
		$this->ur['annots'] = $annots;
		$this->ur['form'] = $form;
		$this->ur['signature'] = $signature;
		$this->ur['ef'] = $ef;
		$this->ur['formex'] = $formex;
		if (!$this->sign) {
			$this->setSignature('', '', '', '', 0, array());
		}
	}

	/**
	 * Enable document signature (requires the OpenSSL Library).
	 * The digital signature improve document authenticity and integrity and allows o enable extra features on Acrobat Reader.
	 * To create self-signed signature: openssl req -x509 -nodes -days 365000 -newkey rsa:1024 -keyout tcpdf.crt -out tcpdf.crt
	 * To export crt to p12: openssl pkcs12 -export -in tcpdf.crt -out tcpdf.p12
	 * To convert pfx certificate to pem: openssl pkcs12 -in tcpdf.pfx -out tcpdf.crt -nodes
	 * @param $signing_cert (mixed) signing certificate (string or filename prefixed with 'file://')
	 * @param $private_key (mixed) private key (string or filename prefixed with 'file://')
	 * @param $private_key_password (string) password
	 * @param $extracerts (string) specifies the name of a file containing a bunch of extra certificates to include in the signature which can for example be used to help the recipient to verify the certificate that you used.
	 * @param $cert_type (int) The access permissions granted for this document. Valid values shall be: 1 = No changes to the document shall be permitted; any change to the document shall invalidate the signature; 2 = Permitted changes shall be filling in forms, instantiating page templates, and signing; other changes shall invalidate the signature; 3 = Permitted changes shall be the same as for 2, as well as annotation creation, deletion, and modification; other changes shall invalidate the signature.
	 * @param $info (array) array of option information: Name, Location, Reason, ContactInfo.
	 * @param $approval (string) Enable approval signature eg. for PDF incremental update
	 * @public
	 * @author Nicola Asuni
	 * @since 4.6.005 (2009-04-24)
	 */
	public function setSignature($signing_cert='', $private_key='', $private_key_password='', $extracerts='', $cert_type=2, $info=array(), $approval='') {
		// to create self-signed signature: openssl req -x509 -nodes -days 365000 -newkey rsa:1024 -keyout tcpdf.crt -out tcpdf.crt
		// to export crt to p12: openssl pkcs12 -export -in tcpdf.crt -out tcpdf.p12
		// to convert pfx certificate to pem: openssl
		//     OpenSSL> pkcs12 -in <cert.pfx> -out <cert.crt> -nodes
		$this->sign = true;
		++$this->n;
		$this->sig_obj_id = $this->n; // signature widget
		++$this->n; // signature object ($this->sig_obj_id + 1)
		$this->signature_data = array();
		if (strlen($signing_cert) == 0) {
			$this->Error('Please provide a certificate file and password!');
		}
		if (strlen($private_key) == 0) {
			$private_key = $signing_cert;
		}
		$this->signature_data['signcert'] = $signing_cert;
		$this->signature_data['privkey'] = $private_key;
		$this->signature_data['password'] = $private_key_password;
		$this->signature_data['extracerts'] = $extracerts;
		$this->signature_data['cert_type'] = $cert_type;
		$this->signature_data['info'] = $info;
		$this->signature_data['approval'] = $approval;
	}

	/**
	 * Set the digital signature appearance (a cliccable rectangle area to get signature properties)
	 * @param $x (float) Abscissa of the upper-left corner.
	 * @param $y (float) Ordinate of the upper-left corner.
	 * @param $w (float) Width of the signature area.
	 * @param $h (float) Height of the signature area.
	 * @param $page (int) option page number (if < 0 the current page is used).
	 * @param $name (string) Name of the signature.
	 * @public
	 * @author Nicola Asuni
	 * @since 5.3.011 (2010-06-17)
	 */
	public function setSignatureAppearance($x=0, $y=0, $w=0, $h=0, $page=-1, $name='') {
		$this->signature_appearance = $this->getSignatureAppearanceArray($x, $y, $w, $h, $page, $name);
	}

	/**
	 * Add an empty digital signature appearance (a cliccable rectangle area to get signature properties)
	 * @param $x (float) Abscissa of the upper-left corner.
	 * @param $y (float) Ordinate of the upper-left corner.
	 * @param $w (float) Width of the signature area.
	 * @param $h (float) Height of the signature area.
	 * @param $page (int) option page number (if < 0 the current page is used).
	 * @param $name (string) Name of the signature.
	 * @public
	 * @author Nicola Asuni
	 * @since 5.9.101 (2011-07-06)
	 */
	public function addEmptySignatureAppearance($x=0, $y=0, $w=0, $h=0, $page=-1, $name='') {
		++$this->n;
		$this->empty_signature_appearance[] = array('objid' => $this->n) + $this->getSignatureAppearanceArray($x, $y, $w, $h, $page, $name);
	}

	/**
	 * Get the array that defines the signature appearance (page and rectangle coordinates).
	 * @param $x (float) Abscissa of the upper-left corner.
	 * @param $y (float) Ordinate of the upper-left corner.
	 * @param $w (float) Width of the signature area.
	 * @param $h (float) Height of the signature area.
	 * @param $page (int) option page number (if < 0 the current page is used).
	 * @param $name (string) Name of the signature.
	 * @return (array) Array defining page and rectangle coordinates of signature appearance.
	 * @protected
	 * @author Nicola Asuni
	 * @since 5.9.101 (2011-07-06)
	 */
	protected function getSignatureAppearanceArray($x=0, $y=0, $w=0, $h=0, $page=-1, $name='') {
		$sigapp = array();
		if (($page < 1) OR ($page > $this->numpages)) {
			$sigapp['page'] = $this->page;
		} else {
			$sigapp['page'] = intval($page);
		}
		if (empty($name)) {
			$sigapp['name'] = 'Signature';
		} else {
			$sigapp['name'] = $name;
		}
		$a = $x * $this->k;
		$b = $this->pagedim[($sigapp['page'])]['h'] - (($y + $h) * $this->k);
		$c = $w * $this->k;
		$d = $h * $this->k;
		$sigapp['rect'] = sprintf('%F %F %F %F', $a, $b, ($a + $c), ($b + $d));
		return $sigapp;
	}

	/**
	 * Enable document timestamping (requires the OpenSSL Library).
	 * The trusted timestamping improve document security that means that no one should be able to change the document once it has been recorded.
	 * Use with digital signature only!
	 * @param $tsa_host (string) Time Stamping Authority (TSA) server (prefixed with 'https://')
	 * @param $tsa_username (string) Specifies the username for TSA authorization (optional) OR specifies the TSA authorization PEM file (see: example_66.php, optional)
	 * @param $tsa_password (string) Specifies the password for TSA authorization (optional)
	 * @param $tsa_cert (string) Specifies the location of TSA certificate for authorization (optional for cURL)
	 * @public
	 * @author Richard Stockinger
	 * @since 6.0.090 (2014-06-16)
	 */
	public function setTimeStamp($tsa_host='', $tsa_username='', $tsa_password='', $tsa_cert='') {
		$this->tsa_data = array();
		if (!function_exists('curl_init')) {
			$this->Error('Please enable cURL PHP extension!');
		}
		if (strlen($tsa_host) == 0) {
			$this->Error('Please specify the host of Time Stamping Authority (TSA)!');
		}
		$this->tsa_data['tsa_host'] = $tsa_host;
		if (is_file($tsa_username)) {
			$this->tsa_data['tsa_auth'] = $tsa_username;
		} else {
			$this->tsa_data['tsa_username'] = $tsa_username;
		}
		$this->tsa_data['tsa_password'] = $tsa_password;
		$this->tsa_data['tsa_cert'] = $tsa_cert;
		$this->tsa_timestamp = true;
	}

	/**
	 * NOT YET IMPLEMENTED
	 * Request TSA for a timestamp
	 * @param $signature (string) Digital signature as binary string
	 * @return (string) Timestamped digital signature
	 * @protected
	 * @author Richard Stockinger
	 * @since 6.0.090 (2014-06-16)
	 */
	protected function applyTSA($signature) {
		if (!$this->tsa_timestamp) {
			return $signature;
		}
		//@TODO: implement this feature
		return $signature;
	}

}
