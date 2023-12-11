<?php
namespace Cvy\WP\Metaboxes\Actions;

/**
 * Represents direct URL based actions.
 *
 * This class is designed for internal usage, and DirectLink should be used externally.
 */
abstract class DirectURL extends Action
{
  /**
   * Retrieves the action trigger URL.
   *
   * @param array $args Additional arguments to be included in the URL.
   */
  final protected function get_trigger_url( array $args = [] ) : string
  {
    $args['nonce'] = $this->create_nonce();

    $arg_prefixed_keys = array_map(
      fn( $key ) => $this->prefix_arg_name( $key ),
      array_keys( $args )
    );

    $args = array_combine(
      $arg_prefixed_keys,
      array_values( $args )
    );

    $url = add_query_arg( $args, $this->get_current_object_edit_url() );

    return $url;
  }

  /**
   * Retrieves the URL for editing the current object (post/term/user).
   */
  final protected function get_current_object_edit_url() : string
  {
    $object_type = $this->metabox->get_current_object_type();

    $object_id = $this->metabox->get_current_object_id();

    switch ( $object_type )
    {
      case 'post':
        return get_edit_post_link( $object_id );

      default:
        throw new Exception( "Unexpected object type: $object_type!" );
    }
  }
}