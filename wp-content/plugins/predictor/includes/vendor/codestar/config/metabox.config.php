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
$options[]    = array(
    'id'        => 'event_ops',
    'title'     => 'Custom Event Options',
    'post_type' => 'event',
    'context'   => 'normal',
    'priority'  => 'default',
    'sections'  => array(
        array(
            'name' => 'options',
            'title' => 'Options',
            'icon'  => 'fa fa-cog',
            'fields' => array(
                array(
                    'type' => 'notice',
                    'class' => 'danger',
                    'content' => 'Enter section information and save. Then go to next tab',
                ),
                array(
                    'id'    => 'published',
                    'type'  => 'switcher',
                    'title' => 'Publish',
                ),
                array(
                    'id' => 'options',
                    'type' => 'group',
                    'title' => 'Options',
                    'desc' => 'Each section name should be unique',
                    'button_title' => 'Add New',
                    'accordion_title' => 'Add New section',
                    'fields' => array(
                        array(
                            'id' => 'title',
                            'type' => 'text',
                            'title' => 'Title',
                        ),
                        array(
                            'id' => 'weight',
                            'type' => 'weight',
                            'title' => 'Weight',
                        ),
                    ),
                ),
            ),
        ),
        array(
            'name' => 'answers',
            'title' => 'Answers',
            'fields' => predictor_answer_fields(),
            'icon'  => 'fa fa-user',
        ),
        array(
            'name' => 'predictions',
            'title' => 'Predictions',
            'fields' => advisory_generate_ihc_form_tables(),
            'icon'  => 'fa fa-user',
        ),
    ),
);
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