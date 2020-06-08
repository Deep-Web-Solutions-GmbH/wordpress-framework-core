<?php

namespace DeepWebSolutions\Framework\Core\Abstracts;

use DeepWebSolutions\Framework\Core\Exceptions\InexistentProperty;
use DeepWebSolutions\Framework\Core\Exceptions\ReadOnly;
use DeepWebSolutions\Framework\Utilities\Loader;
use Psr\Log\LoggerInterface;

defined( 'ABSPATH' ) || exit;

/**
 * A template for encapsulating some of the most often required abilities of a class.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.de>
 * @package DeepWebSolutions\Framework\Core\Abstracts
 */
abstract class Root {
	// region FIELDS AND CONSTANTS

	/**
	 * Instance of the hooks and shortcodes loader.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  protected
	 * @var     Loader
	 */
	protected Loader $loader;

	/**
	 * Instance of the PSR-3-compatible logger used throughout the plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  protected
	 * @var     LoggerInterface
	 */
	protected LoggerInterface $logger;

	/**
	 * Maintains a list of all IDs of root class instances.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  private
	 * @var     array
	 */
	private static array $root_id = array();

	/**
	 * Maintains a list of all public names of root class instances.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  private
	 * @var     array
	 */
	private static array $root_public_name = array();

	// endregion

	// region MAGIC METHODS

	/**
	 * Root constructor.
	 *
	 * @param   Loader          $loader     Instance of the hooks and shortcodes loader.
	 * @param   LoggerInterface $logger     Instance of the PSR-3-compatible logger used throughout out plugin.
	 * @param   string|false    $root_id    The unique ID of the class instance. Must be persistent across requests.
	 * @param   string|false    $root_name  The 'nice_name' of the class instance. Must be persistent across requests. Mustn't be unique.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function __construct( Loader $loader, LoggerInterface $logger, $root_id = false, $root_name = false ) {
		$this->logger = $logger;
		$this->loader = $loader;

        self::$root_id[ static::class ]          = $root_id ?: hash( 'sha512', static::class ); // phpcs:ignore
        self::$root_public_name[ static::class ] = $root_name ?: static::class; // phpcs:ignore
	}

	/**
	 * Used for easily accessing global variables and the values of defined getters.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $name   Name of the property that should be retrieved.
	 *
	 * @return  mixed
	 */
	public function __get( string $name ) {
		if ( method_exists( $this, ( $function = "get_{$name}" ) ) || method_exists( $this, ( $function = 'get' . ucfirst( $name ) ) ) ) { // phpcs:ignore
			return $this->{$function}();
		}

		if ( method_exists( $this, ( $function = "is_{$name}" ) ) || method_exists( $this, ( $function = 'is' . ucfirst( $name ) ) ) ) { // phpcs:ignore
			return $this->{$function}();
		}

		if ( isset( $GLOBALS[ $name ] ) ) {
			return $GLOBALS[ $name ];
		}

		return false;
	}

	/**
	 * Used for writing data to global variables and to existent properties that have a setter defined.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $name   The name of the property that should be reassigned.
	 * @param   mixed   $value  The value that should be assigned to the property.
	 *
	 * @throws  ReadOnly            Thrown if there is a getter for the property, but no setter.
	 * @throws  InexistentProperty  Thrown if there are no getters and no setter for the property, and a global variable also doesn't exist already.
	 *
	 * @return  mixed
	 */
	public function __set( string $name, $value ) {
		$function = "set_{$name}";
		if ( method_exists( $this, $function ) ) {
			return $this->{$function}( $value );
		}

		$function = 'set' . ucfirst( $name );
		if ( method_exists( $this, $function ) ) {
			return $this->{$function}( $value );
		}

		if ( method_exists( $this, "get_{$name}" ) || method_exists( $this, 'get' . ucfirst( $name ) )
			|| method_exists( $this, "is_{$name}" ) || method_exists( $this, 'is' . ucfirst( $name ) ) ) {
			throw new ReadOnly( sprintf( 'Property %s is ready-only', $name ) );
		}

		if ( isset( $GLOBALS[ $name ] ) ) {
			$GLOBALS[ $name ] = $value; // phpcs:ignore
			return true;
		}

		throw new InexistentProperty( sprintf( 'Inexistent property: %s', $name ) );
	}

