<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * KioskPlacedOrder Entity
 *
 * @property int $id
 * @property int $kiosk_id
 * @property int $user_id
 * @property int $weekly_order
 * @property int $status
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Kiosk $kiosk
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\KioskCancelledOrderProduct[] $kiosk_cancelled_order_products
 * @property \App\Model\Entity\KioskOrderProduct[] $kiosk_order_products
 * @property \App\Model\Entity\KioskOrder[] $kiosk_orders
 * @property \App\Model\Entity\KioskTransferredStock10[] $kiosk_transferred_stock10
 * @property \App\Model\Entity\KioskTransferredStock11[] $kiosk_transferred_stock11
 * @property \App\Model\Entity\KioskTransferredStock17[] $kiosk_transferred_stock17
 * @property \App\Model\Entity\KioskTransferredStock7[] $kiosk_transferred_stock7
 * @property \App\Model\Entity\MobilePlacedOrder[] $mobile_placed_orders
 * @property \App\Model\Entity\OnDemandProduct[] $on_demand_products
 * @property \App\Model\Entity\StockTransfer[] $stock_transfer
 */
class KioskPlacedOrder extends Entity
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
