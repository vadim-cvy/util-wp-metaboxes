<?php
namespace Cvy\WP\Metaboxes\Actions;

/**
 * Direct URL based action handler.
 *
 * This class is developed for internal usage. DirectLink class (not DirectURL one)
 * is what you're looking for probably.
 */
abstract class DirectURL extends Action
{
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

    $url = add_query_arg( $args, $this->get_target_object_edit_url() );

    return $url;
  }

  abstract protected function get_target_object_edit_url() : string;
}