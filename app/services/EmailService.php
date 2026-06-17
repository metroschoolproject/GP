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

    /**
     * Send booking confirmation to customer when package booking is created.
     */
    public function sendBookingConfirmation(array $customer, array $booking, array $items): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($customer['email'], $customer['name']);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Booking Request Received — Booking #' . $booking['id'];

            $deposit = number_format((float)$booking['total_amount'] * 0.10, 0) . ' MMK';
            $total   = number_format((float)$booking['total_amount'], 0) . ' MMK';
            $payUrl  = URLROOT . '/booking/pay/' . $booking['id'];

            $itemRows = '';
            foreach ($items as $item) {
                $name = htmlspecialchars($item['service_name'] ?? $item['package_name'] ?? 'Service', ENT_QUOTES);
                $date = !empty($item['selected_date']) ? date('l, M j, Y', strtotime($item['selected_date'])) : 'TBD';
                $itemRows .= "<tr><td style='padding:8px 0;border-bottom:1px solid #f0e8e0;color:#555;'>{$name}</td><td style='padding:8px 0;border-bottom:1px solid #f0e8e0;color:#888;text-align:right;'>{$date}</td></tr>";
            }

            $htmlBody = <<<HTML
<div style="font-family:Poppins,sans-serif;max-width:600px;margin:0 auto;color:#333;">
  <div style="background:linear-gradient(135deg,#6b4459 0%,#c4a882 100%);padding:30px;border-radius:8px 8px 0 0;color:white;text-align:center;">
    <h1 style="margin:0;font-size:24px;">Booking Request Received</h1>
    <p style="margin:8px 0 0;opacity:.85;">Booking #{$booking['id']}</p>
  </div>
  <div style="padding:30px;background:#faf6f1;border-radius:0 0 8px 8px;">
    <p>Dear {$customer['name']},</p>
    <p>Thank you for your booking request on Golden Promise! Your selections are reserved and waiting for your deposit payment.</p>
    <table style="width:100%;border-collapse:collapse;margin:20px 0;">
      <thead><tr><th style="text-align:left;color:#999;font-size:12px;padding-bottom:6px;">SERVICE</th><th style="text-align:right;color:#999;font-size:12px;padding-bottom:6px;">DATE</th></tr></thead>
      <tbody>{$itemRows}</tbody>
    </table>
    <div style="background:white;padding:16px 20px;border-radius:6px;border-left:4px solid #6b4459;margin:20px 0;">
      <div style="display:flex;justify-content:space-between;margin-bottom:6px;"><span style="color:#999;font-size:12px;">DEPOSIT DUE (10%)</span><span style="font-size:20px;font-weight:bold;color:#6b4459;">{$deposit}</span></div>
      <div style="display:flex;justify-content:space-between;"><span style="color:#999;font-size:12px;">TOTAL</span><span style="color:#666;">{$total}</span></div>
    </div>
    <p><a href="{$payUrl}" style="display:inline-block;padding:12px 30px;background:#6b4459;color:white;text-decoration:none;border-radius:4px;font-weight:bold;">Pay Deposit Now</a></p>
    <p style="color:#888;font-size:13px;">Your booking will be confirmed once the deposit is received. The remaining balance is due 3 days before your event.</p>
  </div>
