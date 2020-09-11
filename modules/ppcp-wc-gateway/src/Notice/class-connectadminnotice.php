<?php
/**
 * Registers the admin message to "connect your account" if necessary.
 *
 * @package Inpsyde\PayPalCommerce\WcGateway\Notice
 */

declare(strict_types=1);

namespace Inpsyde\PayPalCommerce\WcGateway\Notice;

use Inpsyde\PayPalCommerce\AdminNotices\Entity\Message;
use Inpsyde\PayPalCommerce\Onboarding\State;
use Inpsyde\PayPalCommerce\WcGateway\Settings\Settings;
use Psr\Container\ContainerInterface;

/**
 * Class ConnectAdminNotice
 */
class ConnectAdminNotice {

	/**
	 * The state.
	 *
	 * @var State
	 */
	private $state;

	/**
	 * The settings.
	 *
	 * @var ContainerInterface
	 */
	private $settings;

	/**
	 * ConnectAdminNotice constructor.
	 *
	 * @param State              $state The state.
	 * @param ContainerInterface $settings The settings.
	 */
	public function __construct( State $state, ContainerInterface $settings ) {
		$this->state    = $state;
		$this->settings = $settings;
	}

	/**
	 * Returns the message.
	 *
	 * @return Message|null
	 */
	public function connect_message() {
		if ( ! $this->should_display() ) {
			return null;
		}

		$message = sprintf(
			/* translators: %1$s the gateway name. */
			__(
				'PayPal Checkout is almost ready. To get started, <a href="%1$s">connect your account</a>.',
				'paypal-payments-for-woocommerce'
			),
			admin_url( 'admin.php?page=wc-settings&tab=checkout&section=ppcp-gateway' )
		);
		return new Message( $message, 'warning' );
	}

	/**
	 * Whether the message should display.
	 *
	 * @return bool
	 */
	protected function should_display(): bool {
		return $this->state->current_state() < State::STATE_PROGRESSIVE;
	}
}
