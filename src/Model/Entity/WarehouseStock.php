<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * WarehouseStock Entity
 *
 * @property int $id
 * @property int $product_id
 * @property int $user_id
 * @property int $warehouse_vendor_id
 * @property string $reference_number
 * @property int $quantity
 * @property float $price
 * @property int $in_out
 * @property string $remarks
 * @property int $status
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Product $product
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\WarehouseVendor $warehouse_vendor
 */
class WarehouseStock extends Entity
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
