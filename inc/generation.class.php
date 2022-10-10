<?php

/**
 * -------------------------------------------------------------------------
 * GenInventoryNumber plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GenInventoryNumber.
 *
 * GenInventoryNumber is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * GenInventoryNumber is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GenInventoryNumber. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2008-2022 by GenInventoryNumber plugin team.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/pluginsGLPI/geninventorynumber
 * -------------------------------------------------------------------------
 */

use Glpi\Toolbox\Sanitizer;

class PluginGeninventorynumberGeneration {

   /**
    * Flag that indicates whether serial update is allowed.
    * Flag is set to true during massive plugin_geninventorynumber_generate/plugin_geninventorynumber_overwrite
    * massive actions process, as this is the only place that should be used to update serial numbers when plugin is active.
    * @var boolean
    */
   private static $serial_update_allowed = false;

   static function autoName($config, CommonDBTM $item) {

      $template = Sanitizer::unsanitize($config['template']);

      $pattern = '/'
          . '^(?<prefix>.*)'    // capture every char located before the "autonum" part
          . '<'                 // "<" char that indicates beginning of the "autonum" part
          . '(?<autonum>'
          . '.*?'               // capture chars located before the # mask part (lazy ? quantifier prevent inclusion of < in "autonum" part)
          . '#{1,10}'           // # mask part
          . '.*?'               // capture chars located after the # mask part (lazy ? quantifier prevent inclusion of > in "autonum" part)
          . ')'
          . '>'                 // ">" char that indicates ending the "autonum" part
          . '(?<suffix>.*)$'    // capture every char located after the "autonum" part
          . '/';
      $matches = [];
      if (preg_match($pattern, $template, $matches) !== 1) {
         return $config['template']; // Return verbatim value
      }

      $prefix  = $matches['prefix'];
      $autonum = $matches['autonum'];
      $suffix  = $matches['suffix'];

      // Find # mask length.
      // autonum par may contains # at multiple places, for instance <#\Y-\m-\d_######>, so we try to find
      // the longer "#" suite.
      $mask = null;
      for ($i = 10; $i > 0; $i--) {
          $mask = str_repeat('#', $i);
          if (str_contains($autonum, str_repeat('#', $i))) {
              break;
          }
      }

      $numero = $config['use_index']
         ? PluginGeninventorynumberConfig::getNextIndex()
         : PluginGeninventorynumberConfigField::getNextIndex($config['itemtype']);

      $autonum = str_replace(
         [
             $mask,
            '\\y',
            '\\Y',
            '\\m',
            '\\d',
            '\\s',
            '\\n',
            '\\g',
         ], [
            Toolbox::str_pad($numero, strlen($mask), '0', STR_PAD_LEFT),
            date('y'),
            date('Y'),
            date('m'),
            date('d'),
            $item->input['serial'] ?? $item->fields['serial'] ?? '',
            $item->input['name'] ?? $item->fields['name'] ?? '',
            '',
         ],
         $autonum
      );

      $result = $prefix . $autonum . $suffix;

      return Sanitizer::sanitize($result);
   }

   /**
    * @override CommonDBTM::preItemAdd
    */
   static function preItemAdd(CommonDBTM $item) {
      $config = PluginGeninventorynumberConfigField::getConfigFieldByItemType(get_class($item));

      if (in_array(get_class($item), PluginGeninventorynumberConfigField::getEnabledItemTypes())) {
         if ((!Session::haveRight("plugin_geninventorynumber", CREATE))) {
            if (!isCommandLine()) {
               Session::addMessageAfterRedirect(__('You can\'t modify inventory number',
                                                'geninventorynumber'), true, ERROR);
            }
            return;
         }

         if (PluginGeninventorynumberConfig::isGenerationActive()
            && PluginGeninventorynumberConfigField::isActiveForItemType(get_class($item))) {
            $item->input['otherserial'] = self::autoName($config, $item);
            if (!isCommandLine()) {
               Session::addMessageAfterRedirect(__('An inventory number have been generated', 'geninventorynumber'), true);
            }

            if ($config['use_index']) {
               PluginGeninventorynumberConfig::updateIndex();
            } else {
               PluginGeninventorynumberConfigField::updateIndex(get_class($item));
            }
         }
      }
   }

