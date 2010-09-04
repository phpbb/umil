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
* @package   umil
* @author    Maël Soucaze <maelsoucaze@phpbb.fr> (Maël Soucaze) http://www.phpbb.fr/
* @copyright (c) 2008 phpBB Group
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License
* @version   $Id$
*
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

$lang = array_merge($lang, array(
	'INSTALL_TEST_MOD'				=> 'Installation du MOD de test',
	'INSTALL_TEST_MOD_CONFIRM'		=> 'Es-tu prêt à installer le MOD de test ?',

	'REMOVE_TEST_ROW'				=> 'Supprimer la ligne de test dans la table phpbb_test.',

	'TEST_BOOLEAN'					=> 'Booléen personnalisé',
	'TEST_MOD'						=> 'MOD de test',
	'TEST_MOD_EXPLAIN'				=> 'Ceci est un exemple de l’utilisation de la méthode automatique d’UMIL. Les auteurs de MOD(s) intéressés dans l’utilisation de cette fonctionnalité devraient l’ouvrir dans un éditeur de texte (comme Notepad++) afin de savoir comment il peut être utilisé.<br /><br /><strong>Ceci n’est pas un MOD réel.</strong>',
	'TEST_USERNAME'					=> 'Saisir un nom d’utilisateur',
	'TEST_USERNAME_EXPLAIN'			=> 'Saisi un nom d’utilisateur ou sélectionne un nom d’utilisateur dans la fenêtre pop-up sélectionnée.',

	'UNINSTALL_TEST_MOD'			=> 'Désinstaller le MOD de test',
	'UNINSTALL_TEST_MOD_CONFIRM'	=> 'Es-tu sûr de vouloir désinstaller le MOD de test ? Tous les réglages et les données sauvegardés par ce MOD seront supprimés !',
	'UPDATE_TEST_MOD'				=> 'Mettre à jour le MOD de test',
	'UPDATE_TEST_MOD_CONFIRM'		=> 'Es-tu sûr de vouloir mettre à jour le MOD de test ?',
));

?>