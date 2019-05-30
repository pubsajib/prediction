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
    'name' => 'classes',
    'title' => 'Classes',
    'icon' => 'fa fa-plus-circle',
    'fields' => array(
        [
            'id' => 'classes',
            'type' => 'group',
            'title' => 'Classes',
            'button_title' => 'Add New',
            'accordion_title' => 'Add New Class',
            'fields' => [
                ['id' => 'name', 'type' => 'text', 'title' => 'Name'],
                ['id' => 'id', 'type' => 'text', 'title' => 'ID'],
                ['id' => 'participated', 'type' => 'number', 'title' => 'Participated'],
                ['id' => 'engagement', 'type' => 'number', 'title' => 'Engagement'],
                ['id' => 'accuricy', 'type' => 'number', 'title' => 'Accuricy'],
                ['id' => 'm_participated', 'type' => 'number', 'title' => 'Match Participated'],
                ['id' => 'm_engagement', 'type' => 'number', 'title' => 'Match Engagement'],
                ['id' => 'm_accuricy', 'type' => 'number', 'title' => 'Match Accuricy'],
                ['id' => 't_participated', 'type' => 'number', 'title' => 'Toss Participated'],
                ['id' => 't_engagement', 'type' => 'number', 'title' => 'Toss Engagement'],
                ['id' => 't_accuricy', 'type' => 'number', 'title' => 'Toss Accuricy'],
                ['id' => 'is_active', 'type' => 'switcher', 'title' => 'Active'],
            ]
        ],
    ),
);
$options[] = array(
    'name' => 'cron',
    'title' => 'CRON',
    'icon' => 'fa fa-toggle-on',
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
                ['id' => 'tournament', 'type' => 'number', 'title' => 'Tournament ID'],
                ['id' => 'participation', 'type' => 'number', 'title' => 'Participation'],
                ['id' => 'grace', 'type' => 'number', 'title' => 'Grace'],
                ['id' => 'engagement', 'type' => 'number', 'title' => 'Engagement'],
                ['id' => 'm_participation', 'type' => 'number', 'title' => 'Match Participation'],
                ['id' => 'm_grace', 'type' => 'number', 'title' => 'Match Grace'],
                ['id' => 'm_engagement', 'type' => 'number', 'title' => 'Match Engagement'],
                ['id' => 't_participation', 'type' => 'number', 'title' => 'Toss Participation'],
                ['id' => 't_grace', 'type' => 'number', 'title' => 'Toss Grace'],
                ['id' => 't_engagement', 'type' => 'number', 'title' => 'Toss Engagement'],
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