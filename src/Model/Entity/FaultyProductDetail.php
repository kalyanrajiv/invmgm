<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * FaultyProductDetail Entity
 *
 * @property int $id
 * @property int $kiosk_id
 * @property int $credit_receipt_id
 * @property int $credit_by
 * @property int $product_id
 * @property int $customer_id
 * @property int $quantity
 * @property float $sale_price
 * @property int $discount
 * @property int $status
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Kiosk $kiosk
 * @property \App\Model\Entity\CreditReceipt $credit_receipt
 * @property \App\Model\Entity\Product $product
 * @property \App\Model\Entity\Customer $customer
 */
class FaultyProductDetail extends Entity
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
