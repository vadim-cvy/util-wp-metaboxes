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
      $type = esc_attr( $notice['type'] );

      $msg_wrap_tag_name = $this->get_msg_wrap_tag_name();

      $msg_prefix = $this->get_msg_prefix( $notice['type'] );

      $msg = $notice['msg'];

      echo
        "<div class='notice notice-$type'>
          <$msg_wrap_tag_name>
            $msg_prefix $msg
          </$msg_wrap_tag_name>
        </div>"
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

  public function add_success_notice( string $msg  ) : void
  {
    $this->add_notice( 'success', $msg );
  }

  public function add_info_notice( string $msg ) : void
  {
    $this->add_notice( 'info', $msg );
  }

  public function add_error_notice( string $msg ) : void
  {
    $this->add_notice( 'error', $msg );
  }

  private function add_notice( string $type, string $msg ) : void
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

  protected function get_msg_wrap_tag_name() : string
  {
    return 'p';
  }

  protected function get_msg_prefix( string $notice_type ) : string
  {
    if ( $notice_type === 'success' )
    {
      return 'Success!';
    }
    else if ( $notice_type === 'error' )
    {
      return 'Error!';
    }

    return '';
  }

  private function get_session_index() : void
  {
    return $this->metabox_slug . '_notices';
  }
}