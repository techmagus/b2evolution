<?php
/**
 * This file implements the {@link Plugins_admin} class, which gets used for administrative
 * handling of the {@link Plugin Plugins}.
 *
 * This file is part of the b2evolution/evocms project - {@link http://b2evolution.net/}.
 * See also {@link http://sourceforge.net/projects/evocms/}.
 *
 * @copyright (c)2003-2010 by Francois PLANQUE - {@link http://fplanque.net/}.
 * Parts of this file are copyright (c)2006 by Daniel HAHLER - {@link http://daniel.hahler.de/}.
 *
 * @license http://b2evolution.net/about/license.html GNU General Public License (GPL)
 *
 * {@internal Open Source relicensing agreement:
 * Daniel HAHLER grants Francois PLANQUE the right to license
 * Daniel HAHLER's contributions to this file and the b2evolution project
 * under any OSI approved OSS license (http://www.opensource.org/licenses/).
 * }}
 *
 * @package plugins
 *
 * @author blueyed: Daniel HAHLER
 *
 * @version $Id$
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );


load_class( 'plugins/model/_plugins.class.php', 'Plugins' );


/**
 * A Plugins object that loads all Plugins, not just the enabled ones.
 * This is needed for the backoffice plugin management.
 *
 * This extends the basic Plugins by adding all the functionnality needed for administering plugins
 * in addition to just using the already enabled plugins.
 *
 * @package plugins
 */
class Plugins_admin extends Plugins
{
	/**
	 * Load all plugins (not just enabled ones).
	 */
	var $sql_load_plugins_table = '
			SELECT plug_ID, plug_priority, plug_classname, plug_code, plug_name, plug_shortdesc, plug_apply_rendering, plug_status, plug_version, plug_spam_weight
			  FROM T_plugins
			 ORDER BY plug_priority, plug_classname';


	/**
	 * Get the list of all events/hooks supported by the plugin framework.
	 *
	 * Also puts in additional events provided by plugins.
	 * fp> please provide an example/use case
	 *
	 * Additional to the returned event methods (which can be disabled), there are internal
	 * ones which just get called on the plugin (and get not remembered in T_pluginevents), e.g.:
	 *  - AfterInstall
	 *  - BeforeEnable
	 *  - BeforeDisable
	 *  - BeforeInstall
	 *  - BeforeUninstall
	 *  - BeforeUninstallPayload
	 *  - DisplaySkin (called on a skin from {@link GetProvidedSkins()})
	 *  - ExecCronJob
	 *  - GetDefaultSettings
	 *  - GetDefaultUserSettings
	 *  - GetExtraEvents
	 *  - GetHtsrvMethods
	 *  - PluginInit
	 *  - PluginSettingsUpdateAction (Called as action before updating the plugin's settings)
	 *  - PluginSettingsEditAction (Called as action before editing the plugin's settings)
	 *  - PluginSettingsEditDisplayAfter (Called after standard plugin settings are displayed for editing)
	 *  - PluginSettingsValidateSet (Called before setting a plugin's setting in the backoffice)
	 *  - PluginUserSettingsUpdateAction (Called as action before updating the plugin's user settings)
	 *  - PluginUserSettingsEditDisplayAfter (Called after displaying normal user settings)
	 *  - PluginUserSettingsValidateSet (Called before setting a plugin's user setting in the backoffice)
	 *  - PluginVersionChanged (Called when we detect a version change)
	 *
	 *  The max length of event names is 40 chars (T_pluginevents.pevt_event).
	 *
	 * @internal When adding a new event, please make sure to add a description as well.
	 *           Please also add a new well-documented method to the "Plugin" class.
	 *
	 * @return array Name of event (key) => description (value)
	 */
	function get_supported_events()
	{
		static $supported_events;

		if( empty( $supported_events ) )
		{
			$supported_events = array(
				'AdminAfterPageFooter' => 'This gets called after the backoffice HTML footer has been displayed.',
				'AdminDisplayEditorButton' => 'Display action buttons on the edit screen(s)',
				'AdminDisplayToolbar' => 'Display a toolbar on the edit screen(s)',
				'AdminDisplayCommentFormFieldset' => 'Display form fieldsets on the backoffice comment editing form',
				'AdminDisplayItemFormFieldset' => 'Display form fieldsets on the backoffice item editing screen(s)',
				'AdminEndHtmlHead' => 'This gets called at the end of the HTML HEAD section in backoffice skins',
				'AdminAfterEvobarInit' => 'This gets called after the Evobar menu has been initialized.',
				'AdminAfterMenuInit' => 'This gets called after the backoffice menu has been initialized.',
				'AdminTabAction' => 'This gets called before AdminTabPayload when the Tools tab for the plugin is selected; no output allowed!',
				'AdminTabPayload' => 'This gets called when the Tools tab for the plugin is selected and content should be displayed.',
				'AdminToolAction' => '', // Tblue> Not used yet/anymore?
				'AdminToolPayload' => 'This gets called when the plugin\'s block in the Tools menu should be displayed.',

				'AdminBeforeItemEditCreate' => 'This gets called before a new item gets created from the backoffice.',
				'AdminBeforeItemEditUpdate' => 'This gets called before an existing item gets updated from the backoffice.',
				'AdminBeforeItemEditDelete' => 'This gets called before an existing item gets deleted from the backoffice.',

				'AdminBeginPayload' => 'This gets called before the main payload in the backoffice is displayed.',

				'CacheObjects' => 'Cache data objects.',
				'CachePageContent' => 'Cache page content.',
				'CacheIsCollectingContent' => 'Gets asked for if we are generating cached content.',

				'AfterCommentDelete' => 'Gets called after a comment has been deleted from the database.',
				'AfterCommentInsert' => 'Gets called after a comment has been inserted into the database.',
				'AfterCommentUpdate' => 'Gets called after a comment has been updated in the database.',
				
				'AfterCollectionDelete' => 'Gets called after a blog has been deleted from the database.',
				'AfterCollectionInsert' => 'Gets called after a blog has been inserted into the database.',
				'AfterCollectionUpdate' => 'Gets called after a blog has been updated in the database.',
				
				'GetCollectionKinds' => 'Defines blog kinds, their names and description.',
				'InitCollectionKinds' => 'Defines blog settings by its kind.',

				'AfterItemDelete' => 'This gets called after an item has been deleted from the database.',
				'PrependItemInsertTransact' => 'This gets called before an item is inserted into the database.',
				'AfterItemInsert' => 'This gets called after an item has been inserted into the database.',
				'PrependItemUpdateTransact' => 'This gets called before an item gets updated in the database..',
				'AfterItemUpdate' => 'This gets called after an item has been updated in the database.',
				'AppendItemPreviewTransact' => 'This gets called when instantiating an item for preview.',

 				'FilterItemContents' => 'Filters the content of a post/item right after input.',
 				'UnfilterItemContents' => 'Unfilters the content of a post/item right before editing.',

 				// fp> rename to "PreRender"
				'RenderItemAsHtml' => 'Renders content when generated as HTML.',
				'RenderItemAsXml' => 'Renders content when generated as XML.',
				'RenderItemAsText' => 'Renders content when generated as plain text.',

				// fp> rename to "DispRender"
				// dh> TODO: those do not get called anymore!
				'DisplayItemAsHtml' => 'Called on an item when it gets displayed as HTML.',
				'DisplayItemAsXml' => 'Called on an item when it gets displayed as XML.',
				'DisplayItemAsText' => 'Called on an item when it gets displayed as text.',

				// fp> These is actually RENDERing, right?
				// TODO: Rename to "DispRender"
				'FilterCommentAuthor' => 'Filters the comment author.',
				'FilterCommentAuthorUrl' => 'Filters the URL of the comment author.',
				'FilterCommentContent' => 'Filters the content of a comment.',

				'AfterUserDelete' => 'This gets called after an user has been deleted from the database.',
				'AfterUserInsert' => 'This gets called after an user has been inserted into the database.',
				'AfterUserUpdate' => 'This gets called after an user has been updated in the database.',

				// fp> This is actually RENDERing, right?
				// TODO: Rename to "DispRender"
				'FilterIpAddress' => 'Called when displaying an IP address.',

				'ItemApplyAsRenderer' => 'Asks the plugin if it wants to apply as a renderer for an item.',
				'ItemCanComment' => 'Asks the plugin if an item can receive comments/feedback.',
				'ItemSendPing' => 'Send a ping to a service about new items.',
				'ItemViewsIncreased' => 'Called when the view counter of an item got increased.',

				'SkinTag' => 'This method gets invoked when a plugin is called by its code. Providing this method causes the plugin to be listed as a widget.',

				'AppendHitLog' => 'Called when a hit gets logged, but before it gets recorded.',
				
				'BeforeThumbCreate' => 'This gets called before an image thumbnail gets created.',
				'AfterFileUpload' => 'Called before an uploaded file gets saved on server.',

				'DisplayCommentToolbar' => 'Display a toolbar on the public feedback form',
				'DisplayCommentFormButton' => 'Called in the submit button section of the frontend comment form.',
				'DisplayCommentFormFieldset' => 'Called at the end of the frontend comment form.',
				'DisplayMessageFormButton' => 'Called in the submit button section of the frontend message form.',
				'DisplayMessageFormFieldset' => 'Called at the end of the frontend message form.',
				'DisplayLoginFormFieldset' => 'Called when displaying the "Login" form.',
				'DisplayRegisterFormFieldset' => 'Called when displaying the "Register" form.',
				'DisplayValidateAccountFormFieldset' => 'Called when displaying the "Validate account" form.',
				'DisplayProfileFormFieldset' => 'Called when displaying the "User profile" form.',

				'ProfileFormSent' => 'Called when a private profile form has been sent and gets received.',
				'CommentFormSent' => 'Called when a public comment form has been sent and gets received.',
				'BeforeCommentFormInsert' => 'Called before a comment gets recorded through the public comment form.',
				'AfterCommentFormInsert' => 'Called after a comment has been added through public form.',

				'BeforeTrackbackInsert' => 'Gets called before a trackback gets recorded.',
				'AfterTrackbackInsert' => 'Gets called after a trackback has been recorded.',

				'LoginAttempt' => 'Called when a user tries to login.',
				'LoginAttemptNeedsRawPassword' => 'A plugin has to return true here, if it needs a raw (un-hashed) password in LoginAttempt.',
				'AlternateAuthentication' => 'Called at the end of the login process, if the user did not try to login, the session has no user attached or only the username and no password is given (see Plugin::AlternateAuthentication() for more info).',
				'MessageFormSent' => 'Called when the "Message to user" form has been submitted.',
				'MessageFormSentCleanup' => 'Called after a email message has been sent through public form.',
				'Logout' => 'Called when a user logs out.',

				'GetSpamKarmaForComment' => 'Asks plugin for the spam karma of a comment/trackback.',

				// Other Plugins can use this:
				'CaptchaValidated' => 'Validate the test from CaptchaPayload to detect humans.',
				'CaptchaValidatedCleanup' => 'Cleanup data used for CaptchaValidated.',
				'CaptchaPayload' => 'Provide a turing test to detect humans.',

				'RegisterFormSent' => 'Called when the "Register" form has been submitted.',
				'ValidateAccountFormSent' => 'Called when the "Validate account" form has been submitted.',
				'AppendUserRegistrTransact' => 'Gets appended to the transaction that creates a new user on registration.',
				'AfterUserRegistration' => 'Gets called after a new user has registered.',

				'SessionLoaded' => 'Gets called after $Session is initialized, quite early.',
				'BeforeSessionsDelete' => 'Gets called when sessions are being pruned to enable plugin house cleaning, plugin might change the sess_lastseen timestamp of any sessions they want to keep',

				'AfterLoginAnonymousUser' => 'Gets called at the end of the login procedure for anonymous visitors.',
				'AfterLoginRegisteredUser' => 'Gets called at the end of the login procedure for registered users.',

				'BeforeBlogDisplay' => 'Gets called before a (part of the blog) gets displayed.',
				'SkinBeginHtmlHead' => 'Gets called at the top of the HTML HEAD section in a skin.',
				'SkinEndHtmlBody' => 'Gets called at the end of the skin\'s HTML BODY section.',
				'DisplayTrackbackAddr' => 'Called to display the trackback URL for an item.',

				'GetCronJobs' => 'Gets a list of implemented cron jobs.',
				'GetProvidedSkins' => 'Get a list of "skins" handled by the plugin.',

				'PluginUserSettingsEditAction' => 'Called as action before editing a user\'s settings.',

				// allow plugins to handle $disp modes
				'GetHandledDispModes' => 'Called when building possible $disp list',
				'HandleDispMode' => 'Called when displaying $disp',
			);

			if( ! defined('EVO_IS_INSTALLING') || ! EVO_IS_INSTALLING )
			{ // only call this, if we're not in the process of installation, to avoid errors from Plugins in this case!

				// Let Plugins add additional events (if they trigger those events themselves):
				// fp> please provide an example/use case
				$this->load_plugins_table();

				$rev_sorted_IDs = array_reverse( $this->sorted_IDs ); // so higher priority overwrites lower (just for desc)

				foreach( $rev_sorted_IDs as $plugin_ID )
				{
					$Plugin = & $this->get_by_ID( $plugin_ID );

					if( ! $Plugin )
					{
						continue;
					}

					$extra_events = $Plugin->GetExtraEvents();
					if( is_array($extra_events) )
					{
						$supported_events = array_merge( $supported_events, $extra_events );
					}
				}
			}
		}

		return $supported_events;
	}


