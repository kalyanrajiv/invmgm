<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * CustomerProductPrice Entity
 *
 * @property int $id
 * @property int $customer_id
 * @property int $product_id
 * @property int $sale_price
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Customer $customer
 * @property \App\Model\Entity\Product $product
 */
class CustomerProductPrice extends Entity
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
        'customer_id' => true,
        'product_id' => true,
        'sale_price' => true,
        'created' => true,
        'modified' => true,
        'customer' => true,
        'product' => true
    ];
}