</div>
HTML;
            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = "Booking #{$booking['id']} received. Deposit due: {$deposit}. Pay at: {$payUrl}";
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log('Email send error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send event-detail email to customer, each supplier, and admin after payment is verified.
     */
    public function sendPaymentVerifiedEvent(array $customer, array $suppliers, array $admin, array $booking, array $items): bool
    {
        $bookingId  = $booking['id'];
        $total      = number_format((float)$booking['total_amount'], 0) . ' MMK';
        $customerUrl = URLROOT . '/booking/detail/' . $bookingId;
        $supplierUrl = URLROOT . '/supplier/bookings/detail/' . $bookingId;

        $itemRows = '';
        foreach ($items as $item) {
            $name  = htmlspecialchars($item['service_name'] ?? $item['package_name'] ?? 'Service', ENT_QUOTES);
            $date  = !empty($item['booking_date']) ? date('l, M j, Y', strtotime($item['booking_date'])) : 'TBD';
            $start = !empty($item['start_time']) ? date('g:i A', strtotime($item['start_time'])) : '';
            $time  = $start ?: 'Full day';
            $itemRows .= "<tr><td style='padding:8px 0;border-bottom:1px solid #f0e8e0;color:#555;'>{$name}</td><td style='padding:8px 0;border-bottom:1px solid #f0e8e0;color:#888;'>{$date}</td><td style='padding:8px 0;border-bottom:1px solid #f0e8e0;color:#888;text-align:right;'>{$time}</td></tr>";
        }

        $sent = true;

        // Email to customer
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($customer['email'], $customer['name']);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Payment Verified — Your Event is Confirmed! Booking #' . $bookingId;
            $this->mailer->Body = <<<HTML
<div style="font-family:Poppins,sans-serif;max-width:600px;margin:0 auto;color:#333;">
  <div style="background:linear-gradient(135deg,#166534 0%,#4ade80 100%);padding:30px;border-radius:8px 8px 0 0;color:white;text-align:center;">
    <h1 style="margin:0;font-size:24px;">✓ Payment Verified</h1>
    <p style="margin:8px 0 0;opacity:.85;">Your event is confirmed!</p>
  </div>
  <div style="padding:30px;background:#faf6f1;border-radius:0 0 8px 8px;">
    <p>Dear {$customer['name']},</p>
    <p>Great news! Your deposit payment has been verified. Here are your confirmed event details:</p>
    <table style="width:100%;border-collapse:collapse;margin:20px 0;">
      <thead><tr>
        <th style="text-align:left;color:#999;font-size:12px;padding-bottom:6px;">SERVICE</th>
        <th style="text-align:left;color:#999;font-size:12px;padding-bottom:6px;">DATE</th>
        <th style="text-align:right;color:#999;font-size:12px;padding-bottom:6px;">TIME</th>
      </tr></thead>
      <tbody>{$itemRows}</tbody>
    </table>
    <div style="background:#f0fdf4;padding:15px;border-radius:4px;border:1px solid #86efac;margin:20px 0;">
      <p style="margin:0;color:#15803d;font-size:13px;">Total Amount: <strong>{$total}</strong><br>The remaining balance is due 3 days before your event.</p>
    </div>
    <p><a href="{$customerUrl}" style="display:inline-block;padding:12px 30px;background:#6b4459;color:white;text-decoration:none;border-radius:4px;font-weight:bold;">View My Booking</a></p>
  </div>
</div>
HTML;
            $this->mailer->AltBody = "Payment verified for Booking #{$bookingId}. View details at: {$customerUrl}";
            if (!$this->mailer->send()) $sent = false;
        } catch (Exception $e) {
            error_log('Email send error (customer): ' . $e->getMessage());
            $sent = false;
        }

        // Email to each supplier
        foreach ($suppliers as $supplier) {
            if (empty($supplier['email'])) continue;
            try {
                $this->mailer->clearAddresses();
                $this->mailer->addAddress($supplier['email'], $supplier['name'] ?? $supplier['shop_name'] ?? 'Supplier');
                $this->mailer->isHTML(true);
                $this->mailer->Subject = 'New Confirmed Booking — Event Details #' . $bookingId;
                $supplierName = htmlspecialchars($supplier['shop_name'] ?? $supplier['name'] ?? 'Supplier', ENT_QUOTES);
                $customerName = htmlspecialchars($customer['name'], ENT_QUOTES);
                $this->mailer->Body = <<<HTML
<div style="font-family:Poppins,sans-serif;max-width:600px;margin:0 auto;color:#333;">
  <div style="background:linear-gradient(135deg,#1e40af 0%,#60a5fa 100%);padding:30px;border-radius:8px 8px 0 0;color:white;text-align:center;">
    <h1 style="margin:0;font-size:24px;">New Confirmed Booking</h1>
    <p style="margin:8px 0 0;opacity:.85;">Payment verified — action required</p>
  </div>
  <div style="padding:30px;background:#faf6f1;border-radius:0 0 8px 8px;">
    <p>Dear {$supplierName},</p>
    <p>A booking from <strong>{$customerName}</strong> has been confirmed with verified payment. Please review the event details below:</p>
    <table style="width:100%;border-collapse:collapse;margin:20px 0;">
      <thead><tr>
        <th style="text-align:left;color:#999;font-size:12px;padding-bottom:6px;">SERVICE</th>
        <th style="text-align:left;color:#999;font-size:12px;padding-bottom:6px;">DATE</th>
        <th style="text-align:right;color:#999;font-size:12px;padding-bottom:6px;">TIME</th>
      </tr></thead>
      <tbody>{$itemRows}</tbody>
    </table>
    <p><a href="{$supplierUrl}" style="display:inline-block;padding:12px 30px;background:#1e40af;color:white;text-decoration:none;border-radius:4px;font-weight:bold;">View Booking Details</a></p>
  </div>
</div>
HTML;
                $this->mailer->AltBody = "Confirmed booking #{$bookingId} from {$customerName}. View at: {$supplierUrl}";
                if (!$this->mailer->send()) $sent = false;
            } catch (Exception $e) {
                error_log('Email send error (supplier): ' . $e->getMessage());
                $sent = false;
            }
        }

        // Admin copy
        if (!empty($admin['email'])) {
            try {
                $this->mailer->clearAddresses();
                $this->mailer->addAddress($admin['email'], $admin['name'] ?? 'Admin');
                $this->mailer->isHTML(true);
                $this->mailer->Subject = '[Admin] Payment Verified — Booking #' . $bookingId;
                $customerName = htmlspecialchars($customer['name'], ENT_QUOTES);
                $customerEmail = htmlspecialchars($customer['email'], ENT_QUOTES);
                $this->mailer->Body = <<<HTML
<div style="font-family:Poppins,sans-serif;max-width:600px;margin:0 auto;color:#333;">
  <div style="background:#374151;padding:30px;border-radius:8px 8px 0 0;color:white;text-align:center;">
    <h1 style="margin:0;font-size:22px;">[Admin] Payment Verified</h1>
    <p style="margin:8px 0 0;opacity:.75;">Booking #{$bookingId}</p>
  </div>
  <div style="padding:30px;background:#faf6f1;border-radius:0 0 8px 8px;">
    <p><strong>Customer:</strong> {$customerName} ({$customerEmail})<br><strong>Total:</strong> {$total}</p>
    <table style="width:100%;border-collapse:collapse;margin:20px 0;">
      <thead><tr>
        <th style="text-align:left;color:#999;font-size:12px;padding-bottom:6px;">SERVICE</th>
        <th style="text-align:left;color:#999;font-size:12px;padding-bottom:6px;">DATE</th>
        <th style="text-align:right;color:#999;font-size:12px;padding-bottom:6px;">TIME</th>
      </tr></thead>
      <tbody>{$itemRows}</tbody>
    </table>
    <p><a href="{$customerUrl}" style="display:inline-block;padding:10px 24px;background:#374151;color:white;text-decoration:none;border-radius:4px;">View Booking</a></p>
  </div>
</div>
HTML;
                $this->mailer->AltBody = "[Admin] Booking #{$bookingId} payment verified. Customer: {$customerName}. View: {$customerUrl}";
                if (!$this->mailer->send()) $sent = false;
            } catch (Exception $e) {
                error_log('Email send error (admin): ' . $e->getMessage());
                $sent = false;
            }
        }

        return $sent;
    }

    /**
     * Notify customer that a supplier accepted their booking.
     */
    public function sendSupplierAccepted(array $customer, string $shopName, string $serviceName, string $eventDate, int $bookingId): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($customer['email'], $customer['name']);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Supplier Accepted Your Booking — ' . $serviceName;
            $bookingUrl = URLROOT . '/booking/detail/' . $bookingId;
            $shopHtml    = htmlspecialchars($shopName, ENT_QUOTES);
            $serviceHtml = htmlspecialchars($serviceName, ENT_QUOTES);
            $dateHtml    = htmlspecialchars($eventDate, ENT_QUOTES);
            $customerHtml = htmlspecialchars($customer['name'], ENT_QUOTES);
            $this->mailer->Body = <<<HTML
