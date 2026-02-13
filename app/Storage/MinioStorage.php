<?php
namespace App\Storage;

/**
 * MinIO / S3-compatible storage driver
 * Uses MinIO's S3 API via PHP curl (no extra SDK required)
 */
class MinioStorage implements StorageInterface {
    private string $endpoint;
    private string $bucket;
    private string $accessKey;
    private string $secretKey;
    private string $region;
    private bool $useSSL;

    public function __construct() {
        $this->endpoint  = env('MINIO_ENDPOINT', 'localhost:9000');
        $this->bucket    = env('MINIO_BUCKET', 'uploads');
        $this->accessKey = env('MINIO_ACCESS_KEY', 'minioadmin');
        $this->secretKey = env('MINIO_SECRET_KEY', 'minioadmin');
        $this->region    = env('MINIO_REGION', 'us-east-1');
        $this->useSSL    = env('MINIO_USE_SSL', 'false') === 'true';
    }

    public function put(string $sourcePath, string $destination): bool {
        $content = file_get_contents($sourcePath);
        $contentType = mime_content_type($sourcePath) ?: 'application/octet-stream';

        $date = gmdate('Ymd\THis\Z');
        $dateShort = gmdate('Ymd');
        $method = 'PUT';
        $uri = '/' . $this->bucket . '/' . $destination;

        $headers = $this->signRequest($method, $uri, $content, $contentType, $date, $dateShort);

        $protocol = $this->useSSL ? 'https' : 'http';
        $url = $protocol . '://' . $this->endpoint . $uri;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => $content,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200;
    }

    public function delete(string $path): bool {
        $date = gmdate('Ymd\THis\Z');
        $dateShort = gmdate('Ymd');
        $method = 'DELETE';
        $uri = '/' . $this->bucket . '/' . $path;

        $headers = $this->signRequest($method, $uri, '', '', $date, $dateShort);

        $protocol = $this->useSSL ? 'https' : 'http';
        $url = $protocol . '://' . $this->endpoint . $uri;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);

        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 204 || $httpCode === 200;
    }

    public function exists(string $path): bool {
        $date = gmdate('Ymd\THis\Z');
        $dateShort = gmdate('Ymd');
        $uri = '/' . $this->bucket . '/' . $path;

        $headers = $this->signRequest('HEAD', $uri, '', '', $date, $dateShort);

        $protocol = $this->useSSL ? 'https' : 'http';
        $url = $protocol . '://' . $this->endpoint . $uri;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_NOBODY => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
        ]);

        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200;
    }

    public function url(string $path): string {
        $protocol = $this->useSSL ? 'https' : 'http';
        return $protocol . '://' . $this->endpoint . '/' . $this->bucket . '/' . $path;
    }

    public function getFullPath(string $path): string {
        // For MinIO, return the URL (used for streaming downloads)
        return $this->url($path);
    }

    /**
     * AWS Signature V4 signing for S3-compatible APIs
     */
    private function signRequest(string $method, string $uri, string $payload, string $contentType, string $date, string $dateShort): array {
        $payloadHash = hash('sha256', $payload);
        $host = $this->endpoint;

        $canonicalHeaders = "host:{$host}\nx-amz-content-sha256:{$payloadHash}\nx-amz-date:{$date}\n";
        $signedHeaders = 'host;x-amz-content-sha256;x-amz-date';

        if ($contentType) {
            $canonicalHeaders = "content-type:{$contentType}\n" . $canonicalHeaders;
            $signedHeaders = 'content-type;' . $signedHeaders;
        }

        $canonicalRequest = "{$method}\n{$uri}\n\n{$canonicalHeaders}\n{$signedHeaders}\n{$payloadHash}";
        $credentialScope = "{$dateShort}/{$this->region}/s3/aws4_request";
        $stringToSign = "AWS4-HMAC-SHA256\n{$date}\n{$credentialScope}\n" . hash('sha256', $canonicalRequest);

        $signingKey = hash_hmac('sha256', 'aws4_request',
            hash_hmac('sha256', 's3',
                hash_hmac('sha256', $this->region,
                    hash_hmac('sha256', $dateShort, 'AWS4' . $this->secretKey, true),
                true),
            true),
        true);

        $signature = hash_hmac('sha256', $stringToSign, $signingKey);
        $authorization = "AWS4-HMAC-SHA256 Credential={$this->accessKey}/{$credentialScope}, SignedHeaders={$signedHeaders}, Signature={$signature}";

        $headers = [
            "Host: {$host}",
            "x-amz-date: {$date}",
            "x-amz-content-sha256: {$payloadHash}",
            "Authorization: {$authorization}",
        ];

        if ($contentType) {
            array_unshift($headers, "Content-Type: {$contentType}");
        }

        return $headers;
    }
}
