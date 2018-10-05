<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * KioskOrder Entity
 *
 * @property int $id
 * @property int $kiosk_id
 * @property int $user_id
 * @property int $kiosk_placed_order_id
 * @property int $status
 * @property int $is_on_demand
 * @property \Cake\I18n\Time $dispatched_on
 * @property \Cake\I18n\Time $received_on
 * @property int $received_by
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Kiosk $kiosk
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\KioskPlacedOrder $kiosk_placed_order
 * @property \App\Model\Entity\KioskTransferredStock1[] $kiosk_transferred_stock1
 * @property \App\Model\Entity\KioskTransferredStock10[] $kiosk_transferred_stock10
 * @property \App\Model\Entity\KioskTransferredStock11[] $kiosk_transferred_stock11
 * @property \App\Model\Entity\KioskTransferredStock13[] $kiosk_transferred_stock13
 * @property \App\Model\Entity\KioskTransferredStock17[] $kiosk_transferred_stock17
 * @property \App\Model\Entity\KioskTransferredStock18[] $kiosk_transferred_stock18
 * @property \App\Model\Entity\KioskTransferredStock2[] $kiosk_transferred_stock2
 * @property \App\Model\Entity\KioskTransferredStock20[] $kiosk_transferred_stock20
 * @property \App\Model\Entity\KioskTransferredStock3[] $kiosk_transferred_stock3
 * @property \App\Model\Entity\KioskTransferredStock4[] $kiosk_transferred_stock4
 * @property \App\Model\Entity\KioskTransferredStock5[] $kiosk_transferred_stock5
 * @property \App\Model\Entity\KioskTransferredStock7[] $kiosk_transferred_stock7
 * @property \App\Model\Entity\KioskTransferredStock8[] $kiosk_transferred_stock8
 * @property \App\Model\Entity\OrderDispute[] $order_disputes
 * @property \App\Model\Entity\RevertStock[] $revert_stocks
 * @property \App\Model\Entity\StockTransfer[] $stock_transfer
 * @property \App\Model\Entity\StockTransferByKiosk[] $stock_transfer_by_kiosk
 * @property \App\Model\Entity\TransientStock[] $transient_stock
 */
class KioskOrder extends Entity
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
