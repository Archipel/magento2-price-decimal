<?php
/**
 * Created by IntelliJ IDEA.
 * User: Thomas
 * Date: 2019-01-20
 * Time: 9:34 PM
 */
namespace Lillik\PriceDecimal\Model\Plugin;

class ConfigPlugin {

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
            $this->scopeConfig = $scopeConfig;
            $this->resourceConnection = $resourceConnection;
    }

    public function aroundSave(\Magento\Config\Model\Config $subject, \Closure $proceed) {
        $rv = $proceed();

        $decimalPlaces = intval($this->scopeConfig->getValue('catalog_price_decimal/general/price_precision', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $connection = $this->resourceConnection->getConnection();
        $dbname = $connection->getConfig()['dbname'];
        $columns = $connection->fetchAll(
            "SELECT
                    *
                FROM
                    INFORMATION_SCHEMA.COLUMNS
                WHERE
                    TABLE_SCHEMA = '$dbname' AND
                    DATA_TYPE = 'decimal' AND
                    (NUMERIC_PRECISION < 65 OR NUMERIC_SCALE < $decimalPlaces)
                ORDER BY
                    TABLE_SCHEMA, TABLE_NAME"
        );
        foreach($columns as $c){
            $query = "ALTER TABLE `$dbname`.`{$c['TABLE_NAME']}` CHANGE COLUMN `{$c['COLUMN_NAME']}` `{$c['COLUMN_NAME']}` DECIMAL(65,$decimalPlaces) "
                    .($c['IS_NULLABLE'] == 'YES' ? 'NULL' : 'NOT NULL')
                    .($c['COLUMN_DEFAULT'] === null ? ($c['IS_NULLABLE'] == 'YES' ? " DEFAULT NULL": '') : ' DEFAULT '.$c['COLUMN_DEFAULT'])
                    .($c['COLUMN_COMMENT'] ? ' COMMENT \''.addslashes($c['COLUMN_COMMENT']).'\'' : '');
            $connection->query($query);
        }

        return $rv;
    }
}