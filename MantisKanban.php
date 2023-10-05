<?php

class MantisKanbanPlugin extends MantisPlugin {

	function register() {
		$this->name         = 'Mantis Kanban';
		$this->description  = 'Advanced Kanban board view';
		$this->page         = 'config';

		$this->version = '1.2.0';
		
		$this->requires = array(
			'MantisCore'    => '2.25.7',
		);

		$this->author   = 'Joanna Chlasta, Stefan Moises, Joscha Krug, Garret Handel';
		$this->contact  = 'garrethandel@gmail.com';
		$this->url      = 'https://github.com/mantisbt-plugins/MantisKanban';
	}

	function init() {
		spl_autoload_register(array('MantisKanbanPlugin', 'autoload'));

		$t_path = config_get_global('plugin_path') . plugin_get_current() . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR;

		set_include_path(get_include_path() . PATH_SEPARATOR . $t_path);

		// register our custom tables
	#$GLOBALS['g_db_table']['mantis_team_user_table']    = '%db_table_prefix%_team_user%db_table_suffix%';
	#$GLOBALS['g_db_table']['mantis_team_table']         = '%db_table_prefix%_team%db_table_suffix%';

	}

	public static function autoload($className) {
		if (class_exists('ezcBase')) {
			ezcBase::autoload($className);
		}
	}

	function config() {
		global $g_status_enum_string;
		$default_statuses   = MantisEnum::getAssocArrayIndexedByValues( $g_status_enum_string );
		$tag_list = tag_get_candidates_for_bug(0);
		$columns = array();
		$default_columns = plugin_config_get('kanban_custom_columns');
		if (!$default_columns) {
			$i = 0;
			foreach($default_statuses as $num => $status) {
				$columns[$i] = array('status' => array($num), 'tag' => '', 'wip_limit' => 0, 'color' => get_status_color( $num ), 'enabled' => ON);
				$i++;
			}
			plugin_config_set('kanban_custom_columns', $columns);
		}
		return array(
			'kanban_simple_columns' => ON,
			'kanban_custom_columns' => $columns,
		);
	}

	function hooks() {
		$hooks = array(
			'EVENT_MENU_MAIN'           => 'main_menu',
			'EVENT_LAYOUT_RESOURCES'    => 'resources',
			'EVENT_CORE_HEADERS' => 'csp_headers',
		);
		return $hooks;
	}

	/**
	 * Register gravatar url as an img-src for CSP header
	 */
	function csp_headers() {
		// if( config_get( 'show_avatar' ) !== OFF ) {
			http_csp_add( 'img-src', 'https://secure.gravatar.com/' );
		// }
	}

	/**
	 * Adds a new link to the main menu to enter the kanban board
	 * @return array new link for the main menu
	 */
	function main_menu() {
		$t_menu[] = array(
			'title' => plugin_lang_get('main_menu_kanban'),
			'url' => plugin_page('kanban_page'),
			'access_level' => ANYBODY,
			'icon' => 'fa-tasks',
		);
		return $t_menu;
		// return array('<a href="' . plugin_page('kanban_page') . '">' . plugin_lang_get('main_menu_kanban') . '</a>',);
	}

	/**
	 * Create the resource link to load the jQuery library.
	 */
	function resources( $p_event ) {
			return '<script type="text/javascript" src="' . plugin_file( 'kanban.js' ) . '"></script>';
	}

}
