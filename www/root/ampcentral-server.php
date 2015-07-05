<?php
/*
 *   AmpCentral Server
 *
 *   Copyright (C) 2000-2004 Solarix
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 */

// Initialization
//
require ('auth.php');

OpenLibrary('hui.library');
OpenLibrary('ampcentral-server.library');
OpenLibrary('locale.library');
OpenLibrary('ampshared.library');

$gHui = new Hui($gEnv['root']['db']);
$gHui -> LoadWidget('xml');
$gHui -> LoadWidget('amppage');
$gHui -> LoadWidget('amptoolbar');

$gLocale = new Locale('ampcentral-server_root_server', $gEnv['root']['locale']['language']);

$gPage_content = $gStatus = $gToolbars = $gXml_def = '';
$gPage_title = $gLocale -> GetStr('ampcentral-server.title');

$gMenu = get_ampoliros_root_menu_def($gEnv['root']['locale']['language']);

$gToolbars['repository'] = array('repository' => array('label' => $gLocale -> GetStr('repository.toolbar'), 'themeimage' => 'view_text', 'action' => build_events_call_string('', array(array('main', 'default', '')))), 'newrepository' => array('label' => $gLocale -> GetStr('newrepository.toolbar'), 'themeimage' => 'filenew', 'action' => build_events_call_string('', array(array('main', 'newrepository', '')))));

$gToolbars['modules'] = array('modules' => array('label' => $gLocale -> GetStr('modules.toolbar'), 'themeimage' => 'view_detailed', 'action' => build_events_call_string('', array(array('main', 'modules', '')))), 'newrepository' => array('label' => $gLocale -> GetStr('newmodule.toolbar'), 'themeimage' => 'filenew', 'action' => build_events_call_string('', array(array('main', 'newmodule', '')))));

// Action dispatcher
//
$gAction_disp = new HuiDispatcher('action');

// ----- Repositories ------
//
$gAction_disp -> AddEvent('newrepository', 'action_newrepository');
function action_newrepository($eventData) {
	global $gEnv, $gLocale, $gStatus;

	$rep = new AmpCentralRepository($gEnv['root']['db']);
	if ($rep -> Create($eventData['name'], $eventData['description'], $eventData['logevents'] == 'on' ? true : false))
		$gStatus = $gLocale -> GetStr('repository_created.status');
	else
		$gStatus = $gLocale -> GetStr('repository_not_created.status');
}

$gAction_disp -> AddEvent('editrepository', 'action_editrepository');
function action_editrepository($eventData) {
	global $gEnv, $gLocale, $gStatus;

	$rep = new AmpCentralRepository($gEnv['root']['db'], $eventData['id']);
	$rep -> SetName($eventData['name']);
	$rep -> SetDescription($eventData['description']);
	$rep -> SetLogEvents($eventData['logevents'] == 'on' ? true : false);
	$gLocale -> GetStr('repository_updated.status');
}

$gAction_disp -> AddEvent('removerepository', 'action_removerepository');
function action_removerepository($eventData) {
	global $gEnv, $gLocale, $gStatus;

	$rep = new AmpCentralRepository($gEnv['root']['db'], $eventData['id']);
	if ($rep -> Remove())
		$gStatus = $gLocale -> GetStr('repository_removed.status');
	else
		$gStatus = $gLocale -> GetStr('repository_not_removed.status');
}

$gAction_disp -> AddEvent('eraselog', 'action_eraselog');
function action_eraselog($eventData) {
	global $gEnv, $gLocale, $gStatus;

	$rep = new AmpCentralRepository($gEnv['root']['db'], $eventData['id']);
	if ($rep -> EraseLog())
		$gStatus = $gLocale -> GetStr('log_erased.status');
	else
		$gStatus = $gLocale -> GetStr('log_not_erased.status');
}

$gAction_disp -> AddEvent('enablemodules', 'action_enablemodules');
function action_enablemodules($eventData) {
	global $gEnv, $gLocale, $gStatus;

	$rep = new AmpCentralRepository($gEnv['root']['db'], $eventData['repid']);

	if (isset($eventData['modules']) and is_array($eventData['modules'])) {
		while (list (, $id) = each($eventData['modules'])) {
			$rep -> EnableModule($id);
		}
	}
}

$gAction_disp -> AddEvent('disablemodules', 'action_disablemodules');
function action_disablemodules($eventData) {
	global $gEnv, $gLocale, $gStatus;

	$rep = new AmpCentralRepository($gEnv['root']['db'], $eventData['repid']);

	if (isset($eventData['modules']) and is_array($eventData['modules'])) {
		while (list (, $id) = each($eventData['modules'])) {
			$rep -> DisableModule($id);
		}
	}
}

