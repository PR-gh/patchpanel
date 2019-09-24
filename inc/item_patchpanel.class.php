<?php
/**
 * -------------------------------------------------------------------------
 * PatchPanel plugin for GLPI
 * Copyright (C) 2019 by the PatchPanel Development Team.
 *
 * https://github.com/pluginsGLPI/patchpanel
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of PatchPanel.
 *
 * PatchPanel is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * PatchPanel is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PatchPanel. If not, see <http://www.gnu.org/licenses/>.
 * --------------------------------------------------------------------------
 */

class PluginPatchpanelItem_Patchpanel extends CommonDBRelation {

   static public $itemtype_1 = 'PluginPatchpanelPatchpanel';
   static public $items_id_1 = 'pluginpatchpanelpatchpanel_id';
   static public $itemtype_2 = 'itemtype';
   static public $items_id_2 = 'items_id';
   static public $checkItem_1_Rights = self::DONT_CHECK_ITEM_RIGHTS;
   static public $mustBeAttached_1      = false;
   static public $mustBeAttached_2      = false;

   static function getTypeName($nb = 0) {
       return _n('Item', 'Item', $nb);
   }

   /**
    * @see CommonDBTM::prepareInputForAdd
    */
   function prepareInputForAdd($input) {
      if (isset($input["logical_number"]) && (strlen($input["logical_number"]) == 0)) {
         unset($input["logical_number"]);
      }
      return parent::prepareInputForAdd($input);
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      global $CFG_GLPI;
      $nb = 0;
      if ($_SESSION['glpishow_count_on_tabs'] && $item->getType() != Netpoint::getType()) {
         switch ($item->getType()) {
            case 'PluginPatchpanelPatchpanel':
               $nb = countElementsInTable(
                  self::getTable(),
                  ['pluginpatchpanelpatchpanel_id'  => $item->getID()]);
            default:
               if (in_array($item->getType(), $CFG_GLPI['networkport_types'])) {
                  $nb = countElementsInTable(
                     NetworkPort::getTable(),
                     ['itemtype' => $item->getType(), 'items_id' => $item->getID()]);
               }
         }
      }
      return self::createTabEntry(PluginPatchpanelPatchpanel::getTypeName(Session::getPluralNumber()), $nb);
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      self::showItems($item, $withtemplate);
   }

   function getRawName() {
      return $this->fields['name'];
   }

   /**
    * Print patchpanel form
    */
   function showForm($ID, $options = []) {
      global $CFG_GLPI;

      if (!isset($options['several'])) {
         $options['several'] = false;
      }

      if (!self::canView()) {
         return false;
      }
      $options['entities_id'] = $_SESSION['glpiactive_entity'];

      $this->initForm($ID, $options);
      $this->showFormHeader($options);
      echo "<input type='hidden' name='pluginpatchpanelpatchpanel_id' value='".$this->fields["pluginpatchpanelpatchpanel_id"]."'>\n";

      echo "<tr class='tab_bg_1'><td>" . __('Name') . "</td>\n";
      echo "<td>";
      Html::autocompletionTextField($this, "name");
      echo "</td>";
      if (!$options['several']) {
         echo "<td>". _n('Port number', 'Ports number', 1) ."</td>\n";
         echo "<td>";
         Html::autocompletionTextField($this, "logical_number", ['size' => 5]);
         echo "</td></tr>\n";
      } else {
         echo "<td>". _n('Port number', 'Port numbers', Session::getPluralNumber()) ."</td>\n";
         echo "<td>";
         echo "<input type='hidden' name='several' value='yes'>";
         echo "<input type='hidden' name='logical_number' value=''>\n";
         echo __('from') . "&nbsp;";
         Dropdown::showNumber('from_logical_number', ['value' => 0]);
         echo "&nbsp;".__('to') . "&nbsp;";
         Dropdown::showNumber('to_logical_number', ['value' => 0]);
         echo "</td></tr>\n";
      }
      echo "<tr class='tag_bg_1'><th colspan=4>".__("Patch Panel Network Outlet", 'patchpanel')."</th></tr>";
      if (!$options['several']) {
         echo "<tr class='tab_bg_1'><td>". __('Network outlet') . "</td>\n";
         echo "<td>";
         Netpoint::dropdownNetpoint("netpoints_id", $this->fields["netpoints_id"]);
         echo "</td>";
      }
      if ($this->isNewID($ID)) {
         echo "<td>". __('Auto Create Netpoint', 'patchpanel') . "</td>\n";
         echo "<td><input type='checkbox' name='create_netpoint' value='yes' /></td>\n";
         if (!$options['several']) {
            echo "<td colspan=2></td>";
         }
      } else {
            echo "<td colspan=2></td>";
      }
      if ($options['several']) {
         echo "<td colspan=2></td></tr>";
      } else {
         echo "</tr><tr class='tag_bg_1'><th colspan=4>".__("Patch Panel Network Outlet - Opposite", 'patchpanel')."</th></tr>";
         echo "<td>". __('Item') . "</td>\n";
         echo "<td colspan=3>";
         self::showDropdownItem($this->fields);
         echo "</td></tr>\n";
      }

      $this->showFormButtons($options);
   }

