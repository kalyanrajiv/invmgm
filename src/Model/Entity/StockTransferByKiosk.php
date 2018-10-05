<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * StockTransferByKiosk Entity
 *
 * @property int $id
 * @property int $kiosk_order_id
 * @property int $product_id
 * @property int $quantity
 * @property float $sale_price
 * @property int $cost_price
 * @property string $remarks
 * @property int $status
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\KioskOrder $kiosk_order
 * @property \App\Model\Entity\Product $product
 */
class StockTransferByKiosk extends Entity
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
