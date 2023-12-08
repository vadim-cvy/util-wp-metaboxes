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
  }

  abstract protected function handle() : void;

  final protected function get_arg( string $key )
  {
    return $this->get_submitted_args()[ $key ] ?? null;
  }

  final protected function is_submitted() : bool
  {
    return ! empty( $this->get_submitted_args() );
  }

  final protected function get_submitted_args()
  {
    return $_GET[ $this->get_name() ] ?? $_POST[ $this->get_name() ] ?? [];
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