   /**
    * Print Dropdown to Choose ItemType and ItemId of Opposite NetworkOutlet
    * @return void
    */
   static function showDropdownItem(Array $ipp) {
      global $CFG_GLPI;

      $rand = mt_rand();
      $paramPrimalType = ['rand' => $rand];
      $paramParent_Id = ['rand' => $rand, 'name' => 'parent'];
      $paramItems_id = ['rand' => $rand, 'name' => 'items_id'];
      $itemtype = NetworkPort::getType();

      echo "<span id='show_$rand'>";
      // Init Form
      if (!empty($ipp['itemtype']) && !empty($ipp['items_id']) && $ipp['items_id'] > 0) {
         $paramPrimalType['value'] = $ipp['itemtype'];
         $item = new $ipp['itemtype'];
         if ($item->getFromDB($ipp['items_id'])) {
            $paramItems_id['value'] = $ipp['items_id'];
            if ($paramPrimalType['value'] == NetworkPort::getType()) {
               $paramPrimalType['value'] = $item->fields['itemtype'];
               $paramParent_Id['value'] = $item->fields['items_id'];
            }
         }
         // Ajax
      } else if (!empty($ipp['item_type_parent'])) {
         $paramPrimalType['value'] = $ipp['item_type_parent'];
         if (!empty($ipp['parent'])) {
            $paramParent_Id['value'] = $ipp['parent'];
         }
         if (!empty($ipp['items_id'])) {
            $paramItems_id['value'] = $ipp['items_id'];
         }
      }

      $itemsTypeParent = array_merge(['Netpoint'], $CFG_GLPI['networkport_types']);
      $rand = Dropdown::showItemTypes("item_type_parent", $itemsTypeParent, $paramPrimalType);

      Ajax::updateItemOnSelectEvent(
         "dropdown_item_type_parent$rand",
         "show_$rand",
         $CFG_GLPI["root_doc"]."/plugins/patchpanel/ajax/getDropdownItem_Patchpanel.php",
         [  'item_type_parent'  => '__VALUE__',
            'id'        => $ipp['id']]);

      if (isset($paramPrimalType['value'])) {
         if ($paramPrimalType['value'] != Netpoint::getType()) {
            Dropdown::show($paramPrimalType['value'], $paramParent_Id);

            $functionJS_item_type_parent = Html::jsGetElementbyID(Html::cleanId('dropdown_item_type_parent'.$rand)).".val()";
            $retAjax = Ajax::updateItemOnSelectEvent(
               "dropdown_parent$rand",
               "show_$rand",
               $CFG_GLPI["root_doc"]."/plugins/patchpanel/ajax/getDropdownItem_Patchpanel.php",
               [  'item_type_parent'  => $functionJS_item_type_parent,
                  'parent'  => '__VALUE__',
                  'id'        => $ipp['id']],
               false);
            $retAjax = str_replace("\"$functionJS_item_type_parent\"", $functionJS_item_type_parent, $retAjax);
            echo $retAjax;

            if (isset($paramParent_Id['value'])) {
               $paramItems_id['condition'] = self::getNetportsDropdownArray($paramPrimalType['value'], $paramParent_Id['value'], isset($paramItems_id['value']) ? $paramItems_id['value'] : -1);
            }
         } else {
            $itemtype = Netpoint::getType();
            $paramItems_id['condition'] = self::getConditionNetpointDropdownArray(isset($paramItems_id['value']) ? $paramItems_id['value'] : -1);
         }
         if (isset($paramItems_id['condition'])) {
            Dropdown::show($itemtype, $paramItems_id);
         }
         echo "<input type='hidden' name='itemtype' value='$itemtype' />";
      }

      echo "</span>";
   }

