<?php
/*
Plugin Name: Proud Issue
Plugin URI: http://proudcity.com/
Description: Declares an Issue custom post type.
Version: 1.0
Author: ProudCity
Author URI: http://proudcity.com/
License: Affero GPL v3
*/

namespace Proud\Issue;

// Load Extendible
// -----------------------
if ( ! class_exists( 'ProudPlugin' ) ) {
  require_once( plugin_dir_path(__FILE__) . '../wp-proud-core/proud-plugin.class.php' );
}

class ProudIssue extends \ProudPlugin {

  public function __construct() {

    $this->hook( 'init', 'create_issue' );
    $this->hook( 'admin_enqueue_scripts', 'agency_assets' );
    $this->hook( 'rest_api_init', 'issue_rest_support' );
  }

  //add assets
  public function agency_assets() {
    $path = plugins_url('assets/',__FILE__);
    wp_enqueue_script('proud-issue/js', $path . 'js/proud-issue.js', ['proud','jquery'], null, true);
  }


  public function create_issue() {
      $labels = array(
          'name'               => _x( 'Issues', 'post name', 'wp-issue' ),
          'singular_name'      => _x( 'Issue', 'post type singular name', 'wp-issue' ),
          'menu_name'          => _x( 'Issues', 'admin menu', 'wp-issue' ),
          'name_admin_bar'     => _x( 'Issue', 'add new on admin bar', 'wp-issue' ),
          'add_new'            => _x( 'Add New', 'issue', 'wp-issue' ),
          'add_new_item'       => __( 'Add New Issue', 'wp-issue' ),
          'new_item'           => __( 'New Issue', 'wp-issue' ),
          'edit_item'          => __( 'Edit Issue', 'wp-issue' ),
          'view_item'          => __( 'View Issue', 'wp-issue' ),
          'all_items'          => __( 'All Issues', 'wp-issue' ),
          'search_items'       => __( 'Search issue', 'wp-issue' ),
          'parent_item_colon'  => __( 'Parent issue:', 'wp-issue' ),
          'not_found'          => __( 'No issues found.', 'wp-issue' ),
          'not_found_in_trash' => __( 'No issues found in Trash.', 'wp-issue' )
      );

      $args = array(
          'labels'             => $labels,
          'description'        => __( 'Description.', 'wp-issue' ),
          'public'             => true,
          'publicly_queryable' => true,
          'show_ui'            => true,
          'show_in_menu'       => true,
          'query_var'          => true,
          'rewrite'            => array( 'slug' => 'issues' ),
          'capability_type'    => 'post',
          'has_archive'        => false,
          'hierarchical'       => false,
          'menu_position'      => null,
          'show_in_rest'       => true,
          'rest_base'          => 'issues',
          'rest_controller_class' => 'WP_REST_Posts_Controller',
          'supports'           => array( 'title', 'editor' )
      );

      register_post_type( 'issue', $args );
  }

  public function issue_rest_support() {
    register_rest_field( 'issue',
          'meta',
          array(
              'get_callback'    => array( $this, 'issue_rest_metadata' ),
              'update_callback' => null,
              'schema'          => null,
          )
      );
  }

  /**
   * Alter the REST endpoint.
   * Add metadata to t$forms = RGFormsModel::get_forms( 1, 'title' );he post response
   */
  public function issue_rest_metadata( $object, $field_name, $request ) {
    $IssueMeta = new IssuesMeta;
    return $IssueMeta->get_options( $object[ 'id' ] );
  }

} // class
$Issue = new ProudIssue;


// Issues meta box
class IssuesMeta extends \ProudMetaBox {

  public $options = [  // Meta options, key => default                             
    'icon' => '',
    'issue_category_type' => '',
    'form' => '',
    'url' => '',
    'iframe' => '',
  ];

  public function __construct() {
    parent::__construct( 
      'issue', // key
      'Issue information', // title
      'issue', // screen
      'normal',  // position
      'high' // priority
    );
  }

  /**
   * Called on form creation
   * @param $displaying : false if just building form, true if about to display
   * Use displaying:true to do any difficult loading that should only occur when
   * the form actually will display
   */
  public function set_fields( $displaying ) {

    // Already set, no loading necessary
    if( $displaying ) {
      return;
    }

    $this->fields = [];

    $this->fields['icon'] = [
      '#type' => 'fa-icon',
      '#title' => __('Icon'),
      '#description' => __('Select the icon to use in the Actions app'),
    ];

    $this->fields['issue_category_type'] = [
      '#type' => 'radios',
      '#title' => __('Type'),
      '#options' => [
        'form' => __('Form'),
        'iframe' => __('Iframe'),
        'link' => __('External link'),
        'custom' => __('Custom text'),
      ],
    ];

    $this->fields['form'] = [
      '#type' => 'gravityform',
      '#title' => __('Form'),
      '#description' => __('Select a form. <a href="admin.php?page=gf_edit_forms" target="_blank">Create a new form</a>.'),
      '#states' => [
        'visible' => [
          'issue_category_type' => [
            'operator' => '==',
            'value' => ['form'],
            'glue' => '||'
          ],
        ],
      ],
    ];

    $this->fields['url'] = [
      '#type' => 'text',
      '#title' => __('Link URL'),
      '#states' => [
        'visible' => [
          'issue_category_type' => [
            'operator' => '==',
            'value' => ['link'],
            'glue' => '||'
          ],
        ],
      ],
    ];

    $this->fields['iframe'] = [
      '#type' => 'text',
      '#title' => __('Iframe URL'),
      '#description' => __('Enter the URL for the Iframe (the src attribute). Only applies if form is blank.'),
      '#states' => [
        'visible' => [
          'issue_category_type' => [
            'operator' => '==',
            'value' => ['iframe'],
            'glue' => '||'
          ],
        ],
      ],
    ];
  }

  /**
   * Displays the Issues metadata fieldset.
   */
  public function settings_content( $post ) {
    // Call parent
    parent::settings_content( $post );
    // Add js settings
    global $proudcore;
    $settings = $this->get_field_names( ['issue_category_type'] );
    $proudcore->addJsSettings( [
      'proud_issue' => $settings
    ] );
  }
}

if( is_admin() )
  new IssuesMeta;
