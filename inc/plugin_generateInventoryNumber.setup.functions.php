<?php

/*
   ----------------------------------------------------------------------
   GLPI - Gestionnaire Libre de Parc Informatique
   Copyright (C) 2003-2005 by the INDEPNET Development Team.

   http://indepnet.net/   http://glpi-project.org/
   ----------------------------------------------------------------------

   LICENSE

   This file is part of GLPI.

   GLPI is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 2 of the License, or
   (at your option) any later version.

   GLPI is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with GLPI; if not, write to the Free Software
   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
   ------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Béchu Philippe
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
	die("Sorry. You can't access directly to this file");
}

function plugin_generateInventoryNumber_Install() {

	global $DB;

	$sql = "CREATE TABLE `glpi_plugin_generateinventorynumber_config` (
	  `ID` int(11) NOT NULL auto_increment,
	  `FK_entities` int(11)  NOT NULL default -1,
	  `active` int(1)  NOT NULL default 0,
	  `template_computer` varchar(255)  collate utf8_unicode_ci NOT NULL default '',
	  `template_monitor` varchar(255)  collate utf8_unicode_ci NOT NULL default '',  
	  `template_printer` varchar(255)  collate utf8_unicode_ci NOT NULL default '',
	  `template_peripheral` varchar(255)  collate utf8_unicode_ci NOT NULL default '',
	  `template_phone` varchar(255)  collate utf8_unicode_ci NOT NULL default '',
	  `template_software` varchar(255)  collate utf8_unicode_ci NOT NULL default '',
	  `template_networking` varchar(255)  collate utf8_unicode_ci NOT NULL default '',
	  `next_number` int(11)  NOT NULL default 0,
	  PRIMARY KEY  (`ID`)
	) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
	$DB->query($sql) or die($DB->error());
	
	$sql = "INSERT INTO `glpi071`.`glpi_plugin_generateinventorynumber_config` (
			`ID` ,`FK_entities` ,`active` ,`template_computer` ,`template_monitor` ,`template_printer` ,`template_peripheral` ,`template_phone` ,`template_software` ,`template_networking`,`next_number`)
			VALUES (NULL , '-1', '0', '#######', '#######', '#######', '#######', '#######', '#######', '#######','0');";
	$DB->query($sql) or die($DB->error());
}

function plugin_generateInventoryNumber_Uninstall() {

	global $DB;
	$DB->query("DROP TABLE glpi_plugin_generateinventorynumber_config;") or die($DB->error());
}
?>