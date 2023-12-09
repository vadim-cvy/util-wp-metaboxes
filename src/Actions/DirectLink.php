<?php
namespace Cvy\WP\Metaboxes\Actions;

abstract class DirectLink extends DirectURL
{
  final protected function on_handled() : void
  {
    wp_redirect( $this->get_target_object_edit_url() );
    exit();
  }














  final protected function get_action_link_tag(
    string $action_name,
    string $label,
    array $url_args = [],
    array $tag_attrs = []
  ) : string
  {
    $tag_attrs['href'] = $this->get_action_url( $action_name, $url_args );

    return $this->get_action_tag( $action_name, $label, 'a', $tag_attrs );
  }

  private function get_action_tag( string $action_name, string $label, string $tag, array $attrs ) : string
  {
    $attrs['class'] = $attrs['class'] ?? '';

    $css_base_class = str_replace( '_', '-', $this->get_slug() ) . '-action-trigger';

    $attrs['class'] .=
      $css_base_class . ' ' .
      $css_base_class . '_' . str_replace( '_', '-', $action_name );

    $output = '<' . $tag;

    foreach ( $attrs as $key => $value )
    {
      $output .= sprintf( ' %s="%s"', $key, esc_attr( $value ) );
    }

    $output .= sprintf( '>%s</%s>', $label, $tag );

    return $output;
  }
}