<?php
/**
*
* @author Username (Joe Smith) joesmith@example.org
* @package umil
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
define('UMIL_AUTO', true);
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
$user->session_begin();
$auth->acl($user->data);
$user->setup();

if (!file_exists($phpbb_root_path . 'umil/umil_auto.' . $phpEx))
{
	trigger_error('Please download the latest UMIL (Unified MOD Install Library) from: <a href="http://www.phpbb.com/mods/umil/">phpBB.com/mods/umil</a>', E_USER_ERROR);
}

// The name of the mod to be displayed during installation.
$mod_name = 'TEST_MOD';

/*
* The name of the config variable which will hold the currently installed version
* You do not need to set this yourself, UMIL will handle setting and updating the version itself.
*/
$version_config_name = 'test_version';

/*
* The language file which will be included when installing
* Language entries that should exist in the language file for UMIL (replace $mod_name with the mod's name you set to $mod_name above)
* $mod_name
* 'INSTALL_' . $mod_name
* 'INSTALL_' . $mod_name . '_CONFIRM'
* 'UPDATE_' . $mod_name
* 'UPDATE_' . $mod_name . '_CONFIRM'
* 'UNINSTALL_' . $mod_name
* 'UNINSTALL_' . $mod_name . '_CONFIRM'
*/
$language_file = 'mods/umil_auto_example';

/*
* Options to display to the user (this is purely optional, if you do not need the options you do not have to set up this variable at all)
* Uses the acp_board style of outputting information, with some extras (such as the 'default' and 'select_user' options)
*/
$options = array(
	'test_username'	=> array('lang' => 'TEST_USERNAME', 'type' => 'text:40:255', 'explain' => true, 'default' => $user->data['username'], 'select_user' => true),
	'test_boolean'	=> array('lang' => 'TEST_BOOLEAN', 'type' => 'radio:yes_no', 'default' => true),
);

/*
* Optionally we may specify our own logo image to show in the upper corner instead of the default logo.
* $phpbb_root_path will get prepended to the path specified
* Image height should be 50px to prevent cut-off or stretching.
*/
//$logo_img = 'styles/prosilver/imageset/site_logo.gif';

