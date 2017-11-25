<?php

namespace Album\Model;

use Zend\Db\TableGateway\TableGateway;

class AlbumTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        $adapter = $this->tableGateway->getAdapter();

        $statement  = $adapter->query('
            SELECT * FROM album
                LEFT JOIN(
                    SELECT MAX(id) AS photo_id, album_id, MAX(thumb) AS thumb,
                    MAX(created) AS last_image_date, COUNT(*) AS count
                    FROM 
                        photo
                    GROUP BY 
                        album_id
                ) AS p
            ON 
                album.id = p.album_id
            ORDER BY 
                album.id
        ');

        $results = $statement->execute();
        // $resultSet = $this->tableGateway->select();
        return $results;
    }

    public function getAlbumDropdownOptions()
    {
        $adapter = $this->tableGateway->getAdapter();

        $statement  = $adapter->query('SELECT id, title FROM album');

        $results = $statement->execute();

        return $results; 
    }
    public function getAlbum($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function saveAlbum(Album $album)
    {
        $data = array(
            'title'  => $album->title,
            'description' => $album->description,
            'name_photographer' => $album->name_photographer,
            'email' => $album->email,
            'phone' => $album->phone

           // 'created' => time();
          //  'changed' => $album->changed
        );

        $id = (int)$album->id;
        if ($id == 0) {
            $data['created'] = date('Y-m-d H:i:s',time());
            $this->tableGateway->insert($data);
        } else {
            if ($this->getAlbum($id)) {
                $data['changed'] = date('Y-m-d H:i:s',time()); //time();
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }


    public function deleteAlbum($id)
    {
        $this->tableGateway->delete(array('id' => $id));
    
    }


    /*
    * @return Result
    */
    public function deletePhotosFromAlbum($id)
    {
        $adapter = $this->tableGateway->getAdapter();

        $statement  = $adapter->query('DELETE FROM photo WHERE album_id = ?', array($id));

        //$result = $statement->execute(); 

        return $statement; //$resul->getAffectedRows()
    } 
}