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

class acp_umil_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_ban',
			'title'		=> 'ACP_UMIL',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'default'		=> array('title' => 'ACP_UMIL', 'auth' => 'acl_a_umil', 'cat' => array('ACP_MODS_GENERAL')),
			),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}

?>