<?php
# MantisBT - a php based bugtracking system
# Copyright (C) 2002 - 2013  MantisBT Team - mantisbt-dev@lists.sourceforge.net
# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

form_security_validate( 'plugin_kanban_config_edit' );

auth_reauthenticate( );
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

$kanban_simple_columns = gpc_get_int( 'kanban_simple_columns', ON );

if( plugin_config_get( 'kanban_simple_columns' ) != $kanban_simple_columns ) {
	plugin_config_set( 'kanban_simple_columns', $kanban_simple_columns );
}

$columns = plugin_config_get( 'kanban_custom_columns' );
$i = 0;
foreach ($columns as $column) {
	echo "Current: ". $i;
	$columns[$i]['tag'] = gpc_get_string('kanban_column_tag_'.$i);
	$columns[$i]['enabled'] = gpc_get_bool('kanban_column_enabled_'.$i);
	$columns[$i]['title'] = gpc_get_string('kanban_column_title_'.$i);
	$i++;
}

plugin_config_set('kanban_custom_columns', $columns);


$kanban_api_token = gpc_get_string('kanban_api_token');
if (plugin_config_get('kanban_api_token') != $kanban_api_token) {
	plugin_config_set('kanban_api_token');
}

form_security_purge( 'plugin_kanban_config_edit' );
print_successful_redirect( plugin_page( 'config', true ) );
