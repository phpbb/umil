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
if (!defined('IN_PHPBB'))
{
	exit;
}

define('UMIL_VERSION', '1.2.0-a1');

/**
* Multicall instructions
*
* With the "multicall" (as I am calling it) you can make a single function call and have it repeat the actions multiple times on information sent from an array.
*
* To do this (it does not work on the _exists functions), all you must do is send the first variable in the function call as an array and for each item, send an array for each of the variables in order.
*
* Example:
* $umil->config_add(array(
*	array('config_name', 'config_value', false),
*	array('config_name1', 'config_value1', false),
*	array('config_name2', 'config_value2', false),
*	array('config_name3', 'config_value3', false),
* );
*/

/**
* UMIL - Unified MOD Installation Library class
*
* Cache Functions
*	cache_purge($type = '', $style_id = 0)
*
* Config Functions:
*	config_exists($config_name, $return_result = false)
*	config_add($config_name, $config_value = '', $is_dynamic = false)
*	config_update($config_name, $config_value, $is_dynamic = false)
*	config_remove($config_name)
*
* Module Functions
*	module_exists($class, $parent, $module)
*	module_add($class, $parent = 0, $data = array())
*	module_remove($class, $parent = 0, $module = '')
*
* Permissions/Auth Functions
*	permission_exists($auth_option, $global = true)
*	permission_add($auth_option, $global = true)
*	permission_remove($auth_option, $global = true)
*	permission_set($name, $auth_option = array(), $type = 'role', $global = true, $has_permission = true)
*	permission_unset($name, $auth_option = array(), $type = 'role', $global = true)
*
* Table Functions
*	table_exists($table_name)
*	table_add($table_name, $table_data = array())
*	table_remove($table_name)
*
* Table Column Functions
*	table_column_exists($table_name, $column_name)
*	table_column_add($table_name, $column_name = '', $column_data = array())
*	table_column_update($table_name, $column_name = '', $column_data = array())
*	table_column_remove($table_name, $column_name = '')
*
* Table Key/Index Functions
*	table_index_exists($table_name, $index_name)
*	table_index_add($table_name, $index_name = '', $column = array())
*	table_index_remove($table_name, $index_name = '')
*
* Version Check Function
* 	version_check($url, $path, $file)
*/
class umil
{
	/**
	* This will hold the text output for the inputted command (if the mod author would like to display the command that was ran)
	*
	* @var string
	*/
	var $command = '';

	/**
	* This will hold the text output for the result of the command.  $user->lang['SUCCESS'] if everything worked.
	*
	* @var string
	*/
	var $result = '';

	/**
	* Auto run $this->display_results after running a command
	*/
	var $auto_display_results = false;

	/**
	* Stand Alone option (this makes it possible to just use the single umil file and not worry about any language stuff
	*/
	var $stand_alone = false;

    /**
	* Were any new permissions added (used in umil_frontend)?
	*/
	var $permissions_added = false;

	/**
	* Mod ID for saving log/record to phpbb_umil table.
	*
	* @var mixed Boolean false to completely ignore this part (no logs/records will be saved) or an integer which corresponds to the mod_id this will be stored under in the database
	*/
	var $mod_id = false;

	/**
	* Constructor
	*/
	function umil($stand_alone = false, $mod_id = false)
	{
		global $config;

		$this->stand_alone = $stand_alone;
		$this->mod_id = (is_numeric($mod_id) && $mod_id >= 1) ? (int) $mod_id : false;

		// Include some language files for the non stand-alone version
		if (!$stand_alone)
		{
			$this->add_lang(array('umil', 'acp/common', 'acp/permissions'));
		}

		// Check to see if a newer version is available.
		$info = $this->version_check('www.phpbb.com', '/updatecheck', ((defined('PHPBB_QA')) ? 'umil_qa.txt' : 'umil.txt'));
		if (is_array($info) && isset($info[0]) && isset($info[1]))
		{
			if (version_compare(UMIL_VERSION, $info[0], '<'))
			{
				global $template, $user, $phpbb_root_path;

				page_header('', false);

				$this_file = str_replace(array(phpbb_realpath($phpbb_root_path), '\\'), array('', '/'), __FILE__);
				$user->lang['UPDATE_UMIL'] = (isset($user->lang['UPDATE_UMIL'])) ? $user->lang['UPDATE_UMIL'] : 'Please download the latest UMIL (Unified MOD Install Library) from: <a href="%1$s">%1$s</a>';
				$template->assign_vars(array(
					'S_BOARD_DISABLED'		=> true,
					'L_BOARD_DISABLED'		=> (!$stand_alone) ? sprintf($user->lang['UPDATE_UMIL'], $info[1]) : sprintf('Please download the latest UMIL (Unified MOD Install Library) from: <a href="%1$s">%1$s</a>, then replace the file %2$s with the root/umil/umil.php file included in the downloaded package.', $info[1], $this_file),
				));
			}
		}

		// Check to make sure our phpbb_umil table exists
		if (!$this->config_exists('umil_db_version') || version_compare($config['umil_db_version'], UMIL_VERSION, '<'))
		{
			$versions = array(
				'1.2.0-a1' => array(
					'table_add' => array(
						array('phpbb_umil', array(
							'COLUMNS'		=> array(
								'mod_id'			=> array('UINT', NULL, 'auto_increment'),
								'mod_version'		=> array('VCHAR', ''), // Currently installed version
								'mod_versionname'	=> array('VCHAR', ''), // Will be used to check which mod is which. ($version_config_name)
								'mod_updatecheck'	=> array('VCHAR', ''), // URL to automatically check for updates to this mod
								'mod_lang'			=> array('VCHAR', ''), // Mod Name (not localized) ($mod_name)
								'mod_langfile'		=> array('VCHAR', ''), // Path to Mod Language file ($language_file)
								'mod_name'			=> array('TEXT_UNI', NULL), // Mod Name (English localized mod name, in case the language file gets deleted, lost, etc)
								'mod_desc'			=> array('TEXT_UNI', NULL), // Mod Description (English localized, in case the language file gets deleted, lost, etc)
								'mod_changes'		=> array('TEXT', NULL), // Record of all changes for all versions of the mod (basically serialize($versions))
							),
							'PRIMARY_KEY'	=> 'mod_id',
						)),
					),
					'permission_add' => array(
						'a_umil',
					),
					'module_add' => array(
						// Add ACP UMIL category and module
						array('acp', 'ACP_CAT_DOT_MODS', 'ACP_CAT_UMIL'),
						array('acp', 'ACP_CAT_UMIL', array(
							'module_basename'		=> 'umil',
						)),
					),
				),
			);
			$this->run_actions('update', $versions, 'umil_db_version');
		}
	}

	/**
	* umil_start
	*
	* A function which runs (almost) every time a function here is ran
	*/
	function umil_start()
	{
		global $db, $user;

		// Set up the command.  This will get the arguments sent to the function.
		$this->command = '';
		$args = func_get_args();
		if (sizeof($args))
		{
			$lang_key = array_shift($args);

			if (sizeof($args))
			{
				$lang_args = array();
				foreach ($args as $arg)
				{
					$lang_args[] = (isset($user->lang[$arg])) ? $user->lang[$arg] : $arg;
				}

				$this->command = @vsprintf(((isset($user->lang[$lang_key])) ? $user->lang[$lang_key] : $lang_key), $lang_args);
			}
			else
			{
				$this->command = ((isset($user->lang[$lang_key])) ? $user->lang[$lang_key] : $lang_key);
			}
		}

		$this->result('SUCCESS');
		$db->sql_return_on_error(true);

		//$db->sql_transaction('begin');
	}

	/**
	* result function
	*
	* This makes it easy to manage the stand alone version.
	*/
	function result()
	{
		global $user;

		// Set up the command.  This will get the arguments sent to the function.
		$args = func_get_args();
		if (sizeof($args))
		{
			$lang_key = array_shift($args);

			if (sizeof($args))
			{
				$lang_args = array();
				foreach ($args as $arg)
				{
					$lang_args[] = (isset($user->lang[$arg])) ? $user->lang[$arg] : $arg;
				}

				$this->result = @vsprintf(((isset($user->lang[$lang_key])) ? $user->lang[$lang_key] : $lang_key), $lang_args);
			}
			else
			{
				$this->result = ((isset($user->lang[$lang_key])) ? $user->lang[$lang_key] : $lang_key);
			}
		}
	}

	/**
	* umil_end
	*
	* A function which runs (almost) every time a function here is ran
	*/
	function umil_end()
	{
		global $db, $user;

		// Set up the result.  This will get the arguments sent to the function.
		$args = func_get_args();
		if (sizeof($args))
		{
			call_user_func_array(array($this, 'result'), $args);
		}

		if ($db->sql_error_triggered)
		{
			if ($this->result == ((isset($user->lang['SUCCESS'])) ? $user->lang['SUCCESS'] : 'SUCCESS'))
			{
				$this->result = 'SQL ERROR ' . $db->sql_error_returned['message'];
			}
			else
			{
				$this->result .= '<br /><br />SQL ERROR ' . $db->sql_error_returned['message'];
			}

			//$db->sql_transaction('rollback');
		}
		else
		{
			//$db->sql_transaction('commit');
		}

		$db->sql_return_on_error(false);

		// Auto output if requested.
		if ($this->auto_display_results && method_exists($this, 'display_results'))
		{
			$this->display_results();
		}

		return '<strong>' . $this->command . '</strong><br />' . $this->result;
	}

