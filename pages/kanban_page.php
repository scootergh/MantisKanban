<?php

/**
 * @package MantisBT
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2010  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * Kanban plugin forked by Stefan Moises / shoptimax - moises@shoptimax.de
 */

/**
 * MantisBT Core API's
 */
// require_once( 'core.php' );
require_api( 'compress_api.php' );
require_api( 'filter_api.php' );
require_api( 'last_visited_api.php' );
require_api( 'current_user_api.php' );
require_api( 'bug_api.php' );
require_api( 'string_api.php' );
require_api( 'date_api.php' );
require_api( 'icon_api.php' );
/**
 * Plugin includes
 */
// Projax for AJAX buttons etc.
// require_once( 'projax_api.php' );
require_once( 'kanban_ajax_api.php' );
// custom Kanban functions
require_once( 'kanban_api.php' );

// GENERAL SETTINGS
/*
 * status of task, see lang/strings_german.txt ($s_status_enum_string) and
 * MantisKanban/lang/strings_german.txt:
 * Sets your kanban board columns - format is
 *
 * $columns = array(
 *	COLUM_NAME => array('status' => ARRAY_OF_MANTIS_STATI, WORK_IN_PROGRESS_LIMIT),
 *      ...
 * example:
 * $columns = array(
 *	lang_get('header_column_1') => array('status' => array(10), 'wip_limit' => 0),
 * means: Column 1 of your board has the name of the key "header_column_1" (e.g. "New"),
 * shows all tickets with status = 10 and has a "work in progress" limit of 0 (unlimited).
 *
 * Status codes and strings:
 * -------------------------
 * 10: new
 * 20: feedback
 * 30: acknowledged
 * 40: confirmed
 * 50: assigned
 * 80: resolved
 * 90: closed
*/
// $columns = array(
// 	lang_get('header_column_1') => array('status' => array(10), 'tag' => 'TODO', 'wip_limit' => 0, 'color' => get_status_color(10)),
// 	lang_get('header_column_2') => array('status' => array(50), 'tag' => 'WIP', 'wip_limit' => 0, 'color' => get_status_color(50)),
// 	lang_get('header_column_3') => array('status' => array(80), 'tag' => 'Completed', 'wip_limit' => 0, 'color' => get_status_color(80)),
// 	lang_get('header_column_4') => array('status' => array(90), 'tag' => 'Testing', 'wip_limit' => 0, 'color' => get_status_color(90)),
// 	lang_get('header_column_5') => array('status' => array(20), 'tag' => 'Released', 'wip_limit' => 0, 'color' => get_status_color(90)),
// 	//lang_get('header_column_6') => array('status' => array(60,80,90), 'wip_limit' => 0),
// );
function getTagName($tag_id) {
	global $tags;
	foreach ($tags as $tag) {
		if ($tag['id'] == $tag_id) {
			return $tag['name'];
		}
	}
}

$c_columns = plugin_config_get('kanban_custom_columns');
$columns = [];
foreach ($c_columns as $column) {
	if ($column['enabled']) {
		$columns[] = $column;
	}
}
$tags = tag_get_candidates_for_bug(0);

if( ON == plugin_config_get( 'kanban_simple_columns' ) )
{
	$defaults   = MantisEnum::getAssocArrayIndexedByValues( $g_status_enum_string );
	$columns    = null;
	$hideUntilThisStatus = config_get('bug_resolved_status_threshold');
	foreach($defaults as $num=>$status)
	{
		// if( $num < $hideUntilThisStatus )
		// {
			$wip_limit = 12;
			//no limit for "new"
			if(10 == $num)
			{
				$wip_limit = 0;
			}
			$columns[ucfirst(kanban_get_status_text($num))] = array('status' => array($num), 'wip_limit' => $wip_limit, 'color' => get_status_color( $num ) );
		// }
	}
}

// default sorting of the tickets in the columns
// either 'last_updated' or 'priority'
$f_default_sort_by = 'priority';//'last_updated';

