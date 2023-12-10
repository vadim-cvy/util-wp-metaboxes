<?php
namespace Cvy\WP\Metaboxes\Actions;
use \Cvy\WP\Metaboxes\Metabox;
use \Exception;

abstract class AJAX extends DirectURL
{
  public function __construct( Metabox $metabox )
  {
    parent::__construct( $metabox_slug, $notices_manager );

    add_action( 'admin_enqueue_scripts', fn() => $this->enqueue_assets() );
  }

  final protected function on_handled() : void
  {
    $class_name = get_called_class();

    throw new Exception( "You must ouput AJAX response in $class_name::handle() and then call exit()!" );
  }

  private function enqueue_assets() : void
  {
    $this->enqueue_js();

    $ajax_url = $this->get_trigger_url();

    $this->localize_js_data( $ajax_url );
  }

  abstract protected function enqueue_js() : void;

  abstract protected function localize_js_data( string $ajax_url ) : void;
}