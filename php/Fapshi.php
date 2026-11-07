<?php
declare(strict_types=1);

/**
 * Small dependency-free client for the Fapshi API.
 * Credentials are passed at runtime and are never stored in this file.
 */
final class Fapshi
{
    private string $apiUser;
    private string $apiKey;
    private string $baseUrl;

    public function __construct(array $config = [])
    {
        $this->apiUser = trim((string) ($config['apiuser'] ?? ''));
        $this->apiKey = trim((string) ($config['apikey'] ?? ''));
        $this->baseUrl = rtrim((string) ($config['base_url'] ?? 'https://sandbox.fapshi.com'), '/');
    }

    public function isConfigured(): bool
    {
        return $this->apiUser !== '' && $this->apiKey !== '';
    }

    public function initiate_pay(array $data): array
    {
        return $this->request('POST', '/initiate-pay', $data);
    }

    public function direct_pay(array $data): array
    {
        if (!isset($data['amount'], $data['phone']) || !is_int($data['amount']) || $data['amount'] < 100) {
            return ['statusCode' => 400, 'message' => 'Invalid amount or phone number.'];
        }
        if (!preg_match('/^6[0-9]{8}$/', (string) $data['phone'])) {
            return ['statusCode' => 400, 'message' => 'Invalid Cameroon phone number.'];
        }
        return $this->request('POST', '/direct-pay', $data);
    }

    public function payment_status(string $transId): array
    {
        if (!preg_match('/^[a-zA-Z0-9]{8,10}$/', $transId)) {
            return ['statusCode' => 400, 'message' => 'Invalid transaction ID.'];
        }
        return $this->request('GET', '/payment-status/' . rawurlencode($transId));
    }

    public function balance(): array
    {
        return $this->request('GET', '/balance');
    }

    public function get_user_trans(string $userId): array
    {
        if (!preg_match('/^[a-zA-Z0-9_-]{1,100}$/', $userId)) {
            return ['statusCode' => 400, 'message' => 'Invalid user ID.'];
        }
        return $this->request('GET', '/transaction/' . rawurlencode($userId));
    }

    public function search(array $params = []): array
    {
        return $this->request('GET', '/search?' . http_build_query($params));
    }

    private function request(string $method, string $path, ?array $payload = null): array
    {
        if (!$this->isConfigured()) {
            return ['statusCode' => 503, 'message' => 'Fapshi credentials are not configured.'];
        }
        if (!function_exists('curl_init')) {
            return ['statusCode' => 503, 'message' => 'The PHP cURL extension is required.'];
        }

        $headers = [
            'apiuser: ' . $this->apiUser,
            'apikey: ' . $this->apiKey,
            'Accept: application/json',
            'Content-Type: application/json',
        ];
        $options = [
            CURLOPT_URL => $this->baseUrl . $path,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ];
        if ($payload !== null) {
            $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            $options[CURLOPT_POSTFIELDS] = $json;
        }

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $body = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if ($body === false) {
            error_log('Fapshi request failed: ' . $error);
            return ['statusCode' => 0, 'message' => 'Payment service could not be reached.'];
        }
        $decoded = json_decode((string) $body, true);
        if (!is_array($decoded)) {
            error_log('Fapshi returned a non-JSON response (HTTP ' . $status . ').');
            return ['statusCode' => $status ?: 502, 'message' => 'Invalid response from payment service.'];
        }
        $decoded['statusCode'] = $status;
        return $decoded;
    }
}
