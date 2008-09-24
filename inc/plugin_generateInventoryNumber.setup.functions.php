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
	  `template_networking` varchar(255)  collate utf8_unicode_ci NOT NULL default '',
	  `generate_ocs` int(1)  NOT NULL default 1,
	  `generate_data_injection` int(1)  NOT NULL default 1,
	  `generate_internal` int(1)  NOT NULL default 1,
	  `next_number` int(11)  NOT NULL default 0,
	  PRIMARY KEY  (`ID`)
	) ENGINE=MyISAM CHARSET=utf8 COLLATE=utf8_unicode_ci;";
	$DB->query($sql) or die($DB->error());
	
	$sql = "INSERT INTO `glpi_plugin_generateinventorynumber_config` (
			`ID` ,`FK_entities` ,`active` ,`template_computer` ,`template_monitor` ,`template_printer` ,
			`template_peripheral` ,`template_phone` ,`template_networking`,`next_number`,
			`generate_ocs`,`generate_data_injection`,`generate_internal`)
			VALUES (NULL , '-1', '0', '&lt;#######&gt;', '&lt;#######&gt;', '&lt;#######&gt;', '&lt;#######&gt;', '&lt;#######&gt;', '&lt;#######&gt;','1','1','1','0');";
	$DB->query($sql) or die($DB->error());
	
	$sql="
	CREATE TABLE `glpi_plugin_generateinventorynumber_profiles` (
	  `ID` int(11) NOT NULL auto_increment,
	  `name` varchar(255) default NULL,
	  `interface` varchar(50) collate utf8_unicode_ci NOT NULL default 'generateinventorynumber',
	  `is_default` int(6) NOT NULL default '0',
	  `generate` char(1) default NULL,
	  `generate_overwrite` char(1) default NULL,
	  PRIMARY KEY  (`ID`)
	) ENGINE=MyISAM CHARSET=utf8 COLLATE=utf8_unicode_ci;";
	$DB->query($sql) or die($DB->error());
}

function plugin_generateInventoryNumber_Uninstall() {

	global $DB;
	$DB->query("DROP TABLE glpi_plugin_generateinventorynumber_config;") or die($DB->error());
	$DB->query("DROP TABLE glpi_plugin_generateinventorynumber_profiles;") or die($DB->error());	
}

function plugin_generateInventoryNumber_createfirstaccess($ID) {

	global $DB;

	$inventoryProfile = new GenerateInventoryNumberProfile;
	if (!$inventoryProfile->getFromDB($ID)) {

		$Profile = new Profile();
		$Profile->getFromDB($ID);
		$name = $Profile->fields["name"];

		$query = "INSERT INTO `glpi_plugin_generateinventorynumber_profiles` ( `ID`, `name` , `interface`, `is_default`, `generate`, `generate_overwrite`) VALUES ('$ID', '$name','generateinventorynumber','0','w','w');";
		$DB->query($query);
	}
}

function plugin_generateInventoryNumber_createaccess($ID){

	global $DB;
	
	$Profile=new Profile();
	$Profile->GetfromDB($ID);
	$name=$Profile->fields["name"];
	
	$query ="INSERT INTO `glpi_plugin_generateinventorynumber_profiles` ( `ID`, `name` , `interface`, `is_default`, `generate`, `generate_overwrite`) VALUES ('$ID', '$name','generateInventoryNumber','0',NULL,NULL);";
	$DB->query($query);

}

?>