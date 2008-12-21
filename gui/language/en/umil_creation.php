<?php
/**
*
* umil_creation [English]
* 
* @author David Lewis (Highway of Life) highwayoflife@gmail.com
* @package umil
* @version $Id$
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* DO NOT CHANGE
*/
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
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'COPY_AUTHOR'		=> 'Copyright Group/Author',
	'COPY_AUTHOR_EXPLAIN'	=> 'Enter the Group or Author this Modification is Copyrighted to.',

	'EMAIL'				=> 'E-mail Address',
	'EMAIL_EXPLAIN'		=> 'Enter your E-mail address as it will appear in the header portion of the script.',

	'FILENAME'			=> 'File name',
	'FILENAME_EXPLAIN'	=> 'Enter the filename of the installation script. i.e. example_mod_install.php',
	'FULLNAME'			=> 'Full Name',
	'FULLNAME_EXPLAIN'	=> 'Enter your Full Name as it will appear in the header portion of the script.',
	
	'LANG_FILE'			=> 'Language File',
	'LANG_FILE_EXPLAIN'	=> 'Enter the path of the language file to be included in the installation script. i.e. <strong>mods/info_acp_example_mod</strong>',
	
	'MOD_NAME'			=> 'Modification Name',
	'MOD_NAME_EXPLAIN'	=> 'Enter the Modification Name.',
	
	'SHORTNAME'			=> 'MOD Short Name',
	'SHORTNAME_EXPLAIN'	=> 'Enter the Modification shortname as it will appear in the config, allowed characters are a-z and underscore.',

	'USERNAME'			=> 'Username',
	'USERNAME_EXPLAIN'	=> 'Enter your Username as it will appear in the header portion of the script.',
	
	'VERSION'			=> 'Version',
	'VERSION_EXPLAIN'	=> 'The version of the MOD in version_compare compatible version format. e.g.: <strong>x.y.z</strong>, the following optional suffixes are allowed: <strong>-dev</strong>, <strong>-RCx</strong>, <strong>-ax</strong>, and <strong>-bx</strong>.',
));

?>