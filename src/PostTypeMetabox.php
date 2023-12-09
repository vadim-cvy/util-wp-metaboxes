<?php
namespace Cvy\WP\Metaboxes;

abstract class PostTypeMetabox extends Metabox
{
  final protected function register() : void
  {
    add_meta_box(
      $this->get_slug(),
      $this->get_title(),
      fn() => $this->render(),
      get_current_screen(),
      $this->get_context(),
      $this->get_priority()
    );
  }

  abstract protected function get_post_types() : array;

  protected function get_context() : string
  {
    return 'normal';
  }

  protected function get_priority() : string
  {
    return 'high';
  }

  protected function is_current_screen_authorized() : bool
  {
    $screen = get_current_screen();

    return $screen->base === 'post' && in_array( $screen->post_type, $this->get_post_types() );
  }

  final public function get_current_post_id() : int
  {
    return $this->get_current_object_id();
  }

  final public function get_current_object_id() : int
  {
    $id = get_the_ID() ? get_the_ID() : null;

    return $id ?? $_GET['post'] ?? $_POST['post_ID'] : 0;
  }
}