<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

try {
    $payload = 'ZXlKcGRpSTZJbmt6TURoUGNHVlhWRWsyUjAxRFVtSkVXVVZ3UW5jOVBTSXNJblpoYkhWbElqb2lNblJHVTFVMlkyZHZUVFpLT0RWNE1HTjFTMlk0T0Zwa2JWVnhkbTVJYVZCbGQweFBSM0pMY0dJMGRFWkxVVTFMSzBzdmMySkVWR1ZEVVROVWVUQXpTbGg2YUZKRU1YWnJUVVJGZW5vMGJ5OTZWQzgyUnpoTk1UUlJUbkpHY1haU01VVXpOakJ4Tm5wVGNIaGtTV1ZGTVN0TFZWYzJjbVp0VmtKWFlYbFNTa2t3ZHpVcmVURkdWVTVOYTNCamFGSXlNWGxXWVZKd1pEQlRNbFpFTjJaVmFWWnpaMkpwV1ROTGRsRndSRlUyWVd4WFZWbDNPVXBCZVRFdlNrRkZZakJJZWtoTlVrZE9lbWt5TVVwMU9FOXBZbmxqVEdsV1UzcEViek5YVDNwaU9FUlNPVUVyV0hSWmFsWm5SVDBpTENKdFlXTWlPaUptWVdZeVpHVmhORE0xTkdaaE5HSXlabVJpTXpjNE16bGhZamxrTUdRMVlUY3labUptWVRCaU1qZzVZekl3TWpReU9HVTVabVl6TURRMk56TmhOMlE0SWl3aWRHRm5Ja2x1WjI4OUlpd2libVZrSWpvaUlpdDk=';
    
    // First, let's see what happens if we decrypt the raw payload directly:
    try {
        $decryptedDirect = App\Helpers\EncryptDecrypt::decrypt($payload);
        echo "Direct decryption success:\n";
        print_r($decryptedDirect);
        echo "\n\n";
    } catch (\Exception $e) {
        echo "Direct decryption failed: " . $e->getMessage() . "\n\n";
    }

    // Second, let's see if we base64_decode it first:
    try {
        $decoded = base64_decode($payload);
        $decryptedDecoded = App\Helpers\EncryptDecrypt::decrypt($decoded);
        echo "Decryption after base64_decode success:\n";
        print_r($decryptedDecoded);
        echo "\n\n";
    } catch (\Exception $e) {
        echo "Decryption after base64_decode failed: " . $e->getMessage() . "\n\n";
    }

} catch (\Exception $e) {
    echo "General failure: " . $e->getMessage() . "\n";
}
