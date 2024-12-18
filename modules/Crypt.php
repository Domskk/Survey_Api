<?php
class Crypt {
    private $cipher;
    private $secret_key;

    public function __construct($secret_key) {
        $this->cipher = "AES-256-CBC";
        $this->secret_key = $secret_key;
    }

    public function encryptData($data) {
        if (empty($data) || !is_string($data)) {
            throw new Exception("Invalid data for encryption.");
        }

        $iv = random_bytes(16); // Generate a random IV for each encryption
        $encryptedString = openssl_encrypt($data, $this->cipher, $this->secret_key, OPENSSL_RAW_DATA, $iv);

        if (!$encryptedString) {
            throw new Exception("Encryption failed.");
        }

        // Concatenate IV with the encrypted data and encode in base64
        $encryptedBase64 = base64_encode($iv . $encryptedString);
        return json_encode(["data" => $encryptedBase64]);
    }

    public function decryptData($input) {
        // Check if input is JSON and decode it
        $data = is_array($input) ? $input : json_decode($input, true);
        if (!isset($data['data'])) {
            throw new Exception("Invalid encrypted data format.");
        }

        $decoded = base64_decode($data['data']);
        if ($decoded === false) {
            throw new Exception("Base64 decoding failed.");
        }

        // Extract IV (first 16 bytes) and encrypted data (remaining bytes)
        $iv = substr($decoded, 0, 16);
        $encrypted_data = substr($decoded, 16);

        // Perform decryption
        $decrypted = openssl_decrypt($encrypted_data, $this->cipher, $this->secret_key, OPENSSL_RAW_DATA, $iv);

        if (!$decrypted) {
            throw new Exception("Decryption failed.");
        }

        return $decrypted;
    }
}
?>