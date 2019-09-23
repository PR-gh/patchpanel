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

$plugin = new Plugin();
if (!$plugin->isInstalled('PatchPanel') || !$plugin->isActivated('PatchPanel')) {
   Html::displayNotFoundError();
}

Session::checkRight("networking", READ);

Html::header(PluginPatchpanelPatchpanel::getTypeName(Session::getPluralNumber()), $_SERVER['PHP_SELF'], "assets", "pluginpatchpanelpatchpanel", "patchpanel");

Search::show('PluginPatchpanelPatchpanel');

Html::footer();
