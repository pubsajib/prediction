<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
// ===============================================================================================
// -----------------------------------------------------------------------------------------------
// METABOX OPTIONS
// -----------------------------------------------------------------------------------------------
// ===============================================================================================
$options      = array();
// -----------------------------------------
// Page Metabox Options                    -
// -----------------------------------------
// $options[] = array(
// 	'id' => 'date',
// 	'title' => 'Date Option',
// 	'post_type' => 'event',
// 	'context' => 'side',
// 	'priority' => 'default',
// 	'sections' => array(
// 		array(
// 			'name' => 'Date',
// 			'fields' =>  [
//                 ['id' => 'start', 'type'  => 'text', 'title' => 'Start Date'],
//             ]
// 		),
// 	),
// );
$options[]    = array(
    'id'        => 'event_ops',
    'title'     => 'Custom Event Options',
    'post_type' => 'event',
    'context'   => 'normal',
    'priority'  => 'default',
    'sections'  => array(
        array(
            'name' => 'teams',
            'title' => 'Match',
            'icon'  => 'fa fa-group',
            'fields' => array(
                ['type' => 'notice', 'class' => 'danger', 'content' => 'Enter section information and save. Then go to next tab'],
                ['id' => 'restricted', 'type'  => 'switcher', 'title' => 'Restricted content'],
                [
                    'id' => 'teams',
                    'type' => 'group',
                    'title' => 'Match',
                    'desc' => 'Each section name should be unique',
                    'button_title' => 'Add New',
                    'accordion_title' => 'Add New section',
                    'fields' => [
                        ['id' => 'name', 'type' => 'text', 'title' => 'Name'],
                        ['id' => 'end', 'type' => 'datetime', 'title' => 'End date'],
                        ['id' => 'subtitle', 'type' => 'text', 'title' => 'Sub title'],
                        ['id' => 'discussion', 'type' => 'text', 'title' => 'Discussion'],
                    ],
                ],
            ),
        ),
        ['name' => 'options', 'title' => 'Options', 'icon'  => 'fa fa-cog', 'fields' => predictor_option_fields()],
        ['name' => 'answers', 'title' => 'Answers', 'icon'  => 'fa fa-check-square', 'fields' => predictor_answer_fields()],
        ['name' => 'predictions', 'title' => 'Predictions', 'icon'  => 'fa fa-reply', 'fields' => prediction_answers()],
        // [
        //     'name' => 'remove', 
        //     'title' => 'Remove', 
        //     'icon'  => 'fa fa-ban', 
        //     'fields' => [
        //         ['type' => 'notice', 'class' => 'danger', 'content' => 'Enabling this option will remove event form calculation.'],
        //         ['id' => 'cremove', 'type'  => 'switcher', 'title' => 'Remove from calculation'],
        //     ]
        // ],
    ),
);
// $options[]    =  [
//     'id'        => 'date_ops',
//     'title'     => 'Custom Blog Options',
//     'post_type' => 'post',
//     'context'   => 'normal',
//     'priority'  => 'default',
//     'sections'  => [
//         [
//             'name' => 'date', 
//             'title' => 'Event Date', 
//             'icon'  => 'fa fa-check-square', 
//             'fields' => [
//                 ['id' => 'start', 'type' => 'datetime', 'title' => 'Start date'],
//                 ['id' => 'end', 'type' => 'datetime', 'title' => 'End date']
//             ]
//         ],
//     ],
// ];
CSFramework_Metabox::instance( $options );
class CSFramework_Option_weight extends CSFramework_Options {
    protected $defaults = '';
    public function __construct( $field, $value = '', $unique = '' ) {
        $this->defaults = $value;
        parent::__construct( $field, $value, $unique );
    }
  public function output(){
    echo $this->element_before();
    echo '<input type="text" style="width:48%; margin: 0 2% 10px 0;" name="'. $this->element_name() .'[0][name]" value="'. @$this->element_value()[0][name] .'"'. $this->element_class() . $this->element_attributes() .' placeholder="name" />';
    echo '<input type="text" style="width:48%; margin: 0 0 10px 2%;" name="'. $this->element_name() .'[0][value]" value="'. @$this->element_value()[0][value] .'"'. $this->element_class() . $this->element_attributes() .' placeholder="weight" />';
    echo '<br>';
    echo '<input type="text" style="width:48%; margin: 0 2% 10px 0;" name="'. $this->element_name() .'[1][name]" value="'. @$this->element_value()[1][name] .'"'. $this->element_class() . $this->element_attributes() .' placeholder="name" />';
    echo '<input type="text" style="width:48%; margin: 0 0 10px 2%;" name="'. $this->element_name() .'[1][value]" value="'. @$this->element_value()[1][value] .'"'. $this->element_class() . $this->element_attributes() .' placeholder="weight" />';
    echo '<br>';
    echo '<input type="text" style="width:48%; margin: 0 2% 10px 0;" name="'. $this->element_name() .'[2][name]" value="'. @$this->element_value()[2][name] .'"'. $this->element_class() . $this->element_attributes() .' placeholder="name" />';
    echo '<input type="text" style="width:48%; margin: 0 0 10px 2%;" name="'. $this->element_name() .'[2][value]" value="'. @$this->element_value()[2][value] .'"'. $this->element_class() . $this->element_attributes() .' placeholder="weight" />';
    echo $this->element_after();
  }
}
class CSFramework_Option_datetime extends CSFramework_Options {
    protected $defaults = '';
    public function __construct( $field, $value = '', $unique = '' ) {
        $this->defaults = $value;
        parent::__construct( $field, $value, $unique );
    }
  public function output(){
    echo $this->element_before();
    echo '<input type="datetime-local" name="'. $this->element_name() .'" value="'. @$this->element_value() .'"'. $this->element_class() . $this->element_attributes() .'/>';
    echo $this->element_after();
  }
}