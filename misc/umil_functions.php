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

/**
* Convert plain SQL to the UMIL array format (only supports table creation currently)
*
* WARNING: This function can only GUESS in some instances.
*	You must compare the results yourself and make sure that the returned result is what it should be
*	Watch for vchar/text fields especially!
*
* @param string $sql
*/
function umil_convert_sql($sql)
{
	// Storing all regular expressions (you may need to tweak some of these for your own use, they are somewhat restrictive to prevent problems)
	$regex = array(
		'table_add'						=> '#[^\#]CREATE TABLE ([a-z_]+) ([^;]+);#',
		'table_add_key'					=> '#\(([a-z_,\s]+)\)#',
		'table_add_key_name'			=> '#([a-z_]+)#',
		'table_add_column_name'			=> '#([a-z_]+)#',
		'table_add_column_default'		=> '#default \'([^\']*)\'#i',
		'table_add_is_primary'			=> '#PRIMARY KEY#i',
		'table_add_is_unique'			=> '#UNIQUE KEY#i',
		'table_add_is_key'				=> '#KEY#i',
		'table_add_is_auto_increment'	=> '#AUTO_INCREMENT#i',
		'table_add_is_true_sort'		=> '#COLLATE utf8_unicode_ci#i',
	);

	// Column types
	$column_types = array(
		'int(%d)'					=> 'INT:',
		'bigint(20)'				=> 'BINT',
		'mediumint(8) UNSIGNED'		=> 'UINT',
		'int(%d) UNSIGNED'			=> 'UINT:',
		'tinyint(%d)'				=> 'TINT:',
		'smallint(4) UNSIGNED'		=> 'USINT',
		'tinyint(1) UNSIGNED'		=> 'BOOL',
		'varchar(255)'				=> 'VCHAR',
		'varchar(%d)'				=> 'VCHAR:',
		'char(%d)'					=> 'CHAR:',
		'text'						=> 'TEXT_UNI',
		'mediumtext'				=> 'MTEXT_UNI',
		'int(11) UNSIGNED'			=> 'TIMESTAMP',
		'decimal(%d,2)'				=> 'DECIMAL:',
		'decimal(%d,3)'				=> 'PDECIMAL:',
		'varbinary(255)'			=> 'VARBINARY',

		// Extras
		'float'						=> 'PDECIMAL',
		'decimal'					=> 'PDECIMAL',
		'mediumint'					=> 'UINT',
		'smallint'					=> 'USINT',
		'varbinary'					=> 'VARBINARY',
	);

	// No backticks
	$sql = str_replace('`', '', $sql);

	// Will hold all of teh data to return
	$data = array();

	// Convert tables
	$table_matches = false;
	preg_match_all($regex['table_add'], $sql, $table_matches);

	foreach ($table_matches[1] as $cnt => $table_name)
	{
		$table_data = array();

		// Explode based on newlines
		$lines = preg_split('#[\r\n|\n]#', $table_matches[2][$cnt]);

		foreach ($lines as $ln_num => $line)
		{
			$line = trim($line);

			// Ignore blank lines
			if (!$line || strpos($line, '(') === 0 || strpos($line, ')') === 0)
			{
				continue;
			}

			if (preg_match($regex['table_add_is_primary'], $line)) // Primary Key
			{
				// Try to find the key part
				if (($key_string = quick_preg_match($regex['table_add_key'], $line)) === false)
				{
					continue;
				}

				// No spaces
				$key_string = str_replace(' ', '', $key_string);

				$table_data['PRIMARY_KEY'] = explode(',', $key_string);
			}
			else if (preg_match($regex['table_add_is_unique'], $line)) // Unique Key
			{
				// Try to find the key part
				if (($key_name = quick_preg_match($regex['table_add_key_name'], $line)) === false)
				{
					continue;
				}
				if (($key_string = quick_preg_match($regex['table_add_key'], $line)) === false)
				{
					continue;
				}

				// BOOM!
				$key_string = explode(',', str_replace(' ', '', $key_string));

				$table_data['KEYS'][$key_name] = array('UNIQUE', ((sizeof($key_string) > 1) ? $key_string : $key_string[0]));
			}
			else if (preg_match($regex['table_add_is_key'], $line)) // Key
			{
				// Try to find the key part
				if (($key_name = quick_preg_match($regex['table_add_key_name'], $line)) === false)
				{
					continue;
				}
				if (($key_string = quick_preg_match($regex['table_add_key'], $line)) === false)
				{
					continue;
				}

				// BOOM!
				$key_string = explode(',', str_replace(' ', '', $key_string));

				$table_data['KEYS'][$key_name] = array('INDEX', ((sizeof($key_string) > 1) ? $key_string : $key_string[0]));
			}
			else // Column
			{
				// Special field?
				$special = false;
				if (preg_match($regex['table_add_is_auto_increment'], $line))
				{
					// Remove it from the line
					$line = preg_replace($regex['table_add_is_auto_increment'], '', $line);

					$special = 'auto_increment';
				}
				if (preg_match($regex['table_add_is_true_sort'], $line))
				{
					// Remove it from the line
					$line = preg_replace($regex['table_add_is_true_sort'], '', $line);

					$special = 'true_sort';
				}

				// Ignore not null/null (table creator will do this on its own)
				$line = preg_replace('#NOT NULL|NULL|,#i', '', $line);

				// Column Name
				if (($column_name = quick_preg_match($regex['table_add_column_name'], $line)) === false)
				{
					continue;
				}

				// Remove the column name for further work (up to the first space?)
				$line = substr($line, strpos($line, ' '));

				// Default
				if (($default = quick_preg_match($regex['table_add_column_default'], $line)) !== false)
				{
					// Remove the default for further work
					$line = str_replace(quick_preg_match($regex['table_add_column_default'], $line, 0), '', $line);
				}

				$line = trim($line);

				// Now we should only have the column type data left in the line
				$column_type = false;

				// Does it match any of our types?
				$matched_length = 0;
				foreach ($column_types as $sql_type => $formatted_type)
				{
					if (preg_match('#' . str_replace(array(' ', '%d'), array('\s', '([0-9]+)'), preg_quote($sql_type, '#')) . '#i', $line))
					{
						// Go with the longest match
						if (utf8_strlen(str_replace('%d', '', $sql_type)) > $matched_length)
						{
							// I win a cake
							if (strpos($sql_type, '%d'))
							{
								$column_type = $formatted_type . quick_preg_match('#' . str_replace('%d', '([0-9]+)', preg_quote($sql_type, '#')) . '#i', $line);
							}
							else
							{
								$column_type = $formatted_type;
							}

							$matched_length = utf8_strlen(str_replace('%d', '', $sql_type));
						}
					}
				}

				// Sorry, can't convert
				if ($column_type === false)
				{
					$column_type = $line;
				}
				else if (strpos($column_type, 'INT') || $column_type == 'TIMESTAMP')
				{
					$default = (int) $default;
				}
				else if (strpos($column_type, 'DECIMAL'))
				{
					$default = (float) $default;
				}

				if ($special)
				{
					$table_data['COLUMNS'][$column_name] = array($column_type, $default, $special);
				}
				else
				{
					$table_data['COLUMNS'][$column_name] = array($column_type, $default);
				}
			}
		}

		// Add to teh data
		$data['table_add'][] = array($table_name, $table_data);
	}

	return $data;
}

