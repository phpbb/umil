<?php
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

$mod_name = 'TEST_MOD';
$version_config_name = 'test_version';
$language_file = 'mods/umil_auto_example';

$logo_img = 'styles/prosilver/theme/images/created_by.jpg';

$versions = array(
	'1.0.0'	=> array(
		'cache_purge'	=> array(
			'auth',
			'imageset',
			'template',
			'theme',
			'',
			array('auth', 1),
			array('imageset', 1),
			array('template', 1),
			array('theme', 1),
			array('', 1),

			// Should Error
			array('imageset', 3),
			array('template', 3),
			array('theme', 3),
		),

		'config_add'	=> array(
			array('test1', true, false),
			array('test2', false, true),
			array('test3', 0, false),
			array('test4', 1, true),
			array('test5', 2, false),
			array('test6', '', true),
			array('test7', 'a', false),
			array('test8', 'b', true),

			// Should Error
			array('test1', true, false),
			array('test2', false, false),
			array('test2', true, true),
		),

		'config_update'	=> array(
			array('test3', true, false),
			array('test3', true, true),

			// Should error
			array('test9', true, false),
			array('test10', false, true),
		),

		'config_remove'	=> array(
			array('test1'),
			array('test2'),
			'test3',
			'test4',

			// Should Error
			array('test11'),
			'test12',
		),

		'module_add'	=> array(
			array('acp', 0, 'ACP_CAT_DOT_MODS2'),
			array('acp', '', 'ACP_CAT_DOT_MODS3'),
			array('acp', false, 'ACP_CAT_DOT_MODS4'),
			array('acp', 'ACP_CAT_DOT_MODS2', 'ACP_CAT_TEST_MOD'),
			array('acp', 'ACP_CAT_DOT_MODS3', 'ACP_CAT_TEST_MOD2'),
			array('acp', 'ACP_CAT_TEST_MOD', array(
					'module_basename'		=> 'board',
					'modes'					=> array('settings', 'features'),
				),
			),
			array('acp', 'ACP_CAT_TEST_MOD', array(
					'module_basename'	=> 'board',
					'module_langname'	=> 'ACP_AVATAR_SETTINGS',
					'module_mode'		=> 'avatar',
					'module_auth'		=> 'acl_a_board',
					'after'				=> 'ACP_BOARD_SETTINGS',
				),
			),
			array('mcp', 0, 'ACP_CAT_DOT_MODS2'),
			array('mcp', '', 'ACP_CAT_DOT_MODS3'),
			array('mcp', false, 'ACP_CAT_DOT_MODS4'),
			array('mcp', 'ACP_CAT_DOT_MODS2', array(
					'module_basename'		=> 'main',
				),
			),
			array('ucp', 0, 'ACP_CAT_DOT_MODS2'),
			array('ucp', '', 'ACP_CAT_DOT_MODS3'),
			array('ucp', false, 'ACP_CAT_DOT_MODS4'),
			array('ucp', 'ACP_CAT_DOT_MODS2', array(
					'module_basename'		=> 'pm',
				),
			),

			// Should Error
			array('acp', 0, 'ACP_CAT_DOT_MODS2'),
			array('acp', '', 'ACP_CAT_DOT_MODS3'),
			array('acp', false, 'ACP_CAT_DOT_MODS4'),
			array('mcp', 0, 'ACP_CAT_DOT_MODS2'),
			array('mcp', '', 'ACP_CAT_DOT_MODS3'),
			array('mcp', false, 'ACP_CAT_DOT_MODS4'),
			array('mcp', 'ACP_CAT_DOT_MODS2', array(
					'module_basename'		=> 'main',
				),
			),
			array('ucp', 0, 'ACP_CAT_DOT_MODS2'),
		),

		'module_remove'	=> array(
			array('acp', 'ACP_CAT_TEST_MOD', array(
					'module_basename'		=> 'board',
					'modes'					=> array('settings', 'features'),
				),
			),
			array('acp', 'ACP_CAT_TEST_MOD', array(
					'module_basename'	=> 'board',
					'module_langname'	=> 'ACP_AVATAR_SETTINGS',
					'module_mode'		=> 'avatar',
					'module_auth'		=> 'acl_a_board',
					'after'				=> 'ACP_BOARD_SETTINGS',
				),
			),
			array('acp', 'ACP_CAT_DOT_MODS2', 'ACP_CAT_TEST_MOD'),
			array('acp', 'ACP_CAT_DOT_MODS3', 'ACP_CAT_TEST_MOD2'),
			array('acp', 0, 'ACP_CAT_DOT_MODS2'),
			array('acp', '', 'ACP_CAT_DOT_MODS3'),
			array('acp', false, 'ACP_CAT_DOT_MODS4'),
			array('mcp', 'ACP_CAT_DOT_MODS2', array(
					'module_basename'		=> 'main',
				),
			),
			array('mcp', 0, 'ACP_CAT_DOT_MODS2'),
			array('mcp', '', 'ACP_CAT_DOT_MODS3'),
			array('mcp', false, 'ACP_CAT_DOT_MODS4'),
			array('ucp', 'ACP_CAT_DOT_MODS2', array(
					'module_basename'		=> 'pm',
				),
			),
			array('ucp', 0, 'ACP_CAT_DOT_MODS2'),
			array('ucp', '', 'ACP_CAT_DOT_MODS3'),
			array('ucp', false, 'ACP_CAT_DOT_MODS4'),

			// Should Error
			array('acp', 0, 'ACP_CAT_DOT_MODS2'),
			array('acp', '', 'ACP_CAT_DOT_MODS3'),
			array('acp', false, 'ACP_CAT_DOT_MODS4'),
		),

		'permission_add'	=> array(
			'a_test1',
			'a_test2',
			'a_test3',
			array('a_test4', true),
			array('a_test5', false),
			array('a_test5', true),

			// Should Error
			array('a_test1', true),
			array('a_test5', false),
		),

		'permission_set' => array(
			array('ROLE_ADMIN_FULL', 'a_test1'),
			array('ROLE_ADMIN_FULL', array('a_test2', 'a_test3', 'a_test4', 'a_test5')),
			array('GUESTS', 'a_test1', 'group', false),
			array('GUESTS', array('a_test2', 'a_test3', 'a_test4', 'a_test5'), 'group', false),

			// Should Error
			array('ROLE_ADMIN_FULL', 'a_test1', 'group'),
			array('GUESTS', 'a_test1'),
			array('AROLE_ADMIN_FULL', 'a_test1'),
		),

		'permission_remove'	=> array(
			'a_test1',
			array('a_test5', false),
			array('a_test5', true),

			// Should Error
			'a_test1',
			array('a_test1', false),
			array('a_test5', false),
		),

		'permission_unset'	=> array(
			array('ROLE_ADMIN_FULL', 'a_test2'),
			array('ROLE_ADMIN_FULL', array('a_test3', 'a_test4')),
			array('GUESTS', 'a_test2', 'group', false),
			array('GUESTS', array('a_test3', 'a_test4'), 'group', false),

			// Should Error
			array('ROLE_ADMIN_FULL', 'a_test2', 'group'),
			array('GUESTS', 'a_test2'),
			array('AROLE_ADMIN_FULL', 'a_test2'),
		),

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
			array('phpbb_test1', array(
					'COLUMNS'		=> array(
						'test_id'		=> array('UINT', NULL, 'auto_increment'),
						'test_text'		=> array('VCHAR_UNI', ''),
						'test_bool'		=> array('BOOL', 0),
					),
					'PRIMARY_KEY'	=> array('test_id', 'test_bool'),
					'KEYS'			=> array(
						'test_text'		=> array('INDEX', 'test_text'),
					),
				),
			),
			array('phpbb_test2', array(
					'COLUMNS'		=> array(
						'test_id'		=> array('UINT', NULL, 'auto_increment'),
						'test_text'		=> array('VCHAR_UNI', ''),
						'test_bool'		=> array('BOOL', 0),
					),
					'PRIMARY_KEY'	=> array('test_id'),
				),
			),

			// Should Error
			array('phpbb_test1', array(
					'COLUMNS'		=> array(
						'test_id'		=> array('UINT', NULL, 'auto_increment'),
						'test_text'		=> array('VCHAR_UNI', ''),
						'test_bool'		=> array('BOOL', 0),
					),
					'PRIMARY_KEY'	=> array('test_id'),
				),
			),
		),

		'table_remove'	=> array(
			'phpbb_test2',

			// Should Error
			'phpbb_test3',
		),


		'table_column_add' => array(
			array('phpbb_test', 'test_time', array('TIMESTAMP', 0)),
			array('phpbb_test1', 'test_time', array('TIMESTAMP', 0)),

			// Should Error
			array('phpbb_test1', 'test_time', array('TIMESTAMP', 0)),
			array('phpbb_test1', 'test_bool', array('BOOL', 0)),
			array('phpbb_test2', 'test_bool', array('BOOL', 0)),
		),

		'table_column_update'	=> array(
			array('phpbb_test', 'test_time', array('TIMESTAMP', 1)),
			array('phpbb_test1', 'test_time', array('BOOL', 1)),

			// Should Error
			array('phpbb_test1', 'test_time1', array('TIMESTAMP', 0)),
			array('phpbb_test2', 'test_bool', array('BOOL', 0)),
		),

		'table_column_remove'	=> array(
			array('phpbb_test1', 'test_time'),

			// Should Error
			array('phpbb_test1', 'test_time1'),
			array('phpbb_test2', 'test_bool'),
		),

		'table_index_add'	=> array(
			array('phpbb_test', 'test_time'),
			array('phpbb_test1', 'test_bool', 'test_bool'),

			// Should Error
			array('phpbb_test1', 'test_time'),
			array('phpbb_test2', 'test_bool'),
		),

		'table_index_remove'	=> array(
			array('phpbb_test', 'test_time'),
			array('phpbb_test1', 'test_bool'),

			// Should Error
			array('phpbb_test1', 'test_time'),
			array('phpbb_test2', 'test_bool'),
		),

		'table_row_insert'	=> array(
			array('phpbb_test', array(
				'test_text'	=> '123',
				'test_bool'	=> false,
				'test_time'	=> time(),
			)),
			array('phpbb_test1', array(
				'test_text'	=> '1234',
				'test_bool'	=> true,
			)),

			// Should Error
			array('phpbb_test1', array(
				'test_text'	=> '123',
				'test_bool'	=> false,
				'test_time'	=> time(),
			)),
			array('phpbb_test2', array(
				'test_text'	=> '123',
			)),
		),

		'table_row_update'	=> array(
			array('phpbb_test',
				array(
					'test_text'	=> '123',
				),
				array(
					'test_bool'	=> true,
				),
			),
			array('phpbb_test1',
				array(
					'test_text'	=> '1234',
				),
				array(
					'test_text'	=> '12345',
				),
			),

			// Should Error
			array('phpbb_test1',
				array(
					'test_time'	=> '1234',
				),
				array(
					'test_time'	=> '12345',
				),
			),
			array('phpbb_test2',
				array(
					'test_time'	=> '1234',
				),
				array(
					'test_time'	=> '12345',
				),
			),
		),

		'table_row_remove'	=> array(
			array('phpbb_test1',
				array(
					'test_text'	=> '12345',
				),
			),

			// Should Error
			array('phpbb_test1',
				array(
					'test_time'	=> '12345',
				),
			),
			array('phpbb_test2',
				array(
					'test_time'	=> '12345',
				),
			),
		),

		'custom'	=> array(
			'test1',

			// Should cause an error in the results
			'test2',
			'test3',
		),
	),
);

// Include the UMIF Auto file and everything else will be handled automatically.
include($phpbb_root_path . 'umil/umil_auto.' . $phpEx);

function test1($action, $version)
{
	return 'CLICK_VIEW_PRIVMSG';
}

function test2($action, $version)
{
	return array(
		'command'	=> array(
			'CLICK_VIEW_PRIVMSG',
			'!!!',
			'111',
		),
		'result'	=> 'FAIL',
	);
}

function test3($action, $version)
{
	global $db;

	$sql = 'SELECT * FROM non_existant_table';
	$db->sql_query($sql);
}
?>