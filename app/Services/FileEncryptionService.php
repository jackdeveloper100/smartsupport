<?php
namespace App\Services;

class FileEncryptionService
{
    private $encryptionKey;
    private $encryptionMethod = 'aes-256-cbc';

    public function __construct($encryptionKey)
    {
        // AES-256 requires a 32-byte key
        $this->encryptionKey = hash('sha256', $encryptionKey, true);
    }

    public function encryptFile($inputFilePath, $outputFilePath)
    {
        $plaintext = file_get_contents($inputFilePath);

        if ($plaintext === false) {
            throw new \Exception("Cannot read input file: $inputFilePath");
        }

        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->encryptionMethod));

        $encrypted = openssl_encrypt(
            $plaintext,
            $this->encryptionMethod,
            $this->encryptionKey,
            OPENSSL_RAW_DATA,
            $iv
        );

        $encryptedData = base64_encode($iv . $encrypted);
        if (file_put_contents($outputFilePath, $encryptedData) === false) {
            throw new \Exception("Cannot write to output file: $outputFilePath");
        }

        return true;
    }

    public function decryptFile($encryptedFilePath, $outputFilePath)
    {
        $encryptedContent = file_get_contents($encryptedFilePath);
        if ($encryptedContent === false) {
            throw new \Exception("Cannot read encrypted file: $encryptedFilePath");
        }

        $data = base64_decode($encryptedContent, true);

        if ($data === false) {
            throw new \Exception("Invalid encrypted file format");
        }

        $ivLength = openssl_cipher_iv_length($this->encryptionMethod);
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);

        $plaintext = openssl_decrypt(
            $encrypted,
            $this->encryptionMethod,
            $this->encryptionKey,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($plaintext === false) {
            throw new \Exception("Decryption failed - wrong password or corrupted file");
        }

        if (file_put_contents($outputFilePath, $plaintext) === false) {
            throw new \Exception("Cannot write to output file: $outputFilePath");
        }

        return true;
    }
}
