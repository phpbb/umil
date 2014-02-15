<?php
/**
 *
 * @author Nathan Guse (EXreaction) http://lithiumstudios.org
 * @author David Lewis (Highway of Life) highwayoflife@gmail.com
 * @package umil
 * @copyright (c) 2008 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 * @translated by: Sherlock - http://www.phpbbukraine.net/
 */

/**
 * @ignore
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
	'ACTION'						=> 'Дія',
	'ADVANCED'						=> 'Додатково',
	'AUTH_CACHE_PURGE'				=> 'Очищення кеша прав доступу',

	'CACHE_PURGE'					=> 'Очищення кеша конференції',
	'CONFIGURE'						=> 'Конфігурація',
	'CONFIG_ADD'					=> 'Додавання нової змінної конфігурації: %s',
	'CONFIG_ALREADY_EXISTS'			=> 'Помилка: Перемінна конфігурації %s вже існує.',
	'CONFIG_NOT_EXIST'				=> 'Помилка: Перемінна конфігурації %s не існує.',
	'CONFIG_REMOVE'					=> 'Видалення змінної конфігурації: %s',
	'CONFIG_UPDATE'					=> 'Оновлення змінної конфігурації: %s',

	'DISPLAY_RESULTS'				=> 'Відображати всі результати',
	'DISPLAY_RESULTS_EXPLAIN'		=> 'Виберіть ТАК для відображення всіх дій і результатів при їх виконанні.',

	'ERROR_NOTICE'					=> 'Під час виконання відбулися помилки. Завантажте <a href="%1$s">цей файл</a> зі списком помилок і попросіть автора МОДа про допомогу.<br /><br />Якщо ви маєте проблеми з завантажуванням файлу, ви можете отримати до нього доступ за FTP: %2$s',
	'ERROR_NOTICE_NO_FILE'			=> 'Під час виконання відбулися помилки. Запишіть їх і попросіть автора МОДа про допомогу.',

	'FAIL'							=> 'Відмова',
	'FILE_COULD_NOT_READ'			=> 'Помилка: Файл %s не доступний для читання.',
	'FOUNDERS_ONLY'					=> 'Ви повинні мати права засновника конференції для доступу до даної сторінки.',

	'GROUP_NOT_EXIST'				=> 'Група не існує',

	'IGNORE'						=> 'Пропустити',
	'IMAGESET_CACHE_PURGE'			=> 'Оновити %s набір зображень',
	'INSTALL'						=> 'Встановлення',
	'INSTALL_MOD'					=> 'Встановити %s',
	'INSTALL_MOD_CONFIRM'			=> 'Ви готові до встановлення %s?',

	'MODULE_ADD'					=> 'Додавання %1$s модуля: %2$s',
	'MODULE_ALREADY_EXIST'			=> 'Помилка: модуль вже існує.',
	'MODULE_NOT_EXIST'				=> 'Помилка: модуль не існує.',
	'MODULE_REMOVE'					=> 'Видалення %1$s модуля: %2$s',

	'NONE'							=> 'Ні',
	'NO_TABLE_DATA'					=> 'Помилка: дані таблиці не визначені',

	'PARENT_NOT_EXIST'				=> 'Помилка: вказана батьківська категорія для модуля не існує.',
	'PERMISSIONS_WARNING'			=> 'Додано нові параметри прав доступу. Не забудьте перевірити налаштування прав доступу і переконатися в їх коректності.',
	'PERMISSION_ADD'				=> 'Додано нове право доступу: %s',
	'PERMISSION_ALREADY_EXISTS'		=> 'Помилка: право доступу %s вже існує.',
	'PERMISSION_NOT_EXIST'			=> 'Помилка: право доступу %s не існує.',
	'PERMISSION_REMOVE'				=> 'Видалення права доступу: %s',
	'PERMISSION_ROLE_ADD'			=> 'Додавання нової ролі: %s',
	'PERMISSION_ROLE_UPDATE'		=> 'Оновлення ролі: %s',
	'PERMISSION_ROLE_REMOVE'		=> 'Видалення ролі: %s',
	'PERMISSION_SET_GROUP'			=> 'Встановлення прав доступу для групи %s.',
	'PERMISSION_SET_ROLE'			=> 'Встановлення прав доступу для ролі %s.',
	'PERMISSION_UNSET_GROUP'		=> 'Скидання прав доступу для групи %s.',
	'PERMISSION_UNSET_ROLE'			=> 'Скидання прав доступу для ролі %s.',

	'ROLE_ALREADY_EXISTS'			=> 'Роль вже існує.',
	'ROLE_NOT_EXIST'				=> 'Роль не існує',

	'SUCCESS'						=> 'Успішно',

	'TABLE_ADD'						=> 'Додавання нової таблиці: %s',
	'TABLE_ALREADY_EXISTS'			=> 'Помилка: таблиця %s вже існує.',
	'TABLE_COLUMN_ADD'				=> 'Додавання нового поля %2$s в таблицю %1$s',
	'TABLE_COLUMN_ALREADY_EXISTS'	=> 'Помилка: поле %2$s вже існує в таблиці %1$s.',
	'TABLE_COLUMN_NOT_EXIST'		=> 'Помилка: поле %2$s не існує в таблиці %1$s.',
	'TABLE_COLUMN_REMOVE'			=> 'Видалення поля %2$s з таблиці %1$s',
	'TABLE_COLUMN_UPDATE'			=> 'Оновлення поля %2$s в таблиці %1$s',
	'TABLE_KEY_ADD'					=> 'Додавання індексу %2$s в таблицю %1$s',
	'TABLE_KEY_ALREADY_EXIST'		=> 'Помилка: Індекс %2$s вже існує в таблиці %1$s.',
	'TABLE_KEY_NOT_EXIST'			=> 'Помилка: Індекс %2$s не існує в таблиці %1$s.',
	'TABLE_KEY_REMOVE'				=> 'Видалення індекса %2$s з таблиці %1$s',
	'TABLE_NOT_EXIST'				=> 'Помилка: таблиця %s не існує.',
	'TABLE_REMOVE'					=> 'Видалення таблиці: %s',
	'TABLE_ROW_INSERT_DATA'			=> 'Вставка даних в таблицю %s.',
	'TABLE_ROW_REMOVE_DATA'			=> 'Видалення рядка з таблиці %s',
	'TABLE_ROW_UPDATE_DATA'			=> 'Оновлення рядка в таблиці %s.',
	'TEMPLATE_CACHE_PURGE'			=> 'Оновлення шаблонів %s',
	'THEME_CACHE_PURGE'				=> 'Оновлення теми %s',

	'UNINSTALL'						=> 'Деінсталяція',
	'UNINSTALL_MOD'					=> 'Деінсталяція %s',
	'UNINSTALL_MOD_CONFIRM'			=> 'Ви готові до деінсталяції %s? Всі налаштування і дані для цього МОДа будуть видалені!',
	'UNKNOWN'						=> 'Невідомо',
	'UPDATE_MOD'					=> 'Оновити %s',
	'UPDATE_MOD_CONFIRM'			=> 'Ви готові оновити %s?',
	'UPDATE_UMIL'					=> 'Ця версія UMIL застаріла.<br /><br />Завантажте нову версію UMIL (Unified MOD Install Library): <a href="%1$s">%1$s</a>',

	'VERSIONS'						=> 'Версія МОДа: <strong>%1$s</strong><br />Встановлено: <strong>%2$s</strong>',
	'VERSION_SELECT'				=> 'Вибір версії',
	'VERSION_SELECT_EXPLAIN'		=> 'Не міняйте установки на Пропустити, якщо тільки ви не впевнені у своїх діях, або про це не говорилося прямо.',
));

?>