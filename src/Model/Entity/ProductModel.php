<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * MobileModel Entity
 *
 * @property int $id
 * @property int $brand_id
 * @property string $model
 * @property string $brief_description
 * @property int $status
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Brand $brand
 * @property \App\Model\Entity\MobileBlkReSale[] $mobile_blk_re_sales
 * @property \App\Model\Entity\MobilePrice[] $mobile_prices
 * @property \App\Model\Entity\MobilePurchase[] $mobile_purchases
 * @property \App\Model\Entity\MobileReSale[] $mobile_re_sales
 * @property \App\Model\Entity\MobileRepairPrice[] $mobile_repair_prices
 * @property \App\Model\Entity\MobileRepair[] $mobile_repairs
 * @property \App\Model\Entity\MobileUnlockPrice[] $mobile_unlock_prices
 * @property \App\Model\Entity\MobileUnlock[] $mobile_unlocks
 */
class ProductModel extends Entity
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
