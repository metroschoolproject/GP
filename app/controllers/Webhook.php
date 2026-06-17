<?php

class Webhook extends Controller
{
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
