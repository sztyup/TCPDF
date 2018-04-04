<?php
//============================================================+
// File name   : tcpdf.php
// Version     : 6.2.13
// Begin       : 2002-08-03
// Last Update : 2015-06-18
// Author      : Nicola Asuni - Tecnick.com LTD - www.tecnick.com - info@tecnick.com
// License     : GNU-LGPL v3 (http://www.gnu.org/copyleft/lesser.html)
// -------------------------------------------------------------------
// Copyright (C) 2002-2015 Nicola Asuni - Tecnick.com LTD
//
// This file is part of TCPDF software library.
//
// TCPDF is free software: you can redistribute it and/or modify it
// under the terms of the GNU Lesser General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// TCPDF is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// See the GNU Lesser General Public License for more details.
//
// You should have received a copy of the License
// along with TCPDF. If not, see
// <http://www.tecnick.com/pagefiles/tcpdf/LICENSE.TXT>.
//
// See LICENSE.TXT file for more information.
// -------------------------------------------------------------------
//
// Description :
//   This is a PHP class for generating PDF documents without requiring external extensions.
//
// NOTE:
//   This class was originally derived in 2002 from the Public
//   Domain FPDF class by Olivier Plathey (http://www.fpdf.org),
//   but now is almost entirely rewritten and contains thousands of
//   new lines of code and hundreds new features.

//============================================================+

/**
 * @file
 * This is a PHP class for generating PDF documents without requiring external extensions.<br>
 * TCPDF project (http://www.tcpdf.org) was originally derived in 2002 from the Public Domain FPDF class by Olivier Plathey (http://www.fpdf.org), but now is almost entirely rewritten.<br>
 * @package com.tecnick.tcpdf
 * @author Nicola Asuni
 * @version 6.2.8
 */

// TCPDF configuration
require_once(dirname(__FILE__).'/tcpdf_autoconfig.php');
// TCPDF static font methods and data
require_once(dirname(__FILE__).'/include/tcpdf_font_data.php');
// TCPDF static font methods and data
require_once(dirname(__FILE__).'/include/tcpdf_fonts.php');
// TCPDF static color methods and data
require_once(dirname(__FILE__).'/include/tcpdf_colors.php');
// TCPDF static image methods and data
require_once(dirname(__FILE__).'/include/tcpdf_images.php');
// TCPDF static methods and data
require_once(dirname(__FILE__).'/include/tcpdf_static.php');

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

/**
 * @class TCPDF
 * PHP class for generating PDF documents without requiring external extensions.
 * TCPDF project (http://www.tcpdf.org) has been originally derived in 2002 from the Public Domain FPDF class by Olivier Plathey (http://www.fpdf.org), but now is almost entirely rewritten.<br>
 * @package com.tecnick.tcpdf
 * @brief PHP class for generating PDF documents without requiring external extensions.
 * @version 6.2.8
 * @author Nicola Asuni - info@tecnick.com
 */
class TCPDF {

	// Protected properties

	/**
	 * Current page number.
	 * @protected
	 */
	protected $page;

	/**
	 * Current object number.
	 * @protected
	 */
	protected $n;

	/**
	 * Array of object offsets.
	 * @protected
	 */
	protected $offsets = array();

	/**
	 * Array of object IDs for each page.
	 * @protected
	 */
	protected $pageobjects = array();

	/**
	 * Buffer holding in-memory PDF.
	 * @protected
	 */
	protected $buffer;

	/**
	 * Array containing pages.
	 * @protected
	 */
	protected $pages = array();

	/**
	 * Current document state.
	 * @protected
	 */
	protected $state;

	/**
	 * Compression flag.
	 * @protected
	 */
	protected $compress;

	/**
	 * Current page orientation (P = Portrait, L = Landscape).
	 * @protected
	 */
	protected $CurOrientation;

	/**
	 * Page dimensions.
	 * @protected
	 */
	protected $pagedim = array();

	/**
	 * Scale factor (number of points in user unit).
	 * @protected
	 */
	protected $k;

	/**
	 * Width of page format in points.
	 * @protected
	 */
	protected $fwPt;

	/**
	 * Height of page format in points.
	 * @protected
	 */
	protected $fhPt;

	/**
	 * Current width of page in points.
	 * @protected
	 */
	protected $wPt;

	/**
	 * Current height of page in points.
	 * @protected
	 */
	protected $hPt;

	/**
	 * Current width of page in user unit.
	 * @protected
	 */
	protected $w;

	/**
	 * Current height of page in user unit.
	 * @protected
	 */
	protected $h;

	/**
	 * Left margin.
	 * @protected
	 */
	protected $lMargin;

	/**
	 * Right margin.
	 * @protected
	 */
	protected $rMargin;

	/**
	 * Cell left margin (used by regions).
	 * @protected
	 */
	protected $clMargin;

	/**
	 * Cell right margin (used by regions).
	 * @protected
	 */
	protected $crMargin;

	/**
	 * Top margin.
	 * @protected
	 */
	protected $tMargin;

	/**
	 * Page break margin.
	 * @protected
	 */
	protected $bMargin;

	/**
	 * Array of cell internal paddings ('T' => top, 'R' => right, 'B' => bottom, 'L' => left).
	 * @since 5.9.000 (2010-10-03)
	 * @protected
	 */
	protected $cell_padding = array('T' => 0, 'R' => 0, 'B' => 0, 'L' => 0);

	/**
	 * Array of cell margins ('T' => top, 'R' => right, 'B' => bottom, 'L' => left).
	 * @since 5.9.000 (2010-10-04)
	 * @protected
	 */
	protected $cell_margin = array('T' => 0, 'R' => 0, 'B' => 0, 'L' => 0);

	/**
	 * Current horizontal position in user unit for cell positioning.
	 * @protected
	 */
	protected $x;

	/**
	 * Current vertical position in user unit for cell positioning.
	 * @protected
	 */
	protected $y;

	/**
	 * Height of last cell printed.
	 * @protected
	 */
	protected $lasth;

	/**
	 * Line width in user unit.
	 * @protected
	 */
	protected $LineWidth;

	/**
	 * Array of standard font names.
	 * @protected
	 */
	protected $CoreFonts;

	/**
	 * Array of used fonts.
	 * @protected
	 */
	protected $fonts = array();

	/**
	 * Array of font files.
	 * @protected
	 */
	protected $FontFiles = array();

	/**
	 * Array of encoding differences.
	 * @protected
	 */
	protected $diffs = array();

	/**
	 * Array of used images.
	 * @protected
	 */
	protected $images = array();

	/**
	 * Depth of the svg tag, to keep track if the svg tag is a subtag or the root tag.
	 * @protected
	 */
	protected $svg_tag_depth = 0;

	/**
	 * Array of Annotations in pages.
	 * @protected
	 */
	protected $PageAnnots = array();

	/**
	 * Array of internal links.
	 * @protected
	 */
	protected $links = array();

	/**
	 * Current font family.
	 * @protected
	 */
	protected $FontFamily;

	/**
	 * Current font style.
	 * @protected
	 */
	protected $FontStyle;

	/**
	 * Current font ascent (distance between font top and baseline).
	 * @protected
	 * @since 2.8.000 (2007-03-29)
	 */
	protected $FontAscent;

	/**
	 * Current font descent (distance between font bottom and baseline).
	 * @protected
	 * @since 2.8.000 (2007-03-29)
	 */
	protected $FontDescent;

	/**
	 * Underlining flag.
	 * @protected
	 */
	protected $underline;

	/**
	 * Overlining flag.
	 * @protected
	 */
	protected $overline;

	/**
	 * Current font info.
	 * @protected
	 */
	protected $CurrentFont;

	/**
	 * Current font size in points.
	 * @protected
	 */
	protected $FontSizePt;

	/**
	 * Current font size in user unit.
	 * @protected
	 */
	protected $FontSize;

	/**
	 * Commands for drawing color.
	 * @protected
	 */
	protected $DrawColor;

	/**
	 * Commands for filling color.
	 * @protected
	 */
	protected $FillColor;

	/**
	 * Commands for text color.
	 * @protected
	 */
	protected $TextColor;

	/**
	 * Indicates whether fill and text colors are different.
	 * @protected
	 */
	protected $ColorFlag;

	/**
	 * Automatic page breaking.
	 * @protected
	 */
	protected $AutoPageBreak;

	/**
	 * Threshold used to trigger page breaks.
	 * @protected
	 */
	protected $PageBreakTrigger;

	/**
	 * Flag set when processing page header.
	 * @protected
	 */
	protected $InHeader = false;

	/**
	 * Flag set when processing page footer.
	 * @protected
	 */
	protected $InFooter = false;

	/**
	 * Zoom display mode.
	 * @protected
	 */
	protected $ZoomMode;

	/**
	 * Layout display mode.
	 * @protected
	 */
	protected $LayoutMode;

	/**
	 * If true set the document information dictionary in Unicode.
	 * @protected
	 */
	protected $docinfounicode = true;

	/**
	 * Document title.
	 * @protected
	 */
	protected $title = '';

	/**
	 * Document subject.
	 * @protected
	 */
	protected $subject = '';

	/**
	 * Document author.
	 * @protected
	 */
	protected $author = '';

	/**
	 * Document keywords.
	 * @protected
	 */
	protected $keywords = '';

	/**
	 * Document creator.
	 * @protected
	 */
	protected $creator = '';

	/**
	 * Starting page number.
	 * @protected
	 */
	protected $starting_page_number = 1;

	/**
	 * The right-bottom (or left-bottom for RTL) corner X coordinate of last inserted image.
	 * @since 2002-07-31
	 * @author Nicola Asuni
	 * @protected
	 */
	protected $img_rb_x;

	/**
	 * The right-bottom corner Y coordinate of last inserted image.
	 * @since 2002-07-31
	 * @author Nicola Asuni
	 * @protected
	 */
	protected $img_rb_y;

	/**
	 * Adjusting factor to convert pixels to user units.
	 * @since 2004-06-14
	 * @author Nicola Asuni
	 * @protected
	 */
	protected $imgscale = 1;

	/**
	 * Boolean flag set to true when the input text is unicode (require unicode fonts).
	 * @since 2005-01-02
	 * @author Nicola Asuni
	 * @protected
	 */
	protected $isunicode = false;

	/**
	 * PDF version.
	 * @since 1.5.3
	 * @protected
	 */
	protected $PDFVersion = '1.7';

	/**
	 * ID of the stored default header template (-1 = not set).
	 * @protected
	 */
	protected $header_xobjid = false;

	/**
	 * If true reset the Header Xobject template at each page
	 * @protected
	 */
	protected $header_xobj_autoreset = false;

	/**
	 * Minimum distance between header and top page margin.
	 * @protected
	 */
	protected $header_margin;

	/**
	 * Minimum distance between footer and bottom page margin.
	 * @protected
	 */
	protected $footer_margin;

	/**
	 * Original left margin value.
	 * @protected
	 * @since 1.53.0.TC013
	 */
	protected $original_lMargin;

	/**
	 * Original right margin value.
	 * @protected
	 * @since 1.53.0.TC013
	 */
	protected $original_rMargin;

	/**
	 * Default font used on page header.
	 * @protected
	 */
	protected $header_font;

	/**
	 * Default font used on page footer.
	 * @protected
	 */
	protected $footer_font;

	/**
	 * Language templates.
	 * @protected
	 */
	protected $l;

	/**
	 * Boolean flag to print/hide page header.
	 * @protected
	 */
	protected $print_header = true;

	/**
	 * Boolean flag to print/hide page footer.
	 * @protected
	 */
	protected $print_footer = true;

	/**
	 * Header image logo.
	 * @protected
	 */
	protected $header_logo = '';

	/**
	 * Width of header image logo in user units.
	 * @protected
	 */
	protected $header_logo_width = 30;

	/**
	 * Title to be printed on default page header.
	 * @protected
	 */
	protected $header_title = '';

	/**
	 * String to pring on page header after title.
	 * @protected
	 */
	protected $header_string = '';

	/**
	 * Color for header text (RGB array).
	 * @since 5.9.174 (2012-07-25)
	 * @protected
	 */
	protected $header_text_color = array(0,0,0);

	/**
	 * Color for header line (RGB array).
	 * @since 5.9.174 (2012-07-25)
	 * @protected
	 */
	protected $header_line_color = array(0,0,0);

	/**
	 * Color for footer text (RGB array).
	 * @since 5.9.174 (2012-07-25)
	 * @protected
	 */
	protected $footer_text_color = array(0,0,0);

	/**
	 * Color for footer line (RGB array).
	 * @since 5.9.174 (2012-07-25)
	 * @protected
	 */
	protected $footer_line_color = array(0,0,0);

	/**
	 * Text shadow data array.
	 * @since 5.9.174 (2012-07-25)
	 * @protected
	 */
	protected $txtshadow = array('enabled'=>false, 'depth_w'=>0, 'depth_h'=>0, 'color'=>false, 'opacity'=>1, 'blend_mode'=>'Normal');

	/**
	 * Default number of columns for html table.
	 * @protected
	 */
	protected $default_table_columns = 4;

	// variables for html parser

	/**
	 * HTML PARSER: array to store current link and rendering styles.
	 * @protected
	 */
	protected $HREF = array();

	/**
	 * List of available fonts on filesystem.
	 * @protected
	 */
	protected $fontlist = array();

	/**
	 * Current foreground color.
	 * @protected
	 */
	protected $fgcolor;

	/**
	 * HTML PARSER: array of boolean values, true in case of ordered list (OL), false otherwise.
	 * @protected
	 */
	protected $listordered = array();

	/**
	 * HTML PARSER: array count list items on nested lists.
	 * @protected
	 */
	protected $listcount = array();

	/**
	 * HTML PARSER: current list nesting level.
	 * @protected
	 */
	protected $listnum = 0;

	/**
	 * HTML PARSER: indent amount for lists.
	 * @protected
	 */
	protected $listindent = 0;

	/**
	 * HTML PARSER: current list indententation level.
	 * @protected
	 */
	protected $listindentlevel = 0;

	/**
	 * Current background color.
	 * @protected
	 */
	protected $bgcolor;

	/**
	 * Temporary font size in points.
	 * @protected
	 */
	protected $tempfontsize = 10;

	/**
	 * Spacer string for LI tags.
	 * @protected
	 */
	protected $lispacer = '';

	/**
	 * Default encoding.
	 * @protected
	 * @since 1.53.0.TC010
	 */
	protected $encoding = 'UTF-8';

	/**
	 * PHP internal encoding.
	 * @protected
	 * @since 1.53.0.TC016
	 */
	protected $internal_encoding;

	/**
	 * Boolean flag to indicate if the document language is Right-To-Left.
	 * @protected
	 * @since 2.0.000
	 */
	protected $rtl = false;

	/**
	 * Boolean flag used to force RTL or LTR string direction.
	 * @protected
	 * @since 2.0.000
	 */
	protected $tmprtl = false;

	// --- bookmark ---

	/**
	 * Outlines for bookmark.
	 * @protected
	 * @since 2.1.002 (2008-02-12)
	 */
	protected $outlines = array();

	/**
	 * Outline root for bookmark.
	 * @protected
	 * @since 2.1.002 (2008-02-12)
	 */
	protected $OutlineRoot;

	// --- javascript and form ---

	/**
	 * Javascript code.
	 * @protected
	 * @since 2.1.002 (2008-02-12)
	 */
	protected $javascript = '';

	/**
	 * Javascript counter.
	 * @protected
	 * @since 2.1.002 (2008-02-12)
	 */
	protected $n_js;

	/**
	 * line through state
	 * @protected
	 * @since 2.8.000 (2008-03-19)
	 */
	protected $linethrough;

	/**
	 * Array with additional document-wide usage rights for the document.
	 * @protected
	 * @since 5.8.014 (2010-08-23)
	 */
	protected $ur = array();

	/**
	 * DPI (Dot Per Inch) Document Resolution (do not change).
	 * @protected
	 * @since 3.0.000 (2008-03-27)
	 */
	protected $dpi = 72;

	/**
	 * Array of page numbers were a new page group was started (the page numbers are the keys of the array).
	 * @protected
	 * @since 3.0.000 (2008-03-27)
	 */
	protected $newpagegroup = array();

	/**
	 * Array that contains the number of pages in each page group.
	 * @protected
	 * @since 3.0.000 (2008-03-27)
	 */
	protected $pagegroups = array();

	/**
	 * Current page group number.
	 * @protected
	 * @since 3.0.000 (2008-03-27)
	 */
	protected $currpagegroup = 0;

	/**
	 * Array of transparency objects and parameters.
	 * @protected
	 * @since 3.0.000 (2008-03-27)
	 */
	protected $extgstates;

	/**
	 * Set the default JPEG compression quality (1-100).
	 * @protected
	 * @since 3.0.000 (2008-03-27)
	 */
	protected $jpeg_quality;

	/**
	 * Default cell height ratio.
	 * @protected
	 * @since 3.0.014 (2008-05-23)
	 */
	protected $cell_height_ratio = K_CELL_HEIGHT_RATIO;

	/**
	 * PDF viewer preferences.
	 * @protected
	 * @since 3.1.000 (2008-06-09)
	 */
	protected $viewer_preferences;

	/**
	 * A name object specifying how the document should be displayed when opened.
	 * @protected
	 * @since 3.1.000 (2008-06-09)
	 */
	protected $PageMode;

	/**
	 * Array for storing gradient information.
	 * @protected
	 * @since 3.1.000 (2008-06-09)
	 */
	protected $gradients = array();

	/**
	 * Array used to store positions inside the pages buffer (keys are the page numbers).
	 * @protected
	 * @since 3.2.000 (2008-06-26)
	 */
	protected $intmrk = array();

	/**
	 * Array used to store positions inside the pages buffer (keys are the page numbers).
	 * @protected
	 * @since 5.7.000 (2010-08-03)
	 */
	protected $bordermrk = array();

	/**
	 * Array used to store page positions to track empty pages (keys are the page numbers).
	 * @protected
	 * @since 5.8.007 (2010-08-18)
	 */
	protected $emptypagemrk = array();

	/**
	 * Array used to store content positions inside the pages buffer (keys are the page numbers).
	 * @protected
	 * @since 4.6.021 (2009-07-20)
	 */
	protected $cntmrk = array();

	/**
	 * Array used to store footer positions of each page.
	 * @protected
	 * @since 3.2.000 (2008-07-01)
	 */
	protected $footerpos = array();

	/**
	 * Array used to store footer length of each page.
	 * @protected
	 * @since 4.0.014 (2008-07-29)
	 */
	protected $footerlen = array();

	/**
	 * Boolean flag to indicate if a new line is created.
	 * @protected
	 * @since 3.2.000 (2008-07-01)
	 */
	protected $newline = true;

	/**
	 * End position of the latest inserted line.
	 * @protected
	 * @since 3.2.000 (2008-07-01)
	 */
	protected $endlinex = 0;

	/**
	 * PDF string for width value of the last line.
	 * @protected
	 * @since 4.0.006 (2008-07-16)
	 */
	protected $linestyleWidth = '';

	/**
	 * PDF string for CAP value of the last line.
	 * @protected
	 * @since 4.0.006 (2008-07-16)
	 */
	protected $linestyleCap = '0 J';

	/**
	 * PDF string for join value of the last line.
	 * @protected
	 * @since 4.0.006 (2008-07-16)
	 */
	protected $linestyleJoin = '0 j';

	/**
	 * PDF string for dash value of the last line.
	 * @protected
	 * @since 4.0.006 (2008-07-16)
	 */
	protected $linestyleDash = '[] 0 d';

	/**
	 * Boolean flag to indicate if marked-content sequence is open.
	 * @protected
	 * @since 4.0.013 (2008-07-28)
	 */
	protected $openMarkedContent = false;

	/**
	 * Count the latest inserted vertical spaces on HTML.
	 * @protected
	 * @since 4.0.021 (2008-08-24)
	 */
	protected $htmlvspace = 0;

	/**
	 * Array of Spot colors.
	 * @protected
	 * @since 4.0.024 (2008-09-12)
	 */
	protected $spot_colors = array();

	/**
	 * Symbol used for HTML unordered list items.
	 * @protected
	 * @since 4.0.028 (2008-09-26)
	 */
	protected $lisymbol = '';

	/**
	 * String used to mark the beginning and end of EPS image blocks.
	 * @protected
	 * @since 4.1.000 (2008-10-18)
	 */
	protected $epsmarker = 'x#!#EPS#!#x';

	/**
	 * Array of transformation matrix.
	 * @protected
	 * @since 4.2.000 (2008-10-29)
	 */
	protected $transfmatrix = array();

	/**
	 * Current key for transformation matrix.
	 * @protected
	 * @since 4.8.005 (2009-09-17)
	 */
	protected $transfmatrix_key = 0;

	/**
	 * Booklet mode for double-sided pages.
	 * @protected
	 * @since 4.2.000 (2008-10-29)
	 */
	protected $booklet = false;

	/**
	 * Epsilon value used for float calculations.
	 * @protected
	 * @since 4.2.000 (2008-10-29)
	 */
	protected $feps = 0.005;

	/**
	 * Array used for custom vertical spaces for HTML tags.
	 * @protected
	 * @since 4.2.001 (2008-10-30)
	 */
	protected $tagvspaces = array();

	/**
	 * HTML PARSER: custom indent amount for lists. Negative value means disabled.
	 * @protected
	 * @since 4.2.007 (2008-11-12)
	 */
	protected $customlistindent = -1;

	/**
	 * Boolean flag to indicate if the border of the cell sides that cross the page should be removed.
	 * @protected
	 * @since 4.2.010 (2008-11-14)
	 */
	protected $opencell = true;

	/**
	 * Array of files to embedd.
	 * @protected
	 * @since 4.4.000 (2008-12-07)
	 */
	protected $embeddedfiles = array();

	/**
	 * Boolean flag to indicate if we are inside a PRE tag.
	 * @protected
	 * @since 4.4.001 (2008-12-08)
	 */
	protected $premode = false;

	/**
	 * Array used to store positions of graphics transformation blocks inside the page buffer.
	 * keys are the page numbers
	 * @protected
	 * @since 4.4.002 (2008-12-09)
	 */
	protected $transfmrk = array();

	/**
	 * Default color for html links.
	 * @protected
	 * @since 4.4.003 (2008-12-09)
	 */
	protected $htmlLinkColorArray = array(0, 0, 255);

	/**
	 * Default font style to add to html links.
	 * @protected
	 * @since 4.4.003 (2008-12-09)
	 */
	protected $htmlLinkFontStyle = 'U';

	/**
	 * Counts the number of pages.
	 * @protected
	 * @since 4.5.000 (2008-12-31)
	 */
	protected $numpages = 0;

	/**
	 * Array containing page lengths in bytes.
	 * @protected
	 * @since 4.5.000 (2008-12-31)
	 */
	protected $pagelen = array();

	/**
	 * Counts the number of pages.
	 * @protected
	 * @since 4.5.000 (2008-12-31)
	 */
	protected $numimages = 0;

	/**
	 * Store the image keys.
	 * @protected
	 * @since 4.5.000 (2008-12-31)
	 */
	protected $imagekeys = array();

	/**
	 * Length of the buffer in bytes.
	 * @protected
	 * @since 4.5.000 (2008-12-31)
	 */
	protected $bufferlen = 0;

	/**
	 * Counts the number of fonts.
	 * @protected
	 * @since 4.5.000 (2009-01-02)
	 */
	protected $numfonts = 0;

	/**
	 * Store the font keys.
	 * @protected
	 * @since 4.5.000 (2009-01-02)
	 */
	protected $fontkeys = array();

	/**
	 * Store the font object IDs.
	 * @protected
	 * @since 4.8.001 (2009-09-09)
	 */
	protected $font_obj_ids = array();

	/**
	 * Store the fage status (true when opened, false when closed).
	 * @protected
	 * @since 4.5.000 (2009-01-02)
	 */
	protected $pageopen = array();

	/**
	 * Default monospace font.
	 * @protected
	 * @since 4.5.025 (2009-03-10)
	 */
	protected $default_monospaced_font = 'courier';

	/**
	 * Cloned copy of the current class object.
	 * @protected
	 * @since 4.5.029 (2009-03-19)
	 */
	protected $objcopy;

	/**
	 * Array used to store the lengths of cache files.
	 * @protected
	 * @since 4.5.029 (2009-03-19)
	 */
	protected $cache_file_length = array();

	/**
	 * Table header content to be repeated on each new page.
	 * @protected
	 * @since 4.5.030 (2009-03-20)
	 */
	protected $thead = '';

	/**
	 * Margins used for table header.
	 * @protected
	 * @since 4.5.030 (2009-03-20)
	 */
	protected $theadMargins = array();

	/**
	 * Boolean flag to enable document digital signature.
	 * @protected
	 * @since 4.6.005 (2009-04-24)
	 */
	protected $sign = false;

	/**
	 * Digital signature data.
	 * @protected
	 * @since 4.6.005 (2009-04-24)
	 */
	protected $signature_data = array();

	/**
	 * Digital signature max length.
	 * @protected
	 * @since 4.6.005 (2009-04-24)
	 */
	protected $signature_max_length = 11742;

	/**
	 * Data for digital signature appearance.
	 * @protected
	 * @since 5.3.011 (2010-06-16)
	 */
	protected $signature_appearance = array('page' => 1, 'rect' => '0 0 0 0');

	/**
	 * Array of empty digital signature appearances.
	 * @protected
	 * @since 5.9.101 (2011-07-06)
	 */
	protected $empty_signature_appearance = array();

	/**
	 * Boolean flag to enable document timestamping with TSA.
	 * @protected
	 * @since 6.0.085 (2014-06-19)
	 */
	protected $tsa_timestamp = false;

	/**
	 * Timestamping data.
	 * @protected
	 * @since 6.0.085 (2014-06-19)
	 */
	protected $tsa_data = array();

	/**
	 * Regular expression used to find blank characters (required for word-wrapping).
	 * @protected
	 * @since 4.6.006 (2009-04-28)
	 */
	protected $re_spaces = '/[^\S\xa0]/';

	/**
	 * Array of $re_spaces parts.
	 * @protected
	 * @since 5.5.011 (2010-07-09)
	 */
	protected $re_space = array('p' => '[^\S\xa0]', 'm' => '');

	/**
	 * Digital signature object ID.
	 * @protected
	 * @since 4.6.022 (2009-06-23)
	 */
	protected $sig_obj_id = 0;

	/**
	 * ID of page objects.
	 * @protected
	 * @since 4.7.000 (2009-08-29)
	 */
	protected $page_obj_id = array();

	/**
	 * List of form annotations IDs.
	 * @protected
	 * @since 4.8.000 (2009-09-07)
	 */
	protected $form_obj_id = array();

	/**
	 * Deafult Javascript field properties. Possible values are described on official Javascript for Acrobat API reference. Annotation options can be directly specified using the 'aopt' entry.
	 * @protected
	 * @since 4.8.000 (2009-09-07)
	 */
	protected $default_form_prop = array('lineWidth'=>1, 'borderStyle'=>'solid', 'fillColor'=>array(255, 255, 255), 'strokeColor'=>array(128, 128, 128));

	/**
	 * Javascript objects array.
	 * @protected
	 * @since 4.8.000 (2009-09-07)
	 */
	protected $js_objects = array();

	/**
	 * Current form action (used during XHTML rendering).
	 * @protected
	 * @since 4.8.000 (2009-09-07)
	 */
	protected $form_action = '';

	/**
	 * Current form encryption type (used during XHTML rendering).
	 * @protected
	 * @since 4.8.000 (2009-09-07)
	 */
	protected $form_enctype = 'application/x-www-form-urlencoded';

	/**
	 * Current method to submit forms.
	 * @protected
	 * @since 4.8.000 (2009-09-07)
	 */
	protected $form_mode = 'post';

	/**
	 * List of fonts used on form fields (fontname => fontkey).
	 * @protected
	 * @since 4.8.001 (2009-09-09)
	 */
	protected $annotation_fonts = array();

	/**
	 * List of radio buttons parent objects.
	 * @protected
	 * @since 4.8.001 (2009-09-09)
	 */
	protected $radiobutton_groups = array();

	/**
	 * List of radio group objects IDs.
	 * @protected
	 * @since 4.8.001 (2009-09-09)
	 */
	protected $radio_groups = array();

	/**
	 * Text indentation value (used for text-indent CSS attribute).
	 * @protected
	 * @since 4.8.006 (2009-09-23)
	 */
	protected $textindent = 0;

	/**
	 * Store page number when startTransaction() is called.
	 * @protected
	 * @since 4.8.006 (2009-09-23)
	 */
	protected $start_transaction_page = 0;

	/**
	 * Store Y position when startTransaction() is called.
	 * @protected
	 * @since 4.9.001 (2010-03-28)
	 */
	protected $start_transaction_y = 0;

	/**
	 * True when we are printing the thead section on a new page.
	 * @protected
	 * @since 4.8.027 (2010-01-25)
	 */
	protected $inthead = false;

	/**
	 * Array of column measures (width, space, starting Y position).
	 * @protected
	 * @since 4.9.001 (2010-03-28)
	 */
	protected $columns = array();

	/**
	 * Number of colums.
	 * @protected
	 * @since 4.9.001 (2010-03-28)
	 */
	protected $num_columns = 1;

	/**
	 * Current column number.
	 * @protected
	 * @since 4.9.001 (2010-03-28)
	 */
	protected $current_column = 0;

	/**
	 * Starting page for columns.
	 * @protected
	 * @since 4.9.001 (2010-03-28)
	 */
	protected $column_start_page = 0;

	/**
	 * Maximum page and column selected.
	 * @protected
	 * @since 5.8.000 (2010-08-11)
	 */
	protected $maxselcol = array('page' => 0, 'column' => 0);

	/**
	 * Array of: X difference between table cell x start and starting page margin, cellspacing, cellpadding.
	 * @protected
	 * @since 5.8.000 (2010-08-11)
	 */
	protected $colxshift = array('x' => 0, 's' => array('H' => 0, 'V' => 0), 'p' => array('L' => 0, 'T' => 0, 'R' => 0, 'B' => 0));

	/**
	 * Text rendering mode: 0 = Fill text; 1 = Stroke text; 2 = Fill, then stroke text; 3 = Neither fill nor stroke text (invisible); 4 = Fill text and add to path for clipping; 5 = Stroke text and add to path for clipping; 6 = Fill, then stroke text and add to path for clipping; 7 = Add text to path for clipping.
	 * @protected
	 * @since 4.9.008 (2010-04-03)
	 */
	protected $textrendermode = 0;

	/**
	 * Text stroke width in doc units.
	 * @protected
	 * @since 4.9.008 (2010-04-03)
	 */
	protected $textstrokewidth = 0;

	/**
	 * Current stroke color.
	 * @protected
	 * @since 4.9.008 (2010-04-03)
	 */
	protected $strokecolor;

	/**
	 * Default unit of measure for document.
	 * @protected
	 * @since 5.0.000 (2010-04-22)
	 */
	protected $pdfunit = 'mm';

	/**
	 * Boolean flag true when we are on TOC (Table Of Content) page.
	 * @protected
	 */
	protected $tocpage = false;

	/**
	 * Boolean flag: if true convert vector images (SVG, EPS) to raster image using GD or ImageMagick library.
	 * @protected
	 * @since 5.0.000 (2010-04-26)
	 */
	protected $rasterize_vector_images = false;

	/**
	 * Boolean flag: if true enables font subsetting by default.
	 * @protected
	 * @since 5.3.002 (2010-06-07)
	 */
	protected $font_subsetting = true;

	/**
	 * Array of default graphic settings.
	 * @protected
	 * @since 5.5.008 (2010-07-02)
	 */
	protected $default_graphic_vars = array();

	/**
	 * Array of XObjects.
	 * @protected
	 * @since 5.8.014 (2010-08-23)
	 */
	protected $xobjects = array();

	/**
	 * Boolean value true when we are inside an XObject.
	 * @protected
	 * @since 5.8.017 (2010-08-24)
	 */
	protected $inxobj = false;

	/**
	 * Current XObject ID.
	 * @protected
	 * @since 5.8.017 (2010-08-24)
	 */
	protected $xobjid = '';

	/**
	 * Percentage of character stretching.
	 * @protected
	 * @since 5.9.000 (2010-09-29)
	 */
	protected $font_stretching = 100;

	/**
	 * Increases or decreases the space between characters in a text by the specified amount (tracking).
	 * @protected
	 * @since 5.9.000 (2010-09-29)
	 */
	protected $font_spacing = 0;

	/**
	 * Array of no-write regions.
	 * ('page' => page number or empy for current page, 'xt' => X top, 'yt' => Y top, 'xb' => X bottom, 'yb' => Y bottom, 'side' => page side 'L' = left or 'R' = right)
	 * @protected
	 * @since 5.9.003 (2010-10-14)
	 */
	protected $page_regions = array();

	/**
	 * Boolean value true when page region check is active.
	 * @protected
	 */
	protected $check_page_regions = true;

	/**
	 * Array of PDF layers data.
	 * @protected
	 * @since 5.9.102 (2011-07-13)
	 */
	protected $pdflayers = array();

	/**
	 * A dictionary of names and corresponding destinations (Dests key on document Catalog).
	 * @protected
	 * @since 5.9.097 (2011-06-23)
	 */
	protected $dests = array();

	/**
	 * Object ID for Named Destinations
	 * @protected
	 * @since 5.9.097 (2011-06-23)
	 */
	protected $n_dests;

	/**
	 * Embedded Files Names
	 * @protected
	 * @since 5.9.204 (2013-01-23)
	 */
	protected $efnames = array();

	/**
	 * Directory used for the last SVG image.
	 * @protected
	 * @since 5.0.000 (2010-05-05)
	 */
	protected $svgdir = '';

	/**
	 *  Deafult unit of measure for SVG.
	 * @protected
	 * @since 5.0.000 (2010-05-02)
	 */
	protected $svgunit = 'px';

	/**
	 * Array of SVG gradients.
	 * @protected
	 * @since 5.0.000 (2010-05-02)
	 */
	protected $svggradients = array();

	/**
	 * ID of last SVG gradient.
	 * @protected
	 * @since 5.0.000 (2010-05-02)
	 */
	protected $svggradientid = 0;

	/**
	 * Boolean value true when in SVG defs group.
	 * @protected
	 * @since 5.0.000 (2010-05-02)
	 */
	protected $svgdefsmode = false;

	/**
	 * Array of SVG defs.
	 * @protected
	 * @since 5.0.000 (2010-05-02)
	 */
	protected $svgdefs = array();

	/**
	 * Boolean value true when in SVG clipPath tag.
	 * @protected
	 * @since 5.0.000 (2010-04-26)
	 */
	protected $svgclipmode = false;

	/**
	 * Array of SVG clipPath commands.
	 * @protected
	 * @since 5.0.000 (2010-05-02)
	 */
	protected $svgclippaths = array();

	/**
	 * Array of SVG clipPath tranformation matrix.
	 * @protected
	 * @since 5.8.022 (2010-08-31)
	 */
	protected $svgcliptm = array();

	/**
	 * ID of last SVG clipPath.
	 * @protected
	 * @since 5.0.000 (2010-05-02)
	 */
	protected $svgclipid = 0;

	/**
	 * SVG text.
	 * @protected
	 * @since 5.0.000 (2010-05-02)
	 */
	protected $svgtext = '';

	/**
	 * SVG text properties.
	 * @protected
	 * @since 5.8.013 (2010-08-23)
	 */
	protected $svgtextmode = array();

	/**
	 * Array of SVG properties.
	 * @protected
	 * @since 5.0.000 (2010-05-02)
	 */
	protected $svgstyles = array(array(
		'alignment-baseline' => 'auto',
		'baseline-shift' => 'baseline',
		'clip' => 'auto',
		'clip-path' => 'none',
		'clip-rule' => 'nonzero',
		'color' => 'black',
		'color-interpolation' => 'sRGB',
		'color-interpolation-filters' => 'linearRGB',
		'color-profile' => 'auto',
		'color-rendering' => 'auto',
		'cursor' => 'auto',
		'direction' => 'ltr',
		'display' => 'inline',
		'dominant-baseline' => 'auto',
		'enable-background' => 'accumulate',
		'fill' => 'black',
		'fill-opacity' => 1,
		'fill-rule' => 'nonzero',
		'filter' => 'none',
		'flood-color' => 'black',
		'flood-opacity' => 1,
		'font' => '',
		'font-family' => 'helvetica',
		'font-size' => 'medium',
		'font-size-adjust' => 'none',
		'font-stretch' => 'normal',
		'font-style' => 'normal',
		'font-variant' => 'normal',
		'font-weight' => 'normal',
		'glyph-orientation-horizontal' => '0deg',
		'glyph-orientation-vertical' => 'auto',
		'image-rendering' => 'auto',
		'kerning' => 'auto',
		'letter-spacing' => 'normal',
		'lighting-color' => 'white',
		'marker' => '',
		'marker-end' => 'none',
		'marker-mid' => 'none',
		'marker-start' => 'none',
		'mask' => 'none',
		'opacity' => 1,
		'overflow' => 'auto',
		'pointer-events' => 'visiblePainted',
		'shape-rendering' => 'auto',
		'stop-color' => 'black',
		'stop-opacity' => 1,
		'stroke' => 'none',
		'stroke-dasharray' => 'none',
		'stroke-dashoffset' => 0,
		'stroke-linecap' => 'butt',
		'stroke-linejoin' => 'miter',
		'stroke-miterlimit' => 4,
		'stroke-opacity' => 1,
		'stroke-width' => 1,
		'text-anchor' => 'start',
		'text-decoration' => 'none',
		'text-rendering' => 'auto',
		'unicode-bidi' => 'normal',
		'visibility' => 'visible',
		'word-spacing' => 'normal',
		'writing-mode' => 'lr-tb',
		'text-color' => 'black',
		'transfmatrix' => array(1, 0, 0, 1, 0, 0)
		));

	/**
	 * If true force sRGB color profile for all document.
	 * @protected
	 * @since 5.9.121 (2011-09-28)
	 */
	protected $force_srgb = false;

	/**
	 * If true set the document to PDF/A mode.
	 * @protected
	 * @since 5.9.121 (2011-09-27)
	 */
	protected $pdfa_mode = false;

	/**
	 * Document creation date-time
	 * @protected
	 * @since 5.9.152 (2012-03-22)
	 */
	protected $doc_creation_timestamp;

	/**
	 * Document modification date-time
	 * @protected
	 * @since 5.9.152 (2012-03-22)
	 */
	protected $doc_modification_timestamp;

	/**
	 * Custom XMP data.
	 * @protected
	 * @since 5.9.128 (2011-10-06)
	 */
	protected $custom_xmp = '';

	/**
	 * Overprint mode array.
	 * (Check the "Entries in a Graphics State Parameter Dictionary" on PDF 32000-1:2008).
	 * @protected
	 * @since 5.9.152 (2012-03-23)
	 */
	protected $overprint = array('OP' => false, 'op' => false, 'OPM' => 0);

	/**
	 * Alpha mode array.
	 * (Check the "Entries in a Graphics State Parameter Dictionary" on PDF 32000-1:2008).
	 * @protected
	 * @since 5.9.152 (2012-03-23)
	 */
	protected $alpha = array('CA' => 1, 'ca' => 1, 'BM' => '/Normal', 'AIS' => false);

	/**
	 * Define the page boundaries boxes to be set on document.
	 * @protected
	 * @since 5.9.152 (2012-03-23)
	 */
	protected $page_boxes = array('MediaBox', 'CropBox', 'BleedBox', 'TrimBox', 'ArtBox');

	/**
	 * If true print TCPDF meta link.
	 * @protected
	 * @since 5.9.152 (2012-03-23)
	 */
	protected $tcpdflink = true;

	/**
	 * Cache array for computed GD gamma values.
	 * @protected
	 * @since 5.9.1632 (2012-06-05)
	 */
	protected $gdgammacache = array();

	//------------------------------------------------------------
	// METHODS
	//------------------------------------------------------------

	/**
	 * This is the class constructor.
	 * It allows to set up the page format, the orientation and the measure unit used in all the methods (except for the font sizes).
	 *
	 * IMPORTANT: Please note that this method sets the mb_internal_encoding to ASCII, so if you are using the mbstring module functions with TCPDF you need to correctly set/unset the mb_internal_encoding when needed.
	 *
	 * @param $orientation (string) page orientation. Possible values are (case insensitive):<ul><li>P or Portrait (default)</li><li>L or Landscape</li><li>'' (empty string) for automatic orientation</li></ul>
	 * @param $unit (string) User measure unit. Possible values are:<ul><li>pt: point</li><li>mm: millimeter (default)</li><li>cm: centimeter</li><li>in: inch</li></ul><br />A point equals 1/72 of inch, that is to say about 0.35 mm (an inch being 2.54 cm). This is a very common unit in typography; font sizes are expressed in that unit.
	 * @param $format (mixed) The format used for pages. It can be either: one of the string values specified at getPageSizeFromFormat() or an array of parameters specified at setPageFormat().
	 * @param $unicode (boolean) TRUE means that the input text is unicode (default = true)
	 * @param $encoding (string) Charset encoding (used only when converting back html entities); default is UTF-8.
	 * @param $diskcache (boolean) DEPRECATED FEATURE
	 * @param $pdfa (boolean) If TRUE set the document to PDF/A mode.
	 * @public
	 * @see getPageSizeFromFormat(), setPageFormat()
	 */
	public function __construct($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false) {
		/* Set internal character encoding to ASCII */
		if (function_exists('mb_internal_encoding') AND mb_internal_encoding()) {
			$this->internal_encoding = mb_internal_encoding();
			mb_internal_encoding('ASCII');
		}
		// set file ID for trailer
		$serformat = (is_array($format) ? json_encode($format) : $format);
		$this->file_id = md5(TCPDF_STATIC::getRandomSeed('TCPDF'.$orientation.$unit.$serformat.$encoding));
		$this->font_obj_ids = array();
		$this->page_obj_id = array();
		$this->form_obj_id = array();
		// set pdf/a mode
		$this->pdfa_mode = $pdfa;
		$this->force_srgb = false;
		// set language direction
		$this->rtl = false;
		$this->tmprtl = false;
		// some checks
		$this->_dochecks();
		// initialization of properties
		$this->isunicode = $unicode;
		$this->page = 0;
		$this->transfmrk[0] = array();
		$this->pagedim = array();
		$this->n = 2;
		$this->buffer = '';
		$this->pages = array();
		$this->state = 0;
		$this->fonts = array();
		$this->FontFiles = array();
		$this->diffs = array();
		$this->images = array();
		$this->links = array();
		$this->gradients = array();
		$this->InFooter = false;
		$this->lasth = 0;
		$this->FontFamily = defined('PDF_FONT_NAME_MAIN')?PDF_FONT_NAME_MAIN:'helvetica';
		$this->FontStyle = '';
		$this->FontSizePt = 12;
		$this->underline = false;
		$this->overline = false;
		$this->linethrough = false;
		$this->DrawColor = '0 G';
		$this->FillColor = '0 g';
		$this->TextColor = '0 g';
		$this->ColorFlag = false;
		$this->pdflayers = array();
		// encryption values
		$this->encrypted = false;
		$this->last_enc_key = '';
		// standard Unicode fonts
		$this->CoreFonts = array(
			'courier'=>'Courier',
			'courierB'=>'Courier-Bold',
			'courierI'=>'Courier-Oblique',
			'courierBI'=>'Courier-BoldOblique',
			'helvetica'=>'Helvetica',
			'helveticaB'=>'Helvetica-Bold',
			'helveticaI'=>'Helvetica-Oblique',
			'helveticaBI'=>'Helvetica-BoldOblique',
			'times'=>'Times-Roman',
			'timesB'=>'Times-Bold',
			'timesI'=>'Times-Italic',
			'timesBI'=>'Times-BoldItalic',
			'symbol'=>'Symbol',
			'zapfdingbats'=>'ZapfDingbats'
		);
		// set scale factor
		$this->setPageUnit($unit);
		// set page format and orientation
		$this->setPageFormat($format, $orientation);
		// page margins (1 cm)
		$margin = 28.35 / $this->k;
		$this->SetMargins($margin, $margin);
		$this->clMargin = $this->lMargin;
		$this->crMargin = $this->rMargin;
		// internal cell padding
		$cpadding = $margin / 10;
		$this->setCellPaddings($cpadding, 0, $cpadding, 0);
		// cell margins
		$this->setCellMargins(0, 0, 0, 0);
		// line width (0.2 mm)
		$this->LineWidth = 0.57 / $this->k;
		$this->linestyleWidth = sprintf('%F w', ($this->LineWidth * $this->k));
		$this->linestyleCap = '0 J';
		$this->linestyleJoin = '0 j';
		$this->linestyleDash = '[] 0 d';
		// automatic page break
		$this->SetAutoPageBreak(true, (2 * $margin));
		// full width display mode
		$this->SetDisplayMode('fullwidth');
		// compression
		$this->SetCompression();
		// set default PDF version number
		$this->setPDFVersion();
		$this->tcpdflink = true;
		$this->encoding = $encoding;
		$this->HREF = array();
		$this->getFontsList();
		$this->fgcolor = array('R' => 0, 'G' => 0, 'B' => 0);
		$this->strokecolor = array('R' => 0, 'G' => 0, 'B' => 0);
		$this->bgcolor = array('R' => 255, 'G' => 255, 'B' => 255);
		$this->extgstates = array();
		$this->setTextShadow();
		// signature
		$this->sign = false;
		$this->tsa_timestamp = false;
		$this->tsa_data = array();
		$this->signature_appearance = array('page' => 1, 'rect' => '0 0 0 0', 'name' => 'Signature');
		$this->empty_signature_appearance = array();
		// user's rights
		$this->ur['enabled'] = false;
		$this->ur['document'] = '/FullSave';
		$this->ur['annots'] = '/Create/Delete/Modify/Copy/Import/Export';
		$this->ur['form'] = '/Add/Delete/FillIn/Import/Export/SubmitStandalone/SpawnTemplate';
		$this->ur['signature'] = '/Modify';
		$this->ur['ef'] = '/Create/Delete/Modify/Import';
		$this->ur['formex'] = '';
		// set default JPEG quality
		$this->jpeg_quality = 75;
		// initialize some settings
		TCPDF_FONTS::utf8Bidi(array(''), '', false, $this->isunicode, $this->CurrentFont);
		// set default font
		$this->SetFont($this->FontFamily, $this->FontStyle, $this->FontSizePt);
		$this->setHeaderFont(array($this->FontFamily, $this->FontStyle, $this->FontSizePt));
		$this->setFooterFont(array($this->FontFamily, $this->FontStyle, $this->FontSizePt));
		// check if PCRE Unicode support is enabled
		if ($this->isunicode AND (@preg_match('/\pL/u', 'a') == 1)) {
			// PCRE unicode support is turned ON
			// \s     : any whitespace character
			// \p{Z}  : any separator
			// \p{Lo} : Unicode letter or ideograph that does not have lowercase and uppercase variants. Is used to chunk chinese words.
			// \xa0   : Unicode Character 'NO-BREAK SPACE' (U+00A0)
			//$this->setSpacesRE('/(?!\xa0)[\s\p{Z}\p{Lo}]/u');
			$this->setSpacesRE('/(?!\xa0)[\s\p{Z}]/u');
		} else {
			// PCRE unicode support is turned OFF
			$this->setSpacesRE('/[^\S\xa0]/');
		}
		$this->default_form_prop = array('lineWidth'=>1, 'borderStyle'=>'solid', 'fillColor'=>array(255, 255, 255), 'strokeColor'=>array(128, 128, 128));
		// set document creation and modification timestamp
		$this->doc_creation_timestamp = time();
		$this->doc_modification_timestamp = $this->doc_creation_timestamp;
		// get default graphic vars
		$this->default_graphic_vars = $this->getGraphicVars();
		$this->header_xobj_autoreset = false;
		$this->custom_xmp = '';
		// Call cleanup method after script execution finishes or exit() is called.
		// NOTE: This will not be executed if the process is killed with a SIGTERM or SIGKILL signal.
		register_shutdown_function(array($this, '_destroy'), true);
	}

	/**
	 * Default destructor.
	 * @public
	 * @since 1.53.0.TC016
	 */
	public function __destruct() {
		// restore internal encoding
		if (isset($this->internal_encoding) AND !empty($this->internal_encoding)) {
			mb_internal_encoding($this->internal_encoding);
		}
		// cleanup
		$this->_destroy(true);
	}

	/**
	 * Set the units of measure for the document.
	 * @param $unit (string) User measure unit. Possible values are:<ul><li>pt: point</li><li>mm: millimeter (default)</li><li>cm: centimeter</li><li>in: inch</li></ul><br />A point equals 1/72 of inch, that is to say about 0.35 mm (an inch being 2.54 cm). This is a very common unit in typography; font sizes are expressed in that unit.
	 * @public
	 * @since 3.0.015 (2008-06-06)
	 */
	public function setPageUnit($unit) {
		$unit = strtolower($unit);
		//Set scale factor
		switch ($unit) {
			// points
			case 'px':
			case 'pt': {
				$this->k = 1;
				break;
			}
			// millimeters
			case 'mm': {
				$this->k = $this->dpi / 25.4;
				break;
			}
			// centimeters
			case 'cm': {
				$this->k = $this->dpi / 2.54;
				break;
			}
			// inches
			case 'in': {
				$this->k = $this->dpi;
				break;
			}
			// unsupported unit
			default : {
				$this->Error('Incorrect unit: '.$unit);
				break;
			}
		}
		$this->pdfunit = $unit;
		if (isset($this->CurOrientation)) {
			$this->setPageOrientation($this->CurOrientation);
		}
	}

	/**
	 * Change the format of the current page
	 * @param $format (mixed) The format used for pages. It can be either: one of the string values specified at getPageSizeFromFormat() documentation or an array of two numbers (width, height) or an array containing the following measures and options:<ul>
	 * <li>['format'] = page format name (one of the above);</li>
	 * <li>['Rotate'] : The number of degrees by which the page shall be rotated clockwise when displayed or printed. The value shall be a multiple of 90.</li>
	 * <li>['PZ'] : The page's preferred zoom (magnification) factor.</li>
	 * <li>['MediaBox'] : the boundaries of the physical medium on which the page shall be displayed or printed:</li>
	 * <li>['MediaBox']['llx'] : lower-left x coordinate</li>
	 * <li>['MediaBox']['lly'] : lower-left y coordinate</li>
	 * <li>['MediaBox']['urx'] : upper-right x coordinate</li>
	 * <li>['MediaBox']['ury'] : upper-right y coordinate</li>
	 * <li>['CropBox'] : the visible region of default user space:</li>
	 * <li>['CropBox']['llx'] : lower-left x coordinate</li>
	 * <li>['CropBox']['lly'] : lower-left y coordinate</li>
	 * <li>['CropBox']['urx'] : upper-right x coordinate</li>
	 * <li>['CropBox']['ury'] : upper-right y coordinate</li>
	 * <li>['BleedBox'] : the region to which the contents of the page shall be clipped when output in a production environment:</li>
	 * <li>['BleedBox']['llx'] : lower-left x coordinate</li>
	 * <li>['BleedBox']['lly'] : lower-left y coordinate</li>
	 * <li>['BleedBox']['urx'] : upper-right x coordinate</li>
	 * <li>['BleedBox']['ury'] : upper-right y coordinate</li>
	 * <li>['TrimBox'] : the intended dimensions of the finished page after trimming:</li>
	 * <li>['TrimBox']['llx'] : lower-left x coordinate</li>
	 * <li>['TrimBox']['lly'] : lower-left y coordinate</li>
	 * <li>['TrimBox']['urx'] : upper-right x coordinate</li>
	 * <li>['TrimBox']['ury'] : upper-right y coordinate</li>
	 * <li>['ArtBox'] : the extent of the page's meaningful content:</li>
	 * <li>['ArtBox']['llx'] : lower-left x coordinate</li>
	 * <li>['ArtBox']['lly'] : lower-left y coordinate</li>
	 * <li>['ArtBox']['urx'] : upper-right x coordinate</li>
	 * <li>['ArtBox']['ury'] : upper-right y coordinate</li>
	 * <li>['BoxColorInfo'] :specify the colours and other visual characteristics that should be used in displaying guidelines on the screen for each of the possible page boundaries other than the MediaBox:</li>
	 * <li>['BoxColorInfo'][BOXTYPE]['C'] : an array of three numbers in the range 0-255, representing the components in the DeviceRGB colour space.</li>
	 * <li>['BoxColorInfo'][BOXTYPE]['W'] : the guideline width in default user units</li>
	 * <li>['BoxColorInfo'][BOXTYPE]['S'] : the guideline style: S = Solid; D = Dashed</li>
	 * <li>['BoxColorInfo'][BOXTYPE]['D'] : dash array defining a pattern of dashes and gaps to be used in drawing dashed guidelines</li>
	 * <li>['trans'] : the style and duration of the visual transition to use when moving from another page to the given page during a presentation</li>
	 * <li>['trans']['Dur'] : The page's display duration (also called its advance timing): the maximum length of time, in seconds, that the page shall be displayed during presentations before the viewer application shall automatically advance to the next page.</li>
	 * <li>['trans']['S'] : transition style : Split, Blinds, Box, Wipe, Dissolve, Glitter, R, Fly, Push, Cover, Uncover, Fade</li>
	 * <li>['trans']['D'] : The duration of the transition effect, in seconds.</li>
	 * <li>['trans']['Dm'] : (Split and Blinds transition styles only) The dimension in which the specified transition effect shall occur: H = Horizontal, V = Vertical. Default value: H.</li>
	 * <li>['trans']['M'] : (Split, Box and Fly transition styles only) The direction of motion for the specified transition effect: I = Inward from the edges of the page, O = Outward from the center of the pageDefault value: I.</li>
	 * <li>['trans']['Di'] : (Wipe, Glitter, Fly, Cover, Uncover and Push transition styles only) The direction in which the specified transition effect shall moves, expressed in degrees counterclockwise starting from a left-to-right direction. If the value is a number, it shall be one of: 0 = Left to right, 90 = Bottom to top (Wipe only), 180 = Right to left (Wipe only), 270 = Top to bottom, 315 = Top-left to bottom-right (Glitter only). If the value is a name, it shall be None, which is relevant only for the Fly transition when the value of SS is not 1.0. Default value: 0.</li>
	 * <li>['trans']['SS'] : (Fly transition style only) The starting or ending scale at which the changes shall be drawn. If M specifies an inward transition, the scale of the changes drawn shall progress from SS to 1.0 over the course of the transition. If M specifies an outward transition, the scale of the changes drawn shall progress from 1.0 to SS over the course of the transition. Default: 1.0.</li>
	 * <li>['trans']['B'] : (Fly transition style only) If true, the area that shall be flown in is rectangular and opaque. Default: false.</li>
	 * </ul>
	 * @param $orientation (string) page orientation. Possible values are (case insensitive):<ul>
	 * <li>P or Portrait (default)</li>
	 * <li>L or Landscape</li>
	 * <li>'' (empty string) for automatic orientation</li>
	 * </ul>
	 * @protected
	 * @since 3.0.015 (2008-06-06)
	 * @see getPageSizeFromFormat()
	 */
	protected function setPageFormat($format, $orientation='P') {
		if (!empty($format) AND isset($this->pagedim[$this->page])) {
			// remove inherited values
			unset($this->pagedim[$this->page]);
		}
		if (is_string($format)) {
			// get page measures from format name
			$pf = TCPDF_STATIC::getPageSizeFromFormat($format);
			$this->fwPt = $pf[0];
			$this->fhPt = $pf[1];
		} else {
			// the boundaries of the physical medium on which the page shall be displayed or printed
			if (isset($format['MediaBox'])) {
				$this->pagedim = TCPDF_STATIC::setPageBoxes($this->page, 'MediaBox', $format['MediaBox']['llx'], $format['MediaBox']['lly'], $format['MediaBox']['urx'], $format['MediaBox']['ury'], false, $this->k, $this->pagedim);
				$this->fwPt = (($format['MediaBox']['urx'] - $format['MediaBox']['llx']) * $this->k);
				$this->fhPt = (($format['MediaBox']['ury'] - $format['MediaBox']['lly']) * $this->k);
			} else {
				if (isset($format[0]) AND is_numeric($format[0]) AND isset($format[1]) AND is_numeric($format[1])) {
					$pf = array(($format[0] * $this->k), ($format[1] * $this->k));
				} else {
					if (!isset($format['format'])) {
						// default value
						$format['format'] = 'A4';
					}
					$pf = TCPDF_STATIC::getPageSizeFromFormat($format['format']);
				}
				$this->fwPt = $pf[0];
				$this->fhPt = $pf[1];
				$this->pagedim = TCPDF_STATIC::setPageBoxes($this->page, 'MediaBox', 0, 0, $this->fwPt, $this->fhPt, true, $this->k, $this->pagedim);
			}
			// the visible region of default user space
			if (isset($format['CropBox'])) {
				$this->pagedim = TCPDF_STATIC::setPageBoxes($this->page, 'CropBox', $format['CropBox']['llx'], $format['CropBox']['lly'], $format['CropBox']['urx'], $format['CropBox']['ury'], false, $this->k, $this->pagedim);
			}
			// the region to which the contents of the page shall be clipped when output in a production environment
			if (isset($format['BleedBox'])) {
				$this->pagedim = TCPDF_STATIC::setPageBoxes($this->page, 'BleedBox', $format['BleedBox']['llx'], $format['BleedBox']['lly'], $format['BleedBox']['urx'], $format['BleedBox']['ury'], false, $this->k, $this->pagedim);
			}
			// the intended dimensions of the finished page after trimming
			if (isset($format['TrimBox'])) {
				$this->pagedim = TCPDF_STATIC::setPageBoxes($this->page, 'TrimBox', $format['TrimBox']['llx'], $format['TrimBox']['lly'], $format['TrimBox']['urx'], $format['TrimBox']['ury'], false, $this->k, $this->pagedim);
			}
			// the page's meaningful content (including potential white space)
			if (isset($format['ArtBox'])) {
				$this->pagedim = TCPDF_STATIC::setPageBoxes($this->page, 'ArtBox', $format['ArtBox']['llx'], $format['ArtBox']['lly'], $format['ArtBox']['urx'], $format['ArtBox']['ury'], false, $this->k, $this->pagedim);
			}
			// specify the colours and other visual characteristics that should be used in displaying guidelines on the screen for the various page boundaries
			if (isset($format['BoxColorInfo'])) {
				$this->pagedim[$this->page]['BoxColorInfo'] = $format['BoxColorInfo'];
			}
			if (isset($format['Rotate']) AND (($format['Rotate'] % 90) == 0)) {
				// The number of degrees by which the page shall be rotated clockwise when displayed or printed. The value shall be a multiple of 90.
				$this->pagedim[$this->page]['Rotate'] = intval($format['Rotate']);
			}
			if (isset($format['PZ'])) {
				// The page's preferred zoom (magnification) factor
				$this->pagedim[$this->page]['PZ'] = floatval($format['PZ']);
			}
			if (isset($format['trans'])) {
				// The style and duration of the visual transition to use when moving from another page to the given page during a presentation
				if (isset($format['trans']['Dur'])) {
					// The page's display duration
					$this->pagedim[$this->page]['trans']['Dur'] = floatval($format['trans']['Dur']);
				}
				$stansition_styles = array('Split', 'Blinds', 'Box', 'Wipe', 'Dissolve', 'Glitter', 'R', 'Fly', 'Push', 'Cover', 'Uncover', 'Fade');
				if (isset($format['trans']['S']) AND in_array($format['trans']['S'], $stansition_styles)) {
					// The transition style that shall be used when moving to this page from another during a presentation
					$this->pagedim[$this->page]['trans']['S'] = $format['trans']['S'];
					$valid_effect = array('Split', 'Blinds');
					$valid_vals = array('H', 'V');
					if (isset($format['trans']['Dm']) AND in_array($format['trans']['S'], $valid_effect) AND in_array($format['trans']['Dm'], $valid_vals)) {
						$this->pagedim[$this->page]['trans']['Dm'] = $format['trans']['Dm'];
					}
					$valid_effect = array('Split', 'Box', 'Fly');
					$valid_vals = array('I', 'O');
					if (isset($format['trans']['M']) AND in_array($format['trans']['S'], $valid_effect) AND in_array($format['trans']['M'], $valid_vals)) {
						$this->pagedim[$this->page]['trans']['M'] = $format['trans']['M'];
					}
					$valid_effect = array('Wipe', 'Glitter', 'Fly', 'Cover', 'Uncover', 'Push');
					if (isset($format['trans']['Di']) AND in_array($format['trans']['S'], $valid_effect)) {
						if (((($format['trans']['Di'] == 90) OR ($format['trans']['Di'] == 180)) AND ($format['trans']['S'] == 'Wipe'))
							OR (($format['trans']['Di'] == 315) AND ($format['trans']['S'] == 'Glitter'))
							OR (($format['trans']['Di'] == 0) OR ($format['trans']['Di'] == 270))) {
							$this->pagedim[$this->page]['trans']['Di'] = intval($format['trans']['Di']);
						}
					}
					if (isset($format['trans']['SS']) AND ($format['trans']['S'] == 'Fly')) {
						$this->pagedim[$this->page]['trans']['SS'] = floatval($format['trans']['SS']);
					}
					if (isset($format['trans']['B']) AND ($format['trans']['B'] === true) AND ($format['trans']['S'] == 'Fly')) {
						$this->pagedim[$this->page]['trans']['B'] = 'true';
					}
				} else {
					$this->pagedim[$this->page]['trans']['S'] = 'R';
				}
				if (isset($format['trans']['D'])) {
					// The duration of the transition effect, in seconds
					$this->pagedim[$this->page]['trans']['D'] = floatval($format['trans']['D']);
				} else {
					$this->pagedim[$this->page]['trans']['D'] = 1;
				}
			}
		}
		$this->setPageOrientation($orientation);
	}

	/**
	 * Set page orientation.
	 * @param $orientation (string) page orientation. Possible values are (case insensitive):<ul><li>P or Portrait (default)</li><li>L or Landscape</li><li>'' (empty string) for automatic orientation</li></ul>
	 * @param $autopagebreak (boolean) Boolean indicating if auto-page-break mode should be on or off.
	 * @param $bottommargin (float) bottom margin of the page.
	 * @public
	 * @since 3.0.015 (2008-06-06)
	 */
	public function setPageOrientation($orientation, $autopagebreak='', $bottommargin='') {
		if (!isset($this->pagedim[$this->page]['MediaBox'])) {
			// the boundaries of the physical medium on which the page shall be displayed or printed
			$this->pagedim = TCPDF_STATIC::setPageBoxes($this->page, 'MediaBox', 0, 0, $this->fwPt, $this->fhPt, true, $this->k, $this->pagedim);
		}
		if (!isset($this->pagedim[$this->page]['CropBox'])) {
			// the visible region of default user space
			$this->pagedim = TCPDF_STATIC::setPageBoxes($this->page, 'CropBox', $this->pagedim[$this->page]['MediaBox']['llx'], $this->pagedim[$this->page]['MediaBox']['lly'], $this->pagedim[$this->page]['MediaBox']['urx'], $this->pagedim[$this->page]['MediaBox']['ury'], true, $this->k, $this->pagedim);
		}
		if (!isset($this->pagedim[$this->page]['BleedBox'])) {
			// the region to which the contents of the page shall be clipped when output in a production environment
			$this->pagedim = TCPDF_STATIC::setPageBoxes($this->page, 'BleedBox', $this->pagedim[$this->page]['CropBox']['llx'], $this->pagedim[$this->page]['CropBox']['lly'], $this->pagedim[$this->page]['CropBox']['urx'], $this->pagedim[$this->page]['CropBox']['ury'], true, $this->k, $this->pagedim);
		}
		if (!isset($this->pagedim[$this->page]['TrimBox'])) {
			// the intended dimensions of the finished page after trimming
			$this->pagedim = TCPDF_STATIC::setPageBoxes($this->page, 'TrimBox', $this->pagedim[$this->page]['CropBox']['llx'], $this->pagedim[$this->page]['CropBox']['lly'], $this->pagedim[$this->page]['CropBox']['urx'], $this->pagedim[$this->page]['CropBox']['ury'], true, $this->k, $this->pagedim);
		}
		if (!isset($this->pagedim[$this->page]['ArtBox'])) {
			// the page's meaningful content (including potential white space)
			$this->pagedim = TCPDF_STATIC::setPageBoxes($this->page, 'ArtBox', $this->pagedim[$this->page]['CropBox']['llx'], $this->pagedim[$this->page]['CropBox']['lly'], $this->pagedim[$this->page]['CropBox']['urx'], $this->pagedim[$this->page]['CropBox']['ury'], true, $this->k, $this->pagedim);
		}
		if (!isset($this->pagedim[$this->page]['Rotate'])) {
			// The number of degrees by which the page shall be rotated clockwise when displayed or printed. The value shall be a multiple of 90.
			$this->pagedim[$this->page]['Rotate'] = 0;
		}
		if (!isset($this->pagedim[$this->page]['PZ'])) {
			// The page's preferred zoom (magnification) factor
			$this->pagedim[$this->page]['PZ'] = 1;
		}
		if ($this->fwPt > $this->fhPt) {
			// landscape
			$default_orientation = 'L';
		} else {
			// portrait
			$default_orientation = 'P';
		}
		$valid_orientations = array('P', 'L');
		if (empty($orientation)) {
			$orientation = $default_orientation;
		} else {
			$orientation = strtoupper($orientation[0]);
		}
		if (in_array($orientation, $valid_orientations) AND ($orientation != $default_orientation)) {
			$this->CurOrientation = $orientation;
			$this->wPt = $this->fhPt;
			$this->hPt = $this->fwPt;
		} else {
			$this->CurOrientation = $default_orientation;
			$this->wPt = $this->fwPt;
			$this->hPt = $this->fhPt;
		}
		if ((abs($this->pagedim[$this->page]['MediaBox']['urx'] - $this->hPt) < $this->feps) AND (abs($this->pagedim[$this->page]['MediaBox']['ury'] - $this->wPt) < $this->feps)){
			// swap X and Y coordinates (change page orientation)
			$this->pagedim = TCPDF_STATIC::swapPageBoxCoordinates($this->page, $this->pagedim);
		}
		$this->w = ($this->wPt / $this->k);
		$this->h = ($this->hPt / $this->k);
		if (TCPDF_STATIC::empty_string($autopagebreak)) {
			if (isset($this->AutoPageBreak)) {
				$autopagebreak = $this->AutoPageBreak;
			} else {
				$autopagebreak = true;
			}
		}
		if (TCPDF_STATIC::empty_string($bottommargin)) {
			if (isset($this->bMargin)) {
				$bottommargin = $this->bMargin;
			} else {
				// default value = 2 cm
				$bottommargin = 2 * 28.35 / $this->k;
			}
		}
		$this->SetAutoPageBreak($autopagebreak, $bottommargin);
		// store page dimensions
		$this->pagedim[$this->page]['w'] = $this->wPt;
		$this->pagedim[$this->page]['h'] = $this->hPt;
		$this->pagedim[$this->page]['wk'] = $this->w;
		$this->pagedim[$this->page]['hk'] = $this->h;
		$this->pagedim[$this->page]['tm'] = $this->tMargin;
		$this->pagedim[$this->page]['bm'] = $bottommargin;
		$this->pagedim[$this->page]['lm'] = $this->lMargin;
		$this->pagedim[$this->page]['rm'] = $this->rMargin;
		$this->pagedim[$this->page]['pb'] = $autopagebreak;
		$this->pagedim[$this->page]['or'] = $this->CurOrientation;
		$this->pagedim[$this->page]['olm'] = $this->original_lMargin;
		$this->pagedim[$this->page]['orm'] = $this->original_rMargin;
	}

	/**
	 * Set regular expression to detect withespaces or word separators.
	 * The pattern delimiter must be the forward-slash character "/".
	 * Some example patterns are:
	 * <pre>
	 * Non-Unicode or missing PCRE unicode support: "/[^\S\xa0]/"
	 * Unicode and PCRE unicode support: "/(?!\xa0)[\s\p{Z}]/u"
	 * Unicode and PCRE unicode support in Chinese mode: "/(?!\xa0)[\s\p{Z}\p{Lo}]/u"
	 * if PCRE unicode support is turned ON ("\P" is the negate class of "\p"):
	 *      \s     : any whitespace character
	 *      \p{Z}  : any separator
	 *      \p{Lo} : Unicode letter or ideograph that does not have lowercase and uppercase variants. Is used to chunk chinese words.
	 *      \xa0   : Unicode Character 'NO-BREAK SPACE' (U+00A0)
	 * </pre>
	 * @param $re (string) regular expression (leave empty for default).
	 * @public
	 * @since 4.6.016 (2009-06-15)
	 */
	public function setSpacesRE($re='/[^\S\xa0]/') {
		$this->re_spaces = $re;
		$re_parts = explode('/', $re);
		// get pattern parts
		$this->re_space = array();
		if (isset($re_parts[1]) AND !empty($re_parts[1])) {
			$this->re_space['p'] = $re_parts[1];
		} else {
			$this->re_space['p'] = '[\s]';
		}
		// set pattern modifiers
		if (isset($re_parts[2]) AND !empty($re_parts[2])) {
			$this->re_space['m'] = $re_parts[2];
		} else {
			$this->re_space['m'] = '';
		}
	}

	/**
	 * Enable or disable Right-To-Left language mode
	 * @param $enable (Boolean) if true enable Right-To-Left language mode.
	 * @param $resetx (Boolean) if true reset the X position on direction change.
	 * @public
	 * @since 2.0.000 (2008-01-03)
	 */
	public function setRTL($enable, $resetx=true) {
		$enable = $enable ? true : false;
		$resetx = ($resetx AND ($enable != $this->rtl));
		$this->rtl = $enable;
		$this->tmprtl = false;
		if ($resetx) {
			$this->Ln(0);
		}
	}

	/**
	 * Return the RTL status
	 * @return boolean
	 * @public
	 * @since 4.0.012 (2008-07-24)
	 */
	public function getRTL() {
		return $this->rtl;
	}

	/**
	 * Force temporary RTL language direction
	 * @param $mode (mixed) can be false, 'L' for LTR or 'R' for RTL
	 * @public
	 * @since 2.1.000 (2008-01-09)
	 */
	public function setTempRTL($mode) {
		$newmode = false;
		switch (strtoupper($mode)) {
			case 'LTR':
			case 'L': {
				if ($this->rtl) {
					$newmode = 'L';
				}
				break;
			}
			case 'RTL':
			case 'R': {
				if (!$this->rtl) {
					$newmode = 'R';
				}
				break;
			}
			case false:
			default: {
				$newmode = false;
				break;
			}
		}
		$this->tmprtl = $newmode;
	}

	/**
	 * Return the current temporary RTL status
	 * @return boolean
	 * @public
	 * @since 4.8.014 (2009-11-04)
	 */
	public function isRTLTextDir() {
		return ($this->rtl OR ($this->tmprtl == 'R'));
	}

	/**
	 * Set the last cell height.
	 * @param $h (float) cell height.
	 * @author Nicola Asuni
	 * @public
	 * @since 1.53.0.TC034
	 */
	public function setLastH($h) {
		$this->lasth = $h;
	}

	/**
	 * Return the cell height
	 * @param $fontsize (int) Font size in internal units
	 * @param $padding (boolean) If true add cell padding
	 * @public
	 */
	public function getCellHeight($fontsize, $padding=TRUE) {
		$height = ($fontsize * $this->cell_height_ratio);
		if ($padding) {
			$height += ($this->cell_padding['T'] + $this->cell_padding['B']);
		}
		return round($height, 6);
	}

	/**
	 * Reset the last cell height.
	 * @public
	 * @since 5.9.000 (2010-10-03)
	 */
	public function resetLastH() {
		$this->lasth = $this->getCellHeight($this->FontSize);
	}

	/**
	 * Get the last cell height.
	 * @return last cell height
	 * @public
	 * @since 4.0.017 (2008-08-05)
	 */
	public function getLastH() {
		return $this->lasth;
	}

	/**
	 * Set the adjusting factor to convert pixels to user units.
	 * @param $scale (float) adjusting factor to convert pixels to user units.
	 * @author Nicola Asuni
	 * @public
	 * @since 1.5.2
	 */
	public function setImageScale($scale) {
		$this->imgscale = $scale;
	}

	/**
	 * Returns the adjusting factor to convert pixels to user units.
	 * @return float adjusting factor to convert pixels to user units.
	 * @author Nicola Asuni
	 * @public
	 * @since 1.5.2
	 */
	public function getImageScale() {
		return $this->imgscale;
	}

	/**
	 * Returns an array of page dimensions:
	 * <ul><li>$this->pagedim[$this->page]['w'] = page width in points</li><li>$this->pagedim[$this->page]['h'] = height in points</li><li>$this->pagedim[$this->page]['wk'] = page width in user units</li><li>$this->pagedim[$this->page]['hk'] = page height in user units</li><li>$this->pagedim[$this->page]['tm'] = top margin</li><li>$this->pagedim[$this->page]['bm'] = bottom margin</li><li>$this->pagedim[$this->page]['lm'] = left margin</li><li>$this->pagedim[$this->page]['rm'] = right margin</li><li>$this->pagedim[$this->page]['pb'] = auto page break</li><li>$this->pagedim[$this->page]['or'] = page orientation</li><li>$this->pagedim[$this->page]['olm'] = original left margin</li><li>$this->pagedim[$this->page]['orm'] = original right margin</li><li>$this->pagedim[$this->page]['Rotate'] = The number of degrees by which the page shall be rotated clockwise when displayed or printed. The value shall be a multiple of 90.</li><li>$this->pagedim[$this->page]['PZ'] = The page's preferred zoom (magnification) factor.</li><li>$this->pagedim[$this->page]['trans'] : the style and duration of the visual transition to use when moving from another page to the given page during a presentation<ul><li>$this->pagedim[$this->page]['trans']['Dur'] = The page's display duration (also called its advance timing): the maximum length of time, in seconds, that the page shall be displayed during presentations before the viewer application shall automatically advance to the next page.</li><li>$this->pagedim[$this->page]['trans']['S'] = transition style : Split, Blinds, Box, Wipe, Dissolve, Glitter, R, Fly, Push, Cover, Uncover, Fade</li><li>$this->pagedim[$this->page]['trans']['D'] = The duration of the transition effect, in seconds.</li><li>$this->pagedim[$this->page]['trans']['Dm'] = (Split and Blinds transition styles only) The dimension in which the specified transition effect shall occur: H = Horizontal, V = Vertical. Default value: H.</li><li>$this->pagedim[$this->page]['trans']['M'] = (Split, Box and Fly transition styles only) The direction of motion for the specified transition effect: I = Inward from the edges of the page, O = Outward from the center of the pageDefault value: I.</li><li>$this->pagedim[$this->page]['trans']['Di'] = (Wipe, Glitter, Fly, Cover, Uncover and Push transition styles only) The direction in which the specified transition effect shall moves, expressed in degrees counterclockwise starting from a left-to-right direction. If the value is a number, it shall be one of: 0 = Left to right, 90 = Bottom to top (Wipe only), 180 = Right to left (Wipe only), 270 = Top to bottom, 315 = Top-left to bottom-right (Glitter only). If the value is a name, it shall be None, which is relevant only for the Fly transition when the value of SS is not 1.0. Default value: 0.</li><li>$this->pagedim[$this->page]['trans']['SS'] = (Fly transition style only) The starting or ending scale at which the changes shall be drawn. If M specifies an inward transition, the scale of the changes drawn shall progress from SS to 1.0 over the course of the transition. If M specifies an outward transition, the scale of the changes drawn shall progress from 1.0 to SS over the course of the transition. Default: 1.0. </li><li>$this->pagedim[$this->page]['trans']['B'] = (Fly transition style only) If true, the area that shall be flown in is rectangular and opaque. Default: false.</li></ul></li><li>$this->pagedim[$this->page]['MediaBox'] : the boundaries of the physical medium on which the page shall be displayed or printed<ul><li>$this->pagedim[$this->page]['MediaBox']['llx'] = lower-left x coordinate in points</li><li>$this->pagedim[$this->page]['MediaBox']['lly'] = lower-left y coordinate in points</li><li>$this->pagedim[$this->page]['MediaBox']['urx'] = upper-right x coordinate in points</li><li>$this->pagedim[$this->page]['MediaBox']['ury'] = upper-right y coordinate in points</li></ul></li><li>$this->pagedim[$this->page]['CropBox'] : the visible region of default user space<ul><li>$this->pagedim[$this->page]['CropBox']['llx'] = lower-left x coordinate in points</li><li>$this->pagedim[$this->page]['CropBox']['lly'] = lower-left y coordinate in points</li><li>$this->pagedim[$this->page]['CropBox']['urx'] = upper-right x coordinate in points</li><li>$this->pagedim[$this->page]['CropBox']['ury'] = upper-right y coordinate in points</li></ul></li><li>$this->pagedim[$this->page]['BleedBox'] : the region to which the contents of the page shall be clipped when output in a production environment<ul><li>$this->pagedim[$this->page]['BleedBox']['llx'] = lower-left x coordinate in points</li><li>$this->pagedim[$this->page]['BleedBox']['lly'] = lower-left y coordinate in points</li><li>$this->pagedim[$this->page]['BleedBox']['urx'] = upper-right x coordinate in points</li><li>$this->pagedim[$this->page]['BleedBox']['ury'] = upper-right y coordinate in points</li></ul></li><li>$this->pagedim[$this->page]['TrimBox'] : the intended dimensions of the finished page after trimming<ul><li>$this->pagedim[$this->page]['TrimBox']['llx'] = lower-left x coordinate in points</li><li>$this->pagedim[$this->page]['TrimBox']['lly'] = lower-left y coordinate in points</li><li>$this->pagedim[$this->page]['TrimBox']['urx'] = upper-right x coordinate in points</li><li>$this->pagedim[$this->page]['TrimBox']['ury'] = upper-right y coordinate in points</li></ul></li><li>$this->pagedim[$this->page]['ArtBox'] : the extent of the page's meaningful content<ul><li>$this->pagedim[$this->page]['ArtBox']['llx'] = lower-left x coordinate in points</li><li>$this->pagedim[$this->page]['ArtBox']['lly'] = lower-left y coordinate in points</li><li>$this->pagedim[$this->page]['ArtBox']['urx'] = upper-right x coordinate in points</li><li>$this->pagedim[$this->page]['ArtBox']['ury'] = upper-right y coordinate in points</li></ul></li></ul>
	 * @param $pagenum (int) page number (empty = current page)
	 * @return array of page dimensions.
	 * @author Nicola Asuni
	 * @public
	 * @since 4.5.027 (2009-03-16)
	 */
	public function getPageDimensions($pagenum='') {
		if (empty($pagenum)) {
			$pagenum = $this->page;
		}
		return $this->pagedim[$pagenum];
	}

	/**
	 * Returns the page width in units.
	 * @param $pagenum (int) page number (empty = current page)
	 * @return int page width.
	 * @author Nicola Asuni
	 * @public
	 * @since 1.5.2
	 * @see getPageDimensions()
	 */
	public function getPageWidth($pagenum='') {
		if (empty($pagenum)) {
			return $this->w;
		}
		return $this->pagedim[$pagenum]['w'];
	}

	/**
	 * Returns the page height in units.
	 * @param $pagenum (int) page number (empty = current page)
	 * @return int page height.
	 * @author Nicola Asuni
	 * @public
	 * @since 1.5.2
	 * @see getPageDimensions()
	 */
	public function getPageHeight($pagenum='') {
		if (empty($pagenum)) {
			return $this->h;
		}
		return $this->pagedim[$pagenum]['h'];
	}

	/**
	 * Returns the page break margin.
	 * @param $pagenum (int) page number (empty = current page)
	 * @return int page break margin.
	 * @author Nicola Asuni
	 * @public
	 * @since 1.5.2
	 * @see getPageDimensions()
	 */
	public function getBreakMargin($pagenum='') {
		if (empty($pagenum)) {
			return $this->bMargin;
		}
		return $this->pagedim[$pagenum]['bm'];
	}

	/**
	 * Returns the scale factor (number of points in user unit).
	 * @return int scale factor.
	 * @author Nicola Asuni
	 * @public
	 * @since 1.5.2
	 */
	public function getScaleFactor() {
		return $this->k;
	}

	/**
	 * Defines the left, top and right margins.
	 * @param $left (float) Left margin.
	 * @param $top (float) Top margin.
	 * @param $right (float) Right margin. Default value is the left one.
	 * @param $keepmargins (boolean) if true overwrites the default page margins
	 * @public
	 * @since 1.0
	 * @see SetLeftMargin(), SetTopMargin(), SetRightMargin(), SetAutoPageBreak()
	 */
	public function SetMargins($left, $top, $right=-1, $keepmargins=false) {
		//Set left, top and right margins
		$this->lMargin = $left;
		$this->tMargin = $top;
		if ($right == -1) {
			$right = $left;
		}
		$this->rMargin = $right;
		if ($keepmargins) {
			// overwrite original values
			$this->original_lMargin = $this->lMargin;
			$this->original_rMargin = $this->rMargin;
		}
	}

	/**
	 * Defines the left margin. The method can be called before creating the first page. If the current abscissa gets out of page, it is brought back to the margin.
	 * @param $margin (float) The margin.
	 * @public
	 * @since 1.4
	 * @see SetTopMargin(), SetRightMargin(), SetAutoPageBreak(), SetMargins()
	 */
	public function SetLeftMargin($margin) {
		//Set left margin
		$this->lMargin = $margin;
		if (($this->page > 0) AND ($this->x < $margin)) {
			$this->x = $margin;
		}
	}

	/**
	 * Defines the top margin. The method can be called before creating the first page.
	 * @param $margin (float) The margin.
	 * @public
	 * @since 1.5
	 * @see SetLeftMargin(), SetRightMargin(), SetAutoPageBreak(), SetMargins()
	 */
	public function SetTopMargin($margin) {
		//Set top margin
		$this->tMargin = $margin;
		if (($this->page > 0) AND ($this->y < $margin)) {
			$this->y = $margin;
		}
	}

	/**
	 * Defines the right margin. The method can be called before creating the first page.
	 * @param $margin (float) The margin.
	 * @public
	 * @since 1.5
	 * @see SetLeftMargin(), SetTopMargin(), SetAutoPageBreak(), SetMargins()
	 */
	public function SetRightMargin($margin) {
		$this->rMargin = $margin;
		if (($this->page > 0) AND ($this->x > ($this->w - $margin))) {
			$this->x = $this->w - $margin;
		}
	}

	/**
	 * Set the same internal Cell padding for top, right, bottom, left-
	 * @param $pad (float) internal padding.
	 * @public
	 * @since 2.1.000 (2008-01-09)
	 * @see getCellPaddings(), setCellPaddings()
	 */
	public function SetCellPadding($pad) {
		if ($pad >= 0) {
			$this->cell_padding['L'] = $pad;
			$this->cell_padding['T'] = $pad;
			$this->cell_padding['R'] = $pad;
			$this->cell_padding['B'] = $pad;
		}
	}

	/**
	 * Set the internal Cell paddings.
	 * @param $left (float) left padding
	 * @param $top (float) top padding
	 * @param $right (float) right padding
	 * @param $bottom (float) bottom padding
	 * @public
	 * @since 5.9.000 (2010-10-03)
	 * @see getCellPaddings(), SetCellPadding()
	 */
	public function setCellPaddings($left='', $top='', $right='', $bottom='') {
		if (($left !== '') AND ($left >= 0)) {
			$this->cell_padding['L'] = $left;
		}
		if (($top !== '') AND ($top >= 0)) {
			$this->cell_padding['T'] = $top;
		}
		if (($right !== '') AND ($right >= 0)) {
			$this->cell_padding['R'] = $right;
		}
		if (($bottom !== '') AND ($bottom >= 0)) {
			$this->cell_padding['B'] = $bottom;
		}
	}

	/**
	 * Get the internal Cell padding array.
	 * @return array of padding values
	 * @public
	 * @since 5.9.000 (2010-10-03)
	 * @see setCellPaddings(), SetCellPadding()
	 */
	public function getCellPaddings() {
		return $this->cell_padding;
	}

	/**
	 * Set the internal Cell margins.
	 * @param $left (float) left margin
	 * @param $top (float) top margin
	 * @param $right (float) right margin
	 * @param $bottom (float) bottom margin
	 * @public
	 * @since 5.9.000 (2010-10-03)
	 * @see getCellMargins()
	 */
	public function setCellMargins($left='', $top='', $right='', $bottom='') {
		if (($left !== '') AND ($left >= 0)) {
			$this->cell_margin['L'] = $left;
		}
		if (($top !== '') AND ($top >= 0)) {
			$this->cell_margin['T'] = $top;
		}
		if (($right !== '') AND ($right >= 0)) {
			$this->cell_margin['R'] = $right;
		}
		if (($bottom !== '') AND ($bottom >= 0)) {
			$this->cell_margin['B'] = $bottom;
		}
	}

	/**
	 * Get the internal Cell margin array.
	 * @return array of margin values
	 * @public
	 * @since 5.9.000 (2010-10-03)
	 * @see setCellMargins()
	 */
	public function getCellMargins() {
		return $this->cell_margin;
	}

	/**
	 * Adjust the internal Cell padding array to take account of the line width.
	 * @param $brd (mixed) Indicates if borders must be drawn around the cell. The value can be a number:<ul><li>0: no border (default)</li><li>1: frame</li></ul> or a string containing some or all of the following characters (in any order):<ul><li>L: left</li><li>T: top</li><li>R: right</li><li>B: bottom</li></ul> or an array of line styles for each border group - for example: array('LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)))
	 * @return array of adjustments
	 * @public
	 * @since 5.9.000 (2010-10-03)
	 */
	protected function adjustCellPadding($brd=0) {
		if (empty($brd)) {
			return;
		}
		if (is_string($brd)) {
			// convert string to array
			$slen = strlen($brd);
			$newbrd = array();
			for ($i = 0; $i < $slen; ++$i) {
				$newbrd[$brd[$i]] = true;
			}
			$brd = $newbrd;
		} elseif (($brd === 1) OR ($brd === true) OR (is_numeric($brd) AND (intval($brd) > 0))) {
			$brd = array('LRTB' => true);
		}
		if (!is_array($brd)) {
			return;
		}
		// store current cell padding
		$cp = $this->cell_padding;
		// select border mode
		if (isset($brd['mode'])) {
			$mode = $brd['mode'];
			unset($brd['mode']);
		} else {
			$mode = 'normal';
		}
		// process borders
		foreach ($brd as $border => $style) {
			$line_width = $this->LineWidth;
			if (is_array($style) AND isset($style['width'])) {
				// get border width
				$line_width = $style['width'];
			}
			$adj = 0; // line width inside the cell
			switch ($mode) {
				case 'ext': {
					$adj = 0;
					break;
				}
				case 'int': {
					$adj = $line_width;
					break;
				}
				case 'normal':
				default: {
					$adj = ($line_width / 2);
					break;
				}
			}
			// correct internal cell padding if required to avoid overlap between text and lines
			if ((strpos($border,'T') !== false) AND ($this->cell_padding['T'] < $adj)) {
				$this->cell_padding['T'] = $adj;
			}
			if ((strpos($border,'R') !== false) AND ($this->cell_padding['R'] < $adj)) {
				$this->cell_padding['R'] = $adj;
			}
			if ((strpos($border,'B') !== false) AND ($this->cell_padding['B'] < $adj)) {
				$this->cell_padding['B'] = $adj;
			}
			if ((strpos($border,'L') !== false) AND ($this->cell_padding['L'] < $adj)) {
				$this->cell_padding['L'] = $adj;
			}
		}
		return array('T' => ($this->cell_padding['T'] - $cp['T']), 'R' => ($this->cell_padding['R'] - $cp['R']), 'B' => ($this->cell_padding['B'] - $cp['B']), 'L' => ($this->cell_padding['L'] - $cp['L']));
	}

	/**
	 * Enables or disables the automatic page breaking mode. When enabling, the second parameter is the distance from the bottom of the page that defines the triggering limit. By default, the mode is on and the margin is 2 cm.
	 * @param $auto (boolean) Boolean indicating if mode should be on or off.
	 * @param $margin (float) Distance from the bottom of the page.
	 * @public
	 * @since 1.0
	 * @see Cell(), MultiCell(), AcceptPageBreak()
	 */
	public function SetAutoPageBreak($auto, $margin=0) {
		$this->AutoPageBreak = $auto ? true : false;
		$this->bMargin = $margin;
		$this->PageBreakTrigger = $this->h - $margin;
	}

	/**
	 * Return the auto-page-break mode (true or false).
	 * @return boolean auto-page-break mode
	 * @public
	 * @since 5.9.088
	 */
	public function getAutoPageBreak() {
		return $this->AutoPageBreak;
	}

	/**
	 * Defines the way the document is to be displayed by the viewer.
	 * @param $zoom (mixed) The zoom to use. It can be one of the following string values or a number indicating the zooming factor to use. <ul><li>fullpage: displays the entire page on screen </li><li>fullwidth: uses maximum width of window</li><li>real: uses real size (equivalent to 100% zoom)</li><li>default: uses viewer default mode</li></ul>
	 * @param $layout (string) The page layout. Possible values are:<ul><li>SinglePage Display one page at a time</li><li>OneColumn Display the pages in one column</li><li>TwoColumnLeft Display the pages in two columns, with odd-numbered pages on the left</li><li>TwoColumnRight Display the pages in two columns, with odd-numbered pages on the right</li><li>TwoPageLeft (PDF 1.5) Display the pages two at a time, with odd-numbered pages on the left</li><li>TwoPageRight (PDF 1.5) Display the pages two at a time, with odd-numbered pages on the right</li></ul>
	 * @param $mode (string) A name object specifying how the document should be displayed when opened:<ul><li>UseNone Neither document outline nor thumbnail images visible</li><li>UseOutlines Document outline visible</li><li>UseThumbs Thumbnail images visible</li><li>FullScreen Full-screen mode, with no menu bar, window controls, or any other window visible</li><li>UseOC (PDF 1.5) Optional content group panel visible</li><li>UseAttachments (PDF 1.6) Attachments panel visible</li></ul>
	 * @public
	 * @since 1.2
	 */
	public function SetDisplayMode($zoom, $layout='SinglePage', $mode='UseNone') {
		if (($zoom == 'fullpage') OR ($zoom == 'fullwidth') OR ($zoom == 'real') OR ($zoom == 'default') OR (!is_string($zoom))) {
			$this->ZoomMode = $zoom;
		} else {
			$this->Error('Incorrect zoom display mode: '.$zoom);
		}
		$this->LayoutMode = TCPDF_STATIC::getPageLayoutMode($layout);
		$this->PageMode = TCPDF_STATIC::getPageMode($mode);
	}

	/**
	 * Activates or deactivates page compression. When activated, the internal representation of each page is compressed, which leads to a compression ratio of about 2 for the resulting document. Compression is on by default.
	 * Note: the Zlib extension is required for this feature. If not present, compression will be turned off.
	 * @param $compress (boolean) Boolean indicating if compression must be enabled.
	 * @public
	 * @since 1.4
	 */
	public function SetCompression($compress=true) {
		if (function_exists('gzcompress')) {
			$this->compress = $compress ? true : false;
		} else {
			$this->compress = false;
		}
	}

	/**
	 * Set flag to force sRGB_IEC61966-2.1 black scaled ICC color profile for the whole document.
	 * @param $mode (boolean) If true force sRGB output intent.
	 * @public
	 * @since 5.9.121 (2011-09-28)
	 */
	public function setSRGBmode($mode=false) {
		$this->force_srgb = $mode ? true : false;
	}

	/**
	 * Turn on/off Unicode mode for document information dictionary (meta tags).
	 * This has effect only when unicode mode is set to false.
	 * @param $unicode (boolean) if true set the meta information in Unicode
	 * @since 5.9.027 (2010-12-01)
	 * @public
	 */
	public function SetDocInfoUnicode($unicode=true) {
		$this->docinfounicode = $unicode ? true : false;
	}

	/**
	 * Defines the title of the document.
	 * @param $title (string) The title.
	 * @public
	 * @since 1.2
	 * @see SetAuthor(), SetCreator(), SetKeywords(), SetSubject()
	 */
	public function SetTitle($title) {
		$this->title = $title;
	}

	/**
	 * Defines the subject of the document.
	 * @param $subject (string) The subject.
	 * @public
	 * @since 1.2
	 * @see SetAuthor(), SetCreator(), SetKeywords(), SetTitle()
	 */
	public function SetSubject($subject) {
		$this->subject = $subject;
	}

	/**
	 * Defines the author of the document.
	 * @param $author (string) The name of the author.
	 * @public
	 * @since 1.2
	 * @see SetCreator(), SetKeywords(), SetSubject(), SetTitle()
	 */
	public function SetAuthor($author) {
		$this->author = $author;
	}

	/**
	 * Associates keywords with the document, generally in the form 'keyword1 keyword2 ...'.
	 * @param $keywords (string) The list of keywords.
	 * @public
	 * @since 1.2
	 * @see SetAuthor(), SetCreator(), SetSubject(), SetTitle()
	 */
	public function SetKeywords($keywords) {
		$this->keywords = $keywords;
	}

	/**
	 * Defines the creator of the document. This is typically the name of the application that generates the PDF.
	 * @param $creator (string) The name of the creator.
	 * @public
	 * @since 1.2
	 * @see SetAuthor(), SetKeywords(), SetSubject(), SetTitle()
	 */
	public function SetCreator($creator) {
		$this->creator = $creator;
	}

	/**
	 * Throw an exception or print an error message and die if the K_TCPDF_PARSER_THROW_EXCEPTION_ERROR constant is set to true.
	 * @param $msg (string) The error message
	 * @public
	 * @since 1.0
	 */
	public function Error($msg) {
		// unset all class variables
		$this->_destroy(true);
		if (defined('K_TCPDF_THROW_EXCEPTION_ERROR') AND !K_TCPDF_THROW_EXCEPTION_ERROR) {
			die('<strong>TCPDF ERROR: </strong>'.$msg);
		} else {
			throw new Exception('TCPDF ERROR: '.$msg);
		}
	}

	/**
	 * This method begins the generation of the PDF document.
	 * It is not necessary to call it explicitly because AddPage() does it automatically.
	 * Note: no page is created by this method
	 * @public
	 * @since 1.0
	 * @see AddPage(), Close()
	 */
	public function Open() {
		$this->state = 1;
	}

	/**
	 * Terminates the PDF document.
	 * It is not necessary to call this method explicitly because Output() does it automatically.
	 * If the document contains no page, AddPage() is called to prevent from getting an invalid document.
	 * @public
	 * @since 1.0
	 * @see Open(), Output()
	 */
	public function Close() {
		if ($this->state == 3) {
			return;
		}
		if ($this->page == 0) {
			$this->AddPage();
		}
		$this->endLayer();
		if ($this->tcpdflink) {
			// save current graphic settings
			$gvars = $this->getGraphicVars();
			$this->setEqualColumns();
			$this->lastpage(true);
			$this->SetAutoPageBreak(false);
			$this->x = 0;
			$this->y = $this->h - (1 / $this->k);
			$this->lMargin = 0;
			$this->_outSaveGraphicsState();
			$font = defined('PDF_FONT_NAME_MAIN')?PDF_FONT_NAME_MAIN:'helvetica';
			$this->SetFont($font, '', 1);
			$this->setTextRenderingMode(0, false, false);
			$msg = "\x50\x6f\x77\x65\x72\x65\x64\x20\x62\x79\x20\x54\x43\x50\x44\x46\x20\x28\x77\x77\x77\x2e\x74\x63\x70\x64\x66\x2e\x6f\x72\x67\x29";
			$lnk = "\x68\x74\x74\x70\x3a\x2f\x2f\x77\x77\x77\x2e\x74\x63\x70\x64\x66\x2e\x6f\x72\x67";
			$this->Cell(0, 0, $msg, 0, 0, 'L', 0, $lnk, 0, false, 'D', 'B');
			$this->_outRestoreGraphicsState();
			// restore graphic settings
			$this->setGraphicVars($gvars);
		}
		// close page
		$this->endPage();
		// close document
		$this->_enddoc();
		// unset all class variables (except critical ones)
		$this->_destroy(false);
	}

	/**
	 * Move pointer at the specified document page and update page dimensions.
	 * @param $pnum (int) page number (1 ... numpages)
	 * @param $resetmargins (boolean) if true reset left, right, top margins and Y position.
	 * @public
	 * @since 2.1.000 (2008-01-07)
	 * @see getPage(), lastpage(), getNumPages()
	 */
	public function setPage($pnum, $resetmargins=false) {
		if (($pnum == $this->page) AND ($this->state == 2)) {
			return;
		}
		if (($pnum > 0) AND ($pnum <= $this->numpages)) {
			$this->state = 2;
			// save current graphic settings
			//$gvars = $this->getGraphicVars();
			$oldpage = $this->page;
			$this->page = $pnum;
			$this->wPt = $this->pagedim[$this->page]['w'];
			$this->hPt = $this->pagedim[$this->page]['h'];
			$this->w = $this->pagedim[$this->page]['wk'];
			$this->h = $this->pagedim[$this->page]['hk'];
			$this->tMargin = $this->pagedim[$this->page]['tm'];
			$this->bMargin = $this->pagedim[$this->page]['bm'];
			$this->original_lMargin = $this->pagedim[$this->page]['olm'];
			$this->original_rMargin = $this->pagedim[$this->page]['orm'];
			$this->AutoPageBreak = $this->pagedim[$this->page]['pb'];
			$this->CurOrientation = $this->pagedim[$this->page]['or'];
			$this->SetAutoPageBreak($this->AutoPageBreak, $this->bMargin);
			// restore graphic settings
			//$this->setGraphicVars($gvars);
			if ($resetmargins) {
				$this->lMargin = $this->pagedim[$this->page]['olm'];
				$this->rMargin = $this->pagedim[$this->page]['orm'];
				$this->SetY($this->tMargin);
			} else {
				// account for booklet mode
				if ($this->pagedim[$this->page]['olm'] != $this->pagedim[$oldpage]['olm']) {
					$deltam = $this->pagedim[$this->page]['olm'] - $this->pagedim[$this->page]['orm'];
					$this->lMargin += $deltam;
					$this->rMargin -= $deltam;
				}
			}
		} else {
			$this->Error('Wrong page number on setPage() function: '.$pnum);
		}
	}

	/**
	 * Reset pointer to the last document page.
	 * @param $resetmargins (boolean) if true reset left, right, top margins and Y position.
	 * @public
	 * @since 2.0.000 (2008-01-04)
	 * @see setPage(), getPage(), getNumPages()
	 */
	public function lastPage($resetmargins=false) {
		$this->setPage($this->getNumPages(), $resetmargins);
	}

	/**
	 * Get current document page number.
	 * @return int page number
	 * @public
	 * @since 2.1.000 (2008-01-07)
	 * @see setPage(), lastpage(), getNumPages()
	 */
	public function getPage() {
		return $this->page;
	}

	/**
	 * Get the total number of insered pages.
	 * @return int number of pages
	 * @public
	 * @since 2.1.000 (2008-01-07)
	 * @see setPage(), getPage(), lastpage()
	 */
	public function getNumPages() {
		return $this->numpages;
	}

	/**
	 * Adds a new TOC (Table Of Content) page to the document.
	 * @param $orientation (string) page orientation.
	 * @param $format (mixed) The format used for pages. It can be either: one of the string values specified at getPageSizeFromFormat() or an array of parameters specified at setPageFormat().
	 * @param $keepmargins (boolean) if true overwrites the default page margins with the current margins
	 * @public
	 * @since 5.0.001 (2010-05-06)
	 * @see AddPage(), startPage(), endPage(), endTOCPage()
	 */
	public function addTOCPage($orientation='', $format='', $keepmargins=false) {
		$this->AddPage($orientation, $format, $keepmargins, true);
	}

	/**
	 * Terminate the current TOC (Table Of Content) page
	 * @public
	 * @since 5.0.001 (2010-05-06)
	 * @see AddPage(), startPage(), endPage(), addTOCPage()
	 */
	public function endTOCPage() {
		$this->endPage(true);
	}

	/**
	 * Adds a new page to the document. If a page is already present, the Footer() method is called first to output the footer (if enabled). Then the page is added, the current position set to the top-left corner according to the left and top margins (or top-right if in RTL mode), and Header() is called to display the header (if enabled).
	 * The origin of the coordinate system is at the top-left corner (or top-right for RTL) and increasing ordinates go downwards.
	 * @param $orientation (string) page orientation. Possible values are (case insensitive):<ul><li>P or PORTRAIT (default)</li><li>L or LANDSCAPE</li></ul>
	 * @param $format (mixed) The format used for pages. It can be either: one of the string values specified at getPageSizeFromFormat() or an array of parameters specified at setPageFormat().
	 * @param $keepmargins (boolean) if true overwrites the default page margins with the current margins
	 * @param $tocpage (boolean) if true set the tocpage state to true (the added page will be used to display Table Of Content).
	 * @public
	 * @since 1.0
	 * @see startPage(), endPage(), addTOCPage(), endTOCPage(), getPageSizeFromFormat(), setPageFormat()
	 */
	public function AddPage($orientation='', $format='', $keepmargins=false, $tocpage=false) {
		if ($this->inxobj) {
			// we are inside an XObject template
			return;
		}
		if (!isset($this->original_lMargin) OR $keepmargins) {
			$this->original_lMargin = $this->lMargin;
		}
		if (!isset($this->original_rMargin) OR $keepmargins) {
			$this->original_rMargin = $this->rMargin;
		}
		// terminate previous page
		$this->endPage();
		// start new page
		$this->startPage($orientation, $format, $tocpage);
	}

	/**
	 * Terminate the current page
	 * @param $tocpage (boolean) if true set the tocpage state to false (end the page used to display Table Of Content).
	 * @public
	 * @since 4.2.010 (2008-11-14)
	 * @see AddPage(), startPage(), addTOCPage(), endTOCPage()
	 */
	public function endPage($tocpage=false) {
		// check if page is already closed
		if (($this->page == 0) OR ($this->numpages > $this->page) OR (!$this->pageopen[$this->page])) {
			return;
		}
		// print page footer
		$this->setFooter();
		// close page
		$this->_endpage();
		// mark page as closed
		$this->pageopen[$this->page] = false;
		if ($tocpage) {
			$this->tocpage = false;
		}
	}

	/**
	 * Starts a new page to the document. The page must be closed using the endPage() function.
	 * The origin of the coordinate system is at the top-left corner and increasing ordinates go downwards.
	 * @param $orientation (string) page orientation. Possible values are (case insensitive):<ul><li>P or PORTRAIT (default)</li><li>L or LANDSCAPE</li></ul>
	 * @param $format (mixed) The format used for pages. It can be either: one of the string values specified at getPageSizeFromFormat() or an array of parameters specified at setPageFormat().
	 * @param $tocpage (boolean) if true the page is designated to contain the Table-Of-Content.
	 * @since 4.2.010 (2008-11-14)
	 * @see AddPage(), endPage(), addTOCPage(), endTOCPage(), getPageSizeFromFormat(), setPageFormat()
	 * @public
	 */
	public function startPage($orientation='', $format='', $tocpage=false) {
		if ($tocpage) {
			$this->tocpage = true;
		}
		// move page numbers of documents to be attached
		if ($this->tocpage) {
			// move reference to unexistent pages (used for page attachments)
			// adjust outlines
			$tmpoutlines = $this->outlines;
			foreach ($tmpoutlines as $key => $outline) {
				if (!$outline['f'] AND ($outline['p'] > $this->numpages)) {
					$this->outlines[$key]['p'] = ($outline['p'] + 1);
				}
			}
			// adjust dests
			$tmpdests = $this->dests;
			foreach ($tmpdests as $key => $dest) {
				if (!$dest['f'] AND ($dest['p'] > $this->numpages)) {
					$this->dests[$key]['p'] = ($dest['p'] + 1);
				}
			}
			// adjust links
			$tmplinks = $this->links;
			foreach ($tmplinks as $key => $link) {
				if (!$link['f'] AND ($link['p'] > $this->numpages)) {
					$this->links[$key]['p'] = ($link['p'] + 1);
				}
			}
		}
		if ($this->numpages > $this->page) {
			// this page has been already added
			$this->setPage($this->page + 1);
			$this->SetY($this->tMargin);
			return;
		}
		// start a new page
		if ($this->state == 0) {
			$this->Open();
		}
		++$this->numpages;
		$this->swapMargins($this->booklet);
		// save current graphic settings
		$gvars = $this->getGraphicVars();
		// start new page
		$this->_beginpage($orientation, $format);
		// mark page as open
		$this->pageopen[$this->page] = true;
		// restore graphic settings
		$this->setGraphicVars($gvars);
		// mark this point
		$this->setPageMark();
		// print page header
		$this->setHeader();
		// restore graphic settings
		$this->setGraphicVars($gvars);
		// mark this point
		$this->setPageMark();
		// print table header (if any)
		$this->setTableHeader();
		// set mark for empty page check
		$this->emptypagemrk[$this->page]= $this->pagelen[$this->page];
	}

	/**
	 * Set start-writing mark on current page stream used to put borders and fills.
	 * Borders and fills are always created after content and inserted on the position marked by this method.
	 * This function must be called after calling Image() function for a background image.
	 * Background images must be always inserted before calling Multicell() or WriteHTMLCell() or WriteHTML() functions.
	 * @public
	 * @since 4.0.016 (2008-07-30)
	 */
	public function setPageMark() {
		$this->intmrk[$this->page] = $this->pagelen[$this->page];
		$this->bordermrk[$this->page] = $this->intmrk[$this->page];
		$this->setContentMark();
	}

	/**
	 * Set start-writing mark on selected page.
	 * Borders and fills are always created after content and inserted on the position marked by this method.
	 * @param $page (int) page number (default is the current page)
	 * @protected
	 * @since 4.6.021 (2009-07-20)
	 */
	protected function setContentMark($page=0) {
		if ($page <= 0) {
			$page = $this->page;
		}
		if (isset($this->footerlen[$page])) {
			$this->cntmrk[$page] = $this->pagelen[$page] - $this->footerlen[$page];
		} else {
			$this->cntmrk[$page] = $this->pagelen[$page];
		}
	}

	/**
	 * Set header data.
	 * @param $ln (string) header image logo
	 * @param $lw (string) header image logo width in mm
	 * @param $ht (string) string to print as title on document header
	 * @param $hs (string) string to print on document header
	 * @param $tc (array) RGB array color for text.
	 * @param $lc (array) RGB array color for line.
	 * @public
	 */
	public function setHeaderData($ln='', $lw=0, $ht='', $hs='', $tc=array(0,0,0), $lc=array(0,0,0)) {
		$this->header_logo = $ln;
		$this->header_logo_width = $lw;
		$this->header_title = $ht;
		$this->header_string = $hs;
		$this->header_text_color = $tc;
		$this->header_line_color = $lc;
	}

	/**
	 * Set footer data.
	 * @param $tc (array) RGB array color for text.
	 * @param $lc (array) RGB array color for line.
	 * @public
	 */
	public function setFooterData($tc=array(0,0,0), $lc=array(0,0,0)) {
		$this->footer_text_color = $tc;
		$this->footer_line_color = $lc;
	}

	/**
	 * Returns header data:
	 * <ul><li>$ret['logo'] = logo image</li><li>$ret['logo_width'] = width of the image logo in user units</li><li>$ret['title'] = header title</li><li>$ret['string'] = header description string</li></ul>
	 * @return array()
	 * @public
	 * @since 4.0.012 (2008-07-24)
	 */
	public function getHeaderData() {
		$ret = array();
		$ret['logo'] = $this->header_logo;
		$ret['logo_width'] = $this->header_logo_width;
		$ret['title'] = $this->header_title;
		$ret['string'] = $this->header_string;
		$ret['text_color'] = $this->header_text_color;
		$ret['line_color'] = $this->header_line_color;
		return $ret;
	}

	/**
	 * Set header margin.
	 * (minimum distance between header and top page margin)
	 * @param $hm (int) distance in user units
	 * @public
	 */
	public function setHeaderMargin($hm=10) {
		$this->header_margin = $hm;
	}

	/**
	 * Returns header margin in user units.
	 * @return float
	 * @since 4.0.012 (2008-07-24)
	 * @public
	 */
	public function getHeaderMargin() {
		return $this->header_margin;
	}

	/**
	 * Set footer margin.
	 * (minimum distance between footer and bottom page margin)
	 * @param $fm (int) distance in user units
	 * @public
	 */
	public function setFooterMargin($fm=10) {
		$this->footer_margin = $fm;
	}

	/**
	 * Returns footer margin in user units.
	 * @return float
	 * @since 4.0.012 (2008-07-24)
	 * @public
	 */
	public function getFooterMargin() {
		return $this->footer_margin;
	}
	/**
	 * Set a flag to print page header.
	 * @param $val (boolean) set to true to print the page header (default), false otherwise.
	 * @public
	 */
	public function setPrintHeader($val=true) {
		$this->print_header = $val ? true : false;
	}

	/**
	 * Set a flag to print page footer.
	 * @param $val (boolean) set to true to print the page footer (default), false otherwise.
	 * @public
	 */
	public function setPrintFooter($val=true) {
		$this->print_footer = $val ? true : false;
	}

	/**
	 * Return the right-bottom (or left-bottom for RTL) corner X coordinate of last inserted image
	 * @return float
	 * @public
	 */
	public function getImageRBX() {
		return $this->img_rb_x;
	}

	/**
	 * Return the right-bottom (or left-bottom for RTL) corner Y coordinate of last inserted image
	 * @return float
	 * @public
	 */
	public function getImageRBY() {
		return $this->img_rb_y;
	}

	/**
	 * Reset the xobject template used by Header() method.
	 * @public
	 */
	public function resetHeaderTemplate() {
		$this->header_xobjid = false;
	}

	/**
	 * Set a flag to automatically reset the xobject template used by Header() method at each page.
	 * @param $val (boolean) set to true to reset Header xobject template at each page, false otherwise.
	 * @public
	 */
	public function setHeaderTemplateAutoreset($val=true) {
		$this->header_xobj_autoreset = $val ? true : false;
	}

	/**
	 * This method is used to render the page header.
	 * It is automatically called by AddPage() and could be overwritten in your own inherited class.
	 * @public
	 */
	public function Header() {
		if ($this->header_xobjid === false) {
			// start a new XObject Template
			$this->header_xobjid = $this->startTemplate($this->w, $this->tMargin);
			$headerfont = $this->getHeaderFont();
			$headerdata = $this->getHeaderData();
			$this->y = $this->header_margin;
			if ($this->rtl) {
				$this->x = $this->w - $this->original_rMargin;
			} else {
				$this->x = $this->original_lMargin;
			}
			if (($headerdata['logo']) AND ($headerdata['logo'] != K_BLANK_IMAGE)) {
				$imgtype = TCPDF_IMAGES::getImageFileType(K_PATH_IMAGES.$headerdata['logo']);
				if (($imgtype == 'eps') OR ($imgtype == 'ai')) {
					$this->ImageEps(K_PATH_IMAGES.$headerdata['logo'], '', '', $headerdata['logo_width']);
				} elseif ($imgtype == 'svg') {
					$this->ImageSVG(K_PATH_IMAGES.$headerdata['logo'], '', '', $headerdata['logo_width']);
				} else {
					$this->Image(K_PATH_IMAGES.$headerdata['logo'], '', '', $headerdata['logo_width']);
				}
				$imgy = $this->getImageRBY();
			} else {
				$imgy = $this->y;
			}
			$cell_height = $this->getCellHeight($headerfont[2] / $this->k);
			// set starting margin for text data cell
			if ($this->getRTL()) {
				$header_x = $this->original_rMargin + ($headerdata['logo_width'] * 1.1);
			} else {
				$header_x = $this->original_lMargin + ($headerdata['logo_width'] * 1.1);
			}
			$cw = $this->w - $this->original_lMargin - $this->original_rMargin - ($headerdata['logo_width'] * 1.1);
			$this->SetTextColorArray($this->header_text_color);
			// header title
			$this->SetFont($headerfont[0], 'B', $headerfont[2] + 1);
			$this->SetX($header_x);
			$this->Cell($cw, $cell_height, $headerdata['title'], 0, 1, '', 0, '', 0);
			// header string
			$this->SetFont($headerfont[0], $headerfont[1], $headerfont[2]);
			$this->SetX($header_x);
			$this->MultiCell($cw, $cell_height, $headerdata['string'], 0, '', 0, 1, '', '', true, 0, false, true, 0, 'T', false);
			// print an ending header line
			$this->SetLineStyle(array('width' => 0.85 / $this->k, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $headerdata['line_color']));
			$this->SetY((2.835 / $this->k) + max($imgy, $this->y));
			if ($this->rtl) {
				$this->SetX($this->original_rMargin);
			} else {
				$this->SetX($this->original_lMargin);
			}
			$this->Cell(($this->w - $this->original_lMargin - $this->original_rMargin), 0, '', 'T', 0, 'C');
			$this->endTemplate();
		}
		// print header template
		$x = 0;
		$dx = 0;
		if (!$this->header_xobj_autoreset AND $this->booklet AND (($this->page % 2) == 0)) {
			// adjust margins for booklet mode
			$dx = ($this->original_lMargin - $this->original_rMargin);
		}
		if ($this->rtl) {
			$x = $this->w + $dx;
		} else {
			$x = 0 + $dx;
		}
		$this->printTemplate($this->header_xobjid, $x, 0, 0, 0, '', '', false);
		if ($this->header_xobj_autoreset) {
			// reset header xobject template at each page
			$this->header_xobjid = false;
		}
	}

	/**
	 * This method is used to render the page footer.
	 * It is automatically called by AddPage() and could be overwritten in your own inherited class.
	 * @public
	 */
	public function Footer() {
		$cur_y = $this->y;
		$this->SetTextColorArray($this->footer_text_color);
		//set style for cell border
		$line_width = (0.85 / $this->k);
		$this->SetLineStyle(array('width' => $line_width, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $this->footer_line_color));
		//print document barcode
		$barcode = $this->getBarcode();
		if (!empty($barcode)) {
			$this->Ln($line_width);
			$barcode_width = round(($this->w - $this->original_lMargin - $this->original_rMargin) / 3);
			$style = array(
				'position' => $this->rtl?'R':'L',
				'align' => $this->rtl?'R':'L',
				'stretch' => false,
				'fitwidth' => true,
				'cellfitalign' => '',
				'border' => false,
				'padding' => 0,
				'fgcolor' => array(0,0,0),
				'bgcolor' => false,
				'text' => false
			);
			$this->write1DBarcode($barcode, 'C128', '', $cur_y + $line_width, '', (($this->footer_margin / 3) - $line_width), 0.3, $style, '');
		}
		$w_page = isset($this->l['w_page']) ? $this->l['w_page'].' ' : '';
		if (empty($this->pagegroups)) {
			$pagenumtxt = $w_page.$this->getAliasNumPage().' / '.$this->getAliasNbPages();
		} else {
			$pagenumtxt = $w_page.$this->getPageNumGroupAlias().' / '.$this->getPageGroupAlias();
		}
		$this->SetY($cur_y);
		//Print page number
		if ($this->getRTL()) {
			$this->SetX($this->original_rMargin);
			$this->Cell(0, 0, $pagenumtxt, 'T', 0, 'L');
		} else {
			$this->SetX($this->original_lMargin);
			$this->Cell(0, 0, $this->getAliasRightShift().$pagenumtxt, 'T', 0, 'R');
		}
	}

	/**
	 * This method is used to render the page header.
	 * @protected
	 * @since 4.0.012 (2008-07-24)
	 */
	protected function setHeader() {
		if (!$this->print_header OR ($this->state != 2)) {
			return;
		}
		$this->InHeader = true;
		$this->setGraphicVars($this->default_graphic_vars);
		$temp_thead = $this->thead;
		$temp_theadMargins = $this->theadMargins;
		$lasth = $this->lasth;
		$newline = $this->newline;
		$this->_outSaveGraphicsState();
		$this->rMargin = $this->original_rMargin;
		$this->lMargin = $this->original_lMargin;
		$this->SetCellPadding(0);
		//set current position
		if ($this->rtl) {
			$this->SetXY($this->original_rMargin, $this->header_margin);
		} else {
			$this->SetXY($this->original_lMargin, $this->header_margin);
		}
		$this->SetFont($this->header_font[0], $this->header_font[1], $this->header_font[2]);
		$this->Header();
		//restore position
		if ($this->rtl) {
			$this->SetXY($this->original_rMargin, $this->tMargin);
		} else {
			$this->SetXY($this->original_lMargin, $this->tMargin);
		}
		$this->_outRestoreGraphicsState();
		$this->lasth = $lasth;
		$this->thead = $temp_thead;
		$this->theadMargins = $temp_theadMargins;
		$this->newline = $newline;
		$this->InHeader = false;
	}

	/**
	 * This method is used to render the page footer.
	 * @protected
	 * @since 4.0.012 (2008-07-24)
	 */
	protected function setFooter() {
		if ($this->state != 2) {
			return;
		}
		$this->InFooter = true;
		// save current graphic settings
		$gvars = $this->getGraphicVars();
		// mark this point
		$this->footerpos[$this->page] = $this->pagelen[$this->page];
		$this->_out("\n");
		if ($this->print_footer) {
			$this->setGraphicVars($this->default_graphic_vars);
			$this->current_column = 0;
			$this->num_columns = 1;
			$temp_thead = $this->thead;
			$temp_theadMargins = $this->theadMargins;
			$lasth = $this->lasth;
			$this->_outSaveGraphicsState();
			$this->rMargin = $this->original_rMargin;
			$this->lMargin = $this->original_lMargin;
			$this->SetCellPadding(0);
			//set current position
			$footer_y = $this->h - $this->footer_margin;
			if ($this->rtl) {
				$this->SetXY($this->original_rMargin, $footer_y);
			} else {
				$this->SetXY($this->original_lMargin, $footer_y);
			}
			$this->SetFont($this->footer_font[0], $this->footer_font[1], $this->footer_font[2]);
			$this->Footer();
			//restore position
			if ($this->rtl) {
				$this->SetXY($this->original_rMargin, $this->tMargin);
			} else {
				$this->SetXY($this->original_lMargin, $this->tMargin);
			}
			$this->_outRestoreGraphicsState();
			$this->lasth = $lasth;
			$this->thead = $temp_thead;
			$this->theadMargins = $temp_theadMargins;
		}
		// restore graphic settings
		$this->setGraphicVars($gvars);
		$this->current_column = $gvars['current_column'];
		$this->num_columns = $gvars['num_columns'];
		// calculate footer length
		$this->footerlen[$this->page] = $this->pagelen[$this->page] - $this->footerpos[$this->page] + 1;
		$this->InFooter = false;
	}

	/**
	 * Check if we are on the page body (excluding page header and footer).
	 * @return true if we are not in page header nor in page footer, false otherwise.
	 * @protected
	 * @since 5.9.091 (2011-06-15)
	 */
	protected function inPageBody() {
		return (($this->InHeader === false) AND ($this->InFooter === false));
	}

	/**
	 * This method is used to render the table header on new page (if any).
	 * @protected
	 * @since 4.5.030 (2009-03-25)
	 */
	protected function setTableHeader() {
		if ($this->num_columns > 1) {
			// multi column mode
			return;
		}
		if (isset($this->theadMargins['top'])) {
			// restore the original top-margin
			$this->tMargin = $this->theadMargins['top'];
			$this->pagedim[$this->page]['tm'] = $this->tMargin;
			$this->y = $this->tMargin;
		}
		if (!TCPDF_STATIC::empty_string($this->thead) AND (!$this->inthead)) {
			// set margins
			$prev_lMargin = $this->lMargin;
			$prev_rMargin = $this->rMargin;
			$prev_cell_padding = $this->cell_padding;
			$this->lMargin = $this->theadMargins['lmargin'] + ($this->pagedim[$this->page]['olm'] - $this->pagedim[$this->theadMargins['page']]['olm']);
			$this->rMargin = $this->theadMargins['rmargin'] + ($this->pagedim[$this->page]['orm'] - $this->pagedim[$this->theadMargins['page']]['orm']);
			$this->cell_padding = $this->theadMargins['cell_padding'];
			if ($this->rtl) {
				$this->x = $this->w - $this->rMargin;
			} else {
				$this->x = $this->lMargin;
			}
			// account for special "cell" mode
			if ($this->theadMargins['cell']) {
				if ($this->rtl) {
					$this->x -= $this->cell_padding['R'];
				} else {
					$this->x += $this->cell_padding['L'];
				}
			}
			$gvars = $this->getGraphicVars();
			if (!empty($this->theadMargins['gvars'])) {
				// set the correct graphic style
				$this->setGraphicVars($this->theadMargins['gvars']);
				$this->rMargin = $gvars['rMargin'];
				$this->lMargin = $gvars['lMargin'];
			}
			// print table header
			$this->writeHTML($this->thead, false, false, false, false, '');
			$this->setGraphicVars($gvars);
			// set new top margin to skip the table headers
			if (!isset($this->theadMargins['top'])) {
				$this->theadMargins['top'] = $this->tMargin;
			}
			// store end of header position
			if (!isset($this->columns[0]['th'])) {
				$this->columns[0]['th'] = array();
			}
			$this->columns[0]['th']['\''.$this->page.'\''] = $this->y;
			$this->tMargin = $this->y;
			$this->pagedim[$this->page]['tm'] = $this->tMargin;
			$this->lasth = 0;
			$this->lMargin = $prev_lMargin;
			$this->rMargin = $prev_rMargin;
			$this->cell_padding = $prev_cell_padding;
		}
	}

	/**
	 * Returns the current page number.
	 * @return int page number
	 * @public
	 * @since 1.0
	 * @see getAliasNbPages()
	 */
	public function PageNo() {
		return $this->page;
	}

	/**
	 * Returns the array of spot colors.
	 * @return (array) Spot colors array.
	 * @public
	 * @since 6.0.038 (2013-09-30)
	 */
	public function getAllSpotColors() {
		return $this->spot_colors;
	}

	/**
	 * Defines a new spot color.
	 * It can be expressed in RGB components or gray scale.
	 * The method can be called before the first page is created and the value is retained from page to page.
	 * @param $name (string) Full name of the spot color.
	 * @param $c (float) Cyan color for CMYK. Value between 0 and 100.
	 * @param $m (float) Magenta color for CMYK. Value between 0 and 100.
	 * @param $y (float) Yellow color for CMYK. Value between 0 and 100.
	 * @param $k (float) Key (Black) color for CMYK. Value between 0 and 100.
	 * @public
	 * @since 4.0.024 (2008-09-12)
	 * @see SetDrawSpotColor(), SetFillSpotColor(), SetTextSpotColor()
	 */
	public function AddSpotColor($name, $c, $m, $y, $k) {
		if (!isset($this->spot_colors[$name])) {
			$i = (1 + count($this->spot_colors));
			$this->spot_colors[$name] = array('C' => $c, 'M' => $m, 'Y' => $y, 'K' => $k, 'name' => $name, 'i' => $i);
		}
	}

	/**
	 * Set the spot color for the specified type ('draw', 'fill', 'text').
	 * @param $type (string) Type of object affected by this color: ('draw', 'fill', 'text').
	 * @param $name (string) Name of the spot color.
	 * @param $tint (float) Intensity of the color (from 0 to 100 ; 100 = full intensity by default).
	 * @return (string) PDF color command.
	 * @public
	 * @since 5.9.125 (2011-10-03)
	 */
	public function setSpotColor($type, $name, $tint=100) {
		$spotcolor = TCPDF_COLORS::getSpotColor($name, $this->spot_colors);
		if ($spotcolor === false) {
			$this->Error('Undefined spot color: '.$name.', you must add it using the AddSpotColor() method.');
		}
		$tint = (max(0, min(100, $tint)) / 100);
		$pdfcolor = sprintf('/CS%d ', $this->spot_colors[$name]['i']);
		switch ($type) {
			case 'draw': {
				$pdfcolor .= sprintf('CS %F SCN', $tint);
				$this->DrawColor = $pdfcolor;
				$this->strokecolor = $spotcolor;
				break;
			}
			case 'fill': {
				$pdfcolor .= sprintf('cs %F scn', $tint);
				$this->FillColor = $pdfcolor;
				$this->bgcolor = $spotcolor;
				break;
			}
			case 'text': {
				$pdfcolor .= sprintf('cs %F scn', $tint);
				$this->TextColor = $pdfcolor;
				$this->fgcolor = $spotcolor;
				break;
			}
		}
		$this->ColorFlag = ($this->FillColor != $this->TextColor);
		if ($this->state == 2) {
			$this->_out($pdfcolor);
		}
		if ($this->inxobj) {
			// we are inside an XObject template
			$this->xobjects[$this->xobjid]['spot_colors'][$name] = $this->spot_colors[$name];
		}
		return $pdfcolor;
	}

	/**
	 * Defines the spot color used for all drawing operations (lines, rectangles and cell borders).
	 * @param $name (string) Name of the spot color.
	 * @param $tint (float) Intensity of the color (from 0 to 100 ; 100 = full intensity by default).
	 * @public
	 * @since 4.0.024 (2008-09-12)
	 * @see AddSpotColor(), SetFillSpotColor(), SetTextSpotColor()
	 */
	public function SetDrawSpotColor($name, $tint=100) {
		$this->setSpotColor('draw', $name, $tint);
	}

	/**
	 * Defines the spot color used for all filling operations (filled rectangles and cell backgrounds).
	 * @param $name (string) Name of the spot color.
	 * @param $tint (float) Intensity of the color (from 0 to 100 ; 100 = full intensity by default).
	 * @public
	 * @since 4.0.024 (2008-09-12)
	 * @see AddSpotColor(), SetDrawSpotColor(), SetTextSpotColor()
	 */
	public function SetFillSpotColor($name, $tint=100) {
		$this->setSpotColor('fill', $name, $tint);
	}

	/**
	 * Defines the spot color used for text.
	 * @param $name (string) Name of the spot color.
	 * @param $tint (int) Intensity of the color (from 0 to 100 ; 100 = full intensity by default).
	 * @public
	 * @since 4.0.024 (2008-09-12)
	 * @see AddSpotColor(), SetDrawSpotColor(), SetFillSpotColor()
	 */
	public function SetTextSpotColor($name, $tint=100) {
		$this->setSpotColor('text', $name, $tint);
	}

	/**
	 * Set the color array for the specified type ('draw', 'fill', 'text').
	 * It can be expressed in RGB, CMYK or GRAY SCALE components.
	 * The method can be called before the first page is created and the value is retained from page to page.
	 * @param $type (string) Type of object affected by this color: ('draw', 'fill', 'text').
	 * @param $color (array) Array of colors (1=gray, 3=RGB, 4=CMYK or 5=spotcolor=CMYK+name values).
	 * @param $ret (boolean) If true do not send the PDF command.
	 * @return (string) The PDF command or empty string.
	 * @public
	 * @since 3.1.000 (2008-06-11)
	 */
	public function setColorArray($type, $color, $ret=false) {
		if (is_array($color)) {
			$color = array_values($color);
			// component: grey, RGB red or CMYK cyan
			$c = isset($color[0]) ? $color[0] : -1;
			// component: RGB green or CMYK magenta
			$m = isset($color[1]) ? $color[1] : -1;
			// component: RGB blue or CMYK yellow
			$y = isset($color[2]) ? $color[2] : -1;
			// component: CMYK black
			$k = isset($color[3]) ? $color[3] : -1;
			// color name
			$name = isset($color[4]) ? $color[4] : '';
			if ($c >= 0) {
				return $this->setColor($type, $c, $m, $y, $k, $ret, $name);
			}
		}
		return '';
	}

	/**
	 * Defines the color used for all drawing operations (lines, rectangles and cell borders).
	 * It can be expressed in RGB, CMYK or GRAY SCALE components.
	 * The method can be called before the first page is created and the value is retained from page to page.
	 * @param $color (array) Array of colors (1, 3 or 4 values).
	 * @param $ret (boolean) If true do not send the PDF command.
	 * @return string the PDF command
	 * @public
	 * @since 3.1.000 (2008-06-11)
	 * @see SetDrawColor()
	 */
	public function SetDrawColorArray($color, $ret=false) {
		return $this->setColorArray('draw', $color, $ret);
	}

	/**
	 * Defines the color used for all filling operations (filled rectangles and cell backgrounds).
	 * It can be expressed in RGB, CMYK or GRAY SCALE components.
	 * The method can be called before the first page is created and the value is retained from page to page.
	 * @param $color (array) Array of colors (1, 3 or 4 values).
	 * @param $ret (boolean) If true do not send the PDF command.
	 * @public
	 * @since 3.1.000 (2008-6-11)
	 * @see SetFillColor()
	 */
	public function SetFillColorArray($color, $ret=false) {
		return $this->setColorArray('fill', $color, $ret);
	}

	/**
	 * Defines the color used for text. It can be expressed in RGB components or gray scale.
	 * The method can be called before the first page is created and the value is retained from page to page.
	 * @param $color (array) Array of colors (1, 3 or 4 values).
	 * @param $ret (boolean) If true do not send the PDF command.
	 * @public
	 * @since 3.1.000 (2008-6-11)
	 * @see SetFillColor()
	 */
	public function SetTextColorArray($color, $ret=false) {
		return $this->setColorArray('text', $color, $ret);
	}

	/**
	 * Defines the color used by the specified type ('draw', 'fill', 'text').
	 * @param $type (string) Type of object affected by this color: ('draw', 'fill', 'text').
	 * @param $col1 (float) GRAY level for single color, or Red color for RGB (0-255), or CYAN color for CMYK (0-100).
	 * @param $col2 (float) GREEN color for RGB (0-255), or MAGENTA color for CMYK (0-100).
	 * @param $col3 (float) BLUE color for RGB (0-255), or YELLOW color for CMYK (0-100).
	 * @param $col4 (float) KEY (BLACK) color for CMYK (0-100).
	 * @param $ret (boolean) If true do not send the command.
	 * @param $name (string) spot color name (if any)
	 * @return (string) The PDF command or empty string.
	 * @public
	 * @since 5.9.125 (2011-10-03)
	 */
	public function setColor($type, $col1=0, $col2=-1, $col3=-1, $col4=-1, $ret=false, $name='') {
		// set default values
		if (!is_numeric($col1)) {
			$col1 = 0;
		}
		if (!is_numeric($col2)) {
			$col2 = -1;
		}
		if (!is_numeric($col3)) {
			$col3 = -1;
		}
		if (!is_numeric($col4)) {
			$col4 = -1;
		}
		// set color by case
		$suffix = '';
		if (($col2 == -1) AND ($col3 == -1) AND ($col4 == -1)) {
			// Grey scale
			$col1 = max(0, min(255, $col1));
			$intcolor = array('G' => $col1);
			$pdfcolor = sprintf('%F ', ($col1 / 255));
			$suffix = 'g';
		} elseif ($col4 == -1) {
			// RGB
			$col1 = max(0, min(255, $col1));
			$col2 = max(0, min(255, $col2));
			$col3 = max(0, min(255, $col3));
			$intcolor = array('R' => $col1, 'G' => $col2, 'B' => $col3);
			$pdfcolor = sprintf('%F %F %F ', ($col1 / 255), ($col2 / 255), ($col3 / 255));
			$suffix = 'rg';
		} else {
			$col1 = max(0, min(100, $col1));
			$col2 = max(0, min(100, $col2));
			$col3 = max(0, min(100, $col3));
			$col4 = max(0, min(100, $col4));
			if (empty($name)) {
				// CMYK
				$intcolor = array('C' => $col1, 'M' => $col2, 'Y' => $col3, 'K' => $col4);
				$pdfcolor = sprintf('%F %F %F %F ', ($col1 / 100), ($col2 / 100), ($col3 / 100), ($col4 / 100));
				$suffix = 'k';
			} else {
				// SPOT COLOR
				$intcolor = array('C' => $col1, 'M' => $col2, 'Y' => $col3, 'K' => $col4, 'name' => $name);
				$this->AddSpotColor($name, $col1, $col2, $col3, $col4);
				$pdfcolor = $this->setSpotColor($type, $name, 100);
			}
		}
		switch ($type) {
			case 'draw': {
				$pdfcolor .= strtoupper($suffix);
				$this->DrawColor = $pdfcolor;
				$this->strokecolor = $intcolor;
				break;
			}
			case 'fill': {
				$pdfcolor .= $suffix;
				$this->FillColor = $pdfcolor;
				$this->bgcolor = $intcolor;
				break;
			}
			case 'text': {
				$pdfcolor .= $suffix;
				$this->TextColor = $pdfcolor;
				$this->fgcolor = $intcolor;
				break;
			}
		}
		$this->ColorFlag = ($this->FillColor != $this->TextColor);
		if (($type != 'text') AND ($this->state == 2)) {
			if (!$ret) {
				$this->_out($pdfcolor);
			}
			return $pdfcolor;
		}
		return '';
	}

	/**
	 * Defines the color used for all drawing operations (lines, rectangles and cell borders). It can be expressed in RGB components or gray scale. The method can be called before the first page is created and the value is retained from page to page.
	 * @param $col1 (float) GRAY level for single color, or Red color for RGB (0-255), or CYAN color for CMYK (0-100).
	 * @param $col2 (float) GREEN color for RGB (0-255), or MAGENTA color for CMYK (0-100).
	 * @param $col3 (float) BLUE color for RGB (0-255), or YELLOW color for CMYK (0-100).
	 * @param $col4 (float) KEY (BLACK) color for CMYK (0-100).
	 * @param $ret (boolean) If true do not send the command.
	 * @param $name (string) spot color name (if any)
	 * @return string the PDF command
	 * @public
	 * @since 1.3
	 * @see SetDrawColorArray(), SetFillColor(), SetTextColor(), Line(), Rect(), Cell(), MultiCell()
	 */
	public function SetDrawColor($col1=0, $col2=-1, $col3=-1, $col4=-1, $ret=false, $name='') {
		return $this->setColor('draw', $col1, $col2, $col3, $col4, $ret, $name);
	}

	/**
	 * Defines the color used for all filling operations (filled rectangles and cell backgrounds). It can be expressed in RGB components or gray scale. The method can be called before the first page is created and the value is retained from page to page.
	 * @param $col1 (float) GRAY level for single color, or Red color for RGB (0-255), or CYAN color for CMYK (0-100).
	 * @param $col2 (float) GREEN color for RGB (0-255), or MAGENTA color for CMYK (0-100).
	 * @param $col3 (float) BLUE color for RGB (0-255), or YELLOW color for CMYK (0-100).
	 * @param $col4 (float) KEY (BLACK) color for CMYK (0-100).
	 * @param $ret (boolean) If true do not send the command.
	 * @param $name (string) Spot color name (if any).
	 * @return (string) The PDF command.
	 * @public
	 * @since 1.3
	 * @see SetFillColorArray(), SetDrawColor(), SetTextColor(), Rect(), Cell(), MultiCell()
	 */
	public function SetFillColor($col1=0, $col2=-1, $col3=-1, $col4=-1, $ret=false, $name='') {
		return $this->setColor('fill', $col1, $col2, $col3, $col4, $ret, $name);
	}

	/**
	 * Defines the color used for text. It can be expressed in RGB components or gray scale. The method can be called before the first page is created and the value is retained from page to page.
	 * @param $col1 (float) GRAY level for single color, or Red color for RGB (0-255), or CYAN color for CMYK (0-100).
	 * @param $col2 (float) GREEN color for RGB (0-255), or MAGENTA color for CMYK (0-100).
	 * @param $col3 (float) BLUE color for RGB (0-255), or YELLOW color for CMYK (0-100).
	 * @param $col4 (float) KEY (BLACK) color for CMYK (0-100).
	 * @param $ret (boolean) If true do not send the command.
	 * @param $name (string) Spot color name (if any).
	 * @return (string) Empty string.
	 * @public
	 * @since 1.3
	 * @see SetTextColorArray(), SetDrawColor(), SetFillColor(), Text(), Cell(), MultiCell()
	 */
	public function SetTextColor($col1=0, $col2=-1, $col3=-1, $col4=-1, $ret=false, $name='') {
		return $this->setColor('text', $col1, $col2, $col3, $col4, $ret, $name);
	}

	/**
	 * Returns the length of a string in user unit. A font must be selected.<br>
	 * @param $s (string) The string whose length is to be computed
	 * @param $fontname (string) Family font. It can be either a name defined by AddFont() or one of the standard families. It is also possible to pass an empty string, in that case, the current family is retained.
	 * @param $fontstyle (string) Font style. Possible values are (case insensitive):<ul><li>empty string: regular</li><li>B: bold</li><li>I: italic</li><li>U: underline</li><li>D: line-through</li><li>O: overline</li></ul> or any combination. The default value is regular.
	 * @param $fontsize (float) Font size in points. The default value is the current size.
	 * @param $getarray (boolean) if true returns an array of characters widths, if false returns the total length.
	 * @return mixed int total string length or array of characted widths
	 * @author Nicola Asuni
	 * @public
	 * @since 1.2
	 */
	public function GetStringWidth($s, $fontname='', $fontstyle='', $fontsize=0, $getarray=false) {
		return $this->GetArrStringWidth(TCPDF_FONTS::utf8Bidi(TCPDF_FONTS::UTF8StringToArray($s, $this->isunicode, $this->CurrentFont), $s, $this->tmprtl, $this->isunicode, $this->CurrentFont), $fontname, $fontstyle, $fontsize, $getarray);
	}

	/**
	 * Returns the string length of an array of chars in user unit or an array of characters widths. A font must be selected.<br>
	 * @param $sa (string) The array of chars whose total length is to be computed
	 * @param $fontname (string) Family font. It can be either a name defined by AddFont() or one of the standard families. It is also possible to pass an empty string, in that case, the current family is retained.
	 * @param $fontstyle (string) Font style. Possible values are (case insensitive):<ul><li>empty string: regular</li><li>B: bold</li><li>I: italic</li><li>U: underline</li><li>D: line through</li><li>O: overline</li></ul> or any combination. The default value is regular.
	 * @param $fontsize (float) Font size in points. The default value is the current size.
	 * @param $getarray (boolean) if true returns an array of characters widths, if false returns the total length.
	 * @return mixed int total string length or array of characted widths
	 * @author Nicola Asuni
	 * @public
	 * @since 2.4.000 (2008-03-06)
	 */
	public function GetArrStringWidth($sa, $fontname='', $fontstyle='', $fontsize=0, $getarray=false) {
		// store current values
		if (!TCPDF_STATIC::empty_string($fontname)) {
			$prev_FontFamily = $this->FontFamily;
			$prev_FontStyle = $this->FontStyle;
			$prev_FontSizePt = $this->FontSizePt;
			$this->SetFont($fontname, $fontstyle, $fontsize, '', 'default', false);
		}
		// convert UTF-8 array to Latin1 if required
		if ($this->isunicode AND (!$this->isUnicodeFont())) {
			$sa = TCPDF_FONTS::UTF8ArrToLatin1Arr($sa);
		}
		$w = 0; // total width
		$wa = array(); // array of characters widths
		foreach ($sa as $ck => $char) {
			// character width
			$cw = $this->GetCharWidth($char, isset($sa[($ck + 1)]));
			$wa[] = $cw;
			$w += $cw;
		}
		// restore previous values
		if (!TCPDF_STATIC::empty_string($fontname)) {
			$this->SetFont($prev_FontFamily, $prev_FontStyle, $prev_FontSizePt, '', 'default', false);
		}
		if ($getarray) {
			return $wa;
		}
		return $w;
	}

	/**
	 * Returns the length of the char in user unit for the current font considering current stretching and spacing (tracking).
	 * @param $char (int) The char code whose length is to be returned
	 * @param $notlast (boolean) If false ignore the font-spacing.
	 * @return float char width
	 * @author Nicola Asuni
	 * @public
	 * @since 2.4.000 (2008-03-06)
	 */
	public function GetCharWidth($char, $notlast=true) {
		// get raw width
		$chw = $this->getRawCharWidth($char);
		if (($this->font_spacing < 0) OR (($this->font_spacing > 0) AND $notlast)) {
			// increase/decrease font spacing
			$chw += $this->font_spacing;
		}
		if ($this->font_stretching != 100) {
			// fixed stretching mode
			$chw *= ($this->font_stretching / 100);
		}
		return $chw;
	}

	/**
	 * Returns the length of the char in user unit for the current font.
	 * @param $char (int) The char code whose length is to be returned
	 * @return float char width
	 * @author Nicola Asuni
	 * @public
	 * @since 5.9.000 (2010-09-28)
	 */
	public function getRawCharWidth($char) {
		if ($char == 173) {
			// SHY character will not be printed
			return (0);
		}
		if (isset($this->CurrentFont['cw'][$char])) {
			$w = $this->CurrentFont['cw'][$char];
		} elseif (isset($this->CurrentFont['dw'])) {
			// default width
			$w = $this->CurrentFont['dw'];
		} elseif (isset($this->CurrentFont['cw'][32])) {
			// default width
			$w = $this->CurrentFont['cw'][32];
		} else {
			$w = 600;
		}
		return $this->getAbsFontMeasure($w);
	}

	/**
	 * Returns the numbero of characters in a string.
	 * @param $s (string) The input string.
	 * @return int number of characters
	 * @public
	 * @since 2.0.0001 (2008-01-07)
	 */
	public function GetNumChars($s) {
		if ($this->isUnicodeFont()) {
			return count(TCPDF_FONTS::UTF8StringToArray($s, $this->isunicode, $this->CurrentFont));
		}
		return strlen($s);
	}

	

	/**
	 * Creates a new internal link and returns its identifier. An internal link is a clickable area which directs to another place within the document.<br />
	 * The identifier can then be passed to Cell(), Write(), Image() or Link(). The destination is defined with SetLink().
	 * @public
	 * @since 1.5
	 * @see Cell(), Write(), Image(), Link(), SetLink()
	 */
	public function AddLink() {
		// create a new internal link
		$n = count($this->links) + 1;
		$this->links[$n] = array('p' => 0, 'y' => 0, 'f' => false);
		return $n;
	}

	/**
	 * Defines the page and position a link points to.
	 * @param $link (int) The link identifier returned by AddLink()
	 * @param $y (float) Ordinate of target position; -1 indicates the current position. The default value is 0 (top of page)
	 * @param $page (int) Number of target page; -1 indicates the current page (default value). If you prefix a page number with the * character, then this page will not be changed when adding/deleting/moving pages.
	 * @public
	 * @since 1.5
	 * @see AddLink()
	 */
	public function SetLink($link, $y=0, $page=-1) {
		$fixed = false;
		if (!empty($page) AND ($page[0] == '*')) {
			$page = intval(substr($page, 1));
			// this page number will not be changed when moving/add/deleting pages
			$fixed = true;
		}
		if ($page < 0) {
			$page = $this->page;
		}
		if ($y == -1) {
			$y = $this->y;
		}
		$this->links[$link] = array('p' => $page, 'y' => $y, 'f' => $fixed);
	}

	/**
	 * Puts a link on a rectangular area of the page.
	 * Text or image links are generally put via Cell(), Write() or Image(), but this method can be useful for instance to define a clickable area inside an image.
	 * @param $x (float) Abscissa of the upper-left corner of the rectangle
	 * @param $y (float) Ordinate of the upper-left corner of the rectangle
	 * @param $w (float) Width of the rectangle
	 * @param $h (float) Height of the rectangle
	 * @param $link (mixed) URL or identifier returned by AddLink()
	 * @param $spaces (int) number of spaces on the text to link
	 * @public
	 * @since 1.5
	 * @see AddLink(), Annotation(), Cell(), Write(), Image()
	 */
	public function Link($x, $y, $w, $h, $link, $spaces=0) {
		$this->Annotation($x, $y, $w, $h, $link, array('Subtype'=>'Link'), $spaces);
	}

	/**
	 * Puts a markup annotation on a rectangular area of the page.
	 * !!!!THE ANNOTATION SUPPORT IS NOT YET FULLY IMPLEMENTED !!!!
	 * @param $x (float) Abscissa of the upper-left corner of the rectangle
	 * @param $y (float) Ordinate of the upper-left corner of the rectangle
	 * @param $w (float) Width of the rectangle
	 * @param $h (float) Height of the rectangle
	 * @param $text (string) annotation text or alternate content
	 * @param $opt (array) array of options (see section 8.4 of PDF reference 1.7).
	 * @param $spaces (int) number of spaces on the text to link
	 * @public
	 * @since 4.0.018 (2008-08-06)
	 */
	public function Annotation($x, $y, $w, $h, $text, $opt=array('Subtype'=>'Text'), $spaces=0) {
		if ($this->inxobj) {
			// store parameters for later use on template
			$this->xobjects[$this->xobjid]['annotations'][] = array('x' => $x, 'y' => $y, 'w' => $w, 'h' => $h, 'text' => $text, 'opt' => $opt, 'spaces' => $spaces);
			return;
		}
		if ($x === '') {
			$x = $this->x;
		}
		if ($y === '') {
			$y = $this->y;
		}
		// check page for no-write regions and adapt page margins if necessary
		list($x, $y) = $this->checkPageRegions($h, $x, $y);
		// recalculate coordinates to account for graphic transformations
		if (isset($this->transfmatrix) AND !empty($this->transfmatrix)) {
			for ($i=$this->transfmatrix_key; $i > 0; --$i) {
				$maxid = count($this->transfmatrix[$i]) - 1;
				for ($j=$maxid; $j >= 0; --$j) {
					$ctm = $this->transfmatrix[$i][$j];
					if (isset($ctm['a'])) {
						$x = $x * $this->k;
						$y = ($this->h - $y) * $this->k;
						$w = $w * $this->k;
						$h = $h * $this->k;
						// top left
						$xt = $x;
						$yt = $y;
						$x1 = ($ctm['a'] * $xt) + ($ctm['c'] * $yt) + $ctm['e'];
						$y1 = ($ctm['b'] * $xt) + ($ctm['d'] * $yt) + $ctm['f'];
						// top right
						$xt = $x + $w;
						$yt = $y;
						$x2 = ($ctm['a'] * $xt) + ($ctm['c'] * $yt) + $ctm['e'];
						$y2 = ($ctm['b'] * $xt) + ($ctm['d'] * $yt) + $ctm['f'];
						// bottom left
						$xt = $x;
						$yt = $y - $h;
						$x3 = ($ctm['a'] * $xt) + ($ctm['c'] * $yt) + $ctm['e'];
						$y3 = ($ctm['b'] * $xt) + ($ctm['d'] * $yt) + $ctm['f'];
						// bottom right
						$xt = $x + $w;
						$yt = $y - $h;
						$x4 = ($ctm['a'] * $xt) + ($ctm['c'] * $yt) + $ctm['e'];
						$y4 = ($ctm['b'] * $xt) + ($ctm['d'] * $yt) + $ctm['f'];
						// new coordinates (rectangle area)
						$x = min($x1, $x2, $x3, $x4);
						$y = max($y1, $y2, $y3, $y4);
						$w = (max($x1, $x2, $x3, $x4) - $x) / $this->k;
						$h = ($y - min($y1, $y2, $y3, $y4)) / $this->k;
						$x = $x / $this->k;
						$y = $this->h - ($y / $this->k);
					}
				}
			}
		}
		if ($this->page <= 0) {
			$page = 1;
		} else {
			$page = $this->page;
		}
		if (!isset($this->PageAnnots[$page])) {
			$this->PageAnnots[$page] = array();
		}
		$this->PageAnnots[$page][] = array('n' => ++$this->n, 'x' => $x, 'y' => $y, 'w' => $w, 'h' => $h, 'txt' => $text, 'opt' => $opt, 'numspaces' => $spaces);
		if (!$this->pdfa_mode) {
			if ((($opt['Subtype'] == 'FileAttachment') OR ($opt['Subtype'] == 'Sound')) AND (!TCPDF_STATIC::empty_string($opt['FS']))
				AND (@file_exists($opt['FS']) OR TCPDF_STATIC::isValidURL($opt['FS']))
				AND (!isset($this->embeddedfiles[basename($opt['FS'])]))) {
				$this->embeddedfiles[basename($opt['FS'])] = array('f' => ++$this->n, 'n' => ++$this->n, 'file' => $opt['FS']);
			}
		}
		// Add widgets annotation's icons
		if (isset($opt['mk']['i']) AND @file_exists($opt['mk']['i'])) {
			$this->Image($opt['mk']['i'], '', '', 10, 10, '', '', '', false, 300, '', false, false, 0, false, true);
		}
		if (isset($opt['mk']['ri']) AND @file_exists($opt['mk']['ri'])) {
			$this->Image($opt['mk']['ri'], '', '', 0, 0, '', '', '', false, 300, '', false, false, 0, false, true);
		}
		if (isset($opt['mk']['ix']) AND @file_exists($opt['mk']['ix'])) {
			$this->Image($opt['mk']['ix'], '', '', 0, 0, '', '', '', false, 300, '', false, false, 0, false, true);
		}
	}

	/**
	 * Embedd the attached files.
	 * @since 4.4.000 (2008-12-07)
	 * @protected
	 * @see Annotation()
	 */
	protected function _putEmbeddedFiles() {
		if ($this->pdfa_mode) {
			// embedded files are not allowed in PDF/A mode
			return;
		}
		reset($this->embeddedfiles);
		foreach ($this->embeddedfiles as $filename => $filedata) {
			$data = TCPDF_STATIC::fileGetContents($filedata['file']);
			if ($data !== FALSE) {
				$rawsize = strlen($data);
				if ($rawsize > 0) {
					// update name tree
					$this->efnames[$filename] = $filedata['f'].' 0 R';
					// embedded file specification object
					$out = $this->_getobj($filedata['f'])."\n";
					$out .= '<</Type /Filespec /F '.$this->_datastring($filename, $filedata['f']).' /EF <</F '.$filedata['n'].' 0 R>> >>';
					$out .= "\n".'endobj';
					$this->_out($out);
					// embedded file object
					$filter = '';
					if ($this->compress) {
						$data = gzcompress($data);
						$filter = ' /Filter /FlateDecode';
					}
					$stream = $this->_getrawstream($data, $filedata['n']);
					$out = $this->_getobj($filedata['n'])."\n";
					$out .= '<< /Type /EmbeddedFile'.$filter.' /Length '.strlen($stream).' /Params <</Size '.$rawsize.'>> >>';
					$out .= ' stream'."\n".$stream."\n".'endstream';
					$out .= "\n".'endobj';
					$this->_out($out);
				}
			}
		}
	}

	/**
	 * Returns the remaining width between the current position and margins.
	 * @return int Return the remaining width
	 * @protected
	 */
	protected function getRemainingWidth() {
		list($this->x, $this->y) = $this->checkPageRegions(0, $this->x, $this->y);
		if ($this->rtl) {
			return ($this->x - $this->lMargin);
		} else {
			return ($this->w - $this->rMargin - $this->x);
		}
	}

	/**
	 * Set the block dimensions accounting for page breaks and page/column fitting
	 * @param $w (float) width
	 * @param $h (float) height
	 * @param $x (float) X coordinate
	 * @param $y (float) Y coodiante
	 * @param $fitonpage (boolean) if true the block is resized to not exceed page dimensions.
	 * @return array($w, $h, $x, $y)
	 * @protected
	 * @since 5.5.009 (2010-07-05)
	 */
	protected function fitBlock($w, $h, $x, $y, $fitonpage=false) {
		if ($w <= 0) {
			// set maximum width
			$w = ($this->w - $this->lMargin - $this->rMargin);
			if ($w <= 0) {
				$w = 1;
			}
		}
		if ($h <= 0) {
			// set maximum height
			$h = ($this->PageBreakTrigger - $this->tMargin);
			if ($h <= 0) {
				$h = 1;
			}
		}
		// resize the block to be vertically contained on a single page or single column
		if ($fitonpage OR $this->AutoPageBreak) {
			$ratio_wh = ($w / $h);
			if ($h > ($this->PageBreakTrigger - $this->tMargin)) {
				$h = $this->PageBreakTrigger - $this->tMargin;
				$w = ($h * $ratio_wh);
			}
			// resize the block to be horizontally contained on a single page or single column
			if ($fitonpage) {
				$maxw = ($this->w - $this->lMargin - $this->rMargin);
				if ($w > $maxw) {
					$w = $maxw;
					$h = ($w / $ratio_wh);
				}
			}
		}
		// Check whether we need a new page or new column first as this does not fit
		$prev_x = $this->x;
		$prev_y = $this->y;
		if ($this->checkPageBreak($h, $y) OR ($this->y < $prev_y)) {
			$y = $this->y;
			if ($this->rtl) {
				$x += ($prev_x - $this->x);
			} else {
				$x += ($this->x - $prev_x);
			}
			$this->newline = true;
		}
		// resize the block to be contained on the remaining available page or column space
		if ($fitonpage) {
			$ratio_wh = ($w / $h);
			if (($y + $h) > $this->PageBreakTrigger) {
				$h = $this->PageBreakTrigger - $y;
				$w = ($h * $ratio_wh);
			}
			if ((!$this->rtl) AND (($x + $w) > ($this->w - $this->rMargin))) {
				$w = $this->w - $this->rMargin - $x;
				$h = ($w / $ratio_wh);
			} elseif (($this->rtl) AND (($x - $w) < ($this->lMargin))) {
				$w = $x - $this->lMargin;
				$h = ($w / $ratio_wh);
			}
		}
		return array($w, $h, $x, $y);
	}


	/**
	 * Returns the relative X value of current position.
	 * The value is relative to the left border for LTR languages and to the right border for RTL languages.
	 * @return float
	 * @public
	 * @since 1.2
	 * @see SetX(), GetY(), SetY()
	 */
	public function GetX() {
		//Get x position
		if ($this->rtl) {
			return ($this->w - $this->x);
		} else {
			return $this->x;
		}
	}

	/**
	 * Returns the absolute X value of current position.
	 * @return float
	 * @public
	 * @since 1.2
	 * @see SetX(), GetY(), SetY()
	 */
	public function GetAbsX() {
		return $this->x;
	}

	/**
	 * Returns the ordinate of the current position.
	 * @return float
	 * @public
	 * @since 1.0
	 * @see SetY(), GetX(), SetX()
	 */
	public function GetY() {
		return $this->y;
	}

	/**
	 * Defines the abscissa of the current position.
	 * If the passed value is negative, it is relative to the right of the page (or left if language is RTL).
	 * @param $x (float) The value of the abscissa in user units.
	 * @param $rtloff (boolean) if true always uses the page top-left corner as origin of axis.
	 * @public
	 * @since 1.2
	 * @see GetX(), GetY(), SetY(), SetXY()
	 */
	public function SetX($x, $rtloff=false) {
		$x = floatval($x);
		if (!$rtloff AND $this->rtl) {
			if ($x >= 0) {
				$this->x = $this->w - $x;
			} else {
				$this->x = abs($x);
			}
		} else {
			if ($x >= 0) {
				$this->x = $x;
			} else {
				$this->x = $this->w + $x;
			}
		}
		if ($this->x < 0) {
			$this->x = 0;
		}
		if ($this->x > $this->w) {
			$this->x = $this->w;
		}
	}

	/**
	 * Moves the current abscissa back to the left margin and sets the ordinate.
	 * If the passed value is negative, it is relative to the bottom of the page.
	 * @param $y (float) The value of the ordinate in user units.
	 * @param $resetx (bool) if true (default) reset the X position.
	 * @param $rtloff (boolean) if true always uses the page top-left corner as origin of axis.
	 * @public
	 * @since 1.0
	 * @see GetX(), GetY(), SetY(), SetXY()
	 */
	public function SetY($y, $resetx=true, $rtloff=false) {
		$y = floatval($y);
		if ($resetx) {
			//reset x
			if (!$rtloff AND $this->rtl) {
				$this->x = $this->w - $this->rMargin;
			} else {
				$this->x = $this->lMargin;
			}
		}
		if ($y >= 0) {
			$this->y = $y;
		} else {
			$this->y = $this->h + $y;
		}
		if ($this->y < 0) {
			$this->y = 0;
		}
		if ($this->y > $this->h) {
			$this->y = $this->h;
		}
	}

	/**
	 * Defines the abscissa and ordinate of the current position.
	 * If the passed values are negative, they are relative respectively to the right and bottom of the page.
	 * @param $x (float) The value of the abscissa.
	 * @param $y (float) The value of the ordinate.
	 * @param $rtloff (boolean) if true always uses the page top-left corner as origin of axis.
	 * @public
	 * @since 1.2
	 * @see SetX(), SetY()
	 */
	public function SetXY($x, $y, $rtloff=false) {
		$this->SetY($y, false, $rtloff);
		$this->SetX($x, $rtloff);
	}

	/**
	 * Set the absolute X coordinate of the current pointer.
	 * @param $x (float) The value of the abscissa in user units.
	 * @public
	 * @since 5.9.186 (2012-09-13)
	 * @see setAbsX(), setAbsY(), SetAbsXY()
	 */
	public function SetAbsX($x) {
		$this->x = floatval($x);
	}

	/**
	 * Set the absolute Y coordinate of the current pointer.
	 * @param $y (float) (float) The value of the ordinate in user units.
	 * @public
	 * @since 5.9.186 (2012-09-13)
	 * @see setAbsX(), setAbsY(), SetAbsXY()
	 */
	public function SetAbsY($y) {
		$this->y = floatval($y);
	}

	/**
	 * Set the absolute X and Y coordinates of the current pointer.
	 * @param $x (float) The value of the abscissa in user units.
	 * @param $y (float) (float) The value of the ordinate in user units.
	 * @public
	 * @since 5.9.186 (2012-09-13)
	 * @see setAbsX(), setAbsY(), SetAbsXY()
	 */
	public function SetAbsXY($x, $y) {
		$this->SetAbsX($x);
		$this->SetAbsY($y);
	}

	/**
	 * Send the document to a given destination: string, local file or browser.
	 * In the last case, the plug-in may be used (if present) or a download ("Save as" dialog box) may be forced.<br />
	 * The method first calls Close() if necessary to terminate the document.
	 * @param $name (string) The name of the file when saved. Note that special characters are removed and blanks characters are replaced with the underscore character.
	 * @param $dest (string) Destination where to send the document. It can take one of the following values:<ul><li>I: send the file inline to the browser (default). The plug-in is used if available. The name given by name is used when one selects the "Save as" option on the link generating the PDF.</li><li>D: send to the browser and force a file download with the name given by name.</li><li>F: save to a local server file with the name given by name.</li><li>S: return the document as a string (name is ignored).</li><li>FI: equivalent to F + I option</li><li>FD: equivalent to F + D option</li><li>E: return the document as base64 mime multi-part email attachment (RFC 2045)</li></ul>
	 * @return string
	 * @public
	 * @since 1.0
	 * @see Close()
	 */
	public function Output($name='doc.pdf', $dest='I') {
		//Output PDF to some destination
		//Finish document if necessary
		if ($this->state < 3) {
			$this->Close();
		}
		//Normalize parameters
		if (is_bool($dest)) {
			$dest = $dest ? 'D' : 'F';
		}
		$dest = strtoupper($dest);
		if ($dest[0] != 'F') {
			$name = preg_replace('/[\s]+/', '_', $name);
			$name = preg_replace('/[^a-zA-Z0-9_\.-]/', '', $name);
		}
		if ($this->sign) {
			// *** apply digital signature to the document ***
			// get the document content
			$pdfdoc = $this->getBuffer();
			// remove last newline
			$pdfdoc = substr($pdfdoc, 0, -1);
			// remove filler space
			$byterange_string_len = strlen(TCPDF_STATIC::$byterange_string);
			// define the ByteRange
			$byte_range = array();
			$byte_range[0] = 0;
			$byte_range[1] = strpos($pdfdoc, TCPDF_STATIC::$byterange_string) + $byterange_string_len + 10;
			$byte_range[2] = $byte_range[1] + $this->signature_max_length + 2;
			$byte_range[3] = strlen($pdfdoc) - $byte_range[2];
			$pdfdoc = substr($pdfdoc, 0, $byte_range[1]).substr($pdfdoc, $byte_range[2]);
			// replace the ByteRange
			$byterange = sprintf('/ByteRange[0 %u %u %u]', $byte_range[1], $byte_range[2], $byte_range[3]);
			$byterange .= str_repeat(' ', ($byterange_string_len - strlen($byterange)));
			$pdfdoc = str_replace(TCPDF_STATIC::$byterange_string, $byterange, $pdfdoc);
			// write the document to a temporary folder
			$tempdoc = TCPDF_STATIC::getObjFilename('doc', $this->file_id);
			$f = TCPDF_STATIC::fopenLocal($tempdoc, 'wb');
			if (!$f) {
				$this->Error('Unable to create temporary file: '.$tempdoc);
			}
			$pdfdoc_length = strlen($pdfdoc);
			fwrite($f, $pdfdoc, $pdfdoc_length);
			fclose($f);
			// get digital signature via openssl library
			$tempsign = TCPDF_STATIC::getObjFilename('sig', $this->file_id);
			if (empty($this->signature_data['extracerts'])) {
				openssl_pkcs7_sign($tempdoc, $tempsign, $this->signature_data['signcert'], array($this->signature_data['privkey'], $this->signature_data['password']), array(), PKCS7_BINARY | PKCS7_DETACHED);
			} else {
				openssl_pkcs7_sign($tempdoc, $tempsign, $this->signature_data['signcert'], array($this->signature_data['privkey'], $this->signature_data['password']), array(), PKCS7_BINARY | PKCS7_DETACHED, $this->signature_data['extracerts']);
			}
			// read signature
			$signature = file_get_contents($tempsign);
			// extract signature
			$signature = substr($signature, $pdfdoc_length);
			$signature = substr($signature, (strpos($signature, "%%EOF\n\n------") + 13));
			$tmparr = explode("\n\n", $signature);
			$signature = $tmparr[1];
			// decode signature
			$signature = base64_decode(trim($signature));
			// add TSA timestamp to signature
			$signature = $this->applyTSA($signature);
			// convert signature to hex
			$signature = current(unpack('H*', $signature));
			$signature = str_pad($signature, $this->signature_max_length, '0');
			// Add signature to the document
			$this->buffer = substr($pdfdoc, 0, $byte_range[1]).'<'.$signature.'>'.substr($pdfdoc, $byte_range[1]);
			$this->bufferlen = strlen($this->buffer);
		}
		switch($dest) {
			case 'I': {
				// Send PDF to the standard output
				if (ob_get_contents()) {
					$this->Error('Some data has already been output, can\'t send PDF file');
				}
				if (php_sapi_name() != 'cli') {
					// send output to a browser
					header('Content-Type: application/pdf');
					if (headers_sent()) {
						$this->Error('Some data has already been output to browser, can\'t send PDF file');
					}
					header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
					//header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
					header('Pragma: public');
					header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
					header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
					header('Content-Disposition: inline; filename="'.basename($name).'"');
					TCPDF_STATIC::sendOutputData($this->getBuffer(), $this->bufferlen);
				} else {
					echo $this->getBuffer();
				}
				break;
			}
			case 'D': {
				// download PDF as file
				if (ob_get_contents()) {
					$this->Error('Some data has already been output, can\'t send PDF file');
				}
				header('Content-Description: File Transfer');
				if (headers_sent()) {
					$this->Error('Some data has already been output to browser, can\'t send PDF file');
				}
				header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
				//header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
				header('Pragma: public');
				header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
				header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
				// force download dialog
				if (strpos(php_sapi_name(), 'cgi') === false) {
					header('Content-Type: application/force-download');
					header('Content-Type: application/octet-stream', false);
					header('Content-Type: application/download', false);
					header('Content-Type: application/pdf', false);
				} else {
					header('Content-Type: application/pdf');
				}
				// use the Content-Disposition header to supply a recommended filename
				header('Content-Disposition: attachment; filename="'.basename($name).'"');
				header('Content-Transfer-Encoding: binary');
				TCPDF_STATIC::sendOutputData($this->getBuffer(), $this->bufferlen);
				break;
			}
			case 'F':
			case 'FI':
			case 'FD': {
				// save PDF to a local file
				$f = TCPDF_STATIC::fopenLocal($name, 'wb');
				if (!$f) {
					$this->Error('Unable to create output file: '.$name);
				}
				fwrite($f, $this->getBuffer(), $this->bufferlen);
				fclose($f);
				if ($dest == 'FI') {
					// send headers to browser
					header('Content-Type: application/pdf');
					header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
					//header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
					header('Pragma: public');
					header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
					header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
					header('Content-Disposition: inline; filename="'.basename($name).'"');
					TCPDF_STATIC::sendOutputData(file_get_contents($name), filesize($name));
				} elseif ($dest == 'FD') {
					// send headers to browser
					if (ob_get_contents()) {
						$this->Error('Some data has already been output, can\'t send PDF file');
					}
					header('Content-Description: File Transfer');
					if (headers_sent()) {
						$this->Error('Some data has already been output to browser, can\'t send PDF file');
					}
					header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
					header('Pragma: public');
					header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
					header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
					// force download dialog
					if (strpos(php_sapi_name(), 'cgi') === false) {
						header('Content-Type: application/force-download');
						header('Content-Type: application/octet-stream', false);
						header('Content-Type: application/download', false);
						header('Content-Type: application/pdf', false);
					} else {
						header('Content-Type: application/pdf');
				 	}
					// use the Content-Disposition header to supply a recommended filename
					header('Content-Disposition: attachment; filename="'.basename($name).'"');
					header('Content-Transfer-Encoding: binary');
					TCPDF_STATIC::sendOutputData(file_get_contents($name), filesize($name));
				}
				break;
			}
			case 'E': {
				// return PDF as base64 mime multi-part email attachment (RFC 2045)
				$retval = 'Content-Type: application/pdf;'."\r\n";
				$retval .= ' name="'.$name.'"'."\r\n";
				$retval .= 'Content-Transfer-Encoding: base64'."\r\n";
				$retval .= 'Content-Disposition: attachment;'."\r\n";
				$retval .= ' filename="'.$name.'"'."\r\n\r\n";
				$retval .= chunk_split(base64_encode($this->getBuffer()), 76, "\r\n");
				return $retval;
			}
			case 'S': {
				// returns PDF as a string
				return $this->getBuffer();
			}
			default: {
				$this->Error('Incorrect output destination: '.$dest);
			}
		}
		return '';
	}

	/**
	 * Unset all class variables except the following critical variables.
	 * @param $destroyall (boolean) if true destroys all class variables, otherwise preserves critical variables.
	 * @param $preserve_objcopy (boolean) if true preserves the objcopy variable
	 * @public
	 * @since 4.5.016 (2009-02-24)
	 */
	public function _destroy($destroyall=false, $preserve_objcopy=false) {
		if ($destroyall AND !$preserve_objcopy) {
			// remove all temporary files
			$tmpfiles = glob(K_PATH_CACHE.'__tcpdf_'.$this->file_id.'_*');
			if (!empty($tmpfiles)) {
				array_map('unlink', $tmpfiles);
			}
		}
		$preserve = array(
			'file_id',
			'internal_encoding',
			'state',
			'bufferlen',
			'buffer',
			'cached_files',
			'sign',
			'signature_data',
			'signature_max_length',
			'byterange_string',
			'tsa_timestamp',
			'tsa_data'
		);
		foreach (array_keys(get_object_vars($this)) as $val) {
			if ($destroyall OR !in_array($val, $preserve)) {
				if ((!$preserve_objcopy OR ($val != 'objcopy')) AND ($val != 'file_id') AND isset($this->$val)) {
					unset($this->$val);
				}
			}
		}
	}

	/**
	 * Check for locale-related bug
	 * @protected
	 */
	protected function _dochecks() {
		//Check for locale-related bug
		if (1.1 == 1) {
			$this->Error('Don\'t alter the locale before including class file');
		}
		//Check for decimal separator
		if (sprintf('%.1F', 1.0) != '1.0') {
			setlocale(LC_NUMERIC, 'C');
		}
	}

	/**
	 * Return an array containing variations for the basic page number alias.
	 * @param $a (string) Base alias.
	 * @return array of page number aliases
	 * @protected
	 */
	protected function getInternalPageNumberAliases($a= '') {
		$alias = array();
		// build array of Unicode + ASCII variants (the order is important)
		$alias = array('u' => array(), 'a' => array());
		$u = '{'.$a.'}';
		$alias['u'][] = TCPDF_STATIC::_escape($u);
		if ($this->isunicode) {
			$alias['u'][] = TCPDF_STATIC::_escape(TCPDF_FONTS::UTF8ToLatin1($u, $this->isunicode, $this->CurrentFont));
			$alias['u'][] = TCPDF_STATIC::_escape(TCPDF_FONTS::utf8StrRev($u, false, $this->tmprtl, $this->isunicode, $this->CurrentFont));
			$alias['a'][] = TCPDF_STATIC::_escape(TCPDF_FONTS::UTF8ToLatin1($a, $this->isunicode, $this->CurrentFont));
			$alias['a'][] = TCPDF_STATIC::_escape(TCPDF_FONTS::utf8StrRev($a, false, $this->tmprtl, $this->isunicode, $this->CurrentFont));
		}
		$alias['a'][] = TCPDF_STATIC::_escape($a);
		return $alias;
	}

	/**
	 * Return an array containing all internal page aliases.
	 * @return array of page number aliases
	 * @protected
	 */
	protected function getAllInternalPageNumberAliases() {
		$basic_alias = array(TCPDF_STATIC::$alias_tot_pages, TCPDF_STATIC::$alias_num_page, TCPDF_STATIC::$alias_group_tot_pages, TCPDF_STATIC::$alias_group_num_page, TCPDF_STATIC::$alias_right_shift);
		$pnalias = array();
		foreach($basic_alias as $k => $a) {
			$pnalias[$k] = $this->getInternalPageNumberAliases($a);
		}
		return $pnalias;
	}

	/**
	 * Replace right shift page number aliases with spaces to correct right alignment.
	 * This works perfectly only when using monospaced fonts.
	 * @param $page (string) Page content.
	 * @param $aliases (array) Array of page aliases.
	 * @param $diff (int) initial difference to add.
	 * @return replaced page content.
	 * @protected
	 */
	protected function replaceRightShiftPageNumAliases($page, $aliases, $diff) {
		foreach ($aliases as $type => $alias) {
			foreach ($alias as $a) {
				// find position of compensation factor
				$startnum = (strpos($a, ':') + 1);
				$a = substr($a, 0, $startnum);
				if (($pos = strpos($page, $a)) !== false) {
					// end of alias
					$endnum = strpos($page, '}', $pos);
					// string to be replaced
					$aa = substr($page, $pos, ($endnum - $pos + 1));
					// get compensation factor
					$ratio = substr($page, ($pos + $startnum), ($endnum - $pos - $startnum));
					$ratio = preg_replace('/[^0-9\.]/', '', $ratio);
					$ratio = floatval($ratio);
					if ($type == 'u') {
						$chrdiff = floor(($diff + 12) * $ratio);
						$shift = str_repeat(' ', $chrdiff);
						$shift = TCPDF_FONTS::UTF8ToUTF16BE($shift, false, $this->isunicode, $this->CurrentFont);
					} else {
						$chrdiff = floor(($diff + 11) * $ratio);
						$shift = str_repeat(' ', $chrdiff);
					}
					$page = str_replace($aa, $shift, $page);
				}
			}
		}
		return $page;
	}

	/**
	 * Set page boxes to be included on page descriptions.
	 * @param $boxes (array) Array of page boxes to set on document: ('MediaBox', 'CropBox', 'BleedBox', 'TrimBox', 'ArtBox').
	 * @protected
	 */
	protected function setPageBoxTypes($boxes) {
		$this->page_boxes = array();
		foreach ($boxes as $box) {
			if (in_array($box, TCPDF_STATIC::$pageboxes)) {
				$this->page_boxes[] = $box;
			}
		}
	}


	/**
	 * Initialize a new page.
	 * @param $orientation (string) page orientation. Possible values are (case insensitive):<ul><li>P or PORTRAIT (default)</li><li>L or LANDSCAPE</li></ul>
	 * @param $format (mixed) The format used for pages. It can be either: one of the string values specified at getPageSizeFromFormat() or an array of parameters specified at setPageFormat().
	 * @protected
	 * @see getPageSizeFromFormat(), setPageFormat()
	 */
	protected function _beginpage($orientation='', $format='') {
		++$this->page;
		$this->pageobjects[$this->page] = array();
		$this->setPageBuffer($this->page, '');
		// initialize array for graphics tranformation positions inside a page buffer
		$this->transfmrk[$this->page] = array();
		$this->state = 2;
		if (TCPDF_STATIC::empty_string($orientation)) {
			if (isset($this->CurOrientation)) {
				$orientation = $this->CurOrientation;
			} elseif ($this->fwPt > $this->fhPt) {
				// landscape
				$orientation = 'L';
			} else {
				// portrait
				$orientation = 'P';
			}
		}
		if (TCPDF_STATIC::empty_string($format)) {
			$this->pagedim[$this->page] = $this->pagedim[($this->page - 1)];
			$this->setPageOrientation($orientation);
		} else {
			$this->setPageFormat($format, $orientation);
		}
		if ($this->rtl) {
			$this->x = $this->w - $this->rMargin;
		} else {
			$this->x = $this->lMargin;
		}
		$this->y = $this->tMargin;
		if (isset($this->newpagegroup[$this->page])) {
			// start a new group
			$this->currpagegroup = $this->newpagegroup[$this->page];
			$this->pagegroups[$this->currpagegroup] = 1;
		} elseif (isset($this->currpagegroup) AND ($this->currpagegroup > 0)) {
			++$this->pagegroups[$this->currpagegroup];
		}
	}

	/**
	 * Mark end of page.
	 * @protected
	 */
	protected function _endpage() {
		$this->setVisibility('all');
		$this->state = 1;
	}

	/**
	 * Begin a new object and return the object number.
	 * @return int object number
	 * @protected
	 */
	protected function _newobj() {
		$this->_out($this->_getobj());
		return $this->n;
	}

	/**
	 * Return the starting object string for the selected object ID.
	 * @param $objid (int) Object ID (leave empty to get a new ID).
	 * @return string the starting object string
	 * @protected
	 * @since 5.8.009 (2010-08-20)
	 */
	protected function _getobj($objid='') {
		if ($objid === '') {
			++$this->n;
			$objid = $this->n;
		}
		$this->offsets[$objid] = $this->bufferlen;
		$this->pageobjects[$this->page][] = $objid;
		return $objid.' 0 obj';
	}

	/**
	 * Underline text.
	 * @param $x (int) X coordinate
	 * @param $y (int) Y coordinate
	 * @param $txt (string) text to underline
	 * @protected
	 */
	protected function _dounderline($x, $y, $txt) {
		$w = $this->GetStringWidth($txt);
		return $this->_dounderlinew($x, $y, $w);
	}

	/**
	 * Underline for rectangular text area.
	 * @param $x (int) X coordinate
	 * @param $y (int) Y coordinate
	 * @param $w (int) width to underline
	 * @protected
	 * @since 4.8.008 (2009-09-29)
	 */
	protected function _dounderlinew($x, $y, $w) {
		$linew = - $this->CurrentFont['ut'] / 1000 * $this->FontSizePt;
		return sprintf('%F %F %F %F re f', $x * $this->k, ((($this->h - $y) * $this->k) + $linew), $w * $this->k, $linew);
	}

	/**
	 * Line through text.
	 * @param $x (int) X coordinate
	 * @param $y (int) Y coordinate
	 * @param $txt (string) text to linethrough
	 * @protected
	 */
	protected function _dolinethrough($x, $y, $txt) {
		$w = $this->GetStringWidth($txt);
		return $this->_dolinethroughw($x, $y, $w);
	}

	/**
	 * Line through for rectangular text area.
	 * @param $x (int) X coordinate
	 * @param $y (int) Y coordinate
	 * @param $w (int) line length (width)
	 * @protected
	 * @since 4.9.008 (2009-09-29)
	 */
	protected function _dolinethroughw($x, $y, $w) {
		$linew = - $this->CurrentFont['ut'] / 1000 * $this->FontSizePt;
		return sprintf('%F %F %F %F re f', $x * $this->k, ((($this->h - $y) * $this->k) + $linew + ($this->FontSizePt / 3)), $w * $this->k, $linew);
	}

	/**
	 * Overline text.
	 * @param $x (int) X coordinate
	 * @param $y (int) Y coordinate
	 * @param $txt (string) text to overline
	 * @protected
	 * @since 4.9.015 (2010-04-19)
	 */
	protected function _dooverline($x, $y, $txt) {
		$w = $this->GetStringWidth($txt);
		return $this->_dooverlinew($x, $y, $w);
	}

	/**
	 * Overline for rectangular text area.
	 * @param $x (int) X coordinate
	 * @param $y (int) Y coordinate
	 * @param $w (int) width to overline
	 * @protected
	 * @since 4.9.015 (2010-04-19)
	 */
	protected function _dooverlinew($x, $y, $w) {
		$linew = - $this->CurrentFont['ut'] / 1000 * $this->FontSizePt;
		return sprintf('%F %F %F %F re f', $x * $this->k, (($this->h - $y + $this->FontAscent) * $this->k) - $linew, $w * $this->k, $linew);

	}

	/**
	 * Format a data string for meta information
	 * @param $s (string) data string to escape.
	 * @param $n (int) object ID
	 * @return string escaped string.
	 * @protected
	 */
	protected function _datastring($s, $n=0) {
		if ($n == 0) {
			$n = $this->n;
		}
		$s = $this->_encrypt_data($n, $s);
		return '('. TCPDF_STATIC::_escape($s).')';
	}

	/**
	 * Set the document creation timestamp
	 * @param $time (mixed) Document creation timestamp in seconds or date-time string.
	 * @public
	 * @since 5.9.152 (2012-03-23)
	 */
	public function setDocCreationTimestamp($time) {
		if (is_string($time)) {
			$time = TCPDF_STATIC::getTimestamp($time);
		}
		$this->doc_creation_timestamp = intval($time);
	}

	/**
	 * Set the document modification timestamp
	 * @param $time (mixed) Document modification timestamp in seconds or date-time string.
	 * @public
	 * @since 5.9.152 (2012-03-23)
	 */
	public function setDocModificationTimestamp($time) {
		if (is_string($time)) {
			$time = TCPDF_STATIC::getTimestamp($time);
		}
		$this->doc_modification_timestamp = intval($time);
	}

	/**
	 * Returns document creation timestamp in seconds.
	 * @return (int) Creation timestamp in seconds.
	 * @public
	 * @since 5.9.152 (2012-03-23)
	 */
	public function getDocCreationTimestamp() {
		return $this->doc_creation_timestamp;
	}

	/**
	 * Returns document modification timestamp in seconds.
	 * @return (int) Modfication timestamp in seconds.
	 * @public
	 * @since 5.9.152 (2012-03-23)
	 */
	public function getDocModificationTimestamp() {
		return $this->doc_modification_timestamp;
	}

	/**
	 * Returns a formatted date for meta information
	 * @param $n (int) Object ID.
	 * @param $timestamp (int) Timestamp to convert.
	 * @return string escaped date string.
	 * @protected
	 * @since 4.6.028 (2009-08-25)
	 */
	protected function _datestring($n=0, $timestamp=0) {
		if ((empty($timestamp)) OR ($timestamp < 0)) {
			$timestamp = $this->doc_creation_timestamp;
		}
		return $this->_datastring('D:'.TCPDF_STATIC::getFormattedDate($timestamp), $n);
	}

	/**
	 * Format a text string for meta information
	 * @param $s (string) string to escape.
	 * @param $n (int) object ID
	 * @return string escaped string.
	 * @protected
	 */
	protected function _textstring($s, $n=0) {
		if ($this->isunicode) {
			//Convert string to UTF-16BE
			$s = TCPDF_FONTS::UTF8ToUTF16BE($s, true, $this->isunicode, $this->CurrentFont);
		}
		return $this->_datastring($s, $n);
	}

	/**
	 * get raw output stream.
	 * @param $s (string) string to output.
	 * @param $n (int) object reference for encryption mode
	 * @protected
	 * @author Nicola Asuni
	 * @since 5.5.000 (2010-06-22)
	 */
	protected function _getrawstream($s, $n=0) {
		if ($n <= 0) {
			// default to current object
			$n = $this->n;
		}
		return $this->_encrypt_data($n, $s);
	}

	/**
	 * Output a string to the document.
	 * @param $s (string) string to output.
	 * @protected
	 */
	protected function _out($s) {
		if ($this->state == 2) {
			if ($this->inxobj) {
				// we are inside an XObject template
				$this->xobjects[$this->xobjid]['outdata'] .= $s."\n";
			} elseif ((!$this->InFooter) AND isset($this->footerlen[$this->page]) AND ($this->footerlen[$this->page] > 0)) {
				// puts data before page footer
				$pagebuff = $this->getPageBuffer($this->page);
				$page = substr($pagebuff, 0, -$this->footerlen[$this->page]);
				$footer = substr($pagebuff, -$this->footerlen[$this->page]);
				$this->setPageBuffer($this->page, $page.$s."\n".$footer);
				// update footer position
				$this->footerpos[$this->page] += strlen($s."\n");
			} else {
				// set page data
				$this->setPageBuffer($this->page, $s."\n", true);
			}
		} elseif ($this->state > 0) {
			// set general data
			$this->setBuffer($s."\n");
		}
	}

	/**
	 * Set header font.
	 * @param $font (array) Array describing the basic font parameters: (family, style, size).
	 * @public
	 * @since 1.1
	 */
	public function setHeaderFont($font) {
		$this->header_font = $font;
	}

	/**
	 * Get header font.
	 * @return array() Array describing the basic font parameters: (family, style, size).
	 * @public
	 * @since 4.0.012 (2008-07-24)
	 */
	public function getHeaderFont() {
		return $this->header_font;
	}

	/**
	 * Set footer font.
	 * @param $font (array) Array describing the basic font parameters: (family, style, size).
	 * @public
	 * @since 1.1
	 */
	public function setFooterFont($font) {
		$this->footer_font = $font;
	}

	/**
	 * Get Footer font.
	 * @return array() Array describing the basic font parameters: (family, style, size).
	 * @public
	 * @since 4.0.012 (2008-07-24)
	 */
	public function getFooterFont() {
		return $this->footer_font;
	}

	/**
	 * Set language array.
	 * @param $language (array)
	 * @public
	 * @since 1.1
	 */
	public function setLanguageArray($language) {
		$this->l = $language;
		if (isset($this->l['a_meta_dir'])) {
			$this->rtl = $this->l['a_meta_dir']=='rtl' ? true : false;
		} else {
			$this->rtl = false;
		}
	}

	/**
	 * Returns the PDF data.
	 * @public
	 */
	public function getPDFData() {
		if ($this->state < 3) {
			$this->Close();
		}
		return $this->buffer;
	}

	/**
	 * Output anchor link.
	 * @param $url (string) link URL or internal link (i.e.: &lt;a href="#23,4.5"&gt;link to page 23 at 4.5 Y position&lt;/a&gt;)
	 * @param $name (string) link name
	 * @param $fill (boolean) Indicates if the cell background must be painted (true) or transparent (false).
	 * @param $firstline (boolean) if true prints only the first line and return the remaining string.
	 * @param $color (array) array of RGB text color
	 * @param $style (string) font style (U, D, B, I)
	 * @param $firstblock (boolean) if true the string is the starting of a line.
	 * @return the number of cells used or the remaining text if $firstline = true;
	 * @public
	 */
	public function addHtmlLink($url, $name, $fill=false, $firstline=false, $color='', $style=-1, $firstblock=false) {
		if (isset($url[1]) AND ($url[0] == '#') AND is_numeric($url[1])) {
			// convert url to internal link
			$lnkdata = explode(',', $url);
			if (isset($lnkdata[0]) ) {
				$page = substr($lnkdata[0], 1);
				if (isset($lnkdata[1]) AND (strlen($lnkdata[1]) > 0)) {
					$lnky = floatval($lnkdata[1]);
				} else {
					$lnky = 0;
				}
				$url = $this->AddLink();
				$this->SetLink($url, $lnky, $page);
			}
		}
		// store current settings
		$prevcolor = $this->fgcolor;
		$prevstyle = $this->FontStyle;
		if (empty($color)) {
			$this->SetTextColorArray($this->htmlLinkColorArray);
		} else {
			$this->SetTextColorArray($color);
		}
		if ($style == -1) {
			$this->SetFont('', $this->FontStyle.$this->htmlLinkFontStyle);
		} else {
			$this->SetFont('', $this->FontStyle.$style);
		}
		$ret = $this->Write($this->lasth, $name, $url, $fill, '', false, 0, $firstline, $firstblock, 0);
		// restore settings
		$this->SetFont('', $prevstyle);
		$this->SetTextColorArray($prevcolor);
		return $ret;
	}

	/**
	 * Converts pixels to User's Units.
	 * @param $px (int) pixels
	 * @return float value in user's unit
	 * @public
	 * @see setImageScale(), getImageScale()
	 */
	public function pixelsToUnits($px) {
		return ($px / ($this->imgscale * $this->k));
	}

	/**
	 * Reverse function for htmlentities.
	 * Convert entities in UTF-8.
	 * @param $text_to_convert (string) Text to convert.
	 * @return string converted text string
	 * @public
	 */
	public function unhtmlentities($text_to_convert) {
		return @html_entity_decode($text_to_convert, ENT_QUOTES, $this->encoding);
	}

	
	// START TRANSFORMATIONS SECTION -----------------------

	// END TRANSFORMATIONS SECTION -------------------------

	/**
	 * Add a Named Destination.
	 * NOTE: destination names are unique, so only last entry will be saved.
	 * @param $name (string) Destination name.
	 * @param $y (float) Y position in user units of the destiantion on the selected page (default = -1 = current position; 0 = page start;).
	 * @param $page (int|string) Target page number (leave empty for current page). If you prefix a page number with the * character, then this page will not be changed when adding/deleting/moving pages.
	 * @param $x (float) X position in user units of the destiantion on the selected page (default = -1 = current position;).
	 * @return (string) Stripped named destination identifier or false in case of error.
	 * @public
	 * @author Christian Deligant, Nicola Asuni
	 * @since 5.9.097 (2011-06-23)
	 */
	public function setDestination($name, $y=-1, $page='', $x=-1) {
		// remove unsupported characters
		$name = TCPDF_STATIC::encodeNameObject($name);
		if (TCPDF_STATIC::empty_string($name)) {
			return false;
		}
		if ($y == -1) {
			$y = $this->GetY();
		} elseif ($y < 0) {
			$y = 0;
		} elseif ($y > $this->h) {
			$y = $this->h;
		}
		if ($x == -1) {
			$x = $this->GetX();
		} elseif ($x < 0) {
			$x = 0;
		} elseif ($x > $this->w) {
			$x = $this->w;
		}
		$fixed = false;
		if (!empty($page) AND ($page[0] == '*')) {
			$page = intval(substr($page, 1));
			// this page number will not be changed when moving/add/deleting pages
			$fixed = true;
		}
		if (empty($page)) {
			$page = $this->PageNo();
			if (empty($page)) {
				return;
			}
		}
		$this->dests[$name] = array('x' => $x, 'y' => $y, 'p' => $page, 'f' => $fixed);
		return $name;
	}

	/**
	 * Return the Named Destination array.
	 * @return (array) Named Destination array.
	 * @public
	 * @author Nicola Asuni
	 * @since 5.9.097 (2011-06-23)
	 */
	public function getDestination() {
		return $this->dests;
	}

	/**
	 * Insert Named Destinations.
	 * @protected
	 * @author Johannes G\FCntert, Nicola Asuni
	 * @since 5.9.098 (2011-06-23)
	 */
	protected function _putdests() {
		if (empty($this->dests)) {
			return;
		}
		$this->n_dests = $this->_newobj();
		$out = ' <<';
		foreach($this->dests as $name => $o) {
			$out .= ' /'.$name.' '.sprintf('[%u 0 R /XYZ %F %F null]', $this->page_obj_id[($o['p'])], ($o['x'] * $this->k), ($this->pagedim[$o['p']]['h'] - ($o['y'] * $this->k)));
		}
		$out .= ' >>';
		$out .= "\n".'endobj';
		$this->_out($out);
	}

	/**
	 * Adds a bookmark - alias for Bookmark().
	 * @param $txt (string) Bookmark description.
	 * @param $level (int) Bookmark level (minimum value is 0).
	 * @param $y (float) Y position in user units of the bookmark on the selected page (default = -1 = current position; 0 = page start;).
	 * @param $page (int|string) Target page number (leave empty for current page). If you prefix a page number with the * character, then this page will not be changed when adding/deleting/moving pages.
	 * @param $style (string) Font style: B = Bold, I = Italic, BI = Bold + Italic.
	 * @param $color (array) RGB color array (values from 0 to 255).
	 * @param $x (float) X position in user units of the bookmark on the selected page (default = -1 = current position;).
	 * @param $link (mixed) URL, or numerical link ID, or named destination (# character followed by the destination name), or embedded file (* character followed by the file name).
	 * @public
	 */
	public function setBookmark($txt, $level=0, $y=-1, $page='', $style='', $color=array(0,0,0), $x=-1, $link='') {
		$this->Bookmark($txt, $level, $y, $page, $style, $color, $x, $link);
	}

	/**
	 * Adds a bookmark.
	 * @param $txt (string) Bookmark description.
	 * @param $level (int) Bookmark level (minimum value is 0).
	 * @param $y (float) Y position in user units of the bookmark on the selected page (default = -1 = current position; 0 = page start;).
	 * @param $page (int|string) Target page number (leave empty for current page). If you prefix a page number with the * character, then this page will not be changed when adding/deleting/moving pages.
	 * @param $style (string) Font style: B = Bold, I = Italic, BI = Bold + Italic.
	 * @param $color (array) RGB color array (values from 0 to 255).
	 * @param $x (float) X position in user units of the bookmark on the selected page (default = -1 = current position;).
	 * @param $link (mixed) URL, or numerical link ID, or named destination (# character followed by the destination name), or embedded file (* character followed by the file name).
	 * @public
	 * @since 2.1.002 (2008-02-12)
	 */
	public function Bookmark($txt, $level=0, $y=-1, $page='', $style='', $color=array(0,0,0), $x=-1, $link='') {
		if ($level < 0) {
			$level = 0;
		}
		if (isset($this->outlines[0])) {
			$lastoutline = end($this->outlines);
			$maxlevel = $lastoutline['l'] + 1;
		} else {
			$maxlevel = 0;
		}
		if ($level > $maxlevel) {
			$level = $maxlevel;
		}
		if ($y == -1) {
			$y = $this->GetY();
		} elseif ($y < 0) {
			$y = 0;
		} elseif ($y > $this->h) {
			$y = $this->h;
		}
		if ($x == -1) {
			$x = $this->GetX();
		} elseif ($x < 0) {
			$x = 0;
		} elseif ($x > $this->w) {
			$x = $this->w;
		}
		$fixed = false;
		if (!empty($page) AND ($page[0] == '*')) {
			$page = intval(substr($page, 1));
			// this page number will not be changed when moving/add/deleting pages
			$fixed = true;
		}
		if (empty($page)) {
			$page = $this->PageNo();
			if (empty($page)) {
				return;
			}
		}
		$this->outlines[] = array('t' => $txt, 'l' => $level, 'x' => $x, 'y' => $y, 'p' => $page, 'f' => $fixed, 's' => strtoupper($style), 'c' => $color, 'u' => $link);
	}

	/**
	 * Sort bookmarks for page and key.
	 * @protected
	 * @since 5.9.119 (2011-09-19)
	 */
	protected function sortBookmarks() {
		// get sorting columns
		$outline_p = array();
		$outline_y = array();
		foreach ($this->outlines as $key => $row) {
			$outline_p[$key] = $row['p'];
			$outline_k[$key] = $key;
		}
		// sort outlines by page and original position
		array_multisort($outline_p, SORT_NUMERIC, SORT_ASC, $outline_k, SORT_NUMERIC, SORT_ASC, $this->outlines);
	}

	/**
	 * Create a bookmark PDF string.
	 * @protected
	 * @author Olivier Plathey, Nicola Asuni
	 * @since 2.1.002 (2008-02-12)
	 */
	protected function _putbookmarks() {
		$nb = count($this->outlines);
		if ($nb == 0) {
			return;
		}
		// sort bookmarks
		$this->sortBookmarks();
		$lru = array();
		$level = 0;
		foreach ($this->outlines as $i => $o) {
			if ($o['l'] > 0) {
				$parent = $lru[($o['l'] - 1)];
				//Set parent and last pointers
				$this->outlines[$i]['parent'] = $parent;
				$this->outlines[$parent]['last'] = $i;
				if ($o['l'] > $level) {
					//Level increasing: set first pointer
					$this->outlines[$parent]['first'] = $i;
				}
			} else {
				$this->outlines[$i]['parent'] = $nb;
			}
			if (($o['l'] <= $level) AND ($i > 0)) {
				//Set prev and next pointers
				$prev = $lru[$o['l']];
				$this->outlines[$prev]['next'] = $i;
				$this->outlines[$i]['prev'] = $prev;
			}
			$lru[$o['l']] = $i;
			$level = $o['l'];
		}
		//Outline items
		$n = $this->n + 1;
		$nltags = '/<br[\s]?\/>|<\/(blockquote|dd|dl|div|dt|h1|h2|h3|h4|h5|h6|hr|li|ol|p|pre|ul|tcpdf|table|tr|td)>/si';
		foreach ($this->outlines as $i => $o) {
			$oid = $this->_newobj();
			// covert HTML title to string
			$title = preg_replace($nltags, "\n", $o['t']);
			$title = preg_replace("/[\r]+/si", '', $title);
			$title = preg_replace("/[\n]+/si", "\n", $title);
			$title = strip_tags($title);
			$title = $this->stringTrim($title);
			$out = '<</Title '.$this->_textstring($title, $oid);
			$out .= ' /Parent '.($n + $o['parent']).' 0 R';
			if (isset($o['prev'])) {
				$out .= ' /Prev '.($n + $o['prev']).' 0 R';
			}
			if (isset($o['next'])) {
				$out .= ' /Next '.($n + $o['next']).' 0 R';
			}
			if (isset($o['first'])) {
				$out .= ' /First '.($n + $o['first']).' 0 R';
			}
			if (isset($o['last'])) {
				$out .= ' /Last '.($n + $o['last']).' 0 R';
			}
			if (isset($o['u']) AND !empty($o['u'])) {
				// link
				if (is_string($o['u'])) {
					if ($o['u'][0] == '#') {
						// internal destination
						$out .= ' /Dest /'.TCPDF_STATIC::encodeNameObject(substr($o['u'], 1));
					} elseif ($o['u'][0] == '%') {
						// embedded PDF file
						$filename = basename(substr($o['u'], 1));
						$out .= ' /A <</S /GoToE /D [0 /Fit] /NewWindow true /T << /R /C /P '.($o['p'] - 1).' /A '.$this->embeddedfiles[$filename]['a'].' >> >>';
					} elseif ($o['u'][0] == '*') {
						// embedded generic file
						$filename = basename(substr($o['u'], 1));
						$jsa = 'var D=event.target.doc;var MyData=D.dataObjects;for (var i in MyData) if (MyData[i].path=="'.$filename.'") D.exportDataObject( { cName : MyData[i].name, nLaunch : 2});';
						$out .= ' /A <</S /JavaScript /JS '.$this->_textstring($jsa, $oid).'>>';
					} else {
						// external URI link
						$out .= ' /A <</S /URI /URI '.$this->_datastring($this->unhtmlentities($o['u']), $oid).'>>';
					}
				} elseif (isset($this->links[$o['u']])) {
					// internal link ID
					$l = $this->links[$o['u']];
					if (isset($this->page_obj_id[($l['p'])])) {
						$out .= sprintf(' /Dest [%u 0 R /XYZ 0 %F null]', $this->page_obj_id[($l['p'])], ($this->pagedim[$l['p']]['h'] - ($l['y'] * $this->k)));
					}
				}
			} elseif (isset($this->page_obj_id[($o['p'])])) {
				// link to a page
				$out .= ' '.sprintf('/Dest [%u 0 R /XYZ %F %F null]', $this->page_obj_id[($o['p'])], ($o['x'] * $this->k), ($this->pagedim[$o['p']]['h'] - ($o['y'] * $this->k)));
			}
			// set font style
			$style = 0;
			if (!empty($o['s'])) {
				// bold
				if (strpos($o['s'], 'B') !== false) {
					$style |= 2;
				}
				// oblique
				if (strpos($o['s'], 'I') !== false) {
					$style |= 1;
				}
			}
			$out .= sprintf(' /F %d', $style);
			// set bookmark color
			if (isset($o['c']) AND is_array($o['c']) AND (count($o['c']) == 3)) {
				$color = array_values($o['c']);
				$out .= sprintf(' /C [%F %F %F]', ($color[0] / 255), ($color[1] / 255), ($color[2] / 255));
			} else {
				// black
				$out .= ' /C [0.0 0.0 0.0]';
			}
			$out .= ' /Count 0'; // normally closed item
			$out .= ' >>';
			$out .= "\n".'endobj';
			$this->_out($out);
		}
		//Outline root
		$this->OutlineRoot = $this->_newobj();
		$this->_out('<< /Type /Outlines /First '.$n.' 0 R /Last '.($n + $lru[0]).' 0 R >>'."\n".'endobj');
	}

	/**
	 * Create a new page group.
	 * NOTE: call this function before calling AddPage()
	 * @param $page (int) starting group page (leave empty for next page).
	 * @public
	 * @since 3.0.000 (2008-03-27)
	 */
	public function startPageGroup($page='') {
		if (empty($page)) {
			$page = $this->page + 1;
		}
		$this->newpagegroup[$page] = sizeof($this->newpagegroup) + 1;
	}

	/**
	 * Set the starting page number.
	 * @param $num (int) Starting page number.
	 * @since 5.9.093 (2011-06-16)
	 * @public
	 */
	public function setStartingPageNumber($num=1) {
		$this->starting_page_number = max(0, intval($num));
	}

	/**
	 * Returns the string alias used right align page numbers.
	 * If the current font is unicode type, the returned string wil contain an additional open curly brace.
	 * @return string
	 * @since 5.9.099 (2011-06-27)
	 * @public
	 */
	public function getAliasRightShift() {
		// calculate aproximatively the ratio between widths of aliases and replacements.
		$ref = '{'.TCPDF_STATIC::$alias_right_shift.'}{'.TCPDF_STATIC::$alias_tot_pages.'}{'.TCPDF_STATIC::$alias_num_page.'}';
		$rep = str_repeat(' ', $this->GetNumChars($ref));
		$wrep = $this->GetStringWidth($rep);
		if ($wrep > 0) {
			$wdiff = max(1, ($this->GetStringWidth($ref) / $wrep));
		} else {
			$wdiff = 1;
		}
		$sdiff = sprintf('%F', $wdiff);
		$alias = TCPDF_STATIC::$alias_right_shift.$sdiff.'}';
		if ($this->isUnicodeFont()) {
			$alias = '{'.$alias;
		}
		return $alias;
	}

	/**
	 * Returns the string alias used for the total number of pages.
	 * If the current font is unicode type, the returned string is surrounded by additional curly braces.
	 * This alias will be replaced by the total number of pages in the document.
	 * @return string
	 * @since 4.0.018 (2008-08-08)
	 * @public
	 */
	public function getAliasNbPages() {
		if ($this->isUnicodeFont()) {
			return '{'.TCPDF_STATIC::$alias_tot_pages.'}';
		}
		return TCPDF_STATIC::$alias_tot_pages;
	}

	/**
	 * Returns the string alias used for the page number.
	 * If the current font is unicode type, the returned string is surrounded by additional curly braces.
	 * This alias will be replaced by the page number.
	 * @return string
	 * @since 4.5.000 (2009-01-02)
	 * @public
	 */
	public function getAliasNumPage() {
		if ($this->isUnicodeFont()) {
			return '{'.TCPDF_STATIC::$alias_num_page.'}';
		}
		return TCPDF_STATIC::$alias_num_page;
	}

	/**
	 * Return the alias for the total number of pages in the current page group.
	 * If the current font is unicode type, the returned string is surrounded by additional curly braces.
	 * This alias will be replaced by the total number of pages in this group.
	 * @return alias of the current page group
	 * @public
	 * @since 3.0.000 (2008-03-27)
	 */
	public function getPageGroupAlias() {
		if ($this->isUnicodeFont()) {
			return '{'.TCPDF_STATIC::$alias_group_tot_pages.'}';
		}
		return TCPDF_STATIC::$alias_group_tot_pages;
	}

	/**
	 * Return the alias for the page number on the current page group.
	 * If the current font is unicode type, the returned string is surrounded by additional curly braces.
	 * This alias will be replaced by the page number (relative to the belonging group).
	 * @return alias of the current page group
	 * @public
	 * @since 4.5.000 (2009-01-02)
	 */
	public function getPageNumGroupAlias() {
		if ($this->isUnicodeFont()) {
			return '{'.TCPDF_STATIC::$alias_group_num_page.'}';
		}
		return TCPDF_STATIC::$alias_group_num_page;
	}

	/**
	 * Return the current page in the group.
	 * @return current page in the group
	 * @public
	 * @since 3.0.000 (2008-03-27)
	 */
	public function getGroupPageNo() {
		return $this->pagegroups[$this->currpagegroup];
	}

	/**
	 * Returns the current group page number formatted as a string.
	 * @public
	 * @since 4.3.003 (2008-11-18)
	 * @see PaneNo(), formatPageNumber()
	 */
	public function getGroupPageNoFormatted() {
		return TCPDF_STATIC::formatPageNumber($this->getGroupPageNo());
	}

	/**
	 * Returns the current page number formatted as a string.
	 * @public
	 * @since 4.2.005 (2008-11-06)
	 * @see PaneNo(), formatPageNumber()
	 */
	public function PageNoFormatted() {
		return TCPDF_STATIC::formatPageNumber($this->PageNo());
	}

	/**
	 * Put pdf layers.
	 * @protected
	 * @since 3.0.000 (2008-03-27)
	 */
	protected function _putocg() {
		if (empty($this->pdflayers)) {
			return;
		}
		foreach ($this->pdflayers as $key => $layer) {
			 $this->pdflayers[$key]['objid'] = $this->_newobj();
			 $out = '<< /Type /OCG';
			 $out .= ' /Name '.$this->_textstring($layer['name'], $this->pdflayers[$key]['objid']);
			 $out .= ' /Usage <<';
			 if (isset($layer['print']) AND ($layer['print'] !== NULL)) {
				$out .= ' /Print <</PrintState /'.($layer['print']?'ON':'OFF').'>>';
			 }
			 $out .= ' /View <</ViewState /'.($layer['view']?'ON':'OFF').'>>';
			 $out .= ' >> >>';
			 $out .= "\n".'endobj';
			 $this->_out($out);
		}
	}

	/**
	 * Start a new pdf layer.
	 * @param $name (string) Layer name (only a-z letters and numbers). Leave empty for automatic name.
	 * @param $print (boolean|null) Set to TRUE to print this layer, FALSE to not print and NULL to not set this option
	 * @param $view (boolean) Set to true to view this layer.
	 * @param $lock (boolean) If true lock the layer
	 * @public
	 * @since 5.9.102 (2011-07-13)
	 */
	public function startLayer($name='', $print=true, $view=true, $lock=true) {
		if ($this->state != 2) {
			return;
		}
		$layer = sprintf('LYR%03d', (count($this->pdflayers) + 1));
		if (empty($name)) {
			$name = $layer;
		} else {
			$name = preg_replace('/[^a-zA-Z0-9_\-]/', '', $name);
		}
		$this->pdflayers[] = array('layer' => $layer, 'name' => $name, 'print' => $print, 'view' => $view, 'lock' => $lock);
		$this->openMarkedContent = true;
		$this->_out('/OC /'.$layer.' BDC');
	}

	/**
	 * End the current PDF layer.
	 * @public
	 * @since 5.9.102 (2011-07-13)
	 */
	public function endLayer() {
		if ($this->state != 2) {
			return;
		}
		if ($this->openMarkedContent) {
			// close existing open marked-content layer
			$this->_out('EMC');
			$this->openMarkedContent = false;
		}
	}

	/**
	 * Set the visibility of the successive elements.
	 * This can be useful, for instance, to put a background
	 * image or color that will show on screen but won't print.
	 * @param $v (string) visibility mode. Legal values are: all, print, screen or view.
	 * @public
	 * @since 3.0.000 (2008-03-27)
	 */
	public function setVisibility($v) {
		if ($this->state != 2) {
			return;
		}
		$this->endLayer();
		switch($v) {
			case 'print': {
				$this->startLayer('Print', true, false);
				break;
			}
			case 'view':
			case 'screen': {
				$this->startLayer('View', false, true);
				break;
			}
			case 'all': {
				$this->_out('');
				break;
			}
			default: {
				$this->Error('Incorrect visibility: '.$v);
				break;
			}
		}
	}

	/**
	 * Add transparency parameters to the current extgstate
	 * @param $parms (array) parameters
	 * @return the number of extgstates
	 * @protected
	 * @since 3.0.000 (2008-03-27)
	 */
	protected function addExtGState($parms) {
		if ($this->pdfa_mode) {
			// transparencies are not allowed in PDF/A mode
			return;
		}
		// check if this ExtGState already exist
		foreach ($this->extgstates as $i => $ext) {
			if ($ext['parms'] == $parms) {
				if ($this->inxobj) {
					// we are inside an XObject template
					$this->xobjects[$this->xobjid]['extgstates'][$i] = $ext;
				}
				// return reference to existing ExtGState
				return $i;
			}
		}
		$n = (count($this->extgstates) + 1);
		$this->extgstates[$n] = array('parms' => $parms);
		if ($this->inxobj) {
			// we are inside an XObject template
			$this->xobjects[$this->xobjid]['extgstates'][$n] = $this->extgstates[$n];
		}
		return $n;
	}

	/**
	 * Add an extgstate
	 * @param $gs (array) extgstate
	 * @protected
	 * @since 3.0.000 (2008-03-27)
	 */
	protected function setExtGState($gs) {
		if ($this->pdfa_mode OR ($this->state != 2)) {
			// transparency is not allowed in PDF/A mode
			return;
		}
		$this->_out(sprintf('/GS%d gs', $gs));
	}

	/**
	 * Put extgstates for object transparency
	 * @protected
	 * @since 3.0.000 (2008-03-27)
	 */
	protected function _putextgstates() {
		foreach ($this->extgstates as $i => $ext) {
			$this->extgstates[$i]['n'] = $this->_newobj();
			$out = '<< /Type /ExtGState';
			foreach ($ext['parms'] as $k => $v) {
				if (is_float($v)) {
					$v = sprintf('%F', $v);
				} elseif ($v === true) {
					$v = 'true';
				} elseif ($v === false) {
					$v = 'false';
				}
				$out .= ' /'.$k.' '.$v;
			}
			$out .= ' >>';
			$out .= "\n".'endobj';
			$this->_out($out);
		}
	}

	/**
	 * Set overprint mode for stroking (OP) and non-stroking (op) painting operations.
	 * (Check the "Entries in a Graphics State Parameter Dictionary" on PDF 32000-1:2008).
	 * @param $stroking (boolean) If true apply overprint for stroking operations.
	 * @param $nonstroking (boolean) If true apply overprint for painting operations other than stroking.
	 * @param $mode (integer) Overprint mode: (0 = each source colour component value replaces the value previously painted for the corresponding device colorant; 1 = a tint value of 0.0 for a source colour component shall leave the corresponding component of the previously painted colour unchanged).
	 * @public
	 * @since 5.9.152 (2012-03-23)
	 */
	public function setOverprint($stroking=true, $nonstroking='', $mode=0) {
		if ($this->state != 2) {
			return;
		}
		$stroking = $stroking ? true : false;
		if (TCPDF_STATIC::empty_string($nonstroking)) {
			// default value if not set
			$nonstroking = $stroking;
		} else {
			$nonstroking = $nonstroking ? true : false;
		}
		if (($mode != 0) AND ($mode != 1)) {
			$mode = 0;
		}
		$this->overprint = array('OP' => $stroking, 'op' => $nonstroking, 'OPM' => $mode);
		$gs = $this->addExtGState($this->overprint);
		$this->setExtGState($gs);
	}

	/**
	 * Get the overprint mode array (OP, op, OPM).
	 * (Check the "Entries in a Graphics State Parameter Dictionary" on PDF 32000-1:2008).
	 * @return array.
	 * @public
	 * @since 5.9.152 (2012-03-23)
	 */
	public function getOverprint() {
		return $this->overprint;
	}

	/**
	 * Set alpha for stroking (CA) and non-stroking (ca) operations.
	 * @param $stroking (float) Alpha value for stroking operations: real value from 0 (transparent) to 1 (opaque).
	 * @param $bm (string) blend mode, one of the following: Normal, Multiply, Screen, Overlay, Darken, Lighten, ColorDodge, ColorBurn, HardLight, SoftLight, Difference, Exclusion, Hue, Saturation, Color, Luminosity
	 * @param $nonstroking (float) Alpha value for non-stroking operations: real value from 0 (transparent) to 1 (opaque).
	 * @param $ais (boolean)
	 * @public
	 * @since 3.0.000 (2008-03-27)
	 */
	public function setAlpha($stroking=1, $bm='Normal', $nonstroking='', $ais=false) {
		if ($this->pdfa_mode) {
			// transparency is not allowed in PDF/A mode
			return;
		}
		$stroking = floatval($stroking);
		if (TCPDF_STATIC::empty_string($nonstroking)) {
			// default value if not set
			$nonstroking = $stroking;
		} else {
			$nonstroking = floatval($nonstroking);
		}
		if ($bm[0] == '/') {
			// remove trailing slash
			$bm = substr($bm, 1);
		}
		if (!in_array($bm, array('Normal', 'Multiply', 'Screen', 'Overlay', 'Darken', 'Lighten', 'ColorDodge', 'ColorBurn', 'HardLight', 'SoftLight', 'Difference', 'Exclusion', 'Hue', 'Saturation', 'Color', 'Luminosity'))) {
			$bm = 'Normal';
		}
		$ais = $ais ? true : false;
		$this->alpha = array('CA' => $stroking, 'ca' => $nonstroking, 'BM' => '/'.$bm, 'AIS' => $ais);
		$gs = $this->addExtGState($this->alpha);
		$this->setExtGState($gs);
	}

	/**
	 * Get the alpha mode array (CA, ca, BM, AIS).
	 * (Check the "Entries in a Graphics State Parameter Dictionary" on PDF 32000-1:2008).
	 * @return array.
	 * @public
	 * @since 5.9.152 (2012-03-23)
	 */
	public function getAlpha() {
		return $this->alpha;
	}

	/**
	 * Set the default JPEG compression quality (1-100)
	 * @param $quality (int) JPEG quality, integer between 1 and 100
	 * @public
	 * @since 3.0.000 (2008-03-27)
	 */
	public function setJPEGQuality($quality) {
		if (($quality < 1) OR ($quality > 100)) {
			$quality = 75;
		}
		$this->jpeg_quality = intval($quality);
	}

	/**
	 * Set the default number of columns in a row for HTML tables.
	 * @param $cols (int) number of columns
	 * @public
	 * @since 3.0.014 (2008-06-04)
	 */
	public function setDefaultTableColumns($cols=4) {
		$this->default_table_columns = intval($cols);
	}

	/**
	 * Set the height of the cell (line height) respect the font height.
	 * @param $h (int) cell proportion respect font height (typical value = 1.25).
	 * @public
	 * @since 3.0.014 (2008-06-04)
	 */
	public function setCellHeightRatio($h) {
		$this->cell_height_ratio = $h;
	}

	/**
	 * return the height of cell repect font height.
	 * @public
	 * @since 4.0.012 (2008-07-24)
	 */
	public function getCellHeightRatio() {
		return $this->cell_height_ratio;
	}

	/**
	 * Set the PDF version (check PDF reference for valid values).
	 * @param $version (string) PDF document version.
	 * @public
	 * @since 3.1.000 (2008-06-09)
	 */
	public function setPDFVersion($version='1.7') {
		if ($this->pdfa_mode) {
			// PDF/A mode
			$this->PDFVersion = '1.4';
		} else {
			$this->PDFVersion = $version;
		}
	}

	/**
	 * Set the viewer preferences dictionary controlling the way the document is to be presented on the screen or in print.
	 * (see Section 8.1 of PDF reference, "Viewer Preferences").
	 * <ul><li>HideToolbar boolean (Optional) A flag specifying whether to hide the viewer application's tool bars when the document is active. Default value: false.</li><li>HideMenubar boolean (Optional) A flag specifying whether to hide the viewer application's menu bar when the document is active. Default value: false.</li><li>HideWindowUI boolean (Optional) A flag specifying whether to hide user interface elements in the document's window (such as scroll bars and navigation controls), leaving only the document's contents displayed. Default value: false.</li><li>FitWindow boolean (Optional) A flag specifying whether to resize the document's window to fit the size of the first displayed page. Default value: false.</li><li>CenterWindow boolean (Optional) A flag specifying whether to position the document's window in the center of the screen. Default value: false.</li><li>DisplayDocTitle boolean (Optional; PDF 1.4) A flag specifying whether the window's title bar should display the document title taken from the Title entry of the document information dictionary (see Section 10.2.1, "Document Information Dictionary"). If false, the title bar should instead display the name of the PDF file containing the document. Default value: false.</li><li>NonFullScreenPageMode name (Optional) The document's page mode, specifying how to display the document on exiting full-screen mode:<ul><li>UseNone Neither document outline nor thumbnail images visible</li><li>UseOutlines Document outline visible</li><li>UseThumbs Thumbnail images visible</li><li>UseOC Optional content group panel visible</li></ul>This entry is meaningful only if the value of the PageMode entry in the catalog dictionary (see Section 3.6.1, "Document Catalog") is FullScreen; it is ignored otherwise. Default value: UseNone.</li><li>ViewArea name (Optional; PDF 1.4) The name of the page boundary representing the area of a page to be displayed when viewing the document on the screen. Valid values are (see Section 10.10.1, "Page Boundaries").:<ul><li>MediaBox</li><li>CropBox (default)</li><li>BleedBox</li><li>TrimBox</li><li>ArtBox</li></ul></li><li>ViewClip name (Optional; PDF 1.4) The name of the page boundary to which the contents of a page are to be clipped when viewing the document on the screen. Valid values are (see Section 10.10.1, "Page Boundaries").:<ul><li>MediaBox</li><li>CropBox (default)</li><li>BleedBox</li><li>TrimBox</li><li>ArtBox</li></ul></li><li>PrintArea name (Optional; PDF 1.4) The name of the page boundary representing the area of a page to be rendered when printing the document. Valid values are (see Section 10.10.1, "Page Boundaries").:<ul><li>MediaBox</li><li>CropBox (default)</li><li>BleedBox</li><li>TrimBox</li><li>ArtBox</li></ul></li><li>PrintClip name (Optional; PDF 1.4) The name of the page boundary to which the contents of a page are to be clipped when printing the document. Valid values are (see Section 10.10.1, "Page Boundaries").:<ul><li>MediaBox</li><li>CropBox (default)</li><li>BleedBox</li><li>TrimBox</li><li>ArtBox</li></ul></li><li>PrintScaling name (Optional; PDF 1.6) The page scaling option to be selected when a print dialog is displayed for this document. Valid values are: <ul><li>None, which indicates that the print dialog should reflect no page scaling</li><li>AppDefault (default), which indicates that applications should use the current print scaling</li></ul></li><li>Duplex name (Optional; PDF 1.7) The paper handling option to use when printing the file from the print dialog. The following values are valid:<ul><li>Simplex - Print single-sided</li><li>DuplexFlipShortEdge - Duplex and flip on the short edge of the sheet</li><li>DuplexFlipLongEdge - Duplex and flip on the long edge of the sheet</li></ul>Default value: none</li><li>PickTrayByPDFSize boolean (Optional; PDF 1.7) A flag specifying whether the PDF page size is used to select the input paper tray. This setting influences only the preset values used to populate the print dialog presented by a PDF viewer application. If PickTrayByPDFSize is true, the check box in the print dialog associated with input paper tray is checked. Note: This setting has no effect on Mac OS systems, which do not provide the ability to pick the input tray by size.</li><li>PrintPageRange array (Optional; PDF 1.7) The page numbers used to initialize the print dialog box when the file is printed. The first page of the PDF file is denoted by 1. Each pair consists of the first and last pages in the sub-range. An odd number of integers causes this entry to be ignored. Negative numbers cause the entire array to be ignored. Default value: as defined by PDF viewer application</li><li>NumCopies integer (Optional; PDF 1.7) The number of copies to be printed when the print dialog is opened for this file. Supported values are the integers 2 through 5. Values outside this range are ignored. Default value: as defined by PDF viewer application, but typically 1</li></ul>
	 * @param $preferences (array) array of options.
	 * @author Nicola Asuni
	 * @public
	 * @since 3.1.000 (2008-06-09)
	 */
	public function setViewerPreferences($preferences) {
		$this->viewer_preferences = $preferences;
	}

	/**
	 * Paints color transition registration bars
	 * @param $x (float) abscissa of the top left corner of the rectangle.
	 * @param $y (float) ordinate of the top left corner of the rectangle.
	 * @param $w (float) width of the rectangle.
	 * @param $h (float) height of the rectangle.
	 * @param $transition (boolean) if true prints tcolor transitions to white.
	 * @param $vertical (boolean) if true prints bar vertically.
	 * @param $colors (string) colors to print separated by comma. Valid values are: A,W,R,G,B,C,M,Y,K,RGB,CMYK,ALL,ALLSPOT,<SPOT_COLOR_NAME>. Where: A = grayscale black, W = grayscale white, R = RGB red, G RGB green, B RGB blue, C = CMYK cyan, M = CMYK magenta, Y = CMYK yellow, K = CMYK key/black, RGB = RGB registration color, CMYK = CMYK registration color, ALL = Spot registration color, ALLSPOT = print all defined spot colors, <SPOT_COLOR_NAME> = name of the spot color to print.
	 * @author Nicola Asuni
	 * @since 4.9.000 (2010-03-26)
	 * @public
	 */
	public function colorRegistrationBar($x, $y, $w, $h, $transition=true, $vertical=false, $colors='A,R,G,B,C,M,Y,K') {
		if (strpos($colors, 'ALLSPOT') !== false) {
			// expand spot colors
			$spot_colors = '';
			foreach ($this->spot_colors as $spot_color_name => $v) {
				$spot_colors .= ','.$spot_color_name;
			}
			if (!empty($spot_colors)) {
				$spot_colors = substr($spot_colors, 1);
				$colors = str_replace('ALLSPOT', $spot_colors, $colors);
			} else {
				$colors = str_replace('ALLSPOT', 'NONE', $colors);
			}
		}
		$bars = explode(',', $colors);
		$numbars = count($bars); // number of bars to print
		if ($numbars <= 0) {
			return;
		}
		// set bar measures
		if ($vertical) {
			$coords = array(0, 0, 0, 1);
			$wb = $w / $numbars; // bar width
			$hb = $h; // bar height
			$xd = $wb; // delta x
			$yd = 0; // delta y
		} else {
			$coords = array(1, 0, 0, 0);
			$wb = $w; // bar width
			$hb = $h / $numbars; // bar height
			$xd = 0; // delta x
			$yd = $hb; // delta y
		}
		$xb = $x;
		$yb = $y;
		foreach ($bars as $col) {
			switch ($col) {
				// set transition colors
				case 'A': { // BLACK (GRAYSCALE)
					$col_a = array(255);
					$col_b = array(0);
					break;
				}
				case 'W': { // WHITE (GRAYSCALE)
					$col_a = array(0);
					$col_b = array(255);
					break;
				}
				case 'R': { // RED (RGB)
					$col_a = array(255,255,255);
					$col_b = array(255,0,0);
					break;
				}
				case 'G': { // GREEN (RGB)
					$col_a = array(255,255,255);
					$col_b = array(0,255,0);
					break;
				}
				case 'B': { // BLUE (RGB)
					$col_a = array(255,255,255);
					$col_b = array(0,0,255);
					break;
				}
				case 'C': { // CYAN (CMYK)
					$col_a = array(0,0,0,0);
					$col_b = array(100,0,0,0);
					break;
				}
				case 'M': { // MAGENTA (CMYK)
					$col_a = array(0,0,0,0);
					$col_b = array(0,100,0,0);
					break;
				}
				case 'Y': { // YELLOW (CMYK)
					$col_a = array(0,0,0,0);
					$col_b = array(0,0,100,0);
					break;
				}
				case 'K': { // KEY - BLACK (CMYK)
					$col_a = array(0,0,0,0);
					$col_b = array(0,0,0,100);
					break;
				}
				case 'RGB': { // BLACK REGISTRATION (RGB)
					$col_a = array(255,255,255);
					$col_b = array(0,0,0);
					break;
				}
				case 'CMYK': { // BLACK REGISTRATION (CMYK)
					$col_a = array(0,0,0,0);
					$col_b = array(100,100,100,100);
					break;
				}
				case 'ALL': { // SPOT COLOR REGISTRATION
					$col_a = array(0,0,0,0,'None');
					$col_b = array(100,100,100,100,'All');
					break;
				}
				case 'NONE': { // SKIP THIS COLOR
					$col_a = array(0,0,0,0,'None');
					$col_b = array(0,0,0,0,'None');
					break;
				}
				default: { // SPECIFIC SPOT COLOR NAME
					$col_a = array(0,0,0,0,'None');
					$col_b = TCPDF_COLORS::getSpotColor($col, $this->spot_colors);
					if ($col_b === false) {
						// in case of error defaults to the registration color
						$col_b = array(100,100,100,100,'All');
					}
					break;
				}
			}
			if ($col != 'NONE') {
				if ($transition) {
					// color gradient
					$this->LinearGradient($xb, $yb, $wb, $hb, $col_a, $col_b, $coords);
				} else {
					$this->SetFillColorArray($col_b);
					// colored rectangle
					$this->Rect($xb, $yb, $wb, $hb, 'F', array());
				}
				$xb += $xd;
				$yb += $yd;
			}
		}
	}

	/**
	 * Paints crop marks.
	 * @param $x (float) abscissa of the crop mark center.
	 * @param $y (float) ordinate of the crop mark center.
	 * @param $w (float) width of the crop mark.
	 * @param $h (float) height of the crop mark.
	 * @param $type (string) type of crop mark, one symbol per type separated by comma: T = TOP, F = BOTTOM, L = LEFT, R = RIGHT, TL = A = TOP-LEFT, TR = B = TOP-RIGHT, BL = C = BOTTOM-LEFT, BR = D = BOTTOM-RIGHT.
	 * @param $color (array) crop mark color (default spot registration color).
	 * @author Nicola Asuni
	 * @since 4.9.000 (2010-03-26)
	 * @public
	 */
	public function cropMark($x, $y, $w, $h, $type='T,R,B,L', $color=array(100,100,100,100,'All')) {
		$this->SetLineStyle(array('width' => (0.5 / $this->k), 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $color));
		$type = strtoupper($type);
		$type = preg_replace('/[^A-Z\-\,]*/', '', $type);
		// split type in single components
		$type = str_replace('-', ',', $type);
		$type = str_replace('TL', 'T,L', $type);
		$type = str_replace('TR', 'T,R', $type);
		$type = str_replace('BL', 'F,L', $type);
		$type = str_replace('BR', 'F,R', $type);
		$type = str_replace('A', 'T,L', $type);
		$type = str_replace('B', 'T,R', $type);
		$type = str_replace('T,RO', 'BO', $type);
		$type = str_replace('C', 'F,L', $type);
		$type = str_replace('D', 'F,R', $type);
		$crops = explode(',', strtoupper($type));
		// remove duplicates
		$crops = array_unique($crops);
		$dw = ($w / 4); // horizontal space to leave before the intersection point
		$dh = ($h / 4); // vertical space to leave before the intersection point
		foreach ($crops as $crop) {
			switch ($crop) {
				case 'T':
				case 'TOP': {
					$x1 = $x;
					$y1 = ($y - $h);
					$x2 = $x;
					$y2 = ($y - $dh);
					break;
				}
				case 'F':
				case 'BOTTOM': {
					$x1 = $x;
					$y1 = ($y + $dh);
					$x2 = $x;
					$y2 = ($y + $h);
					break;
				}
				case 'L':
				case 'LEFT': {
					$x1 = ($x - $w);
					$y1 = $y;
					$x2 = ($x - $dw);
					$y2 = $y;
					break;
				}
				case 'R':
				case 'RIGHT': {
					$x1 = ($x + $dw);
					$y1 = $y;
					$x2 = ($x + $w);
					$y2 = $y;
					break;
				}
			}
			$this->Line($x1, $y1, $x2, $y2);
		}
	}

	/**
	 * Paints a registration mark
	 * @param $x (float) abscissa of the registration mark center.
	 * @param $y (float) ordinate of the registration mark center.
	 * @param $r (float) radius of the crop mark.
	 * @param $double (boolean) if true print two concentric crop marks.
	 * @param $cola (array) crop mark color (default spot registration color 'All').
	 * @param $colb (array) second crop mark color (default spot registration color 'None').
	 * @author Nicola Asuni
	 * @since 4.9.000 (2010-03-26)
	 * @public
	 */
	public function registrationMark($x, $y, $r, $double=false, $cola=array(100,100,100,100,'All'), $colb=array(0,0,0,0,'None')) {
		$line_style = array('width' => max((0.5 / $this->k),($r / 30)), 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $cola);
		$this->SetFillColorArray($cola);
		$this->PieSector($x, $y, $r, 90, 180, 'F');
		$this->PieSector($x, $y, $r, 270, 360, 'F');
		$this->Circle($x, $y, $r, 0, 360, 'C', $line_style, array(), 8);
		if ($double) {
			$ri = $r * 0.5;
			$this->SetFillColorArray($colb);
			$this->PieSector($x, $y, $ri, 90, 180, 'F');
			$this->PieSector($x, $y, $ri, 270, 360, 'F');
			$this->SetFillColorArray($cola);
			$this->PieSector($x, $y, $ri, 0, 90, 'F');
			$this->PieSector($x, $y, $ri, 180, 270, 'F');
			$this->Circle($x, $y, $ri, 0, 360, 'C', $line_style, array(), 8);
		}
	}

	/**
	 * Paints a CMYK registration mark
	 * @param $x (float) abscissa of the registration mark center.
	 * @param $y (float) ordinate of the registration mark center.
	 * @param $r (float) radius of the crop mark.
	 * @author Nicola Asuni
	 * @since 6.0.038 (2013-09-30)
	 * @public
	 */
	public function registrationMarkCMYK($x, $y, $r) {
		// line width
		$lw = max((0.5 / $this->k),($r / 8));
		// internal radius
		$ri = ($r * 0.6);
		// external radius
		$re = ($r * 1.3);
		// Cyan
		$this->SetFillColorArray(array(100,0,0,0));
		$this->PieSector($x, $y, $ri, 270, 360, 'F');
		// Magenta
		$this->SetFillColorArray(array(0,100,0,0));
		$this->PieSector($x, $y, $ri, 0, 90, 'F');
		// Yellow
		$this->SetFillColorArray(array(0,0,100,0));
		$this->PieSector($x, $y, $ri, 90, 180, 'F');
		// Key - black
		$this->SetFillColorArray(array(0,0,0,100));
		$this->PieSector($x, $y, $ri, 180, 270, 'F');
		// registration color
		$line_style = array('width' => $lw, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(100,100,100,100,'All'));
		$this->SetFillColorArray(array(100,100,100,100,'All'));
		// external circle
		$this->Circle($x, $y, $r, 0, 360, 'C', $line_style, array(), 8);
		// cross lines
		$this->Line($x, ($y - $re), $x, ($y - $ri));
		$this->Line($x, ($y + $ri), $x, ($y + $re));
		$this->Line(($x - $re), $y, ($x - $ri), $y);
		$this->Line(($x + $ri), $y, ($x + $re), $y);
	}

	/**
	 * Paints a linear colour gradient.
	 * @param $x (float) abscissa of the top left corner of the rectangle.
	 * @param $y (float) ordinate of the top left corner of the rectangle.
	 * @param $w (float) width of the rectangle.
	 * @param $h (float) height of the rectangle.
	 * @param $col1 (array) first color (Grayscale, RGB or CMYK components).
	 * @param $col2 (array) second color (Grayscale, RGB or CMYK components).
	 * @param $coords (array) array of the form (x1, y1, x2, y2) which defines the gradient vector (see linear_gradient_coords.jpg). The default value is from left to right (x1=0, y1=0, x2=1, y2=0).
	 * @author Andreas W\FCrmser, Nicola Asuni
	 * @since 3.1.000 (2008-06-09)
	 * @public
	 */
	public function LinearGradient($x, $y, $w, $h, $col1=array(), $col2=array(), $coords=array(0,0,1,0)) {
		$this->Clip($x, $y, $w, $h);
		$this->Gradient(2, $coords, array(array('color' => $col1, 'offset' => 0, 'exponent' => 1), array('color' => $col2, 'offset' => 1, 'exponent' => 1)), array(), false);
	}

	/**
	 * Paints a radial colour gradient.
	 * @param $x (float) abscissa of the top left corner of the rectangle.
	 * @param $y (float) ordinate of the top left corner of the rectangle.
	 * @param $w (float) width of the rectangle.
	 * @param $h (float) height of the rectangle.
	 * @param $col1 (array) first color (Grayscale, RGB or CMYK components).
	 * @param $col2 (array) second color (Grayscale, RGB or CMYK components).
	 * @param $coords (array) array of the form (fx, fy, cx, cy, r) where (fx, fy) is the starting point of the gradient with color1, (cx, cy) is the center of the circle with color2, and r is the radius of the circle (see radial_gradient_coords.jpg). (fx, fy) should be inside the circle, otherwise some areas will not be defined.
	 * @author Andreas W\FCrmser, Nicola Asuni
	 * @since 3.1.000 (2008-06-09)
	 * @public
	 */
	public function RadialGradient($x, $y, $w, $h, $col1=array(), $col2=array(), $coords=array(0.5,0.5,0.5,0.5,1)) {
		$this->Clip($x, $y, $w, $h);
		$this->Gradient(3, $coords, array(array('color' => $col1, 'offset' => 0, 'exponent' => 1), array('color' => $col2, 'offset' => 1, 'exponent' => 1)), array(), false);
	}

	/**
	 * Paints a coons patch mesh.
	 * @param $x (float) abscissa of the top left corner of the rectangle.
	 * @param $y (float) ordinate of the top left corner of the rectangle.
	 * @param $w (float) width of the rectangle.
	 * @param $h (float) height of the rectangle.
	 * @param $col1 (array) first color (lower left corner) (RGB components).
	 * @param $col2 (array) second color (lower right corner) (RGB components).
	 * @param $col3 (array) third color (upper right corner) (RGB components).
	 * @param $col4 (array) fourth color (upper left corner) (RGB components).
	 * @param $coords (array) <ul><li>for one patch mesh: array(float x1, float y1, .... float x12, float y12): 12 pairs of coordinates (normally from 0 to 1) which specify the Bezier control points that define the patch. First pair is the lower left edge point, next is its right control point (control point 2). Then the other points are defined in the order: control point 1, edge point, control point 2 going counter-clockwise around the patch. Last (x12, y12) is the first edge point's left control point (control point 1).</li><li>for two or more patch meshes: array[number of patches]: arrays with the following keys for each patch: f: where to put that patch (0 = first patch, 1, 2, 3 = right, top and left of precedent patch - I didn't figure this out completely - just try and error ;-) points: 12 pairs of coordinates of the Bezier control points as above for the first patch, 8 pairs of coordinates for the following patches, ignoring the coordinates already defined by the precedent patch (I also didn't figure out the order of these - also: try and see what's happening) colors: must be 4 colors for the first patch, 2 colors for the following patches</li></ul>
	 * @param $coords_min (array) minimum value used by the coordinates. If a coordinate's value is smaller than this it will be cut to coords_min. default: 0
	 * @param $coords_max (array) maximum value used by the coordinates. If a coordinate's value is greater than this it will be cut to coords_max. default: 1
	 * @param $antialias (boolean) A flag indicating whether to filter the shading function to prevent aliasing artifacts.
	 * @author Andreas W\FCrmser, Nicola Asuni
	 * @since 3.1.000 (2008-06-09)
	 * @public
	 */
	public function CoonsPatchMesh($x, $y, $w, $h, $col1=array(), $col2=array(), $col3=array(), $col4=array(), $coords=array(0.00,0.0,0.33,0.00,0.67,0.00,1.00,0.00,1.00,0.33,1.00,0.67,1.00,1.00,0.67,1.00,0.33,1.00,0.00,1.00,0.00,0.67,0.00,0.33), $coords_min=0, $coords_max=1, $antialias=false) {
		if ($this->pdfa_mode OR ($this->state != 2)) {
			return;
		}
		$this->Clip($x, $y, $w, $h);
		$n = count($this->gradients) + 1;
		$this->gradients[$n] = array();
		$this->gradients[$n]['type'] = 6; //coons patch mesh
		$this->gradients[$n]['coords'] = array();
		$this->gradients[$n]['antialias'] = $antialias;
		$this->gradients[$n]['colors'] = array();
		$this->gradients[$n]['transparency'] = false;
		//check the coords array if it is the simple array or the multi patch array
		if (!isset($coords[0]['f'])) {
			//simple array -> convert to multi patch array
			if (!isset($col1[1])) {
				$col1[1] = $col1[2] = $col1[0];
			}
			if (!isset($col2[1])) {
				$col2[1] = $col2[2] = $col2[0];
			}
			if (!isset($col3[1])) {
				$col3[1] = $col3[2] = $col3[0];
			}
			if (!isset($col4[1])) {
				$col4[1] = $col4[2] = $col4[0];
			}
			$patch_array[0]['f'] = 0;
			$patch_array[0]['points'] = $coords;
			$patch_array[0]['colors'][0]['r'] = $col1[0];
			$patch_array[0]['colors'][0]['g'] = $col1[1];
			$patch_array[0]['colors'][0]['b'] = $col1[2];
			$patch_array[0]['colors'][1]['r'] = $col2[0];
			$patch_array[0]['colors'][1]['g'] = $col2[1];
			$patch_array[0]['colors'][1]['b'] = $col2[2];
			$patch_array[0]['colors'][2]['r'] = $col3[0];
			$patch_array[0]['colors'][2]['g'] = $col3[1];
			$patch_array[0]['colors'][2]['b'] = $col3[2];
			$patch_array[0]['colors'][3]['r'] = $col4[0];
			$patch_array[0]['colors'][3]['g'] = $col4[1];
			$patch_array[0]['colors'][3]['b'] = $col4[2];
		} else {
			//multi patch array
			$patch_array = $coords;
		}
		$bpcd = 65535; //16 bits per coordinate
		//build the data stream
		$this->gradients[$n]['stream'] = '';
		$count_patch = count($patch_array);
		for ($i=0; $i < $count_patch; ++$i) {
			$this->gradients[$n]['stream'] .= chr($patch_array[$i]['f']); //start with the edge flag as 8 bit
			$count_points = count($patch_array[$i]['points']);
			for ($j=0; $j < $count_points; ++$j) {
				//each point as 16 bit
				$patch_array[$i]['points'][$j] = (($patch_array[$i]['points'][$j] - $coords_min) / ($coords_max - $coords_min)) * $bpcd;
				if ($patch_array[$i]['points'][$j] < 0) {
					$patch_array[$i]['points'][$j] = 0;
				}
				if ($patch_array[$i]['points'][$j] > $bpcd) {
					$patch_array[$i]['points'][$j] = $bpcd;
				}
				$this->gradients[$n]['stream'] .= chr(floor($patch_array[$i]['points'][$j] / 256));
				$this->gradients[$n]['stream'] .= chr(floor($patch_array[$i]['points'][$j] % 256));
			}
			$count_cols = count($patch_array[$i]['colors']);
			for ($j=0; $j < $count_cols; ++$j) {
				//each color component as 8 bit
				$this->gradients[$n]['stream'] .= chr($patch_array[$i]['colors'][$j]['r']);
				$this->gradients[$n]['stream'] .= chr($patch_array[$i]['colors'][$j]['g']);
				$this->gradients[$n]['stream'] .= chr($patch_array[$i]['colors'][$j]['b']);
			}
		}
		//paint the gradient
		$this->_out('/Sh'.$n.' sh');
		//restore previous Graphic State
		$this->_outRestoreGraphicsState();
		if ($this->inxobj) {
			// we are inside an XObject template
			$this->xobjects[$this->xobjid]['gradients'][$n] = $this->gradients[$n];
		}
	}

	/**
	 * Set a rectangular clipping area.
	 * @param $x (float) abscissa of the top left corner of the rectangle (or top right corner for RTL mode).
	 * @param $y (float) ordinate of the top left corner of the rectangle.
	 * @param $w (float) width of the rectangle.
	 * @param $h (float) height of the rectangle.
	 * @author Andreas W\FCrmser, Nicola Asuni
	 * @since 3.1.000 (2008-06-09)
	 * @protected
	 */
	protected function Clip($x, $y, $w, $h) {
		if ($this->state != 2) {
			 return;
		}
		if ($this->rtl) {
			$x = $this->w - $x - $w;
		}
		//save current Graphic State
		$s = 'q';
		//set clipping area
		$s .= sprintf(' %F %F %F %F re W n', $x*$this->k, ($this->h-$y)*$this->k, $w*$this->k, -$h*$this->k);
		//set up transformation matrix for gradient
		$s .= sprintf(' %F 0 0 %F %F %F cm', $w*$this->k, $h*$this->k, $x*$this->k, ($this->h-($y+$h))*$this->k);
		$this->_out($s);
	}

	/**
	 * Output gradient.
	 * @param $type (int) type of gradient (1 Function-based shading; 2 Axial shading; 3 Radial shading; 4 Free-form Gouraud-shaded triangle mesh; 5 Lattice-form Gouraud-shaded triangle mesh; 6 Coons patch mesh; 7 Tensor-product patch mesh). (Not all types are currently supported)
	 * @param $coords (array) array of coordinates.
	 * @param $stops (array) array gradient color components: color = array of GRAY, RGB or CMYK color components; offset = (0 to 1) represents a location along the gradient vector; exponent = exponent of the exponential interpolation function (default = 1).
	 * @param $background (array) An array of colour components appropriate to the colour space, specifying a single background colour value.
	 * @param $antialias (boolean) A flag indicating whether to filter the shading function to prevent aliasing artifacts.
	 * @author Nicola Asuni
	 * @since 3.1.000 (2008-06-09)
	 * @public
	 */
	public function Gradient($type, $coords, $stops, $background=array(), $antialias=false) {
		if ($this->pdfa_mode OR ($this->state != 2)) {
			return;
		}
		$n = count($this->gradients) + 1;
		$this->gradients[$n] = array();
		$this->gradients[$n]['type'] = $type;
		$this->gradients[$n]['coords'] = $coords;
		$this->gradients[$n]['antialias'] = $antialias;
		$this->gradients[$n]['colors'] = array();
		$this->gradients[$n]['transparency'] = false;
		// color space
		$numcolspace = count($stops[0]['color']);
		$bcolor = array_values($background);
		switch($numcolspace) {
			case 5:   // SPOT
			case 4: { // CMYK
				$this->gradients[$n]['colspace'] = 'DeviceCMYK';
				if (!empty($background)) {
					$this->gradients[$n]['background'] = sprintf('%F %F %F %F', $bcolor[0]/100, $bcolor[1]/100, $bcolor[2]/100, $bcolor[3]/100);
				}
				break;
			}
			case 3: { // RGB
				$this->gradients[$n]['colspace'] = 'DeviceRGB';
				if (!empty($background)) {
					$this->gradients[$n]['background'] = sprintf('%F %F %F', $bcolor[0]/255, $bcolor[1]/255, $bcolor[2]/255);
				}
				break;
			}
			case 1: { // GRAY SCALE
				$this->gradients[$n]['colspace'] = 'DeviceGray';
				if (!empty($background)) {
					$this->gradients[$n]['background'] = sprintf('%F', $bcolor[0]/255);
				}
				break;
			}
		}
		$num_stops = count($stops);
		$last_stop_id = $num_stops - 1;
		foreach ($stops as $key => $stop) {
			$this->gradients[$n]['colors'][$key] = array();
			// offset represents a location along the gradient vector
			if (isset($stop['offset'])) {
				$this->gradients[$n]['colors'][$key]['offset'] = $stop['offset'];
			} else {
				if ($key == 0) {
					$this->gradients[$n]['colors'][$key]['offset'] = 0;
				} elseif ($key == $last_stop_id) {
					$this->gradients[$n]['colors'][$key]['offset'] = 1;
				} else {
					$offsetstep = (1 - $this->gradients[$n]['colors'][($key - 1)]['offset']) / ($num_stops - $key);
					$this->gradients[$n]['colors'][$key]['offset'] = $this->gradients[$n]['colors'][($key - 1)]['offset'] + $offsetstep;
				}
			}
			if (isset($stop['opacity'])) {
				$this->gradients[$n]['colors'][$key]['opacity'] = $stop['opacity'];
				if ((!$this->pdfa_mode) AND ($stop['opacity'] < 1)) {
					$this->gradients[$n]['transparency'] = true;
				}
			} else {
				$this->gradients[$n]['colors'][$key]['opacity'] = 1;
			}
			// exponent for the exponential interpolation function
			if (isset($stop['exponent'])) {
				$this->gradients[$n]['colors'][$key]['exponent'] = $stop['exponent'];
			} else {
				$this->gradients[$n]['colors'][$key]['exponent'] = 1;
			}
			// set colors
			$color = array_values($stop['color']);
			switch($numcolspace) {
				case 5:   // SPOT
				case 4: { // CMYK
					$this->gradients[$n]['colors'][$key]['color'] = sprintf('%F %F %F %F', $color[0]/100, $color[1]/100, $color[2]/100, $color[3]/100);
					break;
				}
				case 3: { // RGB
					$this->gradients[$n]['colors'][$key]['color'] = sprintf('%F %F %F', $color[0]/255, $color[1]/255, $color[2]/255);
					break;
				}
				case 1: { // GRAY SCALE
					$this->gradients[$n]['colors'][$key]['color'] = sprintf('%F', $color[0]/255);
					break;
				}
			}
		}
		if ($this->gradients[$n]['transparency']) {
			// paint luminosity gradient
			$this->_out('/TGS'.$n.' gs');
		}
		//paint the gradient
		$this->_out('/Sh'.$n.' sh');
		//restore previous Graphic State
		$this->_outRestoreGraphicsState();
		if ($this->inxobj) {
			// we are inside an XObject template
			$this->xobjects[$this->xobjid]['gradients'][$n] = $this->gradients[$n];
		}
	}

	/**
	 * Draw the sector of a circle.
	 * It can be used for instance to render pie charts.
	 * @param $xc (float) abscissa of the center.
	 * @param $yc (float) ordinate of the center.
	 * @param $r (float) radius.
	 * @param $a (float) start angle (in degrees).
	 * @param $b (float) end angle (in degrees).
	 * @param $style (string) Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param $cw: (float) indicates whether to go clockwise (default: true).
	 * @param $o: (float) origin of angles (0 for 3 o'clock, 90 for noon, 180 for 9 o'clock, 270 for 6 o'clock). Default: 90.
	 * @author Maxime Delorme, Nicola Asuni
	 * @since 3.1.000 (2008-06-09)
	 * @public
	 */
	public function PieSector($xc, $yc, $r, $a, $b, $style='FD', $cw=true, $o=90) {
		$this->PieSectorXY($xc, $yc, $r, $r, $a, $b, $style, $cw, $o);
	}

	/**
	 * Draw the sector of an ellipse.
	 * It can be used for instance to render pie charts.
	 * @param $xc (float) abscissa of the center.
	 * @param $yc (float) ordinate of the center.
	 * @param $rx (float) the x-axis radius.
	 * @param $ry (float) the y-axis radius.
	 * @param $a (float) start angle (in degrees).
	 * @param $b (float) end angle (in degrees).
	 * @param $style (string) Style of rendering. See the getPathPaintOperator() function for more information.
	 * @param $cw: (float) indicates whether to go clockwise.
	 * @param $o: (float) origin of angles (0 for 3 o'clock, 90 for noon, 180 for 9 o'clock, 270 for 6 o'clock).
	 * @param $nc (integer) Number of curves used to draw a 90 degrees portion of arc.
	 * @author Maxime Delorme, Nicola Asuni
	 * @since 3.1.000 (2008-06-09)
	 * @public
	 */
	public function PieSectorXY($xc, $yc, $rx, $ry, $a, $b, $style='FD', $cw=false, $o=0, $nc=2) {
		if ($this->state != 2) {
			 return;
		}
		if ($this->rtl) {
			$xc = ($this->w - $xc);
		}
		$op = TCPDF_STATIC::getPathPaintOperator($style);
		if ($op == 'f') {
			$line_style = array();
		}
		if ($cw) {
			$d = $b;
			$b = (360 - $a + $o);
			$a = (360 - $d + $o);
		} else {
			$b += $o;
			$a += $o;
		}
		$this->_outellipticalarc($xc, $yc, $rx, $ry, 0, $a, $b, true, $nc);
		$this->_out($op);
	}

	/**
	 * Returns an array containing current margins:
	 * <ul>
			<li>$ret['left'] = left margin</li>
			<li>$ret['right'] = right margin</li>
			<li>$ret['top'] = top margin</li>
			<li>$ret['bottom'] = bottom margin</li>
			<li>$ret['header'] = header margin</li>
			<li>$ret['footer'] = footer margin</li>
			<li>$ret['cell'] = cell padding array</li>
			<li>$ret['padding_left'] = cell left padding</li>
			<li>$ret['padding_top'] = cell top padding</li>
			<li>$ret['padding_right'] = cell right padding</li>
			<li>$ret['padding_bottom'] = cell bottom padding</li>
	 * </ul>
	 * @return array containing all margins measures
	 * @public
	 * @since 3.2.000 (2008-06-23)
	 */
	public function getMargins() {
		$ret = array(
			'left' => $this->lMargin,
			'right' => $this->rMargin,
			'top' => $this->tMargin,
			'bottom' => $this->bMargin,
			'header' => $this->header_margin,
			'footer' => $this->footer_margin,
			'cell' => $this->cell_padding,
			'padding_left' => $this->cell_padding['L'],
			'padding_top' => $this->cell_padding['T'],
			'padding_right' => $this->cell_padding['R'],
			'padding_bottom' => $this->cell_padding['B']
		);
		return $ret;
	}

	/**
	 * Returns an array containing original margins:
	 * <ul>
			<li>$ret['left'] = left margin</li>
			<li>$ret['right'] = right margin</li>
	 * </ul>
	 * @return array containing all margins measures
	 * @public
	 * @since 4.0.012 (2008-07-24)
	 */
	public function getOriginalMargins() {
		$ret = array(
			'left' => $this->original_lMargin,
			'right' => $this->original_rMargin
		);
		return $ret;
	}

	/**
	 * Returns the current font size.
	 * @return current font size
	 * @public
	 * @since 3.2.000 (2008-06-23)
	 */
	public function getFontSize() {
		return $this->FontSize;
	}

	/**
	 * Returns the current font size in points unit.
	 * @return current font size in points unit
	 * @public
	 * @since 3.2.000 (2008-06-23)
	 */
	public function getFontSizePt() {
		return $this->FontSizePt;
	}

	/**
	 * Returns the current font family name.
	 * @return string current font family name
	 * @public
	 * @since 4.3.008 (2008-12-05)
	 */
	public function getFontFamily() {
		return $this->FontFamily;
	}

	/**
	 * Returns the current font style.
	 * @return string current font style
	 * @public
	 * @since 4.3.008 (2008-12-05)
	 */
	public function getFontStyle() {
		return $this->FontStyle;
	}

	/**
	 * Cleanup HTML code (requires HTML Tidy library).
	 * @param $html (string) htmlcode to fix
	 * @param $default_css (string) CSS commands to add
	 * @param $tagvs (array) parameters for setHtmlVSpace method
	 * @param $tidy_options (array) options for tidy_parse_string function
	 * @return string XHTML code cleaned up
	 * @author Nicola Asuni
	 * @public
	 * @since 5.9.017 (2010-11-16)
	 * @see setHtmlVSpace()
	 */
	public function fixHTMLCode($html, $default_css='', $tagvs='', $tidy_options='') {
		return TCPDF_STATIC::fixHTMLCode($html, $default_css, $tagvs, $tidy_options, $this->tagvspaces);
	}

	/**
	 * Returns the border width from CSS property
	 * @param $width (string) border width
	 * @return int with in user units
	 * @protected
	 * @since 5.7.000 (2010-08-02)
	 */
	protected function getCSSBorderWidth($width) {
		if ($width == 'thin') {
			$width = (2 / $this->k);
		} elseif ($width == 'medium') {
			$width = (4 / $this->k);
		} elseif ($width == 'thick') {
			$width = (6 / $this->k);
		} else {
			$width = $this->getHTMLUnitToUnits($width, 1, 'px', false);
		}
		return $width;
	}

	/**
	 * Returns the border dash style from CSS property
	 * @param $style (string) border style to convert
	 * @return int sash style (return -1 in case of none or hidden border)
	 * @protected
	 * @since 5.7.000 (2010-08-02)
	 */
	protected function getCSSBorderDashStyle($style) {
		switch (strtolower($style)) {
			case 'none':
			case 'hidden': {
				$dash = -1;
				break;
			}
			case 'dotted': {
				$dash = 1;
				break;
			}
			case 'dashed': {
				$dash = 3;
				break;
			}
			case 'double':
			case 'groove':
			case 'ridge':
			case 'inset':
			case 'outset':
			case 'solid':
			default: {
				$dash = 0;
				break;
			}
		}
		return $dash;
	}

	/**
	 * Returns the border style array from CSS border properties
	 * @param $cssborder (string) border properties
	 * @return array containing border properties
	 * @protected
	 * @since 5.7.000 (2010-08-02)
	 */
	protected function getCSSBorderStyle($cssborder) {
		$bprop = preg_split('/[\s]+/', trim($cssborder));
		$border = array(); // value to be returned
		switch (count($bprop)) {
			case 3: {
				$width = $bprop[0];
				$style = $bprop[1];
				$color = $bprop[2];
				break;
			}
			case 2: {
				$width = 'medium';
				$style = $bprop[0];
				$color = $bprop[1];
				break;
			}
			case 1: {
				$width = 'medium';
				$style = $bprop[0];
				$color = 'black';
				break;
			}
			default: {
				$width = 'medium';
				$style = 'solid';
				$color = 'black';
				break;
			}
		}
		if ($style == 'none') {
			return array();
		}
		$border['cap'] = 'square';
		$border['join'] = 'miter';
		$border['dash'] = $this->getCSSBorderDashStyle($style);
		if ($border['dash'] < 0) {
			return array();
		}
		$border['width'] = $this->getCSSBorderWidth($width);
		$border['color'] = TCPDF_COLORS::convertHTMLColorToDec($color, $this->spot_colors);
		return $border;
	}

	/**
	 * Get the internal Cell padding from CSS attribute.
	 * @param $csspadding (string) padding properties
	 * @param $width (float) width of the containing element
	 * @return array of cell paddings
	 * @public
	 * @since 5.9.000 (2010-10-04)
	 */
	public function getCSSPadding($csspadding, $width=0) {
		$padding = preg_split('/[\s]+/', trim($csspadding));
		$cell_padding = array(); // value to be returned
		switch (count($padding)) {
			case 4: {
				$cell_padding['T'] = $padding[0];
				$cell_padding['R'] = $padding[1];
				$cell_padding['B'] = $padding[2];
				$cell_padding['L'] = $padding[3];
				break;
			}
			case 3: {
				$cell_padding['T'] = $padding[0];
				$cell_padding['R'] = $padding[1];
				$cell_padding['B'] = $padding[2];
				$cell_padding['L'] = $padding[1];
				break;
			}
			case 2: {
				$cell_padding['T'] = $padding[0];
				$cell_padding['R'] = $padding[1];
				$cell_padding['B'] = $padding[0];
				$cell_padding['L'] = $padding[1];
				break;
			}
			case 1: {
				$cell_padding['T'] = $padding[0];
				$cell_padding['R'] = $padding[0];
				$cell_padding['B'] = $padding[0];
				$cell_padding['L'] = $padding[0];
				break;
			}
			default: {
				return $this->cell_padding;
			}
		}
		if ($width == 0) {
			$width = $this->w - $this->lMargin - $this->rMargin;
		}
		$cell_padding['T'] = $this->getHTMLUnitToUnits($cell_padding['T'], $width, 'px', false);
		$cell_padding['R'] = $this->getHTMLUnitToUnits($cell_padding['R'], $width, 'px', false);
		$cell_padding['B'] = $this->getHTMLUnitToUnits($cell_padding['B'], $width, 'px', false);
		$cell_padding['L'] = $this->getHTMLUnitToUnits($cell_padding['L'], $width, 'px', false);
		return $cell_padding;
	}

	/**
	 * Get the internal Cell margin from CSS attribute.
	 * @param $cssmargin (string) margin properties
	 * @param $width (float) width of the containing element
	 * @return array of cell margins
	 * @public
	 * @since 5.9.000 (2010-10-04)
	 */
	public function getCSSMargin($cssmargin, $width=0) {
		$margin = preg_split('/[\s]+/', trim($cssmargin));
		$cell_margin = array(); // value to be returned
		switch (count($margin)) {
			case 4: {
				$cell_margin['T'] = $margin[0];
				$cell_margin['R'] = $margin[1];
				$cell_margin['B'] = $margin[2];
				$cell_margin['L'] = $margin[3];
				break;
			}
			case 3: {
				$cell_margin['T'] = $margin[0];
				$cell_margin['R'] = $margin[1];
				$cell_margin['B'] = $margin[2];
				$cell_margin['L'] = $margin[1];
				break;
			}
			case 2: {
				$cell_margin['T'] = $margin[0];
				$cell_margin['R'] = $margin[1];
				$cell_margin['B'] = $margin[0];
				$cell_margin['L'] = $margin[1];
				break;
			}
			case 1: {
				$cell_margin['T'] = $margin[0];
				$cell_margin['R'] = $margin[0];
				$cell_margin['B'] = $margin[0];
				$cell_margin['L'] = $margin[0];
				break;
			}
			default: {
				return $this->cell_margin;
			}
		}
		if ($width == 0) {
			$width = $this->w - $this->lMargin - $this->rMargin;
		}
		$cell_margin['T'] = $this->getHTMLUnitToUnits(str_replace('auto', '0', $cell_margin['T']), $width, 'px', false);
		$cell_margin['R'] = $this->getHTMLUnitToUnits(str_replace('auto', '0', $cell_margin['R']), $width, 'px', false);
		$cell_margin['B'] = $this->getHTMLUnitToUnits(str_replace('auto', '0', $cell_margin['B']), $width, 'px', false);
		$cell_margin['L'] = $this->getHTMLUnitToUnits(str_replace('auto', '0', $cell_margin['L']), $width, 'px', false);
		return $cell_margin;
	}

	/**
	 * Get the border-spacing from CSS attribute.
	 * @param $cssbspace (string) border-spacing CSS properties
	 * @param $width (float) width of the containing element
	 * @return array of border spacings
	 * @public
	 * @since 5.9.010 (2010-10-27)
	 */
	public function getCSSBorderMargin($cssbspace, $width=0) {
		$space = preg_split('/[\s]+/', trim($cssbspace));
		$border_spacing = array(); // value to be returned
		switch (count($space)) {
			case 2: {
				$border_spacing['H'] = $space[0];
				$border_spacing['V'] = $space[1];
				break;
			}
			case 1: {
				$border_spacing['H'] = $space[0];
				$border_spacing['V'] = $space[0];
				break;
			}
			default: {
				return array('H' => 0, 'V' => 0);
			}
		}
		if ($width == 0) {
			$width = $this->w - $this->lMargin - $this->rMargin;
		}
		$border_spacing['H'] = $this->getHTMLUnitToUnits($border_spacing['H'], $width, 'px', false);
		$border_spacing['V'] = $this->getHTMLUnitToUnits($border_spacing['V'], $width, 'px', false);
		return $border_spacing;
	}

	/**
	 * Returns the letter-spacing value from CSS value
	 * @param $spacing (string) letter-spacing value
	 * @param $parent (float) font spacing (tracking) value of the parent element
	 * @return float quantity to increases or decreases the space between characters in a text.
	 * @protected
	 * @since 5.9.000 (2010-10-02)
	 */
	protected function getCSSFontSpacing($spacing, $parent=0) {
		$val = 0; // value to be returned
		$spacing = trim($spacing);
		switch ($spacing) {
			case 'normal': {
				$val = 0;
				break;
			}
			case 'inherit': {
				if ($parent == 'normal') {
					$val = 0;
				} else {
					$val = $parent;
				}
				break;
			}
			default: {
				$val = $this->getHTMLUnitToUnits($spacing, 0, 'px', false);
			}
		}
		return $val;
	}

	/**
	 * Returns the percentage of font stretching from CSS value
	 * @param $stretch (string) stretch mode
	 * @param $parent (float) stretch value of the parent element
	 * @return float font stretching percentage
	 * @protected
	 * @since 5.9.000 (2010-10-02)
	 */
	protected function getCSSFontStretching($stretch, $parent=100) {
		$val = 100; // value to be returned
		$stretch = trim($stretch);
		switch ($stretch) {
			case 'ultra-condensed': {
				$val = 40;
				break;
			}
			case 'extra-condensed': {
				$val = 55;
				break;
			}
			case 'condensed': {
				$val = 70;
				break;
			}
			case 'semi-condensed': {
				$val = 85;
				break;
			}
			case 'normal': {
				$val = 100;
				break;
			}
			case 'semi-expanded': {
				$val = 115;
				break;
			}
			case 'expanded': {
				$val = 130;
				break;
			}
			case 'extra-expanded': {
				$val = 145;
				break;
			}
			case 'ultra-expanded': {
				$val = 160;
				break;
			}
			case 'wider': {
				$val = ($parent + 10);
				break;
			}
			case 'narrower': {
				$val = ($parent - 10);
				break;
			}
			case 'inherit': {
				if ($parent == 'normal') {
					$val = 100;
				} else {
					$val = $parent;
				}
				break;
			}
			default: {
				$val = $this->getHTMLUnitToUnits($stretch, 100, '%', false);
			}
		}
		return $val;
	}

	

	/**
	 * Returns the string used to find spaces
	 * @return string
	 * @protected
	 * @author Nicola Asuni
	 * @since 4.8.024 (2010-01-15)
	 */
	protected function getSpaceString() {
		$spacestr = chr(32);
		if ($this->isUnicodeFont()) {
			$spacestr = chr(0).chr(32);
		}
		return $spacestr;
	}

	/**
	 * Return an hash code used to ensure that the serialized data has been generated by this TCPDF instance.
	 * @param $data (string) serialized data
	 * @return string
	 * @public static
	 */
	protected function getHashForTCPDFtagParams($data) {
		return md5(strlen($data).$this->file_id.$data);
	}

	/**
	 * Serialize an array of parameters to be used with TCPDF tag in HTML code.
	 * @param $data (array) parameters array
	 * @return string containing serialized data
	 * @public static
	 */
	public function serializeTCPDFtagParameters($data) {
		$encoded = urlencode(json_encode($data));
		return $this->getHashForTCPDFtagParams($encoded).$encoded;
	}

	/**
	 * Unserialize parameters to be used with TCPDF tag in HTML code.
	 * @param $data (string) serialized data
	 * @return array containing unserialized data
	 * @protected static
	 */
	protected function unserializeTCPDFtagParameters($data) {
		$hash = substr($data, 0, 32);
		$encoded = substr($data, 32);
		if ($hash != $this->getHashForTCPDFtagParams($encoded)) {
			$this->Error('Invalid parameters');
		}
		return json_decode(urldecode($encoded), true);
	}

	/**
	 * Returns current graphic variables as array.
	 * @return array of graphic variables
	 * @protected
	 * @since 4.2.010 (2008-11-14)
	 */
	protected function getGraphicVars() {
		$grapvars = array(
			'FontFamily' => $this->FontFamily,
			'FontStyle' => $this->FontStyle,
			'FontSizePt' => $this->FontSizePt,
			'rMargin' => $this->rMargin,
			'lMargin' => $this->lMargin,
			'cell_padding' => $this->cell_padding,
			'cell_margin' => $this->cell_margin,
			'LineWidth' => $this->LineWidth,
			'linestyleWidth' => $this->linestyleWidth,
			'linestyleCap' => $this->linestyleCap,
			'linestyleJoin' => $this->linestyleJoin,
			'linestyleDash' => $this->linestyleDash,
			'textrendermode' => $this->textrendermode,
			'textstrokewidth' => $this->textstrokewidth,
			'DrawColor' => $this->DrawColor,
			'FillColor' => $this->FillColor,
			'TextColor' => $this->TextColor,
			'ColorFlag' => $this->ColorFlag,
			'bgcolor' => $this->bgcolor,
			'fgcolor' => $this->fgcolor,
			'htmlvspace' => $this->htmlvspace,
			'listindent' => $this->listindent,
			'listindentlevel' => $this->listindentlevel,
			'listnum' => $this->listnum,
			'listordered' => $this->listordered,
			'listcount' => $this->listcount,
			'lispacer' => $this->lispacer,
			'cell_height_ratio' => $this->cell_height_ratio,
			'font_stretching' => $this->font_stretching,
			'font_spacing' => $this->font_spacing,
			'alpha' => $this->alpha,
			// extended
			'lasth' => $this->lasth,
			'tMargin' => $this->tMargin,
			'bMargin' => $this->bMargin,
			'AutoPageBreak' => $this->AutoPageBreak,
			'PageBreakTrigger' => $this->PageBreakTrigger,
			'x' => $this->x,
			'y' => $this->y,
			'w' => $this->w,
			'h' => $this->h,
			'wPt' => $this->wPt,
			'hPt' => $this->hPt,
			'fwPt' => $this->fwPt,
			'fhPt' => $this->fhPt,
			'page' => $this->page,
			'current_column' => $this->current_column,
			'num_columns' => $this->num_columns
			);
		return $grapvars;
	}

	/**
	 * Set graphic variables.
	 * @param $gvars (array) array of graphic variablesto restore
	 * @param $extended (boolean) if true restore extended graphic variables
	 * @protected
	 * @since 4.2.010 (2008-11-14)
	 */
	protected function setGraphicVars($gvars, $extended=false) {
		if ($this->state != 2) {
			 return;
		}
		$this->FontFamily = $gvars['FontFamily'];
		$this->FontStyle = $gvars['FontStyle'];
		$this->FontSizePt = $gvars['FontSizePt'];
		$this->rMargin = $gvars['rMargin'];
		$this->lMargin = $gvars['lMargin'];
		$this->cell_padding = $gvars['cell_padding'];
		$this->cell_margin = $gvars['cell_margin'];
		$this->LineWidth = $gvars['LineWidth'];
		$this->linestyleWidth = $gvars['linestyleWidth'];
		$this->linestyleCap = $gvars['linestyleCap'];
		$this->linestyleJoin = $gvars['linestyleJoin'];
		$this->linestyleDash = $gvars['linestyleDash'];
		$this->textrendermode = $gvars['textrendermode'];
		$this->textstrokewidth = $gvars['textstrokewidth'];
		$this->DrawColor = $gvars['DrawColor'];
		$this->FillColor = $gvars['FillColor'];
		$this->TextColor = $gvars['TextColor'];
		$this->ColorFlag = $gvars['ColorFlag'];
		$this->bgcolor = $gvars['bgcolor'];
		$this->fgcolor = $gvars['fgcolor'];
		$this->htmlvspace = $gvars['htmlvspace'];
		$this->listindent = $gvars['listindent'];
		$this->listindentlevel = $gvars['listindentlevel'];
		$this->listnum = $gvars['listnum'];
		$this->listordered = $gvars['listordered'];
		$this->listcount = $gvars['listcount'];
		$this->lispacer = $gvars['lispacer'];
		$this->cell_height_ratio = $gvars['cell_height_ratio'];
		$this->font_stretching = $gvars['font_stretching'];
		$this->font_spacing = $gvars['font_spacing'];
		$this->alpha = $gvars['alpha'];
		if ($extended) {
			// restore extended values
			$this->lasth = $gvars['lasth'];
			$this->tMargin = $gvars['tMargin'];
			$this->bMargin = $gvars['bMargin'];
			$this->AutoPageBreak = $gvars['AutoPageBreak'];
			$this->PageBreakTrigger = $gvars['PageBreakTrigger'];
			$this->x = $gvars['x'];
			$this->y = $gvars['y'];
			$this->w = $gvars['w'];
			$this->h = $gvars['h'];
			$this->wPt = $gvars['wPt'];
			$this->hPt = $gvars['hPt'];
			$this->fwPt = $gvars['fwPt'];
			$this->fhPt = $gvars['fhPt'];
			$this->page = $gvars['page'];
			$this->current_column = $gvars['current_column'];
			$this->num_columns = $gvars['num_columns'];
		}
		$this->_out(''.$this->linestyleWidth.' '.$this->linestyleCap.' '.$this->linestyleJoin.' '.$this->linestyleDash.' '.$this->DrawColor.' '.$this->FillColor.'');
		if (!TCPDF_STATIC::empty_string($this->FontFamily)) {
			$this->SetFont($this->FontFamily, $this->FontStyle, $this->FontSizePt);
		}
	}

	/**
	 * Outputs the "save graphics state" operator 'q'
	 * @protected
	 */
	protected function _outSaveGraphicsState() {
		$this->_out('q');
	}

	/**
	 * Outputs the "restore graphics state" operator 'Q'
	 * @protected
	 */
	protected function _outRestoreGraphicsState() {
		$this->_out('Q');
	}

	/**
	 * Set buffer content (always append data).
	 * @param $data (string) data
	 * @protected
	 * @since 4.5.000 (2009-01-02)
	 */
	protected function setBuffer($data) {
		$this->bufferlen += strlen($data);
		$this->buffer .= $data;
	}

	/**
	 * Replace the buffer content
	 * @param $data (string) data
	 * @protected
	 * @since 5.5.000 (2010-06-22)
	 */
	protected function replaceBuffer($data) {
		$this->bufferlen = strlen($data);
		$this->buffer = $data;
	}

	/**
	 * Get buffer content.
	 * @return string buffer content
	 * @protected
	 * @since 4.5.000 (2009-01-02)
	 */
	protected function getBuffer() {
		return $this->buffer;
	}

	/**
	 * Set page buffer content.
	 * @param $page (int) page number
	 * @param $data (string) page data
	 * @param $append (boolean) if true append data, false replace.
	 * @protected
	 * @since 4.5.000 (2008-12-31)
	 */
	protected function setPageBuffer($page, $data, $append=false) {
		if ($append) {
			$this->pages[$page] .= $data;
		} else {
			$this->pages[$page] = $data;
		}
		if ($append AND isset($this->pagelen[$page])) {
			$this->pagelen[$page] += strlen($data);
		} else {
			$this->pagelen[$page] = strlen($data);
		}
	}

	/**
	 * Get page buffer content.
	 * @param $page (int) page number
	 * @return string page buffer content or false in case of error
	 * @protected
	 * @since 4.5.000 (2008-12-31)
	 */
	protected function getPageBuffer($page) {
		if (isset($this->pages[$page])) {
			return $this->pages[$page];
		}
		return false;
	}

	/**
	 * Set image buffer content.
	 * @param $image (string) image key
	 * @param $data (array) image data
	 * @return int image index number
	 * @protected
	 * @since 4.5.000 (2008-12-31)
	 */
	protected function setImageBuffer($image, $data) {
		if (($data['i'] = array_search($image, $this->imagekeys)) === FALSE) {
			$this->imagekeys[$this->numimages] = $image;
			$data['i'] = $this->numimages;
			++$this->numimages;
		}
		$this->images[$image] = $data;
		return $data['i'];
	}

	/**
	 * Set image buffer content for a specified sub-key.
	 * @param $image (string) image key
	 * @param $key (string) image sub-key
	 * @param $data (array) image data
	 * @protected
	 * @since 4.5.000 (2008-12-31)
	 */
	protected function setImageSubBuffer($image, $key, $data) {
		if (!isset($this->images[$image])) {
			$this->setImageBuffer($image, array());
		}
		$this->images[$image][$key] = $data;
	}

	/**
	 * Get image buffer content.
	 * @param $image (string) image key
	 * @return string image buffer content or false in case of error
	 * @protected
	 * @since 4.5.000 (2008-12-31)
	 */
	protected function getImageBuffer($image) {
		if (isset($this->images[$image])) {
			return $this->images[$image];
		}
		return false;
	}

	/**
	 * Set font buffer content.
	 * @param $font (string) font key
	 * @param $data (array) font data
	 * @protected
	 * @since 4.5.000 (2009-01-02)
	 */
	protected function setFontBuffer($font, $data) {
		$this->fonts[$font] = $data;
		if (!in_array($font, $this->fontkeys)) {
			$this->fontkeys[] = $font;
			// store object ID for current font
			++$this->n;
			$this->font_obj_ids[$font] = $this->n;
			$this->setFontSubBuffer($font, 'n', $this->n);
		}
	}

	/**
	 * Set font buffer content.
	 * @param $font (string) font key
	 * @param $key (string) font sub-key
	 * @param $data (array) font data
	 * @protected
	 * @since 4.5.000 (2009-01-02)
	 */
	protected function setFontSubBuffer($font, $key, $data) {
		if (!isset($this->fonts[$font])) {
			$this->setFontBuffer($font, array());
		}
		$this->fonts[$font][$key] = $data;
	}

	/**
	 * Get font buffer content.
	 * @param $font (string) font key
	 * @return string font buffer content or false in case of error
	 * @protected
	 * @since 4.5.000 (2009-01-02)
	 */
	protected function getFontBuffer($font) {
		if (isset($this->fonts[$font])) {
			return $this->fonts[$font];
		}
		return false;
	}

	/**
	 * Move a page to a previous position.
	 * @param $frompage (int) number of the source page
	 * @param $topage (int) number of the destination page (must be less than $frompage)
	 * @return true in case of success, false in case of error.
	 * @public
	 * @since 4.5.000 (2009-01-02)
	 */
	public function movePage($frompage, $topage) {
		if (($frompage > $this->numpages) OR ($frompage <= $topage)) {
			return false;
		}
		if ($frompage == $this->page) {
			// close the page before moving it
			$this->endPage();
		}
		// move all page-related states
		$tmppage = $this->getPageBuffer($frompage);
		$tmppagedim = $this->pagedim[$frompage];
		$tmppagelen = $this->pagelen[$frompage];
		$tmpintmrk = $this->intmrk[$frompage];
		$tmpbordermrk = $this->bordermrk[$frompage];
		$tmpcntmrk = $this->cntmrk[$frompage];
		$tmppageobjects = $this->pageobjects[$frompage];
		if (isset($this->footerpos[$frompage])) {
			$tmpfooterpos = $this->footerpos[$frompage];
		}
		if (isset($this->footerlen[$frompage])) {
			$tmpfooterlen = $this->footerlen[$frompage];
		}
		if (isset($this->transfmrk[$frompage])) {
			$tmptransfmrk = $this->transfmrk[$frompage];
		}
		if (isset($this->PageAnnots[$frompage])) {
			$tmpannots = $this->PageAnnots[$frompage];
		}
		if (isset($this->newpagegroup) AND !empty($this->newpagegroup)) {
			for ($i = $frompage; $i > $topage; --$i) {
				if (isset($this->newpagegroup[$i]) AND (($i + $this->pagegroups[$this->newpagegroup[$i]]) > $frompage)) {
					--$this->pagegroups[$this->newpagegroup[$i]];
					break;
				}
			}
			for ($i = $topage; $i > 0; --$i) {
				if (isset($this->newpagegroup[$i]) AND (($i + $this->pagegroups[$this->newpagegroup[$i]]) > $topage)) {
					++$this->pagegroups[$this->newpagegroup[$i]];
					break;
				}
			}
		}
		for ($i = $frompage; $i > $topage; --$i) {
			$j = $i - 1;
			// shift pages down
			$this->setPageBuffer($i, $this->getPageBuffer($j));
			$this->pagedim[$i] = $this->pagedim[$j];
			$this->pagelen[$i] = $this->pagelen[$j];
			$this->intmrk[$i] = $this->intmrk[$j];
			$this->bordermrk[$i] = $this->bordermrk[$j];
			$this->cntmrk[$i] = $this->cntmrk[$j];
			$this->pageobjects[$i] = $this->pageobjects[$j];
			if (isset($this->footerpos[$j])) {
				$this->footerpos[$i] = $this->footerpos[$j];
			} elseif (isset($this->footerpos[$i])) {
				unset($this->footerpos[$i]);
			}
			if (isset($this->footerlen[$j])) {
				$this->footerlen[$i] = $this->footerlen[$j];
			} elseif (isset($this->footerlen[$i])) {
				unset($this->footerlen[$i]);
			}
			if (isset($this->transfmrk[$j])) {
				$this->transfmrk[$i] = $this->transfmrk[$j];
			} elseif (isset($this->transfmrk[$i])) {
				unset($this->transfmrk[$i]);
			}
			if (isset($this->PageAnnots[$j])) {
				$this->PageAnnots[$i] = $this->PageAnnots[$j];
			} elseif (isset($this->PageAnnots[$i])) {
				unset($this->PageAnnots[$i]);
			}
			if (isset($this->newpagegroup[$j])) {
				$this->newpagegroup[$i] = $this->newpagegroup[$j];
				unset($this->newpagegroup[$j]);
			}
			if ($this->currpagegroup == $j) {
				$this->currpagegroup = $i;
			}
		}
		$this->setPageBuffer($topage, $tmppage);
		$this->pagedim[$topage] = $tmppagedim;
		$this->pagelen[$topage] = $tmppagelen;
		$this->intmrk[$topage] = $tmpintmrk;
		$this->bordermrk[$topage] = $tmpbordermrk;
		$this->cntmrk[$topage] = $tmpcntmrk;
		$this->pageobjects[$topage] = $tmppageobjects;
		if (isset($tmpfooterpos)) {
			$this->footerpos[$topage] = $tmpfooterpos;
		} elseif (isset($this->footerpos[$topage])) {
			unset($this->footerpos[$topage]);
		}
		if (isset($tmpfooterlen)) {
			$this->footerlen[$topage] = $tmpfooterlen;
		} elseif (isset($this->footerlen[$topage])) {
			unset($this->footerlen[$topage]);
		}
		if (isset($tmptransfmrk)) {
			$this->transfmrk[$topage] = $tmptransfmrk;
		} elseif (isset($this->transfmrk[$topage])) {
			unset($this->transfmrk[$topage]);
		}
		if (isset($tmpannots)) {
			$this->PageAnnots[$topage] = $tmpannots;
		} elseif (isset($this->PageAnnots[$topage])) {
			unset($this->PageAnnots[$topage]);
		}
		// adjust outlines
		$tmpoutlines = $this->outlines;
		foreach ($tmpoutlines as $key => $outline) {
			if (!$outline['f']) {
				if (($outline['p'] >= $topage) AND ($outline['p'] < $frompage)) {
					$this->outlines[$key]['p'] = ($outline['p'] + 1);
				} elseif ($outline['p'] == $frompage) {
					$this->outlines[$key]['p'] = $topage;
				}
			}
		}
		// adjust dests
		$tmpdests = $this->dests;
		foreach ($tmpdests as $key => $dest) {
			if (!$dest['f']) {
				if (($dest['p'] >= $topage) AND ($dest['p'] < $frompage)) {
					$this->dests[$key]['p'] = ($dest['p'] + 1);
				} elseif ($dest['p'] == $frompage) {
					$this->dests[$key]['p'] = $topage;
				}
			}
		}
		// adjust links
		$tmplinks = $this->links;
		foreach ($tmplinks as $key => $link) {
			if (!$link['f']) {
				if (($link['p'] >= $topage) AND ($link['p'] < $frompage)) {
					$this->links[$key]['p'] = ($link['p'] + 1);
				} elseif ($link['p'] == $frompage) {
					$this->links[$key]['p'] = $topage;
				}
			}
		}
		// adjust javascript
		$jfrompage = $frompage;
		$jtopage = $topage;
		if (preg_match_all('/this\.addField\(\'([^\']*)\',\'([^\']*)\',([0-9]+)/', $this->javascript, $pamatch) > 0) {
			foreach($pamatch[0] as $pk => $pmatch) {
				$pagenum = intval($pamatch[3][$pk]) + 1;
				if (($pagenum >= $jtopage) AND ($pagenum < $jfrompage)) {
					$newpage = ($pagenum + 1);
				} elseif ($pagenum == $jfrompage) {
					$newpage = $jtopage;
				} else {
					$newpage = $pagenum;
				}
				--$newpage;
				$newjs = "this.addField(\'".$pamatch[1][$pk]."\',\'".$pamatch[2][$pk]."\',".$newpage;
				$this->javascript = str_replace($pmatch, $newjs, $this->javascript);
			}
			unset($pamatch);
		}
		// return to last page
		$this->lastPage(true);
		return true;
	}

	/**
	 * Remove the specified page.
	 * @param $page (int) page to remove
	 * @return true in case of success, false in case of error.
	 * @public
	 * @since 4.6.004 (2009-04-23)
	 */
	public function deletePage($page) {
		if (($page < 1) OR ($page > $this->numpages)) {
			return false;
		}
		// delete current page
		unset($this->pages[$page]);
		unset($this->pagedim[$page]);
		unset($this->pagelen[$page]);
		unset($this->intmrk[$page]);
		unset($this->bordermrk[$page]);
		unset($this->cntmrk[$page]);
		foreach ($this->pageobjects[$page] as $oid) {
			if (isset($this->offsets[$oid])){
				unset($this->offsets[$oid]);
			}
		}
		unset($this->pageobjects[$page]);
		if (isset($this->footerpos[$page])) {
			unset($this->footerpos[$page]);
		}
		if (isset($this->footerlen[$page])) {
			unset($this->footerlen[$page]);
		}
		if (isset($this->transfmrk[$page])) {
			unset($this->transfmrk[$page]);
		}
		if (isset($this->PageAnnots[$page])) {
			unset($this->PageAnnots[$page]);
		}
		if (isset($this->newpagegroup) AND !empty($this->newpagegroup)) {
			for ($i = $page; $i > 0; --$i) {
				if (isset($this->newpagegroup[$i]) AND (($i + $this->pagegroups[$this->newpagegroup[$i]]) > $page)) {
					--$this->pagegroups[$this->newpagegroup[$i]];
					break;
				}
			}
		}
		if (isset($this->pageopen[$page])) {
			unset($this->pageopen[$page]);
		}
		if ($page < $this->numpages) {
			// update remaining pages
			for ($i = $page; $i < $this->numpages; ++$i) {
				$j = $i + 1;
				// shift pages
				$this->setPageBuffer($i, $this->getPageBuffer($j));
				$this->pagedim[$i] = $this->pagedim[$j];
				$this->pagelen[$i] = $this->pagelen[$j];
				$this->intmrk[$i] = $this->intmrk[$j];
				$this->bordermrk[$i] = $this->bordermrk[$j];
				$this->cntmrk[$i] = $this->cntmrk[$j];
				$this->pageobjects[$i] = $this->pageobjects[$j];
				if (isset($this->footerpos[$j])) {
					$this->footerpos[$i] = $this->footerpos[$j];
				} elseif (isset($this->footerpos[$i])) {
					unset($this->footerpos[$i]);
				}
				if (isset($this->footerlen[$j])) {
					$this->footerlen[$i] = $this->footerlen[$j];
				} elseif (isset($this->footerlen[$i])) {
					unset($this->footerlen[$i]);
				}
				if (isset($this->transfmrk[$j])) {
					$this->transfmrk[$i] = $this->transfmrk[$j];
				} elseif (isset($this->transfmrk[$i])) {
					unset($this->transfmrk[$i]);
				}
				if (isset($this->PageAnnots[$j])) {
					$this->PageAnnots[$i] = $this->PageAnnots[$j];
				} elseif (isset($this->PageAnnots[$i])) {
					unset($this->PageAnnots[$i]);
				}
				if (isset($this->newpagegroup[$j])) {
					$this->newpagegroup[$i] = $this->newpagegroup[$j];
					unset($this->newpagegroup[$j]);
				}
				if ($this->currpagegroup == $j) {
					$this->currpagegroup = $i;
				}
				if (isset($this->pageopen[$j])) {
					$this->pageopen[$i] = $this->pageopen[$j];
				} elseif (isset($this->pageopen[$i])) {
					unset($this->pageopen[$i]);
				}
			}
			// remove last page
			unset($this->pages[$this->numpages]);
			unset($this->pagedim[$this->numpages]);
			unset($this->pagelen[$this->numpages]);
			unset($this->intmrk[$this->numpages]);
			unset($this->bordermrk[$this->numpages]);
			unset($this->cntmrk[$this->numpages]);
			foreach ($this->pageobjects[$this->numpages] as $oid) {
				if (isset($this->offsets[$oid])){
					unset($this->offsets[$oid]);
				}
			}
			unset($this->pageobjects[$this->numpages]);
			if (isset($this->footerpos[$this->numpages])) {
				unset($this->footerpos[$this->numpages]);
			}
			if (isset($this->footerlen[$this->numpages])) {
				unset($this->footerlen[$this->numpages]);
			}
			if (isset($this->transfmrk[$this->numpages])) {
				unset($this->transfmrk[$this->numpages]);
			}
			if (isset($this->PageAnnots[$this->numpages])) {
				unset($this->PageAnnots[$this->numpages]);
			}
			if (isset($this->newpagegroup[$this->numpages])) {
				unset($this->newpagegroup[$this->numpages]);
			}
			if ($this->currpagegroup == $this->numpages) {
				$this->currpagegroup = ($this->numpages - 1);
			}
			if (isset($this->pagegroups[$this->numpages])) {
				unset($this->pagegroups[$this->numpages]);
			}
			if (isset($this->pageopen[$this->numpages])) {
				unset($this->pageopen[$this->numpages]);
			}
		}
		--$this->numpages;
		$this->page = $this->numpages;
		// adjust outlines
		$tmpoutlines = $this->outlines;
		foreach ($tmpoutlines as $key => $outline) {
			if (!$outline['f']) {
				if ($outline['p'] > $page) {
					$this->outlines[$key]['p'] = $outline['p'] - 1;
				} elseif ($outline['p'] == $page) {
					unset($this->outlines[$key]);
				}
			}
		}
		// adjust dests
		$tmpdests = $this->dests;
		foreach ($tmpdests as $key => $dest) {
			if (!$dest['f']) {
				if ($dest['p'] > $page) {
					$this->dests[$key]['p'] = $dest['p'] - 1;
				} elseif ($dest['p'] == $page) {
					unset($this->dests[$key]);
				}
			}
		}
		// adjust links
		$tmplinks = $this->links;
		foreach ($tmplinks as $key => $link) {
			if (!$link['f']) {
				if ($link['p'] > $page) {
					$this->links[$key]['p'] = $link['p'] - 1;
				} elseif ($link['p'] == $page) {
					unset($this->links[$key]);
				}
			}
		}
		// adjust javascript
		$jpage = $page;
		if (preg_match_all('/this\.addField\(\'([^\']*)\',\'([^\']*)\',([0-9]+)/', $this->javascript, $pamatch) > 0) {
			foreach($pamatch[0] as $pk => $pmatch) {
				$pagenum = intval($pamatch[3][$pk]) + 1;
				if ($pagenum >= $jpage) {
					$newpage = ($pagenum - 1);
				} elseif ($pagenum == $jpage) {
					$newpage = 1;
				} else {
					$newpage = $pagenum;
				}
				--$newpage;
				$newjs = "this.addField(\'".$pamatch[1][$pk]."\',\'".$pamatch[2][$pk]."\',".$newpage;
				$this->javascript = str_replace($pmatch, $newjs, $this->javascript);
			}
			unset($pamatch);
		}
		// return to last page
		if ($this->numpages > 0) {
			$this->lastPage(true);
		}
		return true;
	}

	/**
	 * Clone the specified page to a new page.
	 * @param $page (int) number of page to copy (0 = current page)
	 * @return true in case of success, false in case of error.
	 * @public
	 * @since 4.9.015 (2010-04-20)
	 */
	public function copyPage($page=0) {
		if ($page == 0) {
			// default value
			$page = $this->page;
		}
		if (($page < 1) OR ($page > $this->numpages)) {
			return false;
		}
		// close the last page
		$this->endPage();
		// copy all page-related states
		++$this->numpages;
		$this->page = $this->numpages;
		$this->setPageBuffer($this->page, $this->getPageBuffer($page));
		$this->pagedim[$this->page] = $this->pagedim[$page];
		$this->pagelen[$this->page] = $this->pagelen[$page];
		$this->intmrk[$this->page] = $this->intmrk[$page];
		$this->bordermrk[$this->page] = $this->bordermrk[$page];
		$this->cntmrk[$this->page] = $this->cntmrk[$page];
		$this->pageobjects[$this->page] = $this->pageobjects[$page];
		$this->pageopen[$this->page] = false;
		if (isset($this->footerpos[$page])) {
			$this->footerpos[$this->page] = $this->footerpos[$page];
		}
		if (isset($this->footerlen[$page])) {
			$this->footerlen[$this->page] = $this->footerlen[$page];
		}
		if (isset($this->transfmrk[$page])) {
			$this->transfmrk[$this->page] = $this->transfmrk[$page];
		}
		if (isset($this->PageAnnots[$page])) {
			$this->PageAnnots[$this->page] = $this->PageAnnots[$page];
		}
		if (isset($this->newpagegroup[$page])) {
			// start a new group
			$this->newpagegroup[$this->page] = sizeof($this->newpagegroup) + 1;
			$this->currpagegroup = $this->newpagegroup[$this->page];
			$this->pagegroups[$this->currpagegroup] = 1;
		} elseif (isset($this->currpagegroup) AND ($this->currpagegroup > 0)) {
			++$this->pagegroups[$this->currpagegroup];
		}
		// copy outlines
		$tmpoutlines = $this->outlines;
		foreach ($tmpoutlines as $key => $outline) {
			if ($outline['p'] == $page) {
				$this->outlines[] = array('t' => $outline['t'], 'l' => $outline['l'], 'x' => $outline['x'], 'y' => $outline['y'], 'p' => $this->page, 'f' => $outline['f'], 's' => $outline['s'], 'c' => $outline['c']);
			}
		}
		// copy links
		$tmplinks = $this->links;
		foreach ($tmplinks as $key => $link) {
			if ($link['p'] == $page) {
				$this->links[] = array('p' => $this->page, 'y' => $link['y'], 'f' => $link['f']);
			}
		}
		// return to last page
		$this->lastPage(true);
		return true;
	}

	/**
	 * Output a Table of Content Index (TOC).
	 * This method must be called after all Bookmarks were set.
	 * Before calling this method you have to open the page using the addTOCPage() method.
	 * After calling this method you have to call endTOCPage() to close the TOC page.
	 * You can override this method to achieve different styles.
	 * @param $page (int) page number where this TOC should be inserted (leave empty for current page).
	 * @param $numbersfont (string) set the font for page numbers (please use monospaced font for better alignment).
	 * @param $filler (string) string used to fill the space between text and page number.
	 * @param $toc_name (string) name to use for TOC bookmark.
	 * @param $style (string) Font style for title: B = Bold, I = Italic, BI = Bold + Italic.
	 * @param $color (array) RGB color array for bookmark title (values from 0 to 255).
	 * @public
	 * @author Nicola Asuni
	 * @since 4.5.000 (2009-01-02)
	 * @see addTOCPage(), endTOCPage(), addHTMLTOC()
	 */
	public function addTOC($page='', $numbersfont='', $filler='.', $toc_name='TOC', $style='', $color=array(0,0,0)) {
		$fontsize = $this->FontSizePt;
		$fontfamily = $this->FontFamily;
		$fontstyle = $this->FontStyle;
		$w = $this->w - $this->lMargin - $this->rMargin;
		$spacer = $this->GetStringWidth(chr(32)) * 4;
		$lmargin = $this->lMargin;
		$rmargin = $this->rMargin;
		$x_start = $this->GetX();
		$page_first = $this->page;
		$current_page = $this->page;
		$page_fill_start = false;
		$page_fill_end = false;
		$current_column = $this->current_column;
		if (TCPDF_STATIC::empty_string($numbersfont)) {
			$numbersfont = $this->default_monospaced_font;
		}
		if (TCPDF_STATIC::empty_string($filler)) {
			$filler = ' ';
		}
		if (TCPDF_STATIC::empty_string($page)) {
			$gap = ' ';
		} else {
			$gap = '';
			if ($page < 1) {
				$page = 1;
			}
		}
		$this->SetFont($numbersfont, $fontstyle, $fontsize);
		$numwidth = $this->GetStringWidth('00000');
		$maxpage = 0; //used for pages on attached documents
		foreach ($this->outlines as $key => $outline) {
			// check for extra pages (used for attachments)
			if (($this->page > $page_first) AND ($outline['p'] >= $this->numpages)) {
				$outline['p'] += ($this->page - $page_first);
			}
			if ($this->rtl) {
				$aligntext = 'R';
				$alignnum = 'L';
			} else {
				$aligntext = 'L';
				$alignnum = 'R';
			}
			if ($outline['l'] == 0) {
				$this->SetFont($fontfamily, $outline['s'].'B', $fontsize);
			} else {
				$this->SetFont($fontfamily, $outline['s'], $fontsize - $outline['l']);
			}
			$this->SetTextColorArray($outline['c']);
			// check for page break
			$this->checkPageBreak(2 * $this->getCellHeight($this->FontSize));
			// set margins and X position
			if (($this->page == $current_page) AND ($this->current_column == $current_column)) {
				$this->lMargin = $lmargin;
				$this->rMargin = $rmargin;
			} else {
				if ($this->current_column != $current_column) {
					if ($this->rtl) {
						$x_start = $this->w - $this->columns[$this->current_column]['x'];
					} else {
						$x_start = $this->columns[$this->current_column]['x'];
					}
				}
				$lmargin = $this->lMargin;
				$rmargin = $this->rMargin;
				$current_page = $this->page;
				$current_column = $this->current_column;
			}
			$this->SetX($x_start);
			$indent = ($spacer * $outline['l']);
			if ($this->rtl) {
				$this->x -= $indent;
				$this->rMargin = $this->w - $this->x;
			} else {
				$this->x += $indent;
				$this->lMargin = $this->x;
			}
			$link = $this->AddLink();
			$this->SetLink($link, $outline['y'], $outline['p']);
			// write the text
			if ($this->rtl) {
				$txt = ' '.$outline['t'];
			} else {
				$txt = $outline['t'].' ';
			}
			$this->Write(0, $txt, $link, false, $aligntext, false, 0, false, false, 0, $numwidth, '');
			if ($this->rtl) {
				$tw = $this->x - $this->lMargin;
			} else {
				$tw = $this->w - $this->rMargin - $this->x;
			}
			$this->SetFont($numbersfont, $fontstyle, $fontsize);
			if (TCPDF_STATIC::empty_string($page)) {
				$pagenum = $outline['p'];
			} else {
				// placemark to be replaced with the correct number
				$pagenum = '{#'.($outline['p']).'}';
				if ($this->isUnicodeFont()) {
					$pagenum = '{'.$pagenum.'}';
				}
				$maxpage = max($maxpage, $outline['p']);
			}
			$fw = ($tw - $this->GetStringWidth($pagenum.$filler));
			$wfiller = $this->GetStringWidth($filler);
			if ($wfiller > 0) {
				$numfills = floor($fw / $wfiller);
			} else {
				$numfills = 0;
			}
			if ($numfills > 0) {
				$rowfill = str_repeat($filler, $numfills);
			} else {
				$rowfill = '';
			}
			if ($this->rtl) {
				$pagenum = $pagenum.$gap.$rowfill;
			} else {
				$pagenum = $rowfill.$gap.$pagenum;
			}
			// write the number
			$this->Cell($tw, 0, $pagenum, 0, 1, $alignnum, 0, $link, 0);
		}
		$page_last = $this->getPage();
		$numpages = ($page_last - $page_first + 1);
		// account for booklet mode
		if ($this->booklet) {
			// check if a blank page is required before TOC
			$page_fill_start = ((($page_first % 2) == 0) XOR (($page % 2) == 0));
			$page_fill_end = (!((($numpages % 2) == 0) XOR ($page_fill_start)));
			if ($page_fill_start) {
				// add a page at the end (to be moved before TOC)
				$this->addPage();
				++$page_last;
				++$numpages;
			}
			if ($page_fill_end) {
				// add a page at the end
				$this->addPage();
				++$page_last;
				++$numpages;
			}
		}
		$maxpage = max($maxpage, $page_last);
		if (!TCPDF_STATIC::empty_string($page)) {
			for ($p = $page_first; $p <= $page_last; ++$p) {
				// get page data
				$temppage = $this->getPageBuffer($p);
				for ($n = 1; $n <= $maxpage; ++$n) {
					// update page numbers
					$a = '{#'.$n.'}';
					// get page number aliases
					$pnalias = $this->getInternalPageNumberAliases($a);
					// calculate replacement number
					if (($n >= $page) AND ($n <= $this->numpages)) {
						$np = $n + $numpages;
					} else {
						$np = $n;
					}
					$na = TCPDF_STATIC::formatTOCPageNumber(($this->starting_page_number + $np - 1));
					$nu = TCPDF_FONTS::UTF8ToUTF16BE($na, false, $this->isunicode, $this->CurrentFont);
					// replace aliases with numbers
					foreach ($pnalias['u'] as $u) {
						$sfill = str_repeat($filler, max(0, (strlen($u) - strlen($nu.' '))));
						if ($this->rtl) {
							$nr = $nu.TCPDF_FONTS::UTF8ToUTF16BE(' '.$sfill, false, $this->isunicode, $this->CurrentFont);
						} else {
							$nr = TCPDF_FONTS::UTF8ToUTF16BE($sfill.' ', false, $this->isunicode, $this->CurrentFont).$nu;
						}
						$temppage = str_replace($u, $nr, $temppage);
					}
					foreach ($pnalias['a'] as $a) {
						$sfill = str_repeat($filler, max(0, (strlen($a) - strlen($na.' '))));
						if ($this->rtl) {
							$nr = $na.' '.$sfill;
						} else {
							$nr = $sfill.' '.$na;
						}
						$temppage = str_replace($a, $nr, $temppage);
					}
				}
				// save changes
				$this->setPageBuffer($p, $temppage);
			}
			// move pages
			$this->Bookmark($toc_name, 0, 0, $page_first, $style, $color);
			if ($page_fill_start) {
				$this->movePage($page_last, $page_first);
			}
			for ($i = 0; $i < $numpages; ++$i) {
				$this->movePage($page_last, $page);
			}
		}
	}

	/**
	 * Output a Table Of Content Index (TOC) using HTML templates.
	 * This method must be called after all Bookmarks were set.
	 * Before calling this method you have to open the page using the addTOCPage() method.
	 * After calling this method you have to call endTOCPage() to close the TOC page.
	 * @param $page (int) page number where this TOC should be inserted (leave empty for current page).
	 * @param $toc_name (string) name to use for TOC bookmark.
	 * @param $templates (array) array of html templates. Use: "#TOC_DESCRIPTION#" for bookmark title, "#TOC_PAGE_NUMBER#" for page number.
	 * @param $correct_align (boolean) if true correct the number alignment (numbers must be in monospaced font like courier and right aligned on LTR, or left aligned on RTL)
	 * @param $style (string) Font style for title: B = Bold, I = Italic, BI = Bold + Italic.
	 * @param $color (array) RGB color array for title (values from 0 to 255).
	 * @public
	 * @author Nicola Asuni
	 * @since 5.0.001 (2010-05-06)
	 * @see addTOCPage(), endTOCPage(), addTOC()
	 */
	public function addHTMLTOC($page='', $toc_name='TOC', $templates=array(), $correct_align=true, $style='', $color=array(0,0,0)) {
		$filler = ' ';
		$prev_htmlLinkColorArray = $this->htmlLinkColorArray;
		$prev_htmlLinkFontStyle = $this->htmlLinkFontStyle;
		// set new style for link
		$this->htmlLinkColorArray = array();
		$this->htmlLinkFontStyle = '';
		$page_first = $this->getPage();
		$page_fill_start = false;
		$page_fill_end = false;
		// get the font type used for numbers in each template
		$current_font = $this->FontFamily;
		foreach ($templates as $level => $html) {
			$dom = $this->getHtmlDomArray($html);
			foreach ($dom as $key => $value) {
				if ($value['value'] == '#TOC_PAGE_NUMBER#') {
					$this->SetFont($dom[($key - 1)]['fontname']);
					$templates['F'.$level] = $this->isUnicodeFont();
				}
			}
		}
		$this->SetFont($current_font);
		$maxpage = 0; //used for pages on attached documents
		foreach ($this->outlines as $key => $outline) {
			// get HTML template
			$row = $templates[$outline['l']];
			if (TCPDF_STATIC::empty_string($page)) {
				$pagenum = $outline['p'];
			} else {
				// placemark to be replaced with the correct number
				$pagenum = '{#'.($outline['p']).'}';
				if (isset($templates['F'.$outline['l']]) && $templates['F'.$outline['l']]) {
					$pagenum = '{'.$pagenum.'}';
				}
				$maxpage = max($maxpage, $outline['p']);
			}
			// replace templates with current values
			$row = str_replace('#TOC_DESCRIPTION#', $outline['t'], $row);
			$row = str_replace('#TOC_PAGE_NUMBER#', $pagenum, $row);
			// add link to page
			$row = '<a href="#'.$outline['p'].','.$outline['y'].'">'.$row.'</a>';
			// write bookmark entry
			$this->writeHTML($row, false, false, true, false, '');
		}
		// restore link styles
		$this->htmlLinkColorArray = $prev_htmlLinkColorArray;
		$this->htmlLinkFontStyle = $prev_htmlLinkFontStyle;
		// move TOC page and replace numbers
		$page_last = $this->getPage();
		$numpages = ($page_last - $page_first + 1);
		// account for booklet mode
		if ($this->booklet) {
			// check if a blank page is required before TOC
			$page_fill_start = ((($page_first % 2) == 0) XOR (($page % 2) == 0));
			$page_fill_end = (!((($numpages % 2) == 0) XOR ($page_fill_start)));
			if ($page_fill_start) {
				// add a page at the end (to be moved before TOC)
				$this->addPage();
				++$page_last;
				++$numpages;
			}
			if ($page_fill_end) {
				// add a page at the end
				$this->addPage();
				++$page_last;
				++$numpages;
			}
		}
		$maxpage = max($maxpage, $page_last);
		if (!TCPDF_STATIC::empty_string($page)) {
			for ($p = $page_first; $p <= $page_last; ++$p) {
				// get page data
				$temppage = $this->getPageBuffer($p);
				for ($n = 1; $n <= $maxpage; ++$n) {
					// update page numbers
					$a = '{#'.$n.'}';
					// get page number aliases
					$pnalias = $this->getInternalPageNumberAliases($a);
					// calculate replacement number
					if ($n >= $page) {
						$np = $n + $numpages;
					} else {
						$np = $n;
					}
					$na = TCPDF_STATIC::formatTOCPageNumber(($this->starting_page_number + $np - 1));
					$nu = TCPDF_FONTS::UTF8ToUTF16BE($na, false, $this->isunicode, $this->CurrentFont);
					// replace aliases with numbers
					foreach ($pnalias['u'] as $u) {
						if ($correct_align) {
							$sfill = str_repeat($filler, (strlen($u) - strlen($nu.' ')));
							if ($this->rtl) {
								$nr = $nu.TCPDF_FONTS::UTF8ToUTF16BE(' '.$sfill, false, $this->isunicode, $this->CurrentFont);
							} else {
								$nr = TCPDF_FONTS::UTF8ToUTF16BE($sfill.' ', false, $this->isunicode, $this->CurrentFont).$nu;
							}
						} else {
							$nr = $nu;
						}
						$temppage = str_replace($u, $nr, $temppage);
					}
					foreach ($pnalias['a'] as $a) {
						if ($correct_align) {
							$sfill = str_repeat($filler, (strlen($a) - strlen($na.' ')));
							if ($this->rtl) {
								$nr = $na.' '.$sfill;
							} else {
								$nr = $sfill.' '.$na;
							}
						} else {
							$nr = $na;
						}
						$temppage = str_replace($a, $nr, $temppage);
					}
				}
				// save changes
				$this->setPageBuffer($p, $temppage);
			}
			// move pages
			$this->Bookmark($toc_name, 0, 0, $page_first, $style, $color);
			if ($page_fill_start) {
				$this->movePage($page_last, $page_first);
			}
			for ($i = 0; $i < $numpages; ++$i) {
				$this->movePage($page_last, $page);
			}
		}
	}

	/**
	 * Stores a copy of the current TCPDF object used for undo operation.
	 * @public
	 * @since 4.5.029 (2009-03-19)
	 */
	public function startTransaction() {
		if (isset($this->objcopy)) {
			// remove previous copy
			$this->commitTransaction();
		}
		// record current page number and Y position
		$this->start_transaction_page = $this->page;
		$this->start_transaction_y = $this->y;
		// clone current object
		$this->objcopy = TCPDF_STATIC::objclone($this);
	}

	/**
	 * Delete the copy of the current TCPDF object used for undo operation.
	 * @public
	 * @since 4.5.029 (2009-03-19)
	 */
	public function commitTransaction() {
		if (isset($this->objcopy)) {
			$this->objcopy->_destroy(true, true);
			unset($this->objcopy);
		}
	}

	/**
	 * This method allows to undo the latest transaction by returning the latest saved TCPDF object with startTransaction().
	 * @param $self (boolean) if true restores current class object to previous state without the need of reassignment via the returned value.
	 * @return TCPDF object.
	 * @public
	 * @since 4.5.029 (2009-03-19)
	 */
	public function rollbackTransaction($self=false) {
		if (isset($this->objcopy)) {
			$this->_destroy(true, true);
			if ($self) {
				$objvars = get_object_vars($this->objcopy);
				foreach ($objvars as $key => $value) {
					$this->$key = $value;
				}
			}
			return $this->objcopy;
		}
		return $this;
	}

	// --- MULTI COLUMNS METHODS -----------------------

	/**
	 * Set multiple columns of the same size
	 * @param $numcols (int) number of columns (set to zero to disable columns mode)
	 * @param $width (int) column width
	 * @param $y (int) column starting Y position (leave empty for current Y position)
	 * @public
	 * @since 4.9.001 (2010-03-28)
	 */
	public function setEqualColumns($numcols=0, $width=0, $y='') {
		$this->columns = array();
		if ($numcols < 2) {
			$numcols = 0;
			$this->columns = array();
		} else {
			// maximum column width
			$maxwidth = ($this->w - $this->original_lMargin - $this->original_rMargin) / $numcols;
			if (($width == 0) OR ($width > $maxwidth)) {
				$width = $maxwidth;
			}
			if (TCPDF_STATIC::empty_string($y)) {
				$y = $this->y;
			}
			// space between columns
			$space = (($this->w - $this->original_lMargin - $this->original_rMargin - ($numcols * $width)) / ($numcols - 1));
			// fill the columns array (with, space, starting Y position)
			for ($i = 0; $i < $numcols; ++$i) {
				$this->columns[$i] = array('w' => $width, 's' => $space, 'y' => $y);
			}
		}
		$this->num_columns = $numcols;
		$this->current_column = 0;
		$this->column_start_page = $this->page;
		$this->selectColumn(0);
	}

	/**
	 * Remove columns and reset page margins.
	 * @public
	 * @since 5.9.072 (2011-04-26)
	 */
	public function resetColumns() {
		$this->lMargin = $this->original_lMargin;
		$this->rMargin = $this->original_rMargin;
		$this->setEqualColumns();
	}

	/**
	 * Set columns array.
	 * Each column is represented by an array of arrays with the following keys: (w = width, s = space between columns, y = column top position).
	 * @param $columns (array)
	 * @public
	 * @since 4.9.001 (2010-03-28)
	 */
	public function setColumnsArray($columns) {
		$this->columns = $columns;
		$this->num_columns = count($columns);
		$this->current_column = 0;
		$this->column_start_page = $this->page;
		$this->selectColumn(0);
	}

	/**
	 * Set position at a given column
	 * @param $col (int) column number (from 0 to getNumberOfColumns()-1); empty string = current column.
	 * @public
	 * @since 4.9.001 (2010-03-28)
	 */
	public function selectColumn($col='') {
		if (is_string($col)) {
			$col = $this->current_column;
		} elseif ($col >= $this->num_columns) {
			$col = 0;
		}
		$xshift = array('x' => 0, 's' => array('H' => 0, 'V' => 0), 'p' => array('L' => 0, 'T' => 0, 'R' => 0, 'B' => 0));
		$enable_thead = false;
		if ($this->num_columns > 1) {
			if ($col != $this->current_column) {
				// move Y pointer at the top of the column
				if ($this->column_start_page == $this->page) {
					$this->y = $this->columns[$col]['y'];
				} else {
					$this->y = $this->tMargin;
				}
				// Avoid to write table headers more than once
				if (($this->page > $this->maxselcol['page']) OR (($this->page == $this->maxselcol['page']) AND ($col > $this->maxselcol['column']))) {
					$enable_thead = true;
					$this->maxselcol['page'] = $this->page;
					$this->maxselcol['column'] = $col;
				}
			}
			$xshift = $this->colxshift;
			// set X position of the current column by case
			$listindent = ($this->listindentlevel * $this->listindent);
			// calculate column X position
			$colpos = 0;
			for ($i = 0; $i < $col; ++$i) {
				$colpos += ($this->columns[$i]['w'] + $this->columns[$i]['s']);
			}
			if ($this->rtl) {
				$x = $this->w - $this->original_rMargin - $colpos;
				$this->rMargin = ($this->w - $x + $listindent);
				$this->lMargin = ($x - $this->columns[$col]['w']);
				$this->x = $x - $listindent;
			} else {
				$x = $this->original_lMargin + $colpos;
				$this->lMargin = ($x + $listindent);
				$this->rMargin = ($this->w - $x - $this->columns[$col]['w']);
				$this->x = $x + $listindent;
			}
			$this->columns[$col]['x'] = $x;
		}
		$this->current_column = $col;
		// fix for HTML mode
		$this->newline = true;
		// print HTML table header (if any)
		if ((!TCPDF_STATIC::empty_string($this->thead)) AND (!$this->inthead)) {
			if ($enable_thead) {
				// print table header
				$this->writeHTML($this->thead, false, false, false, false, '');
				$this->y += $xshift['s']['V'];
				// store end of header position
				if (!isset($this->columns[$col]['th'])) {
					$this->columns[$col]['th'] = array();
				}
				$this->columns[$col]['th']['\''.$this->page.'\''] = $this->y;
				$this->lasth = 0;
			} elseif (isset($this->columns[$col]['th']['\''.$this->page.'\''])) {
				$this->y = $this->columns[$col]['th']['\''.$this->page.'\''];
			}
		}
		// account for an html table cell over multiple columns
		if ($this->rtl) {
			$this->rMargin += $xshift['x'];
			$this->x -= ($xshift['x'] + $xshift['p']['R']);
		} else {
			$this->lMargin += $xshift['x'];
			$this->x += $xshift['x'] + $xshift['p']['L'];
		}
	}

	/**
	 * Return the current column number
	 * @return int current column number
	 * @public
	 * @since 5.5.011 (2010-07-08)
	 */
	public function getColumn() {
		return $this->current_column;
	}

	/**
	 * Return the current number of columns.
	 * @return int number of columns
	 * @public
	 * @since 5.8.018 (2010-08-25)
	 */
	public function getNumberOfColumns() {
		return $this->num_columns;
	}

	/**
	 * Set Text rendering mode.
	 * @param $stroke (int) outline size in user units (0 = disable).
	 * @param $fill (boolean) if true fills the text (default).
	 * @param $clip (boolean) if true activate clipping mode
	 * @public
	 * @since 4.9.008 (2009-04-02)
	 */
	public function setTextRenderingMode($stroke=0, $fill=true, $clip=false) {
		// Ref.: PDF 32000-1:2008 - 9.3.6 Text Rendering Mode
		// convert text rendering parameters
		if ($stroke < 0) {
			$stroke = 0;
		}
		if ($fill === true) {
			if ($stroke > 0) {
				if ($clip === true) {
					// Fill, then stroke text and add to path for clipping
					$textrendermode = 6;
				} else {
					// Fill, then stroke text
					$textrendermode = 2;
				}
				$textstrokewidth = $stroke;
			} else {
				if ($clip === true) {
					// Fill text and add to path for clipping
					$textrendermode = 4;
				} else {
					// Fill text
					$textrendermode = 0;
				}
			}
		} else {
			if ($stroke > 0) {
				if ($clip === true) {
					// Stroke text and add to path for clipping
					$textrendermode = 5;
				} else {
					// Stroke text
					$textrendermode = 1;
				}
				$textstrokewidth = $stroke;
			} else {
				if ($clip === true) {
					// Add text to path for clipping
					$textrendermode = 7;
				} else {
					// Neither fill nor stroke text (invisible)
					$textrendermode = 3;
				}
			}
		}
		$this->textrendermode = $textrendermode;
		$this->textstrokewidth = $stroke;
	}

	/**
	 * Set parameters for drop shadow effect for text.
	 * @param $params (array) Array of parameters: enabled (boolean) set to true to enable shadow; depth_w (float) shadow width in user units; depth_h (float) shadow height in user units; color (array) shadow color or false to use the stroke color; opacity (float) Alpha value: real value from 0 (transparent) to 1 (opaque); blend_mode (string) blend mode, one of the following: Normal, Multiply, Screen, Overlay, Darken, Lighten, ColorDodge, ColorBurn, HardLight, SoftLight, Difference, Exclusion, Hue, Saturation, Color, Luminosity.
	 * @since 5.9.174 (2012-07-25)
	 * @public
	*/
	public function setTextShadow($params=array('enabled'=>false, 'depth_w'=>0, 'depth_h'=>0, 'color'=>false, 'opacity'=>1, 'blend_mode'=>'Normal')) {
		if (isset($params['enabled'])) {
			$this->txtshadow['enabled'] = $params['enabled']?true:false;
		} else {
			$this->txtshadow['enabled'] = false;
		}
		if (isset($params['depth_w'])) {
			$this->txtshadow['depth_w'] = floatval($params['depth_w']);
		} else {
			$this->txtshadow['depth_w'] = 0;
		}
		if (isset($params['depth_h'])) {
			$this->txtshadow['depth_h'] = floatval($params['depth_h']);
		} else {
			$this->txtshadow['depth_h'] = 0;
		}
		if (isset($params['color']) AND ($params['color'] !== false) AND is_array($params['color'])) {
			$this->txtshadow['color'] = $params['color'];
		} else {
			$this->txtshadow['color'] = $this->strokecolor;
		}
		if (isset($params['opacity'])) {
			$this->txtshadow['opacity'] = min(1, max(0, floatval($params['opacity'])));
		} else {
			$this->txtshadow['opacity'] = 1;
		}
		if (isset($params['blend_mode']) AND in_array($params['blend_mode'], array('Normal', 'Multiply', 'Screen', 'Overlay', 'Darken', 'Lighten', 'ColorDodge', 'ColorBurn', 'HardLight', 'SoftLight', 'Difference', 'Exclusion', 'Hue', 'Saturation', 'Color', 'Luminosity'))) {
			$this->txtshadow['blend_mode'] = $params['blend_mode'];
		} else {
			$this->txtshadow['blend_mode'] = 'Normal';
		}
		if ((($this->txtshadow['depth_w'] == 0) AND ($this->txtshadow['depth_h'] == 0)) OR ($this->txtshadow['opacity'] == 0)) {
			$this->txtshadow['enabled'] = false;
		}
	}

	/**
	 * Return the text shadow parameters array.
	 * @return Array of parameters.
	 * @since 5.9.174 (2012-07-25)
	 * @public
	 */
	public function getTextShadow() {
		return $this->txtshadow;
	}

	/**
	 * Returns an array of chars containing soft hyphens.
	 * @param $word (array) array of chars
	 * @param $patterns (array) Array of hypenation patterns.
	 * @param $dictionary (array) Array of words to be returned without applying the hyphenation algorithm.
	 * @param $leftmin (int) Minimum number of character to leave on the left of the word without applying the hyphens.
	 * @param $rightmin (int) Minimum number of character to leave on the right of the word without applying the hyphens.
	 * @param $charmin (int) Minimum word length to apply the hyphenation algorithm.
	 * @param $charmax (int) Maximum length of broken piece of word.
	 * @return array text with soft hyphens
	 * @author Nicola Asuni
	 * @since 4.9.012 (2010-04-12)
	 * @protected
	 */
	protected function hyphenateWord($word, $patterns, $dictionary=array(), $leftmin=1, $rightmin=2, $charmin=1, $charmax=8) {
		$hyphenword = array(); // hyphens positions
		$numchars = count($word);
		if ($numchars <= $charmin) {
			return $word;
		}
		$word_string = TCPDF_FONTS::UTF8ArrSubString($word, '', '', $this->isunicode);
		// some words will be returned as-is
		$pattern = '/^([a-zA-Z0-9_\.\-]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/';
		if (preg_match($pattern, $word_string) > 0) {
			// email
			return $word;
		}
		$pattern = '/(([a-zA-Z0-9\-]+\.)?)((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/';
		if (preg_match($pattern, $word_string) > 0) {
			// URL
			return $word;
		}
		if (isset($dictionary[$word_string])) {
			return TCPDF_FONTS::UTF8StringToArray($dictionary[$word_string], $this->isunicode, $this->CurrentFont);
		}
		// surround word with '_' characters
		$tmpword = array_merge(array(46), $word, array(46));
		$tmpnumchars = $numchars + 2;
		$maxpos = $tmpnumchars - 1;
		for ($pos = 0; $pos < $maxpos; ++$pos) {
			$imax = min(($tmpnumchars - $pos), $charmax);
			for ($i = 1; $i <= $imax; ++$i) {
				$subword = strtolower(TCPDF_FONTS::UTF8ArrSubString($tmpword, $pos, ($pos + $i), $this->isunicode));
				if (isset($patterns[$subword])) {
					$pattern = TCPDF_FONTS::UTF8StringToArray($patterns[$subword], $this->isunicode, $this->CurrentFont);
					$pattern_length = count($pattern);
					$digits = 1;
					for ($j = 0; $j < $pattern_length; ++$j) {
						// check if $pattern[$j] is a number = hyphenation level (only numbers from 1 to 5 are valid)
						if (($pattern[$j] >= 48) AND ($pattern[$j] <= 57)) {
							if ($j == 0) {
								$zero = $pos - 1;
							} else {
								$zero = $pos + $j - $digits;
							}
							// get hyphenation level
							$level = ($pattern[$j] - 48);
							// if two levels from two different patterns match at the same point, the higher one is selected.
							if (!isset($hyphenword[$zero]) OR ($hyphenword[$zero] < $level)) {
								$hyphenword[$zero] = $level;
							}
							++$digits;
						}
					}
				}
			}
		}
		$inserted = 0;
		$maxpos = $numchars - $rightmin;
		for ($i = $leftmin; $i <= $maxpos; ++$i) {
			// only odd levels indicate allowed hyphenation points
			if (isset($hyphenword[$i]) AND (($hyphenword[$i] % 2) != 0)) {
				// 173 = soft hyphen character
				array_splice($word, $i + $inserted, 0, 173);
				++$inserted;
			}
		}
		return $word;
	}

	/**
	 * Returns text with soft hyphens.
	 * @param $text (string) text to process
	 * @param $patterns (mixed) Array of hypenation patterns or a TEX file containing hypenation patterns. TEX patterns can be downloaded from http://www.ctan.org/tex-archive/language/hyph-utf8/tex/generic/hyph-utf8/patterns/
	 * @param $dictionary (array) Array of words to be returned without applying the hyphenation algorithm.
	 * @param $leftmin (int) Minimum number of character to leave on the left of the word without applying the hyphens.
	 * @param $rightmin (int) Minimum number of character to leave on the right of the word without applying the hyphens.
	 * @param $charmin (int) Minimum word length to apply the hyphenation algorithm.
	 * @param $charmax (int) Maximum length of broken piece of word.
	 * @return array text with soft hyphens
	 * @author Nicola Asuni
	 * @since 4.9.012 (2010-04-12)
	 * @public
	 */
	public function hyphenateText($text, $patterns, $dictionary=array(), $leftmin=1, $rightmin=2, $charmin=1, $charmax=8) {
		$text = $this->unhtmlentities($text);
		$word = array(); // last word
		$txtarr = array(); // text to be returned
		$intag = false; // true if we are inside an HTML tag
		$skip = false; // true to skip hyphenation
		if (!is_array($patterns)) {
			$patterns = TCPDF_STATIC::getHyphenPatternsFromTEX($patterns);
		}
		// get array of characters
		$unichars = TCPDF_FONTS::UTF8StringToArray($text, $this->isunicode, $this->CurrentFont);
		// for each char
		foreach ($unichars as $char) {
			if ((!$intag) AND (!$skip) AND TCPDF_FONT_DATA::$uni_type[$char] == 'L') {
				// letter character
				$word[] = $char;
			} else {
				// other type of character
				if (!TCPDF_STATIC::empty_string($word)) {
					// hypenate the word
					$txtarr = array_merge($txtarr, $this->hyphenateWord($word, $patterns, $dictionary, $leftmin, $rightmin, $charmin, $charmax));
					$word = array();
				}
				$txtarr[] = $char;
				if (chr($char) == '<') {
					// we are inside an HTML tag
					$intag = true;
				} elseif ($intag AND (chr($char) == '>')) {
					// end of HTML tag
					$intag = false;
					// check for style tag
					$expected = array(115, 116, 121, 108, 101); // = 'style'
					$current = array_slice($txtarr, -6, 5); // last 5 chars
					$compare = array_diff($expected, $current);
					if (empty($compare)) {
						// check if it is a closing tag
						$expected = array(47); // = '/'
						$current = array_slice($txtarr, -7, 1);
						$compare = array_diff($expected, $current);
						if (empty($compare)) {
							// closing style tag
							$skip = false;
						} else {
							// opening style tag
							$skip = true;
						}
					}
				}
			}
		}
		if (!TCPDF_STATIC::empty_string($word)) {
			// hypenate the word
			$txtarr = array_merge($txtarr, $this->hyphenateWord($word, $patterns, $dictionary, $leftmin, $rightmin, $charmin, $charmax));
		}
		// convert char array to string and return
		return TCPDF_FONTS::UTF8ArrSubString($txtarr, '', '', $this->isunicode);
	}

	/**
	 * Enable/disable rasterization of vector images using ImageMagick library.
	 * @param $mode (boolean) if true enable rasterization, false otherwise.
	 * @public
	 * @since 5.0.000 (2010-04-27)
	 */
	public function setRasterizeVectorImages($mode) {
		$this->rasterize_vector_images = $mode;
	}

	/**
	 * Enable or disable default option for font subsetting.
	 * @param $enable (boolean) if true enable font subsetting by default.
	 * @author Nicola Asuni
	 * @public
	 * @since 5.3.002 (2010-06-07)
	 */
	public function setFontSubsetting($enable=true) {
		if ($this->pdfa_mode) {
			$this->font_subsetting = false;
		} else {
			$this->font_subsetting = $enable ? true : false;
		}
	}

	/**
	 * Return the default option for font subsetting.
	 * @return boolean default font subsetting state.
	 * @author Nicola Asuni
	 * @public
	 * @since 5.3.002 (2010-06-07)
	 */
	public function getFontSubsetting() {
		return $this->font_subsetting;
	}

	/**
	 * Left trim the input string
	 * @param $str (string) string to trim
	 * @param $replace (string) string that replace spaces.
	 * @return left trimmed string
	 * @author Nicola Asuni
	 * @public
	 * @since 5.8.000 (2010-08-11)
	 */
	public function stringLeftTrim($str, $replace='') {
		return preg_replace('/^'.$this->re_space['p'].'+/'.$this->re_space['m'], $replace, $str);
	}

	/**
	 * Right trim the input string
	 * @param $str (string) string to trim
	 * @param $replace (string) string that replace spaces.
	 * @return right trimmed string
	 * @author Nicola Asuni
	 * @public
	 * @since 5.8.000 (2010-08-11)
	 */
	public function stringRightTrim($str, $replace='') {
		return preg_replace('/'.$this->re_space['p'].'+$/'.$this->re_space['m'], $replace, $str);
	}

	/**
	 * Trim the input string
	 * @param $str (string) string to trim
	 * @param $replace (string) string that replace spaces.
	 * @return trimmed string
	 * @author Nicola Asuni
	 * @public
	 * @since 5.8.000 (2010-08-11)
	 */
	public function stringTrim($str, $replace='') {
		$str = $this->stringLeftTrim($str, $replace);
		$str = $this->stringRightTrim($str, $replace);
		return $str;
	}

	/**
	 * Return true if the current font is unicode type.
	 * @return true for unicode font, false otherwise.
	 * @author Nicola Asuni
	 * @public
	 * @since 5.8.002 (2010-08-14)
	 */
	public function isUnicodeFont() {
		return (($this->CurrentFont['type'] == 'TrueTypeUnicode') OR ($this->CurrentFont['type'] == 'cidfont0'));
	}

	/**
	 * Return normalized font name
	 * @param $fontfamily (string) property string containing font family names
	 * @return string normalized font name
	 * @author Nicola Asuni
	 * @public
	 * @since 5.8.004 (2010-08-17)
	 */
	public function getFontFamilyName($fontfamily) {
		// remove spaces and symbols
		$fontfamily = preg_replace('/[^a-z0-9_\,]/', '', strtolower($fontfamily));
		// extract all font names
		$fontslist = preg_split('/[,]/', $fontfamily);
		// find first valid font name
		foreach ($fontslist as $font) {
			// replace font variations
			$font = preg_replace('/regular$/', '', $font);
			$font = preg_replace('/italic$/', 'I', $font);
			$font = preg_replace('/oblique$/', 'I', $font);
			$font = preg_replace('/bold([I]?)$/', 'B\\1', $font);
			// replace common family names and core fonts
			$pattern = array();
			$replacement = array();
			$pattern[] = '/^serif|^cursive|^fantasy|^timesnewroman/';
			$replacement[] = 'times';
			$pattern[] = '/^sansserif/';
			$replacement[] = 'helvetica';
			$pattern[] = '/^monospace/';
			$replacement[] = 'courier';
			$font = preg_replace($pattern, $replacement, $font);
			if (in_array(strtolower($font), $this->fontlist) OR in_array($font, $this->fontkeys)) {
				return $font;
			}
		}
		// return current font as default
		return $this->CurrentFont['fontkey'];
	}

	/**
	 * Start a new XObject Template.
	 * An XObject Template is a PDF block that is a self-contained description of any sequence of graphics objects (including path objects, text objects, and sampled images).
	 * An XObject Template may be painted multiple times, either on several pages or at several locations on the same page and produces the same results each time, subject only to the graphics state at the time it is invoked.
	 * Note: X,Y coordinates will be reset to 0,0.
	 * @param $w (int) Template width in user units (empty string or zero = page width less margins).
	 * @param $h (int) Template height in user units (empty string or zero = page height less margins).
	 * @param $group (mixed) Set transparency group. Can be a boolean value or an array specifying optional parameters: 'CS' (solour space name), 'I' (boolean flag to indicate isolated group) and 'K' (boolean flag to indicate knockout group).
	 * @return int the XObject Template ID in case of success or false in case of error.
	 * @author Nicola Asuni
	 * @public
	 * @since 5.8.017 (2010-08-24)
	 * @see endTemplate(), printTemplate()
	 */
	public function startTemplate($w=0, $h=0, $group=false) {
		if ($this->inxobj) {
			// we are already inside an XObject template
			return false;
		}
		$this->inxobj = true;
		++$this->n;
		// XObject ID
		$this->xobjid = 'XT'.$this->n;
		// object ID
		$this->xobjects[$this->xobjid] = array('n' => $this->n);
		// store current graphic state
		$this->xobjects[$this->xobjid]['gvars'] = $this->getGraphicVars();
		// initialize data
		$this->xobjects[$this->xobjid]['intmrk'] = 0;
		$this->xobjects[$this->xobjid]['transfmrk'] = array();
		$this->xobjects[$this->xobjid]['outdata'] = '';
		$this->xobjects[$this->xobjid]['xobjects'] = array();
		$this->xobjects[$this->xobjid]['images'] = array();
		$this->xobjects[$this->xobjid]['fonts'] = array();
		$this->xobjects[$this->xobjid]['annotations'] = array();
		$this->xobjects[$this->xobjid]['extgstates'] = array();
		$this->xobjects[$this->xobjid]['gradients'] = array();
		$this->xobjects[$this->xobjid]['spot_colors'] = array();
		// set new environment
		$this->num_columns = 1;
		$this->current_column = 0;
		$this->SetAutoPageBreak(false);
		if (($w === '') OR ($w <= 0)) {
			$w = $this->w - $this->lMargin - $this->rMargin;
		}
		if (($h === '') OR ($h <= 0)) {
			$h = $this->h - $this->tMargin - $this->bMargin;
		}
		$this->xobjects[$this->xobjid]['x'] = 0;
		$this->xobjects[$this->xobjid]['y'] = 0;
		$this->xobjects[$this->xobjid]['w'] = $w;
		$this->xobjects[$this->xobjid]['h'] = $h;
		$this->w = $w;
		$this->h = $h;
		$this->wPt = $this->w * $this->k;
		$this->hPt = $this->h * $this->k;
		$this->fwPt = $this->wPt;
		$this->fhPt = $this->hPt;
		$this->x = 0;
		$this->y = 0;
		$this->lMargin = 0;
		$this->rMargin = 0;
		$this->tMargin = 0;
		$this->bMargin = 0;
		// set group mode
		$this->xobjects[$this->xobjid]['group'] = $group;
		return $this->xobjid;
	}

	/**
	 * End the current XObject Template started with startTemplate() and restore the previous graphic state.
	 * An XObject Template is a PDF block that is a self-contained description of any sequence of graphics objects (including path objects, text objects, and sampled images).
	 * An XObject Template may be painted multiple times, either on several pages or at several locations on the same page and produces the same results each time, subject only to the graphics state at the time it is invoked.
	 * @return int the XObject Template ID in case of success or false in case of error.
	 * @author Nicola Asuni
	 * @public
	 * @since 5.8.017 (2010-08-24)
	 * @see startTemplate(), printTemplate()
	 */
	public function endTemplate() {
		if (!$this->inxobj) {
			// we are not inside a template
			return false;
		}
		$this->inxobj = false;
		// restore previous graphic state
		$this->setGraphicVars($this->xobjects[$this->xobjid]['gvars'], true);
		return $this->xobjid;
	}

	/**
	 * Print an XObject Template.
	 * You can print an XObject Template inside the currently opened Template.
	 * An XObject Template is a PDF block that is a self-contained description of any sequence of graphics objects (including path objects, text objects, and sampled images).
	 * An XObject Template may be painted multiple times, either on several pages or at several locations on the same page and produces the same results each time, subject only to the graphics state at the time it is invoked.
	 * @param $id (string) The ID of XObject Template to print.
	 * @param $x (int) X position in user units (empty string = current x position)
	 * @param $y (int) Y position in user units (empty string = current y position)
	 * @param $w (int) Width in user units (zero = remaining page width)
	 * @param $h (int) Height in user units (zero = remaining page height)
	 * @param $align (string) Indicates the alignment of the pointer next to template insertion relative to template height. The value can be:<ul><li>T: top-right for LTR or top-left for RTL</li><li>M: middle-right for LTR or middle-left for RTL</li><li>B: bottom-right for LTR or bottom-left for RTL</li><li>N: next line</li></ul>
	 * @param $palign (string) Allows to center or align the template on the current line. Possible values are:<ul><li>L : left align</li><li>C : center</li><li>R : right align</li><li>'' : empty string : left for LTR or right for RTL</li></ul>
	 * @param $fitonpage (boolean) If true the template is resized to not exceed page dimensions.
	 * @author Nicola Asuni
	 * @public
	 * @since 5.8.017 (2010-08-24)
	 * @see startTemplate(), endTemplate()
	 */
	public function printTemplate($id, $x='', $y='', $w=0, $h=0, $align='', $palign='', $fitonpage=false) {
		if ($this->state != 2) {
			 return;
		}
		if (!isset($this->xobjects[$id])) {
			$this->Error('The XObject Template \''.$id.'\' doesn\'t exist!');
		}
		if ($this->inxobj) {
			if ($id == $this->xobjid) {
				// close current template
				$this->endTemplate();
			} else {
				// use the template as resource for the template currently opened
				$this->xobjects[$this->xobjid]['xobjects'][$id] = $this->xobjects[$id];
			}
		}
		// set default values
		if ($x === '') {
			$x = $this->x;
		}
		if ($y === '') {
			$y = $this->y;
		}
		// check page for no-write regions and adapt page margins if necessary
		list($x, $y) = $this->checkPageRegions($h, $x, $y);
		$ow = $this->xobjects[$id]['w'];
		if ($ow <= 0) {
			$ow = 1;
		}
		$oh = $this->xobjects[$id]['h'];
		if ($oh <= 0) {
			$oh = 1;
		}
		// calculate template width and height on document
		if (($w <= 0) AND ($h <= 0)) {
			$w = $ow;
			$h = $oh;
		} elseif ($w <= 0) {
			$w = $h * $ow / $oh;
		} elseif ($h <= 0) {
			$h = $w * $oh / $ow;
		}
		// fit the template on available space
		list($w, $h, $x, $y) = $this->fitBlock($w, $h, $x, $y, $fitonpage);
		// set page alignment
		$rb_y = $y + $h;
		// set alignment
		if ($this->rtl) {
			if ($palign == 'L') {
				$xt = $this->lMargin;
			} elseif ($palign == 'C') {
				$xt = ($this->w + $this->lMargin - $this->rMargin - $w) / 2;
			} elseif ($palign == 'R') {
				$xt = $this->w - $this->rMargin - $w;
			} else {
				$xt = $x - $w;
			}
			$rb_x = $xt;
		} else {
			if ($palign == 'L') {
				$xt = $this->lMargin;
			} elseif ($palign == 'C') {
				$xt = ($this->w + $this->lMargin - $this->rMargin - $w) / 2;
			} elseif ($palign == 'R') {
				$xt = $this->w - $this->rMargin - $w;
			} else {
				$xt = $x;
			}
			$rb_x = $xt + $w;
		}
		// print XObject Template + Transformation matrix
		$this->StartTransform();
		// translate and scale
		$sx = ($w / $ow);
		$sy = ($h / $oh);
		$tm = array();
		$tm[0] = $sx;
		$tm[1] = 0;
		$tm[2] = 0;
		$tm[3] = $sy;
		$tm[4] = $xt * $this->k;
		$tm[5] = ($this->h - $h - $y) * $this->k;
		$this->Transform($tm);
		// set object
		$this->_out('/'.$id.' Do');
		$this->StopTransform();
		// add annotations
		if (!empty($this->xobjects[$id]['annotations'])) {
			foreach ($this->xobjects[$id]['annotations'] as $annot) {
				// transform original coordinates
				$coordlt = TCPDF_STATIC::getTransformationMatrixProduct($tm, array(1, 0, 0, 1, ($annot['x'] * $this->k), (-$annot['y'] * $this->k)));
				$ax = ($coordlt[4] / $this->k);
				$ay = ($this->h - $h - ($coordlt[5] / $this->k));
				$coordrb = TCPDF_STATIC::getTransformationMatrixProduct($tm, array(1, 0, 0, 1, (($annot['x'] + $annot['w']) * $this->k), ((-$annot['y'] - $annot['h']) * $this->k)));
				$aw = ($coordrb[4] / $this->k) - $ax;
				$ah = ($this->h - $h - ($coordrb[5] / $this->k)) - $ay;
				$this->Annotation($ax, $ay, $aw, $ah, $annot['text'], $annot['opt'], $annot['spaces']);
			}
		}
		// set pointer to align the next text/objects
		switch($align) {
			case 'T': {
				$this->y = $y;
				$this->x = $rb_x;
				break;
			}
			case 'M': {
				$this->y = $y + round($h/2);
				$this->x = $rb_x;
				break;
			}
			case 'B': {
				$this->y = $rb_y;
				$this->x = $rb_x;
				break;
			}
			case 'N': {
				$this->SetY($rb_y);
				break;
			}
			default:{
				break;
			}
		}
	}

	/**
	 * Set the percentage of character stretching.
	 * @param $perc (int) percentage of stretching (100 = no stretching)
	 * @author Nicola Asuni
	 * @public
	 * @since 5.9.000 (2010-09-29)
	 */
	public function setFontStretching($perc=100) {
		$this->font_stretching = $perc;
	}

	/**
	 * Get the percentage of character stretching.
	 * @return float stretching value
	 * @author Nicola Asuni
	 * @public
	 * @since 5.9.000 (2010-09-29)
	 */
	public function getFontStretching() {
		return $this->font_stretching;
	}

	/**
	 * Set the amount to increase or decrease the space between characters in a text.
	 * @param $spacing (float) amount to increase or decrease the space between characters in a text (0 = default spacing)
	 * @author Nicola Asuni
	 * @public
	 * @since 5.9.000 (2010-09-29)
	 */
	public function setFontSpacing($spacing=0) {
		$this->font_spacing = $spacing;
	}

	/**
	 * Get the amount to increase or decrease the space between characters in a text.
	 * @return int font spacing (tracking) value
	 * @author Nicola Asuni
	 * @public
	 * @since 5.9.000 (2010-09-29)
	 */
	public function getFontSpacing() {
		return $this->font_spacing;
	}

	/**
	 * Return an array of no-write page regions
	 * @return array of no-write page regions
	 * @author Nicola Asuni
	 * @public
	 * @since 5.9.003 (2010-10-13)
	 * @see setPageRegions(), addPageRegion()
	 */
	public function getPageRegions() {
		return $this->page_regions;
	}

	/**
	 * Set no-write regions on page.
	 * A no-write region is a portion of the page with a rectangular or trapezium shape that will not be covered when writing text or html code.
	 * A region is always aligned on the left or right side of the page ad is defined using a vertical segment.
	 * You can set multiple regions for the same page.
	 * @param $regions (array) array of no-write regions. For each region you can define an array as follow: ('page' => page number or empy for current page, 'xt' => X top, 'yt' => Y top, 'xb' => X bottom, 'yb' => Y bottom, 'side' => page side 'L' = left or 'R' = right). Omit this parameter to remove all regions.
	 * @author Nicola Asuni
	 * @public
	 * @since 5.9.003 (2010-10-13)
	 * @see addPageRegion(), getPageRegions()
	 */
	public function setPageRegions($regions=array()) {
		// empty current regions array
		$this->page_regions = array();
		// add regions
		foreach ($regions as $data) {
			$this->addPageRegion($data);
		}
	}

	/**
	 * Add a single no-write region on selected page.
	 * A no-write region is a portion of the page with a rectangular or trapezium shape that will not be covered when writing text or html code.
	 * A region is always aligned on the left or right side of the page ad is defined using a vertical segment.
	 * You can set multiple regions for the same page.
	 * @param $region (array) array of a single no-write region array: ('page' => page number or empy for current page, 'xt' => X top, 'yt' => Y top, 'xb' => X bottom, 'yb' => Y bottom, 'side' => page side 'L' = left or 'R' = right).
	 * @author Nicola Asuni
	 * @public
	 * @since 5.9.003 (2010-10-13)
	 * @see setPageRegions(), getPageRegions()
	 */
	public function addPageRegion($region) {
		if (!isset($region['page']) OR empty($region['page'])) {
			$region['page'] = $this->page;
		}
		if (isset($region['xt']) AND isset($region['xb']) AND ($region['xt'] > 0) AND ($region['xb'] > 0)
			AND isset($region['yt'])  AND isset($region['yb']) AND ($region['yt'] >= 0) AND ($region['yt'] < $region['yb'])
			AND isset($region['side']) AND (($region['side'] == 'L') OR ($region['side'] == 'R'))) {
			$this->page_regions[] = $region;
		}
	}

	/**
	 * Remove a single no-write region.
	 * @param $key (int) region key
	 * @author Nicola Asuni
	 * @public
	 * @since 5.9.003 (2010-10-13)
	 * @see setPageRegions(), getPageRegions()
	 */
	public function removePageRegion($key) {
		if (isset($this->page_regions[$key])) {
			unset($this->page_regions[$key]);
		}
	}

	/**
	 * Check page for no-write regions and adapt current coordinates and page margins if necessary.
	 * A no-write region is a portion of the page with a rectangular or trapezium shape that will not be covered when writing text or html code.
	 * A region is always aligned on the left or right side of the page ad is defined using a vertical segment.
	 * @param $h (float) height of the text/image/object to print in user units
	 * @param $x (float) current X coordinate in user units
	 * @param $y (float) current Y coordinate in user units
	 * @return array($x, $y)
	 * @author Nicola Asuni
	 * @protected
	 * @since 5.9.003 (2010-10-13)
	 */
	protected function checkPageRegions($h, $x, $y) {
		// set default values
		if ($x === '') {
			$x = $this->x;
		}
		if ($y === '') {
			$y = $this->y;
		}
		if (!$this->check_page_regions OR empty($this->page_regions)) {
			// no page regions defined
			return array($x, $y);
		}
		if (empty($h)) {
			$h = $this->getCellHeight($this->FontSize);
		}
		// check for page break
		if ($this->checkPageBreak($h, $y)) {
			// the content will be printed on a new page
			$x = $this->x;
			$y = $this->y;
		}
		if ($this->num_columns > 1) {
			if ($this->rtl) {
				$this->lMargin = ($this->columns[$this->current_column]['x'] - $this->columns[$this->current_column]['w']);
			} else {
				$this->rMargin = ($this->w - $this->columns[$this->current_column]['x'] - $this->columns[$this->current_column]['w']);
			}
		} else {
			if ($this->rtl) {
				$this->lMargin = max($this->clMargin, $this->original_lMargin);
			} else {
				$this->rMargin = max($this->crMargin, $this->original_rMargin);
			}
		}
		// adjust coordinates and page margins
		foreach ($this->page_regions as $regid => $regdata) {
			if ($regdata['page'] == $this->page) {
				// check region boundaries
				if (($y > ($regdata['yt'] - $h)) AND ($y <= $regdata['yb'])) {
					// Y is inside the region
					$minv = ($regdata['xb'] - $regdata['xt']) / ($regdata['yb'] - $regdata['yt']); // inverse of angular coefficient
					$yt = max($y, $regdata['yt']);
					$yb = min(($yt + $h), $regdata['yb']);
					$xt = (($yt - $regdata['yt']) * $minv) + $regdata['xt'];
					$xb = (($yb - $regdata['yt']) * $minv) + $regdata['xt'];
					if ($regdata['side'] == 'L') { // left side
						$new_margin = max($xt, $xb);
						if ($this->lMargin < $new_margin) {
							if ($this->rtl) {
								// adjust left page margin
								$this->lMargin = max(0, $new_margin);
							}
							if ($x < $new_margin) {
								// adjust x position
								$x = $new_margin;
								if ($new_margin > ($this->w - $this->rMargin)) {
									// adjust y position
									$y = $regdata['yb'] - $h;
								}
							}
						}
					} elseif ($regdata['side'] == 'R') { // right side
						$new_margin = min($xt, $xb);
						if (($this->w - $this->rMargin) > $new_margin) {
							if (!$this->rtl) {
								// adjust right page margin
								$this->rMargin = max(0, ($this->w - $new_margin));
							}
							if ($x > $new_margin) {
								// adjust x position
								$x = $new_margin;
								if ($new_margin > $this->lMargin) {
									// adjust y position
									$y = $regdata['yb'] - $h;
								}
							}
						}
					}
				}
			}
		}
		return array($x, $y);
	}

} // END OF TCPDF CLASS

//============================================================+
// END OF FILE
//============================================================+
