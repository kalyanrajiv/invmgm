<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * InvoiceOrder Entity
 *
 * @property int $id
 * @property int $kiosk_id
 * @property int $user_id
 * @property int $customer_id
 * @property string $fname
 * @property string $lname
 * @property string $email
 * @property string $mobile
 * @property float $bulk_discount
 * @property string $del_city
 * @property string $del_state
 * @property string $del_zip
 * @property string $del_address_1
 * @property string $del_address_2
 * @property int $invoice_status
 * @property int $status
 * @property float $amount
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Kiosk $kiosk
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Customer $customer
 * @property \App\Model\Entity\InvoiceOrderDetail[] $invoice_order_details
 * @property \App\Model\Entity\Kiosk10000PaymentDetail[] $kiosk10000_payment_details
 * @property \App\Model\Entity\Kiosk10InvoiceOrderDetail[] $kiosk10_invoice_order_details
 * @property \App\Model\Entity\Kiosk10PaymentDetail[] $kiosk10_payment_details
 * @property \App\Model\Entity\Kiosk11InvoiceOrderDetail[] $kiosk11_invoice_order_details
 * @property \App\Model\Entity\Kiosk11PaymentDetail[] $kiosk11_payment_details
 * @property \App\Model\Entity\Kiosk12PaymentDetail[] $kiosk12_payment_details
 * @property \App\Model\Entity\Kiosk13PaymentDetail[] $kiosk13_payment_details
 * @property \App\Model\Entity\Kiosk14PaymentDetail[] $kiosk14_payment_details
 * @property \App\Model\Entity\Kiosk15PaymentDetail[] $kiosk15_payment_details
 * @property \App\Model\Entity\Kiosk16PaymentDetail[] $kiosk16_payment_details
 * @property \App\Model\Entity\Kiosk17PaymentDetail[] $kiosk17_payment_details
 * @property \App\Model\Entity\Kiosk18PaymentDetail[] $kiosk18_payment_details
 * @property \App\Model\Entity\Kiosk19InvoiceOrderDetail[] $kiosk19_invoice_order_details
 * @property \App\Model\Entity\Kiosk19PaymentDetail[] $kiosk19_payment_details
 * @property \App\Model\Entity\Kiosk1PaymentDetail[] $kiosk1_payment_details
 * @property \App\Model\Entity\Kiosk20InvoiceOrderDetail[] $kiosk20_invoice_order_details
 * @property \App\Model\Entity\Kiosk20PaymentDetail[] $kiosk20_payment_details
 * @property \App\Model\Entity\Kiosk21InvoiceOrderDetail[] $kiosk21_invoice_order_details
 * @property \App\Model\Entity\Kiosk21PaymentDetail[] $kiosk21_payment_details
 * @property \App\Model\Entity\Kiosk22PaymentDetail[] $kiosk22_payment_details
 * @property \App\Model\Entity\Kiosk2PaymentDetail[] $kiosk2_payment_details
 * @property \App\Model\Entity\Kiosk3PaymentDetail[] $kiosk3_payment_details
 * @property \App\Model\Entity\Kiosk4PaymentDetail[] $kiosk4_payment_details
 * @property \App\Model\Entity\Kiosk5InvoiceOrderDetail[] $kiosk5_invoice_order_details
 * @property \App\Model\Entity\Kiosk5PaymentDetail[] $kiosk5_payment_details
 * @property \App\Model\Entity\Kiosk7InvoiceOrderDetail[] $kiosk7_invoice_order_details
 * @property \App\Model\Entity\Kiosk7PaymentDetail[] $kiosk7_payment_details
 * @property \App\Model\Entity\Kiosk8InvoiceOrderDetail[] $kiosk8_invoice_order_details
 * @property \App\Model\Entity\Kiosk8PaymentDetail[] $kiosk8_payment_details
 * @property \App\Model\Entity\PaymentDetail[] $payment_details
 * @property \App\Model\Entity\TPaymentDetail[] $t_payment_details
 */
class InvoiceOrder extends Entity
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
