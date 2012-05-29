<table class="widefat meta-repeater" id="record-tracklist">
	<thead>
		<tr>
			<th colspan="4"><h3><span>Tracks</span></h3></th>
			<th class="column-action"><a class="button meta-repeater-add-item">Add Track</a></th>
		</tr>
	</thead>
	
	<tfoot>
	    <tr class="meta-repeater-sort-warning" style="display: none;">
	    	<td colspan="5">
	    		<?php printf( '<span>%1$s <em>%2$s</em></span>',
	    			esc_html__( 'The order has been changed.', 'audiotheme-i18n' ),
	    			esc_html__( 'Save your changes.', 'audiotheme-i18n' )
	    		); ?>
	    	</td>
	    </tr>
	</tfoot>
	
	<tbody class="meta-repeater-items">
		<?php foreach( $tracks as $key => $track ) : ?>
			<tr class="meta-repeater-item">
				<td class="track-number">
					<input type="hidden" name="audiotheme_tracks[<?php echo $key; ?>][post_id]" value="<?php echo $track->ID; ?>" class="clear-on-add">
					<span class="meta-repeater-index"><?php echo $key + 1 . '.'; ?></span>
				</td>
				<td><input type="text" name="audiotheme_tracks[<?php echo $key; ?>][title]" placeholder="Title" value="<?php echo esc_attr( $track->post_title ); ?>" class="widefat clear-on-add"></td>
				<td><input type="text" name="audiotheme_tracks[<?php echo $key; ?>][artist]" placeholder="Artist" value="<?php echo esc_attr( get_post_meta( $track->ID, '_artist', true ) ); ?>" class="widefat"></td>
				<td class="column-track-info">
					<?php
					if ( $track->ID && audiotheme_track_has_download( $track->ID ) ) {
						echo '<span class="has-download remove-on-add">&darr;</span>';
					}
					
					if ( $track->ID && $purchase_url = get_post_meta( $track->ID, '_purchase_url', true ) ) {
						echo '<span class="has-purchase-url remove-on-add">$</span>';
					}
					?>
					&nbsp;
				</td>
				<td class="column-action">
					<?php
					if ( $track->ID ) {
						$args = array( 'post' => $track->ID, 'action' => 'edit' );
						printf( '<a href="%1$s" class="remove-on-add">%2$s</a>',
							esc_url( add_query_arg( $args, admin_url( 'post.php' ) ) ),
							esc_html__( 'Edit', 'audiotheme-i18n' )
						);
					}
					?>
					<a class="meta-repeater-remove-item show-on-add"><img src="<?php echo AUDIOTHEME_URI; ?>/admin/images/delete.png" width="16" height="16" alt="Delete Item" title="Delete Item" class="icon-delete" /></a>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>


<style type="text/css">
.meta-repeater thead th h3 { padding: 0 3px !important; }

.meta-repeater tbody tr { cursor: move; width: 100%; }
.meta-repeater tbody tr:last-child td { border-bottom: none; }
.meta-repeater tbody td { padding: 10px; }
.meta-repeater tbody td.track-number { width: 2em; text-align: right; vertical-align: middle; }

.meta-repeater .column-action { width: 16px; cursor: auto; text-align: right; vertical-align: middle; }
.meta-repeater .column-action a { cursor: pointer; font-family: sans-serif; }
.meta-repeater .column-action .meta-repeater-remove-item { opacity: .2;}
.meta-repeater .column-action .meta-repeater-remove-item:hover { opacity: 1;}

.meta-repeater .column-track-info { font-size: 16px; vertical-align: middle; }
.meta-repeater .column-track-info span { padding: 0 3px }

.meta-repeater .show-on-add { display: none; }

.meta-repeater .ui-sortable-helper { background: #F9F9F9; border-top: 1px solid #DFDFDF; border-bottom: 1px solid #DFDFDF; }
.meta-repeater .ui-sortable-helper td { border-top-width: 0; border-bottom-width: 0;}

.meta-repeater-sort-warning td { color: red; border-top: 1px solid #DFDFDF; border-bottom: none; padding: 10px }


#record-tracklist { margin-bottom: 20px;}
#record-tracklist input:focus { border-color: #DFDFDF; }
</style>

<script type="text/javascript">
jQuery('#record-tracklist').appendTo('#post-body-content');
jQuery(function($) { $('#record-tracklist').metaRepeater(); });
</script>