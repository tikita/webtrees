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

class descendancy_WT_Module extends WT_Module implements WT_Module_Sidebar {
	// Extend WT_Module
	public function getTitle() {
		return /* I18N: Name of a module/sidebar */ WT_I18N::translate('Descendants');
	}

	// Extend WT_Module
	public function getDescription() {
		return /* I18N: Description of the "Descendants" module */ WT_I18N::translate('A sidebar showing the descendants of an individual.');
	}

	// Implement WT_Module_Sidebar
	public function defaultSidebarOrder() {
		return 30;
	}

	// Implement WT_Module_Sidebar
	public function hasSidebarContent() {
		return true;
	}

	// Implement WT_Module_Sidebar
	public function getSidebarAjaxContent() {
		$search   =safe_GET('search');
		$pid   =safe_GET('pid', WT_REGEX_XREF);
		$famid   =safe_GET('famid', WT_REGEX_XREF);

		$last = array('search'=>$search);
		$_SESSION['sb_descendancy_last'] = $last;

		if (!empty($search)) return $this->search($search);
		else if (!empty($pid)) return $this->loadSpouses($pid, 1);
		else if (!empty($famid)) return $this->loadChildren($famid, 1);
	}

	// Implement WT_Module_Sidebar
	public function getSidebarContent() {
		global $WT_IMAGES;

		$out = '<script type="text/javascript">
		<!--
		var dloadedNames = new Array();

		function dsearchQ() {
			var query = jQuery("#sb_desc_name").attr("value");
			if (query.length>1) {
				jQuery("#sb_desc_content").load("sidebar.php?sb_action=descendancy&search="+query);
			}
		}

		jQuery(document).ready(function(){
			jQuery("#sb_desc_name").focus(function(){this.select();});
			jQuery("#sb_desc_name").blur(function(){if (this.value=="") this.value="'.WT_I18N::translate('Search').'";});
			var dtimerid = null;
			jQuery("#sb_desc_name").keyup(function(e) {
				if (dtimerid) window.clearTimeout(dtimerid);
				dtimerid = window.setTimeout("dsearchQ()", 500);
			});

			jQuery(".sb_desc_indi").live("click", function() {
				var pid=this.title;
				if (!dloadedNames[pid]) {
					jQuery("#sb_desc_"+pid+" div").load(this.href);
					jQuery("#sb_desc_"+pid+" div").show();
					jQuery("#sb_desc_"+pid+" .plusminus").attr("src", "'.$WT_IMAGES['minus'].'");
					dloadedNames[pid]=2;
				}
				else if (dloadedNames[pid]==1) {
					dloadedNames[pid]=2;
					jQuery("#sb_desc_"+pid+" div").show();
					jQuery("#sb_desc_"+pid+" .plusminus").attr("src", "'.$WT_IMAGES['minus'].'");
				}
				else {
					dloadedNames[pid]=1;
					jQuery("#sb_desc_"+pid+" div").hide();
					jQuery("#sb_desc_"+pid+" .plusminus").attr("src", "'.$WT_IMAGES['plus'].'");
				}
				return false;
			});
		});
		//-->
		</script>
		<form method="post" action="sidebar.php" onsubmit="return false;">
		<input type="text" name="sb_desc_name" id="sb_desc_name" value="'.WT_I18N::translate('Search').'" />';
		$out .= '</form><div id="sb_desc_content">';

		if ($this->controller) {
			$root = null;
			if ($this->controller->pid) {
				$root = WT_Person::getInstance($this->controller->pid);
			} elseif ($this->controller->famid) {
				$fam = WT_Family::getInstance($this->controller->famid);
				if ($fam) {
					$root = $fam->getHusband();
				}
				if (!$root) {
					$root = $fam->getWife();
				}
			}
			if ($root) {
				$out .= '<ul>'.$this->getPersonLi($root, 1).'</ul>';
			}
		}
		$out .= '</div>';
		return $out;
	}

