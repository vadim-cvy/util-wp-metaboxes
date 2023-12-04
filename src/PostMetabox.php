<?php
namespace Cvy\WP\Metabox;

use \Exception;

// todo: implement term metabox
abstract class PostMetabox extends \Cvy\DesignPatterns\Singleton
{
  private $admin_notices;

  protected function __construct()
  {
    if ( ! session_id() )
    {
      session_start();
    }

    $this->set_admin_notices_initial_value();

    add_action( 'current_screen', fn() => $this->maybe_handle_action() );

    add_action( 'add_meta_boxes', fn() => $this->register() );

    add_action( 'admin_enqueue_scripts', fn() => $this->maybe_enqueue_assets() );

    add_action( 'admin_notices', fn() => $this->display_admin_notices() );
  }

  private function set_admin_notices_initial_value() : void
  {
    $this->admin_notices = [];

    $sess_key = $this->prefix( 'admin_notices' );

    if ( isset( $_SESSION[ $sess_key ] ) )
    {
      $this->admin_notices = $_SESSION[ $sess_key ];

      unset( $_SESSION[ $sess_key ] );
    }
  }

  /**
   * Registers the metabox.
   *
   * @return void
   */
  private function register() : void
  {
    if ( $this->can_register() )
    {
      add_meta_box(
        $this->get_slug(),
        $this->get_title(),
        fn() => $this->display(),
        get_current_screen(),
        $this->get_context(),
        $this->get_priority()
      );
    }
  }

  /**
   * Checks if metabox can be registered.
   *
   * @return boolean
   */
  final protected function can_register() : bool
  {
    if ( ! did_action( 'current_screen' ) && ! current_action() === 'current_screen' )
    {
      throw new Exception( __METHOD__ . ' must not be called before "current_screen" action fired!' );
    }

    $screen = get_current_screen();

    return $screen->base === 'post' &&
      in_array( $screen->post_type, $this->get_post_types() ) &&
      ! $this->is_disabled_on_current_post() &&
      $this->is_user_authorized();
  }

  /**
   * Checks if metabox should be disabled for specifically current post.
   *
   * @return boolean
   */
  abstract protected function is_disabled_on_current_post() : bool;

  /**
   * Checks if current user can see the metabox.
   *
   * @return boolean
   */
  abstract protected function is_user_authorized() : bool;

  /**
   * @return array Post-types metabox should be registered for.
   */
  abstract protected function get_post_types() : array;

  /**
   * @return string Metabox slug.
   */
  abstract protected function get_slug() : string;

  /**
   * @return string Metabox title.
   */
  abstract protected function get_title() : string;

  /**
   * Prints the metabox content.
   *
   * @return void
   */
  abstract protected function display() : void;

  /**
   * @see add_meta_box()
   * @return string The context within the screen where the box should display.
   */
  protected function get_context() : string
  {
    return 'normal';
  }

  /**
   * @see add_meta_box()
   * @return string The priority within the context where the box should show.
   */
  protected function get_priority() : string
  {
    return 'high';
  }

  /**
   * Checks for main/custom actions submission, performs validation and calls action handler.
   *
   * Main submission is handled by
   *
   * @return void
   */
  private function maybe_handle_action() : void
  {
    $action_name = $this->get_request_arg( 'action' );

    if ( ! $action_name )
    {
      return;
    }

    if ( ! $this->can_register() )
    {
      throw new Exception( 'Action is not permitted!' );
    }

    $nonce = $this->get_request_arg( 'nonce' );

    if ( ! $nonce )
    {
      throw new Exception( 'Nonce is missed!' );
    }

    if ( ! $this->verify_action_nonce( $nonce, $action_name ) )
    {
      wp_die( 'The nonce has expired. Try one more time.' );
    }

    $this->{'handle_action__' . $action_name}();

    if ( $action_name !== 'submit' )
    {
      wp_redirect( $this->get_base_url() );
      exit();
    }
  }

  final protected function get_submit_html(
    string $label,
    array $button_attrs = [],
    bool $add_update_post_suffix = true
  ) : string
  {
    if ( $add_update_post_suffix )
    {
      $post_type = get_post_type_object( get_post_type( get_the_ID() ) );

      $label .= ' and Update ' . $post_type->labels->singular_name;
    }

    $action_name = 'submit';

    $button_attrs = array_merge( $button_attrs, [
      'type' => 'submit',
      'class' => 'button button-primary',
      'name' => $this->prefix_input_name( 'action' ),
      'value' => $action_name,
    ]);

    $nonce_input = sprintf( '<input type="hidden" name="%s" value="%s">',
      esc_attr( $this->prefix_input_name( 'nonce' ) ),
      esc_attr( $this->create_action_nonce( $action_name ) )
    );

    $submit_button = $this->get_action_tag(
      $action_name,
      esc_html( $label ),
      'button',
      $button_attrs
    );

    return $nonce_input . $submit_button;
  }

