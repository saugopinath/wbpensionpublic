<?php

namespace App\Helpers;
use Illuminate\Support\Facades\Config;
use Illuminate\Encryption\Encrypter;
class EncryptDecrypt
{
    

   public static function encrypt($payload)
    {
        // Ensure the custom key is the correct length and format
        // A base64 encoded 32-character string is typical for AES-256
        //$key = base64_decode($customKey); 
        $customKey = config('app.APP_SECRET_KEY'); 
        $sek = str_pad(base64_decode($customKey, true), 32, "\0");
        $cipher = Config::get('app.cipher'); // e.g., 'AES-256-CBC'

        // Create a new Encrypter instance with the custom key
        $encrypter = new Encrypter($sek, $cipher);

        // Encrypt the payload. Laravel will automatically serialize the array/object to JSON
        // before encryption and then base64 encode the final encrypted string (which is a JSON object itself)
        $encryptedPayload = $encrypter->encrypt($payload);

        return $encryptedPayload;
    }
    public static function decrypt($encryptedPayload)
    {
       
          $customKey = config('app.APP_SECRET_KEY'); 
          $sek = str_pad(base64_decode($customKey, true), 32, "\0");
          $cipher = Config::get('app.cipher'); // e.g., 'AES-256-CBC'

          // Create a new Encrypter instance with the custom key
          $encrypter = new Encrypter($sek, $cipher);
          $decrypted = $encrypter->decrypt( $encryptedPayload );
          return $decrypted;
    }
}
