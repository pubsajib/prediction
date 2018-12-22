<?php if ( ! defined( 'ABSPATH' ) ) { die; }
$settings  = array(
    'menu_title' => 'Prediction Options',
    'menu_type' => 'menu',
    'menu_slug' => 'prediction-options',
    'ajax_save' => false,
    'show_reset_all' => false,
    'framework_title' => 'Prediction Options',
    'menu_capability' => 'manage_options',
);
$options = [];
$options[] = array(
    'name' => 'general',
    'title' => 'General',
    'icon' => 'fa fa-star',
    'fields' => array(
        [
            'id' => 'criteria_event',
            'type' => 'group',
            'title' => 'Event Criteria',
            'button_title' => 'Add New',
            'accordion_title' => 'Add New Criteria',
            'fields' => [
                ['id' => 'name', 'type' => 'text', 'title' => 'Name']
            ],
            'default' => [
                ['name' => 'Match'],
                ['name' => 'Toss'],
                ['name' => 'Draw'],
                ['name' => 'Others']
            ]
        ],
    ),
);
// BACKUP
$options[] = array(
    'name' => 'backup_section',
    'title' => 'Backup',
    'icon' => 'fa fa-shield',
    'fields' => [
        [
            'type' => 'notice',
            'class' => 'warning',
            'content' => 'You can save your current options. Download a Backup and Import.'
        ],
        ['type' => 'backup'],
    ],
);

CSFramework::instance( $settings, $options );