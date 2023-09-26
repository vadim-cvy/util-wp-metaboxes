# Boilerplate code

## Simple metabox (just display some data)

```php
// VipRoomSimpleMetabox.php

class VipRoomSimpleMetabox extends \Cvy\WP\Metaboxes\PostMetabox
{
  protected function get_post_types() : array
  {
    return [ 'room' ];
  }

  protected function get_slug() : string
  {
    return 'vip_room'
  }

  protected function get_title() : string
  {
    return 'VIP Room';
  }

  protected function enqueue_assets() : void
  {
    // wp_enqueue_script( ... );
  }

  protected function display() : void
  {
    $my_var = 'My var value';

    require_once YOUR_TEMPLATES_DIR . 'vip-room-metabox_simple.php';
  }

  protected function is_disabled_on_current_post() : bool
  {
    return ! get_post_meta( $this->get_post_id(), 'is_vip', true );
  }

  protected function is_user_authorized() : bool
  {
    return current_user_can( 'administrator' );
  }

  // protected function get_context() : string
  // {
  //   return 'normal';
  // }

  // protected function get_priority() : string
  // {
  //   return 'high';
  // }
}
```

```php
// YOUR_TEMPLATES_DIR . 'vip-room-metabox_simple.php'

<h2 class="<?php echo esc_attr( $this->prefix_css_selector( 'title' ) ); ?>">
  Just a simple metabox
</h2>

<p class="<?php echo esc_attr( $this->prefix_css_selector( 'text-wrapper' ) ); ?>">
  The value of $my_var is "<?php echo $my_var; ?>".
</p>
```

```php
// init.php

VipRoomSimpleMetabox::get_instance();
```

## Metabox with SUBMIT Action

```php
// VipRoomSubmittableMetabox.php

class VipRoomSubmittableMetabox extends \Cvy\WP\Metaboxes\PostMetabox
{
  // ... copy and paste the VipRoomSimpleMetabox code here (is implemented above).

  protected function __construct()
  {
    add_action( 'admin_notices', fn() => $this->maybe_display_info_notice() );

    parent::__construct();
  }

  protected function maybe_display_info_notice() : void
  {
    if ( ! $this->can_register() )
    {
      return;
    }

    $price_multiplier = $this->get_price_multiplier_value();
    $recommended_price_multiplier = 1.3;

    if ( $price_multiplier < $recommended_price_multiplier )
    {
      $this->add_info_admin_notice(sprintf(
        'It is recommended to keep price multiplier >= %f. Cur value is %f',
        $recommended_price_multiplier,
        $price_multiplier
      ));
    }
  }

  protected function display() : void
  {
    $price_multiplier = $this->get_price_multiplier_value();

    // ... other input values

    $submit_html = $this->get_submit_html( 'Save', [
      'class' => 'my-custom-class-for-submit-button foo bar',
    ]);

    require_once YOUR_TEMPLATES_DIR . 'vip-room-submittable-metabox.php';
  }

  protected function handle_action__submit() : void
  {
    $new_price_multiplier = $this->get_request_arg( 'price_multiplier' );

    // ... other inputs + validation

    if ( ! $api_connected )
    {
      $this->die( 'Can\'t connect to {some} API! Credentials missed.' );
    }

    if ( $validation_error_msg )
    {
      $this->add_error_admin_notice( 'Error: ' . $validation_error_msg ) :
    }
    else
    {
      $this->add_success_admin_notice( 'Success message here.' );

      update_post_meta( $this->get_post_id(), 'price_multiplier', $new_price_multiplier );

      // ... update other inputs
    }
  }

  protected function get_price_multiplier_value() : float
  {
    return get_post_meta( $this->get_post_id(), 'price_multiplier', true ) || 0;
  }
}
```

