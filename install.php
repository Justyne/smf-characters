<?php

/**
 * This script prepares the database for all the tables and other database changes that are required.
 *
 * NOTE: This script is meant to run using the <samp><database></database></samp> elements of the package-info.xml file. This is so
 * that admins have the choice to uninstall any database data installed with the mod. Also, since using the <samp><database></samp>
 * elements automatically calls on db_extend('packages'), we will only be calling that if we are running this script standalone.
 */

/**
 * Before attempting to execute, this file attempts to load SSI.php to enable access to the database functions.
 */

// If we have found SSI.php and we are outside of SMF, then we are running standalone.
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
{
	require_once(dirname(__FILE__) . '/SSI.php');
}
elseif (!defined('SMF')) // If we are outside SMF and can't find SSI.php, then throw an error
{
	die('<b>Error:</b> Cannot install - please verify you put this file in the same place as SMF\'s SSI.php.');
}

if (SMF == 'SSI')
{
	db_extend('packages');
}

// We have a lot to do. Make sure as best we can that we have the time to do so.
@set_time_limit(600);

global $modSettings, $smcFunc, $txt, $db_prefix;

// Here we will update the $modSettings variables.
$mod_settings = [];
$new_settings = [
];

foreach ($new_settings as $k => $v)
{
	if (!isset($modSettings[$k]))
	{
		$mod_settings[$k] = $v;
	}
}
// Anything that shouldn't be set by default won't be in the list. Note that the check is made to isset not empty, because empty values are pre-existing off values, which are not purged from the DB.

// Hook references to be added.
$hooks = [];
// Bootstrap
$hooks[] = [
	'hook' => 'integrate_user_info',
	'function' => 'integrate_chars',
	'perm' => true,
	'file' => '$sourcedir/Characters.php',
];

// Now, we move on to adding new tables to the database.
$tables = [];
$tables[] = [
	'table_name' => '{db_prefix}characters',
	'columns' => [
		db_field('id_character', 'int', 0, true, true),
		db_field('id_member', 'mediumint'),
		db_field('character_name', 'varchar', 255),
		db_field('avatar', 'varchar', 255),
		db_field('signature', 'text'),
		db_field('id_theme', 'tinyint'),
		db_field('posts', 'mediumint'),
		db_field('age', 'varchar', 255),
		db_field('date_created', 'int'),
		db_field('last_active', 'int'),
		db_field('is_main', 'tinyint'),
		db_field('main_char_group', 'smallint'),
		db_field('char_groups', 'varchar', 255),
		db_field('char_sheet', 'int'),
	],
	'indexes' => [
		[
			'columns' => ['id_character'],
			'type' => 'primary',
		],
		[
			'columns' => ['id_member'],
			'type' => 'index',
		],
	],
];
$tables[] = [
	'table_name' => '{db_prefix}character_sheet_comments',
	'columns' => [
		db_field('id_comment', 'int', 0, true, true),
		db_field('id_character', 'int'),
		db_field('id_author', 'mediumint'),
		db_field('time_posted', 'int'),
		db_field('sheet_comment', 'text'),
	],
	'indexes' => [
		[
			'columns' => ['id_comment'],
			'type' => 'primary',
		],
		[
			'columns' => ['id_character', 'time_posted'],
			'index',
		],
	],
];
$tables[] = [
	'table_name' => '{db_prefix}character_sheet_versions',
	'columns' => [
		db_field('id_version', 'int', 0, true, true),
		db_field('sheet_text', 'mediumtext'),
		db_field('id_character', 'int'),
		db_field('id_member', 'mediumint'),
		db_field('created_time', 'int'),
		db_field('id_approver', 'mediumint'),
		db_field('approved_time', 'int'),
		db_field('approval_state', 'tinyint'),
	],
	'indexes' => [
		[
			'columns' => ['id_version'],
			'type' => 'primary',
		],
		[
			'columns' => ['id_character', 'id_approver'],
			'type' => 'index',
		],
	],
];
$tables[] = [
	'table_name' => '{db_prefix}character_sheet_templates',
	'columns' => [
		db_field('id_template', 'smallint', 0, true, true),
		db_field('template_name', 'varchar', 100),
		db_field('template', 'text'),
		db_field('position', 'smallint'),
	],
	'indexes' => [
		[
			'columns' => ['id_template'],
			'type' => 'primary',
		]
	],
];
$tables[] = [
	'table_name' => '{db_prefix}character_log_topics',
	'columns' => [
		db_field('id_character', 'int'),
		db_field('id_topic', 'mediumint'),
		db_field('id_msg', 'int'),
		db_field('unwatched', 'tinyint'),
	],
	'indexes' => [
		[
			'columns' => ['id_character', 'id_topic'],
			'type' => 'unique',
		],
		[
			'columns' => ['id_topic'],
			'type' => 'index',
		],
	],
];

