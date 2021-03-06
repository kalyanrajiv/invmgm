<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * KioskCancelledOrderProduct Entity
 *
 * @property int $id
 * @property int $kiosk_id
 * @property int $cancelled_by
 * @property int $kiosk_placed_order_id
 * @property int $product_id
 * @property int $category_id
 * @property int $quantity
 * @property int $difference
 * @property string $remarks
 * @property int $status
 * @property int $is_on_demand
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Kiosk $kiosk
 * @property \App\Model\Entity\KioskPlacedOrder $kiosk_placed_order
 * @property \App\Model\Entity\Product $product
 * @property \App\Model\Entity\Category $category
 */
class KioskCancelledOrderProduct extends Entity
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