$gAction_disp -> AddEvent('enableprofiles', 'action_enableprofiles');
function action_enableprofiles($eventData) {
	global $gEnv, $gLocale, $gStatus;

	$rep = new AmpCentralRepository($gEnv['root']['db'], $eventData['repid']);

	if (isset($eventData['profiles']) and is_array($eventData['profiles'])) {
		while (list (, $id) = each($eventData['profiles'])) {
			$rep -> EnableProfile($id);
		}
	}
}

$gAction_disp -> AddEvent('disableprofiles', 'action_disableprofiles');
function action_disableprofiles($eventData) {
	global $gEnv, $gLocale, $gStatus;

	$rep = new AmpCentralRepository($gEnv['root']['db'], $eventData['repid']);

	if (isset($eventData['profiles']) and is_array($eventData['profiles'])) {
		while (list (, $id) = each($eventData['profiles'])) {
			$rep -> DisableProfile($id);
		}
	}
}

// ----- Modules -----
//
$gAction_disp -> AddEvent('addmodule', 'action_addmodule');
function action_addmodule($eventData) {
	global $gEnv, $gLocale, $gStatus;

	$mod = new AmpCentralModule($gEnv['root']['db']);
	if ($mod -> AddVersion($eventData['module']['tmp_name']))
		$gStatus = $gLocale -> GetStr('module_added.status');
	else
		$gStatus = $gLocale -> GetStr('module_not_added.status');

	unlink($eventData['module']['tmp_name']);
}

$gAction_disp -> AddEvent('removemodule', 'action_removemodule');
function action_removemodule($eventData) {
	global $gEnv, $gLocale, $gStatus;

	$mod = new AmpCentralModule($gEnv['root']['db'], $eventData['id']);
	if ($mod -> Remove())
		$gStatus = $gLocale -> GetStr('module_removed.status');
	else
		$gStatus = $gLocale -> GetStr('module_not_removed.status');
}

$gAction_disp -> AddEvent('removeversion', 'action_removeversion');
function action_removeversion($eventData) {
	global $gEnv, $gLocale, $gStatus;

	$mod = new AmpCentralModule($gEnv['root']['db'], $eventData['id']);
	$mod_name = $mod -> mModule;

	if ($mod -> RemoveVersion($eventData['version']))
		$gStatus = sprintf($gLocale -> GetStr('version_removed.status'), $mod_name, $eventData['version']);
	else
		$gStatus = sprintf($gLocale -> GetStr('version_not_removed.status'), $mod_name, $eventData['version']);
}

$gAction_disp -> Dispatch();

// Main dispatcher
//
$gMain_disp = new HuiDispatcher('main');

// ----- Repositories -----
//
function repositories_list_action_builder($pageNumber) {
	return build_events_call_string('', array(array('main', 'default', array('pagenumber' => $pageNumber))));
}