	/**
	 * Un-register a plugin, only if forced.
	 *
	 * This does not un-install it from DB, just from the internal indexes.
	 *
	 * @param Plugin
	 * @param boolean Force unregistering
	 * @return boolean True, if unregistered
	 */
	function unregister( & $Plugin, $force = false )
	{
		if( ! $force )
		{
			return false;
		}

		return parent::unregister($Plugin, $force);
	}


	/**
	 * Count # of registrations of same plugin.
	 *
	 * Plugins with negative ID (auto-generated; not installed (yet)) will not get considered.
	 *
	 * @param string class name
	 * @return int # of regs
	 */
	function count_regs( $classname )
	{
		$count = 0;

		foreach( $this->sorted_IDs as $plugin_ID )
		{
			$Plugin = & $this->get_by_ID( $plugin_ID );
			if( $Plugin && $Plugin->classname == $classname && $Plugin->ID > 0 )
			{
				$count++;
			}
		}
		return $count;
	}


	/**
	 * Discover and register all available plugins in the {@link $plugins_path} folder/subfolders.
	 */
	function discover()
	{
		global $Messages, $Debuglog, $Timer;

		$Timer->resume('plugins_discover');

		$Debuglog->add( 'Discovering plugins...', 'plugins' );

		// too inefficient: foreach( get_filenames( $this->plugins_path, true, false ) as $path )

		// Get subdirs in $this->plugins_path
		$subdirs = array();
		$subdirs = get_filenames( $this->plugins_path, false, true, true, false );
		
		if( empty($subdirs) ) 
			return;

		// Skip plugins which are in a directory that starts with an underscore ("_")
		foreach( $subdirs as $k => $v )
		{
			$v_bn = basename($v);
			if( substr(basename($v_bn), 0, 1) == '_' || substr($v_bn, -7) != '_plugin' )
			{
				unset($subdirs[$k]);
			}
		}
		$subdirs[] = $this->plugins_path;

		foreach( $subdirs as $subdir )
		{
			// Some directories may be unreadable ( get_filenames returns false which is not an array )
			if( !$files = get_filenames( $subdir, true, false, true, false ) ) 
				continue;

			foreach( $files as $filename )
			{
				if( ! (preg_match( '~/_([^/]+)\.plugin\.php$~', $filename, $match ) && is_file( $filename )) )
				{
					continue;
				}

				$classname = $match[1].'_plugin';

				if( $this->get_by_classname($classname) )
				{
					$Debuglog->add( 'Skipping duplicate plugin (classname '.$classname.')!', array('error', 'plugins') );
					continue;
				}
				$this->register( $classname, 0, -1, NULL, $filename ); // auto-generate negative ID; will return string on error.
			}
		}

		$Timer->pause('plugins_discover');
	}


