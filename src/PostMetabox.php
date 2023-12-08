<?php
namespace Cvy\WP\Metaboxes;

abstract class PostMetabox extends PostTypeMetabox
{
  abstract protected function get_target_post_id() : int;

  final protected function is_current_screen_authorized() : bool
  {
    return parent::is_current_screen_authorized()
      && $this->get_current_post_id() === $this->get_target_post_id();
  }
}