	/**
	 * Used for checking whether a global variable or a getter for a given property exists.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $name   The name of the property that existence is being checked.
	 *
	 * @return  bool
	 */
	public function __isset( string $name ) : bool {
		if ( method_exists( $this, ( $function = "get_{$name}" ) ) || method_exists( $this, ( $function = 'get' . ucfirst($name) ) ) ) { // phpcs:ignore
			return true;
		}

		if ( method_exists( $this, ( $function = "is_{$name}" ) ) || method_exists( $this, ( $function = 'is' . ucfirst($name) ) ) ) { // phpcs:ignore
			return true;
		}

		return isset( $GLOBALS[ $name ] );
	}

	// endregion

	// region GETTERS

	/**
	 * Gets the ID of the current class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string|false    The ID of the current class or false if not initialized yet.
	 */
	final public static function get_root_id() {
		return self::$root_id[ static::class ] ?? false;
	}

	/**
	 * Gets the public name of the current class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string|false    The public name of the current class or false if not initialized yet.
	 */
	final protected static function get_root_public_name() {
		return self::$root_public_name[ static::class ] ?? false;
	}

	// endregion

	// region HELPERS

	/**
	 * Computes the short name of the class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	final public static function get_class_name() : string {
		return ( new \ReflectionClass( static::class ) )->getShortName();
	}

	/**
	 * Computes the full name of the class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	final public static function get_full_class_name() : string {
		return '\\' . ltrim( ( new \ReflectionClass( static::class ) )->getName(), '\\' );
	}

	/**
	 * Computes the name of the file that the class is written in.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	final public static function get_file_name() : string {
		return ( new \ReflectionClass( static::class ) )->getFileName();
	}

	/**
	 * Returns the path to the current folder of the class which inherits this class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   bool    $keep_file_name     If true, then returns the path including the end filename.
	 *
	 * @return  string
	 */
	final public static function get_base_path( bool $keep_file_name = false ) {
		$file_name = static::get_file_name();

		return $keep_file_name
			? trailingslashit( $file_name )
			: trailingslashit( plugin_dir_path( $file_name ) );
	}

	/**
	 * Returns the relative URL to the current folder of the class which inherits this class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   bool    $keep_file_name     If true, then returns the URL including the end filename.
	 *
	 * @return  string
	 */
	final public static function get_base_relative_url( bool $keep_file_name = false ) {
		$file_name = static::get_file_name();

		$relative_url = $keep_file_name
			? str_replace( ABSPATH, '', trailingslashit( $file_name ) )
			: trailingslashit( plugin_dir_url( $file_name ) );

		// Fix for operating systems where the directory separator is not a forward slash.
		return str_replace( DIRECTORY_SEPARATOR, '/', $relative_url );
	}

	/**
	 * Returns the path to a custom file or directory prepended by the path
	 * to the calling class' path.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $path       The path to append to the current file's base path.
	 *
	 * @return  string
	 */
	final public static function get_custom_base_path( $path ) {
		return trailingslashit( self::get_base_path() . $path );
	}

	/**
	 * Returns the relative URL to a custom file or directory prepended by the path
	 * to the calling class' path.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $path       The path to append to the current file's base path.
	 *
	 * @return  string
	 */
	final public static function get_custom_base_relative_url( $path ) {
		return trailingslashit( self::get_base_relative_url() . $path );
	}

	/**
	 * Returns the path to the assets folder of the current class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	final public static function get_assets_base_path() {
		return self::get_custom_base_path( 'assets' );
	}

	/**
	 * Returns the relative URL to the assets folder of the current class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	final public static function get_assets_base_relative_url() {
		return self::get_custom_base_relative_url( 'assets' );
	}

	/**
	 * Returns the path to the templates folder of the current class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	final public static function get_templates_base_path() {
		return self::get_custom_base_path( 'templates' );
	}

	/**
	 * Returns the relative URL to the templates folder of the current class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	final public static function get_templates_base_relative_url() {
		return self::get_custom_base_relative_url( 'templates' );
	}

	/**
	 * Returns the path to the classes folder of the current class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	final public static function get_includes_base_path() {
		return self::get_custom_base_path( 'includes' );
	}

	/**
	 * Returns the path to the classes folder of the current class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	final public static function get_includes_base_relative_url() {
		return self::get_custom_base_relative_url( 'includes' );
	}

	// endregion
}
