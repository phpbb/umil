<?php
/**
* This file is part of French (Casual Honorifics) UMIL translation.
* Copyright (C) 2010 phpBB.fr
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; version 2 of the License.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License along
* with this program; if not, write to the Free Software Foundation, Inc.,
* 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*
* umil_creation [French (Casual Honorifics)]
*
* @package   umil
* @author    Maël Soucaze <maelsoucaze@phpbb.fr> (Maël Soucaze) http://www.phpbb.fr/
* @author    David Lewis <highwayoflife@gmail.com> (Highway of Life) http://startrekguide.com/
* @copyright (c) 2008 phpBB Group
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License
* @version   $Id$
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
	'COPY_AUTHOR'		=> 'Copyright du groupe ou de l’auteur',
	'COPY_AUTHOR_EXPLAIN'	=> 'Saisi le groupe ou l’auteur de ce MOD, ce qui agira comme un copyright.',

	'EMAIL'				=> 'Adresse e-mail',
	'EMAIL_EXPLAIN'		=> 'Saisi ton adresse e-mail telle qu’elle apparaîtra dans la partie supérieure du script.',

	'FILENAME'			=> 'Nom du fichier',
	'FILENAME_EXPLAIN'	=> 'Saisi le nom du fichier du script d’installation. Par exemple, example_mod_install.php',
	'FULLNAME'			=> 'Nom complet',
	'FULLNAME_EXPLAIN'	=> 'Saisi ton nom complet tel qu’il apparaîtra dans la partie supérieure du script.',
	
	'LANG_FILE'			=> 'Fichier de langue',
	'LANG_FILE_EXPLAIN'	=> 'Saisi le chemin du fichier de langue à inclure dans le script d’installation. Par exemple, <strong>mods/info_acp_example_mod</strong>',
	
	'MOD_NAME'			=> 'Nom du MOD',
	'MOD_NAME_EXPLAIN'	=> 'Saisi le nom du MOD.',
	
	'SHORTNAME'			=> 'Nom raccourci du MOD',
	'SHORTNAME_EXPLAIN'	=> 'Saisi le nom raccourci du MOD tel qu’il apparaîtra dans la configuration. Les caractères autorisés sont les lettres de l’alphabet et le tiret bas.',

	'USERNAME'			=> 'Nom d’utilisateur',
	'USERNAME_EXPLAIN'	=> 'Saisi ton nom d’utilisateur tel qu’il apparaîtra dans la partie supérieure du script.',
	
	'VERSION'			=> 'Version',
	'VERSION_EXPLAIN'	=> 'La version du MOD dans le format de compatibilité de la version version_compare. Par exemple, <strong>x.y.z</strong>. Les suffixes optionnels autorisés sont les suivants : <strong>-dev</strong>, <strong>-RCx</strong>, <strong>-ax</strong>, et <strong>-bx</strong>.',
));

?>