$gMain_disp -> AddEvent('default', 'main_default');
function main_default($eventData) {
	global $gEnv, $gLocale, $gXml_def, $gPage_title, $gStatus;

	$reps_query = & $gEnv['root']['db'] -> Execute('SELECT * '.'FROM ampcentralreps '.'ORDER BY name');

	if ($reps_query -> NumRows()) {
		$headers[0]['label'] = $gLocale -> GetStr('repository_name.header');
		$headers[1]['label'] = $gLocale -> GetStr('repository_description.header');

		$gXml_def = '<vertgroup><name>vg</name>
		  <children>
		    <table><name>repositories</name>
		      <args>
		        <headers type="array">'.huixml_encode($headers).'</headers>
		        <rowsperpage>10</rowsperpage>
		        <pagesactionfunction>repositores_list_action_builder</pagesactionfunction>
		        <pagenumber>'. (isset($eventData['pagenumber']) ? $eventData['pagenumber'] : '').'</pagenumber>
		      </args>
		      <children>
		';

		$row = 0;

		while (!$reps_query -> eof) {
			$main_tb = array();
			$main_tb['modules'] = array('label' => $gLocale -> GetStr('repository_modules.button'), 'themeimage' => 'view_detailed', 'action' => build_events_call_string('', array(array('main', 'repositorymodules', array('id' => $reps_query -> Fields('id'))))));

			$main_tb['profiles'] = array('label' => $gLocale -> GetStr('repository_profiles.button'), 'themeimage' => 'view_detailed', 'action' => build_events_call_string('', array(array('main', 'repositoryprofiles', array('id' => $reps_query -> Fields('id'))))));

			$main_tb['edit'] = array('label' => $gLocale -> GetStr('edit_repository.button'), 'themeimage' => 'edit', 'action' => build_events_call_string('', array(array('main', 'editrepository', array('id' => $reps_query -> Fields('id'))))));

			if (file_exists(MODULE_PATH.'ampcentral-server/repository_'.$reps_query -> Fields('id').'.log')) {
				$main_tb['log'] = array('label' => $gLocale -> GetStr('repository_log.button'), 'themeimage' => 'toggle_log', 'action' => build_events_call_string('', array(array('main', 'repositorylog', array('id' => $reps_query -> Fields('id'))))));
			}

			$main_tb['remove'] = array('label' => $gLocale -> GetStr('remove_repository.button'), 'needconfirm' => 'true', 'confirmmessage' => $gLocale -> GetStr('remove_repository.confirm'), 'themeimage' => 'edittrash', 'action' => build_events_call_string('', array(array('main', 'default', ''), array('action', 'removerepository', array('id' => $reps_query -> Fields('id'))))));

			$gXml_def.= '<label row="'.$row.'" col="0"><name>name</name>
			  <args>
			    <label type="encoded">'.urlencode($reps_query -> Fields('name')).'</label>
			  </args>
			</label>
			<label row="'.$row.'" col="1"><name>description</name>
			  <args>
			    <label type="encoded">'.urlencode($reps_query -> Fields('description')).'</label>
			  </args>
			</label>
			<amptoolbar row="'.$row.'" col="2"><name>tb</name>
			  <args>
			    <toolbars type="array">'.huixml_encode(array('main' => $main_tb)).'</toolbars>
			    <frame>false</frame>
			  </args>
			</amptoolbar>';
			$row ++;
			$reps_query -> MoveNext();
		}

		$gXml_def.= '      </children>
		    </table>
		  </children>
		</vertgroup>';
	} else {
		if (!strlen($gStatus))
			$gStatus = $gLocale -> GetStr('no_repositories.status');
	}

	$gPage_title.= ' - '.$gLocale -> GetStr('repositories.title');
}

$gMain_disp -> AddEvent('newrepository', 'main_newrepository');
function main_newrepository($eventData) {
	global $gEnv, $gLocale, $gXml_def, $gPage_title, $gStatus;

	$gXml_def = '<vertgroup><name>new</name>
	  <children>
	    <label><name>newrep</name>
	      <args>
	        <label type="encoded">'.urlencode($gLocale -> GetStr('newrepository.title')).'</label>
	        <bold>true</bold>
	      </args>
	    </label>
	    <form><name>newrepository</name>
	      <args>
	        <method>post</method>
	        <action type="encoded">'.urlencode(build_events_call_string('', array(array('main', 'default', ''), array('action', 'newrepository', '')))).'</action>
	      </args>
	      <children>
	        <grid><name>new</name>
	          <children>
	            <label row="0" col="0"><name>name</name>
	              <args>
	                <label type="encoded">'.urlencode($gLocale -> GetStr('repository_name.label')).'</label>
	              </args>
	            </label>
	            <string row="0" col="1"><name>name</name>
	              <args>
	                <disp>action</disp>
	                <size>20</size>
	              </args>
	            </string>
	            <label row="1" col="0"><name>description</name>
	              <args>
	                <label type="encoded">'.urlencode($gLocale -> GetStr('repository_description.label')).'</label>
	              </args>
	            </label>
	            <text row="1" col="1"><name>description</name>
	              <args>
	                <disp>action</disp>
	                <cols>80</cols>
	                <rows>5</rows>
	              </args>
	            </text>
	            <label row="2" col="0"><name>description</name>
	              <args>
	                <label type="encoded">'.urlencode($gLocale -> GetStr('repository_logevents.label')).'</label>
	              </args>
	            </label>
	            <checkbox row="2" col="1"><name>logevents</name>
	              <args>
	                <disp>action</disp>
	              </args>
	            </checkbox>
	          </children>
	        </grid>
	      </children>
	    </form>
	    <horizbar><name>hb</name></horizbar>
	    <button><name>apply</name>
	      <args>
	        <themeimage>button_ok</themeimage>
	        <formsubmit>newrepository</formsubmit>
	        <horiz>true</horiz>
	        <frame>false</frame>
	        <label type="encoded">'.urlencode($gLocale -> GetStr('new_repository.submit')).'</label>
	        <action type="encoded">'.urlencode(build_events_call_string('', array(array('main', 'default', ''), array('action', 'newrepository', '')))).'</action>
	      </args>
	    </button>
	  </children>
	</vertgroup>';

	$gPage_title.= ' - '.$gLocale -> GetStr('newrepository.title');
}

