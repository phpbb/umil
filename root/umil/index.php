<?php
/**
 *
 * @author Nathan Guse (EXreaction) http://lithiumstudios.org
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
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

// This will setup the base UMIL System automatically
if (!class_exists('umil'))
{
    if (!file_exists($phpbb_root_path . 'umil/umil.' . $phpEx))
	{
		trigger_error('Please download the latest UMIL (Unified MOD Install Library) from: <a href="http://www.phpbb.com/mods/umil/">phpBB.com/mods/umil</a>', E_USER_ERROR);
	}

	include($phpbb_root_path . 'umil/umil.' . $phpEx);
}
$umil = new umil();

// redirect to UMIL ACP Panel
redirect(append_sid($phpbb_root_path . 'adm/index.' . $phpEx, 'i=umil&amp;mode=default'));
?>