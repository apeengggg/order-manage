<?php
namespace App\Storage;

class StorageFactory {
    private static ?StorageInterface $instance = null;

    public static function make(): StorageInterface {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $driver = env('STORAGE_DRIVER', 'local');

        switch ($driver) {
            case 'minio':
            case 's3':
                self::$instance = new MinioStorage();
                break;
            case 'local':
            default:
                self::$instance = new LocalStorage();
                break;
        }

        return self::$instance;
    }
}