// Oh joy, we've now made it to extra rows...
$rows = [];

// Now we can add a new column to an existing table
$columns = [];
$columns[] = [
	'table_name' => '{db_prefix}messages',
	'column_info' => db_field('id_character', 'int'),
	'parameters' => [],
	'if_exists' => 'ignore',
	'error' => 'fatal',
];
$columns[] = [
	'table_name' => '{db_prefix}members',
	'column_info' => db_field('current_character', 'int'),
	'parameters' => [],
	'if_exists' => 'ignore',
	'error' => 'fatal',
];
$columns[] = [
	'table_name' => '{db_prefix}members',
	'column_info' => db_field('immersive_mode', 'tinyint', 3, true, false, 1),
	'parameters' => [],
	'if_exists' => 'ignore',
	'error' => 'fatal',
];
$columns[] = [
	'table_name' => '{db_prefix}log_online',
	'column_info' => db_field('id_character', 'int'),
	'parameters' => [],
	'if_exists' => 'ignore',
	'error' => 'fatal',
];
$columns[] = [
	'table_name' => '{db_prefix}boards',
	'column_info' => db_field('in_character', 'tinyint'),
	'parameters' => [],
	'if_exists' => 'ignore',
	'error' => 'fatal',
];
$columns[] = [
	'table_name' => '{db_prefix}membergroups',
	'column_info' => db_field('is_character', 'tinyint'),
	'parameters' => [],
	'if_exists' => 'ignore',
	'error' => 'fatal',
];
$columns[] = [
	'table_name' => '{db_prefix}membergroups',
	'column_info' => db_field('badge_order', 'smallint'),
	'parameters' => [],
	'if_exists' => 'ignore',
	'error' => 'fatal',
];

// Update mod settings if applicable
updateSettings($mod_settings);

// Create new tables, if any
foreach ($tables as $table)
{
	if (!isset($table['if_exists']))
	{
		$table['if_exists'] = 'ignore';
	}
	if (!isset($table['error']))
	{
		$table['error'] = 'fatal';
	}
	if (!isset($table['parameters']))
	{
		$table['parameters'] = [];
	}

	$smcFunc['db_create_table']($table['table_name'], $table['columns'], $table['indexes'], $table['parameters'], $table['if_exists'], $table['error']);

	// Because of issues with SMF in 2.0 RC5 onwards, users coming from older installs may not have all columns as if_exists => update doesn't appear to work.
	// So, for every column, add it to the columns addition - and let SMF deal with it that way.
	foreach ($table['columns'] as $table_info)
		$columns[] = [
			'table_name' => $table['table_name'],
			'column_info' => $table_info,
			'parameters' => [],
			'if_exists' => 'ignore',
			'error' => 'fatal',
		];

	// Now, before we go any further, we should really check this table is MyISAM if we asked for it to be so. It *should* be since SMF's create_table can't do InnoDB but users changing things seems not uncommon.
	// Alternatively MySQL might do funky things with table defaults these days.
	if (!empty($table['parameters']['force_myisam']))
	{
		// The table should exist. Is it already MyISAM?
		if (preg_match('~^`(.+?)`\.(.+?)$~', $db_prefix, $match) !== 0)
		{
			$request = $smcFunc['db_query']('', '
				SHOW TABLE STATUS
				FROM {raw:database_name}
				LIKE {string:table_name}',
				[
					'database_name' => '`' . strtr($match[1], ['`' => '']) . '`',
					'table_name' => str_replace('_', '\_', $match[2]) . str_replace('{db_prefix}', '', $table['table_name']),
				]
			);
		}
		else
		{
			$request = $smcFunc['db_query']('', '
				SHOW TABLE STATUS
				LIKE {string:table_name}',
				[
					'table_name' => str_replace('_', '\_', $db_prefix) . str_replace('{db_prefix}', '', $table['table_name']),
				]
			);
		}

		if ($request !== false)
		{
			while ($row = $smcFunc['db_fetch_assoc']($request))
			{
				if ((isset($row['Type']) && strtolower($row['Type']) != 'myisam') || (isset($row['Engine']) && strtolower($row['Engine']) != 'myisam'))
				{
					// Not MyISAM.
					$smcFunc['db_query']('', '
						ALTER TABLE ' . $table['table_name'] . ' ENGINE = MyISAM'
					);
				}
			}
			$smcFunc['db_free_result']($request);
		}
	}

	// This table requires one or more fulltext indexes. If for some weird-ass reason others exist, leave them alone, but we need to verify the ones the installer demands.
	if (!empty($table['parameters']['requires_fulltext']))
	{
		$indexes_to_build = $table['parameters']['requires_fulltext'];
		$request = $smcFunc['db_query']('', '
			SHOW INDEX
			FROM ' . $table['table_name']
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			if (in_array($row['Column_name'], $indexes_to_build) && ((isset($row['Index_type']) && $row['Index_type'] == 'FULLTEXT') || (isset($row['Comment']) && $row['Comment'] == 'FULLTEXT')))
			{
				$indexes_to_build = array_diff($indexes_to_build, [$row['Column_name']]);
			}
		}
		$smcFunc['db_free_result']($request);

		foreach ($indexes_to_build as $index)
		{
			$smcFunc['db_query']('', '
				ALTER TABLE ' . $table['table_name'] . '
				DROP INDEX {raw:index}',
				[
					'db_error_skip' => true,
					'index' => $index,
				]
			);

			$smcFunc['db_query']('', '
				ALTER TABLE ' . $table['table_name'] . '
				ADD FULLTEXT {raw:index} ({raw:index})',
				[
					'index' => $index,
				]
			);
		}
	}
}

