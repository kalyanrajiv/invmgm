<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * TransferSurplus Entity
 *
 * @property int $id
 * @property string $invoice_reference
 * @property int $customer_id
 * @property int $product_id
 * @property int $category_id
 * @property int $quantity
 * @property float $cost_price
 * @property float $sale_price
 * @property string $bulk_discount
 * @property int $vat_applied
 * @property int $product_receipt_id
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Category $category
 * @property \App\Model\Entity\Customer $customer
 * @property \App\Model\Entity\Product $product
 * @property \App\Model\Entity\ProductReceipt $product_receipt
 */
class TransferSurplus extends Entity
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
