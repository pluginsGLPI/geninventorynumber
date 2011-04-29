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

foreach (glob(GLPI_ROOT . '/plugins/geninventorynumber/inc/*.php') as $file) {
	include_once ($file);
}

/**
* Deletes plugin rights for profile if deleted
*
* @param	object	CommonDBTM object currently purged (only if 'Profile' type)
* @return	object	returns the input
*/
function plugin_pre_item_purge_geninventorynumber($item) {
	$type = get_class($item);
	switch ($type) {
		case PROFILE_TYPE :
			$PluginGeninventorynumberProfile = new PluginGeninventorynumberProfile;
			$PluginGeninventorynumberProfile->cleanProfiles($item->fields["id"]);
			break;
	}
	return $item;
}

/**
* Adds tabs to currently displayed object (only for 'Profile' type)
*
* @param	object	CommonDBTM object shown (only if 'Profile' type)
* @return	array or bool	List of tab names or false (no tabs)
*/
function plugin_get_headings_geninventorynumber($item, $withtemplate = '') {
	global $LANG;
	$type=get_class($item);
	if (in_array($type, array (PROFILE_TYPE))) {
		return array (
			1 => $LANG["plugin_geninventorynumber"]["title"][1],
		);
	} else {
		return false;
	}
}

/**
* Define plugin functions associated with tabs for currently displayed object
*
* @param 	object	CommonDBTM object shown (only if 'Profile' type)
* @return	array or bool	List of function names or false
*/
function plugin_headings_actions_geninventorynumber($item) {
	$type=get_class($item);
	if (in_array($type, array (PROFILE_TYPE
		))) {
		return array (
			1 => "plugin_headings_geninventorynumber",
		);
	} else {
		return false;
	}
}

/**
* Shows right management part for plugin when a Profile is edited
*
* @param	object	Objet CommonDBTM object shown (only if 'Profile' type)
* @return	null
*/
function plugin_headings_geninventorynumber($item) {
	global $CFG_GLPI;

	switch (get_class($item)) {
		case PROFILE_TYPE :
			$prof = new PluginGeninventorynumberProfile();
			if (!$prof->getFromDB($item->getField('id'))) {
				$prof->createAccess($item->getField('id'));
				$prof->getFromDB($item->getField('id'));
			}
			$prof->showForm($item->getField('id'), array('target' => $CFG_GLPI["root_doc"]."/plugins/geninventorynumber/front/profile.form.php"));
		break;

		default :
        break;
   }
}

/**
* Deletes an inventory number if user-defined when generation is active
*
* @param	object CommonDBTM object to be updated
* @return	null
*/
function plugin_pre_item_update_geninventorynumber($item) {
	global $GENINVENTORYNUMBER_INVENTORY_TYPES, $LANG;

	$type = get_class($item);
	if (isset ($type) && in_array ($type,$GENINVENTORYNUMBER_INVENTORY_TYPES)) {
      $fields = plugin_geninventorynumber_getFieldInfos('otherserial');
		$template = addslashes_deep($fields[$type]['template']);
 		if ($fields[$type]['enabled'] && $fields[$type]['template'] != '') {
			if (isset ($item->input["otherserial"])) {
				unset ($item->input["otherserial"]);
				$_SESSION["MESSAGE_AFTER_REDIRECT"] = $LANG["plugin_geninventorynumber"]["massiveaction"][2];
			}
		}
	}
	return $item;
}