<div style="font-family:Poppins,sans-serif;max-width:600px;margin:0 auto;color:#333;">
  <div style="background:linear-gradient(135deg,#166534 0%,#4ade80 100%);padding:30px;border-radius:8px 8px 0 0;color:white;text-align:center;">
    <h1 style="margin:0;font-size:24px;">✓ Supplier Accepted!</h1>
  </div>
  <div style="padding:30px;background:#faf6f1;border-radius:0 0 8px 8px;">
    <p>Dear {$customerHtml},</p>
    <p><strong>{$shopHtml}</strong> has accepted your booking for <strong>{$serviceHtml}</strong> on <strong>{$dateHtml}</strong>.</p>
    <p>Please complete your deposit payment to confirm the booking.</p>
    <p><a href="{$bookingUrl}" style="display:inline-block;padding:12px 30px;background:#6b4459;color:white;text-decoration:none;border-radius:4px;font-weight:bold;">View Booking &amp; Pay</a></p>
  </div>
</div>
HTML;
            $this->mailer->AltBody = "{$shopName} accepted your booking for {$serviceName} on {$eventDate}. View: {$bookingUrl}";
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log('Email send error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Notify customer that a supplier declined their booking.
     */
    public function sendSupplierDeclined(array $customer, string $shopName, string $serviceName, string $eventDate, int $bookingId): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($customer['email'], $customer['name']);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Booking Declined — ' . $serviceName;
            $bookingUrl   = URLROOT . '/booking/myBookings';
            $shopHtml     = htmlspecialchars($shopName, ENT_QUOTES);
            $serviceHtml  = htmlspecialchars($serviceName, ENT_QUOTES);
            $dateHtml     = htmlspecialchars($eventDate, ENT_QUOTES);
            $customerHtml = htmlspecialchars($customer['name'], ENT_QUOTES);
            $this->mailer->Body = <<<HTML
<div style="font-family:Poppins,sans-serif;max-width:600px;margin:0 auto;color:#333;">
  <div style="background:linear-gradient(135deg,#991b1b 0%,#f87171 100%);padding:30px;border-radius:8px 8px 0 0;color:white;text-align:center;">
    <h1 style="margin:0;font-size:24px;">Booking Declined</h1>
  </div>
  <div style="padding:30px;background:#faf6f1;border-radius:0 0 8px 8px;">
    <p>Dear {$customerHtml},</p>
    <p>Unfortunately, <strong>{$shopHtml}</strong> is unavailable for <strong>{$serviceHtml}</strong> on <strong>{$dateHtml}</strong>.</p>
    <p>You can search for another supplier or contact us for assistance.</p>
    <p><a href="{$bookingUrl}" style="display:inline-block;padding:12px 30px;background:#6b4459;color:white;text-decoration:none;border-radius:4px;font-weight:bold;">View My Bookings</a></p>
  </div>
</div>
HTML;
            $this->mailer->AltBody = "{$shopName} declined your booking for {$serviceName} on {$eventDate}.";
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log('Email send error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Notify suppliers of a new custom booking request.
     */
    public function sendNewBookingRequest(array $supplier, string $customerName, array $items, int $bookingId): bool
    {
        if (empty($supplier['email'])) return false;
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($supplier['email'], $supplier['name'] ?? $supplier['shop_name'] ?? 'Supplier');
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'New Booking Request — Please Respond Within 48 Hours';
            $bookingUrl    = URLROOT . '/supplier/bookingDetail/' . $bookingId;
            $supplierHtml  = htmlspecialchars($supplier['shop_name'] ?? $supplier['name'] ?? 'Supplier', ENT_QUOTES);
            $customerHtml  = htmlspecialchars($customerName, ENT_QUOTES);
            $itemRows = '';
            foreach ($items as $item) {
                $name = htmlspecialchars($item['service_name'] ?? 'Service', ENT_QUOTES);
                $date = !empty($item['selected_date']) ? date('l, M j, Y', strtotime($item['selected_date'])) : 'TBD';
                $itemRows .= "<tr><td style='padding:6px 0;border-bottom:1px solid #f0e8e0;color:#555;'>{$name}</td><td style='padding:6px 0;border-bottom:1px solid #f0e8e0;color:#888;text-align:right;'>{$date}</td></tr>";
            }
            $this->mailer->Body = <<<HTML
<div style="font-family:Poppins,sans-serif;max-width:600px;margin:0 auto;color:#333;">
  <div style="background:linear-gradient(135deg,#92400e 0%,#fbbf24 100%);padding:30px;border-radius:8px 8px 0 0;color:white;text-align:center;">
    <h1 style="margin:0;font-size:24px;">New Booking Request</h1>
    <p style="margin:8px 0 0;opacity:.85;">Response required within 48 hours</p>
  </div>
  <div style="padding:30px;background:#faf6f1;border-radius:0 0 8px 8px;">
    <p>Dear {$supplierHtml},</p>
    <p><strong>{$customerHtml}</strong> has requested your services. Please review and respond within <strong>48 hours</strong>:</p>
    <table style="width:100%;border-collapse:collapse;margin:20px 0;">
      <thead><tr><th style="text-align:left;color:#999;font-size:12px;padding-bottom:6px;">SERVICE</th><th style="text-align:right;color:#999;font-size:12px;padding-bottom:6px;">DATE</th></tr></thead>
      <tbody>{$itemRows}</tbody>
    </table>
    <p><a href="{$bookingUrl}" style="display:inline-block;padding:12px 30px;background:#92400e;color:white;text-decoration:none;border-radius:4px;font-weight:bold;">Accept or Decline</a></p>
  </div>
</div>
HTML;
            $this->mailer->AltBody = "New booking request from {$customerName}. Respond within 48 hours: {$bookingUrl}";
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