```php
// YOUR_TEMPLATES_DIR . 'vip-room-submittable-metabox.php'

printf ( '<label class="%s">', esc_attr( $this->prefix_css_selector( 'label' ) ) );

echo 'Price Multiplier:';

printf( '<input type="number" name="%s" value="%s" step="0.1">',
  esc_attr( $this->prefix_input_name( 'price_multiplier' ) )
  esc_attr( $price_multiplier )
);

echo '</label>';

// ... Other inputs

echo $submit_html;
```

```php
// init.php

VipRoomSubmittableMetabox::get_instance();
```

## Metabox with LINK Action

```php
// VipRoomLinkActionMetabox.php

class VipRoomLinkActionMetabox extends \Cvy\WP\Metaboxes\PostMetabox
{
  // ... copy and paste the VipRoomSimpleMetabox code here (see "Metabox with no action" above).

  protected function __construct()
  {
    add_action( 'admin_notices', fn() => $this->maybe_display_info_notice() );

    parent::__construct();
  }

  protected function maybe_display_info_notice() : void
  {
    if ( ! $this->can_register() )
    {
      return;
    }

    $refund_requests_number = count( $this->get_refund_requests() );

    if ( $refund_requests_number > 0 )
    {
      $this->add_info_admin_notice(
        "Action required: there are $refund_requests_number refund requests for this room."
      );
    }
  }

  protected function display() : void
  {
    $table = $this->get_refund_requests_table();

    require_once YOUR_TEMPLATES_DIR . 'vip-room-link-action-metabox.php';
  }

  protected function get_refund_requests_table() : array
  {
    $table_heading_row = [
      'id' => 'ID',
      'reason' => 'Reason',
      // ... other headings
      'actions' => 'Actions',
    ];

    $table_data_rows = [];

    foreach ( $this->get_refund_requests() as $request )
    {
      $row = [];

      foreach ( array_keys( $table_heading_row ) as $cell_key )
      {
        switch ( $cell_key )
        {
          case 'id':
            $row[ $cell_key ] = '#' . $request->id;
            break;

          // ... other cases

          case 'actions':
            $url_args = [ 'request_id' => $request->id ];

            $tag_attrs = [
              'on-click' => 'e =>
                ! confirm( \'Are you sure you want to confirm this request?\' ) ?
                e.preventDefault() :
                null',

              'class' => 'button button-primary',
            ];

            $row[ $cell_key ] =
              $this->get_action_link_tag( 'confirm_refund_request', 'Confirm', $url_args, $tag_attrs );

            // ... $row[ $cell_key ] .= other links

            break;
        }
      }

      $table_data_rows[] = $row;
    }

    return array_merge(
      [ $table_heading_row ],
      $table_data_rows
    );
  }

  protected function handle_action__confirm_refund_request() : void
  {
    $request_id = $this->get_request_arg( 'request_id' );

    // ... validate request exists

    if ( ! $is_request_found )
    {
      $this->die( "Can't find request #$request_id!" );
    }

    // ... perform refund request

    if ( $error_msg )
    {
      $this->add_error_admin_notice( 'Error: ' . $error_msg ) :
    }
    else
    {
      $this->add_success_admin_notice( 'Success message here.' );
    }
  }

  protected function get_refund_requests() : float
  {
    global $wpdb;

    return $wpdb->get_results($wpdb->prepare(
      // ... SQL query + args
    ));
  }
}
```

```php
// YOUR_TEMPLATES_DIR . 'vip-room-link-action-metabox.php'

printf ( '<table id="%s">', esc_attr( $this->prefix_css_selector( 'refund-requests-table' ) ) );

foreach ( $table as $row_index => $row )
{
  echo '<tr>';

  foreach ( $row as $cell_key => $cell_value )
  {
    $cell_tag = $row_index === 0 ? 'th' : 'td';

    $cell_class = $this->prefix_css_selector( 'refund-requests-table__cell_' . $cell_key );

    printf( '<%s class="%s">%s</%s>',
      $cell_tag,
      esc_attr( $cell_class ),
      $cell_value,
      $cell_tag
    );
  }

  echo '</tr>';
}

echo '</table>';
```

```php
// init.php

VipRoomLinkActionMetabox::get_instance();
```