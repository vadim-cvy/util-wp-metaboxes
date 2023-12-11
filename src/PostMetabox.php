<?php
namespace Cvy\WP\Metaboxes;

/**
 * Base class for creating WordPress metaboxes associated with a specific post.
 */
abstract class PostMetabox extends PostTypeMetabox
{
  /**
   * Retrieves post ID metabox should be associated with.
   */
  abstract protected function get_target_post_id() : int;

  /**
   * @override
   */
  protected function is_current_screen_eligable() : bool
  {
    return parent::is_current_screen_eligable()
      && $this->get_current_post_id() === $this->get_target_post_id();
  }

  /**
   * @override
   */
  final protected function get_post_types() : array
  {
    return [ get_post_type( $this->get_target_post_id() ) ];
  }
}