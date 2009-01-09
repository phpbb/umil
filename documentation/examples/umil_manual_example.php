<?php
/**
*
* @author Username (Joe Smith) joesmith@example.org
* @package umil
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/*
* This is only an example of an install/update file using the manual method of UMIL.
* You should probably use the umil_auto method unless you are willing to handle installation, uninstallation, and updating yourself
*
* This does exactly the same thing as umil_auto_example, but does not have the uninstall or version selection options
*/

/**
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/umil_auto_example');

if (!file_exists($phpbb_root_path . 'umil/umil.' . $phpEx))
{
	trigger_error('Please download the latest UMIL (Unified MOD Install Library) from: <a href="http://www.phpbb.com/mods/umil/">phpBB.com/mods/umil</a>', E_USER_ERROR);
}

// We only allow a founder install this MOD
if ($user->data['user_type'] != USER_FOUNDER)
{
    if ($user->data['user_id'] == ANONYMOUS)
    {
        login_box('', 'LOGIN');
    }

    trigger_error('NOT_AUTHORISED');
}

if (!class_exists('umil'))
{
	include($phpbb_root_path . 'umil/umil.' . $phpEx);
}

// If you want a completely stand alone version (able to use UMIL without messing with any of the language stuff) send true, otherwise send false
$umil = new umil(true);

if (confirm_box(true))
{
	// Install the base 0.3.0 version
	if (!$umil->config_exists('test_version'))
	{
		// Lets add a config setting named test_enable and set it to true
		$umil->config_add('test_enable', true);

		// We must handle the version number ourselves.
		$umil->config_add('test_version', '0.3.0');
	}

	switch ($config['test_version'])
	{
		// Update to 0.3.1
		case '0.3.0' :
			// Now to add some permission settings.  Showing both the one at a time and "multicall" options
			$umil->permission_add('a_test_mod', true);
			$umil->permission_add(array(
				array('f_test_mod', false),
				array('u_test_mod', true),
			));

			// How about we give some default permissions then as well?
			$umil->permission_set(array(
				// Global Role permissions
				array('ROLE_ADMIN_FULL', 'a_test_mod'),
				array('ROLE_USER_FULL', 'u_test_mod'),

				// Global Group permissions
				array('GUESTS', 'u_test_mod', 'group'),

				// Local Permissions
				array('ROLE_FORUM_STANDARD', 'f_test_mod'),
			));

		// No breaks

		// Update to 0.7.0
		case '0.3.1' :
			// Lets change our test_enable to false
			$umil->config_update('test_enable', false);

			// Lets remove some of those permission settings we added before
			$umil->permission_remove(array(
				array('f_test_mod', false),
				array('u_test_mod', true),
			));

			// Now to add a table (this uses the layout from develop/create_schema_files.php and from phpbb_db_tools)
			$umil->table_add('phpbb_test', array(
				'COLUMNS'		=> array(
					'test_id'		=> array('UINT', NULL, 'auto_increment'),
					'test_text'		=> array('VCHAR_UNI', ''),
					'test_bool'		=> array('BOOL', 0),
				),
				'PRIMARY_KEY'	=> 'test_id',
				'KEYS'			=> array(
					'test_bool'		=> array('INDEX', 'test_bool'),
				),
			));

		// Update to 0.9.0
		case '0.7.0' :
			// Lets add a new column to the phpbb_test table named test_time
			$umil->table_column_add('phpbb_test', 'test_time', array('TIMESTAMP', 0));

			// Lets make the test_time column we just added an index
			$umil->table_index_add('phpbb_test', 'test_time', 'test_time');

			// Alright, now lets add some modules to the ACP
			$umil->module_add(array(
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
					),
				),
			));

		// Update to 0.9.1
		case '0.9.0' :
			// This is done in the custom function from umil_auto_example
			$sql_ary = array(
				'test_text'		=> 'This is a test message.',
				'test_bool'		=> 1,
				'test_time'		=> time(),
			);
			$sql = 'INSERT INTO ' . $table_prefix . 'test ' . $db->sql_build_array('INSERT', $sql_ary);
			$db->sql_query($sql);

		// Update to 1.0.0
		case '0.9.1' :
	}

	// We must handle the version number ourselves.
	$umil->config_update('test_version', '1.0.0');

	// We are done
	trigger_error('Done!');
}
else
{
	confirm_box(false, 'INSTALL_TEST_MOD');
}

// Shouldn't get here.
redirect($phpbb_root_path . $user->page['page_name']);

?>