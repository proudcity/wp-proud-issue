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
    $this->hook( 'admin_init', 'issue_admin' );
    $this->hook( 'admin_enqueue_scripts', 'agency_assets' );
    $this->hook( 'save_post', 'add_issue_fields', 10, 2 );
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

  public function issue_admin() {
    add_meta_box( 'issue_meta_box',
      'Issue information',
      array($this, 'display_issue_meta_box'),
      'issue', 'normal', 'high'
    );
  }

  public function issue_rest_support() {
    register_api_field( 'issue',
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
      $return = array();
      $this->build_fields($object[ 'id' ]);
      foreach ($this->fields as $key => $field) {
        if ($value = get_post_meta( $object[ 'id' ], $key, true )) {
          $return[$key] = $value;
        }
      }
      return $return;
  }

  public function build_fields($id) {
    $this->fields = [];

    $this->fields['icon'] = [
      '#type' => 'fa-icon',
      '#title' => __('Icon'),
      '#description' => __('Select the icon to use in the Actions app'),
      '#name' => 'icon',
      '#value' => get_post_meta( $id, 'icon', true ),
    ];

    $this->fields['issue_category_type'] = [
      '#type' => 'radios',
      '#title' => __('Type'),
      '#name' => 'issue_category_type',
      '#options' => [
        'form' => _x('Form'),
        'iframe' => _x('Iframe'),
        'link' => _x('External link'),
      ],
      '#value' => get_post_meta( $id, 'issue_category_type', true ),
    ];

    $this->fields['form'] = [
      '#type' => 'gravityform',
      '#title' => _x('Form'),
      '#description' => __('Select a form. <a href="admin.php?page=gf_edit_forms" target="_blank">Create a new form</a>.'),
      '#name' => 'form',
      '#value' => get_post_meta( $id, 'form', true ),
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
      '#title' => _x('Link URL'),
      '#name' => 'url',
      '#value' => get_post_meta( $id, 'url', true ),
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
      '#title' => _x('Iframe URL'),
      '#description' => __('Enter the URL for the Iframe (the src attribute). Only applies if form is blank.'),
      '#name' => 'iframe',
      '#value' => get_post_meta( $id, 'iframe', true ),
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

    return $this->fields;
  }


  public function display_issue_meta_box( $issue ) {
    $this->build_fields($issue->ID);
    $form = new \Proud\Core\FormHelper( $this->key, $this->fields );
    $form->printFields();
  }

  /**
   * Saves contact metadata fields 
   */
  public function add_issue_fields( $id, $issue ) {
    if ( $issue->post_type == 'issue' ) {
      foreach ($this->build_fields($id) as $key => $field) {
        if ( !empty( $_POST[$key] ) ) {  // @todo: check if it has been set already to allow clearing of value
          update_post_meta( $id, $key, $_POST[$key] );
        }
      }
    }
  }


} // class
$Issue = new ProudIssue;