/**
* Generates a number for the object juste added
*
* @param	object	CommonDBTM object just added
* @return	null
*/
function plugin_item_add_geninventorynumber($item) {
	global $DB, $LANG;

	$massive_action = false;
	$type = get_class($item);
	$fields = plugin_geninventorynumber_getFieldInfos('otherserial');

	if (isset ($fields[$type])) {
		$config = new PluginGeninventorynumberConfig;
		$config->getFromDb(1);

		//Globally check if auto generation is on
		if ($config->fields['active']) {
			if ($fields[$type]['enabled']) {
				$template = addslashes_deep($fields[$type]['template']);

				$commonitem = new $type;
				$commonitem->getFromDB($item->fields["id"]);

				$generated_field = plugin_geninventorynumber_autoName($template, $type, 0, $commonitem->fields, $fields);

				//Cannot use update() because it'll launch pre_item_update and clean the inventory number...
				$sql = "UPDATE " . $commonitem->getTable() . " SET otherserial='" . $generated_field . "' WHERE id=" . $item->fields["id"];
				$DB->query($sql);

				if (!$massive_action && strstr($_SESSION["MESSAGE_AFTER_REDIRECT"], $LANG["plugin_geninventorynumber"]["massiveaction"][3]) === false)
					$_SESSION["MESSAGE_AFTER_REDIRECT"] .= $LANG["plugin_geninventorynumber"]["massiveaction"][3];

				if ($fields[$type]['use_index'])
					$sql = "UPDATE glpi_plugin_geninventorynumber_configs SET next_number=next_number+1 WHERE FK_entities=0";
				else
					$sql = "UPDATE glpi_plugin_geninventorynumber_indexes SET next_number=next_number+1 WHERE FK_entities=0 AND type='".$type."' AND field='otherserial'";
				$DB->query($sql);
			}
		}
	}
	return $item;
}

/**
* Alias for plugin_geninventorynumber_haveRight
*
* @param	string
* @param	string
* @return	bool
*/
function plugin_geninventorynumber_haveTypeRight($type, $right) {
	return plugin_geninventorynumber_haveRight($type, $right);
}

/**
* Define list of massive actions available through this plugin
*
* @param	string	Type of object currently displayed
* @return	array	list of key(action name) and values (displayed string) for massive actions
*/
function plugin_geninventorynumber_MassiveActions($type) {
	global $LANG, $GENINVENTORYNUMBER_INVENTORY_TYPES;

	$values = array ();
	if (in_array($type,$GENINVENTORYNUMBER_INVENTORY_TYPES)) {
      $fields = plugin_geninventorynumber_getFieldInfos('otherserial');
      if ($fields[$type]['enabled']) {
         if (plugin_geninventorynumber_haveRight("generate", "w")) {
            $values["plugin_geninventorynumber_generate"] = $LANG["plugin_geninventorynumber"]["massiveaction"][0];
         }
         if (plugin_geninventorynumber_haveRight("generate_overwrite", "w")) {
            $values["plugin_geninventorynumber_generate_overwrite"] = $LANG["plugin_geninventorynumber"]["massiveaction"][1];
         }
         return $values;
      }
      else {
         return array ();
      }
	} else {
		return array ();
	}
}

/**
* Shows validate button when plugin massive action selected in dropdown
*
* @param	array	Options as designed for MassiveActionsDisplay hook
* @return	string	HTML code for button or empty string
*/
function plugin_geninventorynumber_MassiveActionsDisplay($options = array()) {
	global $LANG, $GENINVENTORYNUMBER_INVENTORY_TYPES;
	if (in_array ($options['itemtype'],$GENINVENTORYNUMBER_INVENTORY_TYPES)) {
		switch ($options['action']) {
			case "plugin_geninventorynumber_generate" :
			case "plugin_geninventorynumber_generate_overwrite" :
				echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"" . $LANG["buttons"][2] . "\" >";
				break;
			default :
				break;
		}
	}
	return "";
}

/**
* Executes massive actions for this plugin
*
*	$data structure : array('item'=>array('ID', 'ID2', ...),'itemtype'=>'TypeObjets', 'action'=>'NomAction')
*
* @param	array	Massive Actions Parameters (as designed for hook)
* @return	null
*/
function plugin_geninventorynumber_MassiveActionsProcess($data) {
	global $DB;

	switch ($data['action']) {
		case "plugin_geninventorynumber_generate" :
		case "plugin_geninventorynumber_generate_overwrite" :
			foreach ($data["item"] as $key => $val) {
				if ($val == 1) {
					$commonitem = new $data['itemtype'];
					$commonitem->getFromDB($key);
					if (//Only generates inventory number for object without it !
					 (($data["action"] == "plugin_geninventorynumber_generate") && isset ($commonitem->fields["otherserial"]) && $commonitem->fields["otherserial"] == "") //Or is overwrite action is selected
					|| ($data["action"] == "plugin_geninventorynumber_generate_overwrite")) {
						plugin_item_add_geninventorynumber($commonitem);
					}
				}
			}
			break;
		default :
			break;
	}
}

