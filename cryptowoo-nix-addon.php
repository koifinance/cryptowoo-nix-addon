<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Plugin Name: CryptoWoo NIX Platform Add-on
 * Plugin URI: https://github.com/NixPlatform/cryptowoo-nix-addon
 * GitHub Plugin URI: NixPlatform/cryptowoo-nix-addon
 * Forked From: NixPlatform/cryptowoo-nix-addon
 * Description: Accept NIX payments in WooCommerce. Requires CryptoWoo main plugin and CryptoWoo HD Wallet Add-on.
 * Version: 1.0
 * Author: Pieter Hamels
 * Author URI: https://github.com/NixPlatform
 * License: GPLv2
 * Text Domain: cryptowoo-nix-addon
 * Domain Path: /lang
 * WC tested up to: 3.2.5
 *
 */

define( 'CWNIX_VER', '1.0' );
define( 'CWNIX_FILE', __FILE__ );
$cw_dir = WP_PLUGIN_DIR . "/cryptowoo";
//$cw_license_path = "$cw_dir/am-license-menu.php";

// Load the plugin update library if it is not already loaded
/*if ( ! class_exists( 'CWNIX_License_Menu' ) && file_exists( $cw_license_path ) ) {
	require_once( $cw_license_path );

	class CWNIX_License_Menu extends CWoo_License_Menu {};

	CWNIX_License_Menu::instance( CWNIX_FILE, 'CryptoWoo NIX Add-on', CWNIX_VER, 'plugin', 'https://www.cryptowoo.com/' );
}*/

/**
 * Plugin activation
 */
function cryptowoo_nix_addon_activate() {

	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	$hd_add_on_file = 'cryptowoo-hd-wallet-addon/cryptowoo-hd-wallet-addon.php';
	if ( ! file_exists( WP_PLUGIN_DIR . '/' . $hd_add_on_file ) || ! file_exists( WP_PLUGIN_DIR . '/cryptowoo/cryptowoo.php' ) ) {

		// If WooCommerce is not installed then show installation notice
		add_action( 'admin_notices', 'cryptowoo_nix_notinstalled_notice' );

		return;
	} elseif ( ! is_plugin_active( $hd_add_on_file ) ) {
		add_action( 'admin_notices', 'cryptowoo_nix_inactive_notice' );

		return;
	}
}

register_activation_hook( __FILE__, 'cryptowoo_nix_addon_activate' );
add_action( 'admin_init', 'cryptowoo_nix_addon_activate' );

/**
 * CryptoWoo inactive notice
 */
