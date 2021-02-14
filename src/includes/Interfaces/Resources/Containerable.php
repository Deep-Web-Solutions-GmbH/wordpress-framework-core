<?php

namespace DeepWebSolutions\Framework\Core\Interfaces\Resources;

use DI\Container;

defined( 'ABSPATH' ) || exit;

/**
 * Implementing classes need to define a logic for retrieving a DI container.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Interfaces\Resources
 */
interface Containerable {
	/**
	 * Gets an instance of the PHP-DI container.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_container(): Container;
}