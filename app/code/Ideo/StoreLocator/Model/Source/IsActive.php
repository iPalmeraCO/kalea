<?php
/**
 * Copyright © 2018 Ideo. All rights reserved.
 * @license GPLv3  
 */

namespace Ideo\StoreLocator\Model\Source;

use \Magento\Framework\Data\OptionSourceInterface;

class IsActive implements OptionSourceInterface
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->getAvailableStatuses();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }

    /**
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ACTIVE => __('Enabled'), self::STATUS_INACTIVE => __('Disabled')];
    }
}
