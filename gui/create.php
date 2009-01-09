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
$table_name = request_var('table_name', '');

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
	'shortname'		=> str_replace(array(' ', '-'), '_', strtolower(request_var('shortname', ''))), // allow a-z, - and _ -limit 50chars
	'copy_author'	=> request_var('copy_author', ''), // allow alphanumeric
	'lang_file'		=> strtolower(request_var('lang_file', '')), // allow ASCII, _, and /
);

$error = array();
$permissions_add = $config_add = $permission_set = $module_add = $column_add = $index_add = $table_add = $table_keys = array(array('name' => ''));

if ($submit || (isset($_POST['add_fields'])))
{
	$permissions_add	= request_var('permissions', array(0 => array('' => '')));
	$config_add			= request_var('configs', array(0 => array('' => '')));
	$permission_set		= request_var('set_perms', array(0 => array('' => '')));
	$column_add			= request_var('columns', array(0 => array('' => '')));
	$index_add			= request_var('index', array(0 => array('' => '')));
	$module_add			= request_var('modules', array(0 => array('' => '')));
	$table_add			= request_var('table', array(0 => array('' => '')));
	$table_keys			= request_var('table_keys', array(0 => array('' => '')));

	add_fields($permissions_add, 'permission_fields');
	add_fields($config_add, 'config_fields');
	add_fields($permission_set, 'set_perm_fields');
	add_fields($module_add, 'module_fields');
	add_fields($column_add, 'column_fields');
	add_fields($index_add, 'index_fields');
	add_fields($table_add, 'table_fields');
	add_fields($table_keys, 'table_keys_fields');
}

if ($submit)
{
	if (!function_exists('validate_data'))
	{
		include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
	}

	if (!isset($_REQUEST['ignore_errors']))
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
	}
}

$template->assign_vars(array(
	'AUTHOR_USERNAME'		=> $mod['username'],
	'AUTHOR_FULLNAME'		=> $mod['fullname'],
	'AUTHOR_EMAIL'			=> $mod['email'],
	'GENERATION_TIMESTAMP'	=> $mod['filename'] . ' ' . $mod['version'] . ' ' . gmdate('Y-m-d H:i:sT') . ' ' . $mod['username'],
	'COPYRIGHT_YEAR'		=> date('Y'),
	'COPYRIGHT_AUTHOR'		=> $mod['copy_author'],
	'LANG_FILE'				=> $mod['lang_file'],
	'MOD_NAME'				=> $mod['mod_name'],
	'MOD_VERSION'			=> $mod['version'],
	'MOD_SHORT'				=> $mod['shortname'],
	'MOD_CONFIG'			=> $mod['shortname'] . '_version',
	'MOD_ENABLE'			=> $mod['shortname'] . '_enable',
	'S_PURGE_CACHE'			=> isset($_REQUEST['purge_cache']) ? true : false,
));

foreach ($permissions_add as $key => $value)
{
	if (!contains_data($value, 'name'))
	{
		continue;
	}

	$template->assign_block_vars('permissions', array(
		'VALUE'		=> (isset($value['name'])) ? $value['name'] : '',
		'ITEM'		=> $key + 1,
		'GLOBAL'	=> (isset($value['global'])) ? ' checked="checked"' : '',
		'S_GLOBAL'	=> (isset($value['global'])) ? 1 : 0,
	));
}

foreach ($permission_set as $key => $value)
{
	if (!contains_data($value, array('name', 'role')))
	{
		continue;
	}

	$template->assign_block_vars('set_perms', array(
		'NAME'		=> (isset($value['name'])) ? $value['name'] : '',
		'ITEM'		=> $key + 1,
		'ROLE'		=> (isset($value['role'])) ? $value['role'] : '',
	));
}

foreach ($config_add as $key => $value)
{
	if (!contains_data($value, array('name', 'value')))
	{
		continue;
	}

	$template->assign_block_vars('configs', array(
		'NAME'		=> (isset($value['name'])) ? $value['name'] : '',
		'ITEM'		=> $key + 1,
		'VALUE'		=> (isset($value['value'])) ? $value['value'] : '',
		'DYNAMIC'	=> (isset($value['dynamic'])) ? ' checked="checked"' : '',
		'S_DYNAMIC' => (isset($value['dynamic'])) ? 1 : 0,
	));
}

foreach ($module_add as $key => $value)
{
	if (!contains_data($value, array('class', 'parent', 'basename')))
	{
		continue;
	}

	$template->assign_block_vars('modules', array(
		'CLASS'		=> (isset($value['class'])) ? $value['class'] : '',
		'ITEM'		=> $key + 1,
		'PARENT'	=> (isset($value['parent'])) ? $value['parent'] : '',
		'BASENAME'	=> (isset($value['basename'])) ? $value['basename'] : '',
	));
}

foreach ($column_add as $key => $value)
{
	if (!contains_data($value, array('table', 'name', 'type')))
	{
		continue;
	}

	$type = (isset($value['type'])) ? $value['type'] : '';
	$num = (isset($value['num'])) ? $value['num'] : 0;
	$default = (isset($value['default'])) ? $value['default'] : '';
	$default = (is_numeric($default)) ? $default : (strpos($default, "'") !== false) ? $default : "'$default'";

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
		'DEFAULT'		=> $default,
		'OPTION'		=> (isset($value['option'])) ? ' checked="checked"' : '',
	));
}

