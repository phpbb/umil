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

if (!defined('IN_PHPBB'))
{
	exit;
}

class acp_umil
{
	var $u_action;
	var $new_config;

	function main($id, $mode)
	{
		global $db, $user, $auth, $template, $table_prefix, $cache;
		global $config, $phpbb_admin_path, $phpbb_root_path, $phpEx;

		if (!class_exists('umil'))
		{
		    if (!file_exists($phpbb_root_path . 'umil/umil.' . $phpEx))
			{
				trigger_error('Please download the latest UMIL (Unified MOD Install Library) from: <a href="http://www.phpbb.com/mods/umil/">phpBB.com/mods/umil</a>', E_USER_ERROR);
			}

			include($phpbb_root_path . 'umil/umil.' . $phpEx);
		}
		$umil = new umil();

		$umil->add_lang('acp_umil');

		$submit = (isset($_POST['submit'])) ? true : false;
		$action = request_var('action', '');
		$id = request_var('id', 0);
		$mod = request_var('mod', '');

		$form_key = 'acp_umil';
		add_form_key($form_key);

		if ($submit && !check_form_key($form_key))
		{
			trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$this->tpl_name = 'acp_umil';
		$this->page_title = 'ACP_UMIL';

		// Get the available mods
		$available_mods = get_available_mods($umil);

		switch ($action)
		{
			case 'install' :
				trigger_error('not done' . adm_back_link($this->u_action));
			break;

			case 'update' :
			case 'uninstall' :
				if (confirm_box(true))
				{
					trigger_error('not done' . adm_back_link($this->u_action));
				}
				else
				{
					confirm_box(false, 'DELETE_ITEM');
				}
			break;

			default :
				if ($id)
				{
					$sql = 'SELECT * FROM ' . $table_prefix . 'umil';
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						$template->assign_block_vars('mods', array(
						));
					}
					$db->sql_freeresult($result);
				}
				else
				{
					$installed_mods = array();

					// Get installed mods
					$sql = 'SELECT * FROM ' . $table_prefix . 'umil';
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						$installed_mods[$row['mod_versionname']] = array_merge($row, array(
							'mod_name'		=> (isset($user->lang[$row['mod_lang']])) ? $user->lang[$row['mod_lang']] : $row['mod_name'],
							'mod_desc'		=> (isset($user->lang[$row['mod_lang'] . '_EXPLAIN'])) ? $user->lang[$row['mod_lang'] . '_EXPLAIN'] : $row['mod_desc'],
						));

					}
					$db->sql_freeresult($result);

					// Output installed mods
					foreach ($installed_mods as $row)
					{
						if ($row['mod_langfile'])
						{
							$umil->add_lang($row['mod_langfile'], true);
						}

						// is a new version available locally?
						$update = (isset($available_mods[$row['mod_versionname']]) && version_compare($available_mods[$row['mod_versionname']], $row['mod_version'], '>')) ? true : false;

						// Check for the latest version remotely (if possible)
						if ($row['mod_updatecheck'])
						{
							$file = substr($row['mod_updatecheck'], (strrpos($row['mod_updatecheck'], '/') + 1));
							$url = substr($row['mod_updatecheck'], 0, strrpos($row['mod_updatecheck'], '/'));
							$url = parse_url($url);

							// Give 2 seconds to check for the latest version
							$latest_version = $umil->version_check($url['host'], $url['path'], $file, 2);
						}

						$template->assign_block_vars('installed_mods', array(
							'MOD_NAME'		=> $row['mod_name'],
							'MOD_VERSION'	=> $row['mod_version'],

							'LATEST_VERSION'	=> ($row['mod_updatecheck'] && isset($latest_version[0])) ? $latest_version[0] : '',

							'U_UPDATE'		=> ($update) ? $this->u_action . '&amp;action=update&amp;id=' . $row['mod_id'] : '',
							'U_UNINSTALL'	=> $this->u_action . '&amp;action=uninstall&amp;id=' . $row['mod_id'],
						));
					}

					// Output uninstalled mods
					foreach ($available_mods as $row)
					{
						$template->assign_block_vars('uninstalled_mods', array(
							'MOD_NAME'		=> $row['mod_name'],
							'MOD_VERSION'	=> $row['mod_version'],

							'LATEST_VERSION'	=> (isset($row['mod_versionlast'][0])) ? $row['mod_versionlast'][0] : '',

							'U_INSTALL'	=> $this->u_action . '&amp;action=install&amp;mod=' . $row['mod_versionname'],
						));
					}
				}


			break;
		}
	}

	function get_available_mods(&$umil)
	{
		global $user, $phpbb_root_path, $phpEx;

		$available_mods = array();

		$dir = @opendir($phpbb_root_path . 'umil/mods');
		if ($dir)
		{
			while (($file = readdir($dir)) !== false)
			{
				if (strpos($file, $phpEx))
				{
					include($phpbb_root_path . 'umil/mods/' . $file);

					if (!isset($mod_name) || !isset($language_file) || !isset($version_config_name) || !isset($versions))
					{
						// Not a valid UMI File
						unset($mod_name, $language_file, $version_config_name, $versions);
						continue;
					}

					$umil->add_lang($language_file, true);

					// Find the current version
					$current_version = '0.0.0';
					foreach ($versions as $version => $actions)
					{
						$current_version = $version;
					}

					// Check for the latest version remotely (if possible)
					if ($version_check)
					{
						$file = substr($version_check, (strrpos($version_check, '/') + 1));
						$url = substr($version_check, 0, strrpos($version_check, '/'));
						$url = parse_url($url);

						// Give 2 seconds to check for the latest version
						$latest_version = $umil->version_check($url['host'], $url['path'], $file, 2);
					}

					$available_mods[$version_config_name] = array(
						'mod_version'		=> $current_version,
						'mod_versionname'	=> $version_config_name,
						'mod_versionlast'	=> ($version_check) ? $latest_version : '',
						'mod_updatecheck'	=> (isset($version_check)) ? $version_check : '',
						'mod_lang'			=> $mod_name,
						'mod_langfile'		=> $language_file,
						'mod_name'			=> (isset($user->lang[$mod_name])) ? $user->lang[$mod_name] : $mod_name,
						'mod_desc'			=> (isset($user->lang[$mod_name . '_EXPLAIN'])) ? $user->lang[$mod_name . '_EXPLAIN'] : '',
					);

					unset($mod_name, $language_file, $version_config_name, $versions, $version_check);
				}
			}
			closedir($dir);
		}

		return $available_mods;
	}
}

?>