   /**
    * @override CommonDBTM::preItemUpdate
    */
   static function preItemUpdate(CommonDBTM $item) {
      $active = PluginGeninventorynumberConfig::isGenerationActive() &&
          PluginGeninventorynumberConfigField::isActiveForItemType(get_class($item));
      if ($active && !self::$serial_update_allowed) {
         if (isset($item->fields['otherserial'], $item->input['otherserial']) &&
             $item->fields['otherserial'] != $item->input['otherserial']) {
            // Revert otherserial to previous value
            $item->input['otherserial'] = $item->fields['otherserial'];
            if (!isCommandLine()) {
               Session::addMessageAfterRedirect(
                  __('You can\'t modify inventory number', 'geninventorynumber'),
                  true, ERROR);
            }
         }
      }
   }

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::showMassiveActionsSubForm()
   **/
   static function showMassiveActionsSubForm(MassiveAction $ma) {
      global $GENINVENTORYNUMBER_TYPES;

      // KK TODO: check if MassiveAction itemtypes are concerned
      //if (in_array ($options['itemtype'], $GENINVENTORYNUMBER_TYPES)) {
      switch ($ma->getAction()) {
         case "plugin_geninventorynumber_generate" :
         case "plugin_geninventorynumber_overwrite" :
            echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"" .
               _sx('button', 'Add') . "\" >";
            break;
         default :
            break;
      }
       return true;
   }

   /**
    * Generate numbers from a massive update
    *
    * @since 9.1+1.0
    *
    * @param CommonDBTM $item Existing item to update
    */
   static function doMassiveUpdate(CommonDBTM $item) {
      if (!in_array(get_class($item), PluginGeninventorynumberConfigField::getEnabledItemTypes())) {
          return false;
      }

      $config = PluginGeninventorynumberConfigField::getConfigFieldByItemType(get_class($item));

      $tmp    = clone $item;
      $values = ['id' => $item->getID()];

      if (PluginGeninventorynumberConfig::isGenerationActive()
         && PluginGeninventorynumberConfigField::isActiveForItemType(get_class($item))) {
         $values['otherserial']   = self::autoName($config, $item);
         $values['massiveaction'] = true;

         self::$serial_update_allowed = true;
         $success = $tmp->update($values);
         self::$serial_update_allowed = false;

         if (!$success) {
            return false;
         }
         if ($config['use_index']) {
            PluginGeninventorynumberConfig::updateIndex();
         } else {
            PluginGeninventorynumberConfigField::updateIndex(get_class($item));
         }
         return true;
      } else {
         $values['otherserial'] = '';
         return $tmp->update($values);
      }
   }

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
   **/
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item,
                                                       array $ids) {

      switch ($ma->getAction()) {
         case "plugin_geninventorynumber_generate" :
         case "plugin_geninventorynumber_overwrite" :
            //KK Not sure we could have multiple itemtimes
            foreach ($ma->getItems() as $itemtype => $ids) {
               foreach ($ids as $id) {
                  $item = new $itemtype();

                  if (!$item->getFromDB($id)) {
                      $ma->itemDone($itemtype, $id, MassiveAction::ACTION_KO);
                      continue;
                  }

                  if ($ma->getAction() == "plugin_geninventorynumber_generate") {
                     //Only generates inventory number for object without it !
                     if (isset ($item->fields["otherserial"]) && ($item->fields["otherserial"] == "")) {
                        if (!Session::haveRight("plugin_geninventorynumber", CREATE)) {
                           $ma->itemDone($itemtype, $id, MassiveAction::ACTION_NORIGHT);
                        } elseif (self::doMassiveUpdate($item)) {
                           $ma->itemDone($itemtype, $id, MassiveAction::ACTION_OK);
                        } else {
                           $ma->itemDone($itemtype, $id, MassiveAction::ACTION_KO);
                        }
                     } else {
                        $ma->itemDone($itemtype, $id, MassiveAction::ACTION_KO);
                     }
                  }

                  //Or is overwrite action is selected
                  if (($ma->getAction() == "plugin_geninventorynumber_overwrite")) {
                     if (!Session::haveRight("plugin_geninventorynumber", UPDATE)) {
                        $ma->itemDone($itemtype, $id, MassiveAction::ACTION_NORIGHT);
                     } elseif (self::doMassiveUpdate($item)) {
                        $ma->itemDone($itemtype, $id, MassiveAction::ACTION_OK);
                     } else {
                        $ma->itemDone($itemtype, $id, MassiveAction::ACTION_KO);
                     }
                  }
               }
            }
            break;

         default :
            break;
      }
   }
}
