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
			
			if ($conf->global->MAIN_MULTILANGS) {
				$lang_id=GETPOST('lang_id');
				$result = $filetomerge->fetch_by_product ( $object->id, $lang_id );
			} else {
				$result = $filetomerge->fetch_by_product ( $object->id );
			}
			
			
			
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
				if (count($filetomerge->lines)>0) {
					$html = $langs->trans ( 'PropalMergePdfProductActualFile' );
				}
				

				$html .= '<form name=\"filemerge\" action=\"' . DOL_URL_ROOT . '/product/document.php?id=' . $object->id . '\" method=\"post\">';
				$html .= '<input type=\"hidden\" name=\"token\" value=\"' . $_SESSION ['newtoken'] . '\">';
				$html .= '<input type=\"hidden\" name=\"action\" value=\"filemerge\">';
				if (count($filetomerge->lines)==0) {
					$html .= $langs->trans ( 'PropalMergePdfProductChooseFile' );
				}
				
				$html .= '<table class=\"noborder\">';
				
				//Get language
				if ($conf->global->MAIN_MULTILANGS) {
					
					$langs->load("languages");
					
					$html .= '<tr><td>';
					
					$delauft_lang=(empty($lang_id))?$langs->getDefaultLang():$lang_id;
					
					$langs_available=$langs->get_available_languages(DOL_DOCUMENT_ROOT,12);
			
					$html.= '<select class=\"flat\" id=\"lang_id\" name=\"lang_id\">';
	
					asort($langs_available);
			
					$uncompletelanguages=array('da_DA','fi_FI','hu_HU','is_IS','pl_PL','ro_RO','ru_RU','sv_SV','tr_TR','zh_CN');
					foreach ($langs_available as $key => $value)
					{
					    if ($showwarning && in_array($key,$uncompletelanguages))
					    {
					        //$value.=' - '.$langs->trans("TranslationUncomplete",$key);
					    }
						if ($filter && is_array($filter))
						{
							if ( ! array_key_exists($key, $filter))
							{
								$html.= '<option value=\"'.$key.'\">'.$value.'</option>';
							}
						}
						else if ($delauft_lang == $key)
						{
							$html.= '<option value=\"'.$key.'\" selected=\"selected\">'.$value.'</option>';
						}
						else
						{
							$html.= '<option value=\"'.$key.'\">'.$value.'</option>';
						}
					}
					$html.= '</select>';
					
					if ($conf->global->MAIN_MULTILANGS) {
						$html .= '<input type=\"submit\" class=\"button\" name=\"refresh\" value=\"' . $langs->trans ( 'Refresh' ) . '\">';
					}
					
					$html .= '</td></tr>';
				}
				
				$style='impair';
				foreach ( $filearray as $filetoadd ) {

					if ($ext = pathinfo ( $filetoadd ['name'], PATHINFO_EXTENSION ) == 'pdf') {
				
						if ($style=='pair') {
							$style='impair';
						}
						else {
							$style='pair';
						}
						
						
						$checked = '';
						$filename=$filetoadd ['name'];
						
						if ($conf->global->MAIN_MULTILANGS) {
							if (array_key_exists($filetoadd ['name'].'_'.$delauft_lang,$filetomerge->lines)) {
								$filename=$filetoadd ['name'].' '.$langs->trans('Language_'.$delauft_lang);
								$checked =' checked=\"checked\" ';
							}
								
						}else {
							if (array_key_exists($filetoadd ['name'],$filetomerge->lines)) {
								$checked =' checked=\"checked\" ';
							}
						}
						
						$html .= '<tr class=\"'.$style.'\"><td>';
						
						$html .= '<input type=\"checkbox\" '.$checked.' name=\"filetoadd[]\" id=\"filetoadd\" value=\"'.$filetoadd ['name'].'\">'.$filename.'</input>';
						$html .= '</td></tr>';

					}
					
					
				}	
				$html .= '<tr><td>';
				
				
				$html .= '<input type=\"submit\" class=\"button\" name=\"save\" value=\"' . $langs->trans ( 'Save' ) . '\">';
				$html .= '</td></tr>';
				$html .= '</table>';		
				
			
				
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
			
			$filetomerge_file_array = GETPOST ( 'filetoadd');
			
			$filetomerge_file_array=GETPOST('filetoadd');
			
			if ($conf->global->MAIN_MULTILANGS) {
				$lang_id=GETPOST('lang_id');
			}
			
			$is_refresh=GETPOST('refresh');
			if (empty($is_refresh)) {
				//Delete all file already associated
				$filetomerge = new Propalmergepdfproduct ( $this->db );
				
				if ($conf->global->MAIN_MULTILANGS) {
					$filetomerge->delete_by_product ($user, $object->id, $lang_id);
				} else {
					$filetomerge->delete_by_product ($user, $object->id);
				}
				
				
				//for each file checked add it to the product
				if (is_array($filetomerge_file_array)) {
					foreach ($filetomerge_file_array as $filetomerge_file) {
						$filetomerge->fk_product = $object->id;
						$filetomerge->file_name = $filetomerge_file;
						
						if ($conf->global->MAIN_MULTILANGS) {
							$filetomerge->lang = $lang_id;
						}
						
						$filetomerge->create ( $user );
					} 
				}
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