<?php
namespace Cvy\WP\Metaboxes\Actions;
use \Cvy\WP\Metaboxes\Metabox;
use \Exception;

abstract class AJAX extends DirectURL
{
  /**
   * @override
   */
  public function __construct( Metabox $metabox )
  {
    parent::__construct( $metabox );

    add_action( 'admin_enqueue_scripts', fn() => $this->enqueue_assets() );
  }

  /**
   * @override
   */
  final protected function on_handled() : void
  {
    $class_name = get_called_class();

    throw new Exception( "You must ouput AJAX response in $class_name::handle() and then call exit()!" );
  }

  /**
   * Enqueues assets required for AJAX action.
   */
  private function enqueue_assets() : void
  {
    $this->enqueue_js();

    $ajax_url = $this->get_trigger_url();

    $this->localize_js_data( $ajax_url );
  }

  /**
   * Enqueues JavaScript files required for AJAX action.
   */
  abstract protected function enqueue_js() : void;

  /**
   * Localizes data for JavaScript usage.
   *
   * @param string $ajax_url The URL to trigger the AJAX action. You probably want to localize it.
   */
  abstract protected function localize_js_data( string $ajax_url ) : void;
}