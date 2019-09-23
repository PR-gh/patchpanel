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

use Glpi\Event;

include ('../../../inc/includes.php');

Session::checkRight("networking", READ);

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}
if (!isset($_GET["withtemplate"])) {
   $_GET["withtemplate"] = "";
}

$patchpanel = new PluginPatchpanelPatchpanel();
if (isset($_POST["add"])) {
   $patchpanel->check(-1, CREATE, $_POST);

   if ($newID = $patchpanel->add($_POST)) {
      Event::log($newID, "patchpanel", 4, "inventory",
                 sprintf(__('%1$s adds the item %2$s'), $_SESSION["glpiname"], $_POST["name"]));
      if ($_SESSION['glpibackcreated']) {
         Html::redirect($patchpanel->getLinkURL());
      }
   }
   Html::back();

} else if (isset($_POST["delete"])) {
   $patchpanel->check($_POST["id"], DELETE);
   $patchpanel->delete($_POST);

   Event::log($_POST["id"], "patchpanel", 4, "inventory",
              //TRANS: %s is the user login
              sprintf(__('%s deletes an item'), $_SESSION["glpiname"]));

   $patchpanel->redirectToList();

} else if (isset($_POST["restore"])) {
   $patchpanel->check($_POST["id"], DELETE);

   $patchpanel->restore($_POST);
   Event::log($_POST["id"], "patchpanel", 4, "inventory",
              //TRANS: %s is the user login
              sprintf(__('%s restores an item'), $_SESSION["glpiname"]));
   $patchpanel->redirectToList();

} else if (isset($_POST["purge"])) {
   $patchpanel->check($_POST["id"], PURGE);

   $patchpanel->delete($_POST, 1);
   Event::log($_POST["id"], "patchpanel", 4, "inventory",
              //TRANS: %s is the user login
              sprintf(__('%s purges an item'), $_SESSION["glpiname"]));
   $patchpanel->redirectToList();

} else if (isset($_POST["update"])) {
   $patchpanel->check($_POST["id"], UPDATE);

   $patchpanel->update($_POST);
   Event::log($_POST["id"], "patchpanel", 4, "inventory",
              //TRANS: %s is the user login
              sprintf(__('%s updates an item'), $_SESSION["glpiname"]));
   Html::back();

} else {
   Html::header(PluginPatchPanelPatchpanel::getTypeName(Session::getPluralNumber()), $_SERVER['PHP_SELF'], "assets", "patchpanel");
   $patchpanel->display(['id'           => $_GET["id"],
                             'withtemplate' => $_GET["withtemplate"]]);
   Html::footer();
}
