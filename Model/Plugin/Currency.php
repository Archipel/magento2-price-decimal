<?php
/**
 *
 * @package package Lillik\PriceDecimal\Model\Plugin\Local
 *
 * @author  Lilian Codreanu <lilian.codreanu@gmail.com>
 */

namespace Lillik\PriceDecimal\Model\Plugin;

class Currency extends PriceFormatPluginAbstract
{

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Framework\CurrencyInterface $subject
     * @param array                                ...$args
     *
     * @return array
     */
    public function beforeToCurrency(
        \Lillik\PriceDecimal\Model\Currency $subject,
        ...$arguments
    ) {
        if ($this->getConfig()->isEnable()) {
            if(isset($arguments[1]['precision']) && $arguments[1]['precision'] == 'max'){
                $decimals = strstr(strval(floatval($arguments[0])), ".");
                if($decimals) {
                    $arguments[1]['precision'] = strlen($decimals) - 1;
                }
                else{
                    $arguments[1]['precision'] = 0;
                }
            }
            else {
                $arguments[1]['precision'] = $subject->getPriceDisplayDecimals();
            }
        }
        return $arguments;
    }
}
