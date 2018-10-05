<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * CreditReceipt Entity
 *
 * @property int $id
 * @property int $customer_id
 * @property string $fname
 * @property string $lname
 * @property string $email
 * @property string $mobile
 * @property string $address_1
 * @property string $address_2
 * @property string $city
 * @property string $state
 * @property string $zip
 * @property float $credit_amount
 * @property int $bulk_discount
 * @property int $processed_by
 * @property int $status
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Customer $customer
 * @property \App\Model\Entity\CreditPaymentDetail[] $credit_payment_details
 * @property \App\Model\Entity\CreditProductDetail[] $credit_product_details
 * @property \App\Model\Entity\FaultyProduct[] $faulty_products
 * @property \App\Model\Entity\Kiosk10CreditPaymentDetail[] $kiosk10_credit_payment_details
 * @property \App\Model\Entity\Kiosk10CreditProductDetail[] $kiosk10_credit_product_details
 * @property \App\Model\Entity\Kiosk11CreditPaymentDetail[] $kiosk11_credit_payment_details
 * @property \App\Model\Entity\Kiosk11CreditProductDetail[] $kiosk11_credit_product_details
 * @property \App\Model\Entity\Kiosk19CreditPaymentDetail[] $kiosk19_credit_payment_details
 * @property \App\Model\Entity\Kiosk19CreditProductDetail[] $kiosk19_credit_product_details
 * @property \App\Model\Entity\Kiosk1CreditPaymentDetail[] $kiosk1_credit_payment_details
 * @property \App\Model\Entity\Kiosk1CreditProductDetail[] $kiosk1_credit_product_details
 * @property \App\Model\Entity\Kiosk20CreditPaymentDetail[] $kiosk20_credit_payment_details
 * @property \App\Model\Entity\Kiosk20CreditProductDetail[] $kiosk20_credit_product_details
 * @property \App\Model\Entity\Kiosk21CreditPaymentDetail[] $kiosk21_credit_payment_details
 * @property \App\Model\Entity\Kiosk21CreditProductDetail[] $kiosk21_credit_product_details
 * @property \App\Model\Entity\Kiosk3CreditPaymentDetail[] $kiosk3_credit_payment_details
 * @property \App\Model\Entity\Kiosk5CreditPaymentDetail[] $kiosk5_credit_payment_details
 * @property \App\Model\Entity\Kiosk5CreditProductDetail[] $kiosk5_credit_product_details
 * @property \App\Model\Entity\Kiosk7CreditPaymentDetail[] $kiosk7_credit_payment_details
 * @property \App\Model\Entity\Kiosk7CreditProductDetail[] $kiosk7_credit_product_details
 * @property \App\Model\Entity\Kiosk8CreditPaymentDetail[] $kiosk8_credit_payment_details
 * @property \App\Model\Entity\Kiosk8CreditProductDetail[] $kiosk8_credit_product_details
 * @property \App\Model\Entity\KioskFaultyProductDetail[] $kiosk_faulty_product_details
 */
class CreditReceipt extends Entity
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
