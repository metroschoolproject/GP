<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * EmailService
 * Handles transactional emails for bookings, payments, and payouts
 */
class EmailService
{
    private $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->configureMailer();
    }

    private function configureMailer(): void
    {
        $this->mailer->isSMTP();
        $this->mailer->Host = defined('MAIL_HOST') ? MAIL_HOST : 'smtp.gmail.com';
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = defined('MAIL_USERNAME') ? MAIL_USERNAME : '';
        $this->mailer->Password = defined('MAIL_PASSWORD') ? MAIL_PASSWORD : '';
        $this->mailer->SMTPSecure = defined('MAIL_ENCRYPTION') ? MAIL_ENCRYPTION : 'tls';
        $this->mailer->Port = defined('MAIL_PORT') ? MAIL_PORT : 587;
        $this->mailer->setFrom(defined('MAIL_FROM') ? MAIL_FROM : 'noreply@goldenpromise.com', 'Golden Promise');
    }

    /**
     * Send final payment reminder to customer (2-3 days before event).
     */
    public function sendFinalPaymentReminder(array $customer, array $booking, string $dueDate): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($customer['email'], $customer['name']);

            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Final Payment Due — Booking #' . $booking['id'];

            $balance = ((float)$booking['total_amount'] - (float)$booking['paid_amount']);
            $balanceText = number_format($balance, 0) . ' MMK';

            $htmlBody = <<<HTML
<div style="font-family: Poppins, sans-serif; max-width: 600px; margin: 0 auto; color: #333;">
  <div style="background: linear-gradient(135deg, #6b4459 0%, #c27a8e 100%); padding: 30px; border-radius: 8px 8px 0 0; color: white; text-align: center;">
    <h1 style="margin: 0; font-size: 24px;">Final Payment Due</h1>
    <p style="margin: 8px 0 0 0; opacity: 0.9;">Your wedding is coming soon!</p>
  </div>

  <div style="padding: 30px; background: #faf6f1; border-radius: 0 0 8px 8px;">
    <p>Hi {$customer['name']},</p>

    <p>Your wedding event is scheduled for <strong>{$dueDate}</strong>. We need the final payment to confirm all arrangements with your suppliers.</p>

    <div style="background: white; padding: 20px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #6b4459;">
      <p style="margin: 0 0 10px 0; color: #999; font-size: 12px;">FINAL PAYMENT DUE</p>
      <div style="font-size: 28px; font-weight: bold; color: #6b4459;">{$balanceText}</div>
      <p style="margin: 10px 0 0 0; color: #666; font-size: 13px;">Booking Total: {$booking['total_amount']} MMK</p>
    </div>

    <p><strong>What to do:</strong></p>
    <ol style="color: #666; line-height: 1.8;">
      <li>Log in to your Golden Promise account</li>
      <li>Go to Booking #{$booking['id']}</li>
      <li>Click "Complete Final Payment"</li>
      <li>Choose your payment method (KBZ Pay, AYA Bank, MM QR, or Card)</li>
    </ol>

    <p style="margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 4px; color: #856404; font-size: 13px;">
      ⚠️ <strong>Important:</strong> Final payment must be received by {$dueDate} to ensure all suppliers are confirmed for your wedding date.
    </p>

    <p style="margin-top: 20px;">
      <a href="{$this->getPaymentUrl($booking['id'])}" style="display: inline-block; padding: 12px 30px; background: #6b4459; color: white; text-decoration: none; border-radius: 4px; font-weight: bold;">Pay Now</a>
    </p>

    <p style="margin-top: 30px; color: #999; font-size: 13px; border-top: 1px solid #ddd; padding-top: 20px;">
      Need help? Contact us at support@goldenpromise.com or call 09-XXXXX-XXXXX
    </p>
  </div>
