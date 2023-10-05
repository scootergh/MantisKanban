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

auth_reauthenticate( );
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

layout_page_header( plugin_lang_get( 'title') . ': ' . plugin_lang_get( 'config' ) );

layout_page_begin( 'manage_overview_page.php' );

print_manage_menu( 'manage_plugin_page.php' );

$t_project_id = helper_get_current_project();

$tags = tag_get_candidates_for_bug(0);

?>

<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>
	<div class="form-container">
		<form action="<?php echo plugin_page( 'config_edit' )?>" method="post">
			<?php echo form_security_field( 'plugin_kanban_config_edit' ) ?>
			<div class="widget-box widget-color-blue2">
				<div class="widget-header widget-header-small">
					<h4 class="widget-title lighter">
						<?php
						print_icon( 'fa-text-width', 'ace-icon' );
						echo "&nbsp;";
						echo plugin_lang_get( 'title') . ': '
							. plugin_lang_get( 'config' );
						?>
					</h4>
				</div>

				<div class="widget-body">
					<div class="widget-main no-padding table-responsive">
						<table class="table table-bordered table-condensed table-striped">
							<tr>
								<th class="category">
									<label for="kanban_simple_columns">
										<?php echo plugin_lang_get( 'columns' ) ?>
									</label>
								</th>
								<td>
									<input type="radio" name="kanban_simple_columns" value="1" <?php echo( ON == plugin_config_get( 'kanban_simple_columns' ) ) ? 'checked="checked" ' : ''?>/>
									<?php echo plugin_lang_get( 'simple_columns' )?>
								</td>
								<td>
									<input type="radio" name="kanban_simple_columns" value="0" <?php echo( OFF == plugin_config_get( 'kanban_simple_columns' ) ) ? 'checked="checked" ' : ''?>/>
									<?php echo plugin_lang_get( 'combined_columns' )?>
								</td>
							</tr>
							<tr>
								<th class="category">
									<label for="kanban_api_token">
										<?php echo plugin_lang_get('api_token') ?>
									</label>
								</th>
								<td>
									<input type="text" name="kanban_api_token" value="<?php echo plugin_config_get('kanban_api_token') ?>" style="width:300px;">
								</td>
							</tr>
						</table>
					</div>

				</div>
				<div class="widget-header widget-header-small">
					<h4 class="widget-title lighter">
						<?php
						print_icon( 'fa-text-width', 'ace-icon' );
						echo "&nbsp;";
						echo plugin_lang_get( 'custom_columns');
						?>
					</h4>
				</div>
				<?php
				$columns = plugin_config_get( 'kanban_custom_columns' );

				?>
				<div class="widget-body">
					<div class="widget-main no-padding table-responsive">
						<table class="table table-bordered table-condensed table-striped">
							<tr>
								<th>Issue Tag</th>
								<th>Column Enabled</th>
								<th>Custom Column Name</th>
							</tr>
							<?php
							$column_count = 0;
							foreach ($columns as $column) {
							?>
							<tr>
								<th class="category">
									<label for="kanban_custom_columns">
										<select name="kanban_column_tag_<?php echo $column_count ?>">
										<?php
										$t_rows = tag_get_candidates_for_bug( 0 );

										$none_selected = true;
										foreach ($t_rows as $t_row) {
											if ($column['tag'] == $t_row['id']) {
												echo '<option value="0">', $t_row['name'], '</option>';
												$none_selected = false;
											}
										}
										if ($none_selected) {
											echo '<option value="0"></option>';
										}
										foreach ( $t_rows as $t_row ) {
											echo '<option value="', $t_row['id'], '" title="', string_attribute( $t_row['description'] );
											echo '"'. ($column['tag'] == $t_row['id'] ? 'selected' : '') .'>', string_attribute( $t_row['name'] ), '</option>';
										}
										?>
										</select>
									</label>
								</th>
								<td>
									<input type="checkbox" name="kanban_column_enabled_<?php echo $column_count ?>" class="ace" <?php echo( ON == $column['enabled'] ) ? 'checked="checked" ' : ''?>/>
									<span class="lbl">Enable Column</span>
								</td>
								<td>
									<input type="text" name="kanban_column_title_<?php echo $column_count ?>" value="<?php echo $column['title'] ?>"/>
								</td>
							</tr>
							<?php
							$column_count++;
							}
							?>
						</table>
					</div>

					<div class="widget-toolbox padding-8 clearfix">
						<button class="btn btn-primary btn-white btn-round">
							<?php echo lang_get( 'change_configuration' )?>
						</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>

<?php
layout_page_end();
