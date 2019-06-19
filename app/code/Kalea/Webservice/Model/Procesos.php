<?php
    namespace Kalea\Webservice\Model;

    use \Magento\Framework\Model\AbstractModel;

    class Procesos extends AbstractModel
    {
       
       /**
         * Initialize resource model
         *
         * @return void
         */
        protected function _construct()
        {
            $this->_init('Kalea\Webservice\Model\Procesos');
        }

        public function getprueba(){
            return "SISIS";
        }
        
}