$gMain_disp -> AddEvent('editrepository', 'main_editrepository');
function main_editrepository($eventData) {
	global $gEnv, $gLocale, $gXml_def, $gPage_title, $gStatus;

	$rep = new AmpCentralRepository($gEnv['root']['db'], $eventData['id']);

	$gXml_def = '<vertgroup><name>edit</name>
	  <children>
	    <label><name>editrep</name>
	      <args>
	        <label type="encoded">'.urlencode($gLocale -> GetStr('editrepository.title')).'</label>
	        <bold>true</bold>
	      </args>
	    </label>
	    <form><name>editrepository</name>
	      <args>
	        <method>post</method>
	        <action type="encoded">'.urlencode(build_events_call_string('', array(array('main', 'default', ''), array('action', 'editrepository', array('id' => $eventData['id']))))).'</action>
	      </args>
	      <children>
	        <grid><name>edit</name>
	          <children>
	            <label row="0" col="0"><name>name</name>
	              <args>
	                <label type="encoded">'.urlencode($gLocale -> GetStr('repository_name.label')).'</label>
	              </args>
	            </label>
	            <string row="0" col="1"><name>name</name>
	              <args>
	                <disp>action</disp>
	                <size>20</size>
	                <value type="encoded">'.urlencode($rep -> mName).'</value>
	              </args>
	            </string>
	            <label row="1" col="0"><name>description</name>
	              <args>
	                <label type="encoded">'.urlencode($gLocale -> GetStr('repository_description.label')).'</label>
	              </args>
	            </label>
	            <text row="1" col="1"><name>description</name>
	              <args>
	                <disp>action</disp>
	                <cols>80</cols>
	                <rows>5</rows>
	                <value type="encoded">'.urlencode($rep -> mDescription).'</value>
	              </args>
	            </text>
	            <label row="2" col="0"><name>description</name>
	              <args>
	                <label type="encoded">'.urlencode($gLocale -> GetStr('repository_logevents.label')).'</label>
	              </args>
	            </label>
	            <checkbox row="2" col="1"><name>logevents</name>
	              <args>
	                <disp>action</disp>
	                <checked>'. ($rep -> mLogEvents ? 'true' : 'false').'</checked>
	              </args>
	            </checkbox>
	          </children>
	        </grid>
	      </children>
	    </form>
	    <horizbar><name>hb</name></horizbar>
	    <button><name>apply</name>
	      <args>
	        <themeimage>button_ok</themeimage>
	        <formsubmit>editrepository</formsubmit>
	        <horiz>true</horiz>
	        <frame>false</frame>
	        <label type="encoded">'.urlencode($gLocale -> GetStr('edit_repository.submit')).'</label>
	        <action type="encoded">'.urlencode(build_events_call_string('', array(array('main', 'default', ''), array('action', 'editrepository', array('id' => $eventData['id']))))).'</action>
	      </args>
	    </button>
	  </children>
	</vertgroup>';

	$gPage_title.= ' - '.$gLocale -> GetStr('editrepository.title');
}