foreach ($table_add as $key => $value)
{
	if (!contains_data($value, array('name', 'type'), $table_name))
	{
		continue;
	}

	$type = (isset($value['type'])) ? $value['type'] : '';
	$num = (isset($value['num'])) ? $value['num'] : 0;
	$default = (isset($value['default'])) ? $value['default'] : '';
	$default = (is_numeric($default)) ? $default : (strpos($default, "'") !== false) ? $default : "'$default'";

	$s_type_options = $s_num_options = '';
	column_type($s_type_options, $s_num_options, $type, $num);

	$template->assign_block_vars('table', array(
		'ITEM'			=> $key + 1,
		'NAME'			=> (isset($value['name'])) ? $value['name'] : '',
		'TYPE_OPTIONS'	=> $s_type_options,
		'TYPE'			=> (strpos($type, '%d') !== false) ? $type . (int) $num : $type,
		'NUM_OPTIONS'	=> $s_num_options,
		'NUM'			=> $num,
		'DEFAULT'		=> $default,
		'OPTION'		=> (isset($value['option'])) ? ' checked="checked"' : '',
	));
}

foreach ($table_keys as $key => $value)
{
	if (!contains_data($value, array('index', 'type', 'column')))
	{
		continue;
	}

	$type = (isset($value['type'])) ? $value['type'] : '';

	if ($type == 'PRIMARY' && $submit)
	{
		$template->assign_block_vars('primary_keys', array(
			'MULTIPLE_KEYS'	=> (isset($value['column2'])) ? true : false,
			'COLUMN'		=> (isset($value['column'])) ? $value['column'] : '',
			'COLUMN2'		=> (isset($value['column2'])) ? $value['column2'] : '',
		));
	}

	$template->assign_block_vars('table_keys', array(
		'ITEM'			=> $key + 1,
		'INDEX'			=> (isset($value['index'])) ? $value['index'] : '',
		'TYPE'			=> $type,
		'TYPE_OPTIONS'	=> index_type($type),
		'COLUMN'		=> (isset($value['column'])) ? $value['column'] : '',
		'COLUMN2'		=> (isset($value['column2'])) ? $value['column2'] : '',
	));
}

foreach ($index_add as $key => $value)
{
	if (!contains_data($value, array('table', 'index', 'column')))
	{
		continue;
	}

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
	'TABLE_NAME'			=> $table_name,
	'S_ADD_FIELDS_OPTIONS'	=> $s_fields_options,
	'ERROR'					=> implode('<br />', $error),
	'PHP_START'				=> "<?php\n",
	'PHP_END'				=> "\n?>",
));

if ($submit && !$error)
{
	header('Content-type: text/plain');
	@define('HEADER_INC', true);
	$template_file = 'umil/umif_manual_v1.txt';
}
else
{
	$template_file = 'umil/create_umil.html';
}

// Output page
page_header($user->lang['INDEX']);

$template->set_filenames(array(
	'body' => $template_file,
));

page_footer();

/**
 * Determine if variables/arrays from user input contain data and determine if we should set loops based upon this check.
 *
 * @param array $data
 * @param mixed $check_fields
 * @param mixed $optional_vars
 * @return bool
 */
function contains_data($data, $check_fields, $optional_vars = false)
{
	global $submit;

	if (!$submit)
	{
		return true;
	}

	if ($optional_vars !== false)
	{
		$optional_vars = (is_array($optional_vars)) ? $optional_vars : array($optional_vars);

		foreach ($optional_vars as $var)
		{
			if (empty($var))
			{
				return false;
			}
		}
	}

	if (!is_array($check_fields))
	{
		$check_fields = array($check_fields);
	}

	foreach ($check_fields as $field)
	{
		if (isset($data[$field]) && $data[$field])
		{
			continue;
		}
		else
		{
			return false;
		}
	}

	return true;
}

/**
 * Generic validation of e-mail address
 *
 * @param string $email
 * @return mixed
 */
function validate_generic_email($email)
{
	if (!preg_match('/^' . get_preg_expression('email') . '$/i', $email))
	{
		return 'EMAIL_INVALID';
	}

	return false;
}

/**
 * Function to add additional fields to the GUI
 *
 * @param array $data
 * @param string $request
 */
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

/**
 * Define index/key types, Primary, Unique and index.
 *
 * @param string $selected_type
 * @return Type options list.
 */
function index_type($selected_type = '')
{
	$type_ary = array(
		'INDEX'		=> 'Index key',
		'PRIMARY'	=> 'Primary key',
		'UNIQUE'	=> 'Unique key',
	);

	$s_type_options = '';
	foreach ($type_ary as $key => $value)
	{
		$selected = ($selected_type == $key) ? ' selected="selected"' : '';
		$s_type_options .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
	}

	return $s_type_options;
}

/**
 * Define table column types
 *
 * @param string $s_type_options
 * @param string $s_num_options
 * @param string $selected_type
 * @param int $selected_num
 */
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