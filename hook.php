<?php

/*
 * @version $Id: setup.php,v 1.2 2006/04/02 14:45:27 moyo Exp $
 ----------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2006 by the INDEPNET Development Team.

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
// Original Author of file: Walid Nouh
// Purpose of file:
// ----------------------------------------------------------------------
// Hook done on delete item case

function plugin_pre_item_delete_generateInventoryNumber($input){
	if (isset($input["_item_type_"]))
		switch ($input["_item_type_"]){
			case PROFILE_TYPE :
				// Manipulate data if needed 
				$GenerateInventoryNumberProfile=new GenerateInventoryNumberProfile;
				$GenerateInventoryNumberProfile->cleanProfiles($input["ID"]);
				break;
		}
	return $input;
}

//Define headings added by the plugin
function plugin_get_headings_generateInventoryNumber($type,$withtemplate){
	global $LANGGENINVENTORY;
		
	if (in_array($type,array(PROFILE_TYPE))){
		// template case
		if ($withtemplate)
			return array();
		// Non template case
		else 
			return array(
					1 => $LANGGENINVENTORY["title"][1],
					);
	}else
		return false;

	
}

// Define headings actions added by the plugin	 
function plugin_headings_actions_generateInventoryNumber($type){
		
	if (in_array($type,array(PROFILE_TYPE))){
		return array(
					1 => "plugin_headings_generateInventoryNumber",
					);
	}else
		return false;
	
}

// action heading
function plugin_headings_generateInventoryNumber($type,$ID,$withtemplate=0){
	global $CFG_GLPI;

		switch ($type){
			case PROFILE_TYPE :
				$prof=new GenerateInventoryNumberProfile();	
				if (!$prof->getFromDB($ID))
					plugin_generateInventoryNumber_createaccess($ID);				
				$prof->showForm($CFG_GLPI["root_doc"]."/plugins/generateInventoryNumber/front/plugin_generateInventoryNumber.profile.php",$ID);		
			break;
			default :
			break;
		}
}


?>