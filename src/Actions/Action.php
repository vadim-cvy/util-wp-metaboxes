<?php
namespace Cvy\WP\Metaboxes\Actions;
use \Cvy\WP\Metaboxes\Metabox;
use \Exception;

abstract class Action
{
  final protected Metabox $metabox;

  public function __construct( Metabox $metabox )
  {
    $this->metabox = $metabox;

    $this->listen();
  }

  abstract static public function get_name_base() : string;

  final public function get_name() : string
  {
    return $this->metabox->get_slug() . '_' . $this->get_name_base();
  }

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

  abstract protected function handle() : void;

  abstract protected function on_handled() : void;

  final public function prefix_arg_name( string $arg_base_name ) : string
  {
    return $this->get_arg_name_prefix() . $arg_base_name;
  }

  private function unprefix_arg_name( string $name ) : string
  {
    return str_replace( $this->get_arg_name_prefix(), '', $name );
  }

  private function get_arg_name_prefix() : string
  {
    return $this->get_name() . '_';
  }

  final public function get_arg( string $name )
  {
    return $this->get_args()[ $name ] ?? null;
  }

  final public function is_submitted() : bool
  {
    return ! empty( $this->get_args() );
  }

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

  private function create_nonce() : string
  {
    return wp_create_nonce( $this->get_name() );
  }

  private function verify_nonce( string $nonce ) : bool
  {
    return wp_verify_nonce( $nonce, $this->get_name() );
  }
}