$gMain_disp -> AddEvent('repositorymodules', 'main_repositorymodules');
function main_repositorymodules($eventData) {
	global $gEnv, $gLocale, $gPage_title, $gXml_def;

	$rep = new AmpCentralRepository($gEnv['root']['db'], $eventData['id']);

	$avail_modules = $rep -> AvailableModulesList();
	$unavailable_modules = $available_modules = array();

	$mods_query = & $gEnv['root']['db'] -> Execute('SELECT id,modid '.'FROM ampcentralmodules '.'ORDER BY modid');

	while (!$mods_query -> eof) {
		if (in_array($mods_query -> Fields('id'), $avail_modules))
			$available_modules[$mods_query -> Fields('id')] = $mods_query -> Fields('modid');
		else
			$unavailable_modules[$mods_query -> Fields('id')] = $mods_query -> Fields('modid');

		$mods_query -> MoveNext();
	}

	$headers[0]['label'] = $gLocale -> GetStr('unavailable_modules.label');
	$headers[1]['label'] = $gLocale -> GetStr('available_modules.label');

	$gXml_def = '<vertgroup><name>modules</name>
	  <args>
	    <align>center</align>
	  </args>
	  <children>
	  <label><name>rep</name>
	    <args>
	      <bold>true</bold>
	      <label type="encoded">'.urlencode($rep -> mName).'</label>
	    </args>
	  </label>
	    <table><name>modules</name>
	      <args>
	        <headers type="array">'.huixml_encode($headers).'</headers>
	      </args>
	      <children>
	
	        <form row="0" col="0"><name>unavailablemodules</name>
	          <args>
	            <method>post</method>
	            <action type="encoded">'.urlencode(build_events_call_string('', array(array('main', 'repositorymodules', array('id' => $eventData['id'])), array('action', 'enablemodules', array('repid' => $eventData['id']))))).'</action>
	          </args>
	          <children>
	              <listbox><name>modules</name>
	                <args>
	                  <elements type="array">'.huixml_encode($unavailable_modules).'</elements>
	                  <disp>action</disp>
	                  <size>15</size>
	                  <multiselect>true</multiselect>
	                </args>
	              </listbox>
	          </children>
	        </form>
	
	        <amptoolbar row="1" col="0"><name>tb</name>
	          <args>
	            <toolbars type="array">'.huixml_encode(array('main' => array('enable' => array('label' => $gLocale -> GetStr('enable_modules.button'), 'themeimage' => 'forward2', 'horiz' => 'true', 'formsubmit' => 'unavailablemodules', 'action' => build_events_call_string('', array(array('main', 'repositorymodules', array('id' => $eventData['id'])), array('action', 'enablemodules', array('repid' => $eventData['id'])))))))).'</toolbars>
	            <frame>false</frame>
	          </args>
	        </amptoolbar>
	
	        <form row="0" col="1"><name>availablemodules</name>
	          <args>
	            <method>post</method>
	            <action type="encoded">'.urlencode(build_events_call_string('', array(array('main', 'repositorymodules', array('id' => $eventData['id'])), array('action', 'disablemodules', array('repid' => $eventData['id']))))).'</action>
	          </args>
	          <children>
	              <listbox><name>modules</name>
	                <args>
	                  <elements type="array">'.huixml_encode($available_modules).'</elements>
	                  <disp>action</disp>
	                  <size>15</size>
	                  <multiselect>true</multiselect>
	                </args>
	              </listbox>
	          </children>
	        </form>
	
	        <amptoolbar row="1" col="1"><name>tb</name>
	          <args>
	            <toolbars type="array">'.huixml_encode(array('main' => array('enable' => array('label' => $gLocale -> GetStr('disable_modules.button'), 'themeimage' => 'back2', 'horiz' => 'true', 'formsubmit' => 'availablemodules', 'action' => build_events_call_string('', array(array('main', 'repositorymodules', array('id' => $eventData['id'])), array('action', 'disablemodules', array('repid' => $eventData['id'])))))))).'</toolbars>
	            <frame>false</frame>
	          </args>
	        </amptoolbar>
	
	      </children>
	    </table>
	  </children>
	</vertgroup>';
	$gPage_title.= ' - '.$rep -> mName.' - '.$gLocale -> GetStr('repository_modules.title');
}

