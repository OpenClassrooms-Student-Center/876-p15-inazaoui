<?php

namespace App\Security;

interface InvitationTokenGeneratorInterface {
    
    public function generate(int $bytes = 32): string;

}