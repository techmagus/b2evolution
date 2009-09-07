<?php
/**
 * This file implements the UI controller for settings management.
 *
 * This file is part of the evoCore framework - {@link http://evocore.net/}
 * See also {@link http://sourceforge.net/projects/evocms/}.
 *
 * @copyright (c)2003-2009 by Francois PLANQUE - {@link http://fplanque.net/}
 * Parts of this file are copyright (c)2004-2006 by Daniel HAHLER - {@link http://thequod.de/contact}.
 *
 * {@internal License choice
 * - If you have received this file as part of a package, please find the license.txt file in
 *   the same folder or the closest folder above for complete license terms.
 * - If you have received this file individually (e-g: from http://evocms.cvs.sourceforge.net/)
 *   then you must choose one of the following licenses before using the file:
 *   - GNU General Public License 2 (GPL) - http://www.opensource.org/licenses/gpl-license.php
 *   - Mozilla Public License 1.1 (MPL) - http://www.opensource.org/licenses/mozilla1.1.php
 * }}
 *
 * {@internal Open Source relicensing agreement:
 * Daniel HAHLER grants Francois PLANQUE the right to license
 * Daniel HAHLER's contributions to this file and the b2evolution project
 * under any OSI approved OSS license (http://www.opensource.org/licenses/).
 * }}
 *
 * @package admin
 *
 * {@internal Below is a list of authors who have contributed to design/coding of this file: }}
 * @author fplanque: Francois PLANQUE
 * @author blueyed: Daniel HAHLER
 *
 * @todo separate object inits and permission checks
 *
 * @version $Id$
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

/**
 * @var AdminUI_general
 */
global $AdminUI;


$AdminUI->set_path( 'users' );

param_action( 'list' );

param( 'user_ID', 'integer', NULL );	// Note: should NOT be memorized (would kill navigation/sorting) use memorize_param() if needed
param( 'grp_ID', 'integer', NULL );		// Note: should NOT be memorized:    -- " --

/**
 * @global boolean true, if user is only allowed to edit his profile
 */
$user_profile_only = ! $current_User->check_perm( 'users', 'view' );

if( $user_profile_only )
{ // User has no permissions to view: he can only edit his profile

	if( (isset($user_ID) && $user_ID != $current_User->ID)
	 || isset($grp_ID) )
	{ // User is trying to edit something he should not: add error message (Should be prevented by UI)
		$Messages->add( T_('You have no permission to view other users or groups!'), 'error' );
	}

	// Make sure the user only edits himself:
	$user_ID = $current_User->ID;
	$grp_ID = NULL;
	if( ! in_array( $action, array( 'userupdate', 'edit_user', 'default_settings' ) ) )
	{
		$action = 'edit_user';
	}
}

/*
 * Load editable objects and set $action (while checking permissions)
 */

$UserCache = & get_Cache( 'UserCache' );
$GroupCache = & get_Cache( 'GroupCache' );

if( ! is_null($user_ID) )
{ // User selected
	if( $action == 'userupdate' && $user_ID == 0 )
	{ // we create a new user
		$edited_User = new User();
		$edited_User->set_datecreated( $localtimenow );
	}
	elseif( ($edited_User = & $UserCache->get_by_ID( $user_ID, false )) === false )
	{	// We could not find the User to edit:
		unset( $edited_User );
		forget_param( 'user_ID' );
		$Messages->add( sprintf( T_('Requested &laquo;%s&raquo; object does not exist any longer.'), T_('User') ), 'error' );
		$action = 'list';
	}
	elseif( $action == 'list' )
	{ // 'list' is default, $user_ID given
		if( $user_ID == $current_User->ID || $current_User->check_perm( 'users', 'edit' ) )
		{
			$action = 'edit_user';
		}
		else
		{
			$action = 'view_user';
		}
	}

	if( $action != 'view_user' && $action != 'list' )
	{ // check edit permissions
		if( ! $current_User->check_perm( 'users', 'edit' )
		    && $edited_User->ID != $current_User->ID )
		{ // user is only allowed to _view_ other user's profiles
			$Messages->add( T_('You have no permission to edit other users!'), 'error' );
			$action = 'view_user';
		}
		elseif( $demo_mode )
		{ // Demo mode restrictions: admin/demouser cannot be edited
			if( $edited_User->ID == 1 || $edited_User->login == 'demouser' )
			{
				$Messages->add( T_('You cannot edit the admin and demouser profile in demo mode!'), 'error' );

				if( strpos( $action, 'delete_' ) === 0 || $action == 'promote' )
				{ // Fallback to list/view action
					$action = 'list';
				}
				else
				{
					$action = 'view_user';
				}
			}
		}
	}
}
elseif( $grp_ID !== NULL )
{ // Group selected
	if( $action == 'groupupdate' && $grp_ID == 0 )
	{ // New Group:
		$edited_Group = new Group();
	}
	elseif( ($edited_Group = & $GroupCache->get_by_ID( $grp_ID, false )) === false )
	{ // We could not find the Group to edit:
		unset( $edited_Group );
		forget_param( 'grp_ID' );
		$Messages->add( sprintf( T_('Requested &laquo;%s&raquo; object does not exist any longer.'), T_('Group') ), 'error' );
		$action = 'list';
	}
	elseif( $action == 'list' )
	{ // 'list' is default, $grp_ID given
		if( $current_User->check_perm( 'users', 'edit' ) )
		{
			$action = 'edit_group';
		}
		else
		{
			$action = 'view_group';
		}
	}

	if( $action != 'view_group' && $action != 'list' )
	{ // check edit permissions
		if( !$current_User->check_perm( 'users', 'edit' ) )
		{
			$Messages->add( T_('You have no permission to edit groups!'), 'error' );
			$action = 'view_group';
		}
		elseif( $demo_mode  )
		{ // Additional checks for demo mode: no changes to admin's and demouser's group allowed
			$admin_User = & $UserCache->get_by_ID(1);
			$demo_User = & $UserCache->get_by_login('demouser');
			if( $edited_Group->ID == $admin_User->Group->ID
					|| $edited_Group->ID == $demo_User->group_ID )
			{
				$Messages->add( T_('You cannot edit the groups of user &laquo;admin&raquo; or &laquo;demouser&raquo; in demo mode!'), 'error' );
				$action = 'view_group';
			}
		}
	}
}


