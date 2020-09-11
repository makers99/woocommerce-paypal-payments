<?php
/**
 * Helper class to determine how to proceed with an order depending on the 3d secure feedback.
 *
 * @package Inpsyde\PayPalCommerce\Button\Helper
 */

declare(strict_types=1);

namespace Inpsyde\PayPalCommerce\Button\Helper;

use Inpsyde\PayPalCommerce\ApiClient\Entity\CardAuthenticationResult as AuthResult;
use Inpsyde\PayPalCommerce\ApiClient\Entity\Order;

/**
 * Class ThreeDSecure
 */
class ThreeDSecure {


	const NO_DECISION = 0;
	const PROCCEED    = 1;
	const REJECT      = 2;
	const RETRY       = 3;

	/**
	 * Determine, how we proceed with a given order.
	 *
	 * @link https://developer.paypal.com/docs/business/checkout/add-capabilities/3d-secure/#authenticationresult
	 *
	 * @param Order $order The order for which the decission is needed.
	 *
	 * @return int
	 */
	public function proceed_with_order( Order $order ): int {
		if ( ! $order->payment_source() ) {
			return self::NO_DECISION;
		}
		if ( ! $order->payment_source()->card() ) {
			return self::NO_DECISION;
		}
		if ( ! $order->payment_source()->card()->authentication_result() ) {
			return self::NO_DECISION;
		}
		$result = $order->payment_source()->card()->authentication_result();
		if ( $result->liability_shift() === AuthResult::LIABILITY_SHIFT_POSSIBLE ) {
			return self::PROCCEED;
		}

		if ( $result->liability_shift() === AuthResult::LIABILITY_SHIFT_UNKNOWN ) {
			return self::RETRY;
		}
		if ( $result->liability_shift() === AuthResult::LIABILITY_SHIFT_NO ) {
			return $this->no_liability_shift( $result );
		}
		return self::NO_DECISION;
	}

	/**
	 * Determines how to proceed depending on the Liability Shift.
	 *
	 * @param AuthResult $result The AuthResult object based on which we make the decision.
	 *
	 * @return int
	 */
	private function no_liability_shift( AuthResult $result ): int {

		if (
			$result->enrollment_status() === AuthResult::ENROLLMENT_STATUS_BYPASS
			&& ! $result->authentication_result()
		) {
			return self::PROCCEED;
		}
		if (
			$result->enrollment_status() === AuthResult::ENROLLMENT_STATUS_UNAVAILABLE
			&& ! $result->authentication_result()
		) {
			return self::PROCCEED;
		}
		if (
			$result->enrollment_status() === AuthResult::ENROLLMENT_STATUS_NO
			&& ! $result->authentication_result()
		) {
			return self::PROCCEED;
		}

		if ( $result->authentication_result() === AuthResult::AUTHENTICATION_RESULT_REJECTED ) {
			return self::REJECT;
		}

		if ( $result->authentication_result() === AuthResult::AUTHENTICATION_RESULT_NO ) {
			return self::REJECT;
		}

		if ( $result->authentication_result() === AuthResult::AUTHENTICATION_RESULT_UNABLE ) {
			return self::RETRY;
		}

		if ( ! $result->authentication_result() ) {
			return self::RETRY;
		}
		return self::NO_DECISION;
	}
}