/**
* Export the result to a more UMIL style of output
*
* @param array $result
*/
function umil_export($result, $return = false)
{
	$output = '';
	_umil_export($result, $output);

	// Cleanup
	$output = preg_replace('#\t\),\n\t\),#', "\t)),", $output);
	$output = preg_replace('#[\t]+\n#', '', $output);
	$output = preg_replace('#, \),#', '),', $output);
	$output = preg_replace('#,\n\),#', '),', $output);

	if (!$return)
	{
		echo $output;
	}
	else
	{
		return $output;
	}
}

/**
* umil_export helper (based on the lang_lines function from organize_lang)
*/
function _umil_export($lang, &$output, $tabs = 0, $ignore_first = false)
{
	// add the beggining tabs
	if (!$ignore_first)
	{
		for ($i = 0; $i < $tabs; $i++)
		{
			$output .= "\t";
		}
	}

	$max_length = 0;
	foreach ($lang as $name => $value)
	{
		if (!is_numeric($name))
		{
			$max_length = (utf8_strlen($name) > $max_length) ? utf8_strlen($name) : $max_length;
		}
	}
	$max_length += 2;
	$max_tabs = ceil($max_length / 4) + 1;

	foreach($lang as $name => $value)
	{
		if (!is_numeric($name))
		{
			// make sure to add slashes to single quotes!
			$name = addcslashes($name, "'");

			// add the beginning of the lang section and add slashes to single quotes for the name
			$output .= "'" . $name . "'";
		}

		if (is_array($value))
		{
			// If all of the items below have arrays in has_children is true, else false
			$all_have_children = true;
			$some_have_children = false;
			foreach ($value as $val)
			{
				if (!is_array($val))
				{
					$all_have_children = false;
				}
				else
				{
					$some_have_children = true;
				}
			}

			if ($all_have_children)
			{
				if (!is_numeric($name))
				{
					$output .= ' => ';
				}

				$output .= "array(";

				$output .= "\n";

				_umil_export($value, $output, ($tabs + 1));

				$output .= "\n";

				for ($i=0; $i < $tabs; $i++)
				{
					$output .= "\t";
				}
				$output .= "),\n";
			}
			else if ($some_have_children)
			{
				if (!is_numeric($name))
				{
					//$output .= (utf8_strlen($name) + 2) / 4;
					for ($i = 0; $i <= $max_tabs - floor((utf8_strlen($name) + 2) / 4); $i++)
					{
						$output .= "\t";
					}
					$output .= '=> ';
				}

				$output .= "array(";

				_umil_export($value, $output, $tabs, true);

				$output .= "),\n";
			}
			else
			{
				if (!is_numeric($name))
				{
					for ($i = 0; $i <= $max_tabs - floor((utf8_strlen($name) + 2) / 4); $i++)
					{
						$output .= "\t";
					}
					$output .= '=> ';
				}

				$output .= "array(";

				_umil_export($value, $output, 0);

				$output .= "),\n";
			}

			// add the beggining tabs
			if (!$ignore_first)
			{
				for ($i = 0; $i < $tabs; $i++)
				{
					$output .= "\t";
				}
			}
		}
		else
		{
			// add =>, then slashes to single quotes and add to the output
			if (!is_numeric($name))
			{
				$output .= '=> ';
			}

			if (is_numeric($value))
			{
				$output .= (int) $value . ", ";
			}
			else
			{
				$output .= "'" . addcslashes($value, "'") . "', ";
			}
		}
	}
}

/**
* Perform a quick preg_match and return the match
*
* @param string $pattern
* @param string $subject
* @param int $position
* @return string|bool False if the match fails, else the match
*/
function quick_preg_match($pattern, $subject, $position = 1)
{
	$matches = false;
	preg_match($pattern, $subject, $matches);

	return (isset($matches[$position])) ? $matches[$position] : false;
}