// current sorting
$f_sort_by = gpc_get_string( 'sort', $f_default_sort_by );

auth_ensure_user_authenticated();
$t_current_user_id = auth_get_current_user_id();
$t_current_user_name = current_user_get_field( 'username' );

compress_enable();
# don't index the kanban page
html_robots_noindex();

layout_page_header( plugin_lang_get( 'kanban_link' ) );
layout_page_begin( plugin_page('kanban_page') );
print_recently_visited();

$f_page_number		= gpc_get_int( 'page_number', 1 );

$t_per_page = config_get( 'my_view_bug_count' );
$t_bug_count = null;
$t_page_count = null;

$t_boxes = config_get( 'my_view_boxes' );
asort ($t_boxes);
reset ($t_boxes);

$t_project_id = helper_get_current_project();
$t_icon_path = config_get( 'icon_path' );

$t_versions = version_get_all_rows( $t_project_id, VERSION_ALL, true );

?>
<span id="kanbanConfig" data-ajax-url="<?php echo helper_mantis_url('api/rest/issues/') ?>" data-api-token="<?php echo plugin_config_get('kanban_api_token') ?>"></span>
<link rel="stylesheet" type="text/css" href="<?php echo helper_mantis_url( 'plugins/MantisKanban/files/kanban.css' ); ?>"/>
<div id="kanbanPage">
	<div id="kanbanTableSidebar" class="sidebar">
		<ul class="nav nav-list">
			<?php
			foreach ($t_versions as $version) {
				echo '<li><a class="target-version-link" data-target-version="'.$version['version'].'"><span class="menu-text">'.$version['version'].'</span></a></li>';
			}
			?>
		</ul>
	</div>
	<div id="kanbanTable" border="0" cellspacing="0" cellpadding="0">
		<div>
			<div colspan="<?php echo count($columns)-2;?>">
				<?php echo lang_get( 'sort' ); ?>
				<a href="plugin.php?page=MantisKanban/kanban_page&sort=last_updated" <?php if($f_sort_by == 'last_updated') {?> class="bold"<?php }?>>
					<?php echo lang_get( 'sort_date_modified' );?></a> |
				<a href="plugin.php?page=MantisKanban/kanban_page&sort=priority" <?php if($f_sort_by == 'priority') {?> class="bold"<?php }?>><?php echo lang_get( 'sort_priority_link' );?></a>
			</div>
			<div colspan="2" align="right">
				<?php if( helper_get_current_project() == 0 ) { ?>
					<?php echo lang_get( 'projectdisplay' ); ?>
					<a href="plugin.php?page=MantisKanban/kanban_page&pdisplay=combined"><?php echo lang_get( 'project_nogroups' );?></a> |
					<a href="plugin.php?page=MantisKanban/kanban_page&pdisplay=splitted"><?php echo lang_get( 'project_groups' );?></a>
				<?php } ?>
			</div>
		</div>
		<div>
<?php
$t_per_page = -1;

# Improve performance by caching category data in one pass
if( helper_get_current_project() == 0 ) {
		$rows = category_get_all_rows( 0 );
	$t_categories = array();
	foreach( $rows as $t_row ) {
		$t_categories[] = $t_row->category_id;
	}
	category_cache_array_rows( array_unique( $t_categories ) );
}
// get all user set filters
$t_filter = current_user_get_bug_filter();

// if viewing all projects, allow to switch between combined and split view
// (all projects mixed together or separated into rows)
$f_default_pdisplay = "combined";
$pdisplay = gpc_get_string( 'pdisplay', $f_default_pdisplay );
// only one project to display?
if($t_project_id || $pdisplay == "combined") {
	$all_project_ids = array($t_project_id);
}
else {
	$all_project_ids = user_get_accessible_projects( $t_current_user_id );
}
$rowcounts = array();