// Create new rows, if any
foreach ($rows as $row)
	$smcFunc['db_insert']($row['method'], $row['table_name'], $row['columns'], $row['data'], $row['keys']);

// Create new columns, if any
foreach ($columns as $column)
	$smcFunc['db_add_column']($column['table_name'], $column['column_info'], $column['parameters'], $column['if_exists'], $column['error']);

// Add integration hooks, if any
foreach ($hooks as $hook)
{
	add_integration_function($hook['hook'], $hook['function'], $hook['perm'], !empty($hook['file']) ? $hook['file'] : '');
}

// Create characters if an account doesn't have characters
$insert_rows = [];
$result = $smcFunc['db_query']('', '
	SELECT mem.id_member, mem.real_name, COUNT(id_character) AS count
	FROM {db_prefix}members AS mem
	LEFT JOIN {db_prefix}characters AS chars ON (mem.id_member = chars.id_member)
	GROUP BY mem.id_member HAVING count = 0');
while ($row = $smcFunc['db_fetch_assoc']($result)) {
	$insert_rows[] = [$row['id_member'], $row['real_name'], '', '', 0, 0, '', time(), 0, 1, 0, ''];
}
$smcFunc['db_free_result']($result);

if (!empty($insert_rows)) {
	foreach ($insert_rows as $new_row) {
		$smcFunc['db_insert'](
			'insert',
			'{db_prefix}characters',
			[
				'id_member' => 'int', 'character_name' => 'string', 'avatar' => 'string', 'signature' => 'string', 
				'id_theme' => 'int', 'posts' => 'int', 'age' => 'string', 'date_created' => 'int', 'last_active' => 'int',
				'is_main' => 'int', 'main_char_group' => 'int', 'char_groups' => 'string',
			],
			$new_row,
			[]
		);
		$character_id = $smcFunc['db_insert_id']('{db_prefix}characters');
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}members
			SET current_character = {int:character_id}
			WHERE id_member = {int:id_member}',
			[
				'character_id' => $character_id,
				'id_member' => $new_row[0],
			]
		);
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}log_online
			SET id_character = {int:character_id}
			WHERE id_member = {int:id_member}',
			[
				'character_id' => $character_id,
				'id_member' => $new_row[0],
			]
		);
	}
}

// Are we done?
if (SMF == 'SSI')
{
	echo 'Database changes are complete!';
}

function db_field($name, $type, $size = 0, $unsigned = true, $auto = false, $default = 0)
{
	$fields = [
		'varchar' => [
			'auto' => false,
			'type' => 'varchar',
			'size' => $size == 0 ? 50 : $size,
			'null' => false,
		],
		'text' => [
			'auto' => false,
			'type' => 'text',
			'null' => false,
		],
		'mediumtext' => [
			'auto' => false,
			'type' => 'mediumtext',
			'null' => false,
		],
		'tinyint' => [
			'auto' => $auto,
			'type' => 'tinyint',
			'default' => $default,
			'size' => empty($unsigned) ? 4 : 3,
			'unsigned' => $unsigned,
			'null' => false,
		],
		'smallint' => [
			'auto' => $auto,
			'type' => 'smallint',
			'default' => $default,
			'size' => empty($unsigned) ? 6 : 5,
			'unsigned' => $unsigned,
			'null' => false,
		],
		'mediumint' => [
			'auto' => $auto,
			'type' => 'mediumint',
			'default' => $default,
			'size' => 8,
			'unsigned' => $unsigned,
			'null' => false,
		],
		'int' => [
			'auto' => $auto,
			'type' => 'int',
			'default' => $default,
			'size' => empty($unsigned) ? 11 : 10,
			'unsigned' => $unsigned,
			'null' => false,
		],
		'bigint' => [
			'auto' => $auto,
			'type' => 'bigint',
			'default' => $default,
			'size' => 21,
			'unsigned' => $unsigned,
			'null' => false,
		],
	];

	$field = $fields[$type];
	$field['name'] = $name;

	return $field;
}
?>