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

include ('../../../inc/includes.php');

Session::checkCentralAccess();

$ipp = new \PluginPatchpanelItem_Patchpanel();
$patchPanel = new PluginPatchpanelPatchpanel();

if (isset($_POST['update'])) {
   $ipp->check($_POST['id'], UPDATE);
   //update existing relation
   if ($ipp->update($_POST)) {
      $url = $patchPanel->getFormURLWithID($_POST['pluginpatchpanelpatchpanel_id']);
   } else {
      $url = $ipp->getFormURLWithID($_POST['id']);
   }
   Html::redirect($url);
} else if (isset($_POST['add'])) {

   if (isset($_POST["create_netpoint"])) {
      $patchPanel->getFromDB($_POST['pluginpatchpanelpatchpanel_id']);
      $inputNetpoint = [
         'name' => $patchPanel->fields['name']. ' - '.$_POST["name"],
         'location_id' => $patchPanel->fields['location_id'],
         'comment' => sprintf(__('Auto-created with Patch Panel : %1$s', 'patchpanel'), $patchPanel->fields['name']),
      ];
      $netpoint = new Netpoint();
   }
   if (!isset($_POST["several"])) {
      $ipp->check(-1, CREATE, $_POST);

      if (isset($_POST["create_netpoint"])) {
         $idNetpoint = $netpoint->add($inputNetpoint);
         $_POST['netpoints_id'] = $idNetpoint;
      }

      $newID = $ipp->add($_POST);
      $url = $patchPanel->getFormURLWithID($_POST['pluginpatchpanelpatchpanel_id']);
      Html::redirect($url);
   } else {
      $input = $_POST;
      unset($input['several']);
      unset($input['from_logical_number']);
      unset($input['to_logical_number']);
      unset($input['create_netpoint']);

      for ($i=$_POST["from_logical_number"]; $i<=$_POST["to_logical_number"]; $i++) {
         $add = "";
         if ($i < 10) {
            $add = "0";
         }
         $input["logical_number"] = $i;
         $input["name"]           = $_POST["name"].$add.$i;
         unset($ipp->fields["id"]);

         if (isset($_POST["create_netpoint"])) {
            $inputNetpoint['name'] = $patchPanel->fields['name']. ' - '.$input["name"];
            $idNetpoint = $netpoint->add($inputNetpoint);
            $input['netpoints_id'] = $idNetpoint;
         }

         if ($ipp->can(-1, CREATE, $input)) {
            $ipp->add($input);
         }
      }
      Html::back();
   }
} else if (isset($_POST['purge'])) {
   $ipp->check($_POST['id'], PURGE);
   $ipp->delete($_POST, 1);
   $url = $patchPanel->getFormURLWithID($_POST['pluginpatchpanelpatchpanel_id']);
   Html::redirect($url);
}

$params = [];
if (isset($_GET['id'])) {
   $params['id'] = $_GET['id'];
} else {
   $params = [
      'pluginpatchpanelpatchpanel_id'     => $_GET['pluginpatchpanelpatchpanel_id'],
      'several' => isset($_GET['several']) ? $_GET['several'] : false,
   ];
}
$ajax = isset($_REQUEST['ajax']) ? true : false;

if (!$ajax) {
   Html::header(PluginPatchpanelPatchpanel::getTypeName(Session::getPluralNumber()), $_SERVER['PHP_SELF'], "assets", "pluginpatchpanelpatchpanel", "patchpanel");
}
$ipp->display($params);
if (!$ajax) {
   Html::footer();
}
