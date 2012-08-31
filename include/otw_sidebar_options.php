<?php
/** Manage plugin options
  *
  */

$otw_options = get_option( 'otw_plugin_options' );

$message = '';
$massages = array();
$messages[1] = 'Options saved.';


if( isset( $_GET['message'] ) && isset( $messages[ $_GET['message'] ] ) ){
	$message .= $messages[ $_GET['message'] ];
}
?>

<div class="updated"><p>Check out the <a href="http://otwthemes.com/online-documentation-widget-manager-light/?utm_source=wp.org&utm_medium=admin&utm_content=docs&utm_campaign=wml">Online documentation</a> for this plugin<br /><br /> Upgrade to the full version of <a href="http://otwthemes.com/product/sidebar-widget-manager-for-wordpress/?utm_source=wp.org&utm_medium=admin&utm_content=upgrade&utm_campaign=wml">Sidebar and Widget Manager</a> | <a href="http://otwthemes.com/demos/sidebar-widget-manager/?utm_source=wp.org&utm_medium=admin&utm_content=upgrade&utm_campaign=wml">Demo site</a> | <a href="http://www.youtube.com/watch?v=WT9UK1eX4C8">Video overview</a><br /><br />
Follow on <a href="http://twitter.com/OTWthemes">Twitter</a> | <a href="http://www.facebook.com/pages/OTWthemes/250294028325665">Facebook</a> | <a href="http://www.youtube.com/OTWthemes">YouTube</a> | <a href="https://plus.google.com/117222060323479158835/about">Google+</a></p></div>

<?php if ( $message ) : ?>
<div id="message" class="updated"><p><?php echo $message; ?></p></div>
<?php endif; ?>
<div class="wrap">
	<div id="icon-edit" class="icon32"><br/></div>
	<h2>
		<?php _e('Plugin Options') ?>
	</h2>
	<div class="form-wrap" id="poststuff">
		<form method="post" action="" class="validate">
			<input type="hidden" name="otw_wml_action" value="manage_otw_options" />
			<?php wp_original_referer_field(true, 'previous'); wp_nonce_field('otw-sbm-options'); ?>

			<div id="post-body">
				<div id="post-body-content">
					<div class="form-field">
						
						<label for="sbm_activate_appearence" class="selectit"><?php _e( 'Enable widgets management' )?>
						<input type="checkbox" id="sbm_activate_appearence" name="sbm_activate_appearence" value="1" style="width: 15px;" <?php if( isset( $otw_options['activate_appearence'] ) && $otw_options['activate_appearence'] ){ echo ' checked="checked" ';}?> /></label>
						<p><?php _e( 'Control every single widgets visibility on different pages. When widget control is enabled it will add a button called Set Visibility at the bottom of each widgets panel (Appearance -> Widgets).  You can choose where is the widget displayed on or hidden from.' );?></p>
					</div>
					<p class="submit">
						<input type="submit" value="<?php _e( 'Save Options') ?>" name="submit" class="button"/>
					</p>
				</div>
			</div>
		</form>
	</div>
</div>
