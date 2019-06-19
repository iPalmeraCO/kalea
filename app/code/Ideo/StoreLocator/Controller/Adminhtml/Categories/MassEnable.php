<?php
/**
 * Copyright © 2018 Ideo. All rights reserved.
 * @license GPLv3  
 */

namespace Ideo\StoreLocator\Controller\Adminhtml\Categories;

use \Ideo\StoreLocator\Controller\Adminhtml\MassAction;
use \Magento\Framework\Controller\ResultFactory;
use \Magento\Framework\App\ResponseInterface;

class MassEnable extends MassAction
{
    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->categoryCollectionFactory->create());
        $collectionSize = $collection->getSize();

        /**
         * @var \Ideo\StoreLocator\Api\Data\CategoryInterface $category
         */
        foreach ($collection as $category) {
            $category->setIsActive(true);
            $this->categoryRepository->save($category);
        }

        $this->messageManager->addSuccessMessage(__('A total of %1 category/ies have been enabled.', $collectionSize));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
