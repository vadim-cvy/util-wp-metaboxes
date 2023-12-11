<?php
namespace Cvy\WP\Metaboxes\Actions;
use \Cvy\WP\Metaboxes\Metabox;
use \Exception;

/**
 * Base class for actions associated with a metabox.
 */
abstract class Action
{
  /**
   * @var Metabox Holds the metabox instance.
   */
  final protected Metabox $metabox;

  /**
   * Constructor for Action.
   *
   * @param Metabox $metabox The metabox instance the action is associated with.
   */
  public function __construct( Metabox $metabox )
  {
    $this->metabox = $metabox;

    $this->listen();
  }

  /**
   * Retrieves the base name of the action.
   *
   * @return string The base name of the action. Example: "enable_something".
   */
  abstract static public function get_name_base() : string;

  /**
   * Retrieves the full name of the action.
   *
   * Full name is {metabox slug}_{action base name}.
   */
  final public function get_name() : string
  {
    return $this->metabox->get_slug() . '_' . $this->get_name_base();
  }

  /**
   * Inits the action handling if one is submitted.
   */
  private function listen() : void
  {
    if ( ! $this->is_submitted() )
    {
      return;
    }

    $nonce = $this->get_arg( 'nonce' );

    if ( ! $nonce )
    {
      throw new Exception( 'Nonce is missed!' );
    }

    if ( ! $this->verify_nonce( $nonce ) )
    {
      wp_die( 'The nonce has expired. Try one more time.' );
    }

    $this->handle();

    $this->on_handled();
  }

  /**
   * Handles the action.
   */
  abstract protected function handle() : void;

  /**
   * Callback function executed after the action is handled.
   */
  abstract protected function on_handled() : void;

  /**
   * Prefixes a GET/POST argument name with the action's name.
   *
   * You need to prefix your GET and POST args (including input names) associated
   * with this action to make ::is_submitted(), ::get_args(), etc work properly.
   *
   * @param string $unprefixed_name The unprefixed name of the argument.
   */
  final public function prefix_arg_name( string $unprefixed_name ) : string
  {
    return $this->get_arg_name_prefix() . $unprefixed_name;
  }

  /**
   * Unprefixes a GET/POST argument name.
   *
   * @param string $prefixed_name The prefixed argument name.
   */
  private function unprefix_arg_name( string $prefixed_name ) : string
  {
    return str_replace( $this->get_arg_name_prefix(), '', $prefixed_name );
  }

  /**
   * Retrieves the argument name prefix.
   */
  private function get_arg_name_prefix() : string
  {
    return $this->get_name() . '_';
  }

  /**
   * Retrieves the value of a submitted GET/POST argument by name.
   *
   * @param string $unprefixed_name The unprefixed name of the argument.
   */
  final public function get_arg( string $unprefixed_name )
  {
    return $this->get_args()[ $unprefixed_name ] ?? null;
  }

  /**
   * Checks if the action is submitted.
   */
  final public function is_submitted() : bool
  {
    return ! empty( $this->get_args() );
  }

  /**
   * Retrieves an array of submitted GET/POST arguments.
   *
   * GET and POST arguments are merged.
   */
  final public function get_args()
  {
    $args = [];

    $all_args = array_merge( $_GET, $_POST );

    foreach ( $all_args as $key => $value )
    {
      $unprefixed_key = $this->unprefix_arg_name( $key );

      if ( $key !== $unprefixed_key )
      {
        $args[ $unprefixed_key ] = $value;
      }
    }

    return $args;
  }

  /**
   * Creates and retrieves a nonce value for the action.
   */
  private function create_nonce() : string
  {
    return wp_create_nonce( $this->get_name() );
  }

  /**
   * Verifies the valid nonce value against the submitted one.
   *
   * @param string $nonce The submitted nonce value.
   */
  private function verify_nonce( string $nonce ) : bool
  {
    return wp_verify_nonce( $nonce, $this->get_name() );
  }
}