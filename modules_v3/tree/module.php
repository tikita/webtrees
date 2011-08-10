<?php
// TreeView module class
//
// Tip : you could change the number of generations loaded before ajax calls both in individual page and in treeview page to optimize speed and server load 
//
// Copyright (C) 2011 webtrees development team
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
//
// $Id$

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class tree_WT_Module extends WT_Module implements WT_Module_Tab {	
	var $headers; // CSS and script to include in the top of <head> section, before theme's CSS
	var $style; // the name of the active style, or false
	var $css; // a customized CSS to load AFTER theme's CSS if $style is defined
	var $js; // the TreeViewHandler javascript
	
	function __construct() {
		// define the module inclusions for the page header
  	$this->headers = '<link rel="stylesheet" type="text/css" href="'.WT_MODULES_DIR.$this->getName().'/css/treeview.css" />';
  	$this->js = '<script type="text/javascript" language="javascript" src="'.WT_MODULES_DIR.$this->getName().'/js/treeview.js"></script>';
  	
		// Retrieve the user's personalized style
    if (isset($_COOKIE['tvStyle'])) {
    	$this->style = $_COOKIE['tvStyle'];
    	$this->css = '<link id="tvCSS" rel="stylesheet" type="text/css" href="'.WT_MODULES_DIR.$this->getName().'/css/styles/'.$this->style.'/'.$this->style.'.css" />';
    }
    else {
    	$this->style = false;
    	$this->css = '';
    }
    $this->css .= '<link rel="stylesheet" type="text/css" href="'.WT_MODULES_DIR.$this->getName().'/css/treeview_print.css" media="print" />';
	}
	
	// Extend WT_Module. This title should be normalized when this module will be added officially
	public function getTitle() {
		return /* I18N: Name of a module */ WT_I18N::translate('Interactive tree');
	}

	// Extend WT_Module
	public function getDescription() {
		return /* I18N: Description of the "Interactive tree" module */ WT_I18N::translate('An interactive tree, showing all the ancestors and descendants of a person.');
	}
	
	// Implement WT_Module_Tab
	public function defaultTabOrder() {
		return 68;
	}

	// Implement WT_Module_Tab
	public function getJSCallback() {
		return '';
}

	// Implement WT_Module_Tab
	public function getTabContent() {
		require_once WT_MODULES_DIR.$this->getName().'/class_treeview.php';
    $tv = new TreeView('tvTab');
    $r = $tv->drawViewport($this->controller->pid, 3, $this->style);
		return $r;
	}

	// Implement WT_Module_Tab
	public function hasTabContent() {
		return true;
	}
	// Implement WT_Module_Tab
	public function isGrayedOut() {
		return false;
	}
	// Implement WT_Module_Tab
	public function canLoadAjax() {
		return true;
	}

	// Implement WT_Module_Tab
	public function getPreLoadContent() {
		// a workaround to the lack of a proper method of class Module to insert css and scripts in <head> where needed
		// the required loading order is : headers, theme, css
	  return $this->js.$this->headers;
	}

  // Extend WT_Module
  // We define here actions to proceed when called, either by Ajax or not
  public function modAction($mod_action) {  
		require_once WT_MODULES_DIR.$this->getName().'/class_treeview.php';
    switch($mod_action) {
      case 'treeview':
        $tvName = 'tv';
        $rootid = safe_GET('rootid');
				$rootid = check_rootid($rootid);
        $tv = new TreeView('tv');
				ob_start();
				$person=WT_Person::getInstance($rootid);
	       print_header(WT_I18N::translate('Interactive tree of %s', $person->getFullName()));
        if (WT_USE_LIGHTBOX) {
        	require WT_MODULES_DIR.'lightbox/functions/lb_call_js.php';
				}
        $header = ob_get_clean();
        // we do this trick to insert headers js calls and css
        // should be improved as soon as webtrees will have provided a way to do that
        // in a more proper way (a method of Module object to insert its css and scripts calls in the <head>)
        // The CSS inserted here could be overided by cascading style sheet mechanism
        // when relevant properties are defined in the theme's CSS
        $header = str_replace('<head>', '<head>'.$this->headers, $header);
        // If TreeView was personalized, include the custom CSS at the end of the header, to "cascade" previously loaded CSS
       	$header = str_replace('</head>', $this->js.$this->css.'</head>', $header);
        echo $header;
        echo $tv->drawViewport($rootid, 4, $this->style);
        print_footer();
        break;

      case 'getDetails':
				header('Content-Type: text/html; charset=UTF-8');
        $pid = safe_GET('pid');
        $i = safe_GET('instance');
        $tv = new TreeView($i);
        echo $tv->getDetails($pid);
        break;

      case 'getPersons':
        $q = $_REQUEST["q"];
        $i = safe_GET('instance');
        $tv = new TreeView($i);
        echo $tv->getPersons($q);
        break;

			// dynamically load full medias instead of thumbnails for opened boxes before printing
      case 'getMedias':
        $q = $_REQUEST["q"];
        $i = safe_GET('instance');
        $tv = new TreeView($i);
        echo $tv->getMedias($q);
      	break;

      default:
				header('HTTP/1.0 404 Not Found');
    }
  }
}