$gMain_disp -> AddEvent('repositoryprofiles', 'main_repositoryprofiles');
function main_repositoryprofiles($eventData) {
	global $gEnv, $gLocale, $gPage_title, $gXml_def;

	$rep = new AmpCentralRepository($gEnv['root']['db'], $eventData['id']);

	$avail_profiles = $rep -> AvailableProfilesList();
	$unavailable_profiles = $available_profiles = array();

	$mods_query = & $gEnv['root']['db'] -> Execute('SELECT id,profilename '.'FROM xmlrpcprofiles '.'ORDER BY profilename');

	while (!$mods_query -> eof) {
		if (in_array($mods_query -> Fields('id'), $avail_profiles))
			$available_profiles[$mods_query -> Fields('id')] = $mods_query -> Fields('profilename');
		else
			$unavailable_profiles[$mods_query -> Fields('id')] = $mods_query -> Fields('profilename');

		$mods_query -> MoveNext();
	}

	$headers[0]['label'] = $gLocale -> GetStr('unavailable_profiles.label');
	$headers[1]['label'] = $gLocale -> GetStr('available_profiles.label');

	$gXml_def = '<vertgroup><name>profiles</name>
	  <args>
	    <align>center</align>
	  </args>
	  <children>
	  <label><name>rep</name>
	    <args>
	      <bold>true</bold>
	      <label type="encoded">'.urlencode($rep -> mName).'</label>
	    </args>
	  </label>
	    <table><name>profiles</name>
	      <args>
	        <headers type="array">'.huixml_encode($headers).'</headers>
	      </args>
	      <children>
	
	        <form row="0" col="0"><name>unavailableprofiles</name>
	          <args>
	            <method>post</method>
	            <action type="encoded">'.urlencode(build_events_call_string('', array(array('main', 'repositoryprofiles', array('id' => $eventData['id'])), array('action', 'enableprofiles', array('repid' => $eventData['id']))))).'</action>
	          </args>
	          <children>
	              <listbox><name>profiles</name>
	                <args>
	                  <elements type="array">'.huixml_encode($unavailable_profiles).'</elements>
	                  <disp>action</disp>
	                  <size>15</size>
	                  <multiselect>true</multiselect>
	                </args>
	              </listbox>
	          </children>
	        </form>
	
	        <amptoolbar row="1" col="0"><name>tb</name>
	          <args>
	            <toolbars type="array">'.huixml_encode(array('main' => array('enable' => array('label' => $gLocale -> GetStr('enable_profiles.button'), 'themeimage' => 'forward2', 'horiz' => 'true', 'formsubmit' => 'unavailableprofiles', 'action' => build_events_call_string('', array(array('main', 'repositoryprofiles', array('id' => $eventData['id'])), array('action', 'enableprofiles', array('repid' => $eventData['id'])))))))).'</toolbars>
	            <frame>false</frame>
	          </args>
	        </amptoolbar>
	
	        <form row="0" col="1"><name>availableprofiles</name>
	          <args>
	            <method>post</method>
	            <action type="encoded">'.urlencode(build_events_call_string('', array(array('main', 'repositoryprofiles', array('id' => $eventData['id'])), array('action', 'disableprofiles', array('repid' => $eventData['id']))))).'</action>
	          </args>
	          <children>
	              <listbox><name>profiles</name>
	                <args>
	                  <elements type="array">'.huixml_encode($available_profiles).'</elements>
	                  <disp>action</disp>
	                  <size>15</size>
	                  <multiselect>true</multiselect>
	                </args>
	              </listbox>
	          </children>
	        </form>
	
	        <amptoolbar row="1" col="1"><name>tb</name>
	          <args>
	            <toolbars type="array">'.huixml_encode(array('main' => array('enable' => array('label' => $gLocale -> GetStr('disable_profiles.button'), 'themeimage' => 'back2', 'horiz' => 'true', 'formsubmit' => 'availableprofiles', 'action' => build_events_call_string('', array(array('main', 'repositoryprofiles', array('id' => $eventData['id'])), array('action', 'disableprofiles', array('repid' => $eventData['id'])))))))).'</toolbars>
	            <frame>false</frame>
	          </args>
	        </amptoolbar>
	
	      </children>
	    </table>
	  </children>
	</vertgroup>';
	$gPage_title.= ' - '.$rep -> mName.' - '.$gLocale -> GetStr('repository_profiles.title');
}

$gMain_disp -> AddEvent('repositorylog', 'main_repositorylog');
function main_repositorylog($eventData) {
	global $gEnv, $gLocale, $gPage_title, $gXml_def, $gToolbars;

	$rep = new AmpCentralRepository($gEnv['root']['db'], $eventData['id']);

	$gXml_def = '<vertgroup><name>logs</name>
	  <children>
	    <text><name>log</name>
	      <args>
	        <readonly>true</readonly>
	        <rows>15</rows>
	        <cols>120</cols>
	        <value type="encoded">'.urlencode($rep -> GetLogContent()).'</value>
	      </args>
	    </text>
	  </children>
	</vertgroup>';

	$gToolbars['log'] = array('refresh' => array('label' => $gLocale -> GetStr('refreshlog.toolbar'), 'themeimage' => 'reload', 'action' => build_events_call_string('', array(array('main', 'repositorylog', array('id' => $eventData['id']))))), 'eraselog' => array('label' => $gLocale -> GetStr('eraselog.toolbar'), 'themeimage' => 'edittrash', 'action' => build_events_call_string('', array(array('main', 'default', ''), array('action', 'eraselog', array('id' => $eventData['id'])))), 'needconfirm' => 'true', 'confirmmessage' => $gLocale -> GetStr('eraselog.confirm')));
}