/**
* ???
*
* @param
* @return
*/
function plugin_geninventorynumber_checkRight($module, $right) {
	global $CFG_GLPI;

	if (!plugin_plugin_geninventorynumber_haveRight($module, $right)) {
		// Gestion timeout session
		if (!isset ($_SESSION["glpiID"])) {
			glpi_header($CFG_GLPI["root_doc"] . "/index.php");
			exit ();
		}

		displayRightError();
	}
}

/**
* Create database tables for this plugin and updates from older versions
*
* @return	null
*
*/
function plugin_geninventorynumber_Install() {
	global $DB, $GENINVENTORYNUMBER_INVENTORY_TYPES;

	if (!TableExists("glpi_plugin_geninventorynumber_config") && !TableExists("glpi_plugin_generateinventorynumber_config")&& !TableExists("glpi_plugin_geninventorynumber_configs")) {
		$sql = "CREATE TABLE IF NOT EXISTS `glpi_plugin_geninventorynumber_configs` (
				              `id` int(11) NOT NULL auto_increment,
		                    `name` varchar(255) DEFAULT NULL,
		                    `field` varchar(255) DEFAULT NULL,
				              `FK_entities` int(11)  NOT NULL default -1,
				              `active` int(1)  NOT NULL default 0,
				              `next_number` int(11)  NOT NULL default 0,
		                    `comments` text NULL,
				              PRIMARY KEY  (`id`)
				            ) ENGINE=MyISAM CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$DB->query($sql) or die($DB->error());

		$sql = "INSERT INTO `glpi_plugin_geninventorynumber_configs` (
				               `id` ,`name`,`field`,`FK_entities` ,`active`, `next_number`)
				               VALUES (NULL , 'otherserial','otherserial','0', '0', '0');";
		$DB->query($sql) or die($DB->error());

		$sql = "CREATE TABLE  IF NOT EXISTS `glpi_plugin_geninventorynumber_profiles` (
				              `id` int(11) NOT NULL auto_increment,
				              `name` varchar(255) default NULL,
				              `interface` varchar(50) collate utf8_unicode_ci NOT NULL default 'GenInventoryNumber',
				              `is_default` int(6) NOT NULL default '0',
				              `generate` char(1) default NULL,
				              `generate_overwrite` char(1) default NULL,
				              PRIMARY KEY  (`id`)
				            ) ENGINE=MyISAM CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$DB->query($sql) or die($DB->error());

		$sql = "CREATE TABLE  IF NOT EXISTS `glpi_plugin_geninventorynumber_indexes` (
				            `id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
				            `FK_entities` INT( 11 ) NOT NULL DEFAULT '0',
				            `type` VARCHAR( 255 ) NOT NULL DEFAULT '',
				            `field` VARCHAR( 255 ) NOT NULL DEFAULT 'otherserial',
				            `next_number` INT( 11 ) NOT NULL DEFAULT '0',
				            PRIMARY KEY ( `id` )
				            ) ENGINE = MYISAM CHARSET=utf8 COLLATE=utf8_unicode_ci; ";
		$DB->query($sql) or die($DB->error());

		foreach ($GENINVENTORYNUMBER_INVENTORY_TYPES as $type) {
			$sql = "INSERT INTO `glpi_plugin_geninventorynumber_indexes` (
			            `id` ,`FK_entities` ,`type` ,`field` ,`next_number`) VALUES (NULL , '0', '$type', 'otherserial', '0');";
			$DB->query($sql) or die($DB->error());
		}

		$query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_geninventorynumber_configfields` (
		        `id` int(11) NOT NULL auto_increment,
		        `config_id` int(11) NOT NULL default '0',
		        `device_type` VARCHAR(255) NOT NULL default '',
		        `template` varchar(255) NOT NULL,
		        `enabled` smallint(1) NOT NULL default '0',
		        `use_index` smallint(1) NOT NULL default '0',
		        `index` bigint(20) NOT NULL default '0',
		        PRIMARY KEY  (`id`)
		      ) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
		$DB->query($query);

		$field = new PluginGeninventorynumberConfigField;
		foreach ($GENINVENTORYNUMBER_INVENTORY_TYPES as $type) {
			$input["config_id"] = 1;
			$input["device_type"] = $type;
			$input["template"] = "&lt;#######&gt;";
			$input["enabled"] = 0;
			$input["index"] = 0;
			$field->add($input);
		}

		PluginGeninventoryNumberProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
		} else {
		if (!TableExists("glpi_generateinventorynumber_indexes")) {
			plugin_geninventorynumber_updatev11();
		}
		plugin_geninventorynumber_updatev120();
		plugin_geninventorynumber_updatev130();
		plugin_geninventorynumber_updatev140();
	}

	return true;
}

