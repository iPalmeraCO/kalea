<?php

/**
 * Copyright 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace AlanKent\CalculatorWebService\Api;

/**
 * Defines the service contract for some simple maths functions. The purpose is
 * to demonstrate the definition of a simple web service, not that these
 * functions are really useful in practice. The function prototypes were therefore
 * selected to demonstrate different parameter and return values, not as a good
 * calculator design.
 */
interface CalculatorInterface
{
    /**
     * Return the sum of the two numbers.
     *
     * @api
     * @param int $valor1 Left hand operand.  
     * @param string $valor2 Left hand operand.  
     * @param string $llave Left hand operand.  
     * @param string $products Left hand operand.     
     * @return json The sum of the numbers.
     */
    
    public function get_products($valor1, $valor2, $llave, $products);

    /**
    * @api
    * @return json send invoices pending
    */
    public function invoices();    
}