<?php
/* <Product - Quote - PDF>
 * Copyright (C) 2013 Florian HENRY <florian.henry@open-concept.pro>
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * \file /propalmergepdfproduct/class/actions_propalmergepdfproduct.class.php
 * \ingroup propalmergepdfproduct
 * \brief File of class to manage propalmergepdfproduct hook action
 */

/**
 * \class ActionsPropalMergePdfProduct
 * \brief Class to manage Mailchimp
 */
class ActionsPropalMergePdfProduct {
	var $db;
	var $error;
	var $errors = array ();

	/**
	 * Constructor
	 *
	 * @param DoliDB $db
	 */
	function __construct($db) {

		$this->db = $db;
		$this->error = 0;
		$this->errors = array ();
	}

	/**
	 * formObjectOptions Method Hook Call
	 *
	 * @param array $parameters parameters
	 * @param Object	&$object			Object to use hooks on
	 * @param string	&$action			Action code on calling page ('create', 'edit', 'view', 'add', 'update', 'delete'...)
	 * @param object $hookmanager class instance
	 * @return void
	 */
	function formObjectOptions($parameters, &$object, &$action, $hookmanager) {

		global $langs, $conf, $user;
		
		$langs->load ( 'propalmergepdfproduct@propalmergepdfproduct' );
		
		dol_syslog ( get_class ( $this ) . ':: formObjectOptions', LOG_DEBUG );
		
		/*dol_syslog ( get_class ( $this ) . ':: $hookmanager->contextarray=' . var_export ( $hookmanager->contextarray, true ), LOG_DEBUG );
		dol_syslog ( get_class ( $this ) . ':: $action=' . $action, LOG_DEBUG );
		dol_syslog ( get_class ( $this ) . ':: $object->table_element=' . $object->table_element, LOG_DEBUG );
		dol_syslog ( get_class ( $this ) . ':: $object->id=' . $object->id, LOG_DEBUG );*/
		
		// Add javascript Jquery to add button Select doc form
		if ($object->table_element == 'product' && ! empty ( $object->id )) {
			
			require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';
			dol_include_once ( '/propalmergepdfproduct/class/propalmergepdfproduct.class.php' );
			
			$filetomerge = new Propalmergepdfproduct ( $this->db );
			$filetomerge->fetch_by_product ( $object->id );
			
			$form = new Form ( $db );
			
			if (! empty ( $conf->product->enabled ))
				$upload_dir = $conf->product->multidir_output [$object->entity] . '/' . dol_sanitizeFileName ( $object->ref );
			elseif (! empty ( $conf->service->enabled ))
				$upload_dir = $conf->service->multidir_output [$object->entity] . '/' . dol_sanitizeFileName ( $object->ref );
			
			$filearray = dol_dir_list ( $upload_dir, "files", 0, '', '\.meta$', 'name', SORT_ASC, 1 );
			
			// For each file build select list with PDF extention
			if (count ( $filearray ) > 0) {
				$html = '<BR><BR>';
				// Actual file to merge is :
				if (! empty ( $filetomerge->id )) {
					$html = $langs->trans ( 'PropalMergePdfProductActualFile' ) . ':<b>' . $filetomerge->file_name . '</b>';
				}
				
				// $html .= '<form name=\"filemerge\" action=\"' . dol_buildpath('/propalmergepdfproduct/propalmergepdfproduct.php',1) . '\"
				// method=\"post\">';
				$html .= '<form name=\"filemerge\" action=\"' . DOL_URL_ROOT . '/product/document.php?id=' . $object->id . '\" method=\"post\">';
				$html .= '<input type=\"hidden\" name=\"token\" value=\"' . $_SESSION ['newtoken'] . '\">';
				$html .= '<input type=\"hidden\" name=\"action\" value=\"filemerge\">';
				$html .= $langs->trans ( 'PropalMergePdfProductChooseFile' );
				$html .= '<select name=\"filetoadd\" id=\"filetoadd\" class=\"flat\">';
				$html .= '<option value=\"\"></option>';
				foreach ( $filearray as $filetoadd ) {
					if ($ext = pathinfo ( $filetoadd ['name'], PATHINFO_EXTENSION ) == 'pdf') {
						
						$selected = '';
						if ($filetomerge->file_name == $filetoadd ['name'])
							$selected = ' selected=\"selected\" ';
						
						$html .= '<option value=\"' . $filetoadd ['name'] . '\" ' . $selected . '>' . $filetoadd ['name'] . '</option>';
					}
				}
				$html .= '</select>';
				$html .= '<input type=\"submit\" class=\"button\" value=\"' . $langs->trans ( 'Save' ) . '\">';
				$html .= '</form>';
				
				print '<script type="text/javascript">jQuery(document).ready(function () {jQuery(function() {jQuery(".fiche").append("' . $html . '");});});</script>';
			}
		}
	}

	/**
	 * Return action of hook
	 *
	 * @param array $parameters
	 * @param object $object
	 * @param string $action
	 * @param object $hookmanager class instance
	 * @return void
	 */
	function doActions($parameters = false, &$object, &$action = '', $hookmanager) {

		dol_syslog ( get_class ( $this ) . ':: doActions', LOG_DEBUG );
		
		global $langs, $conf, $user;
		
		if ($object->table_element == 'product' && ! empty ( $object->id ) && $action == 'filemerge') {
			
			dol_include_once ( '/propalmergepdfproduct/class/propalmergepdfproduct.class.php' );
			
			$filetomerge_file = GETPOST ( 'filetoadd', 'alpha' );
			
			$filetomerge = new Propalmergepdfproduct ( $this->db );
			$filetomerge->fetch_by_product ( $object->id );
			
			// If we do not find a row for this product
			if (empty ( $filetomerge->id )) {
				// We create it
				$filetomerge->fk_product = $object->id;
				$filetomerge->file_name = $filetomerge_file;
				$filetomerge->create ( $user );
			} elseif (! empty ( $filetomerge_file )) {
				// If file to merge is specified and the product already exists we update file
				$filetomerge->fk_product = $object->id;
				$filetomerge->file_name = $filetomerge_file;
				$filetomerge->update ( $user );
			} else {
				// It mean the user do not want anymore the file to add
				// We delete it
				$filetomerge->delete ( $user );
			}
		}
		return 0;
	}

	/**
	 * Return action of hook
	 *
	 * @param array $parameters
	 * @param object $object
	 * @param string $action
	 * @param object $hookmanager class instance
	 * @return void
	 */
	function afterPDFCreation($parameters = false, &$object, &$action = '', $hookmanager) {

	}
}