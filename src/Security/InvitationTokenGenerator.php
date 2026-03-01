<?php

namespace App\Security;

final class InvitationTokenGenerator implements InvitationTokenGeneratorInterface
{
    
    public function generate(int $bytes = 32): string {

        return bin2hex(random_bytes($bytes));
    }

}