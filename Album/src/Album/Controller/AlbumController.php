<?php

namespace Album\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Album\Model\Album;     
use Album\Model\Photo;  

use Album\Form\AlbumForm;      
use Album\Form\PhotoForm; 
use Matveev\SimpleImage;

class AlbumController extends AbstractActionController
{
    
    protected $albumTable; // album(SQL table)
    protected $photoTable; // photo(SQL table)

    public function indexAction()
    {
        return array(
            'albums' => $this->getAlbumTable()->fetchAll(),
        );
    }

    public function addalbumAction()
    {
        $form = new AlbumForm();
        $form->get('submit')->setValue('Add album');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $album = new Album();
            $form->setInputFilter($album->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $album->exchangeArray($form->getData());
                $this->getAlbumTable()->saveAlbum($album);

                // Redirect to list of albums
                return $this->redirect()->toRoute('album');
            }
        }
        return array('form' => $form);
    }


    public function editalbumAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('album', array(
                'action' => 'add'
            ));
        }
        // Get the Album with the specified id.  An exception is thrown
        // if it cannot be found, in which case go to the index page.
        try {
            $album = $this->getAlbumTable()->getAlbum($id);
        }
        catch (\Exception $ex) {
            return $this->redirect()->toRoute('album', array(
                'action' => 'index'
            ));
        }

        $form  = new AlbumForm();

        $form->bind($album);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($album->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getAlbumTable()->saveAlbum($form->getData());

                // Redirect to list of albums
                return $this->redirect()->toRoute('album');
            }
        }

        return array(
            'id' => $id,
            'form' => $form,
        );
    }

    public function deletealbumAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            die($id);
            return $this->redirect()->toRoute('album');
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $photos = $this->getPhotoTable()->fetchAllWhere($id);
                foreach ($photos as $photo) {
                    unlink(ROOT_PATH . IMG_PATH . $photo->image);
                    unlink(ROOT_PATH . '/' . IMG_PATH_THUMBS . $photo->thumb);    
                }
                $this->getAlbumTable()->deleteAlbum($id);
                $res = $this->getAlbumTable()->deletePhotosFromAlbum($id);
             
                // Redirect to list of albums
                return $this->redirect()->toRoute('album');
            } 
        }
        return array(
                'id'    => $id,
                'album' => $this->getAlbumTable()->getAlbum($id)
        );
    }
    public function deletephotoAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);

        if (!$id) {
            return $this->redirect()->toRoute('album');
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $photo = $this->getPhotoTable()->getPhoto($id);
                unlink(ROOT_PATH . IMG_PATH . $photo->image);
                unlink(ROOT_PATH . '/' . IMG_PATH_THUMBS . $photo->thumb);
                $this->getPhotoTable()->deletePhoto($id);
            }

            // Redirect to list of albums
            return $this->redirect()->toRoute('album');
        }

        return array(
            'id'    => $id,
            'photo' => $this->getPhotoTable()->getPhoto($id)
        );
    }

    public function getDropdownOptions()
    {
        $options = array();
        $albums = $this->getAlbumTable()->getAlbumDropdownOptions();
        foreach ($albums as $album) 
        {
            $options[$album['id']] = $album['title'];
        }   
        return $options;
    }
    public function uploadphotoAction()
    {
        $album_id = (int) $this->params()->fromRoute('id', 0);

        if (!$album_id) // Need show dropdown
        {
            $options = $this->getDropdownOptions();

            $form = new PhotoForm(true, $options);
        }
        else
        {
            $form = new PhotoForm();
        }
        
        $form->get('submit')->setValue('Upload photo');

        $request = $this->getRequest();

        if ($request->isPost()) {
            $photo = new Photo();
            $form->setInputFilter($photo->getInputFilter());

            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );

            $form->setData($post);
            //$form->setData($request->getPost());

            if ($form->isValid()) {

                $data = $form->getData();
                $ext = end(explode(".", ($data['image']['name'])));

                $new_file_name = time() . $this->get_random_string(10) . '.' . $ext;
                $target = ROOT_PATH . IMG_PATH . $new_file_name;

                      
                if ( move_uploaded_file($data['image']['tmp_name'], $target) ) {
                     
                    $image = new SimpleImage(); 
                    $image->load(ROOT_PATH . IMG_PATH . $new_file_name); 
                    $image->resizeToWidth(250); 
                    $image->save(ROOT_PATH .'/'. IMG_PATH_THUMBS . 'thumb_' . $new_file_name); 
                    $data['image']['name'] = $new_file_name;
                    if ($album_id)
                    {
                        $data['album_id'] = $album_id;
                    } 
                    $new_data = array(
                        'id' => $data['id'],
                        'album_id' => $data['album_id'],
                        'title' => $data['title'],
                        'address' => $data['address'],
                        'image' => $new_file_name,
                        'thumb' => 'thumb_'.$new_file_name,
                    );    
                    $photo->exchangeArray($new_data);
                    $this->getPhotoTable()->savePhoto($photo);
                } 
                
                return $this->redirect()->toRoute('album');
            }
        }
        if (!$album_id)
        {
            return array('form' => $form, 'dropdown' => true);
        }
        else
        {
            return array('form' => $form, 'dropdown' => false, 'album_id' => $album_id);
        }

    }
    public function albumviewAction()
    {
        $album_id = (int) $this->params()->fromRoute('id', 0);
        if (!$album_id) {
            return $this->redirect()->toRoute('album');
        }
        return array(
            'photos' => $this->getPhotoTable()->fetchAllWhere($album_id),
            'album_id' => $album_id
        );
    }
    public function photoviewAction()
    {
        $photo_id = (int) $this->params()->fromRoute('id', 0);
        if (!$photo_id) {
            return $this->redirect()->toRoute('album');
        }
        return array(
            'photo' => $this->getPhotoTable()->getPhoto($photo_id)
        );
    }   

    public function get_random_string($length, $valid_chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789')
    {
        // start with an empty random string
        $random_string = "";

        // count the number of chars in the valid chars string so we know how many choices we have
        $num_valid_chars = strlen($valid_chars);

        // repeat the steps until we've created a string of the right length
        for ($i = 0; $i < $length; $i++)
        {
            // pick a random number from 1 up to the number of valid chars
            $random_pick = mt_rand(1, $num_valid_chars);

            // take the random character out of the string of valid chars
            // subtract 1 from $random_pick because strings are indexed starting at 0, and we started picking at 1
            $random_char = $valid_chars[$random_pick-1];

            // add the randomly-chosen char onto the end of our string so far
            $random_string .= $random_char;
        }

        // return our finished random string
        return $random_string;
    }
    public function getAlbumTable()
    {
        if (!$this->albumTable) {
            $sm = $this->getServiceLocator();
            $this->albumTable = $sm->get('Album\Model\AlbumTable');
        }
        return $this->albumTable;
    }
    public function getPhotoTable()
    {
        if (!$this->photoTable) {
            $sm = $this->getServiceLocator();
            $this->photoTable = $sm->get('Album\Model\PhotoTable');
        }
        return $this->photoTable;
    }
    public function getAdapter()
    {
        $sm = $this->getServiceLocator();
        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
        return $dbAdapter;
    }
}