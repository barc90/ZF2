<?php
namespace Album\Model;

use Zend\Db\TableGateway\TableGateway;

class PhotoTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }
    public function fetchAllWhere($album_id)
    {
        $resultSet = $this->tableGateway->select(
                array(
                    'album_id' => $album_id
                )
        );
        return $resultSet;
    }
    public function getPhoto($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function savePhoto(Photo $photo)
    {
        $data = array(
            'album_id' => $photo->album_id,
            'title'  => $photo->title,
            'address' => $photo->address,
            'image' => $photo->image,
            'thumb' => $photo->thumb
        );

        $id = (int)$photo->id;
        if ($id == 0) 
        {
            $data['created'] = date('Y-m-d H:i:s',time());
            $this->tableGateway->insert($data);
        } 
        else 
        {
            if ($this->getAlbum($id)) 
            {
                $this->tableGateway->update($data, array('id' => $id));
            } 
            else 
            {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function deletePhoto($id)
    {
        $this->tableGateway->delete(array('id' => $id));
    }
}