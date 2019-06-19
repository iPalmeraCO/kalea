<?php
namespace Inchoo\HelloWorld\Controller\Index;

class Pruebawebservice extends \Magento\Framework\App\Action\Action
{
  public function __construct(
\Magento\Framework\App\Action\Context $context)
  {
    return parent::__construct($context);
  }

  public function execute()
  {
    echo 'Entro a la vista';
    exit;
  }
}