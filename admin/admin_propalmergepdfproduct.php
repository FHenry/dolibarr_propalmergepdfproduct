<?php
/* <Product - Quote - PDF>
 * Copyright (C) 2013 Florian HENRY <florian.henry@open-concept.pro>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * 	\file		admin/propalmergepdfproduct.php
 * 	\ingroup	propalmergepdfproduct
 * 	\brief		This file is an example module setup page
 * 				Put some comments here
 */
// Dolibarr environment
$res = @include("../../main.inc.php"); // From htdocs directory
if (! $res) {
    $res = @include("../../../main.inc.php"); // From "custom" directory
}


// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once '../lib/propalmergepdfproduct.lib.php';
//require_once "../class/myclass.class.php";
// Translations
$langs->load("propalmergepdfproduct@propalmergepdfproduct");

// Access control
if (! $user->admin) {
    accessforbidden();
}

// Parameters
$action = GETPOST('action', 'alpha');

/*
 * Actions
 */

/*
 * View
 */
$page_name = "PropalMergePdfProductSetup";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">'
    . $langs->trans("BackToModuleList") . '</a>';
print_fiche_titre($langs->trans($page_name), $linkback);

// Configuration header
$head = propalmergepdfproductAdminPrepareHead();
dol_fiche_head(
    $head,
    'settings',
    $langs->trans("Module103026Name"),
    0,
    "propalmergepdfproduct@propalmergepdfproduct"
);

// Setup page goes here
echo $langs->trans("PropalMergePdfProductNothingToSet");

llxFooter();

$db->close();
