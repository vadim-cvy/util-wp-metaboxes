<?php
namespace Cvy\WP\Metaboxes;

abstract class PostMetabox extends PostTypeMetabox
{
  abstract protected function get_target_post_id() : int;

  protected function is_current_screen_eligable() : bool
  {
    return parent::is_current_screen_eligable()
      && $this->get_current_post_id() === $this->get_target_post_id();
  }

  final protected function get_post_types() : array
  {
    return [ get_post_type( $this->get_target_post_id() ) ];
  }
}