foreach($all_project_ids as $curr_project_id) {
?>
		<div class="header projectHeader">
			<h1><?php echo project_get_name($curr_project_id); ?></h1>
		</div>
		<div id="info-msg">
			Select a Target Version from the menu to the left.
		</div>
<?php

	foreach($t_versions as $version) {
	?>
		<div id="board-<?php echo $version['version'] ?>" class="board-box" style="display: none;">
			<div class="header boardHeader">
				<h2>Target Version: <?php echo $version['version']; ?></h2>
			</div>
			<div class="kanbanBody grid-<?php echo count($columns) ?>-col">
		<?php
		$column_count = 0;
		foreach($columns as $column){
			$title = $column['title'];
			$c_tag = getTagName($column['tag']);
			if($column['status'][0] > 79)
			{
				$t_per_page = 50;
			}
			else
			{
				$t_per_page = -1;
			}
			// set custom filters, partially using the global filters defined by user
			$filter_array = array(
				// FILTER_PROPERTY_STATUS => $column['status'],
				FILTER_PROPERTY_SORT_FIELD_NAME => $f_sort_by,
				FILTER_PROPERTY_SORT_DIRECTION => 'DESC',
				'_view_type' => FILTER_VIEW_TYPE_ADVANCED,
				// general filters set by user, add more if needed
				FILTER_PROPERTY_CATEGORY_ID => $t_filter['show_category'],
				FILTER_PROPERTY_PRIORITY => $t_filter['show_priority'],
				FILTER_PROPERTY_HANDLER_ID => $t_filter['handler_id'],
				FILTER_PROPERTY_TARGET_VERSION => $version['version'],
				FILTER_PROPERTY_TAG_STRING => $c_tag,
			);
			$rows = filter_get_bug_rows( $f_page_number, $t_per_page, $t_page_count, $t_bug_count,
				$filter_array, $curr_project_id
			);
			$rowcounts[$title] = count($rows);
			if(!$rows) {
				?><div valign="top"
					<?php if( isset( $column['color'] ) ) { ?>style="background-color:<?php echo $column['color'];?>"<?php } ?>
					id="<?php echo $column['status'][0];?>"
					class="kanbanColumn kanbanColumn<?php echo $column['status'][0];?>"
					data-tag-id="<?php echo $column['tag'] ?>">
					<h3 <?php if( isset( $column['color'] ) ) { ?>style="background-color:<?php echo $column['color'];?>"<?php } ?>><?php echo $title;?></h3>
				</div><?php
				continue;
			}

			?><div valign="top"
				<?php if( isset( $column['color'] ) ) { ?>style="background-color:<?php echo $column['color'];?>"<?php } ?>
				id="<?php echo $column['status'][0];?>"
				class="<?php if($column['wip_limit'] > 0 && $rowcounts[$title] > $column['wip_limit']){ echo 'alertOff';}?> kanbanColumn kanbanColumn<?php echo $column['status'][0];?>"
				data-tag-id="<?php echo $column['tag'] ?>">

			<h3 <?php if( isset( $column['color'] ) ) { ?>style="background-color:<?php echo $column['color'];?>"<?php } ?>><?php
			echo $title ?>
			</h3>
	<?php
		if(!empty($rows)){
			$i = 0;
			foreach($rows as $row){
				if($i < 150)
				{
					$t_bug = $row;
					echo '<div data-userid="' . $t_current_user_id . '" data-user-name="'.$t_current_user_name.'"  data-ticketid="' . $t_bug->id . '" data-projectid="' . $t_bug->project_id . '" data-tag-id="'. $column['tag'] .'" class="card '. ($i%2==1 ? 'cardOdd' : 'cardEven') . ' card'. category_full_name( $t_bug->category_id, false ) .'">';
					echo icon_get_status_icon($t_bug->priority);
					echo '	<a href="' . string_get_bug_view_url( $t_bug->id) . '" class="bugLink" target="_blank">' . string_display_line_links( $t_bug->summary ) . '</a>';
					echo '	<a href="' . string_get_bug_view_url( $t_bug->id) . '" class="bugLink right" target="_blank"> #'. $t_bug->id .'</a>';

					$priority = get_enum_element( 'priority', $t_bug->priority );
					/*
					echo '<div class="info">';
								echo '<img src="images/plus.png" alt="'.$bug_desc_title.'" title="'.$bug_desc_title.'" border="0"/>';
								echo bug_get_text_field($t_bug->id, 'description');
								echo string_display_line_links( $t_bug->summary );
					echo project_get_name( $t_bug->project_id );
					if( !bug_is_readonly( $t_bug->id ) && access_has_bug_level( $t_update_bug_threshold, $t_bug->id ) ) {
						echo '<a href="' . string_get_bug_update_url( $t_bug->id ) . '"><img border="0" src="plugins/MantisKanban/files/pencil.png' . '" alt="' . lang_get( 'update_bug_button' ) . '" /></a>';
										echo '<br>' . kanban_ajax_button_bug_change_status( $t_bug->id, $t_bug->project_id, $t_current_user_id );
					}

					// Check for attachments
					$t_attachment_count = 0;
					if(( file_can_view_bug_attachments( $t_bug->id ) ) ) {
						$t_attachment_count = file_bug_attachment_count( $t_bug->id );
					}

					if( 0 < $t_attachment_count ) {
						$t_href = string_get_bug_view_url( $t_bug->id ) . '#attachments';
						$t_href_title = sprintf( lang_get( 'view_attachments_for_issue' ), $t_attachment_count, $t_bug->id );
						$t_alt_text = $t_attachment_count . lang_get( 'word_separator' ) . lang_get( 'attachments' );
						echo "<a class=\"attachments\" href=\"$t_href\" title=\"$t_href_title\"><img src=\"plugins/MantisKanban/files/paper-clip.png\" alt=\"$t_alt_text\" title=\"$t_alt_text\" /></a>";

					}
					if( VS_PRIVATE == $t_bug->view_state ) {
						echo '<img src="' . $t_icon_path . 'protected.gif" width="8" height="15" alt="' . lang_get( 'private' ) . '" />';
					}

					echo '</div>';
								*/
					$t_submitted = date( config_get( 'normal_date_format' ), $t_bug->date_submitted );
					echo '<div class="bugTime">'. $t_submitted . '<br>';
					$t_last_updated = date( config_get( 'normal_date_format' ), $t_bug->last_updated );
					echo $t_last_updated .'</div>';


								// print username instead of status
					if(( ON == config_get( 'show_assigned_names' ) ) && ( $t_bug->handler_id > 0 ) && user_exists($t_bug->handler_id) && ( access_has_project_level( config_get( 'view_handler_threshold' ), $t_bug->project_id ) ) ) {
						$emailHash = md5( strtolower( trim( user_get_email($t_bug->handler_id) ) ) );
						echo '<div class="owner">';
						echo '<div class="img-wrap"><img src="https://secure.gravatar.com/avatar/'. $emailHash .'?s=28&d=monsterid" width="28" height="28" /></div>';

						echo user_get_realname( $t_bug->handler_id );
						echo '</div>';
					}
					echo '</div>';

					$i++;
				}
			}
		}
		?>
		</div>
		<?php
		$column_count++;
		} // end columns loop
		?>
		</div>
	</div>
	<?php
	} // end versions loop
	?>
	</div>
<?php
} // end projects loop
?>
		<!-- <div class="totalNums">
			<?php
			foreach($columns as $title => $column){
			?>
			<div align="center" class="totalSum">
				<h2><?php echo $title; ?></h2>
				<div class="<?php if($column['wip_limit'] > 0 && $rowcounts[$title] > $column['wip_limit']) {echo 'alert';}?>">
				<?php
				echo $rowcounts[$title];
				if($column['wip_limit'] > 0 && $rowcounts[$title] > $column['wip_limit']) {
					echo " (Limit: " . $column['wip_limit'] . ")";
				}
				?>
				</div>
			</div>
			<?php
			}
			?>
		</div> -->
	</div>
</div>

<?php
	layout_page_end();
