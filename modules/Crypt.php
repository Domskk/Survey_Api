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
            return [
                "message" => "Invalid data for encryption.",
                "code" => 400
            ];
        }

        try {
            $iv = random_bytes(16); 
            $encryptedString = openssl_encrypt($data, $this->cipher, $this->secret_key, OPENSSL_RAW_DATA, $iv);

            if (!$encryptedString) {
                throw new Exception("Encryption failed.");
            }

            $encryptedBase64 = base64_encode($iv . $encryptedString);
            return [
                "data" => $encryptedBase64,
                "code" => 200
            ];
        } catch (Exception $e) {
            return [
                "message" => $e->getMessage(),
                "code" => 500
            ];
        }
    }

    public function decryptData($input) {
        $data = is_array($input) ? $input : json_decode($input, true);
        if (!isset($data['data'])) {
            return [
                "message" => "Invalid encrypted data format.",
                "code" => 400
            ];
        }

        try {
            $decoded = base64_decode($data['data']);
            if ($decoded === false) {
                throw new Exception("Base64 decoding failed.");
            }

            $iv = substr($decoded, 0, 16);
            $encrypted_data = substr($decoded, 16);

            $decrypted = openssl_decrypt($encrypted_data, $this->cipher, $this->secret_key, OPENSSL_RAW_DATA, $iv);

            if (!$decrypted) {
                throw new Exception("Decryption failed.");
            }

            return [
                "data" => $decrypted,
                "code" => 200
            ];
        } catch (Exception $e) {
            return [
                "message" => $e->getMessage(),
                "code" => 500
            ];
        }
    }
}
?>
