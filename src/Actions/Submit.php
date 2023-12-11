<?php
namespace Cvy\WP\Metaboxes\Actions;

/**
 * Represents a metabox submit action.
 */
abstract class Submit extends Action
{
  /**
   * @override
   */
  final protected function on_handled() : void {}

  /**
   * Generates and retrieves the HTML for the submit button.
   *
   * @param string $label The label for the submit button.
   * @param array $attrs Additional attributes for the button tag.
   * @param bool $show_submit_post_note Whether to show a note about saving post changes.
   */
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

  /**
   * Renders the hidden content needed for the action to be handled properly.
   */
  final public function render_hidden_content() : void
  {
    echo sprintf( '<input type="hidden" name="%s" value="%s">',
      esc_attr( $this->prefix_input_name( 'nonce' ) ),
      esc_attr( $this->create_nonce() )
    );
  }

  /**
   * Prefixes an input name with the action's name.
   *
   * You need to prefix your input names associated with this action to make
   * ::is_submitted(), ::get_args(), etc work properly.
   *
   * @param string $unprefixed_name The unprefixed name of the input.
   */
  final public function prefix_input_name( string $unprefixed_name ) : string
  {
    return $this->prefix_arg_name( $unprefixed_name );
  }
}