   /**
    * List avalaible Netpoint
    * @param int $idNetpoint
    * @return Array
    */
   static function getConditionNetpointDropdownArray($idNetpoint = -1) {
      $condition = [
         'NOT' => [
            'id' => new \QuerySubQuery([
               'SELECT' => 'items_id',
               'FROM'   => self::getTable(),
               'WHERE'  => [
                  'itemtype'  => Netpoint::getType()
               ]
            ])
         ]
      ];
      if ($idNetpoint > 0) {
         $condition = [
            'OR'  => [
               'id'  => $idNetpoint,
               'NOT' => $condition['NOT']
            ]
         ];
      }
      return $condition;
   }

   /**
    * List avalaible Netports
    * @param string $parentType
    * @param int $parentId
    * @param int $idNetports
    * @return Array
    */
   static function getNetportsDropdownArray($parentType, $parentId, $idNetports = -1) {
      $condition = [
         'itemtype'  => $parentType,
         'items_id'  => $parentId,
      ];
      $in = [
         'id'  => new \QuerySubQuery([
            'SELECT' => 'items_id',
            'FROM'   => self::getTable(),
            'WHERE'  => [
               'itemtype'  => NetworkPort::getType()
            ]
         ])
      ];
      if ($idNetports > 0) {
         $condition[] = [
            'OR'  => [
               'NOT' => $in,
               'id'  => $idNetports
            ]
         ];
      } else {
         $condition[] = ['NOT' => $in];
      }
      return $condition;
   }

   /**
    * Print Items
    * @param  Object  $item the current patchpanel instance
    * @return void
    */
   static function showItems ($item) {
      global $CFG_GLPI;
      switch ($item->getType()) {
         case 'PluginPatchpanelPatchpanel':
            self::showItemsPatchPanel($item);
            break;
         case 'Netpoint':
            self::showItemsNetpoint($item);
            break;
         default:
            if (in_array($item->getType(), $CFG_GLPI['networkport_types'])) {
               self::showItemsNetworkPort($item);
            } else {
               return __('Not implemented.', 'patchpanel');
            }
      }
   }

   /**
    * Print patchpanel item for NetworkPort
    * @param Object $item
    * @return void
    */
   static function showItemsNetworkPort($item) {
      global $CFG_GLPI, $DB;

      $itemtype = $item->getType();
      $items_id = $item->getID();
      $netport  = new NetworkPort();
      $ipp = new self();
      $pp = new PluginPatchpanelPatchpanel();

      echo '<h1>'.PluginPatchpanelPatchpanel::getTypeName().'</h1>';
      echo '<table class="tab_cadre_fixehov">';
      foreach ($CFG_GLPI['networkport_instantiations'] as $portType) {
         if ($itemtype != 'NetworkPort') {
            $query = "SELECT n.`id`, ipp.id as idIpp
                     FROM `glpi_networkports` n
                     LEFT OUTER JOIN ".self::getTable()." ipp ON n.id = ipp.items_id AND ipp.itemtype = '".NetworkPort::getType()."'
                     WHERE n.`items_id` = '$items_id'
                           AND n.`itemtype` = '$itemtype'
                           AND n.`instantiation_type` = '$portType'
                           AND n.`is_deleted` = 0
                     ORDER BY n.`logical_number`, n.`name`";
            if ($result = $DB->request($query)) {
               $number_port = $result->numrows();
               if ($number_port != 0) {
                  echo '<thead>';
                  echo '<tr class="tab_bg_1"><th colspan=7>'.$portType::getTypeName(Session::getPluralNumber()).'</th></tr>';
                  echo '<tr class="tab_bg_1"><th>#</th><th>'.__('Name').'</th><th>'.__('Physical Connection', 'patchpanel').'</th><th>'.__('Logical Connection', 'patchpanel').'</th></tr>';
                  echo '</thead>';
                  echo '<tbody>';
                  while ($devid = $result->next()) {
                     echo '<tr class="tab_bg_1">';
                     $netport->getFromDB($devid['id']);
                     $content = "<span class='b'>";
                     // Display link based on default rights
                     $content .= "<a href=\"" . NetworkPort::getFormURLWithID($netport->fields["id"]) ."\">";
                     $content .= $netport->fields["logical_number"];
                     $content .= "</a>";
                     $content .= "</span>";
                     $content .= Html::showToolTip($netport->fields['comment'], ['display' => false]);
                     echo "<td>$content</td>";
                     echo "<td>".$netport->fields['name']."</td>";
                     echo "<td>";
                     if ($devid['idIpp'] != null) {
                        $ipp->getFromDB($devid['idIpp']);
                        $pp->getFromDB($ipp->fields['pluginpatchpanelpatchpanel_id']);
                        $link = '<a href="'.self::getFormURLWithID($ipp->getID()).'">';
                        echo sprintf(__('%1$s on %2$s'), $link.$ipp->getLink().'</a>', "<span class='b'>".$pp->getLink()."</span>");
                     } else {
                        echo __('Not connected.');
                     }
                     echo "</td>";
                     echo "<td>";
                     NetworkPortInstantiation::showConnection($netport);
                     echo "</td>";
                     echo "</tr>";
                  }
                  echo '</tbody>';
               }
            }
         }
      }
      echo '</table>';
   }

