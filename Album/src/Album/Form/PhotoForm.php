<?php
namespace Album\Form;

use Zend\Form\Form;

class PhotoForm extends Form
{
    public function __construct($show_dropdown = false, array $options = array())
    {
        // we want to ignore the name passed
        parent::__construct('photo');
        
        $this->setAttribute('method', 'post');


        if ($show_dropdown)
        {

            $this->add(array(
                'name' => 'album_id',
                'type' => 'Select',
                'options' => array(
                     'label' => 'Album:',
                    // 'empty_option' => 'Please choose album',
                     'value_options' => $options
                )
            ));
        }


        $this->add(array(
            'name' => 'id',
            'type' => 'Hidden',
        ));
        $this->add(array(
            'name' => 'title',
            'type' => 'Text',
            'options' => array(
                'label' => 'Title',
            ),
        ));
        $this->add(array(
            'name' => 'address',
            'type' => 'Textarea',
            'options' => array(
                'label' => 'Address',
            ),
        ));

        $this->add(array(
            'name' => 'image',
            'type' => 'File',
            'options' => array(
                'label' => 'File',
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Go',
                'id' => 'submitbutton',
            ),
        ));
    }
}