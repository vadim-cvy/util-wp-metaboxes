<?php
namespace Cvy\WP\Metaboxes\Actions;

/**
 * Represents direct link (<a href="...">) based actions.
 */
abstract class DirectLink extends DirectURL
{
  /**
   * @override
   */
  final protected function on_handled() : void
  {
    wp_redirect( $this->get_current_object_edit_url() );
    exit();
  }

  /**
   * Retrieves the HTML for an <a> button-styled tag triggering an action.
   *
   * @param string $label The label for the button.
   * @param array $url_args Additional arguments for the URL.
   * @param array $tag_attrs Additional attributes for the link tag.
   */
  final public function get_button( string $label, array $url_args = [], array $tag_attrs = [] ) : string
  {
    $tag_attrs['class'] = $tag_attrs['class'] ?? '';

    $tag_attrs['class'] .= ' button';

    return $this->get_link( $label, $url_args, $tag_attrs );
  }

  /**
   * Retrieves the HTML for an <a> tag triggering an action.
   *
   * @param string $label The label for the link.
   * @param array $url_args Additional arguments for the URL.
   * @param array $tag_attrs Additional attributes for the link tag.
   */
  final public function get_link( string $label, array $url_args = [], array $tag_attrs = [] ) : string
  {
    $tag_attrs['href'] = $this->get_trigger_url( $url_args );

    return $this->build_link_tag( $label, $tag_attrs );
  }

  /**
   * Builds the link tag HTML.
   *
   * @param string $label The label for the link.
   * @param array $attrs Additional attributes for the link tag.
   */
  private function build_link_tag( string $label, $attrs ) : string
  {
    $attrs_str = '';

    foreach ( $attrs as $key => $value )
    {
      $attrs_str .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $value ) );
    }

    return "<a $attrs_str>$label</a>";
  }
}