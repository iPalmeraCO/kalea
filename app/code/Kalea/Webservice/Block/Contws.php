<?php
namespace Kalea\Webservice\Block;
//use Kalea\Webservice\Model\Procesos;

class Contws extends \Magento\Framework\View\Element\Template
{
     public function getservicios()
    {
       $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
       $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
       $connection = $resource->getConnection();
       $tableName = $resource->getTableName('mageplaza_blog_post_category'); //gives table name with prefix
       $sql = "Select * from process_ws_products";                        
        $result = $connection->fetchAll($sql);
      return $result;
    }

    public function getestado($est) {
      if ($est == 0){
        return "En proceso";
      } else{
        return "Terminado";
      }
    }

}