<?php

class Webhook extends Controller
{
    /**
     * Legacy gateway endpoints — all payment and payout flows are now manual.
     * Returning 410 Gone so any stale integrations get a clear signal.
     */
    public function paymentGatewayCallback()
    {
        http_response_code(410);
        echo json_encode(['error' => 'Gateway webhooks are not used in the manual payment flow.']);
    }

    public function payoutCallback()
    {
        http_response_code(410);
        echo json_encode(['error' => 'Payout webhooks are not used in the manual payment flow.']);
    }
}
