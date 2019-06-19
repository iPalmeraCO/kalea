<?php

namespace Magento\Checkout\Block\Checkout\AttributeMerger;



class Plugin

{

  public function afterMerge(\Magento\Checkout\Block\Checkout\AttributeMerger $subject, $result)

  {

    if (array_key_exists('street', $result)) {
      $result['street']['children'][0]['label'] = __('Av calle, numeral , zona:');

      $result['street']['children'][0]['placeholder'] = __('Av calle, numeral , zona:');

      /*$result['street']['children'][1]['placeholder'] = __('Apartment, suite, unit, building, floor, etc.');

      $result['street']['children'][2]['placeholder'] = __('Additional Info');*/

    }



    return $result;

  }

}