/**
* Destroys database tables on uninstall
*
* @return	null
*/function plugin_geninventorynumber_Uninstall() {

	global $DB;
	$tables = array (
		"glpi_plugin_geninventorynumber_configs",
		"glpi_plugin_geninventorynumber_profiles",
		"glpi_plugin_geninventorynumber_indexes",
		"glpi_plugin_geninventorynumber_configfields"
	);

	foreach ($tables as $table) {
		$DB->query("DROP TABLE IF EXISTS `$table`;") or die($DB->error());
	}
}



/**
* Check if a combination of type/field is already registered in the database
*
* @param	string	an object type (litteral) (ex : 'Computer')
* @param	string	the checked field for this type (default:  'otherserial')
* @return	int ou bool	 ID of the configuration line in the table or false
*
* TODO: check table joins on this request ?
* NOTE : doesn't seem to be used by this version or version 1.30. Used by other plugins ?
*/
function plugin_geninventorynumber_isTypeRegistered($type, $field = 'otherserial') {
	global $DB;
	$query = "SELECT config.id FROM `glpi_plugin_geninventorynumber_configfields` as fields,
	               `glpi_plugin_geninventorynumber_config` as config
	                  WHERE config.field='$field' AND config.ID=fields.config_id
	                     ORDER BY fields.device_type";
	$result = $DB->query($query);
	if ($DB->numrows($result)) {
		return $DB->result($result, 0, 'ID');
	} else {
		return false;
	}
}

/**
* Register a combination of type/field into database
*
* @param	string	an object type (litteral) (ex : 'Computer')
* @param	string	the checked field for this type (default:  'otherserial')
* @return	null
*
* NOTE : doesn't seem to be used by this version or version 1.30. Used by other plugins ?
*/
function plugin_geninventorynumber_registerType($type, $field = 'otherserial') {
	global $DB;
   $config_id = plugin_geninventorynumber_isTypeRegistered($type, $field);
   if ($config_id) {
      $sql = "SELECT id FROM `glpi_plugin_geninventorynumber_configfields` WHERE `config_id`='$config_id' AND `device_type`='$type'";
      $result = $DB->query($sql);
      if (!$DB->numrows($result)) {
         $field = new PluginGeninventorynumberConfigField;

         $input["config_id"] = $config_id;
         $input["device_type"] = $type;
         $input["template"] = "&lt;#######&gt;";
         $input["enabled"] = 0;
         $input["index"] = 0;
         $field->add($input);

         $sql = "INSERT INTO `glpi_plugin_geninventorynumber_indexes` (
                     `id` ,`FK_entities` ,`type` ,`field` ,`next_number`) VALUES (NULL , '0', '$type', 'otherserial', '0');";
         $DB->query($sql) or die($DB->error());
      }
 	}
}

/**
* Unregister a combination of type/field into database
*
* @param	string	an object type (litteral) (ex : 'Computer')
* @param	string	the checked field for this type (default:  'otherserial')
* @return	null
*
*  NOTE : doesn't seem to be used by this version or version 1.30. Used by other plugins ?
*/
function plugin_geninventorynumber_unRegisterType($type, $field = 'otherserial') {
	global $DB;
   $query = "DELETE FROM `glpi_plugin_geninventorynumber_configfields` WHERE device_type='$type'";
	$result = $DB->query($query);

   $query = "DELETE FROM `glpi_plugin_geninventorynumber_indexes` WHERE type='$type' AND field='$field'";
   $result = $DB->query($query);
}
?>