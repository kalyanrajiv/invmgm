<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ImportOrderDetail Entity
 *
 * @property int $id
 * @property int $product_id
 * @property int $quantity
 * @property int $import_order_id
 * @property int $import_order_reference_id
 * @property int $status
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Product $product
 * @property \App\Model\Entity\ImportOrder $import_order
 * @property \App\Model\Entity\ImportOrderReference $import_order_reference
 */
class ImportOrderDetail extends Entity
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
