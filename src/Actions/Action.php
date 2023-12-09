<?php
namespace Cvy\WP\Metaboxes\Actions;
use \Cvy\WP\Metaboxes\Notices\NoticesManager;
use \Exception;

abstract class Action
{
  private string $metabox_slug;

  private NoticesManager $notices_manager;

  public function __construct( string $metabox_slug, NoticesManager $notices_manager )
  {
    $this->metabox_slug = $metabox_slug;
    $this->notices_manager = $notices_manager;
  }

  abstract static public function get_name_base() : string;

  final protected function get_name() : string
  {
    return $this->metabox_slug . '_' . $this->get_name_base();
  }

  public function listen() : void
  {
    add_action( 'current_screen', fn() => $this->maybe_handle() );
  }

  private function maybe_handle() : void
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

  final protected function prefix_arg_name( string $arg_base_name ) : string
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

  final protected function get_arg( string $name )
  {
    return $this->get_args()[ $name ] ?? null;
  }

  final protected function is_submitted() : bool
  {
    return ! empty( $this->get_args() );
  }

  final protected function get_args()
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