	/**
	* Run Actions
	*
	* Do-It-All function that can do everything required for installing/updating/uninstalling a mod based on an array of actions and the versions.
	*
	* @param string $action The action. install|update|uninstall
	* @param array $versions The array of versions and the actions for each
	* @param string $version_config_name The name of the config setting which holds/will hold the currently installed version
	* @param string $version_select Added for the UMIL Auto system to allow you to select the version you want to install/update/uninstall to.
	*/
	function run_actions($action, $versions, $version_config_name, $version_select = '')
	{
		// We will sort the actions to prevent issues from mod authors incorrectly listing the version numbers
		uksort($versions, 'version_compare');

		// Find the current version to install
		$current_version = '0.0.0';
		foreach ($versions as $version => $actions)
		{
			$current_version = $version;
		}

		$db_version = '';
		if ($this->config_exists($version_config_name))
		{
			global $config;
			$db_version = $config[$version_config_name];
		}

		// Set the action to install from update if nothing is currently installed
		if ($action == 'update' && !$db_version)
		{
			$action = 'install';
		}

		if ($action == 'install' || $action == 'update')
		{
			$version_installed = $db_version;
			foreach ($versions as $version => $version_actions)
			{
				// If we are updating
				if ($db_version && version_compare($version, $db_version, '<='))
				{
					continue;
				}

				if ($version_select && version_compare($version, $version_select, '>'))
				{
					break;
				}

				foreach ($version_actions as $method => $params)
				{
					if ($method == 'custom')
					{
						if (!is_array($params))
						{
							$params = array($params);
						}

						foreach ($params as $function_name)
						{
							if (function_exists($function_name))
							{
								$return = call_user_func($function_name, $action, $version);
								if (is_string($return))
								{
									$this->umil_start($return);
									$this->umil_end();
								}
								else if (is_array($return) && isset($return['command']))
								{
									if (is_array($return['command']))
									{
										call_user_func_array(array($this, 'umil_start'), $return['command']);
									}
									else
									{
										$this->umil_start($return['command']);
									}

									if (isset($return['result']))
									{
										$this->result($return['result']);
									}

									$this->umil_end();
								}
							}
						}
					}
					else
					{
						if (method_exists($this, $method))
						{
							call_user_func(array($this, $method), $params);
						}
					}
				}

				$version_installed = $version;
			}

			// update the version number or add it
			if ($this->config_exists($version_config_name))
			{
				$this->config_update($version_config_name, $version_installed);
			}
			else
			{
				$this->config_add($version_config_name, $version_installed);
			}
		}
		else if ($action == 'uninstall' && $db_version)
		{
			// reverse version list
			$versions = array_reverse($versions);

			foreach ($versions as $version => $version_actions)
			{
				// Uninstalling and this listed version is newer than installed
				if (version_compare($version, $db_version, '>'))
				{
					continue;
				}

				// Version selection stuff
				if ($version_select && version_compare($version, $version_select, '<='))
				{
					// update the version number
					$this->config_update($version_config_name, $version);
					break;
				}

				$cache_purge = false;
				$version_actions = array_reverse($version_actions);
				foreach ($version_actions as $method => $params)
				{
					if ($method == 'custom')
					{
						if (!is_array($params))
						{
							$params = array($params);
						}

						foreach ($params as $function_name)
						{
							if (function_exists($function_name))
							{
								$return = call_user_func($function_name, $action, $version);
								if (is_string($return))
								{
									$this->umil_start($return);
									$this->umil_end();
								}
								else if (is_array($return) && isset($return['command']))
								{
									if (is_array($return['command']))
									{
										call_user_func_array(array($this, 'umil_start'), $return['command']);
									}
									else
									{
										$this->umil_start($return['command']);
									}

									if (isset($return['result']))
									{
										$this->result($return['result']);
									}

									$this->umil_end();
								}
							}
						}
					}
					else
					{
						// This way we always run the cache purge at the end of the version (done for the uninstall because the instructions are reversed, which would cause the cache purge to be run at the beginning if it was meant to run at the end).
						if ($method == 'cache_purge')
						{
							$cache_purge = $params;
							continue;
						}

						// update mode (reversing an action) isn't possible for uninstallations
						if (strpos($method, 'update'))
						{
							continue;
						}

						// reverse function call
						$method = str_replace(array('add', 'remove', 'temp'), array('temp', 'add', 'remove'), $method);
						$method = str_replace(array('set', 'unset', 'temp'), array('temp', 'set', 'unset'), $method);

						if (method_exists($this, $method))
						{
							call_user_func(array($this, $method), ((is_array($params) ? array_reverse($params) : $params)));
						}
					}
				}

				if ($cache_purge !== false)
				{
					$this->cache_purge($cache_purge);
				}
			}

			if (!$version_select)
			{
				// Unset the version number
				$this->config_remove($version_config_name);
			}
		}
	}