// ----- Modules -----
//
function modules_list_action_builder($pageNumber) {
	return build_events_call_string('', array(array('main', 'modules', array('pagenumber' => $pageNumber))));
}

$gMain_disp -> AddEvent('modules', 'main_modules');
function main_modules($eventData) {
	global $gEnv, $gLocale, $gXml_def, $gPage_title, $gStatus;

	$mods_query = & $gEnv['root']['db'] -> Execute('SELECT * '.'FROM ampcentralmodules '.'ORDER BY modid');

	if ($mods_query -> NumRows()) {
		$headers[0]['label'] = $gLocale -> GetStr('module_name.header');
		$headers[1]['label'] = $gLocale -> GetStr('module_lastversion.header');
		$headers[2]['label'] = $gLocale -> GetStr('module_category.header');

		$gXml_def = '<vertgroup><name>vg</name>
		  <children>
		    <table><name>modules</name>
		      <args>
		        <headers type="array">'.huixml_encode($headers).'</headers>
		        <rowsperpage>10</rowsperpage>
		        <pagesactionfunction>modules_list_action_builder</pagesactionfunction>
		        <pagenumber>'. (isset($eventData['pagenumber']) ? $eventData['pagenumber'] : '').'</pagenumber>
		      </args>
		      <children>
		';

		$row = 0;

		while (!$mods_query -> eof) {
			$gXml_def.= '<label row="'.$row.'" col="0"><name>name</name>
			  <args>
			    <label type="encoded">'.urlencode('<strong>'.$mods_query -> Fields('modid').'</strong><br>'.$mods_query -> Fields('description')).'</label>
			  </args>
			</label>
			<label row="'.$row.'" col="1"><name>lastversion</name>
			  <args>
			    <label type="encoded">'.urlencode($mods_query -> Fields('lastversion')).'</label>
			  </args>
			</label>
			<label row="'.$row.'" col="2"><name>name</name>
			  <args>
			    <label type="encoded">'.urlencode(ucfirst($mods_query -> Fields('category'))).'</label>
			  </args>
			</label>
			<amptoolbar row="'.$row.'" col="3"><name>tb</name>
			  <args>
			    <toolbars type="array">'.huixml_encode(array('main' => array('remove' => array('label' => $gLocale -> GetStr('remove_module.button'), 'needconfirm' => 'true', 'confirmmessage' => $gLocale -> GetStr('remove_module.confirm'), 'themeimage' => 'edittrash', 'action' => build_events_call_string('', array(array('main', 'modules', ''), array('action', 'removemodule', array('id' => $mods_query -> Fields('id')))))), 'versions' => array('label' => $gLocale -> GetStr('module_versions.button'), 'themeimage' => 'view_detailed', 'action' => build_events_call_string('', array(array('main', 'moduleversions', array('id' => $mods_query -> Fields('id'))))))))).'</toolbars>
			    <frame>false</frame>
			  </args>
			</amptoolbar>';
			$row ++;
			$mods_query -> MoveNext();
		}

		$gXml_def.= '      </children>
		    </table>
		  </children>
		</vertgroup>';
	} else {
		if (!strlen($gStatus))
			$gStatus = $gLocale -> GetStr('no_modules.status');
	}

	$gPage_title.= ' - '.$gLocale -> GetStr('modules.title');
}

$gMain_disp -> AddEvent('newmodule', 'main_newmodule');
function main_newmodule($eventData) {
	global $gEnv, $gLocale, $gXml_def, $gPage_title;

	$gXml_def = '<vertgroup><name>new</name>
	  <children>
	    <label><name>newmodule</name>
	      <args>
	        <label type="encoded">'.urlencode($gLocale -> GetStr('newmodule.title')).'</label>
	        <bold>true</bold>
	      </args>
	    </label>
	    <form><name>newmodule</name>
	      <args>
	        <method>post</method>
	        <action type="encoded">'.urlencode(build_events_call_string('', array(array('main', 'modules', ''), array('action', 'addmodule', '')))).'</action>
	      </args>
	      <children>
	        <grid><name>new</name>
	          <children>
	            <label row="0" col="0"><name>file</name>
	              <args>
	                <label type="encoded">'.urlencode($gLocale -> GetStr('module_file.label')).'</label>
	              </args>
	            </label>
	            <file row="0" col="1"><name>module</name>
	              <args>
	                <disp>action</disp>
	                <size>20</size>
	              </args>
	            </file>
	          </children>
	        </grid>
	      </children>
	    </form>
	    <horizbar><name>hb</name></horizbar>
	    <button><name>apply</name>
	      <args>
	        <themeimage>button_ok</themeimage>
	        <formsubmit>newmodule</formsubmit>
	        <horiz>true</horiz>
	        <frame>false</frame>
	        <label type="encoded">'.urlencode($gLocale -> GetStr('addmodule.submit')).'</label>
	        <action type="encoded">'.urlencode(build_events_call_string('', array(array('main', 'modules', ''), array('action', 'addmodule', '')))).'</action>
	      </args>
	    </button>
	  </children>
	</vertgroup>';
	$gPage_title.= ' - '.$gLocale -> GetStr('newmodule.title');
}