function cryptowoo_nix_inactive_notice() {

	?>
    <div class="error">
        <p><?php _e( '<b>CryptoWoo NIX add-on error!</b><br>It seems like the CryptoWoo HD Wallet add-on has been deactivated.<br>
       				Please go to the Plugins menu and make sure that the CryptoWoo HD Wallet add-on is activated.', 'cryptowoo-nix-addon' ); ?></p>
    </div>
	<?php
}


/**
 * CryptoWoo HD Wallet add-on not installed notice
 */
function cryptowoo_nix_notinstalled_notice() {
	$addon_link = '<a href="https://www.cryptowoo.com/shop/cryptowoo-hd-wallet-addon/" target="_blank">CryptoWoo HD Wallet add-on</a>';
	?>
    <div class="error">
        <p><?php printf( __( '<b>CryptoWoo NIX add-on error!</b><br>It seems like the CryptoWoo HD Wallet add-on is not installed.<br>
					The CryptoWoo NIX add-on will only work in combination with the CryptoWoo main plugin and the %s.', 'cryptowoo-nix-addon' ), $addon_link ); ?></p>
    </div>
	<?php
}

function cwnix_hd_enabled() {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	return is_plugin_active( 'cryptowoo-hd-wallet-addon/cryptowoo-hd-wallet-addon.php' ) && is_plugin_active( 'cryptowoo/cryptowoo.php' );
}

if ( cwnix_hd_enabled() ) {
	// Coin symbol and name
	add_filter( 'woocommerce_currencies', 'cwnix_woocommerce_currencies', 10, 1 );
	add_filter( 'cw_get_currency_symbol', 'cwnix_get_currency_symbol', 10, 2 );
	add_filter( 'cw_get_enabled_currencies', 'cwnix_add_coin_identifier', 10, 1 );

	// BIP32 prefixes
	add_filter( 'address_prefixes', 'cwnix_address_prefixes', 10, 1 );

	// Custom block explorer URL
	add_filter( 'cw_link_to_address', 'cwnix_link_to_address', 10, 4 );

	// Options page validations
	add_filter( 'validate_custom_api_genesis', 'cwnix_validate_custom_api_genesis', 10, 2 );
	add_filter( 'validate_custom_api_currency', 'cwnix_validate_custom_api_currency', 10, 2 );
	add_filter( 'cryptowoo_is_ready', 'cwnix_cryptowoo_is_ready', 10, 3 );
	add_filter( 'cw_get_shifty_coins', 'cwnix_cw_get_shifty_coins', 10, 1 );
	add_filter( 'cw_misconfig_notice', 'cwnix_cryptowoo_misconfig_notice', 10, 2 );

	// HD wallet management
	add_filter( 'index_key_ids', 'cwnix_index_key_ids', 10, 1 );
	add_filter( 'mpk_key_ids', 'cwnix_mpk_key_ids', 10, 1 );
	add_filter( 'get_mpk_data_mpk_key', 'cwnix_get_mpk_data_mpk_key', 10, 3 );
	add_filter( 'get_mpk_data_network', 'cwnix_get_mpk_data_network', 10, 3 );
	add_filter( 'cw_discovery_notice', 'cwnix_add_currency_to_array', 10, 1 );

	// Currency params
	add_filter( 'cw_get_currency_params', 'cwnix_get_currency_params', 10, 2 );

	// Order sorting and prioritizing
	add_filter( 'cw_sort_unpaid_addresses', 'cwnix_sort_unpaid_addresses', 10, 2 );
	add_filter( 'cw_prioritize_unpaid_addresses', 'cwnix_prioritize_unpaid_addresses', 10, 2 );
	add_filter( 'cw_filter_batch', 'cwnix_filter_batch', 10, 2 );

	// Add discovery button to currency option
	add_filter( 'redux/options/cryptowoo_payments/field/cryptowoo_nix_mpk', 'hd_wallet_discovery_button' );

	// Exchange rates
	add_filter( 'cw_force_update_exchange_rates', 'cwnix_force_update_exchange_rates', 10, 2 );
	add_filter( 'cw_cron_update_exchange_data', 'cwnix_cron_update_exchange_data', 10, 2 );

	// Catch failing processing API (only if processing_fallback is enabled)
	add_filter( 'cw_get_tx_api_config', 'cwnix_cw_get_tx_api_config', 10, 3 );

	// Insight API URL
	add_filter( 'cw_prepare_insight_api', 'cwnix_override_insight_url', 10, 4 );

	// Add Blockdozer processing
	add_filter( 'cw_update_tx_details', 'cwnix_cw_update_tx_details', 10, 5 );

	// Wallet config
	add_filter( 'wallet_config', 'cwnix_wallet_config', 10, 3 );
	add_filter( 'cw_get_processing_config', 'cwnix_processing_config', 10, 3 );

	// CryptoID API Key
	add_filter("redux/options/cryptowoo_payments/field/cryptoid_api_key", 'redux_remove_field');

	// Options page
	add_action( 'plugins_loaded', 'cwnix_add_fields', 10 );
}

/**
 * NIX font color for aw-cryptocoins
 * see cryptowoo/assets/fonts/aw-cryptocoins/cryptocoins-colors.css
 */
function cwnix_coin_icon_color() { ?>
    <style type="text/css">
        i.cc.NIX:before, i.cc.NIX-alt:before {
            content: "\e92c";
        }

        i.cc.NIX, i.cc.NIX-alt {
            color: #2f60c0;
        }
    </style>
<?php }

add_action( 'wp_head', 'cwnix_coin_icon_color' );

/**
 * Processing API configuration error
 *
 * @param $enabled
 * @param $options
 *
 * @return mixed
 */
function cwnix_cryptowoo_misconfig_notice( $enabled, $options ) {
	$enabled['NIX'] = $options['processing_api_nix'] === 'disabled' && ( (bool) CW_Validate::check_if_unset( 'cryptowoo_nix_mpk', $options ) );

	return $enabled;
}

/**
 * Add currency name
 *
 * @param $currencies
 *
 * @return mixed
 */
function cwnix_woocommerce_currencies( $currencies ) {
	$currencies['NIX'] = __( 'NIX', 'cryptowoo' );

	return $currencies;
}


/**
 * Add currency symbol
 *
 * @param $currency_symbol
 * @param $currency
 *
 * @return string
 */
function cwnix_get_currency_symbol( $currency_symbol, $currency ) {
	return $currency === 'NIX' ? 'NIX' : $currency_symbol;
}


/**
 * Add coin identifier
 *
 * @param $coin_identifiers
 *
 * @return array
 */
function cwnix_add_coin_identifier( $coin_identifiers ) {
	$coin_identifiers['NIX'] = 'nix';

	return $coin_identifiers;
}


/**
 * Add address prefix
 *
 * @param $prefixes
 *
 * @return array
 */
function cwnix_address_prefixes( $prefixes ) {
	$prefixes['NIX']          = '26';
	$prefixes['NIX_MULTISIG'] = '05';

	return $prefixes;
}


/**
 * Add wallet config
 *
 * @param $wallet_config
 * @param $currency
 * @param $options
 *
 * @return array
 */
function cwnix_wallet_config( $wallet_config, $currency, $options ) {
	if ( $currency === 'NIX' ) {
		$wallet_config                       = array(
			'coin_client'   => 'nix',
			'request_coin'  => 'NIX',
			'multiplier'    => (float) $options['multiplier_nix'],
			'safe_address'  => false,
			'decimals'      => 8,
			'mpk_key'       => 'cryptowoo_nix_mpk',
			'fwd_addr_key'  => 'safe_nix_address',
			'threshold_key' => 'forwarding_threshold_nix'
		);
		$wallet_config['hdwallet']           = CW_Validate::check_if_unset( $wallet_config['mpk_key'], $options, false );
		$wallet_config['coin_protocols'][]   = 'nix';
		$wallet_config['forwarding_enabled'] = false;
	}

	return $wallet_config;
}

/**
 * Add InstantSend and "raw" zeroconf settings to processing config
 *
 * @param $pc_conf
 * @param $currency
 * @param $options
 *
 * @return array
 */
function cwnix_processing_config( $pc_conf, $currency, $options ) {
	if ( $currency === 'NIX' ) {
		$pc_conf['instant_send']       = isset( $options['nix_instant_send'] ) ? (bool) $options['nix_instant_send'] : false;
		$pc_conf['instant_send_depth'] = 5; // TODO Maybe add option

		// Maybe accept "raw" zeroconf
		$pc_conf['min_confidence'] = isset( $options['cryptowoo_nix_min_conf'] ) && (int) $options['cryptowoo_nix_min_conf'] === 0 && isset( $options['nix_raw_zeroconf'] ) && (bool) $options['nix_raw_zeroconf'] ? 0 : 1;
	}

	return $pc_conf;
}


/**
 * Override links to payment addresses
 *
 * @param $url
 * @param $address
 * @param $currency
 * @param $options
 *
 * @return string
 */
function cwnix_link_to_address( $url, $address, $currency, $options ) {
	if ( $currency === 'NIX' ) {
		$url = "http://blockchain.nixplatform.io/address/{$address}";
		if ( $options['preferred_block_explorer_nix'] === 'custom' && isset( $options['custom_block_explorer_nix'] ) ) {
			$url = preg_replace( '/{{ADDRESS}}/', $address, $options['custom_block_explorer_nix'] );
			if ( ! wp_http_validate_url( $url ) ) {
				$url = '#';
			}
		}
	}

	return $url;
}

/**
 * Do blockchain.nixplatform.io api processing if chosen
 *
 * @param $batch_data
 * @param $batch_currency
 * @param $orders
 * @param $processing
 * @param $options
 *
 * @return string
 */
function cwnix_cw_update_tx_details( $batch_data, $batch_currency, $orders, $processing, $options ) {
	if ( $batch_currency == "NIX" && $options['processing_api_nix'] == "nixplatform.io" ) {
		$options['custom_api_nix']     = "https://blockchain.nixplatform.io/nix-insight-api/";
		$batch = [];
		foreach ( $orders as $order ) {
			$batch[] = $order->address;
		}
		$batch_data[ $batch_currency ] = CW_Insight::insight_batch_tx_update( "NIX", $batch, $orders, $options );
		usleep( 333333 ); // Max ~3 requests/second TODO remove when we have proper rate limiting

        //$batch_data = CW_Insight::insight_tx_analysis($orders, $batch_data, $options, $chain_height, true);
	} elseif ($batch_currency == "NIX" && $options['processing_api_nix'] == 'cryptoid') {
        // cryptoID.info API: Single address polling
        $prio_orders                 = CW_OrderSorting::prioritize_unpaid_addresses($orders[$batch_currency], 1);
        $batch_data[$batch_currency] = CW_CryptoID::batch_tx_update($batch_currency, $batch, $prio_orders, $options);
        //$force_update_cryptoid = self::maybe_force_order_update('yes', $batch_data[$batch_currency]);
        usleep(333333); // Max ~3 requests/second
    }

	return $batch_data;
}

function cwnix_nix_api_get_block_height($options) {
    $currency = "NIX";

	$bh_transient = sprintf('block-height-%s', $currency);
	if(false !== ($block_height = get_transient($bh_transient))) {
		return (int)$block_height;
	}

	$error = '';

	// Get data
	$url = $options['custom_api_nix'] . "api/getblockcount";

	$result = wp_remote_get($url);

	if (is_wp_error($result)) {

		$error = $result->get_error_message();

		// Action hook for Insight API error
		do_action('cryptowoo_api_error', 'Insight API error: '.$error);

		// Update rate limit transient
		$limit_transient[$currency] = isset($limit_transient[$currency]['count']) ? array('count' => (int)$limit_transient[$currency]['count'] + 1,
		                                                                                  'api' => 'insight') : array('count' => 1,
		                                                                                                              'api' => 'insight');
		// Keep error data until the next full hour (rate limits refresh every full hour). We'll try again after that time.
		set_transient('cryptowoo_limit_rates', $limit_transient, CW_AdminMain::seconds_to_next_hour());

	} else {
		$result = json_decode($result['body']);
	}

	if(isset($result) && is_integer($result)) {
		$block_height = $result;
		set_transient($bh_transient, $block_height, 180); // Cache for 3 minutes
	} else {
		$block_height = 0;
	}

	if ((bool)$error) {
		file_put_contents(CW_LOG_DIR . 'cryptowoo-tx-update.log', date('Y-m-d H:i:s') . " Insight get_block_height {$error}\r\n", FILE_APPEND);
	}
	return (int)$block_height;
}

function cwnix_nix_api_tx_update($address, $order, $options) {
    $currency = "NIX";
	$error = $result = false;

	// Rate limit transient
	$limit_transient = get_transient('cryptowoo_limit_rates');

	// Get data
	$url = $options['custom_api_nix'] . "ext/getaddress/" . $address;

	$result = wp_remote_get($url);

	if (is_wp_error($result)) {
	    $error = $result->get_error_message();

	    // Error "A valid URL was not provided means NIX Platform address did not receive any payment
	    if ($error == "A valid URL was not provided.") {
            $error = false;
            $result = [];
        } else {
		    $error = $error . $url ;

		    // Action hook for API error
		    do_action('cryptowoo_api_error', 'API error: '.$error);

		    // Update rate limit transient
		    if(isset($limit_transient[$currency]['count'])) {
			    $limit_transient[$currency] = array(
				    'count' => (int)$limit_transient[$currency]['count'] + 1,
				    'api' => 'blockchain.nixplatform.io'
			    );
		    } else {
			    $limit_transient[$currency] = array(
				    'count' => 1,
				    'api' => 'blockchain.nixplatform.io'
			    );
		    }
		    // Keep error data until the next full hour (rate limits refresh every full hour). We'll try again after that time.
		    set_transient('cryptowoo_limit_rates', $limit_transient, CW_AdminMain::seconds_to_next_hour());
		    file_put_contents(CW_LOG_DIR . 'cryptowoo-tx-update.log', date('Y-m-d H:i:s') . " Insight full address error {$error}\r\n", FILE_APPEND);
        }
	} else {
		$result = json_decode($result['body']);
	}
	// Delete rate limit transient if the last call was successful
	if (false !== $limit_transient && false === $error) {
		delete_transient('cryptowoo_limit_rates');
	}
	return false !== $error ? $error : $result;
}


/**
 * Override genesis block
 *
 * @param $genesis
 * @param $field_id
 *
 * @return string
 */
function cwnix_validate_custom_api_genesis( $genesis, $field_id ) {
	if ( in_array( $field_id, array( 'custom_api_nix', 'processing_fallback_url_nix' ) ) ) {
		$genesis = 'dd28ad86def767c3cfc34267a950d871fc7462bc57ea4a929fc3596d9b598e41';
	}

	return $genesis;
}


/**
 * Override custom API currency
 *
 * @param $currency
 * @param $field_id
 *
 * @return string
 */
function cwnix_validate_custom_api_currency( $currency, $field_id ) {
	if ( in_array( $field_id, array( 'custom_api_nix', 'processing_fallback_url_nix' ) ) ) {
		$currency = 'NIX';
	}

	return $currency;
}


/**
 * Add currency to cryptowoo_is_ready
 *
 * @param $enabled
 * @param $options
 * @param $changed_values
 *
 * @return array
 */
function cwnix_cryptowoo_is_ready( $enabled, $options, $changed_values ) {
	$enabled['NIX']           = (bool) CW_Validate::check_if_unset( 'cryptowoo_nix_mpk', $options, false );
	$enabled['NIX_transient'] = (bool) CW_Validate::check_if_unset( 'cryptowoo_nix_mpk', $changed_values, false );

	return $enabled;
}


/**
 * Add currency to is_cryptostore check
 *
 * @param $cryptostore
 * @param $woocommerce_currency
 *
 * @return bool
 */
function cwnix_is_cryptostore( $cryptostore, $woocommerce_currency ) {
	return (bool) $cryptostore ?: $woocommerce_currency === 'NIX';
}

add_filter( 'is_cryptostore', 'cwnix_is_cryptostore', 10, 2 );

/**
 * Add currency to Shifty button option field
 *
 * @param $select
 *
 * @return array
 */
function cwnix_cw_get_shifty_coins( $select ) {
	$select['NIX'] = sprintf( __( 'Display only on %s payment pages', 'cryptowoo' ), 'NIX' );

	return $select;
}


/**
 * Add HD index key id for currency
 *
 * @param $index_key_ids
 *
 * @return array
 */
function cwnix_index_key_ids( $index_key_ids ) {
	$index_key_ids['NIX'] = 'cryptowoo_nix_index';

	return $index_key_ids;
}


/**
 * Add HD mpk key id for currency
 *
 * @param $mpk_key_ids
 *
 * @return array
 */
function cwnix_mpk_key_ids( $mpk_key_ids ) {
	$mpk_key_ids['NIX'] = 'cryptowoo_nix_mpk';

	return $mpk_key_ids;
}


/**
 * Override mpk_key
 *
 * @param $mpk_key
 * @param $currency
 * @param $options
 *
 * @return string
 */
function cwnix_get_mpk_data_mpk_key( $mpk_key, $currency, $options ) {
	if ( $currency === 'NIX' ) {
		$mpk_key = "cryptowoo_nix_mpk";
	}

	return $mpk_key;
}


/**
 * Override mpk_data->network
 *
 * @param $mpk_data
 * @param $currency
 * @param $options
 *
 * @return object
 * @throws Exception
 */
function cwnix_get_mpk_data_network( $mpk_data, $currency, $options ) {
	if ( $currency === 'NIX' ) {
		require_once 'Nix.php';
	    require_once 'NIX_NetworkFactory.php';
		$mpk_data->network = BitWasp\Bitcoin\Network\NIX_NetworkFactory::nix();
		$mpk_data->network_config = new \BitWasp\Bitcoin\Key\Deterministic\HdPrefix\NetworkConfig($mpk_data->network, [
			$mpk_data->slip132->p2pkh($mpk_data->bitcoinPrefixes)
		]);
	}

	return $mpk_data;
}

/**
 * Add currency force exchange rate update button
 *
 * @param $data, $options
 *
 * @return array
 */
function cwnix_force_update_exchange_rates( $data, $options ) {
	$nix = CW_ExchangeRates::update_altcoin_fiat_rates( 'NIX', $options );

	// Maybe log exchange rate updates
	if ( (bool) $options[ 'logging' ][ 'rates' ] ) {
		if ( $nix[ 'status' ] === 'not updated' || strpos( $nix[ 'status' ], 'disabled' ) ) {
			$data[ 'nix' ] = strpos( $nix[ 'status' ], 'disabled' ) ? $nix[ 'status' ] : $nix[ 'last_update' ];
		} else {
			$data[ 'nix' ] = $nix;
		}
	}
	return $data;
}

/**
 * Add currency to background exchange rate update
 *
 * @param $data
 * @param $options
 *
 * @return array
 */
function cwnix_cron_update_exchange_data( $data, $options ) {
	$nix = CW_ExchangeRates::update_altcoin_fiat_rates( 'NIX', $options );

	// Maybe log exchange rate updates
	if ( (bool) $options['logging']['rates'] ) {
		if ( $nix['status'] === 'not updated' || strpos( $nix['status'], 'disabled' ) ) {
			$data['nix'] = strpos( $nix['status'], 'disabled' ) ? $nix['status'] : $nix['last_update'];
		} else {
			$data['nix'] = $nix;
		}
	}

	return $data;
}

/**
 * Add currency to currencies array
 *
 * @param $currencies
 *
 * @return array
 */
function cwnix_add_currency_to_array( $currencies ) {
	$currencies[] = 'NIX';

	return $currencies;
}


/**
 * Override currency params in xpub validation
 *
 * @param $currency_params
 * @param $field_id
 *
 * @return object
 */
function cwnix_get_currency_params( $currency_params, $field_id ) {
	if ( strcmp( $field_id, 'cryptowoo_nix_mpk' ) === 0 ) {
		$currency_params                     = new stdClass();
		$currency_params->strlen             = 111;
		$currency_params->mand_mpk_prefix    = 'xpub';   // bip32.org & Electrum prefix
		$currency_params->mand_base58_prefix = '0488b21e'; // Nix
		$currency_params->currency           = 'NIX';
		$currency_params->index_key          = 'cryptowoo_nix_index';
	}

	return $currency_params;
}

/**
 * Add NIX addresses to sort unpaid addresses
 *
 * @param array $top_n
 * @param mixed $address
 *
 * @return array
 */
function cwnix_sort_unpaid_addresses( $top_n, $address ) {
	if ( strcmp( $address->payment_currency, 'NIX' ) === 0 ) {
		$top_n[3]['NIX'][] = $address;
	}

	return $top_n;
}

/**
 * Add NIX addresses to prioritize unpaid addresses
 *
 * @param array $top_n
 * @param mixed $address
 *
 * @return array
 */
function cwnix_prioritize_unpaid_addresses( $top_n, $address ) {
	if ( strcmp( $address->payment_currency, 'NIX' ) === 0 ) {
		$top_n[3][] = $address;
	}

	return $top_n;
}

/**
 * Add NIX addresses to address_batch
 *
 * @param array $address_batch
 * @param mixed $address
 *
 * @return array
 */
function cwnix_filter_batch( $address_batch, $address ) {
	if ( strcmp( $address->payment_currency, 'NIX' ) === 0 ) {
		$address_batch['NIX'][] = $address->address;
	}

	return $address_batch;
}


/**
 * Fallback on failing API
 *
 * @param $api_config
 * @param $currency
 *
 * @return array
 */
function cwnix_cw_get_tx_api_config( $api_config, $currency ) {
	// ToDo: add Blockcypher
	if ( $currency === 'NIX' ) {
		if ( $api_config->tx_update_api === 'nixplatform.io' ) {
			$api_config->tx_update_api   = 'insight';
			$api_config->skip_this_round = false;
		} else {
			$api_config->tx_update_api = 'cryptoid';
			//$api_config->skip_this_round = false;
			$api_config->skip_this_round = (bool) CW_Validate::check_if_unset( 'cryptoid_api_key', $options, false ) ? false : true;
		}
	}

	return $api_config;
}

/**
 * Override Insight API URL if no URL is found in the settings
 *
 * @param $insight
 * @param $endpoint
 * @param $currency
 * @param $options
 *
 * @return mixed
 */
function cwnix_override_insight_url( $insight, $endpoint, $currency, $options ) {
	if ( $currency === 'NIX' && isset( $options['processing_fallback_url_nix'] ) && wp_http_validate_url( $options['processing_fallback_url_nix'] ) ) {
		$fallback_url = $options['processing_fallback_url_nix'];
		$urls         = $endpoint ? CW_Formatting::format_insight_api_url( $fallback_url, $endpoint ) : CW_Formatting::format_insight_api_url( $fallback_url, '' );
		$insight->url = $urls['surl'];
	}

	return $insight;
}

/**
 * Add Redux options
 */
function cwnix_add_fields() {
	$woocommerce_currency = get_option( 'woocommerce_currency' );

	/*
	 * Required confirmations
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'processing-confirmations',
		'id'         => 'cryptowoo_nix_min_conf',
		'type'       => 'spinner',
		'title'      => sprintf( __( '%s Minimum Confirmations', 'cryptowoo' ), 'NIX' ),
		'desc'       => sprintf( __( 'Minimum number of confirmations for <strong>%s</strong> transactions - %s Confirmation Threshold', 'cryptowoo' ), 'NIX', 'NIX' ),
		'default'    => 2,
		'min'        => 1,
		'step'       => 1,
		'max'        => 10,
	) );

	// ToDo: Enable raw zeroconf
	/*
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'processing-confirmations',
		'id'         => 'nix_raw_zeroconf',
		'type'       => 'switch',
		'title'      => __( 'NIX "Raw" Zeroconf', 'cryptowoo' ),
		'subtitle'   => __( 'Accept unconfirmed NIX transactions as soon as they are seen on the network.', 'cryptowoo' ),
		'desc'       => sprintf( __( '%sThis practice is generally not recommended. Only enable this if you know what you are doing!%s', 'cryptowoo' ), '<strong>', '</strong>' ),
		'default'    => false,
		'required'   => array(
			//array('processing_api_nix', '=', 'custom'),
			array( 'cryptowoo_nix_min_conf', '=', 0 )
		),
	) );
	*/


	/*
	 * ToDo: Zeroconf order amount threshold
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'processing-zeroconf',
		'id'         => 'cryptowoo_max_unconfirmed_nix',
		'type'       => 'slider',
		'title'      => sprintf( __( '%s zeroconf threshold (%s)', 'cryptowoo' ), 'NIX', $woocommerce_currency ),
		'desc'       => '',
		'required'   => array( 'cryptowoo_nix_min_conf', '<', 1 ),
		'default'    => 100,
		'min'        => 0,
		'step'       => 10,
		'max'        => 500,
	) );

	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'processing-zeroconf',
		'id'         => 'cryptowoo_nix_zconf_notice',
		'type'       => 'info',
		'style'      => 'info',
		'notice'     => false,
		'required'   => array( 'cryptowoo_nix_min_conf', '>', 0 ),
		'icon'       => 'fa fa-info-circle',
		'title'      => sprintf( __( '%s Zeroconf Threshold Disabled', 'cryptowoo' ), 'NIX' ),
		'desc'       => sprintf( __( 'This option is disabled because you do not accept unconfirmed %s payments.', 'cryptowoo' ), 'NIX' ),
	) );
	 */


	/*
	// Remove 3rd party confidence
	Redux::removeField( 'cryptowoo_payments', 'custom_api_confidence', false );

	/*
	 * Confidence warning
	 * /
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'processing-confidence',
			'id'    => 'nix_confidence_warning',
			'type'  => 'info',
			'title' => __('Be careful!', 'cryptowoo'),
			'style' => 'warning',
			'desc'  => __('Accepting transactions with a low confidence value increases your exposure to double-spend attacks. Only proceed if you don\'t automatically deliver your products and know what you\'re doing.', 'cryptowoo'),
			'required' => array('min_confidence_nix', '<', 95)
	));

	/*
	 * Transaction confidence
	 * /

	Redux::setField( 'cryptowoo_payments', array(
			'section_id'        => 'processing-confidence',
			'id'      => 'min_confidence_nix',
			'type'    => 'switch',
			'title'   => sprintf(__('%s transaction confidence (%s)', 'cryptowoo'), 'NIX', '%'),
			//'desc'    => '',
			'required' => array('cryptowoo_nix_min_conf', '<', 1),

	));


	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'processing-confidence',
		'id'      => 'min_confidence_nix_notice',
		'type'    => 'info',
		'style' => 'info',
		'notice'    => false,
		'required' => array('cryptowoo_nix_min_conf', '>', 0),
		'icon'  => 'fa fa-info-circle',
		'title'   => sprintf(__('%s "Raw" Zeroconf Disabled', 'cryptowoo'), 'NIX'),
		'desc'    => sprintf(__('This option is disabled because you do not accept unconfirmed %s payments.', 'cryptowoo'), 'NIX'),
	));

	// Re-add 3rd party confidence
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'processing-confidence',
		'id'       => 'custom_api_confidence',
		'type'     => 'switch',
		'title'    => __('Third Party Confidence Metrics', 'cryptowoo'),
		'subtitle' => __('Enable this to use the chain.so confidence metrics when accepting zeroconf transactions with your custom Bitcoin, Litecoin, or Dogecoin API.', 'cryptowoo'),
		'default'  => false,
	));
    */

	// Remove blockcypher token field
	Redux::removeField( 'cryptowoo_payments', 'blockcypher_token', false );

	/*
	 * Processing API
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'processing-api',
		'id'                => 'processing_api_nix',
		'type'              => 'select',
		'title'             => sprintf( __( '%s Processing API', 'cryptowoo' ), 'NIX' ),
		'subtitle'          => sprintf( __( 'Choose the API provider you want to use to look up %s payments.', 'cryptowoo' ), 'NIX' ),
		'options'           => array(
			'nixplatform.io' => 'blockchain.nixplatform.io',
			'cryptoid'    => __('cryptoID.info (no fallback)', 'cryptowoo'),
			'custom'     => __( 'Custom (insight)', 'cryptowoo' ),
			'disabled'   => __( 'Disabled', 'cryptowoo' ),
		),
		'desc'              => '',
		'default'           => 'disabled',
		'ajax_save'         => false, // Force page load when this changes
		'validate_callback' => 'redux_validate_processing_api',
		'select2'           => array( 'allowClear' => false ),
	) );

	/*
	 * Processing API custom URL warning
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'processing-api',
		'id'         => 'processing_api_nix_info',
		'type'       => 'info',
		'style'      => 'critical',
		'icon'       => 'el el-warning-sign',
		'required'   => array(
			array( 'processing_api_nix', 'equals', 'custom' ),
			array( 'custom_api_nix', 'equals', '' ),
		),
		'desc'       => sprintf( __( 'Please enter a valid URL in the field below to use a custom %s processing API', 'cryptowoo' ), 'NIX' ),
	) );

Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'processing-api',
		'id'		        => 'cryptoid_api_key',
		'type'              => 'text',
		'title'    			=>  __('cryptoID API Key (required)', 'cryptowoo'),
        'subtitle' 			=> sprintf(__('Use the API token from your %sCryptoID%s account.', 'cryptowoo'), '<strong><a href="https://chainz.cryptoid.info/api.key.dws" title="Request cryptoID API Key" target="_blank">', '</a></strong>'),
		'desc'              => sprintf(__('%sMore info%s', 'cryptowoo'), '<a href="https://chainz.cryptoid.info/api.dws" title="cryptoID API Docs" target="_blank">', '</a>'),
		//'placeholder'       => 'blockchain.nixplatform.io',
		'required' 			=> array(
                    			array('processing_api_nix', 'equals', 'cryptoid')
                			),
		//'validate_callback' => 'redux_validate_custom_api',
		'ajax_save'         => false,
		'msg'               => __( 'Invalid NIX Insight API URL', 'cryptowoo' ),
		//'default'           => '',
		//'text_hint'         => array(
	//		'title'   => 'Please Note:',
		//	'content' => __( 'Make sure the root URL of the API has a trailing slash ( / ).', 'cryptowoo' ),
		//)
	) );
	/*
	 * Custom processing API URL
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'processing-api',
		'id'                => 'custom_api_nix',
		'type'              => 'text',
		'title'             => sprintf( __( '%s Insight API URL', 'cryptowoo' ), 'NIX' ),
		'subtitle'          => sprintf( __( 'Connect to any %sInsight API%s instance.', 'cryptowoo' ), '<a href="https://github.com/bitpay/insight-api/" title="Insight API" target="_blank">', '</a>' ),
		'desc'              => sprintf( __( 'The root URL of the API instance:%sLink to address:%sblockchain.nixplatform.io/address/%sRoot URL: %blockchain.nixplatform.io%s', 'cryptowoo-nix-addon' ), '<p>', '<code>', '</code><br>', '<code>', '</code></p>' ),
		'placeholder'       => 'blockchain.nixplatform.io',
		'required'          => array( 'processing_api_nix', 'equals', 'custom' ),
		'validate_callback' => 'redux_validate_custom_api',
		'ajax_save'         => false,
		'msg'               => __( 'Invalid NIX Insight API URL', 'cryptowoo' ),
		'default'           => '',
		'text_hint'         => array(
			'title'   => 'Please Note:',
			'content' => __( 'Make sure the root URL of the API has a trailing slash ( / ).', 'cryptowoo' ),
		)
	) );



	// Re-add blockcypher token field
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'processing-api',
		'id'                => 'blockcypher_token',
		'type'              => 'text',
		'ajax_save'         => false, // Force page load when this changes
		'desc'              => sprintf( __( '%sMore info%s', 'cryptowoo' ), '<a href="http://dev.blockcypher.com/#rate-limits-and-tokens" title="BlockCypher Docs: Rate limits and tokens" target="_blank">', '</a>' ),
		'title'             => __( 'BlockCypher Token (optional)', 'cryptowoo' ),
		'subtitle'          => sprintf( __( 'Use the API token from your %sBlockCypher%s account.', 'cryptowoo' ), '<strong><a href="https://accounts.blockcypher.com/" title="BlockCypher account nixboard" target="_blank">', '</a></strong>' ),
		'validate_callback' => 'redux_validate_token'
	) );



	// API Resource control information
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'processing-api-resources',
		'id'                => 'processing_fallback_url_nix',
		'type'              => 'text',
		'title'             => sprintf( __( 'Blockdozer NIX API Fallback', 'cryptowoo' ), 'NIX' ),
		'subtitle'          => sprintf( __( 'Fallback to any %sInsight API%s instance in case the Blockdozer API fails. Retry Blockdozer upon beginning of the next hour. Leave empty to disable.', 'cryptowoo' ), '<a href="https://github.com/bitpay/insight-api/" title="Insight API" target="_blank">', '</a>' ),
		'desc'              => sprintf( __( 'The root URL of the API instance:%sLink to address:%sblockchain.nixplatform.io/ext/getaddress/XtuVUju4Baaj7YXShQu4QbLLR7X2aw9Gc8%sRoot URL: %sblockchain.nixplatform.io%s', 'cryptowoo-nix-addon' ), '<p>', '<code>', '</code><br>', '<code>', '</code></p>' ),
		'placeholder'       => 'https://blockchain.nixplatform.io/nix-insight-api/',
		'required'          => array( 'processing_api_nix', 'equals', 'blockcypher' ),
		'validate_callback' => 'redux_validate_custom_api',
		'ajax_save'         => false,
		'msg'               => __( 'Invalid NIX Insight API URL', 'cryptowoo' ),
		'default'           => 'https://blockchain.nixplatform.io/nix-insight-api/',
		'text_hint'         => array(
			'title'   => 'Please Note:',
			'content' => __( 'Make sure the root URL of the API has a trailing slash ( / ).', 'cryptowoo' ),
		)
	) );
	/*
	 * Preferred exchange rate provider
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'rates-exchange',
		'id'                => 'preferred_exchange_nix',
		'type'              => 'select',
		'title'             => 'NIX Exchange (NIX/BTC)',
		'subtitle'          => sprintf( __( 'Choose the exchange you prefer to use to calculate the %sNIX to Bitcoin exchange rate%s', 'cryptowoo' ), '<strong>', '</strong>.' ),
		'desc'              => sprintf( __( 'Cross-calculated via BTC/%s', 'cryptowoo' ), $woocommerce_currency ),
		'options'           => array(
			'idax'      => 'IDAX'
		),
		'default'           => 'idax',
		'ajax_save'         => false, // Force page load when this changes
		'validate_callback' => 'redux_validate_exchange_api',
		'select2'           => array( 'allowClear' => false )
	) );

	/*
	 * Exchange rate multiplier
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'    => 'rates-multiplier',
		'id'            => 'multiplier_nix',
		'type'          => 'slider',
		'title'         => sprintf( __( '%s exchange rate multiplier', 'cryptowoo' ), 'NIX' ),
		'subtitle'      => sprintf( __( 'Extra multiplier to apply when calculating %s prices.', 'cryptowoo' ), 'NIX' ),
		'desc'          => '',
		'default'       => 1,
		'min'           => .01,
		'step'          => .01,
		'max'           => 2,
		'resolution'    => 0.01,
		'validate'      => 'comma_numeric',
		'display_value' => 'text'
	) );

	/*
	 * Preferred blockexplorer
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'rewriting',
		'id'         => 'preferred_block_explorer_nix',
		'type'       => 'select',
		'title'      => sprintf( __( '%s Block Explorer', 'cryptowoo' ), 'NIX' ),
		'subtitle'   => sprintf( __( 'Choose the block explorer you want to use for links to the %s blockchain.', 'cryptowoo' ), 'NIX' ),
		'desc'       => '',
		'options'    => array(
			'autoselect' => __( 'Autoselect by processing API', 'cryptowoo' ),
			'nixplatform.io' => 'blockchain.nixplatform.io',
			'custom'     => __( 'Custom (enter URL below)' ),
		),
		'default'    => 'nixplatform.io',
		'select2'    => array( 'allowClear' => false )
	) );

	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'rewriting',
		'id'         => 'preferred_block_explorer_nix_info',
		'type'       => 'info',
		'style'      => 'critical',
		'icon'       => 'el el-warning-sign',
		'required'   => array(
			array( 'preferred_block_explorer_nix', '=', 'custom' ),
			array( 'custom_block_explorer_nix', '=', '' ),
		),
		'desc'       => sprintf( __( 'Please enter a valid URL in the field below to use a custom %s block explorer', 'cryptowoo' ), 'NIX' ),
	) );
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'rewriting',
		'id'                => 'custom_block_explorer_nix',
		'type'              => 'text',
		'title'             => sprintf( __( 'Custom %s Block Explorer URL', 'cryptowoo' ), 'NIX' ),
		'subtitle'          => __( 'Link to a block explorer of your choice.', 'cryptowoo' ),
		'desc'              => sprintf( __( 'The URL to the page that displays the information for a single address.%sPlease add %s{{ADDRESS}}%s as placeholder for the cryptocurrency address in the URL.%s', 'cryptowoo' ), '<br><strong>', '<code>', '</code>', '</strong>' ),
		'placeholder'       => 'blockchain.nixplatform.io/address/{$address}',
		'required'          => array( 'preferred_block_explorer_nix', '=', 'custom' ),
		'validate_callback' => 'redux_validate_custom_blockexplorer',
		'ajax_save'         => false,
		'msg'               => __( 'Invalid custom block explorer URL', 'cryptowoo' ),
		'default'           => '',
	) );

	/*
	 * Currency Switcher plugin decimals
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'rewriting-switcher',
		'id'         => 'decimals_nix',
		'type'       => 'select',
		'title'      => sprintf( __( '%s amount decimals', 'cryptowoo' ), 'NIX' ),
		'subtitle'   => '',
		'desc'       => __( 'This option overrides the decimals option of the WooCommerce Currency Switcher plugin.', 'cryptowoo' ),
		'required'   => array( 'add_currencies_to_woocs', '=', true ),
		'options'    => array(
			2 => '2',
			4 => '4',
			6 => '6',
			8 => '8'
		),
		'default'    => 4,
		'select2'    => array( 'allowClear' => false )
	) );


	// Remove Bitcoin testnet
	Redux::removeSection( 'cryptowoo_payments', 'wallets-hdwallet-testnet', false );

	/*
	 * HD wallet section start
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'wallets-hdwallet',
		'id'         => 'wallets-hdwallet-nix',
		'type'       => 'section',
		'title'      => __( 'NIX', 'cryptowoo-hd-wallet-addon' ),
		//'required' => array('testmode_enabled','equals','0'),
		'icon'       => 'cc-NIX',
		//'subtitle' => __('Use the field with the correct prefix of your Litecoin MPK. The prefix depends on the wallet client you used to generate the key.', 'cryptowoo-hd-wallet-addon'),
		'indent'     => true,
	) );

	/*
	 * Extended public key
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'wallets-hdwallet',
		'id'                => 'cryptowoo_nix_mpk',
		'type'              => 'text',
		'ajax_save'         => false,
		'username'          => false,
		'title'             => sprintf( __( '%sprefix%s', 'cryptowoo-hd-wallet-addon' ), '<b>NIX "xpub..." ', '</b>' ),
		'desc'              => __( 'NIX HD Wallet Extended Public Key (xpub...)', 'cryptowoo-hd-wallet-addon' ),
		'validate_callback' => 'redux_validate_mpk',
		//'required' => array('cryptowoo_nix_mpk', 'equals', ''),
		'placeholder'       => 'xpub...',
		// xpub format
		'text_hint'         => array(
			'title'   => 'Please Note:',
			'content' => sprintf( __( 'If you enter a used key you will have to run the address discovery process after saving this setting.%sUse a dedicated HD wallet (or at least a dedicated xpub) for your store payments to prevent address reuse.', 'cryptowoo-hd-wallet-addon' ), '<br>' ),
		)
	) );
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'wallets-hdwallet',
		'id'                => 'derivation_path_nix',
		'type'              => 'select',
		'subtitle'          => '',
		'title'             => sprintf( __( '%s Derivation Path', 'cryptowoo-hd-wallet-addon' ), 'NIX' ),
		'desc'              => __( 'Change the derivation path to match the derivation path of your wallet client.', 'cryptowoo-hd-wallet-addon' ),
		'validate_callback' => 'redux_validate_derivation_path',
		'options'           => array(
			'0/' => __( 'm/0/i (e.g. Electrum Standard Wallet)', 'cryptowoo-hd-wallet-addon' ),
			'm'  => __( 'm/i (BIP44 Account)', 'cryptowoo-hd-wallet-addon' ),
		),
		'default'           => '0/',
		'select2'           => array( 'allowClear' => false )
	) );

	/*
	 * HD wallet section end
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'wallets-hdwallet',
		'id'         => 'section-end',
		'type'       => 'section',
		'indent'     => false,
	) );

	// Re-add Bitcoin testnet section
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'wallets-hdwallet',
		'id'         => 'wallets-hdwallet-testnet',
		'type'       => 'section',
		'title'      => __( 'TESTNET', 'cryptowoo-hd-wallet-addon' ),
		//'required' => array('testmode_enabled','equals','0'),
		'icon'       => 'fa fa-flask',
		'desc'       => __( 'Accept BTC testnet coins to addresses created via a "tpub..." extended public key. (testing purposes only!)<br><b>Depending on the position of the first unused address, it could take a while until your changes are saved.</b>', 'cryptowoo-hd-wallet-addon' ),
		'indent'     => true,
	) );

	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'wallets-hdwallet',
		'id'                => 'cryptowoo_btc_test_mpk',
		'type'              => 'text',
		'ajax_save'         => false,
		'username'          => false,
		'desc'              => __( 'Bitcoin TESTNET extended public key (tpub...)', 'cryptowoo-hd-wallet-addon' ),
		'title'             => __( 'Bitcoin TESTNET HD Wallet Extended Public Key', 'cryptowoo-hd-wallet-addon' ),
		'validate_callback' => 'redux_validate_mpk',
		'placeholder'       => 'tpub...',
		'text_hint'         => array(
			'title'   => 'Please Note:',
			'content' => sprintf( __( 'If you enter a used key you will have to run the address discovery process after saving this setting.%sUse a dedicated HD wallet (or at least a dedicated xpub) for your store payments to prevent address reuse.', 'cryptowoo-hd-wallet-addon' ), '<br>' ),
		)
	) );

	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'wallets-hdwallet',
		'id'                => 'derivation_path_btctest',
		'type'              => 'select',
		'subtitle'          => '',
		'title'             => sprintf( __( '%s Derivation Path', 'cryptowoo-hd-wallet-addon' ), 'BTCTEST' ),
		'desc'              => __( 'Change the derivation path to match the derivation path of your wallet client.', 'cryptowoo-hd-wallet-addon' ),
		'validate_callback' => 'redux_validate_derivation_path',
		'options'           => array(
			'0/' => __( 'm/0/i (e.g. Electrum Standard Wallet)', 'cryptowoo-hd-wallet-addon' ),
			'm'  => __( 'm/i (BIP44 Account)', 'cryptowoo-hd-wallet-addon' ),
		),
		'default'           => '0/',
		'select2'           => array( 'allowClear' => false )
	) );

	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'wallets-hdwallet',
		'id'         => 'section-end',
		'type'       => 'section',
		'indent'     => false,
	) );

}

