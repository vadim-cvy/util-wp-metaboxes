<?php
namespace Cvy\WP\Metaboxes\Actions;

abstract class DirectLink extends DirectURL
{
  final protected function on_handled() : void
  {
    wp_redirect( $this->get_current_object_edit_url() );
    exit();
  }

  final public function get_button( string $label, array $url_args = [], array $tag_attrs = [] ) : string
  {
    $tag_attrs['class'] = $tag_attrs['class'] ?? '';

    $tag_attrs['class'] .= ' button';

    return $this->get_link( $label, $url_args, $tag_attrs );
  }

  final public function get_link( string $label, array $url_args = [], array $tag_attrs = [] ) : string
  {
    $tag_attrs['href'] = $this->get_trigger_url( $url_args );

    return $this->build_link_tag( $label, $tag_attrs );
  }

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