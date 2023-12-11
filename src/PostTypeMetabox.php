<?php
namespace Cvy\WP\Metaboxes;

/**
 * Base class for creating WordPress metaboxes associated with specific post types.
 */
abstract class PostTypeMetabox extends Metabox
{
  /**
   * @override
   */
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

  /**
   * @override
   */
  public function get_current_object_type() : string
  {
    return 'post';
  }

  /**
   * Retrieves an array of post types associated with the metabox.
   */
  abstract protected function get_post_types() : array;

  /**
   * Retrieves the metabox context, default is 'normal'.
   */
  protected function get_context() : string
  {
    return 'normal';
  }

  /**
   * Retrieves the metabox priority, default is 'high'.
   */
  protected function get_priority() : string
  {
    return 'high';
  }

  /**
   * @override
   */
  protected function is_current_screen_eligable() : bool
  {
    $screen = get_current_screen();

    return $screen->base === 'post' && in_array( $screen->post_type, $this->get_post_types() );
  }

  /**
   * Retrieves the post ID of the currently opened screen (page).
   */
  final public function get_current_post_id() : int
  {
    return $this->get_current_object_id();
  }

  /**
   * @override
   */
  final public function get_current_object_id() : int
  {
    $id = get_the_ID() ? get_the_ID() : null;

    return $id ?? $_GET['post'] ?? $_POST['post_ID'] : 0;
  }
}