</div>
HTML;

            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = "Final Payment Due: {$balanceText}. Please log in to complete payment by {$dueDate}.";

            return $this->mailer->send();
        } catch (Exception $e) {
            error_log('Email send error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send payment confirmation to customer.
     */
    public function sendPaymentConfirmation(array $customer, array $booking, string $method, float $amount): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($customer['email'], $customer['name']);

            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Payment Received — Booking #' . $booking['id'];

            $amountText = number_format($amount, 0) . ' MMK';
            $balanceText = number_format((float)$booking['total_amount'] - $amount, 0) . ' MMK';

            $htmlBody = <<<HTML
<div style="font-family: Poppins, sans-serif; max-width: 600px; margin: 0 auto; color: #333;">
  <div style="background: linear-gradient(135deg, #166534 0%, #4ade80 100%); padding: 30px; border-radius: 8px 8px 0 0; color: white; text-align: center;">
    <h1 style="margin: 0; font-size: 24px;">✓ Payment Confirmed</h1>
  </div>

  <div style="padding: 30px; background: #faf6f1; border-radius: 0 0 8px 8px;">
    <p>Thank you, {$customer['name']}!</p>

    <p>We've received your payment. Your suppliers will now review and confirm their availability for your booking.</p>

    <div style="background: white; padding: 20px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #166534;">
      <p style="margin: 0 0 10px 0; color: #999; font-size: 12px;">PAYMENT RECEIVED</p>
      <div style="font-size: 28px; font-weight: bold; color: #166534;">{$amountText}</div>
      <p style="margin: 10px 0 0 0; color: #666; font-size: 13px;">Method: {$method}</p>
    </div>

    <p><strong>What's next?</strong></p>
    <ol style="color: #666; line-height: 1.8;">
      <li>Suppliers will review and confirm within 24 hours</li>
      <li>You'll receive notifications as suppliers respond</li>
      <li>Once all suppliers confirm, we'll send you final payment details</li>
      <li>Event happens → Suppliers complete work → You get invoices</li>
    </ol>

    <div style="background: #f0fdf4; padding: 15px; border-radius: 4px; margin-top: 20px; border: 1px solid #86efac;">
      <p style="margin: 0; color: #15803d; font-size: 13px; line-height: 1.6;">
        <strong>Remaining Balance:</strong> {$balanceText}<br>
        <strong>Due:</strong> 3 days before your event date
      </p>
    </div>

    <p style="margin-top: 20px;">
      <a href="{$this->getBookingUrl($booking['id'])}" style="display: inline-block; padding: 12px 30px; background: #6b4459; color: white; text-decoration: none; border-radius: 4px; font-weight: bold;">View Booking</a>
    </p>
  </div>
</div>
HTML;

            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = "Payment confirmed: {$amountText}. Suppliers will now review your booking.";

            return $this->mailer->send();
        } catch (Exception $e) {
            error_log('Email send error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send supplier payout notification.
     */
    public function sendSupplierPayoutNotification(array $supplier, float $amount, int $bookingId): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($supplier['email'], $supplier['name']);

            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Payout Ready — Golden Promise';

            $amountText = number_format($amount, 0) . ' MMK';

            $htmlBody = <<<HTML
<div style="font-family: Poppins, sans-serif; max-width: 600px; margin: 0 auto; color: #333;">
  <div style="background: linear-gradient(135deg, #b8924a 0%, #d4af37 100%); padding: 30px; border-radius: 8px 8px 0 0; color: white; text-align: center;">
    <h1 style="margin: 0; font-size: 24px;">✓ Payout Ready</h1>
  </div>

  <div style="padding: 30px; background: #faf6f1; border-radius: 0 0 8px 8px;">
    <p>Hi {$supplier['name']},</p>

    <p>Great news! A booking you completed has been settled and your payout is ready.</p>

    <div style="background: white; padding: 20px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #b8924a;">
      <p style="margin: 0 0 10px 0; color: #999; font-size: 12px;">PAYOUT AMOUNT</p>
      <div style="font-size: 28px; font-weight: bold; color: #b8924a;">{$amountText}</div>
      <p style="margin: 10px 0 0 0; color: #666; font-size: 13px;">Booking #{$bookingId}</p>
    </div>

    <p><strong>Next steps:</strong></p>
    <ol style="color: #666; line-height: 1.8;">
      <li>Log in to your supplier dashboard</li>
      <li>Go to "Earnings" section</li>
      <li>Click "Cash Out" to request payout to your bank account</li>
      <li>We'll process within 1-2 business days</li>
    </ol>

    <p style="margin-top: 20px;">
      <a href="{$this->getSupplierDashboardUrl()}" style="display: inline-block; padding: 12px 30px; background: #6b4459; color: white; text-decoration: none; border-radius: 4px; font-weight: bold;">View Earnings</a>
    </p>

    <p style="margin-top: 30px; color: #999; font-size: 13px; border-top: 1px solid #ddd; padding-top: 20px;">
      Thank you for being part of the Golden Promise network!
    </p>
  </div>
</div>
HTML;

            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = "Payout ready: {$amountText}. Log in to request cash out.";

            return $this->mailer->send();
        } catch (Exception $e) {
            error_log('Email send error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send booking cancellation notification with refund details.
     */
    public function sendCancellationNotification(array $customer, array $booking, float $refundAmount, string $reason): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($customer['email'], $customer['name']);

            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Booking Cancelled — Refund Details';

            $refundText = number_format($refundAmount, 0) . ' MMK';

            $htmlBody = <<<HTML
<div style="font-family: Poppins, sans-serif; max-width: 600px; margin: 0 auto; color: #333;">
  <div style="background: linear-gradient(135deg, #b94b4b 0%, #dc2626 100%); padding: 30px; border-radius: 8px 8px 0 0; color: white; text-align: center;">
    <h1 style="margin: 0; font-size: 24px;">Booking Cancelled</h1>
  </div>

  <div style="padding: 30px; background: #faf6f1; border-radius: 0 0 8px 8px;">
    <p>Your booking #<strong>{$booking['id']}</strong> has been cancelled.</p>

    <div style="background: white; padding: 20px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #b94b4b;">
      <p style="margin: 0 0 10px 0; color: #999; font-size: 12px;">REFUND AMOUNT</p>
      <div style="font-size: 28px; font-weight: bold; color: #b94b4b;">{$refundText}</div>
    </div>

    <p><strong>Refund Policy Applied:</strong></p>
    <p style="background: #fff3cd; padding: 15px; border-radius: 4px; color: #856404; font-size: 13px;">
      {$reason}
    </p>

    <p style="margin-top: 20px; color: #666;">
      Your refund will be processed to your original payment method within 3-5 business days. Please check your bank account.
    </p>

    <p style="margin-top: 30px; color: #999; font-size: 13px; border-top: 1px solid #ddd; padding-top: 20px;">
      We're sorry to see you go. If you have any questions, contact support@goldenpromise.com
    </p>
  </div>
</div>
HTML;

            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = "Booking cancelled. Refund: {$refundText}. {$reason}";

            return $this->mailer->send();
        } catch (Exception $e) {
            error_log('Email send error: ' . $e->getMessage());
            return false;
        }
    }

    private function getPaymentUrl(int $bookingId): string
    {
        return URLROOT . '/booking/detail/' . $bookingId;
    }

    private function getBookingUrl(int $bookingId): string
    {
        return URLROOT . '/booking/detail/' . $bookingId;
    }

    private function getSupplierDashboardUrl(): string
    {
        return URLROOT . '/supplier/earnings';
    }
}
