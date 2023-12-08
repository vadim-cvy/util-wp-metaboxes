<?php
namespace Cvy\WP\Metaboxes;

abstract class MetaboxNoticesManager extends \Cvy\DesignPatterns\Singleton
{
  private string $metabox_slug;

  public function __construct( string $metabox_slug )
  {
    $this->metabox_slug = $metabox_slug;

    if ( ! session_id() )
    {
      session_start();
    }

    if ( ! isset( $_SESSION[ $this->get_session_index() ] ) )
    {
      $this->reset_notices();
    }

    add_action( 'admin_notices', fn() => $this->render_notices() );
  }

  private function render_notices() : void
  {
    foreach ( $this->get_notices() as $notice )
    {
      printf( '<div class="notice notice-%s">%s</div>',
        $notice['type'],
        $notice['msg']
      );
    }

    $this->reset_notices();
  }

  private function get_notices() : array
  {
    return $_SESSION[ $this->get_session_index() ] ?? [];
  }

  private function reset_notices() : void
  {
    return $_SESSION[ $this->get_session_index() ] = [];
  }

  public function add_success_notice( string $msg, string $print_pattern = null ) : void
  {
    $this->add_notice( 'success', $msg, $print_pattern );
  }

  public function add_info_notice( string $msg, string $print_pattern = null ) : void
  {
    $this->add_notice( 'info', $msg, $print_pattern );
  }

  public function add_error_notice( string $msg, string $print_pattern = null ) : void
  {
    $this->add_notice( 'error', $msg, $print_pattern );
  }

  private function add_notice( string $type, string $msg, string $print_pattern = null ) : void
  {
    if ( ! isset( $print_pattern ) )
    {
      $print_pattern = '<p>'

      if ( $type === 'error' )
      {
        $print_pattern .= '<b>Error:</b> ';
      }

      $print_pattern .= '%s</p>';
    }

    $_SESSION[ $this->get_session_index() ][] = [
      'type' => $type,
      'msg' => $msg,
    ];
  }

  private function get_session_index() : void
  {
    return $this->metabox_slug . '_notices';
  }
}