/*
* The array of versions and actions within each.
* You do not need to order it a specific way (it will be sorted automatically), however, you must enter every version, even if no actions are done for it.
*
* You must use correct version numbering.  Unless you know exactly what you can use, only use X.X.X (replacing X with an integer).
* The version numbering must otherwise be compatible with the version_compare function - http://php.net/manual/en/function.version-compare.php
*/
$versions = array(
	// Version 0.3.0
	'0.3.0'	=> array(
		// Lets add a config setting named test_enable and set it to true
		'config_add' => array(
			array('test_enable', true),

			// This uses the options we specified earlier.
			array('test_username', request_var('test_username', $user->data['username'], true)),
			array('test_boolean', request_var('test_boolean', true)),
		),
	),

	// Version 0.3.1
	'0.3.1'	=> array(
		// Now to add some permission settings
		'permission_add' => array(
			array('a_test_mod', true),
			array('f_test_mod', false),
			array('u_test_mod', true),
		),

		// How about we give some default permissions then as well?
		'permission_set' => array(
			// Global Role permissions
			array('ROLE_ADMIN_FULL', 'a_test_mod'),
			array('ROLE_USER_FULL', 'u_test_mod'),

			// Global Group permissions
			array('GUESTS', 'u_test_mod', 'group'),

			// Local Permissions (local permissions can not be set for groups)
			array('ROLE_FORUM_STANDARD', 'f_test_mod'),
		),
	),

	// Version 0.7.0
	'0.7.0'	=> array(
		// Lets change our test_enable to false
		'config_update'	=> array(
			array('test_enable', false),
		),

		// Lets remove some of those permission settings we added before
		'permission_remove' => array(
			array('f_test_mod', false),
			array('u_test_mod', true),
		),

		// Now to add a table (this uses the layout from develop/create_schema_files.php and from phpbb_db_tools)
		'table_add' => array(
			array('phpbb_test', array(
					'COLUMNS'		=> array(
						'test_id'		=> array('UINT', NULL, 'auto_increment'),
						'test_text'		=> array('VCHAR_UNI', ''),
						'test_bool'		=> array('BOOL', 0),
					),
					'PRIMARY_KEY'	=> 'test_id',
					'KEYS'			=> array(
						'test_bool'		=> array('INDEX', 'test_bool'),
					),
				),
			),
		),
	),

	// Version 0.9.0
	'0.9.0' => array(
		// Lets add a new column to the phpbb_test table named test_time
		'table_column_add' => array(
			array('phpbb_test', 'test_time', array('TIMESTAMP', 0)),
		),

		// Lets make the test_time column we just added an index
		'table_index_add' => array(
			array('phpbb_test', 'test_time', 'test_time'),
		),

		// Alright, now lets add some modules to the ACP
		'module_add' => array(
			// First, lets add a new category named ACP_CAT_TEST_MOD to ACP_CAT_DOT_MODS
			array('acp', 'ACP_CAT_DOT_MODS', 'ACP_CAT_TEST_MOD'),

			// Now we will add the settings and features modes from the acp_board module to the ACP_CAT_TEST_MOD category using the "automatic" method.
			array('acp', 'ACP_CAT_TEST_MOD', array(
					'module_basename'		=> 'board',
					'modes'					=> array('settings', 'features'),
				),
			),

			// Now we will add the avatar mode from acp_board to the ACP_CAT_TEST_MOD category using the "manual" method.
            array('acp', 'ACP_CAT_TEST_MOD', array(
					'module_basename'	=> 'board',
					'module_langname'	=> 'ACP_AVATAR_SETTINGS',
					'module_mode'		=> 'avatar',
					'module_auth'		=> 'acl_a_board',
					'after'				=> 'ACP_BOARD_SETTINGS', // Will be placed after ACP_BOARD_SETTINGS in the category it is in (the one we just added above)
				),
			),
		),
	),

	// Version 0.9.1
	'0.9.1'	=> array(
		// Now we need to insert some data.
		'table_row_insert'	=> array(
			array('phpbb_test', array(
				array(
					'test_text'		=> 'This is a test message.',
					'test_bool'		=> 1,
					'test_time'		=> (time() - 86400),
				),
				array(
					'test_text'		=> 'This is another test message.',
					'test_bool'		=> 0,
					'test_time'		=> time(),
				),
			)),
		),
	),

	// Version 0.9.2
	'0.9.2' => array(
		// Now we need to update some data.
		'table_row_update'	=> array(
			array('phpbb_test', array(
					'test_text'		=> 'This is a test message.',
					'test_bool'		=> 1,
				),
				array(
					'test_text'		=> 'This is a test message. (Edited)',
					'test_bool'		=> 0,
				),
			),
		),

		/*
		* Here is an example of a custom function.  We will have it remove the data we inserted into the table just previously during the uninstallation
		* (obviously not required since the uninstallation removes the table anyways, but just an example for the custom function)
		* Enter 'custom' for the array key and the name of the function for the value.
		*/
		'custom'	=> 'umil_auto_example',
	),

	// Version 1.0.0
	'1.0.0' => array(
		// Nothing changed in this version.
	),
);

// Include the UMIF Auto file and everything else will be handled automatically.
include($phpbb_root_path . 'umil/umil_auto.' . $phpEx);

/*
* Here is our custom function that will be called for version 0.9.1.
*
* @param string $action The action (install|update|uninstall) will be sent through this.
* @param string $version The version this is being run for will be sent through this.
*/
function umil_auto_example($action, $version)
{
	global $db, $table_prefix, $umil;

	if ($action == 'uninstall')
	{
		// Run this when uninstalling
		$umil->table_row_remove('phpbb_test', array('test_text' => 'This is a test message. (Edited)'));
		$umil->table_row_remove('phpbb_test', array('test_text' => 'This is another test message.'));
	}
}

?>