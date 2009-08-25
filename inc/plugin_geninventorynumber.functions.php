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
// Original Author of file: Walid Nouh
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

function plugin_geninventorynumber_getConfig($FK_entities = 0) {
   $config = new plugin_geninventorynumberConfig;
   $config->getFromDB(1);
   return $config;
}

function plugin_geninventorynumber_canGenerate($parm, $config) {
   global $INVENTORY_TYPES;
   return ($config->fields[$INVENTORY_TYPES[$parm["type"]] . "_gen_enabled"]?true:false);
}

function plugin_item_add_geninventorynumber($parm,$massive_action=false) {
   global $INVENTORY_TYPES, $DB,$LANG;
   if (isset ($parm["type"]) && isset ($INVENTORY_TYPES[$parm["type"]])) {
      $config = plugin_geninventorynumber_getConfig(0);

      //Globally check if auto generation is on
      if (plugin_geninventorynumber_isActive($parm["type"])) {

         if (plugin_geninventorynumber_canGenerate($parm, $config)) {
            $template = addslashes_deep($config->fields[plugin_geninventorynumber_getTemplateFieldByType($parm["type"])]);

            $commonitem = new CommonItem;
            $commonitem->setType($parm["type"], true);
            $fields = $commonitem->obj->fields;

            //Cannot use update() because it'll launch pre_item_update and clean the inventory number...
            $sql = "UPDATE " . $commonitem->obj->table . " SET otherserial='" . plugin_geninventorynumber_autoName($template, $parm["type"], 0) . "' WHERE ID=" . $parm["ID"];
            $DB->query($sql);

            if (!$massive_action && strstr($_SESSION["MESSAGE_AFTER_REDIRECT"],$LANG["plugin_geninventorynumber"]["massiveaction"][3]) === false)
            $_SESSION["MESSAGE_AFTER_REDIRECT"].=$LANG["plugin_geninventorynumber"]["massiveaction"][3];

            plugin_geninventorynumber_incrementNumber(0, $parm["type"]);
         }
      }
   }

   return $parm;
}


function plugin_geninventorynumber_getTemplateFieldByType($type) {
   switch ($type) {
      case COMPUTER_TYPE :
         return "template_computer";
         case MONITOR_TYPE :
            return "template_monitor";
            case PRINTER_TYPE :
               return "template_printer";
               case PERIPHERAL_TYPE :
                  return "template_peripheral";
                  case NETWORKING_TYPE :
                     return "template_networking";
                     case PHONE_TYPE :
                        return "template_phone";
                     }
                  }

function plugin_geninventorynumber_autoName($objectName, $type, $FK_entities = 0) {
                     global $DB;

                     $len = strlen($objectName);
                     if ($len > 8 && substr($objectName, 0, 4) === '&lt;' && substr($objectName, $len -4, 4) === '&gt;') {
                        $autoNum = substr($objectName, 4, $len -8);
                        $mask = '';
                        if (preg_match("/\\#{1,10}/", $autoNum, $mask)) {
                           $global = strpos($autoNum, '\\g') !== false && $type != INFOCOM_TYPE ? 1 : 0;
                           $autoNum = str_replace(array (
            '\\y',
            '\\Y',
            '\\m',
            '\\d',
            '_',
            '%',
            '\\g'
                              ), array (
                                 date('y'),
                                 date('Y'),
                                 date('m'),
                                 date('d'),
            '\\_',
            '\\%',
            ''
                              ), $autoNum);
                           $mask = $mask[0];
                           $pos = strpos($autoNum, $mask) + 1;
                           $len = strlen($mask);
                           $like = str_replace('#', '_', $autoNum);

                           if (plugin_geninventorynumber_isGlobalIndexByType($type))
                           $sql = "SELECT next_number FROM glpi_plugin_geninventorynumber_config WHERE FK_entities=$FK_entities";
                           else
                           $sql = "SELECT next_number FROM glpi_plugin_geninventorynumber_indexes WHERE FK_entities=$FK_entities AND field='otherserial' AND type=$type";

                           $result = $DB->query($sql);

                           $objectName = str_replace(array (
                                 $mask,
            '\\_',
            '\\%'
                              ), array (
                                 str_pad($DB->result($result, 0, "next_number"), $len, '0', STR_PAD_LEFT),
            '_',
            '%'
                              ), $autoNum);
                        }
                     }
                     return $objectName;
                  }

function plugin_geninventorynumber_incrementNumber($FK_entities = 0, $type) {
                     global $DB;

                     if (plugin_geninventorynumber_isGlobalIndexByType($type))
                     $sql = "UPDATE glpi_plugin_geninventorynumber_config SET next_number=next_number+1 WHERE FK_entities=$FK_entities";
                     else
                     $sql = "UPDATE glpi_plugin_geninventorynumber_indexes SET next_number=next_number+1 WHERE FK_entities=$FK_entities AND type=$type AND field='otherserial'";
                     $DB->query($sql);
                  }

function plugin_geninventorynumber_isActive($type) {
                     global $INVENTORY_TYPES;
                     $config = plugin_geninventorynumber_getConfig(0);
                     if ($config->fields["active"] && $config->fields["generate_internal"] && $config->fields[$INVENTORY_TYPES[$type] . "_gen_enabled"])
                     return true;
                     else
                     return false;
                  }

function plugin_geninventorynumber_isGlobalIndexByType($type) {
                     global $INVENTORY_TYPES;

                     if (isset ($INVENTORY_TYPES[$type])) {
                        $config = plugin_geninventorynumber_getConfig(0);
                        return $config->fields[$INVENTORY_TYPES[$type] . "_global_index"];
                     }

                     return null;
                  }

function plugin_geninventorynumber_getIndexByTypeName($type)
                  {
                     global $DB,$INVENTORY_TYPES;

                     $type_value = array_search($type,$INVENTORY_TYPES);

                     $query = "SELECT next_number FROM glpi_plugin_geninventorynumber_indexes WHERE type=$type_value";
                     $result = $DB->query($query);
                     return $DB->result($result,0,"next_number");
                  }

function glpi_plugin_geninventorynumber_updateIndexByType($type,$index)
                  {
                     global $DB;
                     $query = "UPDATE glpi_plugin_geninventorynumber_indexes SET next_number=$index WHERE type=$type";
                     $DB->query($query);
                  }

function plugin_geninventorynumber_updateIndexes($params) {
                     global $DB, $INVENTORY_TYPES;

                     if (isset ($params["update"])) {
                        $config = new plugin_geninventorynumberConfig;
                        $config->update($params);

                        //Update each type's index
                        foreach ($INVENTORY_TYPES as $type => $type_name)
                        {
                           if (isset($params["next_number_$type_name"]))
                           glpi_plugin_geninventorynumber_updateIndexByType($type, $params["next_number_$type_name"]);
                        }
                     }
                  }

?>