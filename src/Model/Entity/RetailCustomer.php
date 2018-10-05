<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * RetailCustomer Entity
 *
 * @property int $id
 * @property int $kiosk_id
 * @property string $fname
 * @property string $lname
 * @property string $email
 * @property string $mobile
 * @property string $city
 * @property string $state
 * @property string $country
 * @property string $zip
 * @property string $address_1
 * @property string $address_2
 * @property int $status
 * @property \Cake\I18n\Time $modified
 * @property \Cake\I18n\Time $created
 *
 * @property \App\Model\Entity\Kiosk $kiosk
 * @property \App\Model\Entity\Kiosk10ProductReceipt[] $kiosk10_product_receipts
 * @property \App\Model\Entity\Kiosk11ProductReceipt[] $kiosk11_product_receipts
 * @property \App\Model\Entity\Kiosk12ProductReceipt[] $kiosk12_product_receipts
 * @property \App\Model\Entity\Kiosk13ProductReceipt[] $kiosk13_product_receipts
 * @property \App\Model\Entity\Kiosk14ProductReceipt[] $kiosk14_product_receipts
 * @property \App\Model\Entity\Kiosk15ProductReceipt[] $kiosk15_product_receipts
 * @property \App\Model\Entity\Kiosk16ProductReceipt[] $kiosk16_product_receipts
 * @property \App\Model\Entity\Kiosk17ProductReceipt[] $kiosk17_product_receipts
 * @property \App\Model\Entity\Kiosk18ProductReceipt[] $kiosk18_product_receipts
 * @property \App\Model\Entity\Kiosk19ProductReceipt[] $kiosk19_product_receipts
 * @property \App\Model\Entity\Kiosk1ProductReceipt[] $kiosk1_product_receipts
 * @property \App\Model\Entity\Kiosk20ProductReceipt[] $kiosk20_product_receipts
 * @property \App\Model\Entity\Kiosk21ProductReceipt[] $kiosk21_product_receipts
 * @property \App\Model\Entity\Kiosk22ProductReceipt[] $kiosk22_product_receipts
 * @property \App\Model\Entity\Kiosk2ProductReceipt[] $kiosk2_product_receipts
 * @property \App\Model\Entity\Kiosk3ProductReceipt[] $kiosk3_product_receipts
 * @property \App\Model\Entity\Kiosk4ProductReceipt[] $kiosk4_product_receipts
 * @property \App\Model\Entity\Kiosk5ProductReceipt[] $kiosk5_product_receipts
 * @property \App\Model\Entity\Kiosk7ProductReceipt[] $kiosk7_product_receipts
 * @property \App\Model\Entity\Kiosk8ProductReceipt[] $kiosk8_product_receipts
 * @property \App\Model\Entity\MobileBlkReSale[] $mobile_blk_re_sales
 * @property \App\Model\Entity\MobileReSale[] $mobile_re_sales
 * @property \App\Model\Entity\MobileRepair[] $mobile_repairs
 * @property \App\Model\Entity\MobileUnlockSale[] $mobile_unlock_sales
 * @property \App\Model\Entity\MobileUnlock[] $mobile_unlocks
 */
class RetailCustomer extends Entity
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