	/**
	 * Get the list of all possible values for apply_rendering (defines when a rendering Plugin can apply).
	 *
	 * @todo Add descriptions.
	 *
	 * @param boolean Return an associative array with description for the values?
	 * @return array
	 */
	function get_apply_rendering_values( $with_desc = false )
	{
		static $apply_rendering_values;

		if( empty( $apply_rendering_values ) )
		{
			$apply_rendering_values = array(
					'stealth' => '',
					'always' => '',
					'opt-out' => '',
					'opt-in' => '',
					'lazy' => '',
					'never' => '',
				);
		}
		if( ! $with_desc )
		{
			return array_keys( $apply_rendering_values );
		}

		return $apply_rendering_values;
	}


	/**
	 * Discover plugin events from its source file.
	 *
	 * Get a list of methods that are supported as events out of the Plugin's
	 * class definition.
	 *
	 * @todo Extend to get list of defined classes and global functions and check this list before sourcing/including a Plugin! (prevent fatal error)
	 *
	 * @return array
	 */
	function get_registered_events( $Plugin )
	{
		global $Timer, $Debuglog;

		$Timer->resume( 'plugins_detect_events' );

		$plugin_class_methods = array();

		if( ! is_readable($Plugin->classfile_path) )
		{
			$Debuglog->add( 'get_registered_events(): "'.$Plugin->classfile_path.'" is not readable.', array('plugins', 'error') );
			return array();
		}

		if( ( $classfile_contents = @file_get_contents( $Plugin->classfile_path ) ) === false )
		{
			$Debuglog->add( 'get_registered_events(): "'.$Plugin->classfile_path.'" could not get read.', array('plugins', 'error') );
			return array();
		}

		$supported_events = array_keys( $this->get_supported_events() );

		// TODO: allow optional Plugin callback to get list of methods. Like Plugin::GetRegisteredEvents().
		// fp> bloated. what problem does it solve?
		// dh> With a captcha_base.class.php the actual plugin (extending the class) would have to define all the event methods and not just the methods to provide the tests.
		//     With a GetRegisteredEvents method in captcha_base.class.php this would not be required.
		//     The whole point of such a base class would be to simplify writing a captcha plugin and IMHO it's "bloated" to force a whole block of methods into it that do only call the parent method.

		// TODO: dh> only match in the relevant "class block"
		$had_func_token = false;
		foreach( token_get_all( $classfile_contents ) as $token )
		{
			if( ! is_array( $token ) )
			{	// Single characters does not interest us:
				continue;
			}

			if( $had_func_token && $token[0] == T_STRING )
			{	// We got a function name...
				if( ! in_array( $token[1], $plugin_class_methods )
					&& in_array( $token[1], $supported_events ) )
				{	// ...and it is unique and matches one of our supported events:
					$plugin_class_methods[] = $token[1];
				}

				// Search for the next "function" token:
				$had_func_token = false;
			}
			elseif( ! $had_func_token && $token[0] == T_FUNCTION )
			{	// Begin searching for the function name which must be a T_STRING:
				$had_func_token = true;
			}
		}

		if( ! count( $plugin_class_methods ) )
		{
			$Debuglog->add( 'No functions found in file "'.$Plugin->classfile_path.'".', array('plugins', 'error') );
			return array();
		}

		$Timer->pause( 'plugins_detect_events' );

		return $plugin_class_methods;
	}


