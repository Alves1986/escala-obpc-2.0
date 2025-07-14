<?php
require_once __DIR__ . '/../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class FirebaseAuth {
    private static $projectId = 'escala-obpc-8c54f';

    public static function verifyToken($idToken) {
        try {
            // Obter chave pública do Firebase
            $publicKeys = json_decode(file_get_contents('https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com'), true);
            $decoded = JWT::decode($idToken, new Key(reset($publicKeys), 'RS256'));

            if ($decoded->aud !== self::$projectId) {
                throw new Exception('Invalid audience');
            }
            if ($decoded->iss !== 'https://securetoken.google.com/' . self::$projectId) {
                throw new Exception('Invalid issuer');
            }
            if ($decoded->exp < time()) {
                throw new Exception('Token expired');
            }

            return [
                'user_id' => $decoded->sub,
                'email' => $decoded->email,
                'name' => $decoded->name ?? '',
            ];
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid token: ' . $e->getMessage()]);
            exit;
        }
    }
}
?>