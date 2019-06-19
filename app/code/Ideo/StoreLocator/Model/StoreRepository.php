<?php
/**
 * Copyright © 2018 Ideo. All rights reserved.
 * @license GPLv3  
 */

namespace Ideo\StoreLocator\Model;

use \Ideo\StoreLocator\Api\StoreRepositoryInterface;
use \Ideo\StoreLocator\Model\ResourceModel\Store as ResourceModel;
use \Ideo\StoreLocator\Api\Data\StoreInterface;
use \Magento\Framework\Exception\NoSuchEntityException;
use \Magento\Framework\Exception\StateException;

class StoreRepository implements StoreRepositoryInterface
{
    /**
     * @var \Ideo\StoreLocator\Model\ResourceModel\Store
     */
    private $resourceModel;

    /**
     * @var \Ideo\StoreLocator\Model\StoreFactory
     */
    private $modelFactory;

    /**
     * @var Store[]
     */
    private $instances = [];

    /**
     * StoreRepository constructor.
     * @param ResourceModel $resourceModel
     * @param StoreFactory $modelFactory
     */
    public function __construct(
        ResourceModel $resourceModel,
        StoreFactory $modelFactory
    ) {
        $this->resourceModel = $resourceModel;
        $this->modelFactory = $modelFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        if (!isset($this->instances[$id])) {
            $model = $this->modelFactory->create();

            $model->load($id);

            if (!$model->getId()) {
                throw NoSuchEntityException::singleField('store_id', $id);
            }

            $this->instances[$id] = $model;
        }

        return $this->instances[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function save(StoreInterface $model)
    {
        try {
            $existingModel = $this->get($model->getId());
        } catch (NoSuchEntityException $e) {
            $existingModel = null;
        }

        if ($existingModel !== null) {
            foreach ($existingModel->getData() as $key => $value) {
                if (!$model->hasData($key)) {
                    $model->setData($key, $value);
                }
            }
        }

        $this->resourceModel->save($model);
        unset($this->instances[$model->getId()]);

        return $this->get($model->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function delete(StoreInterface $model)
    {
        $name = $model->getName();
        try {
            unset($this->instances[$model->getId()]);
            $this->resourceModel->delete($model);
        } catch (\Exception $e) {
            throw new StateException(
                __('Unable to remove store %1', $name)
            );
        }
        unset($this->instances[$model->getId()]);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($id)
    {
        $model = $this->get($id);

        return $this->delete($model);
    }
}
