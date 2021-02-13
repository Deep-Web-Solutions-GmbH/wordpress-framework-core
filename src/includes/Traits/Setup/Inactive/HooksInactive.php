<?php

namespace DeepWebSolutions\Framework\Core\Traits\Setup\Inactive;

use DeepWebSolutions\Framework\Core\Interfaces\Traits\Setupable\SetupableInactive;
use DeepWebSolutions\Framework\Utilities\Handlers\HooksHandler;
use DeepWebSolutions\Framework\Utilities\Handlers\Traits\Hooks as HooksUtilities;

defined( 'ABSPATH' ) || exit;

/**
 * Functionality trait for loading hooks of inactive instances.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Traits\Setup\Inactive
 */
trait HooksInactive {
	use HooksUtilities;
	use SetupableInactive {
		setup as setup_hooks_inactive;
	}

	/**
	 * Automagically call the hooks registration method.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   HooksHandler    $hooks_handler      Instance of the hooks handler.
	 */
	public function setup_hooks_inactive( HooksHandler $hooks_handler ): void {
		$this->register_hooks( $hooks_handler );
	}
}