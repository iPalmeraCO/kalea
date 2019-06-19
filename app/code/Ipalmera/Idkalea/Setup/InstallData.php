<?php
namespace Ipalmera\Idkalea\Setup;
use Magento\Framework\Module\Setup\Migration;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
class InstallData implements InstallDataInterface
{
    /**
     * Customer setup factory
     *
     * @var \Magento\Customer\Setup\CustomerSetupFactory
     */
    private $customerSetupFactory;
    /**
     * Init
     *
     * @param \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(\Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory)
    {
        $this->customerSetupFactory = $customerSetupFactory;
    }
    /**
     * Installs DB schema for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
    $installer = $setup;
    $installer->startSetup();
    $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
    $entityTypeId = $customerSetup->getEntityTypeId(\Magento\Customer\Model\Customer::ENTITY);

    $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, "idkalea",  array(
        "type"     => "varchar",
        "backend"  => "",
        "label"    => "idkalea",
        "input"    => "text",        
        "visible"  => true,
        "required" => false,
        "default" => "",
        "frontend" => "",
        "unique"     => true,
        "note"       => ""

    ));

    $idkalea   = $customerSetup->getAttribute(\Magento\Customer\Model\Customer::ENTITY, "idkalea");

    $idkalea = $customerSetup->getEavConfig()->getAttribute(\Magento\Customer\Model\Customer::ENTITY, 'idkalea');
    $used_in_forms[]="adminhtml_customer";
    $used_in_forms[]="checkout_register";
    $used_in_forms[]="customer_account_create";
    $used_in_forms[]="customer_account_edit";
    $used_in_forms[]='customer_address_edit';
    $used_in_forms[]="adminhtml_checkout";
    $idkalea->setData("used_in_forms", $used_in_forms)
        ->setData("is_used_for_customer_segment", true)
        ->setData("is_system", 0)
        ->setData("is_user_defined", 1)
        ->setData("is_visible", 1)
        ->setData("sort_order", 100);
    $idkalea->save();
    $installer->endSetup();
}
}