function versions_list_action_builder($pageNumber) {
	$tmp_main_disp = new HuiDispatcher('main');

	$event_data = $tmp_main_disp -> GetEventData();

	return build_events_call_string('', array(array('main', 'moduleversions', array('pagenumber' => $eventData['pagenumber'], 'id' => $event_data['id']))));
}

$gMain_disp -> AddEvent('moduleversions', 'main_moduleversions');
function main_moduleversions($eventData) {
	global $gEnv, $gLocale, $gPage_title, $gXml_def, $gStatus;

	$module = new AmpCentralModule($gEnv['root']['db'], $eventData['id']);
	$versions = $module -> GetVersionsList(true);

	$headers[0]['label'] = $gLocale -> GetStr('version.header');
	$headers[1]['label'] = $gLocale -> GetStr('date.header');
	$headers[2]['label'] = $gLocale -> GetStr('dependencies.header');

	$gXml_def = '<vertgroup><name>versions</name>
	  <children>
	    <label><name>module</name>
	      <args>
	        <bold>true</bold>
	        <label type="encoded">'.urlencode($module -> mModule).'</label>
	      </args>
	    </label>
	    <table><name>versions</name>
	      <args>
	        <headers type="array">'.huixml_encode($headers).'</headers>
	        <rowsperpage>15</rowsperpage>
	        <pagesactionfunction>versions_list_action_builder</pagesactionfunction>
	        <pagenumber>'. (isset($eventData['pagenumber']) ? $eventData['pagenumber'] : '').'</pagenumber>
	      </args>
	      <children>';

	$row = 0;

	while (list (, $version) = each($versions)) {
		$vers_data = $module -> GetVersionData($version);

		$gXml_def.= '<label row="'.$row.'" col="0"><name>version</name>
		  <args>
		    <label type="encoded">'.urlencode($version).'</label>
		  </args>
		</label>
		<label row="'.$row.'" col="1"><name>date</name>
		  <args>
		    <label type="encoded">'.urlencode($vers_data['date']).'</label>
		  </args>
		</label>
		<label row="'.$row.'" col="2"><name>dependencies</name>
		  <args>
		    <label type="encoded">'.urlencode($vers_data['dependencies']. (strlen($vers_data['suggestions']) ? '<br>('.$vers_data['suggestions'].')' : '')).'</label>
		  </args>
		</label>
		<amptoolbar row="'.$row.'" col="3"><name>tb</name>
		  <args>
		    <toolbars type="array">'.huixml_encode(array('main' => array('remove' => array('label' => $gLocale -> GetStr('remove_version.button'), 'needconfirm' => 'true', 'confirmmessage' => $gLocale -> GetStr('remove_version.confirm'), 'themeimage' => 'edittrash', 'action' => build_events_call_string('', array(array('main', (count($versions) == 1 ? 'modules' : 'moduleversions'), array('id' => $eventData['id'])), array('action', 'removeversion', array('id' => $eventData['id'], 'version' => $version)))))))).'</toolbars>
		    <frame>false</frame>
		  </args>
		</amptoolbar>';

		$row ++;
	}

	$gXml_def.= '      </children>
	    </table>
	  </children>
	</vertgroup>';

	$gPage_title.= ' - '.$module -> mModule.' - '.$gLocale -> GetStr('moduleversions.title');
}

$gMain_disp -> Dispatch();

// Rendering
//
if (strlen($gXml_def))
	$gPage_content = new HuiXml('page', array('definition' => $gXml_def));

$gHui -> AddChild(new HuiAmpPage('page', array('pagetitle' => $gPage_title, 'menu' => $gMenu, 'toolbars' => array(new HuiAmpToolbar('main', array('toolbars' => $gToolbars))), 'maincontent' => $gPage_content, 'status' => $gStatus)));

$gHui -> Render();

?>
