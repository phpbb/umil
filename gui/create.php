<?php
/**
 *
 * @author David Lewis (Highway of Life) highwayoflife@gmail.com
 * @package umil
 * @version $Id$
 * @copyright (c) 2008 phpBB Group
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
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
$user->setup('umil_creation');

$submit = (isset($_REQUEST['submit'])) ? true : false;

define('MAX_MOD_NAME_CHARS', 50);
define('MIN_MOD_NAME_CHARS', 3);
define('MAX_FULLNAME_CHARS', 50);
define('MIN_FULLNAME_CHARS', 3);
define('MAX_EMAIL_CHARS', 60);
define('MIN_EMAIL_CHARS', 6);

$mod = array(
	'username'		=> utf8_normalize_nfc(request_var('username', '', true)),
	'fullname'		=> utf8_normalize_nfc(request_var('fullname', '', true)),
	'email'			=> strtolower(request_var('email', '')),
	'filename'		=> strtolower(request_var('filename', '')), // allow alphanumeric, and _
	'version'		=> request_var('version', ''), // allow 0-9, ., b, a, dev, -, and RC plus 0-2 digits.
	'mod_name'		=> utf8_normalize_nfc(request_var('mod_name', '', true)),
	'shortname'		=> str_replace(array(' ', '-'), '_', strtolower(request_var('mod_shortname', ''))), // allow a-z, - and _ -limit 50chars
	'copy_author'	=> request_var('copyright_author', ''), // allow alphanumeric
	'lang_file'		=> strtolower(request_var('lang_file', '')), // allow ASCII, _, and /
);

$permissions_add = $config_add = $permission_set = $module_add = $column_add = $index_add = array(array('name' => ''));

if ($submit || (isset($_POST['add_fields'])))
{
	$permissions_add	= request_var('permissions', array(0 => array('name' => 0, 'global' => 0)));
	$config_add			= request_var('config_add', array(0 => array('name' => '', 'value' => '', 'dynamic' => 0)));
	$permission_set		= request_var('permission_set', array(0 => array('name' => '', 'role' => '')));
	$column_add			= request_var('columns', array(0 => array('table' => '', 'name' => '', 'type' => '', 'num' => 0, 'default' => '', 'option' => 0)));
	$index_add			= request_var('index_add', array());
	$module_add			= request_var('module_add', array());
	$table_add			= request_var('table_add', array());

	var_dump($column_add);
	var_dump($_POST);

	add_fields($permissions_add, 'permission_fields');
	add_fields($config_add, 'config_fields');
	add_fields($permission_set, 'set_perm_fields');
	add_fields($module_add, 'module_fields');
	add_fields($column_add, 'column_fields');
	add_fields($index_add, 'index_fields');
}

if (0)
{
	$error = validate_data($mod, array(
		'username'		=> array('string', false, MIN_FULLNAME_CHARS, MAX_FULLNAME_CHARS),
		'fullname'		=> array('string', false, MIN_FULLNAME_CHARS, MAX_FULLNAME_CHARS),
		'email'			=> array(
			array('string', true, MIN_EMAIL_CHARS, MAX_EMAIL_CHARS), // optional?
			array('generic_email')),
		'filename'		=> array('match', false, '#^[a-z_\.]{2,}$#'),
		'version'		=> array('match', false, '#^([\d\.]{3,10})(-)?(dev|RC|a|b)?(\d{0,2})?$#'),
		'mod_name'		=> array('string', false, MIN_MOD_NAME_CHARS, MAX_MOD_NAME_CHARS),
		'shortname'		=> array('match', false, '#^[a-z_]{' . MIN_MOD_NAME_CHARS . ',' . MAX_MOD_NAME_CHARS . '}$#'),
		'copy_author'	=> array('string', false, MIN_FULLNAME_CHARS, MAX_FULLNAME_CHARS),
		'lang_file'		=> array('match', false, '#^[a-z\/_]+$#'),
	));

	// Replace "error" strings with their real, localised form
	$error = preg_replace('#^([A-Z_]+)$#e', "(!empty(\$user->lang['\\1'])) ? \$user->lang['\\1'] : '\\1'", $error);

	$replace_ary = array(
		'AUTHOR_USERNAME'		=> $user->data['username'],
		'AUTHOR_FULLNAME'		=> $mod['fullname'],
		'AUTHOR_EMAIL'			=> $mod['email'],
		'GENERATION_TIMESTAMP'	=> $mod['filename'] . ' ' . $mod['version'] . ' ' . gmdate('Y-m-d H:i:sT') . ' ' . $user->data['username'],
		'COPYRIGHT_YEAR'		=> date('Y'),
		'COPYRIGHT_AUTHOR'		=> $mod['copy_author'],
		'LANG_FILE'				=> $mod['lang_file'],
		'MOD_NAME'				=> $mod['mod_name'],
		'MOD_VERSION'			=> $mod['version'],
		'MOD_SHORT'				=> $mod['shortname'],
		'MOD_CONFIG'			=> $mod['shortname'] . '_version',
		'MOD_ENABLE'			=> $mod['shortname'] . '_enable',
		'S_PURGE_CACHE'			=> isset($_REQUEST['purge_cache']) ? true : false,
	);
}

foreach ($permissions_add as $key => $value)
{
	$template->assign_block_vars('permissions', array(
		'VALUE'		=> (isset($value['name'])) ? $value['name'] : '',
		'ITEM'		=> $key + 1,
		'GLOBAL'	=> (isset($value['global'])) ? ' checked="checked"' : '',
	));
}

foreach ($permission_set as $key => $value)
{
	$template->assign_block_vars('set_perms', array(
		'NAME'		=> (isset($value['name'])) ? $value['name'] : '',
		'ITEM'		=> $key + 1,
		'ROLE'		=> (isset($value['role'])) ? $value['role'] : '',
	));
}

foreach ($config_add as $key => $value)
{
	$template->assign_block_vars('configs', array(
		'NAME'		=> (isset($value['name'])) ? $value['name'] : '',
		'ITEM'		=> $key + 1,
		'VALUE'		=> (isset($value['value'])) ? $value['value'] : '',
		'DYNAMIC'	=> (isset($value['dynamic'])) ? ' checked="checked"' : '',
	));
}

foreach ($module_add as $key => $value)
{
	$template->assign_block_vars('modules', array(
		'CLASS'		=> (isset($value['class'])) ? $value['class'] : '',
		'ITEM'		=> $key + 1,
		'PARENT'	=> (isset($value['parent'])) ? $value['parent'] : '',
		'BASENAME'	=> (isset($value['basename'])) ? $value['basename'] : '',
	));
}

foreach ($column_add as $key => $value)
{
	$type = (isset($value['type'])) ? $value['type'] : '';
	$num = (isset($value['num'])) ? $value['num'] : 0;

	$s_type_options = $s_num_options = '';
	column_type($s_type_options, $s_num_options, $type, $num);

	$template->assign_block_vars('columns', array(
		'TABLE'			=> (isset($value['table'])) ? $value['table'] : '',
		'ITEM'			=> $key + 1,
		'NAME'			=> (isset($value['name'])) ? $value['name'] : '',
		'TYPE_OPTIONS'	=> $s_type_options,
		'TYPE'			=> $type,
		'NUM_OPTIONS'	=> $s_num_options,
		'NUM'			=> $num,
		'DEFAULT'		=> (isset($value['default'])) ? $value['default'] : '',
		'OPTION'		=> (isset($value['option'])) ? ' checked="checked"' : '',
	));
}

foreach ($index_add as $key => $value)
{
	$template->assign_block_vars('index', array(
		'TABLE'		=> (isset($value['table'])) ? $value['table'] : '',
		'ITEM'		=> $key + 1,
		'INDEX'		=> (isset($value['index'])) ? $value['index'] : '',
		'COLUMN'	=> (isset($value['column'])) ? $value['column'] : '',
	));
}

foreach ($mod as $key => $value)
{
	$key_upper = strtoupper($key);
	$template->assign_block_vars('info', array(
		'NAME'			=> $key,
		'LANG'			=> $user->lang[$key_upper],
		'VALUE'			=> $value,
		'LANG_EXPLAIN'	=> $user->lang[$key_upper . '_EXPLAIN'],
	));
}

$s_fields_options = '';
for ($i = 0; $i < 11; $i++)
{
	$s_fields_options .= '<option value="' . $i . '">' . $i . '</option>';
}

$template->assign_vars(array(
	'S_ADD_FIELDS_OPTIONS'	=> $s_fields_options,
));

// Output page
page_header($user->lang['INDEX']);

$template->set_filenames(array(
	'body' => 'create_umil.html',
));

page_footer();

function validate_generic_email($email)
{
	if (!preg_match('/^' . get_preg_expression('email') . '$/i', $email))
	{
		return 'EMAIL_INVALID';
	}

	return false;
}

function add_fields(&$data, $request)
{
	$fields = request_var($request, 0);

	if (sizeof($fields))
	{
		for ($i = 0; $i < $fields; $i++)
		{
			$data[] = array();
		}
	}
}

function column_type(&$s_type_options, &$s_num_options, $selected_type = '', $selected_num = 0)
{
	$type_ary	= array(
		'INT:'		=> 'int(%d)',
		'BINT'		=> 'bigint(20)',
		'UINT'		=> 'mediumint(8) UNSIGNED',
		'UINT:'		=> 'int(%d) UNSIGNED',
		'TINT:'		=> 'tinyint(%d)',
		'USINT'		=> 'smallint(4) UNSIGNED',
		'BOOL'		=> 'tinyint(1) UNSIGNED',
		'VCHAR'		=> 'varchar(255)',
		'VCHAR:'	=> 'varchar(%d)',
		'CHAR:'		=> 'char(%d)',
		'XSTEXT'	=> 'text (XSTEXT)',
		'XSTEXT_UNI'=> 'varchar(100)',
		'STEXT'		=> 'text (STEXT)',
		'STEXT_UNI'	=> 'varchar(255)',
		'TEXT'		=> 'text (TEXT)',
		'TEXT_UNI'	=> 'text (UNI)',
		'MTEXT'		=> 'mediumtext',
		'MTEXT_UNI'	=> 'mediumtext (UNI)',
		'TIMESTAMP'	=> 'int(11) UNSIGNED',
		'DECIMAL'	=> 'decimal(5,2)',
		'DECIMAL:'	=> 'decimal(%d,2)',
		'PDECIMAL'	=> 'decimal(6,3)',
		'PDECIMAL:'	=> 'decimal(%d,3)',
		'VCHAR_UNI'	=> 'varchar(255)',
		'VCHAR_UNI:'=> 'varchar(%d)',
		'VCHAR_CI'	=> 'varchar(255)',
		'VARBINARY'	=> 'varbinary(255)',
	);

	foreach ($type_ary as $key => $value)
	{
		$selected = ($selected_type == $key) ? ' selected="selected"' : '';
		$s_type_options .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
	}

	for ($i = 0; $i < 256; $i++)
	{
		$selected = ($selected_num == $i) ? ' selected="selected"' : '';
		$s_num_options .= '<option value="' . $i . '"' . $selected . '>' . $i . '</option>';
	}
}

?>