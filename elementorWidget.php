<?php

class Team_Elementor extends \Elementor\Widget_Base {

  /**
   * Get widget name.
   *
   * @return string Widget name.
   */
  public function get_name() {
    return 'team-elementor';
  }

  /**
   * Get widget title.
   *
   * @return string Widget title.
   */
  public function get_title() {
    return __('Team', 'twd');
  }

  /**
   * Get widget icon.
   *
   * @return string Widget icon.
   */
  public function get_icon() {
    return 'twbb-team twbb-widget-icon';
  }

  /**
   * Get widget categories.
   *
   * @return array Widget categories.
   */
  public function get_categories() {
    return [ 'tenweb-plugins-widgets' ];
  }

  /**
   * Register widget controls.
   */
  protected function _register_controls() {
    $twd_shortcode_views = twd_get_info_for_controls()['views'];
    $categories = twd_get_info_for_controls()['contact_category'];
    $contacts = twd_get_info_for_controls()['contacts'];

    $this->start_controls_section(
      'twd_general',
      [
        'label' => __('Team', 'twd'),
      ]
    );
    if ( $this->get_id() !== null ) {
      $settings = $this->get_init_settings();
    }
    $twd_edit_link = add_query_arg(array( 'post_type' => 'contact'), admin_url('edit.php'));
    $twd_categories_link = add_query_arg(array( 'taxonomy' => 'cont_category','post_type' => 'contact'), admin_url('edit-tags.php'));
    if ( isset($settings) && isset($settings["twd_single_contact"]) && intval($settings["twd_single_contact"]) > 0 ) {
      $twd_id = intval($settings["twd_single_contact"]);
      $twd_edit_link = add_query_arg(array( 'post' => $twd_id, 'action' => 'edit' ), admin_url('post.php'));
    }

    $this->add_control(
      'twd_single_or_list',
      [
        'label' => __( 'Select Template', 'twd' ),
        'label_block' => TRUE,
        'show_label' => TRUE,
        'description' => '',
        'multiple' => TRUE,
        'type' => \Elementor\Controls_Manager::SELECT,
        'default' => 'single_contact',
        'options' => [
          'single_contact' => __( 'Single Contact', 'tenweb-builder' ),
          'contacts_list' => __( 'Contacts List', 'tenweb-builder' ),
        ]
      ]
    );

    $this->add_control(
      'twd_single_contact',
      [
        'label' => __( 'Select Contact', 'twd' ),
        'show_label' => TRUE,
        'description' =>  __('Select the contact to display.', 'twd' ) . ' <a target="_blank" href="' . $twd_edit_link . '">' . __('Edit contact', 'twd') . '</a>',
        'multiple' => TRUE,
        'type' => \Elementor\Controls_Manager::SELECT,
        'default' => array_keys( $contacts )[0],
        'options' => $contacts,
        'condition' => [
          'twd_single_or_list' => 'single_contact',
        ]
      ]
    );

    $this->add_control(
      'twd_cats',
      [
        'label' => __( 'Select Category', 'twd' ),
        'show_label' => TRUE,
        'description' => __('Select the categories to display.', 'twd' ) . ' <a target="_blank" href="' . $twd_categories_link . '">' . __('Edit categories', 'twd') . '</a>',
        'multiple' => TRUE,
        'type' => \Elementor\Controls_Manager::SELECT2,
        'default' => array_keys($categories)[0],
        'options' => $categories,
        'condition' => [
          'twd_single_or_list' => 'contacts_list',
        ]
      ]
    );

    $this->add_control(
      'twd_type',
      [
        'label' => __( 'View Type', 'twd' ),
        'show_label' => TRUE,
        'description' => '',
        'multiple' => TRUE,
        'type' => \Elementor\Controls_Manager::SELECT,
        'default' => 'full',
        'options' => $twd_shortcode_views,
        'condition' => [
          'twd_single_or_list' => 'contacts_list',
        ]
      ]
    );

    $this->end_controls_section();
  }

  /**
   * Render widget output on the frontend.
   */
  protected function render() {
    $settings = $this->get_settings_for_display();
      $atts = [];
    if ( $settings['twd_single_or_list'] == 'single_contact' ) {
      $atts['id'] = (string)$settings[ 'twd_single_contact' ];
    } else {
      if( !is_array($settings['twd_cats']) ) {
        $atts['cats'] = (string)$settings['twd_cats'];
      } else {
        $atts['cats'] = implode( ',',$settings['twd_cats'] );
      }
      $atts['type'] = $settings['twd_type'];
      $atts['order'] = "undefined";
      $atts['tab'] = "1";
      if( $atts['type'] != 'full' ) {
        echo __('In Free version available only FULL view.', 'twd' );
        return;
      }
    }
    echo contShortcodeHandler($atts);
 	}
}

\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Team_Elementor() );