	/**
	* Cache Purge
	*
	* This function is for purging either phpBB3’s data cache, authorization cache, or the styles cache.
	*
	* @param string $type The type of cache you want purged.  Available types: auth, imageset, template, theme.  Anything else sent will purge the forum's cache.
	* @param int $style_id The id of the item you want purged (if the type selected is imageset/template/theme, 0 for all items in that section)
	*/
	function cache_purge($type = '', $style_id = 0)
	{
		global $auth, $cache, $db, $user, $phpbb_root_path, $phpEx;

		// Multicall
		if (is_array($type))
		{
			if (!empty($type)) // Allow an empty array sent for the cache purge.
			{
				foreach ($type as $params)
				{
					call_user_func_array(array($this, 'cache_purge'), $params);
				}
				return;
			}
		}

		$style_id = (int) $style_id;

		switch ($type)
		{
			case 'auth' :
				$this->umil_start('AUTH_CACHE_PURGE');
				$cache->destroy('_acl_options');
				$auth->acl_clear_prefetch();

				return $this->umil_end();
			break;

			case 'imageset' :
				if ($style_id == 0)
				{
					$return = array();
					$sql = 'SELECT imageset_id
						FROM ' . STYLES_IMAGESET_TABLE;
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						$return[] = $this->cache_purge('imageset', $row['imageset_id']);
					}
					$db->sql_freeresult($result);

					return implode('<br /><br />', $return);
				}
				else
				{
					$sql = 'SELECT *
						FROM ' . STYLES_IMAGESET_TABLE . "
						WHERE imageset_id = $style_id";
					$result = $db->sql_query($sql);
					$imageset_row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					if (!$imageset_row)
					{
						$this->umil_start('IMAGESET_CACHE_PURGE', 'UNKNOWN');
						return $this->umil_end('FAIL');
					}

					$this->umil_start('IMAGESET_CACHE_PURGE', $imageset_row['imageset_name']);

					// The following is from includes/acp/acp_styles.php (edited)
					$sql_ary = array();

					$cfg_data_imageset = parse_cfg_file("{$phpbb_root_path}styles/{$imageset_row['imageset_path']}/imageset/imageset.cfg");

					$sql = 'DELETE FROM ' . STYLES_IMAGESET_DATA_TABLE . '
						WHERE imageset_id = ' . $style_id;
					$result = $db->sql_query($sql);

					foreach ($cfg_data_imageset as $image_name => $value)
					{
						if (strpos($value, '*') !== false)
						{
							if (substr($value, -1, 1) === '*')
							{
								list($image_filename, $image_height) = explode('*', $value);
								$image_width = 0;
							}
							else
							{
								list($image_filename, $image_height, $image_width) = explode('*', $value);
							}
						}
						else
						{
							$image_filename = $value;
							$image_height = $image_width = 0;
						}

						if (strpos($image_name, 'img_') === 0 && $image_filename)
						{
							$image_name = substr($image_name, 4);

							$sql_ary[] = array(
								'image_name'		=> (string) $image_name,
								'image_filename'	=> (string) $image_filename,
								'image_height'		=> (int) $image_height,
								'image_width'		=> (int) $image_width,
								'imageset_id'		=> (int) $style_id,
								'image_lang'		=> '',
							);
						}
					}

					$sql = 'SELECT lang_dir
						FROM ' . LANG_TABLE;
					$result = $db->sql_query($sql);

					while ($row = $db->sql_fetchrow($result))
					{
						if (@file_exists("{$phpbb_root_path}styles/{$imageset_row['imageset_path']}/imageset/{$row['lang_dir']}/imageset.cfg"))
						{
							$cfg_data_imageset_data = parse_cfg_file("{$phpbb_root_path}styles/{$imageset_row['imageset_path']}/imageset/{$row['lang_dir']}/imageset.cfg");
							foreach ($cfg_data_imageset_data as $image_name => $value)
							{
								if (strpos($value, '*') !== false)
								{
									if (substr($value, -1, 1) === '*')
									{
										list($image_filename, $image_height) = explode('*', $value);
										$image_width = 0;
									}
									else
									{
										list($image_filename, $image_height, $image_width) = explode('*', $value);
									}
								}
								else
								{
									$image_filename = $value;
									$image_height = $image_width = 0;
								}

								if (strpos($image_name, 'img_') === 0 && $image_filename)
								{
									$image_name = substr($image_name, 4);
									$sql_ary[] = array(
										'image_name'		=> (string) $image_name,
										'image_filename'	=> (string) $image_filename,
										'image_height'		=> (int) $image_height,
										'image_width'		=> (int) $image_width,
										'imageset_id'		=> (int) $style_id,
										'image_lang'		=> (string) $row['lang_dir'],
									);
								}
							}
						}
					}
					$db->sql_freeresult($result);

					$db->sql_multi_insert(STYLES_IMAGESET_DATA_TABLE, $sql_ary);

					$cache->destroy('sql', STYLES_IMAGESET_DATA_TABLE);

					return $this->umil_end();
				}
			break;
			//case 'imageset' :

			case 'template' :
				if ($style_id == 0)
				{
					$return = array();
					$sql = 'SELECT template_id
						FROM ' . STYLES_TEMPLATE_TABLE;
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						$return[] = $this->cache_purge('template', $row['template_id']);
					}
					$db->sql_freeresult($result);

					return implode('<br /><br />', $return);
				}
				else
				{
					$sql = 'SELECT *
						FROM ' . STYLES_TEMPLATE_TABLE . "
						WHERE template_id = $style_id";
					$result = $db->sql_query($sql);
					$template_row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					if (!$template_row)
					{
						$this->umil_start('TEMPLATE_CACHE_PURGE', 'UNKNOWN');
						return $this->umil_end('FAIL');
					}

					$this->umil_start('TEMPLATE_CACHE_PURGE', $template_row['template_name']);

					// The following is from includes/acp/acp_styles.php
					if ($template_row['template_storedb'] && file_exists("{$phpbb_root_path}styles/{$template_row['template_path']}/template/"))
					{
						$filelist = array('' => array());

						$sql = 'SELECT template_filename, template_mtime
							FROM ' . STYLES_TEMPLATE_DATA_TABLE . "
							WHERE template_id = $style_id";
						$result = $db->sql_query($sql);

						while ($row = $db->sql_fetchrow($result))
						{
//							if (@filemtime("{$phpbb_root_path}styles/{$template_row['template_path']}/template/" . $row['template_filename']) > $row['template_mtime'])
//							{
								// get folder info from the filename
								if (($slash_pos = strrpos($row['template_filename'], '/')) === false)
								{
									$filelist[''][] = $row['template_filename'];
								}
								else
								{
									$filelist[substr($row['template_filename'], 0, $slash_pos + 1)][] = substr($row['template_filename'], $slash_pos + 1, strlen($row['template_filename']) - $slash_pos - 1);
								}
//							}
						}
						$db->sql_freeresult($result);

						$includes = array();
						foreach ($filelist as $pathfile => $file_ary)
						{
							foreach ($file_ary as $file)
							{
								if (!($fp = @fopen("{$phpbb_root_path}styles/{$template_row['template_path']}$pathfile$file", 'r')))
								{
									return $this->umil_end('FAIL');
								}
								$template_data = fread($fp, filesize("{$phpbb_root_path}styles/{$template_row['template_path']}$pathfile$file"));
								fclose($fp);

								if (preg_match_all('#<!-- INCLUDE (.*?\.html) -->#is', $template_data, $matches))
								{
									foreach ($matches[1] as $match)
									{
										$includes[trim($match)][] = $file;
									}
								}
							}
						}

						foreach ($filelist as $pathfile => $file_ary)
						{
							foreach ($file_ary as $file)
							{
								// Skip index.
								if (strpos($file, 'index.') === 0)
								{
									continue;
								}

								// We could do this using extended inserts ... but that could be one
								// heck of a lot of data ...
								$sql_ary = array(
									'template_id'			=> (int) $style_id,
									'template_filename'		=> "$pathfile$file",
									'template_included'		=> (isset($includes[$file])) ? implode(':', $includes[$file]) . ':' : '',
									'template_mtime'		=> (int) filemtime("{$phpbb_root_path}styles/{$template_row['template_path']}$pathfile$file"),
									'template_data'			=> (string) file_get_contents("{$phpbb_root_path}styles/{$template_row['template_path']}$pathfile$file"),
								);

								$sql = 'UPDATE ' . STYLES_TEMPLATE_DATA_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
									WHERE template_id = $style_id
										AND template_filename = '" . $db->sql_escape("$pathfile$file") . "'";
								$db->sql_query($sql);
							}
						}
						unset($filelist);
					}

					return $this->umil_end();
				}
			break;
			//case 'template' :

			case 'theme' :
				if ($style_id == 0)
				{
					$return = array();
					$sql = 'SELECT theme_id
						FROM ' . STYLES_THEME_TABLE;
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						$return[] = $this->cache_purge('theme', $row['theme_id']);
					}
					$db->sql_freeresult($result);

