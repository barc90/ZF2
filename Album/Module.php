<?php
namespace Album;

use Album\Model\Album;
use Album\Model\AlbumTable;
use Album\Model\Photo;
use Album\Model\PhotoTable;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

//use Zend\Db\Adapter\Adapter;

class Module
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Album\Model\AlbumTable' =>  function($sm) {
                    $tableGateway = $sm->get('AlbumTableGateway');
                    $table = new AlbumTable($tableGateway);
                    return $table;
                },
                'Album\Model\PhotoTable' =>  function($sm) {
                    $tableGateway = $sm->get('PhotoTableGateway');
                    $table = new PhotoTable($tableGateway);
                    return $table;
                },

                'AlbumTableGateway' => function ($sm) {
                    /*
                    $dbAdapter = new Adapter(array(
                        'driver'         => 'Pdo',
                        'dsn'            => 'mysql:dbname=album;host=localhost',
                        'username' => 'root',
                        'password' => '',
                    )); 
                    */
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');

                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Album());
                    return new TableGateway('album', $dbAdapter, null, $resultSetPrototype);
                },

                'PhotoTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Photo());
                    return new TableGateway('photo', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }

}