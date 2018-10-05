<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * InvoiceOrderDetail Entity
 *
 * @property int $id
 * @property int $kiosk_id
 * @property int $invoice_order_id
 * @property int $product_id
 * @property float $price
 * @property int $quantity
 * @property int $discount
 * @property int $status
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Kiosk $kiosk
 * @property \App\Model\Entity\InvoiceOrder $invoice_order
 * @property \App\Model\Entity\Product $product
 */
class InvoiceOrderDetail extends Entity
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
