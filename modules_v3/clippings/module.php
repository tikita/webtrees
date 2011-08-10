<?php
// Classes and libraries for module system
//
// webtrees: Web based Family History software
// Copyright (C) 2011 webtrees development team.
//
// Derived from PhpGedView
// Copyright (C) 2010 John Finlay
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

require_once WT_ROOT.WT_MODULES_DIR.'clippings/clippings_ctrl.php';

class clippings_WT_Module extends WT_Module implements WT_Module_Menu, WT_Module_Sidebar {
	// Extend class WT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ WT_I18N::translate('Clippings cart');
	}

	// Extend class WT_Module
	public function getDescription() {
		return /* I18N: Description of the "Clippings cart" module */ WT_I18N::translate('Select records from your family tree and save them as a GEDCOM file.');
	}

	// Extend class WT_Module
	public function defaultAccessLevel() {
		return WT_PRIV_USER;
	}

	// Extend WT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'index':
			require_once WT_ROOT.'includes/functions/functions_export.php';
			// TODO: these files should be methods in this class
			require WT_ROOT.WT_MODULES_DIR.$this->getName().'/'.$mod_action.'.php';
			break;
		default:
			header('HTTP/1.0 404 Not Found');
		}
	}

	// Implement WT_Module_Menu
	public function defaultMenuOrder() {
		return 20;
	}

	// Implement WT_Module_Menu
	public function getMenu() {
		global $SEARCH_SPIDER, $controller;

		if ($SEARCH_SPIDER) {
			return null;
		}
		//-- main clippings menu item
		$menu = new WT_Menu($this->getTitle(), 'module.php?mod=clippings&amp;mod_action=index&amp;ged='.WT_GEDURL, 'menu-clippings', 'down');
		$menu->addIcon('clippings');
		$menu->addClass('menuitem', 'menuitem_hover', 'submenu', 'icon_large_clippings');
		$submenu = new WT_Menu($this->getTitle(), 'module.php?mod=clippings&amp;mod_action=index&amp;ged='.WT_GEDURL, 'menu-clippingscart');
		$submenu->addIcon('clippings');
		$submenu->addClass('submenuitem', 'submenuitem_hover', 'submenu', 'icon_small_clippings');
		$menu->addSubmenu($submenu);
		if (isset($controller->indi) && $controller->indi->canDisplayDetails()) {
			$submenu = new WT_Menu(WT_I18N::translate('Add to clippings cart'), "module.php?mod=clippings&amp;mod_action=index&amp;action=add&amp;id={$controller->pid}&amp;type=indi", 'menu-clippingsadd');
			$submenu->addIcon('clippings');
			$submenu->addClass('submenuitem', 'submenuitem_hover', 'submenu', 'icon_small_add_clip');

			$menu->addSubmenu($submenu);
		} elseif (isset($controller->family) && $controller->family->canDisplayDetails()) {
			$submenu = new WT_Menu(WT_I18N::translate('Add to clippings cart'), "module.php?mod=clippings&amp;mod_action=index&amp;action=add&amp;id={$controller->famid}&amp;type=fam", 'menu-clippingsadd');
			$submenu->addIcon('clippings');
			$submenu->addClass('submenuitem', 'submenuitem_hover', 'submenu', 'icon_small_add_clip');
			$menu->addSubmenu($submenu);
		} elseif (isset($controller->mediaobject) && $controller->mediaobject->canDisplayDetails()) {
			$submenu = new WT_Menu(WT_I18N::translate('Add to clippings cart'), "module.php?mod=clippings&amp;mod_action=index&amp;action=add&amp;id={$controller->mid}&amp;type=obje", 'menu-clippingsadd');
			$submenu->addIcon('clippings');
			$submenu->addClass('submenuitem', 'submenuitem_hover', 'submenu', 'icon_small_add_clip');
			$menu->addSubmenu($submenu);
		} elseif (isset($controller->source) && $controller->source->canDisplayDetails()) {
			$submenu = new WT_Menu(WT_I18N::translate('Add to clippings cart'), "module.php?mod=clippings&amp;mod_action=index&amp;action=add&amp;id={$controller->sid}&amp;type=sour", 'menu-clippingsadd');
			$submenu->addIcon('clippings');
			$submenu->addClass('submenuitem', 'submenuitem_hover', 'submenu', 'icon_small_add_clip');
			$menu->addSubmenu($submenu);
		} elseif (isset($controller->note) && $controller->note->canDisplayDetails()) {
			$submenu = new WT_Menu(WT_I18N::translate('Add to clippings cart'), "module.php?mod=clippings&amp;mod_action=index&amp;action=add&amp;id={$controller->nid}&amp;type=note", 'menu-clippingsadd');
			$submenu->addIcon('clippings');
			$submenu->addClass('submenuitem', 'submenuitem_hover', 'submenu', 'icon_small_add_clip');
			$menu->addSubmenu($submenu);
		} elseif (isset($controller->repository) && $controller->repository->canDisplayDetails()) {
			$submenu = new WT_Menu(WT_I18N::translate('Add to clippings cart'), "module.php?mod=clippings&mod_action=index&action=add&id={$controller->rid}&type=repo", 'menu-clippingsadd');
			$submenu->addIcon('clippings');
			$submenu->addClass('submenuitem', 'submenuitem_hover', 'submenu', 'icon_small_add_clip');
			$menu->addSubmenu($submenu);
		}
		return $menu;
	}

	// Implement WT_Module_Sidebar
	public function defaultSidebarOrder() {
		return 60;
	}

	// Impelement WT_Module_Sidebar
	public function hasSidebarContent() {
		return true;
	}

	// Impelement WT_Module_Sidebar
	public function getSidebarContent() {
		global $WT_IMAGES, $cart;

		$out = '';

		if ($this->controller) {
			$out .= '<script type="text/javascript">
			<!--
			jQuery(document).ready(function() {
				jQuery(".add_cart, .remove_cart").live("click", function() {
					jQuery("#sb_clippings_content").load(this.href);
					return false;
				});
			});
			//-->
			</script>
			<div id="sb_clippings_content">';
			$out .= $this->getCartList();
			$root = null;
			if ($this->controller->pid && !WT_Controller_Clippings::id_in_cart($this->controller->pid)) {
				$root = WT_GedcomRecord::getInstance($this->controller->pid);
				if ($root && $root->canDisplayDetails())
					$out .= '<a href="sidebar.php?sb_action=clippings&amp;add='.$root->getXref().'" class="add_cart">
					<img src="'.$WT_IMAGES['clippings'].'" width="20" /> '.WT_I18N::translate('Add %s to cart', $root->getListName()).'</a>';
			}
			$out .= '</div>';
		}
		return $out;
	}

	// Impelement WT_Module_Sidebar
	public function getSidebarAjaxContent() {
		global $cart;
		$controller = new WT_Controller_Clippings();
		$this->controller = $controller;
		$add = safe_GET_xref('add','');
		$add1 = safe_GET_xref('add1','');
		$remove = safe_GET('remove', WT_REGEX_INTEGER, -1);
		$others = safe_GET('others', WT_REGEX_ALPHANUM, '');
		$controller->level1 = safe_GET('level1');
		$controller->level2 = safe_GET('level2');
		$controller->level3 = safe_GET('level3');
		if (!empty($add)) {
			$record = WT_GedcomRecord::getInstance($add);
			if ($record) {
				$controller->id=$record->getXref();
				$controller->type=$record->getType();
				$ret = $controller->add_clipping($record);
				if (isset($_SESSION["cart"])) $_SESSION["cart"]=$cart;
				if ($ret) return $this->askAddOptions($record);
			}
		} elseif (!empty($add1)) {
			$record = WT_Person::getInstance($add1);
			if ($record) {
				$controller->id=$record->getXref();
				$controller->type=strtolower($record->getType());
				if ($others == 'parents') {
					foreach ($record->getChildFamilies() as $family) {
						$controller->add_clipping($family);
						$controller->add_family_members($family);
					}
				} elseif ($others == 'ancestors') {
					$controller->add_ancestors_to_cart($record, $controller->level1);
				} elseif ($others == 'ancestorsfamilies') {
					$controller->add_ancestors_to_cart_families($record, $controller->level2);
				} elseif ($others == 'members') {
					foreach ($record->getSpouseFamilies() as $family) {
						$controller->add_clipping($family);
						$controller->add_family_members($family);
					}
				} elseif ($others == 'descendants') {
					foreach ($record->getSpouseFamilies() as $family) {
						$controller->add_clipping($family);
						$controller->add_family_descendancy($family, $controller->level3);
					}
				}
			}
		}
		else if ($remove!=-1) {
			$ct = count($cart);
			for ($i = $remove +1; $i < $ct; $i++) {
				$cart[$i -1] = $cart[$i];
			}
			unset ($cart[$ct -1]);
		}
		else if (isset($_REQUEST['empty'])) {
			$cart = array ();
			$_SESSION["cart"] = $cart;
		}
		else if (isset($_REQUEST['download'])) {
			return $this->downloadForm();
		}
		if (isset($_SESSION["cart"])) $_SESSION["cart"]=$cart;
		return $this->getCartList();
	}

	public function getCartList() {
		global $WT_IMAGES, $cart;

		$out ='<ul>';
		$ct = count($cart);
		if ($ct==0) $out .= '<br /><br />'.WT_I18N::translate('Your Clippings Cart is empty.').'<br /><br />';
		else {
			for ($i=0; $i<$ct; $i++) {
				$clipping = $cart[$i];
				$tag = strtoupper(substr($clipping['type'], 0, 4)); // source => SOUR
				//print_r($clipping);
				//-- don't show clippings from other gedcoms
				if ($clipping['gedcom']==WT_GEDCOM) {
					$icon='';
					if ($tag=='INDI') $icon = "indis";
					if ($tag=='FAM' ) $icon = "sfamily";
					//if ($tag=='SOUR') $icon = "source";
					//if ($tag=='REPO') $icon = "repository";
					//if ($tag=='NOTE') $icon = "notes";
					//if ($tag=='OBJE') $icon = "media";
					if (!empty($icon)) {
						$out .= '<li>';
						if (!empty($icon)) {
							$out .= '<img src="'.$WT_IMAGES[$icon].'" border="0" alt="'.$tag.'" title="'.$tag.'" width="20" />';
						}
						$record=WT_GedcomRecord::getInstance($clipping['id']);
						if ($record) {
							$out .= '<a href="'.$record->getHtmlUrl().'">';
							if ($record->getType()=="INDI") $out .=$record->getSexImage();
							$out .= ' '.$record->getFullName().' ';
							if ($record->getType()=="INDI" && $record->canDisplayDetails()) {
								$out .= PrintReady(' ('.$record->getLifeSpan().')');
							}
							$out .= '</a>';
						}
						$out .= '<a class="remove_cart" href="sidebar.php?sb_action=clippings&amp;remove='.$i.'">
						<img src="'. $WT_IMAGES["remove"].'" border="0" alt="'.WT_I18N::translate('Remove').'" title="'.WT_I18N::translate('Remove').'" /></a>';
						$out .='</li>';
					}
				}
			}
		}
		$out .= '</ul>';
		if (count($cart)>0) {
			$out .= '<a href="sidebar.php?sb_action=clippings&amp;empty=true" class="remove_cart">'.WT_I18N::translate('Empty Cart').'</a>'.help_link('empty_cart', $this->getName());
			$out .= '<br /><a href="sidebar.php?sb_action=clippings&amp;download=true" class="add_cart">'.WT_I18N::translate('Download Now').'</a>';
		}
		$out .= '<br />';
		return $out;
	}
	public function askAddOptions($person) {
		global $MAX_PEDIGREE_GENERATIONS;
		$out = "<b>".$person->getFullName()."</b>";
		$out .= WT_JS_START;
		$out .= 'function radAncestors(elementid) {var radFamilies=document.getElementById(elementid);radFamilies.checked=true;}
			function continueAjax(frm) {
				var others = jQuery("input[name=\'others\']:checked").val();
				var link = "sidebar.php?sb_action=clippings&add1="+frm.pid.value+"&others="+others+"&level1="+frm.level1.value+"&level2="+frm.level2.value+"&level3="+frm.level3.value;
				jQuery("#sb_clippings_content").load(link);
			}';
		$out .= WT_JS_END;
		if ($person->getType()=='FAM') {

			$out .= '<form action="module.php" method="get" onsubmit="continueAjax(this); return false;">
			<input type="hidden" name="mod" value="clippings" />
			<input type="hidden" name="mod_action" value="index" />
			<table>
			<tr><td class="topbottombar">'.WT_I18N::translate('Which other links from this family would you like to add?').'
			<input type="hidden" name="pid" value="'.$person->getXref().'" />
			<input type="hidden" name="type" value="'.$person->getType().'" />
			<input type="hidden" name="action" value="add1" /></td></tr>
			<tr><td class="optionbox"><input type="radio" name="others" checked value="none" />'.WT_I18N::translate('Add just this family record.').'</td></tr>
			<tr><td class="optionbox"><input type="radio" name="others" value="parents" />'.WT_I18N::translate('Add parents\' records together with this family record.').'</td></tr>
			<tr><td class="optionbox"><input type="radio" name="others" value="members" />'.WT_I18N::translate('Add parents\' and children\'s records together with this family record.').'</td></tr>
			<tr><td class="optionbox"><input type="radio" name="others" value="descendants" />'.WT_I18N::translate('Add parents\' and all descendants\' records together with this family record.').'</td></tr>
			<tr><td class="topbottombar"><input type="submit" value="'.WT_I18N::translate('Continue Adding').'" /></td></tr>
			</table>
			</form>';
		}
		else if ($person->getType()=='INDI') {
			$out .= '<form action="module.php" method="get" onsubmit="continueAjax(this); return false;">
			<input type="hidden" name="mod" value="clippings" />
			<input type="hidden" name="mod_action" value="index" />
		'.WT_I18N::translate('Which links from this person would you also like to add?').'
		<input type="hidden" name="pid" value="'.$person->getXref().'" />
		<input type="hidden" name="type" value="'.$person->getType().'" />
		<input type="hidden" name="action" value="add1" />
		<ul>
		<li><input type="radio" name="others" checked value="none" />'.WT_I18N::translate('Add just this person.').'</li>
		<li><input type="radio" name="others" value="parents" />'.WT_I18N::translate('Add this person, his parents, and siblings.').'</li>
		<li><input type="radio" name="others" value="ancestors" id="ancestors" />'.WT_I18N::translate('Add this person and his direct line ancestors.').'<br />
				'.WT_I18N::translate('Number of generations:').'<input type="text" size="4" name="level1" value="'.$MAX_PEDIGREE_GENERATIONS.'" onfocus="radAncestors(\'ancestors\');"/></li>
		<li><input type="radio" name="others" value="ancestorsfamilies" id="ancestorsfamilies" />'.WT_I18N::translate('Add this person, his direct line ancestors, and their families.').'<br />
				'.WT_I18N::translate('Number of generations:').' <input type="text" size="4" name="level2" value="'. $MAX_PEDIGREE_GENERATIONS.'" onfocus="radAncestors(\'ancestorsfamilies\');" /></li>
		<li><input type="radio" name="others" value="members" />'.WT_I18N::translate('Add this person, his spouse, and children.').'</li>
		<li><input type="radio" name="others" value="descendants" id="descendants" />'.WT_I18N::translate('Add this person, his spouse, and all descendants.').'<br >
				'.WT_I18N::translate('Number of generations:').' <input type="text" size="4" name="level3" value="'.$MAX_PEDIGREE_GENERATIONS.'" onfocus="radAncestors(\'descendants\');" /></li>
		</ul>
		<input type="submit" value="'.WT_I18N::translate('Continue Adding').'" />
		</form>';
		} else if ($person->getType()=='SOUR')  {
			$out .= '<form action="module.php" method="get" onsubmit="continueAjax(this); return false;">
		<input type="hidden" name="mod" value="clippings" />
		<input type="hidden" name="mod_action" value="index" />
		<table>
		<tr><td class="topbottombar">'.WT_I18N::translate('Which records linked to this source should be added?').'
		<input type="hidden" name="pid" value="'.$person->getXref().'" />
		<input type="hidden" name="type" value="'.$person->getType().'" />
		<input type="hidden" name="action" value="add1" /></td></tr>
		<tr><td class="optionbox"><input type="radio" name="others" checked value="none" />'.WT_I18N::translate('Add just this source.').'</td></tr>
		<tr><td class="optionbox"><input type="radio" name="others" value="linked" />'.WT_I18N::translate('Add this source and families/people linked to it.').'</td></tr>
		<tr><td class="topbottombar"><input type="submit" value="'.WT_I18N::translate('Continue Adding').'" />
		</table>
		</form>';
		}
		else return $this->getSidebarContent();
		return $out;
	}

	public function downloadForm() {
		global $TEXT_DIRECTION;
		$controller = $this->controller;
		$out = WT_JS_START;
		$out .= 'function cancelDownload() {
				var link = "sidebar.php?sb_action=clippings";
				jQuery("#sb_clippings_content").load(link);
			}';
		$out .= WT_JS_END;
		$out .= '<form method="get" action="module.php">
		<input type="hidden" name="mod" value="clippings" />
		<input type="hidden" name="mod_action" value="index" />
		<input type="hidden" name="action" value="download" />
		<table>
		<tr><td colspan="2" class="topbottombar"><h2>'.WT_I18N::translate('File Information').'</h2></td></tr>
		<tr><td class="descriptionbox width50 wrap">'.WT_I18N::translate('Zip File(s)').help_link('zip').'</td>
		<td class="optionbox"><input type="checkbox" name="Zip" value="yes" checked="checked" /></td></tr>

		<tr><td class="descriptionbox width50 wrap">'.WT_I18N::translate('Include media (automatically zips files)').help_link('include_media').'</td>
		<td class="optionbox"><input type="checkbox" name="IncludeMedia" value="yes" checked="checked" /></td></tr>
		';

		// Determine the Privatize options available to this user
		if (WT_USER_IS_ADMIN) {
			$radioPrivatizeVisitor = '';
			$radioPrivatizeUser = '';
			$radioPrivatizeGedadmin = '';
			$radioPrivatizeNone = 'checked="checked" ';
		} else if (WT_USER_GEDCOM_ADMIN) {
			$radioPrivatizeVisitor = '';
			$radioPrivatizeUser = '';
			$radioPrivatizeGedadmin = 'checked="checked" ';
			$radioPrivatizeNone = 'DISABLED ';
		} else if (WT_USER_ID) {
			$radioPrivatizeVisitor = '';
			$radioPrivatizeUser = 'checked="checked" ';
			$radioPrivatizeGedadmin = 'DISABLED ';
			$radioPrivatizeNone = 'DISABLED ';
		} else {
			$radioPrivatizeVisitor = 'checked="checked" ';
			$radioPrivatizeUser = 'DISABLED ';
			$radioPrivatizeGedadmin = 'DISABLED ';
			$radioPrivatizeNone = 'DISABLED ';
		}
		$out .= '
		<tr><td class="descriptionbox width50 wrap">'.WT_I18N::translate('Apply privacy settings?').help_link('apply_privacy').'</td>
		<td class="list_value">
		<input type="radio" name="privatize_export" value="visitor" '.$radioPrivatizeVisitor.'/>&nbsp;'.WT_I18N::translate('Visitor').'<br />
		<input type="radio" name="privatize_export" value="user" '.$radioPrivatizeUser.'/>&nbsp;'.WT_I18N::translate('Member').'<br />
		<input type="radio" name="privatize_export" value="gedadmin" '.$radioPrivatizeGedadmin.'/>&nbsp;'.WT_I18N::translate('Administrator').'<br />
		<input type="radio" name="privatize_export" value="none" '.$radioPrivatizeNone.'/>&nbsp;'.WT_I18N::translate('None').'</td></tr>

		<tr><td class="descriptionbox width50 wrap">'.WT_I18N::translate('Convert from UTF-8 to ANSI (ISO-8859-1)').help_link('utf8_ansi').'</td>
		<td class="optionbox"><input type="checkbox" name="convert" value="yes" /></td></tr>

		<tr><td class="descriptionbox width50 wrap">'.WT_I18N::translate('Remove custom webtrees tags? (eg. _WT_USER, _THUM)').help_link('remove_tags').'</td>
		<td class="optionbox"><input type="checkbox" name="remove" value="yes" checked="checked" />
		<input type="hidden" name="conv_path" value="'.getLRM(). $controller->conv_path. getLRM().'" /></td></tr>

		<tr><td class="topbottombar" colspan="2">
		<input type="button" value="'.WT_I18N::translate('Cancel').'" onclick="cancelDownload();" />
		<input type="submit" value="'.WT_I18N::translate('Download Now').'" />
		</form>';

		return $out;
	}

}