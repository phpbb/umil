<?php
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
	'INSTALL_TEST_MOD'				=> 'Install Test Mod',
	'INSTALL_TEST_MOD_CONFIRM'		=> 'Are you ready to install the Test Mod?',

	'REMOVE_TEST_ROW'				=> 'Remove test row into the phpbb_test table.',

	'TEST_BOOLEAN'					=> 'Custom Boolean',
	'TEST_MOD'						=> 'Test Mod',
	'TEST_MOD_EXPLAIN'				=> 'This is an example on how to use the Automatic UMIL method.  Mod authors interested in using this should open it with a text editor (like Notepad++) to see how it can be used.<br /><br /><strong>This is not a real mod.</strong>',
	'TEST_USERNAME'					=> 'Enter a username',
	'TEST_USERNAME_EXPLAIN'			=> 'Enter a username or select a username from the select user popup.',

	'UNINSTALL_TEST_MOD'			=> 'Uninstall Test Mod',
	'UNINSTALL_TEST_MOD_CONFIRM'	=> 'Are you ready to uninstall the Test Mod?  All settings and data saved by this mod will be removed!',
	'UPDATE_TEST_MOD'				=> 'Update Test Mod',
	'UPDATE_TEST_MOD_CONFIRM'		=> 'Are you ready to update the Test Mod?',
));

?>