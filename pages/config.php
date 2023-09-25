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

layout_page_header( lang_get( 'plugin_format_title' ) );

layout_page_begin( 'manage_overview_page.php' );

print_manage_menu( 'manage_plugin_page.php' );

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
							<?php
							foreach ($columns as $title => $column) {
							?>
							<tr>
								<th class="category">
									<label for="kanban_simple_columns">
										Issue Status: <?php echo $column['status'][0] ?>
									</label>
								</th>
								<td>
									<input type="checkbox" id="reset" name="kanban_column_<?php echo $column['status'][0] ?>" class="ace" <?php echo( ON == $column['enabled'] ) ? 'checked="checked" ' : ''?>/>
									<span class="lbl">Enable Column</span>
								</td>
								<td>
									<input type="text" name="kanban_column_<?php echo $column['status'][0] ?>_title" value="<?php echo $title ?>"/>
								</td>
							</tr>
							<?php
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