   /**
    * Print patchpanel items for Netpoint
    * @param Netpoint $netpoint
    * @return void
    */
   static function showItemsNetpoint(Netpoint $netpoint) {
      echo '<table class="tab_cadre_fixehov">';
      echo '<thead><th>'.__('Patch Panel', 'patchpanel').'</th><th>'.__('Opposite Patch Panel', 'patchpanel').'</th></thead>';
      echo '<tbody><tr><td>';
      $ipp = new self();
      $pp = new PluginPatchpanelPatchpanel();
      if ($ipp->getFromDBByCrit(['netpoints_id' => $netpoint->getID()])) {
         $pp->getFromDB($ipp->fields['pluginpatchpanelpatchpanel_id']);
         $link = '<a href="'.self::getFormURLWithID($ipp->getID()).'">';
         echo "&nbsp;". sprintf(__('%1$s on %2$s'), $link.$ipp->getLink().'</a>', "<span class='b'>".$pp->getLink()."</span>");
      } else {
         echo __('Not connected.');
      }
      echo '</td><td>';
      self::showOpposite($ipp->fields);
      echo '</td></tr></tbody>';
      echo '</table>';
   }

   /**
    * Print patchpanel items fort Patchpanel
    * @param  PluginPatchpanelPatchpanel   $patchpanel the current patchpanel instance
    * @return void
    */
   static function showItemsPatchPanel(PluginPatchpanelPatchpanel $patchpanel) {
      global $DB, $CFG_GLPI;

      $ID = $patchpanel->getID();
      $rand = mt_rand();

      if (!$patchpanel->getFromDB($ID)
          || !$patchpanel->can($ID, READ)) {
         return false;
      }
      $canedit = $patchpanel->canEdit($ID);

      $items = $DB->request([
         'FROM'   => self::getTable(),
         'WHERE'  => [
            'pluginpatchpanelpatchpanel_id' => $patchpanel->getID()
         ],
         'ORDER' => 'logical_number ASC'
      ]);
      $link = new self();

      if ($canedit) {
         Session::initNavigateListItems(
            self::getType(),
            //TRANS : %1$s is the itemtype name,
            //        %2$s is the name of the item (used for headings of a list)
            sprintf(
               __('%1$s = %2$s'),
               $patchpanel->getTypeName(1),
               $patchpanel->getName()
            )
         );
         echo "\n<form method='get' action='" . $link->getFormURL() ."'>\n";
         echo "<input type='hidden' name='pluginpatchpanelpatchpanel_id' value='".$patchpanel->getID()."'>\n";
         echo "<div class='firstbloc'><table class='tab_cadre_fixe'>\n";
         echo "<tr class='tab_bg_2'><td class='center'>\n";
         echo __('Add Patch Panel Port', 'patchpanel');
         echo "&nbsp;";
         echo "</td>\n";
         echo "<td class='tab_bg_2 center' width='50%'>";
         echo __('Add several ports');
         echo "&nbsp;<input type='checkbox' name='several' value='1'></td>\n";
         echo "<td>\n";
         echo "<input type='submit' name='create' value=\""._sx('button', 'Add')."\" class='submit'>\n";
         echo "</td></tr></table></div>\n";
         Html::closeForm();
      }
      echo "<table class='tab_cadre_fixehov'>";
      echo "<thead>";
      echo "<tr class='noHover'><th colspan=4>".__("Patch Panel Ports", 'patchpanel')." - ".$items->numrows()."</th></tr>";
      echo "</thead>";
      echo "<tbody>";
      echo "<tr class='tab_bg_1'>";
      echo "<td class='subheader'>#</td>";
      echo "<td class='subheader'>".__("Name")."</td>";
      echo "<td class='subheader'>".__("Network outlet")."</td>";
      echo "<td class='subheader'>".__("Opposite", 'patchpanel')."</td>";
      echo "</tr>";
      $netpoint = new Netpoint();
      while ($ipp = $items->next()) {
         echo "<tr class='tab_bg_2'>";
         echo "<td>";
         if ($canedit) {
            echo "<a href=\"" . PluginPatchpanelItem_Patchpanel::getFormURLWithID($ipp["id"]) ."\">".$ipp['logical_number']."</a>";
         } else {
            echo $ipp['logical_number'];
         }
         echo "</td>";
         echo "<td>".$ipp['name']."</td>";
         echo "<td>";
         if (empty($ipp['netpoints_id']) || $ipp['netpoints_id'] < 0) {
            echo __('Not connected.');
         } else if ($netpoint->getFromDB($ipp['netpoints_id'])) {
            echo "<a href=\"".$netpoint->getLinkURL()."\">".$netpoint->getName()."</a>";
         } else {
            echo __("Link broken", 'patchpanel');
         }
         echo "</td>";
         echo "<td>";
         self::showOpposite($ipp);
         echo "</td></tr>";
      }
      echo "</table>";
   }

