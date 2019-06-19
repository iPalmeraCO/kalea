<?php
/**
 * Copyright © 2018 Ideo. All rights reserved.
 * @license GPLv3  
 */

namespace Ideo\StoreLocator\Ui\Component\Listing\Column;

use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Framework\UrlInterface;

class CategoriesActions extends Column
{
    /** Url path */
    const STORELOCATOR_URL_PATH_EDIT = 'storelocator/categories/edit';
    const STORELOCATOR_URL_PATH_DELETE = 'storelocator/categories/delete';

    /** @var UrlInterface */
    private $urlBuilder;

    /**
     * @var string
     */
    private $viewUrl;

    /**
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface       $urlBuilder
     * @param array              $components
     * @param array              $data
     * @param string             $viewUrl
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = [],
        $viewUrl = self::STORELOCATOR_URL_PATH_EDIT
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->viewUrl = $viewUrl;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['category_id'])) {
                    $item[$name]['view'] = [
                        'href' => $this->urlBuilder->getUrl($this->viewUrl, ['category_id' => $item['category_id']]),
                        'label' => __('Edit')
                    ];
                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(self::STORELOCATOR_URL_PATH_DELETE, ['category_id' => $item['category_id']]),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete "${ $.$data.title }"'),
                            'message' => __('Are you sure you wan\'t to delete a "${ $.$data.title }" category?')
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
