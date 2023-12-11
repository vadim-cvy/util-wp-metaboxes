<?php
namespace Cvy\WP\Metaboxes\Notices;

/**
 * Manages notices specific to the metabox.
 *
 * WP works the way it handles post data submission on the handler page and then
 * redirects you back to the post page. This class holds notices from the submission
 * handler page back to the post page.
 */
abstract class NoticesManager extends \Cvy\DesignPatterns\Singleton
{
  /**
   * @var string Holds the slug for the associated metabox.
   */
  private string $metabox_slug;

  /**
   * Constructor for NoticesManager.
   *
   * @param string $metabox_slug The slug for the associated metabox.
   */
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

  /**
   * Renders notices stored in session.
   */
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

  /**
   * Retrieves notices saved in session.
   */
  private function get_notices() : array
  {
    return $_SESSION[ $this->get_session_index() ] ?? [];
  }

  /**
   * Resets notices saved in session.
   */
  private function reset_notices() : void
  {
    return $_SESSION[ $this->get_session_index() ] = [];
  }

  /**
   * Adds a success notice to the session.
   *
   * @param string $msg The success message.
   */
  public function add_success_notice( string $msg  ) : void
  {
    $this->add_notice( 'success', $msg );
  }

  /**
   * Adds an info notice to the session.
   *
   * @param string $msg The info message.
   */
  public function add_info_notice( string $msg ) : void
  {
    $this->add_notice( 'info', $msg );
  }

  /**
   * Adds an error notice to the session.
   *
   * @param string $msg The error message.
   */
  public function add_error_notice( string $msg ) : void
  {
    $this->add_notice( 'error', $msg );
  }

  /**
   * Adds notice to the session.
   *
   * @param string $type The notice type (success, error, etc.).
   * @param string $msg The notice message.
   */
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

  /**
   * Retrieves the HTML tag name for wrapping notice messages.
   *
   * @return string The HTML tag name. Default is "p".
   */
  protected function get_msg_wrap_tag_name() : string
  {
    return 'p';
  }

  /**
   * Retrieves the prefix for notice messages based on the notice type.
   *
   * @param string $notice_type The type of notice (success, error, etc.).
   */
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

  /**
   * Retrieves the session index for storing notices.
   */
  private function get_session_index() : void
  {
    return $this->metabox_slug . '_notices';
  }
}