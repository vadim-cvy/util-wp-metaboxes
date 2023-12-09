<?php
namespace Cvy\WP\Metaboxes\Actions;
use \Exception;

abstract class AJAX extends DirectURL
{
  public function __construct( string $metabox_slug, NoticesManager $notices_manager )
  {
    parent::__construct( $metabox_slug, $notices_manager );

    add_action( 'admin_enqueue_scripts', fn() => $this->enqueue_js( $this->get_trigger_url() ) );
  }

  final protected function on_handled() : void
  {
    $class_name = get_called_class();

    throw new Exception(sprintf(
      "You must call $class_name::send_{success|error}() in $class_name::handle()!",
      get_called_class()
    ));
  }

  final protected function send_success( array $response_data = [] ) : void
  {
    wp_send_json_success( $response_data );
  }

  final protected function send_error( string $error, array $details_data = [] ) : void
  {
    wp_send_json_error([
      'error' => $error,
      'details' => $details_data,
    ]);
  }

  abstract protected function enqueue_js( string $ajax_url ) : void;
}