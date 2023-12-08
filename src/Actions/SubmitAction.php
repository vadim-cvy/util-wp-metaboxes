<?php
namespace Cvy\WP\Metaboxes\Actions;

abstract class SubmitAction
{
  final protected function handle() : void
  {
    parent::handle();
  }



















  final protected function get_submit_html(
    string $label,
    array $button_attrs = [],
    bool $add_update_post_suffix = true
  ) : string
  {
    if ( $add_update_post_suffix )
    {
      $post_type = get_post_type_object( get_post_type( get_the_ID() ) );

      $label .= ' and Update ' . $post_type->labels->singular_name;
    }

    $action_name = 'submit';

    $button_attrs = array_merge( $button_attrs, [
      'type' => 'submit',
      'class' => 'button button-primary',
      'name' => $this->prefix_input_name( 'action' ),
      'value' => $action_name,
    ]);

    $nonce_input = sprintf( '<input type="hidden" name="%s" value="%s">',
      esc_attr( $this->prefix_input_name( 'nonce' ) ),
      esc_attr( $this->create_nonce( $action_name ) )
    );

    $submit_button = $this->get_action_tag(
      $action_name,
      esc_html( $label ),
      'button',
      $button_attrs
    );

    return $nonce_input . $submit_button;
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

  final protected function get_action_url( string $action_name, array $args = [] ) : string
  {
    return $this->get_base_url(array_merge( $args, [
      'action' => $action_name,
      'nonce' => $this->create_nonce( $action_name ),
    ]));
  }

  final protected function get_base_url( array $args = [] ) : string
  {
    $url = get_edit_post_link( $this->get_post_id(), '&' );

    $prefixed_keys = array_map(
      fn( $key ) => $this->prefix_input_name( $key ),
      array_keys( $args )
    );

    $args = array_combine(
      $prefixed_keys,
      array_values( $args )
    );

    if ( ! empty( $args ) )
    {
      $url = add_query_arg( $args, $url );
    }

    return $url;
  }
}