<?php
namespace Cvy\WP\Metaboxes\Actions;

abstract class Submit extends Action
{
  final protected function on_handled() : void {}

  final public function get_submit_html(
    string $button_label,
    array $button_attrs = [],
    bool $hide_submit_post_note = false
  ) : string
  {
    $button = get_submit_button( $label, 'primary large', '', true, $button_attrs );

    $submit_post_note = $hide_submit_post_note ? '' : '<p><i>Note: this will save any post changes as well</i></p>';

    $nonce_input = sprintf( '<input type="hidden" name="%s" value="%s">',
      esc_attr( $this->prefix_input_name( 'nonce' ) ),
      esc_attr( $this->create_nonce() )
    );

    return $button . $submit_post_note . $nonce_input;
  }

  final public function prefix_input_name( string $input_name ) : string
  {
    return $this->prefix_arg_name( $input_name );
  }
}