	/**
	 * Install a plugin into DB.
	 *
	 * NOTE: this won't install necessary DB changes and not trigger {@link Plugin::AfterInstall}!
	 *
	 * @param string Classname of the plugin to install
	 * @param string Initial DB Status of the plugin ("enabled", "disabled", "needs_config", "broken")
	 * @param string Optional classfile path, if not default (used for tests).
	 * @return Plugin The installed Plugin (perhaps with $install_dep_notes set) or a string in case of error.
	 */
	function & install( $classname, $plug_status = 'enabled', $classfile_path = NULL )
	{
		global $DB, $Debuglog;

		// Load Plugins data from T_plugins (only once), ordered by priority.
		$this->load_plugins_table();

		// Register the plugin:
		$Plugin = & $this->register( $classname, 0, -1, NULL, $classfile_path ); // Auto-generates negative ID; New ID will be set a few lines below

		if( is_string($Plugin) )
		{ // return error message from register()
			return $Plugin;
		}

		if( isset($Plugin->number_of_installs)
		    && ( $this->count_regs( $Plugin->classname ) >= $Plugin->number_of_installs ) )
		{
			$this->unregister( $Plugin, true );
			$r = T_('The plugin cannot be installed again.');
			return $r;
		}

		$install_return = $Plugin->BeforeInstall();
		if( $install_return !== true )
		{
			$this->unregister( $Plugin, true );
			$r = T_('The installation of the plugin failed.');
			if( is_string($install_return) )
			{
				$r .= '<br />'.$install_return;
			}
			return $r;
		}

		// Dependencies:
		/*
		// We must check dependencies against installed Plugins ($Plugins)
		// TODO: not possible anymore.. check it..
		global $Plugins;
		$dep_msgs = $Plugins->validate_dependencies( $Plugin, 'enable' );
		*/
		$dep_msgs = $this->validate_dependencies( $Plugin, 'enable' );

		if( ! empty( $dep_msgs['error'] ) )
		{ // fatal errors (required dependencies):
			$this->unregister( $Plugin, true );
			$r = T_('Some plugin dependencies are not fulfilled:').' <ul><li>'.implode( '</li><li>', $dep_msgs['error'] ).'</li></ul>';
			return $r;
		}

		// All OK, install:
		if( empty($Plugin->code) )
		{
			$Plugin->code = NULL;
		}

		$Plugin->status = $plug_status;

		// Record into DB
		$DB->begin();

		$DB->query( '
				INSERT INTO T_plugins( plug_classname, plug_priority, plug_code, plug_apply_rendering, plug_version, plug_status )
				VALUES( "'.$classname.'", '.$Plugin->priority.', '.$DB->quote($Plugin->code).', '.$DB->quote($Plugin->apply_rendering).', '.$DB->quote($Plugin->version).', '.$DB->quote($Plugin->status).' ) ' );

		// Unset auto-generated ID info
		unset( $this->index_ID_Plugins[ $Plugin->ID ] );
		$key = array_search( $Plugin->ID, $this->sorted_IDs );

		// New ID:
		$Plugin->ID = $DB->insert_id;
		$this->index_ID_Plugins[ $Plugin->ID ] = & $Plugin;
		$this->index_ID_rows[ $Plugin->ID ] = array(
				'plug_ID' => $Plugin->ID,
				'plug_priority' => $Plugin->priority,
				'plug_classname' => $Plugin->classname,
				'plug_code' => $Plugin->code,
				'plug_apply_rendering' => $Plugin->apply_rendering,
				'plug_status' => $Plugin->status,
				'plug_version' => $Plugin->version,
			);
		$this->sorted_IDs[$key] = $Plugin->ID;

		$this->save_events( $Plugin );

		$DB->commit();

		$Debuglog->add( 'Installed plugin: '.$Plugin->name.' ID: '.$Plugin->ID, 'plugins' );

		if( ! empty($dep_msgs['note']) )
		{ // Add dependency notes
			$Plugin->install_dep_notes = $dep_msgs['note'];
		}

		// Do the stuff that we've skipped in register method at the beginning:

		$this->init_settings( $Plugin );

		$tmp_params = array('db_row' => $this->index_ID_rows[$Plugin->ID], 'is_installed' => false);

		if( $Plugin->PluginInit( $tmp_params ) === false && $this->unregister( $Plugin ) )
		{
			$Debuglog->add( 'Unregistered plugin, because PluginInit returned false.', 'plugins' );
			$Plugin = '';
		}

		if( ! defined('EVO_IS_INSTALLING') || ! EVO_IS_INSTALLING )
		{ // do not sort, if we're installing/upgrading.. instantiating Plugins might cause a fatal error!
			$this->sort();
		}

		return $Plugin;
	}


	/**
	 * Uninstall a plugin.
	 *
	 * Removes the Plugin, its Settings and Events from the database.
	 *
	 * @return boolean True on success
	 */
	function uninstall( $plugin_ID )
	{
		global $DB, $Debuglog;

		$Debuglog->add( 'Uninstalling plugin (ID '.$plugin_ID.')...', 'plugins' );

		$Plugin = & $this->get_by_ID( $plugin_ID ); // get the Plugin before any not loaded data might get deleted below

		$DB->begin();

		// Delete Plugin settings (constraints)
		$DB->query( "DELETE FROM T_pluginsettings
		              WHERE pset_plug_ID = $plugin_ID" );

		// Delete Plugin user settings (constraints)
		$DB->query( "DELETE FROM T_pluginusersettings
		              WHERE puset_plug_ID = $plugin_ID" );

		// Delete Plugin events (constraints)
		foreach( $DB->get_col( '
				SELECT pevt_event
				  FROM T_pluginevents
				 WHERE pevt_enabled = 1' ) as $event )
		{
			if( strpos($event, 'RenderItemAs') === 0 )
			{ // Clear pre-rendered content cache, if RenderItemAs* events get removed:
				$DB->query( 'DELETE FROM T_items__prerendering WHERE 1=1' );
				$ItemCache = & get_ItemCache();
				$ItemCache->clear();
				break;
			}
		}
		$DB->query( "DELETE FROM T_pluginevents
		              WHERE pevt_plug_ID = $plugin_ID" );

		// Delete from DB
		$DB->query( "DELETE FROM T_plugins
		              WHERE plug_ID = $plugin_ID" );

		$DB->commit();

		if( $Plugin )
		{
			$this->unregister( $Plugin, true );
		}

		$Debuglog->add( 'Uninstalled plugin (ID '.$plugin_ID.').', 'plugins' );
		return true;
	}


	/**
	 * (Re)load Plugin Events for enabled (normal use) or all (admin use) plugins.
	 *
	 * This is the same as {@link Plugins::load_events()} except that it loads all Plugins (not just enabled ones)
	 */
	function load_events()
	{
		global $Debuglog, $DB;

		$this->index_event_IDs = array();

		$Debuglog->add( 'Loading plugin events.', 'plugins' );
		foreach( $DB->get_results( '
				SELECT pevt_plug_ID, pevt_event
					FROM T_pluginevents INNER JOIN T_plugins ON pevt_plug_ID = plug_ID
				 WHERE pevt_enabled > 0
				 ORDER BY plug_priority, plug_classname', OBJECT, 'Loading plugin events' ) as $l_row )
		{
			$this->index_event_IDs[$l_row->pevt_event][] = $l_row->pevt_plug_ID;
		}
	}


	/**
	 * Save the events that the plugin provides into DB, while removing obsolete
	 * entries (that the plugin does not register anymore).
	 *
	 * @param Plugin Plugin to save events for
	 * @param array List of events to save as enabled for the Plugin.
	 *              By default all provided events get saved as enabled. Pass array() to discover only new ones.
	 * @param array List of events to save as disabled for the Plugin.
	 *              By default, no events get disabled. Disabling an event takes priority over enabling.
	 * @return boolean True, if events have changed, false if not.
	 */
	function save_events( $Plugin, $enable_events = NULL, $disable_events = NULL )
	{
		global $DB, $Debuglog;

		$r = false;

		$saved_events = array();
		foreach( $DB->get_results( '
				SELECT pevt_event, pevt_enabled
				  FROM T_pluginevents
				 WHERE pevt_plug_ID = '.$Plugin->ID ) as $l_row )
		{
			$saved_events[$l_row->pevt_event] = $l_row->pevt_enabled;
		}

		// Discover events from plugin's source file:
		$available_events = $this->get_registered_events( $Plugin );

		$obsolete_events = array_diff( array_keys($saved_events), $available_events );

		if( is_null( $enable_events ) )
		{ // Enable all events:
			$enable_events = $available_events;
		}
		if( is_null( $disable_events ) )
		{
			$disable_events = array();
		}
		if( $disable_events )
		{ // Remove events to be disabled from enabled ones:
			$enable_events = array_diff( $enable_events, $disable_events );
		}

		// New discovered events:
		$discovered_events = array_diff( $available_events, array_keys($saved_events), $enable_events, $disable_events );


		// Delete obsolete events from DB:
		if( $obsolete_events && $DB->query( '
				DELETE FROM T_pluginevents
				WHERE pevt_plug_ID = '.$Plugin->ID.'
				AND pevt_event IN ( "'.implode( '", "', $obsolete_events ).'" )' ) )
		{
			$r = true;
		}

		if( $discovered_events )
		{
			$DB->query( '
				INSERT INTO T_pluginevents( pevt_plug_ID, pevt_event, pevt_enabled )
				VALUES ( '.$Plugin->ID.', "'.implode( '", 1 ), ('.$Plugin->ID.', "', $discovered_events ).'", 1 )' );
			$r = true;

			$Debuglog->add( 'Discovered events ['.implode( ', ', $discovered_events ).'] for Plugin '.$Plugin->name, 'plugins' );
		}

		$new_events_enabled = array();
		if( $enable_events )
		{
			foreach( $enable_events as $l_event )
			{
				if( ! isset( $saved_events[$l_event] ) || ! $saved_events[$l_event] )
				{ // Event not saved yet or not enabled
					$new_events_enabled[] = $l_event;
				}
			}
			if( $new_events_enabled )
			{
				$DB->query( '
					REPLACE INTO T_pluginevents( pevt_plug_ID, pevt_event, pevt_enabled )
					VALUES ( '.$Plugin->ID.', "'.implode( '", 1 ), ('.$Plugin->ID.', "', $new_events_enabled ).'", 1 )' );
				$r = true;
			}
			$Debuglog->add( 'Enabled events ['.implode( ', ', $new_events_enabled ).'] for Plugin '.$Plugin->name, 'plugins' );
		}

		$new_events_disabled = array();
		if( $disable_events )
		{
			foreach( $disable_events as $l_event )
			{
				if( ! isset( $saved_events[$l_event] ) || $saved_events[$l_event] )
				{ // Event not saved yet or enabled
					$new_events_disabled[] = $l_event;
				}
			}
			if( $new_events_disabled )
			{
				$DB->query( '
					REPLACE INTO T_pluginevents( pevt_plug_ID, pevt_event, pevt_enabled )
					VALUES ( '.$Plugin->ID.', "'.implode( '", 0 ), ('.$Plugin->ID.', "', $new_events_disabled ).'", 0 )' );
				$r = true;
			}
			$Debuglog->add( 'Disabled events ['.implode( ', ', $new_events_disabled ).'] for Plugin '.$Plugin->name, 'plugins' );
		}

		if( $r )
		{ // Something has changed: Reload event index
			foreach( array_merge($obsolete_events, $discovered_events, $new_events_enabled, $new_events_disabled) as $event )
			{
				if( strpos($event, 'RenderItemAs') === 0 )
				{ // Clear pre-rendered content cache, if RenderItemAs* events have been added or removed:
					$DB->query( 'DELETE FROM T_items__prerendering WHERE 1=1' );
					$ItemCache = & get_ItemCache();
					$ItemCache->clear();
					break;
				}
			}

			$this->load_events();
		}

		return $r;
	}


	/**
	 * Set the apply_rendering value for a given Plugin ID.
	 *
	 * It makes sure that the index is handled and writes it to DB.
	 *
	 * @return boolean true if set to new value, false in case of error or if already set to same value
	 */
	function set_apply_rendering( $plugin_ID, $apply_rendering )
	{
		global $DB;

		if( ! in_array( $apply_rendering, $this->get_apply_rendering_values() ) )
		{
			debug_die( 'Plugin apply_rendering not in allowed list.' );
		}

		$Plugin = & $this->get_by_ID($plugin_ID);
		if( ! $Plugin )
		{
			return false;
		}

		if( $this->index_ID_rows[$Plugin->ID]['plug_apply_rendering'] == $apply_rendering )
		{ // Already set to same value
			return false;
		}

		$r = $DB->query( '
			UPDATE T_plugins
			  SET plug_apply_rendering = '.$DB->quote($apply_rendering).'
			WHERE plug_ID = '.$plugin_ID );

		$Plugin->apply_rendering = $apply_rendering;

		// Apply-rendering index:
		if( ! isset( $this->index_apply_rendering_codes[ $Plugin->apply_rendering ] )
		    || ! in_array( $Plugin->code, $this->index_apply_rendering_codes[ $Plugin->apply_rendering ] ) )
		{
			$this->index_apply_rendering_codes[ $Plugin->apply_rendering ][] = $Plugin->code;
		}

		return true;
	}


	/**
	 * Set the code for a given Plugin ID.
	 *
	 * It makes sure that the index is handled and writes it to DB.
	 *
	 * @param string Plugin ID
	 * @param string Code to set the plugin to
	 * @return boolean|integer|string
	 *   true, if already set to same value.
	 *   string: error message (already in use, wrong format)
	 *   1 in case of setting it into DB (number of affected rows).
	 *   false, if invalid Plugin.
	 */
	function set_code( $plugin_ID, $code )
	{
		global $DB;

		if( strlen( $code ) > 32 )
		{
			return T_( 'The maximum length of a plugin code is 32 characters.' );
		}

		// TODO: more strict check?! Just "[\w_-]+" as regexp pattern?
		if( strpos( $code, '.' ) !== false )
		{
			return T_( 'The plugin code cannot include a dot!' );
		}

		if( ! empty($code) && isset( $this->index_code_ID[$code] ) )
		{
			if( $this->index_code_ID[$code] == $plugin_ID )
			{ // Already set to same value
				return true;
			}
			else
			{
				return T_( 'The plugin code is already in use by another plugin.' );
			}
		}

		$Plugin = & $this->get_by_ID( $plugin_ID );
		if( ! $Plugin )
		{
			return false;
		}

		if( empty($code) )
		{
			$code = NULL;
		}
		else
		{ // update indexes
			$this->index_code_ID[$code] = & $Plugin->ID;
			$this->index_code_Plugins[$code] = & $Plugin;
		}

		// Update references to code:
		// TODO: dh> we might want to update item renderer codes and blog ping plugin codes here! (old code=>new code)

		$Plugin->code = $code;

		return $DB->query( '
			UPDATE T_plugins
			  SET plug_code = '.$DB->quote($code).'
			WHERE plug_ID = '.$plugin_ID );
	}


	/**
	 * Set the status of an event for a given Plugin.
	 *
	 * @return boolean True, if status has changed; false if not
	 */
	function set_event_status( $plugin_ID, $plugin_event, $enabled )
	{
		global $DB;

		$enabled = $enabled ? 1 : 0;

		$DB->query( '
			UPDATE T_pluginevents
			   SET pevt_enabled = '.$enabled.'
			 WHERE pevt_plug_ID = '.$plugin_ID.'
			   AND pevt_event = "'.$plugin_event.'"' );

		if( $DB->rows_affected )
		{
			$this->load_events();

			if( strpos($plugin_event, 'RenderItemAs') === 0 )
			{ // Clear pre-rendered content cache, if RenderItemAs* events have been added or removed:
				$DB->query( 'DELETE FROM T_items__prerendering WHERE 1=1' );
				$ItemCache = & get_ItemCache();
				$ItemCache->clear();
				break;
			}

			return true;
		}

		return false;
	}


	/**
	 * Set the priority for a given Plugin ID.
	 *
	 * It makes sure that the index is handled and writes it to DB.
	 *
	 * @return boolean|integer
	 *   true, if already set to same value.
	 *   false if another Plugin uses that priority already.
	 *   1 in case of setting it into DB.
	 */
	function set_priority( $plugin_ID, $priority )
	{
		global $DB;

		if( ! preg_match( '~^1?\d?\d$~', $priority ) ) // using preg_match() to catch floating numbers
		{
			debug_die( 'Plugin priority must be numeric (0-100).' );
		}

		$Plugin = & $this->get_by_ID($plugin_ID);
		if( ! $Plugin )
		{
			return false;
		}

		if( $Plugin->priority == $priority )
		{ // Already set to same value
			return true;
		}

		$r = $DB->query( '
			UPDATE T_plugins
			  SET plug_priority = '.$DB->quote($priority).'
			WHERE plug_ID = '.$plugin_ID );

		$Plugin->priority = $priority;

		// TODO: dh> should only re-sort, if sorted by priority before - if it should get re-sorted at all!
		//$this->sort();

		return $r;
	}


	/**
	 * Sort the list of plugins.
	 *
	 * WARNING: do NOT sort by anything else than priority unless you're handling a list of NOT-YET-INSTALLED plugins!
	 *
	 * @param string Order: 'priority' (default), 'name'
	 */
	function sort( $order = 'priority' )
	{
		$this->load_plugins_table();

		foreach( $this->sorted_IDs as $k => $plugin_ID )
		{ // Instantiate every plugin, so invalid ones do not get unregistered during sorting (crashes PHP, because $sorted_IDs gets changed etc)
			if( ! $this->get_by_ID( $plugin_ID ) )
			{
				unset($this->sorted_IDs[$k]);
			}
		}

		switch( $order )
		{
			case 'name':
				usort( $this->sorted_IDs, array( & $this, 'sort_Plugin_name') );
				break;

			case 'group':
				usort( $this->sorted_IDs, array( & $this, 'sort_Plugin_group') );
				break;

			default:
				// Sort array by priority:
				usort( $this->sorted_IDs, array( & $this, 'sort_Plugin_priority') );
		}

		$this->current_idx = -1;
	}

	/**
	 * Callback function to sort plugins by priority (and classname, if they have same priority).
	 */
	function sort_Plugin_priority( & $a_ID, & $b_ID )
	{
		$a_Plugin = & $this->get_by_ID( $a_ID );
		$b_Plugin = & $this->get_by_ID( $b_ID );

		$r = $a_Plugin->priority - $b_Plugin->priority;

		if( $r == 0 )
		{
			$r = strcasecmp( $a_Plugin->classname, $b_Plugin->classname );
		}

		return $r;
	}

	/**
	 * Callback function to sort plugins by name.
	 *
	 * WARNING: do NOT sort by anything else than priority unless you're handling a list of NOT-YET-INSTALLED plugins
	 */
	function sort_Plugin_name( & $a_ID, & $b_ID )
	{
		$a_Plugin = & $this->get_by_ID( $a_ID );
		$b_Plugin = & $this->get_by_ID( $b_ID );

		return strcasecmp( $a_Plugin->name, $b_Plugin->name );
	}


	/**
	 * Callback function to sort plugins by group, sub-group and name.
	 *
	 * Those, which have a group get sorted above the ones without one.
	 *
	 * WARNING: do NOT sort by anything else than priority unless you're handling a list of NOT-YET-INSTALLED plugins
	 */
	function sort_Plugin_group( & $a_ID, & $b_ID )
	{
		$a_Plugin = & $this->get_by_ID( $a_ID );
		$b_Plugin = & $this->get_by_ID( $b_ID );

		// first check if both have a group (-1: only A has a group; 1: only B has a group; 0: both have a group or no group):
		$r = (int)empty($a_Plugin->group) - (int)empty($b_Plugin->group);
		if( $r != 0 )
		{
			return $r;
		}

		// Compare Group
		$r = strcasecmp( $a_Plugin->group, $b_Plugin->group );
		if( $r != 0 )
		{
			return $r;
		}

		// Compare Sub Group
		$r = strcasecmp( $a_Plugin->sub_group, $b_Plugin->sub_group );
		if( $r != 0 )
		{
			return $r;
		}

		// Compare Name
		return strcasecmp( $a_Plugin->name, $b_Plugin->name );
	}


	/**
	 * Validate dependencies of a Plugin.
	 *
	 * @param Plugin
	 * @param string Mode of check: either 'enable' or 'disable'
	 * @return array The key 'note' holds an array of notes (recommendations), the key 'error' holds a list
	 *               of messages for dependency errors.
	 */
	function validate_dependencies( & $Plugin, $mode )
	{
		global $DB, $app_name;
		global $app_version;

		$msgs = array();

		if( $mode == 'disable' )
		{ // Check the whole list of installed plugins if they depend on our Plugin or it's (set of) events.
			$required_by_plugin = array(); // a list of plugin classnames that require our poor Plugin

			foreach( $this->sorted_IDs as $validate_against_ID )
			{
				if( $validate_against_ID == $Plugin->ID )
				{ // the plugin itself
					continue;
				}

				$against_Plugin = & $this->get_by_ID($validate_against_ID);

				if( $against_Plugin->status != 'enabled' )
				{ // The plugin is not enabled (this check is needed when checking deps with the Plugins_admin class)
					continue;
				}

				$deps = $against_Plugin->GetDependencies();

				if( empty($deps['requires']) )
				{ // has no dependencies
					continue;
				}

				if( ! empty($deps['requires']['plugins']) )
				{
					foreach( $deps['requires']['plugins'] as $l_req_plugin )
					{
						if( ! is_array($l_req_plugin) )
						{
							$l_req_plugin = array( $l_req_plugin, 0 );
						}

						if( $Plugin->classname == $l_req_plugin[0] )
						{ // our plugin is required by this one, check if it is the only instance
							if( $this->count_regs($Plugin->classname) < 2 )
							{
								$required_by_plugin[] = $against_Plugin->classname;
							}
						}
					}
				}

				if( ! empty($deps['requires']['events_by_one']) )
				{
					foreach( $deps['requires']['events_by_one'] as $req_events )
					{
						// Get a list of plugins that provide all the events
						$provided_by = array_keys( $this->get_list_by_all_events( $req_events ) );

						if( in_array($Plugin->ID, $provided_by) && count($provided_by) < 2 )
						{ // we're the only Plugin which provides this set of events
							$msgs['error'][] = sprintf( T_( 'The events %s are required by %s (ID %d).' ), implode_with_and($req_events), $against_Plugin->classname, $against_Plugin->ID );
						}
					}
				}

				if( ! empty($deps['requires']['events']) )
				{
					foreach( $deps['requires']['events'] as $req_event )
					{
						// Get a list of plugins that provide all the events
						$provided_by = array_keys( $this->get_list_by_event( $req_event ) );

						if( in_array($Plugin->ID, $provided_by) && count($provided_by) < 2 )
						{ // we're the only Plugin which provides this event
							$msgs['error'][] = sprintf( T_( 'The event %s is required by %s (ID %d).' ), $req_event, $against_Plugin->classname, $against_Plugin->ID );
						}
					}
				}

				// TODO: We might also handle the 'recommends' and add it to $msgs['note']
			}

			if( ! empty( $required_by_plugin ) )
			{ // Prepend the message to the beginning, because it's the most restrictive (IMHO)
				$required_by_plugin = array_unique($required_by_plugin);
				if( ! isset($msgs['error']) )
				{
					$msgs['error'] = array();
				}
				array_unshift( $msgs['error'], sprintf( T_('The plugin is required by the following plugins: %s.'), implode_with_and($required_by_plugin) ) );
			}

			return $msgs;
		}


		// mode 'enable':
		$deps = $Plugin->GetDependencies();

		if( empty($deps) )
		{
			return array();
		}

		foreach( $deps as $class => $dep_list ) // class: "requires" or "recommends"
		{
			if( ! is_array($dep_list) )
			{ // Invalid format: "throw" error (needs not translation)
				return array(
						'error' => array( 'GetDependencies() did not return array of arrays. Please contact the plugin developer.' )
					);
			}
			foreach( $dep_list as $type => $type_params )
			{
				switch( $type )
				{
					case 'events_by_one':
						foreach( $type_params as $sub_param )
						{
							if( ! is_array($sub_param) )
							{ // Invalid format: "throw" error (needs not translation)
								return array(
										'error' => array( 'GetDependencies() did not return array of arrays for "events_by_one". Please contact the plugin developer.' )
									);
							}
							if( ! $this->are_events_available( $sub_param, true ) )
							{
								if( $class == 'recommends' )
								{
									$msgs['note'][] = sprintf( T_( 'The plugin recommends a plugin which provides all of the following events: %s.' ), implode_with_and( $sub_param ) );
								}
								else
								{
									$msgs['error'][] = sprintf( T_( 'The plugin requires a plugin which provides all of the following events: %s.' ), implode_with_and( $sub_param ) );
								}
							}
						}
						break;

					case 'events':
						if( ! $this->are_events_available( $type_params, false ) )
						{
							if( $class == 'recommends' )
							{
								$msgs['note'][] = sprintf( T_( 'The plugin recommends plugins which provide the events: %s.' ), implode_with_and( $type_params ) );
							}
							else
							{
								$msgs['error'][] = sprintf( T_( 'The plugin requires plugins which provide the events: %s.' ), implode_with_and( $type_params ) );
							}
						}
						break;

					case 'plugins':
						if( ! is_array($type_params) )
						{ // Invalid format: "throw" error (needs not translation)
							return array(
									'error' => array( 'GetDependencies() did not return array of arrays for "plugins". Please contact the plugin developer.' )
								);
						}
						foreach( $type_params as $plugin_req )
						{
							if( ! is_array($plugin_req) )
							{
								$plugin_req = array( $plugin_req, '0' );
							}
							elseif( ! isset($plugin_req[1]) )
							{
								$plugin_req[1] = '0';
							}

							if( $versions = $DB->get_col( '
								SELECT plug_version FROM T_plugins
								 WHERE plug_classname = '.$DB->quote($plugin_req[0]).'
									 AND plug_status = "enabled"' ) )
							{
								// Clean up version from CVS Revision prefix/suffix:
								$versions[] = $plugin_req[1];
								$clean_versions = preg_replace( array( '~^(CVS\s+)?\$'.'Revision:\s*~i', '~\s*\$$~' ), '', $versions );
								$clean_req_ver = array_pop($clean_versions);
								usort( $clean_versions, 'version_compare' );
								$clean_oldest_enabled = array_shift($clean_versions);

								if( version_compare( $clean_oldest_enabled, $clean_req_ver, '<' ) )
								{ // at least one instance of the installed plugins is not the current version
									$msgs['error'][] = sprintf( T_( 'The plugin requires at least version %s of the plugin %s, but you have %s.' ), $plugin_req[1], $plugin_req[0], $clean_oldest_enabled );
								}
							}
							else
							{ // no plugin existing
								if( $class == 'recommends' )
								{
									$recommends[] = $plugin_req[0];
								}
								else
								{
									$requires[] = $plugin_req[0];
								}
							}
						}

						if( ! empty( $requires ) )
						{
							$msgs['error'][] = sprintf( T_( 'The plugin requires the plugins: %s.' ), implode_with_and( $requires ) );
						}

						if( ! empty( $recommends ) )
						{
							$msgs['note'][] = sprintf( T_( 'The plugin recommends to install the plugins: %s.' ), implode_with_and( $recommends ) );
						}
						break;


					case 'app_min':
						// min b2evo version:
						if( ! version_compare( $app_version, $type_params, '>=' ) )
						{
							if( $class == 'recommends' )
							{
								$msgs['note'][] = sprintf( /* 1: recommened version; 2: application name (default "b2evolution"); 3: current application version */
									T_('The plugin recommends version %s of %s (%s is installed). Think about upgrading.'), $type_params, $app_name, $app_version );
							}
							else
							{
								$msgs['error'][] = sprintf( /* 1: required version; 2: application name (default "b2evolution"); 3: current application version */
									T_('The plugin requires version %s of %s, but %s is installed.'), $type_params, $app_name, $app_version );
							}
						}
						break;


					case 'api_min':
						// obsolete since 1.9:
						continue;


					default:
						// Unknown depency type, throw an error:
						$msgs['error'][] = sprintf( T_('Unknown dependency type (%s). This probably means that the plugin is not compatible and you have to upgrade your %s installation.'), $type, $app_name );

				}
			}
		}

		return $msgs;
	}


	/**
	 * Filter (post) contents by calling the relevant filter plugins.
	 *
	 * Works very much like render() except that it's called at insert/update time and BEFORE validation.
	 * Gives an opportunity to do some serious cleanup on what the user has typed.
	 *
	 * This uses the lost of renderers, because filtering may need to work in conjunction with rendering,
	 * e-g: code display: you want to filter out tags before validation and later you want to render color/fixed font.
	 * For brute force filtering, use 'always' or 'stealth' modes.
	 * @see Plugins::render()
	 *
	 * @param string content to render (by reference)
	 * @param array renderer codes to use for opt-out, opt-in and lazy
	 * @return string rendered content
	 */
	function filter_contents( & $title, & $content, $renderers )
	{
		// echo 'CALLING FILTERS: '.implode(',',$renderers);

		$params['title'] = & $title;
		$params['content'] = & $content;

		$filter_Plugins = $this->get_list_by_event( 'FilterItemContents' );

		foreach( $filter_Plugins as $loop_filter_Plugin )
		{ // Go through whole list of renders
			// echo ' ',$loop_RendererPlugin->code, ':';

			switch( $loop_filter_Plugin->apply_rendering )
			{
				case 'stealth':
				case 'always':
					// echo 'FORCED ';
					$this->call_method( $loop_filter_Plugin->ID, 'FilterItemContents', $params );
					break;

				case 'opt-out':
				case 'opt-in':
				case 'lazy':
					if( in_array( $loop_filter_Plugin->code, $renderers ) )
					{ // Option is activated
						// echo 'OPT ';
						$this->call_method( $loop_filter_Plugin->ID, 'FilterItemContents', $params );
					}
					// else echo 'NOOPT ';
					break;

				case 'never':
					// echo 'NEVER ';
					break;	// STOP, don't render, go to next renderer
			}
		}

		return $content;
	}


	/**
	 * UnFilter (post) contents by calling the relevant filter plugins.
	 *
	 * This is the opposite of filter_content. It is used to restore some specifcs before editing text.
	 * For example, this can be used to replace complex sequences of tags with a custome meta-tag,
	 * e-g: <strong> can become <s> for convenient editing.
	 *
	 * This uses the list of renderers, because un/filtering may need to work in conjunction with rendering,
	 * e-g: code display: you want to filter in/out tags before validation and later you want to render color/fixed font.
	 * For brute force unfiltering, use 'always' or 'stealth' modes.
	 * @see Plugins::render()
	 * @see Plugins::filter()
	 *
	 * @todo fp> it would probably make sense to do the unfiltering in reverse order compared to filtering
	 *
	 * @param string title to render (by reference)
	 * @param string content to render (by reference)
	 * @param array renderer codes to use for opt-out, opt-in and lazy
	 * @return string rendered content
	 */
	function unfilter_contents( & $title, & $content, $renderers )
	{
		// echo 'CALLING FILTERS: '.implode(',',$renderers);

		$params['title'] = & $title;
		$params['content'] = & $content;

		$filter_Plugins = $this->get_list_by_event( 'UnfilterItemContents' );

		// fp> TODO: reverse order

		foreach( $filter_Plugins as $loop_filter_Plugin )
		{ // Go through whole list of renders
			// echo ' ',$loop_RendererPlugin->code, ':';

			switch( $loop_filter_Plugin->apply_rendering )
			{
				case 'stealth':
				case 'always':
					// echo 'FORCED ';
					$this->call_method( $loop_filter_Plugin->ID, 'UnfilterItemContents', $params );
					break;

				case 'opt-out':
				case 'opt-in':
				case 'lazy':
					if( in_array( $loop_filter_Plugin->code, $renderers ) )
					{ // Option is activated
						// echo 'OPT ';
						$this->call_method( $loop_filter_Plugin->ID, 'UnfilterItemContents', $params );
					}
					// else echo 'NOOPT ';
					break;

				case 'never':
					// echo 'NEVER ';
					break;	// STOP, don't render, go to next renderer
			}
		}

		return $content;
	}

}

/*
 * $Log$
 * Revision 1.36  2011/02/20 23:37:06  fplanque
 * minor/doc
 *
 * Revision 1.35  2011/01/11 05:39:50  sam2kb
 * Additional checks in plugins discover function
 *
 * Revision 1.34  2010/06/01 02:44:44  sam2kb
 * New hooks added: GetCollectionKinds and InitCollectionKinds.
 * Use them to define new and override existing presets for new blogs.
 * See http://forums.b2evolution.net/viewtopic.php?t=21015
 *
 * Revision 1.33  2010/05/24 14:50:40  fplanque
 * no message
 *
 * Revision 1.32  2010/03/14 06:36:58  sam2kb
 * New plugin hooks: BeforeThumbCreate, AfterFileUpload
 *
 * Revision 1.31  2010/02/26 22:15:48  fplanque
 * whitespace/doc/minor
 *
 *
 * Revision 1.29  2010/02/23 05:07:17  sam2kb
 * New plugin hooks: DisplayProfileFormFieldset and ProfileFormSent
 *
 * Revision 1.28  2010/02/08 17:53:24  efy-yury
 * copyright 2009 -> 2010
 *
 * Revision 1.27  2010/01/03 12:36:15  fplanque
 * Renamed hooks
 *
 * Revision 1.26  2010/01/02 20:11:08  sam2kb
 * Added new hooks: AfterBlogInsert, AfterBlogUpdate, AfterBlogDelete
 *
 * Revision 1.25  2009/12/22 23:13:38  fplanque
 * Skins v4, step 1:
 * Added new disp modes
 * Hooks for plugin disp modes
 * Enhanced menu widgets (BIG TIME! :)
 *
 * Revision 1.24  2009/09/26 12:00:43  tblue246
 * Minor/coding style
 *
 * Revision 1.23  2009/09/25 07:33:14  efy-cantor
 * replace get_cache to get_*cache
 *
 * Revision 1.22  2009/09/14 13:24:14  efy-arrin
 * Included the ClassName in load_class() call with proper UpperCase
 *
 * Revision 1.21  2009/07/12 19:12:51  fplanque
 * doc
 *
 * Revision 1.20  2009/07/06 22:55:11  fplanque
 * minor
 *
 * Revision 1.19  2009/07/06 14:55:06  yabs
 * doc
 *
 * Revision 1.18  2009/07/02 21:57:11  blueyed
 * doc fix: move files and classes to the plugins package
 *
 * Revision 1.17  2009/07/02 00:01:50  fplanque
 * needs optimization.
 *
 * Revision 1.16  2009/07/01 23:55:48  fplanque
 * doc
 *
 * Revision 1.15  2009/06/14 12:17:18  yabs
 * adding BeforeSessionsDelete trigger to enable plugins to perform housekeeping based on the sessions that are about to be pruned
 *
 * Revision 1.14  2009/06/07 14:23:24  yabs
 * enabling plugin disp types
 *
 * Revision 1.13  2009/05/26 12:48:11  tblue246
 * - Plugins_admin::get_registered_events(): Fixed possible bug.
 * - Added test case for Plugins_admin::get_registered_events().
 * - Added a sample plugin for the test case.
 *
 * Revision 1.12  2009/05/25 21:10:07  tblue246
 * Plugins_admin::get_registered_events(): Use PHP's tokenizer instead of preg_match_all()
 *
 * Revision 1.11  2009/05/22 17:36:50  sam2kb
 * Added array_unique to prevent "Duplicate entry" errors
 *
 * Revision 1.10  2009/03/23 23:05:34  fplanque
 * Demo of how to tap into menu structure from a plugin.
 *
 * Revision 1.9  2009/03/08 23:57:45  fplanque
 * 2009
 *
 * Revision 1.8  2009/03/06 14:12:28  tblue246
 * Added missing documentation for Plugin hooks
 *
 * Revision 1.7  2009/02/28 22:49:02  blueyed
 * Fix check for valid filename in discover.
 *
 * Revision 1.6  2009/02/27 20:25:08  blueyed
 * Move Plugins_admin::validate_renderer_list back to Plugins, since it gets used for displaying items and saves (at least) a load_plugins_table call/query
 *
 * Revision 1.5  2009/02/23 06:12:25  sam2kb
 * Quotes replaced, see http://forums.b2evolution.net/viewtopic.php?t=17994
 *
 * Revision 1.4  2008/01/21 09:35:32  fplanque
 * (c) 2008
 *
 * Revision 1.3  2007/07/24 23:29:25  blueyed
 * todo
 *
 * Revision 1.2  2007/07/04 23:37:29  blueyed
 * PluginUserSettingsEditAction gets triggered and therefor needs to be in the
 * list of "available events", so Plugins can actually hook there.
 *
 * Revision 1.1  2007/06/25 11:00:46  fplanque
 * MODULES (refactored MVC)
 *
 * Revision 1.41  2007/05/14 02:43:05  fplanque
 * Started renaming tables. There probably won't be a better time than 2.0.
 *
 * Revision 1.40  2007/04/26 00:11:08  fplanque
 * (c) 2007
 *
 * Revision 1.39  2007/04/05 22:57:33  fplanque
 * Added hook: UnfilterItemContents
 *
 * Revision 1.38  2007/03/31 22:46:47  fplanque
 * FilterItemContent event
 *
 * Revision 1.37  2007/03/12 14:07:08  waltercruz
 * Changing the WHERE 1 queries to boolean (WHERE 1=1) queries to satisfy the standarts
 *
 * Revision 1.36  2007/02/24 23:03:00  blueyed
 * MFB: Fixed "Unknown dependency type" error display
 *
 * Revision 1.35  2007/02/23 00:21:23  blueyed
 * Fixed Plugins::get_next() if the last Plugin got unregistered; Added AdminBeforeItemEditDelete hook
 *
 * Revision 1.34  2007/02/19 23:20:07  blueyed
 * Added plugin event SkinEndHtmlBody
 *
 * Revision 1.33  2007/02/18 23:19:28  blueyed
 * doc
 *
 * Revision 1.32  2007/02/18 20:52:38  blueyed
 * Load parent class
 *
 * Revision 1.31  2007/02/16 13:34:08  waltercruz
 * Changing double quotes to single quotes
 *
 * Revision 1.30  2007/01/28 23:58:46  blueyed
 * - Added hook CommentFormSent
 * - Re-ordered comment_post.php to: init, validate, process
 * - RegisterFormSent hook can now filter the form values in a clean way
 *
 * Revision 1.29  2007/01/20 23:43:12  blueyed
 * bugfix for new discover(): exclude subdirs that start with "_" now again
 *
 * Revision 1.28  2007/01/20 16:03:24  blueyed
 * Optimized discover()
 *
 * Revision 1.27  2007/01/15 19:28:29  blueyed
 * Small opt
 *
 * Revision 1.26  2007/01/14 18:15:51  blueyed
 * Nuked hackish $is_admin_class as per todo
 *
 * Revision 1.25  2007/01/13 14:57:28  blueyed
 * Removed $is_admin_class hack from load_events() by re-implementing (copying most of it) to Plugins_admin as per todo
 *
 * Revision 1.24  2007/01/12 05:14:42  fplanque
 * doc
 *
 * Revision 1.23  2007/01/09 01:00:51  blueyed
 * todo
 *
 * Revision 1.22  2007/01/09 00:53:53  blueyed
 * doc
 *
 * Revision 1.21  2007/01/07 19:39:44  fplanque
 * doc
 *
 * Revision 1.20  2007/01/07 05:26:01  fplanque
 * doc
 *
 * Revision 1.19  2006/12/28 23:20:40  fplanque
 * added plugin event for displaying comment form toolbars
 * used by smilies plugin
 *
 * Revision 1.18  2006/12/07 23:13:13  fplanque
 * @var needs to have only one argument: the variable type
 * Otherwise, I can't code!
 *
 * Revision 1.17  2006/12/04 00:18:53  fplanque
 * keeping the login hashing
 *
 * Revision 1.14  2006/12/01 20:46:25  blueyed
 * Moved Plugins::set_priority() to Plugins_admin class
 *
 * Revision 1.13  2006/12/01 20:44:01  blueyed
 * Moved Plugins::set_code() to Plugins_admin class
 *
 * Revision 1.12  2006/12/01 20:41:38  blueyed
 * Moved Plugins::uninstall() to Plugins_admin class
 *
 * Revision 1.11  2006/12/01 20:34:03  blueyed
 * Moved Plugins::get_apply_rendering_values() and Plugins::set_apply_rendering() to Plugins_admin class
 *
 * Revision 1.10  2006/12/01 20:19:15  blueyed
 * Moved Plugins::get_supported_events() to Plugins_admin class
 *
 * Revision 1.9  2006/12/01 20:13:24  blueyed
 * Moved Plugins::count_regs() to Plugins_admin class
 *
 * Revision 1.8  2006/12/01 20:11:24  blueyed
 * Renamed Plugins_admin::validate_list() to validate_renderer_list()
 *
 * Revision 1.7  2006/12/01 20:01:38  blueyed
 * Moved Plugins::validate_dependencies() to Plugins_admin class
 *
 * Revision 1.6  2006/12/01 19:46:42  blueyed
 * Moved Plugins::validate_list() to Plugins_admin class; added stub in Plugins, because at least the starrating_plugin uses it
 *
 * Revision 1.5  2006/12/01 19:16:00  blueyed
 * Moved Plugins::get_registered_events() to Plugins_admin class
 *
 * Revision 1.4  2006/12/01 18:18:22  blueyed
 * Moved Plugins::save_events() to Plugins_admin class
 *
 * Revision 1.3  2006/12/01 02:03:04  blueyed
 * Moved Plugins::set_event_status() to Plugins_admin
 *
 * Revision 1.2  2006/11/30 05:57:54  blueyed
 * Moved Plugins::install() and sort() galore to Plugins_admin
 *
 * Revision 1.1  2006/11/30 05:43:40  blueyed
 * Moved Plugins::discover() to Plugins_admin::discover(); Renamed Plugins_no_DB to Plugins_admin_no_DB (and deriving from Plugins_admin)
 */
?>
