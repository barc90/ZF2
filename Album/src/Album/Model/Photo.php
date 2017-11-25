<?php
namespace Album\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Photo implements InputFilterAwareInterface
{
    public $id;
    public $album_id;
    public $title;
    public $address;
    public $image;
    public $thumb;
    public $created;

    protected $inputFilter;

    public function exchangeArray($data)
    {

        $this->id     = (isset($data['id'])) ? $data['id'] : null;
        $this->album_id  = (isset($data['album_id'])) ? $data['album_id'] : null;
        $this->title  = (isset($data['title'])) ? $data['title'] : null;
        $this->address  = (isset($data['address'])) ? $data['address'] : null;
        $this->image = (isset($data['image'])) ? $data['image'] : null;
        $this->thumb = (isset($data['thumb'])) ? $data['thumb'] : null;
        $this->created  = (isset($data['created'])) ? $data['created'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();


            $inputFilter->add($factory->createInput(array(
                'name'     => 'title',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 50,
                        ),
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'address',
                'required' => false,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 200,
                        ),
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'image',
                'required' => true,
                'validators' => array(
                    array(
                        'name'    => '\Zend\Validator\File\Extension',
                        'options' => array(
                            'extension' => 'gif,jpg,jpeg,png',
                            'messages' => array(
                                'fileExtensionFalse' => 'You not select image',
                            ),
                        ),
                    ),
                    array(
                        'name'    => 'FileSize',
                        'options' => array(
                            'max'      => 20 * 1024 * 1024, // 20MB
                            
                        ),
                    ),
                ),
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

}