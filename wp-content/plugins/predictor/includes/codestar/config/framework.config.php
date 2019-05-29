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
    'name' => 'cron',
    'title' => 'CRON',
    'icon' => 'fa fa-star',
    'fields' => array(
        [
            'id' => 'cron',
            'type' => 'group',
            'title' => 'CRON',
            'button_title' => 'Add New',
            'accordion_title' => 'Add New Rule',
            'fields' => [
                ['id' => 'name', 'type' => 'text', 'title' => 'Name'],
                ['id' => 'id', 'type' => 'text', 'title' => 'ID'],
                ['id' => 'tournament', 'type' => 'text', 'title' => 'Tournament ID'],
                ['id' => 'participation', 'type' => 'text', 'title' => 'Participation'],
                ['id' => 'grace', 'type' => 'text', 'title' => 'Grace'],
                ['id' => 'engagement', 'type' => 'text', 'title' => 'Engagement'],
                ['id' => 'm_participation', 'type' => 'text', 'title' => 'Match Participation'],
                ['id' => 'm_grace', 'type' => 'text', 'title' => 'Match Grace'],
                ['id' => 'm_engagement', 'type' => 'text', 'title' => 'Match Engagement'],
                ['id' => 't_participation', 'type' => 'text', 'title' => 'Toss Participation'],
                ['id' => 't_grace', 'type' => 'text', 'title' => 'Toss Grace'],
                ['id' => 't_engagement', 'type' => 'text', 'title' => 'Toss Engagement'],
                ['id' => 'is_active', 'type' => 'switcher', 'title' => 'Active'],
            ]
        ],
    ),
);
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
$options[] = array(
    'name' => 'classes',
    'title' => 'Classes',
    'icon' => 'fa fa-star',
    'fields' => array(
        [
            'id' => 'classes',
            'type' => 'group',
            'title' => 'Classes',
            'button_title' => 'Add New',
            'accordion_title' => 'Add New Class',
            'fields' => [
                ['id' => 'name', 'type' => 'text', 'title' => 'Name'],
                ['id' => 'participated', 'type' => 'text', 'title' => 'participated'],
                ['id' => 'joined', 'type' => 'text', 'title' => 'joined'],
                ['id' => 'engagement', 'type' => 'text', 'title' => 'engagement'],
                ['id' => 'accuricy', 'type' => 'text', 'title' => 'accuricy'],
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