/*
 * Perform actions, if there were no errors:
 */
if( !$Messages->count('error') )
{ // no errors
	switch( $action )
	{
		case 'new_user':
			// We want to create a new user:
			if( isset( $edited_User ) )
			{ // We want to use a template
				$new_User = $edited_User; // Copy !
				$new_User->set( 'ID', 0 );
				$edited_User = & $new_User;
			}
			else
			{ // We use an empty user:
				$edited_User = & new User();
			}

			// Determine if the user must validate before using the system:
			$edited_User->set( 'validated', ! $Settings->get('newusers_mustvalidate') );
			break;


		case 'change_admin_skin':
			// Skin switch from menu
			param( 'new_admin_skin', 'string', true );
	    param( 'redirect_to', 'string', '' );

	    $UserSettings->set( 'admin_skin', $new_admin_skin );
			$UserSettings->dbupdate();
			$Messages->add( sprintf( T_('Admin skin changed to &laquo;%s&raquo;'), $new_admin_skin ), 'success' );

      header_nocache();
			header_redirect();
			/* EXITED */
			break;


		case 'userupdate':
			// Update existing user OR create new user:
			if( empty($edited_User) || !is_object($edited_User) )
			{
				$Messages->add( 'No user set!' ); // Needs no translation, should be prevented by UI.
				$action = 'list';
				break;
			}

			$reload_page = false; // We set it to true, if a setting changes that needs a page reload (locale, admin skin, ..)

			if( !$current_User->check_perm( 'users', 'edit' ) && $edited_User->ID != $current_User->ID )
			{ // user is only allowed to update him/herself
				$Messages->add( T_('You are only allowed to update your own profile!'), 'error' );
				$action = 'view_user';
				break;
			}

			param( 'edited_user_login', 'string' );
			param_check_not_empty( 'edited_user_login', T_('You must provide a login!') );
			// We want all logins to be lowercase to guarantee uniqueness regardless of the database case handling for UNIQUE indexes:
			$edited_user_login = strtolower( $edited_user_login );

			if( $current_User->check_perm( 'users', 'edit' ) )
			{ // changing level/group is allowed (not in profile mode)
				param_integer_range( 'edited_user_level', 0, 10, T_('User level must be between %d and %d.') );
				$edited_User->set( 'level', $edited_user_level );

				param( 'edited_user_validated', 'integer', 0 );
				if( $edited_User->set( 'validated', $edited_user_validated ) && $edited_User->ID == $current_User->ID )
				{ // validated value has changed for the current user
					$reload_page = true;
				}
				param( 'edited_user_grp_ID', 'integer', true );
				$edited_user_Group = $GroupCache->get_by_ID( $edited_user_grp_ID );
				$edited_User->set_Group( $edited_user_Group );
				// echo 'new group = ';
				// $edited_User->Group->disp('name');
			}

			// check if new login already exists for another user_ID
			$query = '
				SELECT user_ID
				  FROM T_users
				 WHERE user_login = '.$DB->quote($edited_user_login).'
				   AND user_ID != '.$edited_User->ID;
			if( $q = $DB->get_var( $query ) )
			{
				param_error( 'edited_user_login',
					sprintf( T_('This login already exists. Do you want to <a %s>edit the existing user</a>?'),
						'href="?ctrl=users&amp;user_ID='.$q.'"' ) );
			}

			param( 'edited_user_firstname', 'string', true );
			param( 'edited_user_lastname', 'string', true );

			param( 'edited_user_nickname', 'string', true );
			param_check_not_empty( 'edited_user_nickname', T_('Please enter a nickname (can be the same as your login).') );

			param( 'edited_user_ctry_ID', 'integer', true );
			param_check_number( 'edited_user_ctry_ID', 'Please select a country', true );

			param( 'edited_user_idmode', 'string', true );
			param( 'edited_user_locale', 'string', true );

			param( 'edited_user_email', 'string', true );
			param_check_not_empty( 'edited_user_email', T_('Please enter an e-mail address.') );
			param_check_email( 'edited_user_email', true );

			param( 'edited_user_url', 'string', true );
			param_check_url( 'edited_user_url', 'commenting' );

			param( 'edited_user_icq', 'string', true );
			param_check_number( 'edited_user_icq', T_('The ICQ UIN can only be a number, no letters allowed.') );

			param( 'edited_user_aim', 'string', true );

			param( 'edited_user_msn', 'string', true );
			param_check_email( 'edited_user_msn', false );

			param( 'edited_user_yim', 'string', true );
			param( 'edited_user_allow_msgform', 'integer', 0 );
			param( 'edited_user_notify', 'integer', 0 );
			param( 'edited_user_showonline', 'integer', 0 );
			param( 'edited_user_set_login_multiple_sessions', 'integer', 0 );

			param( 'edited_user_pass1', 'string', true );
			param( 'edited_user_pass2', 'string', true );
			if( ! param_check_passwords( 'edited_user_pass1', 'edited_user_pass2', ($edited_User->ID == 0) ) ) // required for new users
			{ // passwords not the same or empty: empty them for the form
				$edited_user_pass1 = '';
				$edited_user_pass2 = '';
			}

			$edited_User->set( 'login', $edited_user_login );
			$edited_User->set( 'firstname', $edited_user_firstname );
			$edited_User->set( 'lastname', $edited_user_lastname );
			$edited_User->set( 'nickname', $edited_user_nickname );
			$edited_User->set( 'idmode', $edited_user_idmode );
			$edited_User->set( 'ctry_ID', $edited_user_ctry_ID);
			if( $edited_User->set( 'locale', $edited_user_locale ) && $edited_User->ID == $current_User->ID )
			{ // locale value has changed for the current user
				$reload_page = true;
			}
			$edited_User->set( 'email', $edited_user_email );
			$edited_User->set( 'url', $edited_user_url );
			$edited_User->set( 'icq', $edited_user_icq );
			$edited_User->set( 'aim', $edited_user_aim );
			$edited_User->set( 'msn', $edited_user_msn );
			$edited_User->set( 'yim', $edited_user_yim );
			$edited_User->set( 'allow_msgform', $edited_user_allow_msgform );
			$edited_User->set( 'notify', $edited_user_notify );
			$edited_User->set( 'showonline', $edited_user_showonline );

			// Features
			param( 'edited_user_admin_skin', 'string', true );
			param_integer_range( 'edited_user_action_icon_threshold', 1, 5, T_('The threshold must be between 1 and 5.') );
			param_integer_range( 'edited_user_action_word_threshold', 1, 5, T_('The threshold must be between 1 and 5.') );
			param( 'edited_user_legend', 'integer', 0 );
			param( 'edited_user_bozo', 'integer', 0 );
			param( 'edited_user_focusonfirst', 'integer', 0 );
			param( 'edited_user_results_per_page', 'integer', null );


			// EXPERIMENTAL user fields:

			// EXISTING fields:
			// Get indices of existing userfields:
			$userfield_IDs = $DB->get_col( '
						SELECT uf_ID
							FROM T_users__fields
						 WHERE uf_user_ID = '.$edited_User->ID );
			foreach( $userfield_IDs as $userfield_ID )
			{
				$uf_val = param( 'uf_'.$userfield_ID, 'string', '' );

				// TODO: type checking

				$edited_User->userfield_update( $userfield_ID, $uf_val );
			}

			// NEW fields:
			for( $i=1; $i<=3; $i++ )
			{	// new fields:
				$new_uf_type = param( 'new_uf_type_'.$i, 'integer', '' );
				$new_uf_val = param( 'new_uf_val_'.$i, 'string', '' );
				if( empty($new_uf_type) && empty($new_uf_val) )
				{
					continue;
				}

				if( empty($new_uf_type) )
				{
					param_error( 'new_uf_val_'.$i, T_('Please select a field type.') );
				}
				if( empty($new_uf_val) )
				{
					param_error( 'new_uf_val_'.$i, T_('Please enter a value.') );
				}

				// echo $new_uf_type.':'.$new_uf_val;

				// TODO: type checking

				$edited_User->userfield_add( $new_uf_type, $new_uf_val );
			}

			if( $Messages->count( 'error' ) )
			{	// We have found validation errors:
				$action = 'edit_user';
				break;
			}

			// OK, no error.
			$new_pass = '';

			if( !empty($edited_user_pass2) )
			{ // Password provided, we must encode it
				$new_pass = md5( $edited_user_pass2 );

				$edited_User->set( 'pass', $new_pass ); // set password
			}

			if( $edited_User->ID != 0 )
			{ // Commit update to the DB:
				$update_r = $edited_User->dbupdate();

				if( $edited_User->ID == $current_User->ID )
				{ // User updates his profile:
					if( $update_r )
					{
						$Messages->add( T_('Your profile has been updated.'), 'success' );
					}
					else
					{
						$Messages->add( T_('Your profile has not been changed.'), 'note' );
					}
				}
				else
				{
					$Messages->add( T_('User updated.'), 'success' );
				}
			}
			else
			{ // Insert user into DB
				$edited_User->dbinsert();
				$Messages->add( T_('New user created.'), 'success' );
			}

			// Now that the User exists in the DB and has an ID, update the settings:

			$UserSettings->set( 'login_multiple_sessions', $edited_user_set_login_multiple_sessions, $edited_User->ID );

			if( $UserSettings->set( 'admin_skin', $edited_user_admin_skin, $edited_User->ID )
					&& ($edited_User->ID == $current_User->ID) )
			{ // admin_skin has changed or was set the first time for the current user
				$reload_page = true;
			}

			// Action icon params:
			$UserSettings->set( 'action_icon_threshold', $edited_user_action_icon_threshold, $edited_User->ID );
			$UserSettings->set( 'action_word_threshold', $edited_user_action_word_threshold, $edited_User->ID );
			$UserSettings->set( 'display_icon_legend', $edited_user_legend, $edited_User->ID );

			// Set bozo validador activation
			$UserSettings->set( 'control_form_abortions', $edited_user_bozo, $edited_User->ID );

			// Focus on first
			$UserSettings->set( 'focus_on_first_input', $edited_user_focusonfirst, $edited_User->ID );

			// Results per page
			if( isset($edited_user_results_per_page) )
			{
				$UserSettings->set( 'results_per_page', $edited_user_results_per_page, $edited_User->ID );
			}

			// Update user settings:
			if( $UserSettings->dbupdate() )
			{
				$Messages->add( T_('User feature settings have been changed.'), 'success');
			}

			// PluginUserSettings
			load_funcs('plugins/_plugin.funcs.php');

			$any_plugin_settings_updated = false;
			$Plugins->restart();
			while( $loop_Plugin = & $Plugins->get_next() )
			{
				$pluginusersettings = $loop_Plugin->GetDefaultUserSettings( $tmp_params = array('for_editing'=>true) );
				if( empty($pluginusersettings) )
				{
					continue;
				}

				// Loop through settings for this plugin:
				foreach( $pluginusersettings as $set_name => $set_meta )
				{
					autoform_set_param_from_request( $set_name, $set_meta, $loop_Plugin, 'UserSettings', $edited_User );
				}

				// Let the plugin handle custom fields:
				$ok_to_update = $Plugins->call_method( $loop_Plugin->ID, 'PluginUserSettingsUpdateAction', $tmp_params = array(
					'User' => & $edited_User, 'action' => 'save' ) );

				if( $ok_to_update === false )
				{
					$loop_Plugin->UserSettings->reset();
				}
				elseif( $loop_Plugin->UserSettings->dbupdate() )
				{
					$any_plugin_settings_updated = true;
				}
			}
			if( $any_plugin_settings_updated )
			{
				$Messages->add( T_('Usersettings of Plugins have been updated.'), 'success' );
			}

			if( $user_profile_only )
			{
				$action = 'edit_user';
			}

			if( $reload_page )
			{ // reload the current page through header redirection:
				if( $action != 'edit_user' )
				{
					$action = 'list';
				}
				header_redirect( regenerate_url( '', 'user_ID='.$edited_User->ID.'&action='.$action, '', '&' ) ); // will save $Messages into Session
			}
			break;


		case 'default_settings':
			$reload_page = false; // We set it to true, if a setting changes that needs a page reload (locale, admin skin, ..)

			// Admin skin:
			$cur_admin_skin = $UserSettings->get('admin_skin');

			$UserSettings->delete( 'admin_skin', $edited_User->ID );
			if( $cur_admin_skin
					&& $UserSettings->get('admin_skin', $edited_User->ID ) != $cur_admin_skin
					&& ($edited_User->ID == $current_User->ID) )
			{ // admin_skin has changed:
				$reload_page = true;
			}

			// Remove all UserSettings where a default exists:
			foreach( $UserSettings->_defaults as $k => $v )
			{
				$UserSettings->delete( $k, $edited_User->ID );
			}

			// Update user settings:
			if( $UserSettings->dbupdate() ) $Messages->add( T_('User feature settings have been changed.'), 'success');

			// PluginUserSettings
			$any_plugin_settings_updated = false;
			$Plugins->restart();
			while( $loop_Plugin = & $Plugins->get_next() )
			{
				$pluginusersettings = $loop_Plugin->GetDefaultUserSettings( $tmp_params = array('for_editing'=>true) );

				if( empty($pluginusersettings) )
				{
					continue;
				}

				foreach( $pluginusersettings as $k => $l_meta )
				{
					if( isset($l_meta['layout']) || ! empty($l_meta['no_edit']) )
					{ // a layout "setting" or not for editing
						continue;
					}

					$loop_Plugin->UserSettings->delete($k, $edited_User->ID);
				}

				// Let the plugin handle custom fields:
				$ok_to_update = $Plugins->call_method( $loop_Plugin->ID, 'PluginUserSettingsUpdateAction', $tmp_params = array(
					'User' => & $edited_User, 'action' => 'reset' ) );

				if( $ok_to_update === false )
				{
					$loop_Plugin->UserSettings->reset();
				}
				elseif( $loop_Plugin->UserSettings->dbupdate() )
				{
					$any_plugin_settings_updated = true;
				}
			}
			if( $any_plugin_settings_updated )
			{
				$Messages->add( T_('Usersettings of Plugins have been updated.'), 'success' );
			}

			// Always display the profile again:
			$action = 'edit_user';

			if( $reload_page )
			{ // reload the current page through header redirection:
				header_redirect( regenerate_url( '', 'user_ID='.$edited_User->ID.'&action='.$action, '', '&' ) ); // will save $Messages into Session
			}
			break;


		case 'promote':
			param( 'prom', 'string', true );

			if( !isset($edited_User)
			    || ! in_array( $prom, array('up', 'down') )
			    || ( $prom == 'up' && $edited_User->get('level') > 9 )
			    || ( $prom == 'down' && $edited_User->get('level') < 1 )
			  )
			{
				$Messages->add( T_('Invalid promotion.'), 'error' );
			}
			else
			{
				$sql = '
					UPDATE T_users
					   SET user_level = user_level '.( $prom == 'up' ? '+' : '-' ).' 1
					 WHERE user_ID = '.$edited_User->ID;

				if( $DB->query( $sql ) )
				{
					$Messages->add( T_('User level changed.'), 'success' );
				}
				else
				{
					$Messages->add( sprintf( 'Couldn\'t change %s\'s level.', $edited_User->login ), 'error' );
				}
			}
			break;


		case 'delete_user':
			/*
			 * Delete user
			 */
			if( !isset($edited_User) )
				debug_die( 'no User set' );

			if( $edited_User->ID == $current_User->ID )
			{
				$Messages->add( T_('You can\'t delete yourself!'), 'error' );
				$action = 'view_user';
				break;
			}
			if( $edited_User->ID == 1 )
			{
				$Messages->add( T_('You can\'t delete User #1!'), 'error' );
				$action = 'view_user';
				break;
			}

			$fullname = $edited_User->dget( 'fullname' );
			if( param( 'confirm', 'integer', 0 ) )
			{ // confirmed, Delete from DB:
				if ( ! empty( $fullname ) )
				{
					$msg = sprintf( T_('User &laquo;%s&raquo; [%s] deleted.'), $fullname, $edited_User->dget( 'login' ) );
				}
				else
				{
					$msg = sprintf( T_('User &laquo;%s&raquo; deleted.'), $edited_User->dget( 'login' ) );
				}

				$edited_User->dbdelete( $Messages );
				unset($edited_User);
				forget_param('user_ID');
				$Messages->add( $msg, 'success' );
				$action = 'list';
			}
			else
			{	// not confirmed, Check for restrictions:
				memorize_param( 'user_ID', 'integer', true );
				if ( ! empty( $fullname ) )
				{
					$msg = sprintf( T_('Cannot delete User &laquo;%s&raquo; [%s]'), $fullname, $edited_User->dget( 'login' ) );
				}
				else
				{
					$msg = sprintf( T_('Cannot delete User &laquo;%s&raquo;'), $edited_User->dget( 'login' ) );
				}

				if( ! $edited_User->check_delete( $msg ) )
				{	// There are restrictions:
					$action = 'view_user';
				}
			}
			break;


		case 'del_settings_set':
			// Delete a set of an array type setting:
			param( 'plugin_ID', 'integer', true );
			param( 'set_path' );

			$admin_Plugins = & get_Cache('Plugins_admin');
			$admin_Plugins->restart();
			$edit_Plugin = & $admin_Plugins->get_by_ID($plugin_ID);

			load_funcs('plugins/_plugin.funcs.php');
			_set_setting_by_path( $edit_Plugin, 'UserSettings', $set_path, NULL );

			$edit_Plugin->Settings->dbupdate();

			$action = 'edit_user';

			break;


		case 'add_settings_set': // delegates to edit_settings
			// Add a new set to an array type setting:
			param( 'plugin_ID', 'integer', true );
			param( 'set_path', 'string', '' );

			$admin_Plugins = & get_Cache('Plugins_admin');
			$admin_Plugins->restart();
			$edit_Plugin = & $admin_Plugins->get_by_ID($plugin_ID);

			load_funcs('plugins/_plugin.funcs.php');
			_set_setting_by_path( $edit_Plugin, 'UserSettings', $set_path, array() );

			$edit_Plugin->Settings->dbupdate();

			$action = 'edit_user';

			break;


		// ---- GROUPS --------------------------------------------------------------------------------------

		case 'new_group':
			// We want to create a new group:
			if( isset( $edited_Group ) )
			{ // We want to use a template
				$new_Group = $edited_Group; // Copy !
				$new_Group->set( 'ID', 0 );
				$edited_Group = & $new_Group;
			}
			else
			{ // We use an empty group:
				$edited_Group = & new Group();
			}
			break;


		case 'groupupdate':
			if( empty($edited_Group) || !is_object($edited_Group) )
			{
				$Messages->add( 'No group set!' ); // Needs no translation, should be prevented by UI.
				$action = 'list';
				break;
			}
			param( 'edited_grp_name', 'string' );

			param_check_not_empty( 'edited_grp_name', T_('You must provide a group name!') );

			// check if the group name already exists for another group
			$query = 'SELECT grp_ID FROM T_groups
			           WHERE grp_name = '.$DB->quote($edited_grp_name).'
			             AND grp_ID != '.$edited_Group->ID;
			if( $q = $DB->get_var( $query ) )
			{
				param_error( 'edited_grp_name',
					sprintf( T_('This group name already exists! Do you want to <a %s>edit the existing group</a>?'),
						'href="?ctrl=users&amp;grp_ID='.$q.'"' ) );
			}

			$edited_Group->set( 'name', $edited_grp_name );

			$edited_Group->set( 'perm_blogs', param( 'edited_grp_perm_blogs', 'string', true ) );
			$edited_Group->set( 'perm_bypass_antispam', param( 'apply_antispam', 'integer', 0 ) ? 0 : 1 );
			$edited_Group->set( 'perm_xhtmlvalidation', param( 'perm_xhtmlvalidation', 'string', true ) );
			$edited_Group->set( 'perm_xhtmlvalidation_xmlrpc', param( 'perm_xhtmlvalidation_xmlrpc', 'string', true ) );
			$edited_Group->set( 'perm_xhtml_css_tweaks', param( 'prevent_css_tweaks', 'integer', 0 ) ? 0 : 1 );
			$edited_Group->set( 'perm_xhtml_iframes', param( 'prevent_iframes', 'integer', 0 ) ? 0 : 1 );
			$edited_Group->set( 'perm_xhtml_javascript', param( 'prevent_javascript', 'integer', 0 ) ? 0 : 1 );
			$edited_Group->set( 'perm_xhtml_objects', param( 'prevent_objects', 'integer', 0 ) ? 0 : 1 );
			$edited_Group->set( 'perm_spamblacklist', param( 'edited_grp_perm_spamblacklist', 'string', true ) );
			$edited_Group->set( 'perm_templates', param( 'edited_grp_perm_templates', 'integer', 0 ) );
			$edited_Group->set( 'perm_stats', param( 'edited_grp_perm_stats', 'string', true ) );
			$edited_Group->set( 'perm_options', param( 'edited_grp_perm_options', 'string', true ) );
			$edited_Group->set( 'perm_files', param( 'edited_grp_perm_files', 'string', true ) );

			if( $edited_Group->ID != 1 )
			{ // Groups others than #1 can be prevented from logging in or editing users
				$edited_Group->set( 'perm_admin', param( 'edited_grp_perm_admin', 'string', true ) );
				$edited_Group->set( 'perm_users', param( 'edited_grp_perm_users', 'string', true ) );
			}

			if( $Messages->count( 'error' ) )
			{	// We have found validation errors:
				$action = 'edit_group';
				break;
			}

			if( $edited_Group->ID == 0 )
			{ // Insert into the DB:
				$edited_Group->dbinsert();
				$Messages->add( T_('New group created.'), 'success' );
			}
			else
			{ // Commit update to the DB:
				$edited_Group->dbupdate();
				$Messages->add( T_('Group updated.'), 'success' );
			}
			// Commit changes in cache:
			$GroupCache->add( $edited_Group );
			break;


		case 'delete_group':
			/*
			 * Delete group
			 */
			if( !isset($edited_Group) )
				debug_die( 'no Group set' );

			if( $edited_Group->ID == 1 )
			{
				$Messages->add( T_('You can\'t delete Group #1!'), 'error' );
				$action = 'view_group';
				break;
			}
			if( $edited_Group->ID == $Settings->get('newusers_grp_ID' ) )
			{
				$Messages->add( T_('You can\'t delete the default group for new users!'), 'error' );
				$action = 'view_group';
				break;
			}

			if( param( 'confirm', 'integer', 0 ) )
			{ // confirmed, Delete from DB:
				$msg = sprintf( T_('Group &laquo;%s&raquo; deleted.'), $edited_Group->dget( 'name' ) );
				$edited_Group->dbdelete( $Messages );
				unset($edited_Group);
				forget_param('grp_ID');
				$Messages->add( $msg, 'success' );
				$action = 'list';
			}
			else
			{	// not confirmed, Check for restrictions:
				memorize_param( 'grp_ID', 'integer', true );
				if( ! $edited_Group->check_delete( sprintf( T_('Cannot delete Group &laquo;%s&raquo;'), $edited_Group->dget( 'name' ) ) ) )
				{	// There are restrictions:
					$action = 'view_group';
				}
			}
			break;
	}
}


// We might delegate to this action from above:
if( $action == 'edit_user' )
{
	$Plugins->trigger_event( 'PluginUserSettingsEditAction', $tmp_params = array( 'User' => & $edited_User ) );

	$Session->delete( 'core.changepwd.request_id' ); // delete the request_id for password change request (from /htsrv/login.php)
}


// Display <html><head>...</head> section! (Note: should be done early if actions do not redirect)
$AdminUI->disp_html_head();

// Display title, menu, messages, etc. (Note: messages MUST be displayed AFTER the actions)
$AdminUI->disp_body_top();


/*
 * Display appropriate payload:
 */
switch( $action )
{
	case 'nil':
		// Display NO payload!
		break;


		case 'delete_user':
			// We need to ask for confirmation:
			$fullname = $edited_User->dget( 'fullname' );
			if ( ! empty( $fullname ) )
			{
				$msg = sprintf( T_('Delete user &laquo;%s&raquo; [%s]?'), $fullname, $edited_User->dget( 'login' ) );
			}
			else
			{
				$msg = sprintf( T_('Delete user &laquo;%s&raquo;?'), $edited_User->dget( 'login' ) );
			}

			$edited_User->confirm_delete( $msg, $action, get_memorized( 'action' ) );
		case 'new_user':
		case 'view_user':
		case 'edit_user':
			// Display user form:
			$AdminUI->disp_view( 'users/views/_user.form.php' );
			break;


		case 'delete_group':
			// We need to ask for confirmation:
			$edited_Group->confirm_delete(
					sprintf( T_('Delete group &laquo;%s&raquo;?'), $edited_Group->dget( 'name' ) ),
					$action, get_memorized( 'action' ) );
		case 'new_group':
		case 'edit_group':
		case 'view_group':
			// Display group form:
			$AdminUI->disp_view( 'users/views/_group.form.php' );
			break;


	case 'promote':
	default:
		// Display user list:
		// NOTE: we don't want this (potentially very long) list to be displayed again and again)
		$AdminUI->disp_payload_begin();
		$AdminUI->disp_view( 'users/views/_user_list.view.php' );
		$AdminUI->disp_payload_end();
}


// Display body bottom, debug info and close </html>:
$AdminUI->disp_global_footer();

/*
 * $Log$
 * Revision 1.24  2009/09/07 23:35:50  fplanque
 * cleanup
 *
 * Revision 1.23  2009/09/07 14:26:49  efy-maxim
 * Country field has been added to User form (but without updater)
 *
 * Revision 1.22  2009/08/30 19:54:22  fplanque
 * less translation messgaes for infrequent errors
 *
 * Revision 1.21  2009/05/31 12:36:04  tblue246
 * minor
 *
 * Revision 1.20  2009/05/31 11:37:34  tblue246
 * doc
 *
 * Revision 1.19  2009/05/31 05:16:44  sam2kb
 * This was probably the oldest bug ;)
 *
 * Revision 1.18  2009/05/26 19:31:59  fplanque
 * Plugins can now have Settings that are specific to each blog.
 *
 * Revision 1.17  2009/03/22 17:20:32  fplanque
 * I think I forgot that earlier
 *
 * Revision 1.15  2009/03/20 03:38:04  fplanque
 * rollback -- http://forums.b2evolution.net/viewtopic.php?t=18269
 *
 * Revision 1.12  2009/03/08 23:57:46  fplanque
 * 2009
 *
 * Revision 1.11  2009/02/01 16:52:29  tblue246
 * Don't display the user's full name if it isn't set when deleting users. Fixes: http://forums.b2evolution.net/viewtopic.php?t=17822
 *
 * Revision 1.10  2009/01/13 23:45:59  fplanque
 * User fields proof of concept
 *
 * Revision 1.9  2008/04/12 19:56:56  fplanque
 * bugfix
 *
 * Revision 1.8  2008/01/21 09:35:35  fplanque
 * (c) 2008
 *
 * Revision 1.7  2008/01/20 18:20:28  fplanque
 * Antispam per group setting
 *
 * Revision 1.6  2008/01/20 15:31:12  fplanque
 * configurable validation/security rules
 *
 * Revision 1.5  2008/01/19 15:45:29  fplanque
 * refactoring
 *
 * Revision 1.4  2008/01/19 10:57:11  fplanque
 * Splitting XHTML checking by group and interface
 *
 * Revision 1.3  2007/09/03 23:47:37  blueyed
 * Use singleton Plugins_admin
 *
 * Revision 1.2  2007/07/09 20:11:53  fplanque
 * admin skin switcher
 *
 * Revision 1.1  2007/06/25 11:01:44  fplanque
 * MODULES (refactored MVC)
 *
 * Revision 1.51  2007/06/19 20:41:11  fplanque
 * renamed generic functions to autoform_*
 *
 * Revision 1.50  2007/06/19 18:47:27  fplanque
 * Nuked unnecessary Param (or I'm missing something badly :/)
 *
 * Revision 1.49  2007/05/26 22:21:32  blueyed
 * Made $limit for Results configurable per user
 *
 * Revision 1.48  2007/04/26 00:11:15  fplanque
 * (c) 2007
 *
 * Revision 1.47  2007/03/08 00:46:17  blueyed
 * Fixed check for disallowing demouser-group-changes in demomode
 *
 * Revision 1.46  2007/02/21 22:21:30  blueyed
 * "Multiple sessions" user setting
 *
 * Revision 1.45  2007/02/21 21:17:20  blueyed
 * When resetting values to defaults, just delete all of them (defaults).
 *
 * Revision 1.44  2006/12/06 22:30:07  fplanque
 * Fixed this use case:
 * Users cannot register themselves.
 * Admin creates users that are validated by default. (they don't have to validate)
 * Admin can invalidate a user. (his email, address actually)
 *
 * Revision 1.43  2006/12/05 02:54:37  blueyed
 * Go to user profile after resetting to defaults; fixed handling of action in case of redirecting
 *
 * Revision 1.42  2006/12/03 19:01:57  blueyed
 * doc
 *
 * Revision 1.41  2006/12/03 16:37:14  fplanque
 * doc
 *
 * Revision 1.40  2006/11/24 18:27:23  blueyed
 * Fixed link to b2evo CVS browsing interface in file docblocks
 *
 * Revision 1.39  2006/11/24 18:06:02  blueyed
 * Handle saving of $Messages centrally in header_redirect()
 *
 * Revision 1.38  2006/11/15 21:14:04  blueyed
 * "Restore defaults" in user profile
 *
 * Revision 1.37  2006/11/13 20:49:52  fplanque
 * doc/cleanup :/
 *
 * Revision 1.36  2006/11/09 23:40:57  blueyed
 * Fixed Plugin UserSettings array type editing; Added jquery and use it for AJAHifying Plugin (User)Settings editing of array types
 *
 * Revision 1.35  2006/10/30 19:00:36  blueyed
 * Lazy-loading of Plugin (User)Settings for PHP5 through overloading
 */
?>
