<?php
namespace Cvy\WP\Metaboxes\Actions;

abstract class Submit extends Action
{
  final protected function on_handled() : void {}

  final public function get_submit_button(
    string $label,
    array $attrs = [],
    bool $show_submit_post_note = true
  ) : string
  {
    ob_start();

    printf( '<div class="%s-submit">', esc_attr( $this->get_name() ) );

    echo get_submit_button( $label, 'primary large', '', true, $attrs );

    if ( $show_submit_post_note )
    {
      echo '<p><i>Note: this will save any post changes as well</i></p>';
    }

    $output = ob_get_contents();

    ob_end_clean();

    return $output;
  }

  final public function _render_hidden_content() : void
  {
    echo sprintf( '<input type="hidden" name="%s" value="%s">',
      esc_attr( $this->prefix_input_name( 'nonce' ) ),
      esc_attr( $this->create_nonce() )
    );
  }

  final public function prefix_input_name( string $input_name ) : string
  {
    return $this->prefix_arg_name( $input_name );
  }
}