   /**
    * Show Opposite
    * @param Array $itemArray data from sql or $ipp->fields
    * @return void
    */
   static function showOpposite (Array $itemArray) {
      if (empty($itemArray['itemtype']) || empty($itemArray['items_id'])) {
         echo __('Not connected.');
      } else {
         $item = new $itemArray['itemtype'];
         if ($item->getFromDB($itemArray['items_id'])) {
            $netlink = $item->getLink();
            $tooltip     = Html::showToolTip($item->fields['comment'], ['display' => false]);
            $netlink     = sprintf(__('%1$s %2$s'),
                              "<span class='b'>".$netlink."</span>\n", $tooltip);

            if ($itemArray['itemtype'] == NetworkPort::getType()) {
               echo "&nbsp;". sprintf(__('%1$s on %2$s'), $netlink, "<span class='b'>".$item->getItem()->getLink()."</span>");
            } else {
               $pp = self::getOppositePatchPanelById($itemArray['id']);
               if ($pp !== false) {
                  echo "&nbsp;". sprintf(__('%1$s on %2$s'), $netlink, "<span class='b'>".$pp->getLink()."</span>");
               } else {
                  echo $netlink;
               }
            }
         } else {
            echo __("Link broken", 'patchpanel');
         }
      }
   }

   /**
    * Get the oppositePatchPanel or false
    * @param int $idNetpoint
    * @return PluginPatchpanelPatchpanel
    */
   static function getOppositePatchPanelById (int $id) {
      global $DB;
      $items = $DB->request([
         'FROM'   => self::getTable(),
         'WHERE'  => [
            'netpoints_id'  => new \QuerySubQuery([
               'SELECT' => 'items_id',
               'FROM'   => self::getTable(),
               'WHERE'  => [
                  'id'        => $id,
                  'itemtype'  => Netpoint::getType()
               ]
            ])
         ]
      ]);
      if ($items->count() === 1) {
         $pp = new PluginPatchpanelPatchpanel();
         $items->next();
         if ($pp->getFromDB($items->current()['pluginpatchpanelpatchpanel_id'])) {
            return $pp;
         }
      }
      return false;
   }
}