	public function getPersonLi(WT_Person $person, $generations=0) {
		global $WT_IMAGES;

		$out = '<li id="sb_desc_'.$person->getXref().'" class="sb_desc_indi_li"><a href="sidebar.php?sb_action=descendancy&amp;pid='.$person->getXref().'" title="'.$person->getXref().'" class="sb_desc_indi">';
		if ($generations>0) {
			$out .= '<img src="'.$WT_IMAGES['minus'].'" border="0" class="plusminus" alt="" />';
		} else {
			$out .= '<img src="'.$WT_IMAGES['plus'].'" border="0" class="plusminus" alt="" />';
		}
		$out .= $person->getSexImage().' '.$person->getListName().' ';
		if ($person->canDisplayDetails()) {
			$out .= PrintReady(' ('.$person->getLifeSpan().')');
		}
		$out .= '</a> <a href="'.$person->getHtmlUrl().'"><img src="'.$WT_IMAGES['button_indi'].'" border="0" alt="indi" /></a>';
		if ($generations>0) {
			$out .= '<div class="desc_tree_div_visible">';
			$out .= $this->loadSpouses($person->getXref());
			$out .= '</div><script type="text/javascript">dloadedNames["'.$person->getXref().'"]=2;</script>';
		} else {
			$out .= '<div class="desc_tree_div">';
			$out .= '</div>';
		}
		$out .= '</li>';
		return $out;
	}

	public function getFamilyLi(WT_Family $family, WT_Person $person, $generations=0) {
		global $WT_IMAGES;

		$out = '<li id="sb_desc_'.$family->getXref().'" class="sb_desc_indi_li"><a href="sidebar.php?sb_action=descendancy&amp;famid='.$family->getXref().'" title="'.$family->getXref().'" class="sb_desc_indi">';
		$out .= '<img src="'.$WT_IMAGES['minus'].'" border="0" class="plusminus" alt="" />';
		$out .= $person->getSexImage().$person->getListName();

		$marryear = $family->getMarriageYear();
		if (!empty($marryear)) {
			$out .= ' ('.WT_Gedcom_Tag::getLabel('MARR').' '.$marryear.')';
		}
		$out .= '</a> <a href="'.$person->getHtmlUrl().'"><img src="'.$WT_IMAGES['button_indi'].'" border="0" alt="indi" /></a>';
		$out .= '<a href="'.$family->getHtmlUrl().'"><img src="'.$WT_IMAGES['button_family'].'" border="0" alt="family" /></a>';
		$out .= '<div class="desc_tree_div_visible">';
		$out .= $this->loadChildren($family->getXref(), $generations);
		$out .= '</div><script type="text/javascript">dloadedNames["'.$family->getXref().'"]=2;</script>';
		$out .= '</li>';
		return $out;
	}

	public function search($query) {
		global $WT_IMAGES;

		if (strlen($query)<2) return '';
		$rows=WT_DB::prepare(
			"SELECT ? AS type, i_id AS xref, i_file AS ged_id, i_gedcom AS gedrec, i_isdead, i_sex".
			" FROM `##individuals`, `##name`".
			" WHERE (i_id LIKE ? OR n_sort LIKE ?)".
			" AND i_id=n_id AND i_file=n_file AND i_file=?".
			" ORDER BY n_sort LIMIT ".WT_AUTOCOMPLETE_LIMIT
		)
		->execute(array('INDI', "%{$query}%", "%{$query}%", WT_GED_ID))
		->fetchAll(PDO::FETCH_ASSOC);

		$out = '';
		foreach ($rows as $row) {
			$person=WT_Person::getInstance($row);
			if ($person->canDisplayName()) {
				$out .= $this->getPersonLi($person);
			}
		}
		if ($out) {
			return '<ul>'.$out.'</ul>';
		} else {
			return '';
		}
	}

	public function loadSpouses($pid, $generations=0) {
		$out = '';
		$person = WT_Person::getInstance($pid);
		if ($person->canDisplayDetails()) {
			foreach($person->getSpouseFamilies() as $family) {
				$spouse = $family->getSpouse($person);
				if ($spouse) {
					$out .= $this->getFamilyLi($family, $spouse, $generations-1);
				}
			}
		}
		if ($out) {
			return '<ul>'.$out.'</ul>';
		} else {
			return '';
		}
	}

	public function loadChildren($famid, $generations=0) {
		$out = '';
		$family = WT_Family::getInstance($famid);
		if ($family->canDisplayDetails()) {
			$children = $family->getChildren();
			if (count($children)>0) {
				foreach($children as $child) {
					$out .= $this->getPersonLi($child, $generations-1);
				}
			} else {
				$out .= WT_I18N::translate('No children');
			}
		}
		if ($out) {
			return '<ul>'.$out.'</ul>';
		} else {
			return '';
		}
	}
}