  final protected function get_action_link_tag(
    string $action_name,
    string $label,
    array $url_args = [],
    array $tag_attrs = []
  ) : string
  {
    $tag_attrs['href'] = $this->get_action_url( $action_name, $url_args );

    return $this->get_action_tag( $action_name, $label, 'a', $tag_attrs );
  }

  private function get_action_tag( string $action_name, string $label, string $tag, array $attrs ) : string
  {
    $attrs['class'] = $attrs['class'] ?? '';

    $css_base_class = str_replace( '_', '-', $this->get_slug() ) . '-action-trigger';

    $attrs['class'] .=
      $css_base_class . ' ' .
      $css_base_class . '_' . str_replace( '_', '-', $action_name );

    $output = '<' . $tag;

    foreach ( $attrs as $key => $value )
    {
      $output .= sprintf( ' %s="%s"', $key, esc_attr( $value ) );
    }

    $output .= sprintf( '>%s</%s>', $label, $tag );

    return $output;
  }

  final protected function get_action_url( string $action_name, array $args = [] ) : string
  {
    return $this->get_base_url(array_merge( $args, [
      'action' => $action_name,
      'nonce' => $this->create_action_nonce( $action_name ),
    ]));
  }

  final protected function get_base_url( array $args = [] ) : string
  {
    $url = get_edit_post_link( $this->get_post_id(), '&' );

    $prefixed_keys = array_map(
      fn( $key ) => $this->prefix_input_name( $key ),
      array_keys( $args )
    );

    $args = array_combine(
      $prefixed_keys,
      array_values( $args )
    );

    if ( ! empty( $args ) )
    {
      $url = add_query_arg( $args, $url );
    }

    return $url;
  }

  private function create_action_nonce( string $action_name ) : string
  {
    return wp_create_nonce( $this->prefix( $action_name ) );
  }

  private function verify_action_nonce( string $nonce, string $action_name ) : bool
  {
    return wp_verify_nonce( $nonce, $this->prefix( $action_name ) );
  }

  /**
   * Retrieves metabox-scoped GET/POST arg.
   *
   * @param string $key Argument key.
   * @return mixed|null Argument value (if one appears in the request), otherwise null.
   */
  protected function get_request_arg( string $key )
  {
    $key = $this->prefix_input_name( $key );

    return $_GET[ $key ] ?? $_POST[ $key ] ?? null;
  }

  private function maybe_enqueue_assets() : void
  {
    if ( $this->can_register() )
    {
      $this->enqueue_assets();
    }
  }

  abstract protected function enqueue_assets() : void;

  private function display_admin_notices() : void
  {
    foreach ( $this->admin_notices as $notice )
    {
      printf( '<div class="notice notice-%s">%s</div>',
        $notice['type'],
        $notice['msg']
      );
    }
  }

  final protected function add_success_admin_notice( string $msg, bool $wrap = true ) : void
  {
    $this->add_admin_notice( 'success', $msg, $wrap );
  }

  final protected function add_info_admin_notice( string $msg, bool $wrap = true ) : void
  {
    $this->add_admin_notice( 'info', $msg, $wrap );
  }

  final protected function add_error_admin_notice( string $msg, bool $wrap = true ) : void
  {
    $this->add_admin_notice( 'error', $msg, $wrap );
  }

  private function add_admin_notice( string $type, string $msg, bool $wrap ) : void
  {
    if ( $wrap )
    {
      $msg = sprintf( '<p>%s</p>', $msg );
    }

    $this->admin_notices[] = [
      'type' => $type,
      'msg' => $msg,
    ];

    if ( get_current_screen()->base === 'post' )
    {
      $_SESSION[ $this->prefix( 'admin_notices' ) ] = $this->admin_notices;
    }
  }

  final protected function get_post_id() : int
  {
    return $_GET['post'] ?? $_POST['post_ID'];
  }

  final protected function die( string $msg ) : void
  {
    $msg = '<b>Error:</b> ' . $msg;

    wp_die( $msg, 'Error!', [
      'back_link' => true,
      'link_url' => $this->get_base_url(),
    ]);
  }

  /**
   * Prefixes string with the metabox slug.
   *
   * @param string $string String needed to be prefixed.
   * @return string Prefixed string.
   */
  final protected function prefix( string $string ) : string
  {
    $prefix = preg_replace( '~\W~', '_', $this->get_slug() );

    if ( strpos( $string, $prefix ) !== 0 )
    {
      $string = $prefix . '_' . $string;
    }

    return $string;
  }

  final protected function prefix_input_name( string $input_name ) : string
  {
    return $this->prefix( $input_name );
  }

  final protected function prefix_css_selector( string $selector ) : string
  {
    return str_replace( '_', '-', $this->prefix( '' ) ) . $selector;
  }
}