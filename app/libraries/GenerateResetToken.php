<?php
class GenerateResetToken{
    public function generateResetToken(int $bytes = 32):array
    {
        $token = bin2hex(random_bytes($bytes));
        $tokenHash = hash('sha256',$token);
        return ['token'=>$token,'token_hash' => $tokenHash];
    }
}

?>