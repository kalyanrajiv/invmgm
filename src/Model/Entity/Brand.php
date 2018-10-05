<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Brand Entity
 *
 * @property int $id
 * @property string $brand
 * @property string $company
 * @property int $status
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\CsvProduct[] $csv_products
 * @property \App\Model\Entity\CsvProducts2[] $csv_products2
 * @property \App\Model\Entity\Kiosk10000Product[] $kiosk10000_products
 * @property \App\Model\Entity\Kiosk10Product[] $kiosk10_products
 * @property \App\Model\Entity\Kiosk1Product[] $kiosk1_products
 * @property \App\Model\Entity\Kiosk2Product[] $kiosk2_products
 * @property \App\Model\Entity\Kiosk3Product[] $kiosk3_products
 * @property \App\Model\Entity\Kiosk4Product[] $kiosk4_products
 * @property \App\Model\Entity\Kiosk5Product[] $kiosk5_products
 * @property \App\Model\Entity\Kiosk6Product[] $kiosk6_products
 * @property \App\Model\Entity\Kiosk7Product[] $kiosk7_products
 * @property \App\Model\Entity\Kiosk8Product[] $kiosk8_products
 * @property \App\Model\Entity\KioskProduct[] $kiosk_products
 * @property \App\Model\Entity\MobileBlkReSale[] $mobile_blk_re_sales
 * @property \App\Model\Entity\MobileModel[] $mobile_models
 * @property \App\Model\Entity\MobilePrice[] $mobile_prices
 * @property \App\Model\Entity\MobilePurchase[] $mobile_purchases
 * @property \App\Model\Entity\MobileReSale[] $mobile_re_sales
 * @property \App\Model\Entity\MobileRepairPrice[] $mobile_repair_prices
 * @property \App\Model\Entity\MobileRepair[] $mobile_repairs
 * @property \App\Model\Entity\MobileUnlockPrice[] $mobile_unlock_prices
 * @property \App\Model\Entity\MobileUnlock[] $mobile_unlocks
 * @property \App\Model\Entity\Product[] $products
 * @property \App\Model\Entity\Products3mar16[] $products3mar16
 * @property \App\Model\Entity\Products8oct[] $products8oct
 */
class Brand extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];
}