					return implode('<br /><br />', $return);
				}
				else
				{
					$sql = 'SELECT *
						FROM ' . STYLES_THEME_TABLE . "
						WHERE theme_id = $style_id";
					$result = $db->sql_query($sql);
					$theme_row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					if (!$theme_row)
					{
						$this->umil_start('THEME_CACHE_PURGE', 'UNKNOWN');
						return $this->umil_end('FAIL');
					}

					$this->umil_start('THEME_CACHE_PURGE', $theme_row['theme_name']);

					// The following is from includes/acp/acp_styles.php
					if ($theme_row['theme_storedb'] && file_exists("{$phpbb_root_path}styles/{$theme_row['theme_path']}/theme/stylesheet.css"))
					{
						$stylesheet = file_get_contents($phpbb_root_path . 'styles/' . $theme_row['theme_path'] . '/theme/stylesheet.css');

						// Match CSS imports
						$matches = array();
						preg_match_all('/@import url\(["\'](.*)["\']\);/i', $stylesheet, $matches);

						if (sizeof($matches))
						{
							foreach ($matches[0] as $idx => $match)
							{
								$content = trim(file_get_contents("{$phpbb_root_path}styles/{$theme_row['theme_path']}/theme/{$matches[1][$idx]}"));
								$stylesheet = str_replace($match, $content, $stylesheet);
							}
						}

						// adjust paths
						$db_theme_data = str_replace('./', 'styles/' . $theme_row['theme_path'] . '/theme/', $stylesheet);

						// Save CSS contents
						$sql_ary = array(
							'theme_mtime'	=> (int) filemtime("{$phpbb_root_path}styles/{$theme_row['theme_path']}/theme/stylesheet.css"),
							'theme_data'	=> $db_theme_data,
						);

						$sql = 'UPDATE ' . STYLES_THEME_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
							WHERE theme_id = $style_id";
						$db->sql_query($sql);

						$cache->destroy('sql', STYLES_THEME_TABLE);
					}

					return $this->umil_end();
				}
			break;
			//case 'theme' :

			default:
				$this->umil_start('CACHE_PURGE');
				$cache->purge();

				return $this->umil_end();
			break;
		}
	}

	/**
	* Config Exists
	*
	* This function is to check to see if a config variable exists or if it does not.
	*
	* @param string $config_name The name of the config setting you wish to check for.
	* @param bool $return_result - return the config value/default if true : default false.
	*
	* @return bool true/false if config exists
	*/
	function config_exists($config_name, $return_result = false)
	{
		global $config, $db, $cache;

		$sql = 'SELECT *
				FROM ' . CONFIG_TABLE . "
				WHERE config_name = '" . $db->sql_escape($config_name) . "'";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($row)
		{
			if (!isset($config[$config_name]))
			{
				$config[$config_name] = $row['config_value'];

				if (!$row['is_dynamic'])
				{
					$cache->destroy('config');
				}
			}

			return ($return_result) ? $row : true;
		}

		// this should never happen, but if it does, we need to remove the config from the array
		if (isset($config[$config_name]))
		{
			unset($config[$config_name]);
			$cache->destroy('config');
		}

		return false;
	}

	/**
	* Config Add
	*
	* This function allows you to add a config setting.
	*
	* @param string $config_name The name of the config setting you would like to add
	* @param mixed $config_value The value of the config setting
	* @param bool $is_dynamic True if it is dynamic (changes very often) and should not be stored in the cache, false if not.
	*
	* @return result
	*/
	function config_add($config_name, $config_value = '', $is_dynamic = false)
	{
		// Multicall
		if (is_array($config_name))
		{
			foreach ($config_name as $params)
			{
				call_user_func_array(array($this, 'config_add'), $params);
			}
			return;
		}

		$this->umil_start('CONFIG_ADD', $config_name);

		if ($this->config_exists($config_name))
		{
			return $this->umil_end('CONFIG_ALREADY_EXISTS', $config_name);
		}

		set_config($config_name, $config_value, $is_dynamic);

		return $this->umil_end();
	}

	/**
	* Config Update
	*
	* This function allows you to update an existing config setting.
	*
	* @param string $config_name The name of the config setting you would like to update
	* @param mixed $config_value The value of the config setting
	* @param bool $is_dynamic True if it is dynamic (changes very often) and should not be stored in the cache, false if not.
	*
	* @return result
	*/
	function config_update($config_name, $config_value = '', $is_dynamic = false)
	{
		// Multicall
		if (is_array($config_name))
		{
			foreach ($config_name as $params)
			{
				call_user_func_array(array($this, 'config_update'), $params);
			}
			return;
		}

		$this->umil_start('CONFIG_UPDATE', $config_name);

		if (!$this->config_exists($config_name))
		{
			return $this->umil_end('CONFIG_NOT_EXIST', $config_name);
		}

		set_config($config_name, $config_value, $is_dynamic);

		return $this->umil_end();
	}

	/**
	* Config Remove
	*
	* This function allows you to remove an existing config setting.
	*
	* @param string $config_name The name of the config setting you would like to remove
	*
	* @return result
	*/
	function config_remove($config_name)
	{
		global $cache, $config, $db;

		// Multicall
		if (is_array($config_name))
		{
			foreach ($config_name as $params)
			{
				call_user_func_array(array($this, 'config_remove'), $params);
			}
			return;
		}

		$this->umil_start('CONFIG_REMOVE', $config_name);

		if (!$this->config_exists($config_name))
		{
			return $this->umil_end('CONFIG_NOT_EXIST', $config_name);
		}

		$sql = 'DELETE FROM ' . CONFIG_TABLE . " WHERE config_name = '" . $db->sql_escape($config_name) . "'";
		$db->sql_query($sql);

		unset($config[$config_name]);
		$cache->destroy('config');

		return $this->umil_end();
	}

	/**
	* Module Exists
	*
	* Check if a module exists
	*
	* @param string $class The module class(acp|mcp|ucp)
	* @param int|string|bool $parent The parent module_id|module_langname (0 for no parent).  Use false to ignore the parent check and check class wide.
	* @param mixed $module The module_langname you would like to check for to see if it exists
	*/
	function module_exists($class, $parent, $module)
	{
		global $db;

		$class = $db->sql_escape($class);
		$module = $db->sql_escape($module);

		// Allows '' to be sent
		$parent = (!$parent) ? 0 : $parent;

		$parent_sql = '';
		if ($parent !== false)
		{
			if (!is_numeric($parent))
			{
				$sql = 'SELECT module_id FROM ' . MODULES_TABLE . "
					WHERE module_langname = '" . $db->sql_escape($parent) . "'
					AND module_class = '$class'";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$row)
				{
					return false;
				}

				$parent_sql = 'AND parent_id = ' . (int) $row['module_id'];
			}
			else
			{
				$parent_sql = 'AND parent_id = ' . (int) $parent;
			}
		}

		$sql = 'SELECT module_id FROM ' . MODULES_TABLE . "
			WHERE module_class = '$class'
			$parent_sql
			AND module_langname = '$module'";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($row)
		{
			return true;
		}

		return false;
	}

	/**
	* Module Add
	*
	* Add a new module
	*
	* @param string $class The module class(acp|mcp|ucp)
	* @param int|string $parent The parent module_id|module_langname (0 for no parent)
	* @param array $data an array of the data on the new module.  This can be setup in two different ways.
	*	1. The "manual" way.  For inserting a category or one at a time.  It will be merged with the base array shown a bit below,
	*		but at the least requires 'module_langname' to be sent, and, if you want to create a module (instead of just a category) you must send module_basename and module_mode.
	* array(
	*		'module_enabled'	=> 1,
	*		'module_display'	=> 1,
	*		'module_basename'	=> '',
	*		'module_class'		=> $class,
	*		'parent_id'			=> (int) $parent,
	*		'module_langname'	=> '',
	*		'module_mode'		=> '',
	*		'module_auth'		=> '',
	*	)
	*	2. The "automatic" way.  For inserting multiple at a time based on the specs in the info file for the module(s).  For this to work the modules must be correctly setup in the info file.
	*		An example follows (this would insert the settings, log, and flag modes from the includes/acp/info/acp_asacp.php file):
	* array(
	* 		'module_basename'	=> 'asacp',
	* 		'modes'				=> array('settings', 'log', 'flag'),
	* )
	* 		Optionally you may not send 'modes' and it will insert all of the modules in that info file.
	*  @param string|bool $include_path If you would like to use a custom include path, specify that here
	*/
	function module_add($class, $parent = 0, $data = array(), $include_path = false)
	{
		global $cache, $db, $user, $phpbb_root_path, $phpEx;

		// Multicall
		if (is_array($class))
		{
			foreach ($class as $params)
			{
				call_user_func_array(array($this, 'module_add'), $params);
			}
			return;
		}

        // Allows '' to be sent
		$parent = (!$parent) ? 0 : $parent;

		// allow sending the name as a string in $data to create a category
		if (!is_array($data))
		{
			$data = array('module_langname' => $data);
		}

		if (!isset($data['module_langname']))
		{
			// The "automatic" way
			$basename = (isset($data['module_basename'])) ? $data['module_basename'] : '';
			$basename = str_replace(array('/', '\\'), '', $basename);
			$class = str_replace(array('/', '\\'), '', $class);
			$include_path = ($include_path === false) ? $phpbb_root_path . 'includes/' : $include_path;
			$info_file = "$class/info/{$class}_$basename.$phpEx";

			// The manual and automatic ways both failed...
			if (!file_exists($include_path . $info_file))
			{
				$this->umil_start('MODULE_ADD', $class, 'UNKNOWN');
				return $this->umil_end('FAIL');
			}

			$classname = "{$class}_{$basename}_info";

			if (!class_exists($classname))
			{
				include($include_path . $info_file);
			}

			$info = new $classname;
			$module = $info->module();
			unset($info);

			$result = '';
			foreach ($module['modes'] as $mode => $module_info)
			{
				if (!isset($data['modes']) || in_array($mode, $data['modes']))
				{
					$new_module = array(
						'module_basename'	=> $basename,
						'module_langname'	=> $module_info['title'],
						'module_mode'		=> $mode,
						'module_auth'		=> $module_info['auth'],
						'module_display'	=> (isset($module_info['display'])) ? $module_info['display'] : true,
					);

					// Run the "manual" way with the data we've collected.
					$result .= ((isset($data['spacer'])) ? $data['spacer'] : '<br />') . $this->module_add($class, $parent, $new_module);
				}
			}

			return $result;
		}

		// The "manual" way
		$this->umil_start('MODULE_ADD', $class, ((isset($user->lang[$data['module_langname']])) ? $user->lang[$data['module_langname']] : $data['module_langname']));

		$class = $db->sql_escape($class);

		if (!is_numeric($parent))
		{
			$sql = 'SELECT module_id FROM ' . MODULES_TABLE . "
				WHERE module_langname = '" . $db->sql_escape($parent) . "'
				AND module_class = '$class'";
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if (!$row)
			{
				return $this->umil_end('FAIL');
			}

			$data['parent_id'] = $row['module_id'];
		}

		if (!class_exists('acp_modules'))
		{
			include($phpbb_root_path . 'includes/acp/acp_modules.' . $phpEx);
			$user->add_lang('acp/modules');
		}
		$acp_modules = new acp_modules();

		$data = array_merge(array(
			'module_enabled'	=> 1,
			'module_display'	=> 1,
			'module_basename'	=> '',
			'module_class'		=> $class,
			'parent_id'			=> (int) $parent,
			'module_langname'	=> '',
			'module_mode'		=> '',
			'module_auth'		=> '',
		), $data);
		$result = $acp_modules->update_module_data($data, true);

		// update_module_data can either return a string, an empty array, or an array with a language string in...
		if (is_array($result) && !empty($result))
		{
			$this->result = implode('<br />', $result);
		}
		else if (!is_array($result) && $result !== '')
		{
			$this->result($result);
		}

		// Clear the Modules Cache
		$cache->destroy("_modules_$class");

		return $this->umil_end();
	}

	/**
	* Module Remove
	*
	* Remove a module
	*
	* @param string $class The module class(acp|mcp|ucp)
	* @param int|string|bool $parent The parent module_id|module_langname (0 for no parent).  Use false to ignore the parent check and check class wide.
	* @param int|string $module The module id|module_langname
	* @param string|bool $include_path If you would like to use a custom include path, specify that here
	*/
	function module_remove($class, $parent = 0, $module = '', $include_path = false)
	{
		global $cache, $db, $user, $phpbb_root_path, $phpEx;

		// Multicall
		if (is_array($class))
		{
			foreach ($class as $params)
			{
				call_user_func_array(array($this, 'module_remove'), $params);
			}
			return;
		}

        // Allows '' to be sent
		$parent = (!$parent) ? 0 : $parent;

		// Imitation of module_add's "automatic" and "manual" method so the uninstaller works from the same set of instructions for umil_auto
		if (is_array($module))
		{
			if (isset($module['module_langname']))
			{
				// Manual Method
				call_user_func(array($this, 'module_remove'), $class, $parent, $module['module_langname']);
				return;
			}

			// Failed.
			if (!isset($module['module_basename']))
			{
				return;
			}

			// Automatic method
			$basename = str_replace(array('/', '\\'), '', $module['module_basename']);
			$class = str_replace(array('/', '\\'), '', $class);
			$include_path = ($include_path === false) ? $phpbb_root_path . 'includes/' : $include_path;
			$info_file = "$class/info/{$class}_$basename.$phpEx";

			if (!file_exists($include_path . $info_file))
			{
				return;
			}

			$classname = "{$class}_{$basename}_info";

			if (!class_exists($classname))
			{
				include($include_path . $info_file);
			}

			$info = new $classname;
			$module_info = $info->module();
			unset($info);

			foreach ($module_info['modes'] as $mode => $info)
			{
				if (!isset($module['modes']) || in_array($mode, $module['modes']))
				{
					call_user_func(array($this, 'module_remove'), $class, $parent, $info['title']);
				}
			}
		}
		else
		{
			$class = $db->sql_escape($class);

			if (!$this->module_exists($class, $parent, $module))
			{
				$this->umil_start('MODULE_REMOVE', $class, ((isset($user->lang[$module])) ? $user->lang[$module] : $module));
				return $this->umil_end('MODULE_NOT_EXIST');
			}

			$parent_sql = '';
			if ($parent !== false)
			{
				if (!is_numeric($parent))
				{
					$sql = 'SELECT module_id FROM ' . MODULES_TABLE . "
						WHERE module_langname = '" . $db->sql_escape($parent) . "'
						AND module_class = '$class'";
					$result = $db->sql_query($sql);
					$row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					// we know it exists from the module_exists check

					$parent_sql = 'AND parent_id = ' . (int) $row['module_id'];
				}
				else
				{
					$parent_sql = 'AND parent_id = ' . (int) $parent;
				}
			}

			$module_ids = array();
			if (!is_numeric($module))
			{
				$module = $db->sql_escape($module);
				$sql = 'SELECT module_id FROM ' . MODULES_TABLE . "
					WHERE module_langname = '$module'
					AND module_class = '$class'
					$parent_sql";
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$module_ids[] = (int) $row['module_id'];
				}
				$db->sql_freeresult($result);

				$module_name = $module;
			}
			else
			{
				$module = (int) $module;
				$sql = 'SELECT module_langname FROM ' . MODULES_TABLE . "
					WHERE module_id = $module
					AND module_class = '$class'
					$parent_sql";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$module_name = $row['module_langname'];
				$module_ids[] = $module;
			}

			$this->umil_start('MODULE_REMOVE', $class, ((isset($user->lang[$module_name])) ? $user->lang[$module_name] : $module_name));

			if (!class_exists('acp_modules'))
			{
				include($phpbb_root_path . 'includes/acp/acp_modules.' . $phpEx);
				$user->add_lang('acp/modules');
			}
			$acp_modules = new acp_modules();
			$acp_modules->module_class = $class;

			foreach ($module_ids as $module_id)
			{
				$result = $acp_modules->delete_module($module_id);
				if (!empty($result))
				{
					if ($this->result == ((isset($user->lang['SUCCESS'])) ? $user->lang['SUCCESS'] : 'SUCCESS'))
					{
						$this->result = implode('<br />', $result);
					}
					else
					{
						$this->result .= '<br />' . implode('<br />', $result);
					}
				}
			}

			$cache->destroy("_modules_$class");

			return $this->umil_end();
		}
	}

	/**
	* Permission Exists
	*
	* Check if a permission (auth) setting exists
	*
	* @param string $auth_option The name of the permission (auth) option
	* @param bool $global True for checking a global permission setting, False for a local permission setting
	*
	* @return bool true if it exists, false if not
	*/
	function permission_exists($auth_option, $global = true)
	{
		global $db;

		if ($global)
		{
			$type_sql = ' AND is_global = 1';
		}
		else
		{
			$type_sql = ' AND is_local = 1';
		}

		$sql = 'SELECT auth_option_id
				FROM ' . ACL_OPTIONS_TABLE . "
				WHERE auth_option = '" . $db->sql_escape($auth_option) . "'"
				. $type_sql;
		$result = $db->sql_query($sql);

		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($row)
		{
			return true;
		}

		return false;
	}

	/**
	* Permission Add
	*
	* Add a permission (auth) option
	*
	* @param string $auth_option The name of the permission (auth) option
	* @param bool $global True for checking a global permission setting, False for a local permission setting
	*
	* @return result
	*/
	function permission_add($auth_option, $global = true)
	{
		global $db;

		// Multicall
		if (is_array($auth_option))
		{
			foreach ($auth_option as $params)
			{
				call_user_func_array(array($this, 'permission_add'), $params);
			}
			return;
		}

		$this->umil_start('PERMISSION_ADD', $auth_option);

		if ($this->permission_exists($auth_option, $global))
		{
			return $this->umil_end('PERMISSION_ALREADY_EXISTS', $auth_option);
		}

		// We've added permissions, so set to true to notify the user.
		$this->permissions_added = true;

		if (!class_exists('auth_admin'))
		{
			global $phpbb_root_path, $phpEx;

			include($phpbb_root_path . 'includes/acp/auth.' . $phpEx);
		}
		$auth_admin = new auth_admin();

		// We have to add a check to see if the !$global (if global, local, and if local, global) permission already exists.  If it does, acl_add_option currently has a bug which would break the ACL system, so we are having a work-around here.
		if ($this->permission_exists($auth_option, !$global))
		{
			$sql_ary = array(
				'is_global'	=> 1,
				'is_local'	=> 1,
			);
			$sql = 'UPDATE ' . ACL_OPTIONS_TABLE . '
				SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
				WHERE auth_option = \'' . $db->sql_escape($auth_option) . "'";
			$db->sql_query($sql);
		}
		else
		{
			if ($global)
			{
				$auth_admin->acl_add_option(array('global' => array($auth_option)));
			}
			else
			{
				$auth_admin->acl_add_option(array('local' => array($auth_option)));
			}
		}

		return $this->umil_end();
	}

	/**
	* Permission Remove
	*
	* Remove a permission (auth) option
	*
	* @param string $auth_option The name of the permission (auth) option
	* @param bool $global True for checking a global permission setting, False for a local permission setting
	*
	* @return result
	*/
	function permission_remove($auth_option, $global = true)
	{
		global $auth, $cache, $db;

		// Multicall
		if (is_array($auth_option))
		{
			foreach ($auth_option as $params)
			{
				call_user_func_array(array($this, 'permission_remove'), $params);
			}
			return;
		}

		$this->umil_start('PERMISSION_REMOVE', $auth_option);

		if (!$this->permission_exists($auth_option, $global))
		{
			return $this->umil_end('PERMISSION_NOT_EXIST', $auth_option);
		}

		if ($global)
		{
			$type_sql = ' AND is_global = 1';
		}
		else
		{
			$type_sql = ' AND is_local = 1';
		}
		$sql = 'SELECT auth_option_id, is_global, is_local FROM ' . ACL_OPTIONS_TABLE . "
			WHERE auth_option = '" . $db->sql_escape($auth_option) . "'" .
			$type_sql;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$id = $row['auth_option_id'];

		// If it is a local and global permission, do not remove the row! :P
		if ($row['is_global'] && $row['is_local'])
		{
			$sql = 'UPDATE ' . ACL_OPTIONS_TABLE . '
				SET ' . (($global) ? 'is_global = 0' : 'is_local = 0') . '
				WHERE auth_option_id = ' . $id;
			$db->sql_query($sql);
		}
		else
		{
			// Delete time
			$db->sql_query('DELETE FROM ' . ACL_GROUPS_TABLE . ' WHERE auth_option_id = ' . $id);
			$db->sql_query('DELETE FROM ' . ACL_ROLES_DATA_TABLE . ' WHERE auth_option_id = ' . $id);
			$db->sql_query('DELETE FROM ' . ACL_USERS_TABLE . ' WHERE auth_option_id = ' . $id);
			$db->sql_query('DELETE FROM ' . ACL_OPTIONS_TABLE . ' WHERE auth_option_id = ' . $id);
		}

		// Purge the auth cache
		$cache->destroy('_acl_options');
		$auth->acl_clear_prefetch();

		return $this->umil_end();
	}

	/**
	* Permission Set
	*
	* Allows you to set permissions for a certain group/role
	*
	* @param string $name The name of the role/group
	* @param string|array $auth_option The auth_option or array of auth_options you would like to set
	* @param string $type The type (role|group)
	* @param bool $has_permission True if you want to give them permission, false if you want to deny them permission
	*/
	function permission_set($name, $auth_option = array(), $type = 'role', $has_permission = true)
	{
		global $auth, $db;

		// Multicall
		if (is_array($name))
		{
			foreach ($name as $params)
			{
				call_user_func_array(array($this, 'permission_set'), $params);
			}
			return;
		}

		if (!is_array($auth_option))
		{
			$auth_option = array($auth_option);
		}

		$new_auth = array();
		$sql = 'SELECT auth_option_id FROM ' . ACL_OPTIONS_TABLE . '
			WHERE ' . $db->sql_in_set('auth_option', $auth_option);
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$new_auth[] = $row['auth_option_id'];
		}
		$db->sql_freeresult($result);

		if (!sizeof($new_auth))
		{
			return false;
		}

		$current_auth = array();
		switch ($type)
		{
			case 'role' :
				$this->umil_start('PERMISSION_SET_ROLE', $name);

				$sql = 'SELECT role_id FROM ' . ACL_ROLES_TABLE . '
					WHERE role_name = \'' . $db->sql_escape($name) . '\'';
				$db->sql_query($sql);
				$role_id = $db->sql_fetchfield('role_id');

				if (!$role_id)
				{
					return $this->umil_end('ROLE_NOT_EXIST');
				}

				$sql = 'SELECT auth_option_id, auth_setting FROM ' . ACL_ROLES_DATA_TABLE . '
					WHERE role_id = ' . $role_id;
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$current_auth[$row['auth_option_id']] = $row['auth_setting'];
				}
				$db->sql_freeresult($result);
			break;

			case 'group' :
				$sql = 'SELECT group_id FROM ' . GROUPS_TABLE . ' WHERE group_name = \'' . $db->sql_escape($name) . '\'';
				$db->sql_query($sql);
				$group_id = $db->sql_fetchfield('group_id');

				if (!$group_id)
				{
					$this->umil_start('PERMISSION_SET_GROUP', $name);
					return $this->umil_end('GROUP_NOT_EXIST');
				}

				// If the group has a role set for them we will add the requested permissions to that role.
				$sql = 'SELECT auth_role_id FROM ' . ACL_GROUPS_TABLE . '
					WHERE group_id = ' . $group_id . '
					AND auth_role_id <> 0
					AND forum_id = 0';
				$db->sql_query($sql);
				$role_id = $db->sql_fetchfield('auth_role_id');
				if ($role_id)
				{
					$sql = 'SELECT role_name FROM ' . ACL_ROLES_TABLE . '
						WHERE role_id = ' . $role_id;
					$db->sql_query($sql);
					$role_name = $db->sql_fetchfield('role_name');

					return $this->permission_set($role_name, $auth_option, 'role', $has_permission);
				}

				$this->umil_start('PERMISSION_SET_GROUP', $name);

				$sql = 'SELECT auth_option_id, auth_setting FROM ' . ACL_GROUPS_TABLE . '
					WHERE group_id = ' . $group_id;
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$current_auth[$row['auth_option_id']] = $row['auth_setting'];
				}
				$db->sql_freeresult($result);
			break;
		}

		$sql_ary = array();
		switch ($type)
		{
			case 'role' :
				foreach ($new_auth as $auth_option_id)
				{
					if (!isset($current_auth[$auth_option_id]))
					{
						$sql_ary[] = array(
							'role_id'			=> $role_id,
							'auth_option_id'	=> $auth_option_id,
							'auth_setting'		=> $has_permission,
				        );
					}
				}

				$db->sql_multi_insert(ACL_ROLES_DATA_TABLE, $sql_ary);
			break;

			case 'group' :
				foreach ($new_auth as $auth_option_id)
				{
					if (!isset($current_auth[$auth_option_id]))
					{
						$sql_ary[] = array(
							'group_id'			=> $group_id,
							'auth_option_id'	=> $auth_option_id,
							'auth_setting'		=> $has_permission,
				        );
					}
				}

				$db->sql_multi_insert(ACL_GROUPS_TABLE, $sql_ary);
			break;
		}

		$auth->acl_clear_prefetch();

		return $this->umil_end();
	}

	/**
	* Permission Unset
	*
	* Allows you to unset (remove) permissions for a certain group/role
	*
	* @param string $name The name of the role/group
	* @param string|array $auth_option The auth_option or array of auth_options you would like to set
	* @param string $type The type (role|group)
	*/
	function permission_unset($name, $auth_option = array(), $type = 'role')
	{
		global $auth, $db;

		// Multicall
		if (is_array($name))
		{
			foreach ($name as $params)
			{
				call_user_func_array(array($this, 'permission_unset'), $params);
			}
			return;
		}

		if (!is_array($auth_option))
		{
			$auth_option = array($auth_option);
		}

		$to_remove = array();
		$sql = 'SELECT auth_option_id FROM ' . ACL_OPTIONS_TABLE . '
			WHERE ' . $db->sql_in_set('auth_option', $auth_option);
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$to_remove[] = $row['auth_option_id'];
		}
		$db->sql_freeresult($result);

		if (!sizeof($to_remove))
		{
			return false;
		}

		switch ($type)
		{
			case 'role' :
				$this->umil_start('PERMISSION_UNSET_ROLE', $name);

				$sql = 'SELECT role_id FROM ' . ACL_ROLES_TABLE . '
					WHERE role_name = \'' . $db->sql_escape($name) . '\'';
				$db->sql_query($sql);
				$role_id = $db->sql_fetchfield('role_id');

				if (!$role_id)
				{
					return $this->umil_end('ROLE_NOT_EXIST');
				}

				$sql = 'DELETE FROM ' . ACL_ROLES_DATA_TABLE . '
					WHERE ' . $db->sql_in_set('auth_option_id', $to_remove);
				$db->sql_query($sql);
			break;

			case 'group' :
				$sql = 'SELECT group_id FROM ' . GROUPS_TABLE . ' WHERE group_name = \'' . $db->sql_escape($name) . '\'';
				$db->sql_query($sql);
				$group_id = $db->sql_fetchfield('group_id');

				if (!$group_id)
				{
					$this->umil_start('PERMISSION_UNSET_GROUP', $name);
					return $this->umil_end('GROUP_NOT_EXIST');
				}

				// If the group has a role set for them we will remove the requested permissions from that role.
				$sql = 'SELECT auth_role_id FROM ' . ACL_GROUPS_TABLE . '
					WHERE group_id = ' . $group_id . '
					AND auth_role_id <> 0';
				$db->sql_query($sql);
				$role_id = $db->sql_fetchfield('auth_role_id');
				if ($role_id)
				{
					$sql = 'SELECT role_name FROM ' . ACL_ROLES_TABLE . '
						WHERE role_id = ' . $role_id;
					$db->sql_query($sql);
					$role_name = $db->sql_fetchfield('role_name');

					return $this->permission_unset($role_name, $auth_option, 'role');
				}

				$this->umil_start('PERMISSION_UNSET_GROUP', $name);

				$sql = 'DELETE FROM ' . ACL_GROUPS_TABLE . '
					WHERE ' . $db->sql_in_set('auth_option_id', $to_remove);
				$db->sql_query($sql);
			break;
		}

		$auth->acl_clear_prefetch();

		return $this->umil_end();
	}

	/**
	* Table Exists
	*
	* Check if a table exists in the DB or not
	*
	* @param string $table_name The table name to check for
	*
	* @return bool true if the table exists, false if not
	*/
	function table_exists($table_name)
	{
		global $db;

		$this->get_table_name($table_name);

		if (!function_exists('get_tables'))
		{
			global $phpbb_root_path, $phpEx;
			include($phpbb_root_path . 'includes/functions_install.' . $phpEx);
		}

		$tables = get_tables($db);

		if (in_array($table_name, $tables))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	* Table Add
	*
	* This only supports input from the array format of db_tools or create_schema_files.
	*/
	function table_add($table_name, $table_data = array())
	{
		global $db, $dbms, $user;

		// Multicall
		if (is_array($table_name))
		{
			foreach ($table_name as $params)
			{
				call_user_func_array(array($this, 'table_add'), $params);
			}
			return;
		}

		$this->get_table_name($table_name);

		$this->umil_start('TABLE_ADD', $table_name);

		if ($this->table_exists($table_name))
		{
			return $this->umil_end('TABLE_ALREADY_EXISTS', $table_name);
		}

		if (!is_array($table_data))
		{
			return $this->umil_end('FAIL');
		}

		$this->sql_create_table($table_name, $table_data);

		return $this->umil_end();
	}

	/**
	* Table Remove
	*
	* Delete/Drop a DB table
	*/
	function table_remove($table_name)
	{
		global $db;

		// Multicall
		if (is_array($table_name))
		{
			foreach ($table_name as $params)
			{
				call_user_func_array(array($this, 'table_remove'), $params);
			}
			return;
		}

		$this->get_table_name($table_name);

		$this->umil_start('TABLE_REMOVE', $table_name);

		if (!$this->table_exists($table_name))
		{
			return $this->umil_end('TABLE_NOT_EXIST', $table_name);
		}

		if (!class_exists('phpbb_db_tools'))
		{
			global $phpbb_root_path, $phpEx;
			include($phpbb_root_path . 'includes/db/db_tools.' . $phpEx);
		}

		$db_tools = new phpbb_db_tools($db);

		if (method_exists($db_tools, 'sql_table_drop'))
		{
			// Added in 3.0.5
			$db_tools->sql_table_drop($table_name);
		}
		else
		{
			$db->sql_query('DROP TABLE ' . $table_name);
		}

		return $this->umil_end();
	}

	/**
	* Table Column Exists
	*
	* Check to see if a column exists in a table
	*/
	function table_column_exists($table_name, $column_name)
	{
		global $db;

		$this->get_table_name($table_name);

		if (!class_exists('phpbb_db_tools'))
		{
			global $phpbb_root_path, $phpEx;
			include($phpbb_root_path . 'includes/db/db_tools.' . $phpEx);
		}

		$db_tools = new phpbb_db_tools($db);
		return $db_tools->sql_column_exists($table_name, $column_name);
	}

	/**
	* Table Column Add
	*
	* Add a new column to a table.
	*/
	function table_column_add($table_name, $column_name = '', $column_data = array())
	{
		global $db;

		// Multicall
		if (is_array($table_name))
		{
			foreach ($table_name as $params)
			{
				call_user_func_array(array($this, 'table_column_add'), $params);
			}
			return;
		}

		$this->get_table_name($table_name);

		$this->umil_start('TABLE_COLUMN_ADD', $table_name, $column_name);

		if ($this->table_column_exists($table_name, $column_name))
		{
			return $this->umil_end('TABLE_COLUMN_ALREADY_EXISTS', $table_name, $column_name);
		}

		if (!class_exists('phpbb_db_tools'))
		{
			global $phpbb_root_path, $phpEx;
			include($phpbb_root_path . 'includes/db/db_tools.' . $phpEx);
		}

		$db_tools = new phpbb_db_tools($db);
		$db_tools->sql_column_add($table_name, $column_name, $column_data);

		return $this->umil_end();
	}

	/**
	* Table Column Update
	*
	* Alter/Update a column in a table.  You can not change a column name with this.
	*/
	function table_column_update($table_name, $column_name = '', $column_data = array())
	{
		global $db;

		// Multicall
		if (is_array($table_name))
		{
			foreach ($table_name as $params)
			{
				call_user_func_array(array($this, 'table_column_update'), $params);
			}
			return;
		}

		$this->get_table_name($table_name);

		$this->umil_start('TABLE_COLUMN_UPDATE', $table_name, $column_name);

		if (!$this->table_column_exists($table_name, $column_name))
		{
			return $this->umil_end('TABLE_COLUMN_NOT_EXIST', $table_name, $column_name);
		}

		if (!class_exists('phpbb_db_tools'))
		{
			global $phpbb_root_path, $phpEx;
			include($phpbb_root_path . 'includes/db/db_tools.' . $phpEx);
		}

		$db_tools = new phpbb_db_tools($db);
		$db_tools->sql_column_change($table_name, $column_name, $column_data);

		return $this->umil_end();
	}

	/**
	* Table Column Remove
	*
	* Remove a column from a table
	*/
	function table_column_remove($table_name, $column_name = '')
	{
		global $db;

		// Multicall
		if (is_array($table_name))
		{
			foreach ($table_name as $params)
			{
				call_user_func_array(array($this, 'table_column_remove'), $params);
			}
			return;
		}

		$this->get_table_name($table_name);

		$this->umil_start('TABLE_COLUMN_REMOVE', $table_name, $column_name);

		if (!$this->table_column_exists($table_name, $column_name))
		{
			return $this->umil_end('TABLE_COLUMN_NOT_EXIST', $table_name, $column_name);
		}

		if (!class_exists('phpbb_db_tools'))
		{
			global $phpbb_root_path, $phpEx;
			include($phpbb_root_path . 'includes/db/db_tools.' . $phpEx);
		}

		$db_tools = new phpbb_db_tools($db);
		$db_tools->sql_column_remove($table_name, $column_name);

		return $this->umil_end();
	}

	/**
	* Table Index Exists
	*
	* Check if a table key/index exists on a table (can not check primary or unique)
	*/
	function table_index_exists($table_name, $index_name)
	{
		global $db;

		$this->get_table_name($table_name);

		if (!class_exists('phpbb_db_tools'))
		{
			global $phpbb_root_path, $phpEx;
			include($phpbb_root_path . 'includes/db/db_tools.' . $phpEx);
		}

		$db_tools = new phpbb_db_tools($db);

		$indexes = $db_tools->sql_list_index($table_name);

		if (in_array($index_name, $indexes))
		{
			return true;
		}

		return false;
	}

	/**
	* Table Index Add
	*
	* Add a new key/index to a table
	*/
	function table_index_add($table_name, $index_name = '', $column = array())
	{
		global $db;

		// Multicall
		if (is_array($table_name))
		{
			foreach ($table_name as $params)
			{
				call_user_func_array(array($this, 'table_index_add'), $params);
			}
			return;
		}

		$this->get_table_name($table_name);

		$this->umil_start('TABLE_KEY_ADD', $table_name, $index_name);

		if ($this->table_index_exists($table_name, $index_name))
		{
			return $this->umil_end('TABLE_KEY_ALREADY_EXIST', $table_name, $index_name);
		}

		if (!is_array($column))
		{
			$column = array($column);
		}

		if (empty($column))
		{
			$column = array($index_name);
		}

		if (!class_exists('phpbb_db_tools'))
		{
			global $phpbb_root_path, $phpEx;
			include($phpbb_root_path . 'includes/db/db_tools.' . $phpEx);
		}

		$db_tools = new phpbb_db_tools($db);
		$db_tools->sql_create_index($table_name, $index_name, $column);

		return $this->umil_end();
	}

	/**
	* Table Index Remove
	*
	* Remove a key/index from a table
	*/
	function table_index_remove($table_name, $index_name = '')
	{
		global $db;

		// Multicall
		if (is_array($table_name))
		{
			foreach ($table_name as $params)
			{
				call_user_func_array(array($this, 'table_index_remove'), $params);
			}
			return;
		}

		$this->get_table_name($table_name);

		$this->umil_start('TABLE_KEY_REMOVE', $table_name, $index_name);

		if (!$this->table_index_exists($table_name, $index_name))
		{
			return $this->umil_end('TABLE_KEY_NOT_EXIST', $table_name, $index_name);
		}

		if (!class_exists('phpbb_db_tools'))
		{
			global $phpbb_root_path, $phpEx;
			include($phpbb_root_path . 'includes/db/db_tools.' . $phpEx);
		}

		$db_tools = new phpbb_db_tools($db);
		$db_tools->sql_index_drop($table_name, $index_name);

		return $this->umil_end();
	}

	/**
	* Version Checker
	*
	* Format the file like the following:
	* http://www.phpbb.com/updatecheck/30x.txt
	*
	* @param string $url The url to access (ex: www.phpbb.com)
	* @param string $path The path to access (ex: /updatecheck)
	* @param string $file The name of the file to access (ex: 30x.txt)
	*
	* @return array|bool False if there was any error, or an array (each line in the file as a value)
	*/
	function version_check($url, $path, $file, $timeout = 10, $port = 80)
	{
		if (!function_exists('get_remote_file'))
		{
			global $phpbb_root_path, $phpEx;

			include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
		}

		$errstr = $errno = '';

		$info = get_remote_file($url, $path, $file, $errstr, $errno, $port, $timeout);

		if ($info === false)
		{
			return false;
		}

		$info = str_replace("\r\n", "\n", $info);
		$info = explode("\n", $info);

		return $info;
	}

	/**
	* Create SQL Table (from phpBB 3.0.5, placed here for backwards compatibility)
	*
	* @param string	$table_name	The table name to create
	* @param array	$table_data	Array containing table data.
	*/
	function sql_create_table($table_name, $table_data)
	{
		global $db;

		if (!class_exists('phpbb_db_tools'))
		{
			global $phpbb_root_path, $phpEx;
			include($phpbb_root_path . 'includes/db/db_tools.' . $phpEx);
		}

		$db_tools = new phpbb_db_tools($db);

		// Use the method from db_tools if it exists
		if (method_exists($db_tools, 'sql_create_table'))
		{
			return $db_tools->sql_create_table($table_name, $table_data);
		}

		// holds the DDL for a column
		$columns = $statements = array();

		// Begin transaction
		$statements[] = 'begin';

		// Determine if we have created a PRIMARY KEY in the earliest
		$primary_key_gen = false;

		// Determine if the table must be created with TEXTIMAGE
		$create_textimage = false;

		// Determine if the table requires a sequence
		$create_sequence = false;

		// Begin table sql statement
		switch ($db_tools->sql_layer)
		{
			case 'mssql':
				$table_sql = 'CREATE TABLE [' . $table_name . '] (' . "\n";
			break;

			default:
				$table_sql = 'CREATE TABLE ' . $table_name . ' (' . "\n";
			break;
		}

		// Iterate through the columns to create a table
		foreach ($table_data['COLUMNS'] as $column_name => $column_data)
		{
			// here lies an array, filled with information compiled on the column's data
			$prepared_column = $db_tools->sql_prepare_column_data($table_name, $column_name, $column_data);

			// here we add the definition of the new column to the list of columns
			switch ($db_tools->sql_layer)
			{
				case 'mssql':
					$columns[] = "\t [{$column_name}] " . $prepared_column['column_type_sql_default'];
				break;

				default:
					$columns[] = "\t {$column_name} " . $prepared_column['column_type_sql'];
				break;
			}

			// see if we have found a primary key set due to a column definition if we have found it, we can stop looking
			if (!$primary_key_gen)
			{
				$primary_key_gen = isset($prepared_column['primary_key_set']) && $prepared_column['primary_key_set'];
			}

			// create textimage DDL based off of the existance of certain column types
			if (!$create_textimage)
			{
				$create_textimage = isset($prepared_column['textimage']) && $prepared_column['textimage'];
			}

			// create sequence DDL based off of the existance of auto incrementing columns
			if (!$create_sequence && isset($prepared_column['auto_increment']) && $prepared_column['auto_increment'])
			{
				$create_sequence = $column_name;
			}
		}

		// this makes up all the columns in the create table statement
		$table_sql .= implode(",\n", $columns);

		// Close the table for two DBMS and add to the statements
		switch ($db_tools->sql_layer)
		{
			case 'firebird':
				$table_sql .= "\n);";
				$statements[] = $table_sql;
			break;

			case 'mssql':
				$table_sql .= "\n) ON [PRIMARY]" . (($create_textimage) ? ' TEXTIMAGE_ON [PRIMARY]' : '');
				$statements[] = $table_sql;
			break;
		}

		// we have yet to create a primary key for this table,
		// this means that we can add the one we really wanted instead
		if (!$primary_key_gen)
		{
			// Write primary key
			if (isset($table_data['PRIMARY_KEY']))
			{
				if (!is_array($table_data['PRIMARY_KEY']))
				{
					$table_data['PRIMARY_KEY'] = array($table_data['PRIMARY_KEY']);
				}

				switch ($db_tools->sql_layer)
				{
					case 'mysql_40':
					case 'mysql_41':
					case 'postgres':
					case 'sqlite':
						$table_sql .= ",\n\t PRIMARY KEY (" . implode(', ', $table_data['PRIMARY_KEY']) . ')';
					break;

					case 'firebird':
					case 'mssql':
						$primary_key_stmts = $db_tools->sql_create_primary_key($table_name, $table_data['PRIMARY_KEY']);
						foreach ($primary_key_stmts as $pk_stmt)
						{
							$statements[] = $pk_stmt;
						}
					break;

					case 'oracle':
						$table_sql .= ",\n\t CONSTRAINT pk_{$table_name} PRIMARY KEY (" . implode(', ', $table_data['PRIMARY_KEY']) . ')';
					break;
				}
			}
		}

		// close the table
		switch ($db_tools->sql_layer)
		{
			case 'mysql_41':
				// make sure the table is in UTF-8 mode
				$table_sql .= "\n) CHARACTER SET `utf8` COLLATE `utf8_bin`;";
				$statements[] = $table_sql;
			break;

			case 'mysql_40':
			case 'sqlite':
				$table_sql .= "\n);";
				$statements[] = $table_sql;
			break;

			case 'postgres':
				// do we need to add a sequence for auto incrementing columns?
				if ($create_sequence)
				{
					$statements[] = "CREATE SEQUENCE {$table_name}_seq;";
				}

				$table_sql .= "\n);";
				$statements[] = $table_sql;
			break;

			case 'oracle':
				$table_sql .= "\n);";
				$statements[] = $table_sql;

				// do we need to add a sequence and a tigger for auto incrementing columns?
				if ($create_sequence)
				{
					// create the actual sequence
					$statements[] = "CREATE SEQUENCE {$table_name}_seq";

					// the trigger is the mechanism by which we increment the counter
					$trigger = "CREATE OR REPLACE TRIGGER t_{$table_name}\n";
					$trigger .= "BEFORE INSERT ON {$table_name}\n";
					$trigger .= "FOR EACH ROW WHEN (\n";
					$trigger .= "\tnew.{$create_sequence} IS NULL OR new.{$create_sequence} = 0\n";
					$trigger .= ")\n";
					$trigger .= "BEGIN\n";
					$trigger .= "\tSELECT {$table_name}_seq.nextval\n";
					$trigger .= "\tINTO :new.{$create_sequence}\n";
					$trigger .= "\tFROM dual\n";
					$trigger .= "END;";

					$statements[] = $trigger;
				}
			break;

			case 'firebird':
				if ($create_sequence)
				{
					$statements[] = "CREATE SEQUENCE {$table_name}_seq;";
				}
			break;
		}

		// Write Keys
		if (isset($table_data['KEYS']))
		{
			foreach ($table_data['KEYS'] as $key_name => $key_data)
			{
				if (!is_array($key_data[1]))
				{
					$key_data[1] = array($key_data[1]);
				}

				$old_return_statements = $db_tools->return_statements;
				$db_tools->return_statements = true;

				$key_stmts = ($key_data[0] == 'UNIQUE') ? $db_tools->sql_create_unique_index($table_name, $key_name, $key_data[1]) : $db_tools->sql_create_index($table_name, $key_name, $key_data[1]);

				foreach ($key_stmts as $key_stmt)
				{
					$statements[] = $key_stmt;
				}

				$db_tools->return_statements = $old_return_statements;
			}
		}

		// Commit Transaction
		$statements[] = 'commit';

		return $db_tools->_sql_run_sql($statements);
	}

	/**
	* Get the real table name
	*
	* @param mixed $table_name
	*/
	function get_table_name(&$table_name)
	{
		global $table_prefix;

		// Replacing phpbb_ with the $table_prefix, but, just in case we have a different table prefix with phpbb_ in it (say, like phpbb_3), we are replacing the table prefix with phpbb_ first to make sure we do not have issues.
		$table_name = str_replace('phpbb_', $table_prefix, str_replace($table_prefix, 'phpbb_', $table_name));
	}

	/**
	* Include a language file (helper for the fall-back options)
	*
	* @param mixed $file The file name or array of file names
	* @param bool $roe (Return On Error) false to trigger an error if the language file does not exist, true to return boolean false if it does not.
	*/
	function add_lang($file, $roe = false)
	{
		global $config, $user, $phpbb_root_path, $phpEx;

		// Multiple?
		if (is_array($file))
		{
			foreach ($file as $f)
			{
				$this->add_lang($f);
			}
			return;
		}

		// First we check if the language file for the user's language is available, if not we check if the board's default language is available, if not we check the english file.
		if (isset($user->data['user_lang']) && file_exists("{$phpbb_root_path}umil/language/{$user->data['user_lang']}/$file.$phpEx"))
		{
			$path = 'umil/language/' . $user->data['user_lang'];
		}
		else if (file_exists("{$phpbb_root_path}umil/language/" . basename($config['default_lang']) . "/$file.$phpEx"))
		{
			$path = 'umil/language/' . basename($config['default_lang']);
		}
		else if (file_exists("{$phpbb_root_path}umil/language/en/$file.$phpEx"))
		{
			$path = 'umil/language/' . 'en';
		}
		// Just for the hell of it...lets try the language/ folder as well...
		else if (isset($user->data['user_lang']) && file_exists("{$phpbb_root_path}language/{$user->data['user_lang']}/$file.$phpEx"))
		{
			$path = 'language/' . $user->data['user_lang'];
		}
		else if (file_exists("{$phpbb_root_path}language/" . basename($config['default_lang']) . "/$file.$phpEx"))
		{
			$path = 'language/' . basename($config['default_lang']);
		}
		else if (file_exists("{$phpbb_root_path}language/en/$file.$phpEx"))
		{
			$path = 'language/en';
		}
		// All options have been exhausted...it doesn't exist.
		else
		{
			if ($roe)
			{
				return false;
			}
			else
			{
				trigger_error("Language file 'umil/language/{$user->data['user_lang']}/$file.$phpEx' missing.", E_USER_ERROR);
			}
		}

		$user->add_lang("./../../$path/$file");
	}
}

?>