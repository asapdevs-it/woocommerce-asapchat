<?php
/**
 * Settings > AsapChat screen.
 *
 * Layout follows docs/mockups/settings.html, behaviour and texts follow the spec (points 4 and 22).
 * Expects $asapchat_view from Asapchat_Admin::build_view().
 *
 * @package asapchat
 */

defined( 'ABSPATH' ) || exit;

$asapchat_state     = $asapchat_view['state'];
$asapchat_connected = Asapchat_Admin::STATE_CONNECTED === $asapchat_state;
$asapchat_error     = Asapchat_Admin::STATE_ERROR === $asapchat_state;
$asapchat_notice    = $asapchat_view['notice'];
$asapchat_post_url  = $asapchat_view['admin_post_url'];
?>
<div class="wrap asapchat-screen">
	<hr class="wp-header-end">
	<div class="asapchat-app">

		<div class="asapchat-head">
			<img class="asapchat-logo" src="<?php echo esc_url( $asapchat_view['logo_url'] ); ?>" alt="AsapChat" width="144" height="30">
			<?php if ( $asapchat_connected ) : ?>
				<div class="asapchat-headact">
					<?php if ( '' !== $asapchat_view['panel_url'] ) : ?>
						<a class="asapchat-headlink" href="<?php echo esc_url( $asapchat_view['panel_url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open AsapChat panel', 'asapchat' ); ?></a>
					<?php endif; ?>
					<form method="post" action="<?php echo esc_url( $asapchat_post_url ); ?>" onsubmit="return window.confirm('<?php echo esc_js( __( 'Disconnect this site from AsapChat? The chat and the search will disappear from your site.', 'asapchat' ) ); ?>');">
						<input type="hidden" name="action" value="asapchat_disconnect">
						<?php wp_nonce_field( 'asapchat_disconnect' ); ?>
						<button type="submit" class="asapchat-headbtn"><?php esc_html_e( 'Disconnect', 'asapchat' ); ?></button>
					</form>
				</div>
			<?php endif; ?>
		</div>

		<div class="asapchat-wrap">

			<?php if ( $asapchat_notice ) : ?>
				<div class="<?php echo $asapchat_connected ? '' : 'asapchat-narrow'; ?>">
					<input type="checkbox" id="asapchat-notice-dismiss" class="asapchat-notice-toggle">
					<div class="asapchat-notice asapchat-notice-<?php echo esc_attr( $asapchat_notice['type'] ); ?>" role="status">
						<p><?php echo esc_html( $asapchat_notice['text'] ); ?></p>
						<label for="asapchat-notice-dismiss" class="asapchat-notice-close">
							<span aria-hidden="true">&times;</span>
							<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice', 'asapchat' ); ?></span>
						</label>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( ! $asapchat_connected ) : ?>
				<div class="asapchat-narrow">

					<?php if ( $asapchat_error ) : ?>
						<div class="asapchat-notice asapchat-notice-error asapchat-notice-static">
							<p><strong><?php esc_html_e( 'The chat and the search are not being added to your site right now.', 'asapchat' ); ?></strong></p>
						</div>
					<?php endif; ?>

					<div class="asapchat-card<?php echo $asapchat_error ? '' : ' asapchat-center'; ?>">

						<?php if ( $asapchat_error ) : ?>
							<h1 class="asapchat-h1"><?php esc_html_e( 'The connection with AsapChat was interrupted', 'asapchat' ); ?></h1>
							<p class="asapchat-lead">
								<?php
								if ( 409 === $asapchat_view['error_code'] ) {
									esc_html_e( 'This connection is assigned to another domain. Connect this site again.', 'asapchat' );
								} elseif ( 410 === $asapchat_view['error_code'] ) {
									esc_html_e( 'This shop was deleted in AsapChat. Connect the site to another shop.', 'asapchat' );
								} else {
									esc_html_e( 'The connection was revoked in the AsapChat panel or the install key expired. Connect the site again to bring the chat back.', 'asapchat' );
								}
								?>
							</p>
						<?php else : ?>
							<h1 class="asapchat-h1"><?php esc_html_e( 'Connect your store to AsapChat', 'asapchat' ); ?></h1>
							<p class="asapchat-lead"><?php esc_html_e( 'AI chat and product search on your site, with no code to paste.', 'asapchat' ); ?></p>
						<?php endif; ?>

						<form class="asapchat-connect" method="post" action="<?php echo esc_url( $asapchat_post_url ); ?>">
							<input type="hidden" name="action" value="asapchat_connect_start">
							<?php wp_nonce_field( 'asapchat_connect' ); ?>
							<button type="submit" class="asapchat-btn asapchat-primary asapchat-wide"<?php disabled( ! $asapchat_view['can_use_button'] ); ?>>
								<?php
								if ( $asapchat_error ) {
									esc_html_e( 'Connect again', 'asapchat' );
								} else {
									esc_html_e( 'Connect to AsapChat', 'asapchat' );
								}
								?>
							</button>
						</form>

						<?php if ( ! $asapchat_view['can_use_button'] ) : ?>
							<p class="asapchat-note"><?php esc_html_e( 'Connecting with the button requires an https address and an admin panel on the same domain as the site. Use an install key.', 'asapchat' ); ?></p>
						<?php endif; ?>

						<?php if ( $asapchat_error ) : ?>
							<p class="asapchat-desc asapchat-kept"><?php esc_html_e( 'Your widget and exclusion settings have been kept.', 'asapchat' ); ?></p>
						<?php else : ?>
							<p class="asapchat-note"><?php esc_html_e( 'No account yet? You will create one in the next step, for free.', 'asapchat' ); ?></p>

							<?php if ( $asapchat_view['can_use_button'] ) : ?>
								<ol class="asapchat-steps">
									<li><?php esc_html_e( 'Click "Connect to AsapChat".', 'asapchat' ); ?></li>
									<li><?php esc_html_e( 'Log in or create an account and go through a short setup wizard.', 'asapchat' ); ?></li>
									<li><?php esc_html_e( 'You come back here automatically. The chat is already on your site.', 'asapchat' ); ?></li>
								</ol>
							<?php endif; ?>

							<p class="asapchat-desc asapchat-consent">
								<?php esc_html_e( 'After you click, you will go to panel.asapchat.io. The plugin connects to AsapChat only from that moment.', 'asapchat' ); ?>
								<a class="asapchat-link" href="<?php echo esc_url( $asapchat_view['privacy_url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Privacy policy', 'asapchat' ); ?></a>
								&middot;
								<a class="asapchat-link" href="<?php echo esc_url( $asapchat_view['terms_url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Terms of service', 'asapchat' ); ?></a>
							</p>
						<?php endif; ?>

						<details class="asapchat-more"<?php echo $asapchat_view['can_use_button'] ? '' : ' open'; ?>>
							<summary><?php esc_html_e( 'I have an install key', 'asapchat' ); ?></summary>
							<form class="asapchat-keyrow" method="post" action="<?php echo esc_url( $asapchat_post_url ); ?>">
								<input type="hidden" name="action" value="asapchat_connect_key">
								<?php wp_nonce_field( 'asapchat_connect_key' ); ?>
								<label class="screen-reader-text" for="asapchat-install-key"><?php esc_html_e( 'Install key', 'asapchat' ); ?></label>
								<input class="asapchat-input asapchat-key" type="text" id="asapchat-install-key" name="asapchat_key" placeholder="ack_..." autocomplete="off" spellcheck="false">
								<button type="submit" class="asapchat-btn"><?php esc_html_e( 'Connect with key', 'asapchat' ); ?></button>
							</form>
							<p class="asapchat-desc"><?php esc_html_e( 'You will find the key in the AsapChat panel: Shop, Plugin, Install key.', 'asapchat' ); ?></p>
						</details>
					</div>
				</div>

			<?php else : ?>
				<?php
				$asapchat_settings = $asapchat_view['settings'];
				$asapchat_look     = $asapchat_view['look'];
				$asapchat_search   = $asapchat_view['search_available'];
				$asapchat_cart_on  = $asapchat_search && ! empty( $asapchat_settings['search_enabled'] );
				$asapchat_excluded = array_map( 'absint', (array) $asapchat_settings['excluded_ids'] );
				$asapchat_color    = preg_match( '/^#[0-9a-fA-F]{6}$/', $asapchat_look['main_color'] ) ? $asapchat_look['main_color'] : '#6543f0';
				?>

				<div class="asapchat-title">
					<h1 class="asapchat-h1"><?php echo esc_html( $asapchat_view['shop_name'] ); ?></h1>
					<?php if ( '' !== $asapchat_view['plan_label'] ) : ?>
						<span class="asapchat-pill asapchat-pill-plan"><?php echo esc_html( $asapchat_view['plan_label'] ); ?></span>
					<?php endif; ?>
					<span class="asapchat-domain"><?php echo esc_html( $asapchat_view['site_host'] ); ?></span>
				</div>

				<form method="post" action="<?php echo esc_url( $asapchat_post_url ); ?>">
					<input type="hidden" name="action" value="asapchat_save">
					<?php wp_nonce_field( 'asapchat_save' ); ?>

					<div class="asapchat-card">
						<h2><?php esc_html_e( 'Status', 'asapchat' ); ?></h2>
						<p class="asapchat-desc"><?php esc_html_e( 'We check whether the widgets actually appeared on your site.', 'asapchat' ); ?></p>
						<div class="asapchat-list">
							<div class="asapchat-item asapchat-stat">
								<div class="asapchat-txt">
									<strong><?php esc_html_e( 'Chat', 'asapchat' ); ?></strong>
									<?php if ( '' !== $asapchat_view['chat_seen'] ) : ?>
										<span class="asapchat-desc">
											<?php
											/* translators: %s: date and time. */
											echo esc_html( sprintf( __( 'Last seen %s', 'asapchat' ), $asapchat_view['chat_seen'] ) );
											?>
										</span>
									<?php else : ?>
										<a class="asapchat-desc asapchat-link" href="<?php echo esc_url( $asapchat_view['home_url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open your store in a new tab to check.', 'asapchat' ); ?></a>
									<?php endif; ?>
								</div>
								<?php if ( '' !== $asapchat_view['chat_seen'] ) : ?>
									<span class="asapchat-pill asapchat-pill-ok"><?php esc_html_e( 'Chat is working', 'asapchat' ); ?></span>
								<?php else : ?>
									<span class="asapchat-pill"><?php esc_html_e( 'Not seen on the site yet', 'asapchat' ); ?></span>
								<?php endif; ?>
							</div>
							<div class="asapchat-item asapchat-stat">
								<div class="asapchat-txt">
									<strong><?php esc_html_e( 'Search', 'asapchat' ); ?></strong>
									<?php if ( '' !== $asapchat_view['search_seen'] ) : ?>
										<span class="asapchat-desc">
											<?php
											/* translators: %s: date and time. */
											echo esc_html( sprintf( _x( 'Last seen %s', 'search', 'asapchat' ), $asapchat_view['search_seen'] ) );
											?>
										</span>
									<?php elseif ( $asapchat_search ) : ?>
										<a class="asapchat-desc asapchat-link" href="<?php echo esc_url( $asapchat_view['home_url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open your store in a new tab to check.', 'asapchat' ); ?></a>
									<?php else : ?>
										<span class="asapchat-desc"><?php esc_html_e( 'Connect your products first.', 'asapchat' ); ?></span>
									<?php endif; ?>
								</div>
								<?php if ( '' !== $asapchat_view['search_seen'] ) : ?>
									<span class="asapchat-pill asapchat-pill-ok"><?php esc_html_e( 'Search is working', 'asapchat' ); ?></span>
								<?php elseif ( $asapchat_search ) : ?>
									<span class="asapchat-pill"><?php echo esc_html_x( 'Not seen on the site yet', 'search', 'asapchat' ); ?></span>
								<?php else : ?>
									<span class="asapchat-pill"><?php esc_html_e( 'Unavailable', 'asapchat' ); ?></span>
								<?php endif; ?>
							</div>
						</div>
					</div>

					<div class="asapchat-card">
						<h2><?php esc_html_e( 'Widgets', 'asapchat' ); ?></h2>
						<p class="asapchat-desc"><?php esc_html_e( 'Turn on or off what should be visible on your site.', 'asapchat' ); ?></p>
						<div class="asapchat-list">
							<div class="asapchat-item">
								<div class="asapchat-txt">
									<strong id="asapchat-chat-label"><?php esc_html_e( 'Chat', 'asapchat' ); ?></strong>
									<span class="asapchat-desc"><?php esc_html_e( 'A conversation window with the AI assistant on your site.', 'asapchat' ); ?></span>
								</div>
								<input type="hidden" name="asapchat_settings[chat_enabled]" value="0">
								<input class="asapchat-toggle" type="checkbox" id="asapchat-chat" name="asapchat_settings[chat_enabled]" value="1" aria-labelledby="asapchat-chat-label"<?php checked( ! empty( $asapchat_settings['chat_enabled'] ) ); ?>>
								<label class="asapchat-sw" for="asapchat-chat"></label>
							</div>

							<div class="asapchat-item<?php echo $asapchat_search ? '' : ' asapchat-off'; ?>">
								<div class="asapchat-txt">
									<strong id="asapchat-search-label"><?php esc_html_e( 'AsapSearch product search', 'asapchat' ); ?></strong>
									<?php if ( $asapchat_search ) : ?>
										<span class="asapchat-desc"><?php esc_html_e( 'Replaces the store search with a fast product search.', 'asapchat' ); ?></span>
									<?php else : ?>
										<span class="asapchat-desc">
											<?php esc_html_e( 'The search will turn on once AsapChat imports your products.', 'asapchat' ); ?>
											<?php if ( '' !== $asapchat_view['connect_url'] ) : ?>
												<a class="asapchat-link" href="<?php echo esc_url( $asapchat_view['connect_url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Connect products', 'asapchat' ); ?></a>
											<?php endif; ?>
										</span>
									<?php endif; ?>
								</div>
								<?php if ( $asapchat_search ) : ?>
									<input type="hidden" name="asapchat_settings[search_enabled]" value="0">
									<input class="asapchat-toggle" type="checkbox" id="asapchat-search" name="asapchat_settings[search_enabled]" value="1" aria-labelledby="asapchat-search-label"<?php checked( ! empty( $asapchat_settings['search_enabled'] ) ); ?>>
								<?php else : ?>
									<?php // An inactive switch keeps the saved value, so the search turns on by itself after the import. ?>
									<input type="hidden" name="asapchat_settings[search_enabled]" value="<?php echo empty( $asapchat_settings['search_enabled'] ) ? '0' : '1'; ?>">
									<input class="asapchat-toggle" type="checkbox" id="asapchat-search" aria-labelledby="asapchat-search-label" disabled>
								<?php endif; ?>
								<label class="asapchat-sw" for="asapchat-search"></label>
							</div>

							<div class="asapchat-item<?php echo $asapchat_cart_on ? '' : ' asapchat-off'; ?>">
								<div class="asapchat-txt">
									<strong id="asapchat-cart-label"><?php esc_html_e( 'Add to cart from search', 'asapchat' ); ?></strong>
									<?php if ( $asapchat_cart_on ) : ?>
										<span class="asapchat-desc"><?php esc_html_e( 'An "Add to cart" button right in the search results.', 'asapchat' ); ?></span>
									<?php else : ?>
										<span class="asapchat-desc"><?php esc_html_e( 'Available together with the search.', 'asapchat' ); ?></span>
									<?php endif; ?>
								</div>
								<?php if ( $asapchat_cart_on ) : ?>
									<input type="hidden" name="asapchat_settings[search_add_to_cart]" value="0">
									<input class="asapchat-toggle" type="checkbox" id="asapchat-cart" name="asapchat_settings[search_add_to_cart]" value="1" aria-labelledby="asapchat-cart-label"<?php checked( ! empty( $asapchat_settings['search_add_to_cart'] ) ); ?>>
								<?php else : ?>
									<input type="hidden" name="asapchat_settings[search_add_to_cart]" value="<?php echo empty( $asapchat_settings['search_add_to_cart'] ) ? '0' : '1'; ?>">
									<input class="asapchat-toggle" type="checkbox" id="asapchat-cart" aria-labelledby="asapchat-cart-label" disabled>
								<?php endif; ?>
								<label class="asapchat-sw" for="asapchat-cart"></label>
							</div>
						</div>
					</div>

					<div class="asapchat-card">
						<h2><?php esc_html_e( 'Look', 'asapchat' ); ?></h2>
						<p class="asapchat-desc"><?php esc_html_e( 'These are the same settings as in the AsapChat panel. A change here is visible there and the other way round.', 'asapchat' ); ?></p>
						<div class="asapchat-grid">
							<div class="asapchat-field">
								<label class="asapchat-label" for="asapchat-bot-name">
									<?php esc_html_e( 'Assistant name', 'asapchat' ); ?>
									<small class="asapchat-count"><?php echo esc_html( mb_strlen( $asapchat_look['bot_name'] ) . ' / 40' ); ?></small>
								</label>
								<input class="asapchat-input" type="text" id="asapchat-bot-name" name="asapchat_look[bot_name]" value="<?php echo esc_attr( $asapchat_look['bot_name'] ); ?>" maxlength="40">
							</div>

							<div class="asapchat-field">
								<label class="asapchat-label" for="asapchat-color-text"><?php esc_html_e( 'Main color', 'asapchat' ); ?></label>
								<div class="asapchat-color">
									<input type="color" value="<?php echo esc_attr( $asapchat_color ); ?>" aria-label="<?php esc_attr_e( 'Main color', 'asapchat' ); ?>">
									<input class="asapchat-input" type="text" id="asapchat-color-text" name="asapchat_look[main_color]" value="<?php echo esc_attr( $asapchat_look['main_color'] ); ?>" maxlength="7" spellcheck="false">
								</div>
							</div>

							<fieldset class="asapchat-field asapchat-full">
								<legend class="asapchat-label"><?php esc_html_e( 'Position on the page', 'asapchat' ); ?></legend>
								<div class="asapchat-seg">
									<input type="radio" id="asapchat-pos-right" name="asapchat_look[position]" value="right-bottom"<?php checked( 'right-bottom', $asapchat_look['position'] ); ?>>
									<label for="asapchat-pos-right"><?php esc_html_e( 'Bottom right', 'asapchat' ); ?></label>
									<input type="radio" id="asapchat-pos-left" name="asapchat_look[position]" value="left-bottom"<?php checked( 'left-bottom', $asapchat_look['position'] ); ?>>
									<label for="asapchat-pos-left"><?php esc_html_e( 'Bottom left', 'asapchat' ); ?></label>
								</div>
							</fieldset>

							<div class="asapchat-field">
								<label class="asapchat-label" for="asapchat-greeting-1">
									<?php esc_html_e( 'Greeting 1', 'asapchat' ); ?>
									<small class="asapchat-count"><?php echo esc_html( mb_strlen( $asapchat_look['greeting_1'] ) . ' / 120' ); ?></small>
								</label>
								<input class="asapchat-input" type="text" id="asapchat-greeting-1" name="asapchat_look[greeting_1]" value="<?php echo esc_attr( $asapchat_look['greeting_1'] ); ?>" maxlength="120">
							</div>

							<div class="asapchat-field">
								<label class="asapchat-label" for="asapchat-greeting-2">
									<?php esc_html_e( 'Greeting 2', 'asapchat' ); ?>
									<small class="asapchat-count"><?php echo esc_html( mb_strlen( $asapchat_look['greeting_2'] ) . ' / 120' ); ?></small>
								</label>
								<input class="asapchat-input" type="text" id="asapchat-greeting-2" name="asapchat_look[greeting_2]" value="<?php echo esc_attr( $asapchat_look['greeting_2'] ); ?>" maxlength="120">
							</div>
						</div>
					</div>

					<div class="asapchat-card">
						<h2><?php esc_html_e( 'Where to show', 'asapchat' ); ?></h2>
						<p class="asapchat-desc"><?php esc_html_e( 'On the selected pages and products we will show neither the chat nor the search.', 'asapchat' ); ?></p>
						<div class="asapchat-grid">
							<div class="asapchat-field asapchat-full">
								<label class="asapchat-label" for="asapchat-delay"><?php esc_html_e( 'Chat delay', 'asapchat' ); ?></label>
								<div class="asapchat-unit">
									<input class="asapchat-input" type="number" id="asapchat-delay" name="asapchat_settings[delay_seconds]" min="0" max="60" step="1" value="<?php echo esc_attr( (string) absint( $asapchat_settings['delay_seconds'] ) ); ?>" aria-describedby="asapchat-delay-desc">
									<span><?php esc_html_e( 'seconds', 'asapchat' ); ?></span>
								</div>
								<p class="asapchat-desc" id="asapchat-delay-desc"><?php esc_html_e( 'How many seconds after entering the page the chat appears. From 0 to 60.', 'asapchat' ); ?></p>
							</div>

							<div class="asapchat-field<?php echo $asapchat_view['woocommerce'] ? '' : ' asapchat-full'; ?>">
								<label class="asapchat-label" for="asapchat-pages"><?php esc_html_e( 'Pages without widgets', 'asapchat' ); ?></label>
								<select class="asapchat-input asapchat-select" id="asapchat-pages" name="asapchat_settings[excluded_page_ids][]" multiple size="6">
									<?php foreach ( $asapchat_view['pages'] as $asapchat_page_id => $asapchat_page_title ) : ?>
										<option value="<?php echo esc_attr( (string) $asapchat_page_id ); ?>"<?php selected( in_array( (int) $asapchat_page_id, $asapchat_excluded, true ) ); ?>><?php echo esc_html( $asapchat_page_title ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>

							<?php if ( $asapchat_view['woocommerce'] ) : ?>
								<div class="asapchat-field">
									<label class="asapchat-label" for="asapchat-products"><?php esc_html_e( 'Products without widgets', 'asapchat' ); ?></label>
									<select class="wc-product-search asapchat-select" id="asapchat-products" name="asapchat_settings[excluded_product_ids][]" multiple data-action="woocommerce_json_search_products" style="width: 100%;">
										<?php foreach ( $asapchat_view['products'] as $asapchat_product_id => $asapchat_product_name ) : ?>
											<option value="<?php echo esc_attr( (string) $asapchat_product_id ); ?>" selected><?php echo esc_html( $asapchat_product_name ); ?></option>
										<?php endforeach; ?>
									</select>
								</div>
							<?php endif; ?>

							<div class="asapchat-field asapchat-full">
								<label class="asapchat-label" for="asapchat-url-parts"><?php esc_html_e( 'Address contains', 'asapchat' ); ?></label>
								<textarea class="asapchat-input" id="asapchat-url-parts" name="asapchat_settings[excluded_url_parts]" rows="3" aria-describedby="asapchat-url-parts-desc"><?php echo esc_textarea( implode( "\n", (array) $asapchat_settings['excluded_url_parts'] ) ); ?></textarea>
								<p class="asapchat-desc" id="asapchat-url-parts-desc"><?php esc_html_e( 'One address fragment per line. The widgets will not appear on any page whose address contains this text.', 'asapchat' ); ?></p>
							</div>
						</div>
					</div>

					<div class="asapchat-card">
						<h2><?php esc_html_e( 'Orders and products source', 'asapchat' ); ?></h2>
						<p class="asapchat-desc"><?php esc_html_e( 'Thanks to this the assistant knows your products and answers questions about order status.', 'asapchat' ); ?></p>
						<div class="asapchat-list">
							<div class="asapchat-item">
								<div class="asapchat-txt"><strong><?php esc_html_e( 'Products', 'asapchat' ); ?></strong></div>
								<?php if ( $asapchat_view['products_ok'] ) : ?>
									<span class="asapchat-pill asapchat-pill-ok"><?php esc_html_e( 'Connected', 'asapchat' ); ?></span>
								<?php else : ?>
									<span class="asapchat-pill"><?php esc_html_e( 'Not connected', 'asapchat' ); ?></span>
								<?php endif; ?>
							</div>
							<div class="asapchat-item">
								<div class="asapchat-txt"><strong><?php esc_html_e( 'Orders', 'asapchat' ); ?></strong></div>
								<?php if ( $asapchat_view['orders_ok'] ) : ?>
									<span class="asapchat-pill asapchat-pill-ok"><?php esc_html_e( 'Connected', 'asapchat' ); ?></span>
								<?php else : ?>
									<span class="asapchat-pill"><?php esc_html_e( 'Not connected', 'asapchat' ); ?></span>
								<?php endif; ?>
							</div>
						</div>

						<?php if ( '' !== $asapchat_view['connect_url'] ) : ?>
							<div class="asapchat-row asapchat-sources-action">
								<a class="asapchat-btn" href="<?php echo esc_url( $asapchat_view['connect_url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Connect in AsapChat', 'asapchat' ); ?></a>
							</div>
						<?php endif; ?>

						<?php if ( $asapchat_view['woocommerce'] ) : ?>
							<p class="asapchat-desc asapchat-sources-note"><?php esc_html_e( 'In the AsapChat panel click "Connect with WooCommerce". You will see the WooCommerce consent screen, you approve it and you are done. You do not copy any keys.', 'asapchat' ); ?></p>
							<details class="asapchat-more">
								<summary><?php esc_html_e( 'How to generate API keys in WooCommerce', 'asapchat' ); ?></summary>
								<ol>
									<li><a class="asapchat-link" href="<?php echo esc_url( $asapchat_view['wc_keys_url'] ); ?>"><?php esc_html_e( 'Go to WooCommerce, Settings, Advanced, REST API.', 'asapchat' ); ?></a></li>
									<li><?php esc_html_e( 'Click "Add key".', 'asapchat' ); ?></li>
									<li><?php esc_html_e( 'Enter a description, e.g. "AsapChat". Permissions: "Read".', 'asapchat' ); ?></li>
									<li><?php esc_html_e( 'Click "Generate API key".', 'asapchat' ); ?></li>
									<li><?php esc_html_e( 'Copy the Consumer key and the Consumer secret. Paste them in the AsapChat panel after clicking "Connect in AsapChat". The secret is visible only once.', 'asapchat' ); ?></li>
								</ol>
							</details>
						<?php else : ?>
							<p class="asapchat-desc asapchat-sources-note"><?php esc_html_e( 'No WooCommerce? The assistant then uses the knowledge base that you fill in the AsapChat panel.', 'asapchat' ); ?></p>
						<?php endif; ?>
					</div>

					<div class="asapchat-save">
						<button type="submit" class="asapchat-btn asapchat-primary"><?php esc_html_e( 'Save changes', 'asapchat' ); ?></button>
						<span class="asapchat-desc"><?php esc_html_e( 'The look is saved on your AsapChat account. The other settings stay in WordPress.', 'asapchat' ); ?></span>
					</div>
				</form>
			<?php endif; ?>

		</div>
	</div>
</div>
