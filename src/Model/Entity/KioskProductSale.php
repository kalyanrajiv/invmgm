<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * KioskProductSale Entity
 *
 * @property int $id
 * @property int $kiosk_id
 * @property int $product_id
 * @property int $customer_id
 * @property int $quantity
 * @property int $cost_price
 * @property float $sale_price
 * @property float $refund_price
 * @property int $discount
 * @property int $discount_status
 * @property float $refund_gain
 * @property int $sold_by
 * @property int $refund_by
 * @property int $status
 * @property int $sale_type
 * @property int $refund_status
 * @property string $refund_remarks
 * @property int $product_receipt_id
 * @property string $remarks
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Kiosk $kiosk
 * @property \App\Model\Entity\Product $product
 * @property \App\Model\Entity\Customer $customer
 * @property \App\Model\Entity\ProductReceipt $product_receipt
 */
class KioskProductSale extends Entity
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
