<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * TempProductDetail Entity
 *
 * @property int $id
 * @property int $kiosk_id
 * @property int $user_id
 * @property int $temp_product_order_id
 * @property int $product_receipt_id
 * @property int $product_id
 * @property int $quantity
 * @property float $amount
 * @property string $remarks
 * @property int $discount
 * @property int $discount_status
 * @property int $status
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Product $product
 * @property \App\Model\Entity\Kiosk $kiosk
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\TempProductOrder $temp_product_order
 * @property \App\Model\Entity\ProductReceipt $product_receipt
 */
class TempProductDetail extends Entity
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
