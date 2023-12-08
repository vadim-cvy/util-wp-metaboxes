<?php
namespace Cvy\WP\Metaboxes;

abstract class Metabox extends \Cvy\DesignPatterns\Singleton
{
  final protected function __construct()
  {
    add_action( 'current_screen', fn() => $this->maybe_init() );
  }

  private function maybe_init() : void
  {
    if ( $this->is_authorized() )
    {
      $this->init();
    }
  }

  protected function init() : void
  {
    $this->register();

    $this->notices_manager = new MetaboxNoticesManager( $this->metabox_slug );

    $this->listen_actions();

    add_action( 'admin_enqueue_scripts', fn() => $this->enqueue_assets() );
  }

  abstract protected function register() : void;

  private function listen_actions() : void
  {
    foreach ( $this->get_action_handlers() as $action_handler )
    {
      $action_handler->listen();
    }
  }

  private function is_authorized() : bool
  {
    if ( ! did_action( 'current_screen' ) && ! current_action() === 'current_screen' )
    {
      throw new \Exception( __METHOD__ . ' must not be called before "current_screen" action fired!' );
    }

    return $this->is_current_screen_authorized()
      && $this->is_current_user_authorized();
  }

  abstract protected function is_current_screen_authorized() : bool;

  abstract protected function is_current_user_authorized() : bool;

  abstract protected function get_slug() : string;

  abstract protected function get_title() : string;

  final protected function render() : void
  {
    ob_start();

    $is_success = $this->render_inner_content();

    $content =
      $is_success ?
      ob_get_contents() :
      '<b>Error. Can\'t render this content!</b>';

    printf( '<div class="%s-metabox-content">%s</div>',
      esc_attr( $this->get_slug() ),
      $content
    );
  }

  abstract protected function render_inner_content() : bool;

  abstract protected function enqueue_assets() : void;

  abstract protected function get_current_object_id() : int;

  abstract protected function get_action_handlers() : array;
}