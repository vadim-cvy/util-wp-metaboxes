<?php
namespace Cvy\WP\Metaboxes;
use \Cvy\WP\Metaboxes\Notices\NoticesManager;

abstract class Metabox extends \Cvy\DesignPatterns\Singleton
{
  private array $actions;

  private NoticesManager $notices_manager;

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

    $this->notices_manager = new NoticesManager( $this->get_slug() );

    $this->init_actions();
  }

  abstract protected function register() : void;

  final protected function get_notices_manager() : NoticesManager
  {
    return $this->notices_manager;
  }

  private function init_actions() : void
  {
    if ( ! isset( $this->actions ) )
    {
      $this->actions = [];

      foreach ( $this->create_action_instances() as $action )
      {
        $this->actions[ $action->get_name_base() ] = $action;
      }
    }

    $this->actions;
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

  abstract public function get_slug() : string;

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

  abstract public function get_current_object_id() : int;

  abstract protected function create_action_instances() : array;
}