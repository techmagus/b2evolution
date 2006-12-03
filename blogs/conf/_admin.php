<?php
/**
 * This is the admin config file
 *
 * This sets how the back-office/admin interface works
 *
 * @package conf
 */
if( !defined('EVO_CONFIG_LOADED') ) die( 'Please, do not access this page directly.' );


/**
 * Default controller to use.
 *
 * This determines the default page when you access the admin.
 */
$default_ctrl = 'edit';


/**
 * Controller mappings.
 *
 * For each controller name, we associate a controller file to be found in /inc/CONTROLLERS/ .
 * The advantage of this indirection is that it is easy to reorganize the controllers into
 * subdirectories by modules. It is also easy to deactivate some controllers if you don't
 * want to provide this functionality on a given installation.
 *
 * Note: while the controller mappings might more or less follow the menu structure, we do not merge
 * the two tables since we could, at any time, decide to make a skin with a different menu structure.
 * The controllers however would most likely remain the same.
 *
 * @global array
 */
$ctrl_mappings = array(
		'antispam'     => 'antispam/antispam.php',
		'browse'       => 'items/b2browse.php',
		'chapters'     => 'collections/categories.php',
		'chapters2'    => 'collections/_chapters.ctrl.php',
		'collections'  => 'collections/_collections.ctrl.php',
		'coll_settings'=> 'collections/_coll_settings.ctrl.php',
		'crontab'      => 'cron/crontab.php',
		'edit'         => 'items/b2edit.php',
		'editactions'  => 'items/edit_actions.php',
		'features'     => 'settings/features.php',
		'files'        => 'files/files.php',
		'fileset'      => 'files/fileset.php',
		'filetypes'    => 'files/filetypes.php',
		'itemstatuses' => 'items/statuses.php',
		'itemtypes'    => 'items/types.php',
		'locales'      => 'settings/locales.php',
		'mtimport'     => 'imports/import-mt.php',
		'plugins'      => 'settings/plugins.php',
		'settings'     => 'settings/settings.php',
		'set_antispam' => 'settings/antispam.php',
		'stats'        => 'sessions/stats.php',
		'templates'    => 'skins/b2template.php',
		'tools'        => '_misc/tools.php',
		'users'        => 'users/b2users.php',
	);


/**
 * Cross posting
 *
 * Possible values:
 *   - -1 if you don't want to use categories at all
 *   - 0 if you want users to post to a single category only
 *   - 1 if you want to be able to cross-post among multiple categories
 *   - 2 if you want to be able to cross-post among multiple blogs/categories
 *   - 3 if you want to be able to change main cat among blogs (which will move the
 *       posts from one blog to another; use with caution)
 * 
 * @todo fp>This should be moved to the backoffice.
 * In the BO, this should actually be split into:
 * App Settings: 
 *  checkbox         [] allow cross posting
 *  another checkbox [] allow moving posting between different blogs
 * Each blog's settings: radio between:
 *    o One category per post
 *    o Multiple categories per post (requires transparent handling of main cat)
 *    o Main cat + extra cats
 *    o Don't use categories  (this requires to transparently manage a default category)
 *
 * @global int $allow_cross_posting
 */
$allow_cross_posting = 1;


/**
 * Moving chapters between blogs?
 *
 * Special value: NULL and we won't even talk about moving
 *
 * @todo fp>This should be moved to the backoffice. Checkbox.
 * 
 * @global bool|NULL $allow_moving_chapters
 */
$allow_moving_chapters = false;


/**
 * Default status for new posts:
 *
 * Possible values: 'published', 'deprecated', 'protected', 'private', 'draft'
 *
 * @todo fp>This should be moved to the backoffice. Select list for each blog.
 * 
 * @global string $default_post_status
 */
$default_post_status = 'published';


/**
 * set this to 1 if you want to use the 'preview' function
 * 
 * @todo fp>This should be moved to the backoffice. Checbox for each blog (features). Useful when a blog has no public skin. (Tracker)
 * 
 * @global boolean $use_preview
 */
$use_preview = 1;


/**
 * Do you want to be able to link each post to an URL ?
 * 
 * @todo fp>This should be moved to the backoffice. Checbox for each blog (features).
 * 
 * @global boolean $use_post_url
 */
$use_post_url = 1;  // 1 to enable, 0 to disable


/**
 * When banning, do you want to be able to report abuse to the
 * centralized ban list at b2evolution.net?
 *
 * @global boolean $report_abuse
 */
$report_abuse = 1;


// ** Image upload ** {{{ @deprecated moved to admin interface, but used for upgrading to 1.6
// fp> TODO: can we move this to _upgrade.php ??
/**
 * Set this to 0 to disable file upload, or 1 to enable it
 * @global boolean $use_fileupload
 * @deprecated 1.6: this is only used for creating the defaults when upgrading
 * This is still used by MMS and XMLRPC though.
 */
$use_fileupload = 1;

/**
 * Enter the real path of the directory where you'll upload the pictures.
 *
 * If you're unsure about what your real path is, please ask your host's support staff.
 * Note that the  directory must be writable by the webserver (ChMod 766).
 * Note for windows-servers users: use forwardslashes instead of backslashes.
 * Example: $fileupload_realpath = '/home/example/public_html/media/';	# WITH traling slash!
 * Alternatively you may want to use a path relative to $basepath.
 *
 * @global string $fileupload_realpath
 * @deprecated 1.6: the user uploads to his own media folder (or somewhere else with write permissions)
 * This is still used by MMS and XMLRPC though.
 */
$fileupload_realpath = $basepath.'media/';	# WARNING: slashes moved!

/**
 * Enter the URL of that directory
 *
 * This is used to generate the links to the pictures
 * Example: $fileupload_url = 'http://example.com/media/';	# WITH traling slash!
 * Alternatively you may want to use an URL relatibe to $baseurl
 *
 * @global string $fileupload_url
 * @deprecated 1.6: the user uploads to his own media folder (or somewhere else with write permissions)
 * This is still used by MMS and the MT importer though.
 */
$fileupload_url = $baseurl.'media/';				# WARNING: slashes moved!

// }}}


/**
 * max length for blog_urlname and blog_stub values
 *
 * (this gets checked when editing/creating blogs).
 *
 * @global int $maxlength_urlname_stub
 */
$maxlength_urlname_stub = 30;
?>