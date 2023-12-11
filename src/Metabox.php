<?php
namespace Cvy\WP\Metaboxes;
use \Cvy\WP\Metaboxes\Notices\NoticesManager;
use \Exception;

/**
 * Base class for creating WordPress metaboxes.
 */
abstract class Metabox extends \Cvy\DesignPatterns\Singleton
{
  /**
   * @var array Holds action instances associated with the metabox.
   */
  private array $actions;

  /**
   * @var NoticesManager Manages notices specific to the metabox.
   */
  private NoticesManager $notices_manager;

  /**
   * Metabox constructor.
   */
  final protected function __construct()
  {
    add_action( 'current_screen', fn() => $this->maybe_init() );
  }

  /**
   * Initializes the metabox if all the conditions are matched.
   */
  private function maybe_init() : void
  {
    if ( $this->is_current_screen_eligable() && $this->is_current_user_authorized() )
    {
      $this->init();
    }
  }

  /**
   * Initializes the metabox.
   */
  protected function init() : void
  {
    $this->register();

    $this->set_actions();
  }

  /**
   * Registers the metabox.
   */
  abstract protected function register() : void;

  /**
   * Retrieves the NoticesManager instance for the metabox.
   */
  final public function get_notices_manager() : NoticesManager
  {
    if ( ! isset( $this->notices_manager ) )
    {
      $this->notices_manager = new NoticesManager( $this->get_slug() );
    }

    return $this->notices_manager;
  }

  /**
   * Checks if the current screen is eligible for the metabox.
   */
  abstract protected function is_current_screen_eligable() : bool;

  /**
   * Checks if the current user is authorized to view the metabox and perform actions.
   */
  abstract protected function is_current_user_authorized() : bool;

  /**
   * Retrieves the unique slug for the metabox.
   */
  abstract public function get_slug() : string;

  /**
   * Retrieves the title of the metabox.
   */
  abstract protected function get_title() : string;

  /**
   * Renders the metabox content.
   */
  final protected function render() : void
  {
    ob_start();

    $is_success = $this->render_inner_content();

    if ( $is_success )
    {
      foreach ( $this->get_actions() as $action )
      {
        if ( is_a( $action, Actions\Submit::class ) )
        {
          $action->render_hidden_content();
        }
      }
    }
    else
    {
      echo '<b>Error! Can\'t render this content.</b>';
    }

    $content = ob_get_contents();

    ob_end_clean();

    printf( '<div class="%s-metabox-content">%s</div>',
      esc_attr( $this->get_slug() ),
      $content
    );
  }

  /**
   * Renders the inner content of the metabox.
   */
  abstract protected function render_inner_content() : bool;

  /**
   * Retrieves the current object ID associated with the metabox.
   */
  abstract public function get_current_object_id() : int;

  /**
   * Retrieves the current object type (post/term/user).
   */
  abstract public function get_current_object_type() : string;

  /**
   * Retrieves action instances associated with the metabox.
   *
   * @return array Associative array: action_name => action_instance.
   */
  final protected function get_actions() : array
  {
    return $this->actions;
  }

  /**
   * Sets up action instances associated with the metabox.
   */
  private function set_actions() : void
  {
    if ( ! isset( $this->actions ) )
    {
      $this->actions = [];

      foreach ( $this->generate_action_instances() as $action )
      {
        $this->actions[ $action->get_name_base() ] = $action;
      }
    }
    else
    {
      throw new Exception( 'Actions are alredy set!' );
    }

    $this->actions;
  }

  /**
   * Generates action instances associated with the metabox.
   *
   * You MUST use get_actions() instead if your goal is just to retrieve actions
   * associated with the metabox.
   */
  abstract protected function generate_action_instances() : array;
}