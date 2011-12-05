<?php

/*
 * @version $Id$
 -------------------------------------------------------------------------
 geninventorynumber - plugin for GLPI
 Copyright (C) 2003-2011 by the geninventorynumber Development Team.

 https://forge.indepnet.net/projects/geninventorynumber
 -------------------------------------------------------------------------

 LICENSE

 This file is part of geninventorynumber plugin.

 geninventorynumber is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 geninventorynumber is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with webservices. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 --------------------------------------------------------------------------
 @package   order
 @author    Walid Nouh
 @copyright Copyright (c) 2010-2011 Walid Nouh
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/geninventorynumber
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */


if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginGeninventorynumberCommon extends CommonDBTM {

   /**
   * Deletes an inventory number if user-defined when generation is active
   *
   * @param object CommonDBTM object to be updated
   * @return   null
   */
   function preUpdateItem(CommonDBTM $item) {
      global $LANG;
   
      $fields = plugin_geninventorynumber_getFieldInfos('otherserial');
      $template = addslashes_deep($fields[$item->getType()]['template']);
      if ($fields[$item->getType()]['is_active'] && $fields[$item->getType()]['template'] != '') {
         if (isset ($item->input["otherserial"])) {
            unset ($item->input["otherserial"]);
            Session::addMessageAfterRedirect($LANG["plugin_geninventorynumber"]["massiveaction"][2], 
                                             false, ERROR);
          }
      }
      return $item;
   }

   /**
   * Generates a number for the object juste added
   *
   * @param object   CommonDBTM object just added
   * @return   null
   */
   function addItem($item) {
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
   
               $generated_field = PluginGeninventorynumberCommon::autoName($template, $type, 0, $commonitem->fields, $fields);
   
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
   * Calculates generated field
   * 
   * @param string   field format (model)
   * @param string   type of the context object
   * @param int      ID of the entity for context object
   * @param array Field list from object
   * @param    array List of fields configured in plugin for the context object (configfields table)
   * @return   string   generated value
   */
   function autoName($objectName, $type, $entities_id = 0, $fields, $field_params = array ()) {
      global $DB;
   
      $len = strlen($objectName);
      if ($len > 8 && substr($objectName, 0, 4) === '&lt;' 
         && substr($objectName, $len -4, 4) === '&gt;') {
   
         $autoNum = substr($objectName, 4, $len -8);
         $mask = '';
         if (preg_match("/\\#{1,10}/", $autoNum, $mask)) {
            $serial = (isset ($fields['serial']) ? $fields['serial'] : '');
   
            $global  = strpos($autoNum, '\\g') !== false && $type != 'Infocom' ? 1 : 0;
            $autoNum = str_replace(array ('\\y', '\\Y', '\\m', '\\d', '_', '%', '\\g', '\\s'), 
                                   array (date('y'), date('Y'), date('m'), date('d'), '\\_', '\\%', '',
                                          $serial), $autoNum);
            $mask    = $mask[0];
            $pos     = strpos($autoNum, $mask) + 1;
            $len     = strlen($mask);
            $like    = str_replace('#', '_', $autoNum);
   
            if ($field_params[$type]['use_index']) {
               $next = PluginGeninventorynumberConfig::getNextIndex($entities_id, 'otherserial');
            } else {
               $next = PluginGeninventorynumberConfig::getNextIndex($entities_id, 'otherserial', $type);
            }
   
            $objectName = str_replace(array ($mask, '\\_', '\\%'), 
                                      array (str_pad($next, $len, '0', STR_PAD_LEFT), '_', '%'), 
                                      $autoNum);
         }
      }
      return $objectName;
   }

}