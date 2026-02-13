<?php
namespace App\Validation;

/**
 * Laravel-like Validator
 *
 * Usage:
 *   $v = new Validator($_POST, [
 *       'name'  => 'required|string|min:3|max:100',
 *       'email' => 'required|email|max:255',
 *       'age'   => 'nullable|integer|min:1|max:150',
 *       'role'  => 'required|in:admin,cs,manager',
 *       'photo' => 'nullable|file|image|max_file:5120',
 *   ]);
 *
 *   if ($v->fails()) {
 *       $errors = $v->errors();      // ['name' => ['The name field is required.']]
 *       $first  = $v->firstError();   // 'The name field is required.'
 *   }
 *
 *   $clean = $v->validated(); // only validated fields
 */
class Validator {
    private array $data;
    private array $files;
    private array $rules;
    private array $errors = [];
    private array $validated = [];
    private array $customMessages;

    /**
     * Custom attribute display names
     */
    private array $attributes;

    public function __construct(array $data, array $rules, array $customMessages = [], array $attributes = []) {
        $this->data = $data;
        $this->files = $_FILES;
        $this->rules = $rules;
        $this->customMessages = $customMessages;
        $this->attributes = $attributes;
        $this->validate();
    }

    /**
     * Static factory
     */
    public static function make(array $data, array $rules, array $messages = [], array $attributes = []): self {
        return new self($data, $rules, $messages, $attributes);
    }

    public function fails(): bool {
        return !empty($this->errors);
    }

    public function passes(): bool {
        return empty($this->errors);
    }

    public function errors(): array {
        return $this->errors;
    }

    /**
     * Get first error message (useful for flash)
     */
    public function firstError(): ?string {
        foreach ($this->errors as $fieldErrors) {
            return $fieldErrors[0] ?? null;
        }
        return null;
    }

    /**
     * Get all error messages as flat array
     */
    public function allErrors(): array {
        $all = [];
        foreach ($this->errors as $fieldErrors) {
            foreach ($fieldErrors as $msg) {
                $all[] = $msg;
            }
        }
        return $all;
    }

    /**
     * Get errors for a specific field
     */
    public function fieldErrors(string $field): array {
        return $this->errors[$field] ?? [];
    }

    /**
     * Get only validated/clean data
     */
    public function validated(): array {
        return $this->validated;
    }

    private function validate(): void {
        foreach ($this->rules as $field => $ruleString) {
            $rules = is_array($ruleString) ? $ruleString : explode('|', $ruleString);
            $value = $this->getValue($field);
            $isNullable = in_array('nullable', $rules);
            $isFile = in_array('file', $rules) || in_array('image', $rules);

            // If nullable and value is empty, skip validation
            if ($isNullable && $this->isEmpty($value, $isFile, $field)) {
                continue;
            }

            $hasError = false;
            foreach ($rules as $rule) {
                if ($rule === 'nullable') continue;

                $params = [];
                if (strpos($rule, ':') !== false) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }

                $method = 'rule' . str_replace('_', '', ucwords($rule, '_'));
                if (method_exists($this, $method)) {
                    $error = $this->$method($field, $value, $params, $isFile);
                    if ($error !== null) {
                        $this->addError($field, $rule, $error);
                        $hasError = true;
                        // Stop on first error for 'required' to avoid cascading errors
                        if ($rule === 'required') break;
                    }
                }
            }

            if (!$hasError) {
                $this->validated[$field] = $value;
            }
        }
    }

    private function getValue(string $field) {
        // Support dot notation for nested data
        if (strpos($field, '.') !== false) {
            $keys = explode('.', $field);
            $value = $this->data;
            foreach ($keys as $key) {
                if (!is_array($value) || !array_key_exists($key, $value)) {
                    return null;
                }
                $value = $value[$key];
            }
            return $value;
        }
        return $this->data[$field] ?? null;
    }

    private function isEmpty($value, bool $isFile, string $field): bool {
        if ($isFile) {
            $file = $this->files[$field] ?? null;
            return !$file || $file['error'] === UPLOAD_ERR_NO_FILE;
        }
        return $value === null || $value === '' || $value === [];
    }

    private function addError(string $field, string $rule, string $message): void {
        // Check for custom message
        $customKey = "$field.$rule";
        if (isset($this->customMessages[$customKey])) {
            $message = $this->customMessages[$customKey];
        } elseif (isset($this->customMessages[$field])) {
            $message = $this->customMessages[$field];
        }

        $this->errors[$field][] = $message;
    }

    private function getLabel(string $field): string {
        if (isset($this->attributes[$field])) {
            return $this->attributes[$field];
        }
        return str_replace(['_', '-'], ' ', $field);
    }

    // ========================================
    // Validation Rules
    // ========================================

    private function ruleRequired(string $field, $value, array $params, bool $isFile): ?string {
        if ($isFile) {
            $file = $this->files[$field] ?? null;
            if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
                return ucfirst($this->getLabel($field)) . ' wajib diisi.';
            }
            return null;
        }
        if ($value === null || $value === '' || $value === []) {
            return ucfirst($this->getLabel($field)) . ' wajib diisi.';
        }
        return null;
    }

    private function ruleString(string $field, $value, array $params, bool $isFile): ?string {
        if ($value !== null && !is_string($value)) {
            return ucfirst($this->getLabel($field)) . ' harus berupa teks.';
        }
        return null;
    }

    private function ruleInteger(string $field, $value, array $params, bool $isFile): ?string {
        if ($value !== null && !ctype_digit(ltrim((string)$value, '-')) && !is_int($value)) {
            return ucfirst($this->getLabel($field)) . ' harus berupa angka bulat.';
        }
        return null;
    }

    private function ruleNumeric(string $field, $value, array $params, bool $isFile): ?string {
        if ($value !== null && !is_numeric($value)) {
            return ucfirst($this->getLabel($field)) . ' harus berupa angka.';
        }
        return null;
    }

    private function ruleFloat(string $field, $value, array $params, bool $isFile): ?string {
        return $this->ruleNumeric($field, $value, $params, $isFile);
    }

    private function ruleEmail(string $field, $value, array $params, bool $isFile): ?string {
        if ($value !== null && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return ucfirst($this->getLabel($field)) . ' harus berupa email yang valid.';
        }
        return null;
    }

    private function ruleUrl(string $field, $value, array $params, bool $isFile): ?string {
        if ($value !== null && !filter_var($value, FILTER_VALIDATE_URL)) {
            return ucfirst($this->getLabel($field)) . ' harus berupa URL yang valid.';
        }
        return null;
    }

    private function ruleIp(string $field, $value, array $params, bool $isFile): ?string {
        if ($value !== null && !filter_var($value, FILTER_VALIDATE_IP)) {
            return ucfirst($this->getLabel($field)) . ' harus berupa alamat IP yang valid.';
        }
        return null;
    }

    private function ruleMin(string $field, $value, array $params, bool $isFile): ?string {
        $min = (float)($params[0] ?? 0);
        if (is_numeric($value)) {
            if ((float)$value < $min) {
                return ucfirst($this->getLabel($field)) . " minimal $min.";
            }
        } elseif (is_string($value)) {
            if (mb_strlen($value) < $min) {
                return ucfirst($this->getLabel($field)) . " minimal $min karakter.";
            }
        } elseif (is_array($value)) {
            if (count($value) < $min) {
                return ucfirst($this->getLabel($field)) . " minimal $min item.";
            }
        }
        return null;
    }

    private function ruleMax(string $field, $value, array $params, bool $isFile): ?string {
        $max = (float)($params[0] ?? 0);
        if (is_numeric($value)) {
            if ((float)$value > $max) {
                return ucfirst($this->getLabel($field)) . " maksimal $max.";
            }
        } elseif (is_string($value)) {
            if (mb_strlen($value) > $max) {
                return ucfirst($this->getLabel($field)) . " maksimal $max karakter.";
            }
        } elseif (is_array($value)) {
            if (count($value) > $max) {
                return ucfirst($this->getLabel($field)) . " maksimal $max item.";
            }
        }
        return null;
    }

    private function ruleBetween(string $field, $value, array $params, bool $isFile): ?string {
        $min = (float)($params[0] ?? 0);
        $max = (float)($params[1] ?? 0);
        if (is_numeric($value)) {
            $v = (float)$value;
            if ($v < $min || $v > $max) {
                return ucfirst($this->getLabel($field)) . " harus antara $min dan $max.";
            }
        } elseif (is_string($value)) {
            $len = mb_strlen($value);
            if ($len < $min || $len > $max) {
                return ucfirst($this->getLabel($field)) . " harus antara $min dan $max karakter.";
            }
        }
        return null;
    }

    private function ruleSize(string $field, $value, array $params, bool $isFile): ?string {
        $size = (int)($params[0] ?? 0);
        if (is_string($value) && mb_strlen($value) !== $size) {
            return ucfirst($this->getLabel($field)) . " harus tepat $size karakter.";
        }
        if (is_array($value) && count($value) !== $size) {
            return ucfirst($this->getLabel($field)) . " harus tepat $size item.";
        }
        return null;
    }

    private function ruleIn(string $field, $value, array $params, bool $isFile): ?string {
        if ($value !== null && !in_array((string)$value, $params, true)) {
            return ucfirst($this->getLabel($field)) . ' harus salah satu dari: ' . implode(', ', $params) . '.';
        }
        return null;
    }

    private function ruleNotIn(string $field, $value, array $params, bool $isFile): ?string {
        if ($value !== null && in_array((string)$value, $params, true)) {
            return ucfirst($this->getLabel($field)) . ' tidak boleh berisi: ' . implode(', ', $params) . '.';
        }
        return null;
    }

    private function ruleBoolean(string $field, $value, array $params, bool $isFile): ?string {
        $acceptable = [true, false, 0, 1, '0', '1', 'true', 'false'];
        if ($value !== null && !in_array($value, $acceptable, true)) {
            return ucfirst($this->getLabel($field)) . ' harus berupa boolean.';
        }
        return null;
    }

    private function ruleAlpha(string $field, $value, array $params, bool $isFile): ?string {
        if ($value !== null && !ctype_alpha(str_replace(' ', '', (string)$value))) {
            return ucfirst($this->getLabel($field)) . ' hanya boleh berisi huruf.';
        }
        return null;
    }

    private function ruleAlphaNum(string $field, $value, array $params, bool $isFile): ?string {
        if ($value !== null && !ctype_alnum(str_replace(' ', '', (string)$value))) {
            return ucfirst($this->getLabel($field)) . ' hanya boleh berisi huruf dan angka.';
        }
        return null;
    }

    private function ruleAlphaDash(string $field, $value, array $params, bool $isFile): ?string {
        if ($value !== null && !preg_match('/^[a-zA-Z0-9_-]+$/', (string)$value)) {
            return ucfirst($this->getLabel($field)) . ' hanya boleh berisi huruf, angka, dash, dan underscore.';
        }
        return null;
    }

    private function ruleRegex(string $field, $value, array $params, bool $isFile): ?string {
        $pattern = $params[0] ?? '';
        if ($value !== null && !preg_match($pattern, (string)$value)) {
            return ucfirst($this->getLabel($field)) . ' format tidak valid.';
        }
        return null;
    }

    private function ruleDate(string $field, $value, array $params, bool $isFile): ?string {
        if ($value !== null && strtotime((string)$value) === false) {
            return ucfirst($this->getLabel($field)) . ' harus berupa tanggal yang valid.';
        }
        return null;
    }

    private function ruleDateFormat(string $field, $value, array $params, bool $isFile): ?string {
        $format = $params[0] ?? 'Y-m-d';
        if ($value !== null) {
            $d = \DateTime::createFromFormat($format, (string)$value);
            if (!$d || $d->format($format) !== (string)$value) {
                return ucfirst($this->getLabel($field)) . " harus format $format.";
            }
        }
        return null;
    }

    private function ruleBefore(string $field, $value, array $params, bool $isFile): ?string {
        $date = $params[0] ?? 'now';
        if ($value !== null && strtotime((string)$value) >= strtotime($date)) {
            return ucfirst($this->getLabel($field)) . " harus sebelum $date.";
        }
        return null;
    }

    private function ruleAfter(string $field, $value, array $params, bool $isFile): ?string {
        $date = $params[0] ?? 'now';
        if ($value !== null && strtotime((string)$value) <= strtotime($date)) {
            return ucfirst($this->getLabel($field)) . " harus setelah $date.";
        }
        return null;
    }

    private function ruleConfirmed(string $field, $value, array $params, bool $isFile): ?string {
        $confirmField = $field . '_confirmation';
        $confirmValue = $this->data[$confirmField] ?? null;
        if ($value !== $confirmValue) {
            return 'Konfirmasi ' . $this->getLabel($field) . ' tidak cocok.';
        }
        return null;
    }

    private function ruleSame(string $field, $value, array $params, bool $isFile): ?string {
        $otherField = $params[0] ?? '';
        $otherValue = $this->data[$otherField] ?? null;
        if ($value !== $otherValue) {
            return ucfirst($this->getLabel($field)) . ' harus sama dengan ' . $this->getLabel($otherField) . '.';
        }
        return null;
    }

    private function ruleDifferent(string $field, $value, array $params, bool $isFile): ?string {
        $otherField = $params[0] ?? '';
        $otherValue = $this->data[$otherField] ?? null;
        if ($value === $otherValue) {
            return ucfirst($this->getLabel($field)) . ' harus berbeda dengan ' . $this->getLabel($otherField) . '.';
        }
        return null;
    }

    private function ruleArray(string $field, $value, array $params, bool $isFile): ?string {
        if ($value !== null && !is_array($value)) {
            return ucfirst($this->getLabel($field)) . ' harus berupa array.';
        }
        return null;
    }

    private function ruleJson(string $field, $value, array $params, bool $isFile): ?string {
        if ($value !== null) {
            json_decode((string)$value);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ucfirst($this->getLabel($field)) . ' harus berupa JSON yang valid.';
            }
        }
        return null;
    }

    private function ruleDigits(string $field, $value, array $params, bool $isFile): ?string {
        $count = (int)($params[0] ?? 0);
        if ($value !== null && (!ctype_digit((string)$value) || strlen((string)$value) !== $count)) {
            return ucfirst($this->getLabel($field)) . " harus $count digit angka.";
        }
        return null;
    }

    private function ruleDigitsBetween(string $field, $value, array $params, bool $isFile): ?string {
        $min = (int)($params[0] ?? 0);
        $max = (int)($params[1] ?? 0);
        if ($value !== null) {
            $len = strlen((string)$value);
            if (!ctype_digit((string)$value) || $len < $min || $len > $max) {
                return ucfirst($this->getLabel($field)) . " harus antara $min dan $max digit.";
            }
        }
        return null;
    }

    private function rulePhone(string $field, $value, array $params, bool $isFile): ?string {
        if ($value !== null && !preg_match('/^[\+]?[0-9\-\s\(\)]{8,20}$/', (string)$value)) {
            return ucfirst($this->getLabel($field)) . ' harus berupa nomor telepon yang valid.';
        }
        return null;
    }

    private function ruleUnique(string $field, $value, array $params, bool $isFile): ?string {
        // unique:table,column,except_id
        $table = $params[0] ?? '';
        $column = $params[1] ?? $field;
        $exceptId = $params[2] ?? null;

        if ($value !== null && $table) {
            $db = getDB();
            $sql = "SELECT COUNT(*) FROM `$table` WHERE `$column` = ?";
            $bindings = [$value];

            if ($exceptId) {
                $sql .= " AND id != ?";
                $bindings[] = $exceptId;
            }

            $stmt = $db->prepare($sql);
            $stmt->execute($bindings);
            if ($stmt->fetchColumn() > 0) {
                return ucfirst($this->getLabel($field)) . ' sudah digunakan.';
            }
        }
        return null;
    }

    private function ruleExists(string $field, $value, array $params, bool $isFile): ?string {
        // exists:table,column
        $table = $params[0] ?? '';
        $column = $params[1] ?? 'id';

        if ($value !== null && $table) {
            $db = getDB();
            $stmt = $db->prepare("SELECT COUNT(*) FROM `$table` WHERE `$column` = ?");
            $stmt->execute([$value]);
            if ($stmt->fetchColumn() == 0) {
                return ucfirst($this->getLabel($field)) . ' tidak ditemukan.';
            }
        }
        return null;
    }

    // ========================================
    // File-specific Rules
    // ========================================

    private function ruleFile(string $field, $value, array $params, bool $isFile): ?string {
        $file = $this->files[$field] ?? null;
        if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null; // handled by 'required'
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ucfirst($this->getLabel($field)) . ' gagal diupload.';
        }
        return null;
    }

    private function ruleImage(string $field, $value, array $params, bool $isFile): ?string {
        $file = $this->files[$field] ?? null;
        if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) return null;

        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
        if (!in_array($file['type'], $allowed)) {
            return ucfirst($this->getLabel($field)) . ' harus berupa file gambar (jpg, png, gif, webp).';
        }
        return null;
    }

    private function ruleMimes(string $field, $value, array $params, bool $isFile): ?string {
        $file = $this->files[$field] ?? null;
        if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) return null;

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $params)) {
            return ucfirst($this->getLabel($field)) . ' harus berformat: ' . implode(', ', $params) . '.';
        }
        return null;
    }

    private function ruleMaxFile(string $field, $value, array $params, bool $isFile): ?string {
        // max_file:5120 (in KB)
        $file = $this->files[$field] ?? null;
        if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) return null;

        $maxKb = (int)($params[0] ?? 0);
        $fileSizeKb = $file['size'] / 1024;
        if ($fileSizeKb > $maxKb) {
            $maxMb = round($maxKb / 1024, 1);
            return ucfirst($this->getLabel($field)) . " maksimal {$maxMb} MB.";
        }
        return null;
    }

    private function ruleMinFile(string $field, $value, array $params, bool $isFile): ?string {
        $file = $this->files[$field] ?? null;
        if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) return null;

        $minKb = (int)($params[0] ?? 0);
        $fileSizeKb = $file['size'] / 1024;
        if ($fileSizeKb < $minKb) {
            return ucfirst($this->getLabel($field)) . " minimal {$minKb} KB.";
        }
        return null;
    }

    private function ruleDimensions(string $field, $value, array $params, bool $isFile): ?string {
        // dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000
        $file = $this->files[$field] ?? null;
        if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) return null;

        $info = getimagesize($file['tmp_name']);
        if (!$info) return ucfirst($this->getLabel($field)) . ' bukan file gambar yang valid.';

        [$width, $height] = $info;
        $constraints = [];
        foreach ($params as $p) {
            [$key, $val] = explode('=', $p);
            $constraints[$key] = (int)$val;
        }

        if (isset($constraints['min_width']) && $width < $constraints['min_width']) {
            return ucfirst($this->getLabel($field)) . " minimal lebar {$constraints['min_width']}px.";
        }
        if (isset($constraints['min_height']) && $height < $constraints['min_height']) {
            return ucfirst($this->getLabel($field)) . " minimal tinggi {$constraints['min_height']}px.";
        }
        if (isset($constraints['max_width']) && $width > $constraints['max_width']) {
            return ucfirst($this->getLabel($field)) . " maksimal lebar {$constraints['max_width']}px.";
        }
        if (isset($constraints['max_height']) && $height > $constraints['max_height']) {
            return ucfirst($this->getLabel($field)) . " maksimal tinggi {$constraints['max_height']}px.";
        }
        if (isset($constraints['width']) && $width !== $constraints['width']) {
            return ucfirst($this->getLabel($field)) . " harus lebar {$constraints['width']}px.";
        }
        if (isset($constraints['height']) && $height !== $constraints['height']) {
            return ucfirst($this->getLabel($field)) . " harus tinggi {$constraints['height']}px.";
        }
        return null;
    }

    // ========================================
    // Conditional / Misc
    // ========================================

    private function ruleRequiredIf(string $field, $value, array $params, bool $isFile): ?string {
        // required_if:other_field,value1,value2
        $otherField = $params[0] ?? '';
        $otherValues = array_slice($params, 1);
        $otherValue = $this->data[$otherField] ?? null;

        if (in_array((string)$otherValue, $otherValues, true)) {
            if ($this->isEmpty($value, $isFile, $field)) {
                return ucfirst($this->getLabel($field)) . ' wajib diisi.';
            }
        }
        return null;
    }

    private function ruleRequiredUnless(string $field, $value, array $params, bool $isFile): ?string {
        $otherField = $params[0] ?? '';
        $otherValues = array_slice($params, 1);
        $otherValue = $this->data[$otherField] ?? null;

        if (!in_array((string)$otherValue, $otherValues, true)) {
            if ($this->isEmpty($value, $isFile, $field)) {
                return ucfirst($this->getLabel($field)) . ' wajib diisi.';
            }
        }
        return null;
    }

    private function ruleRequiredWith(string $field, $value, array $params, bool $isFile): ?string {
        // required_with:field1,field2 - required if any of the listed fields is present
        foreach ($params as $otherField) {
            $otherValue = $this->data[$otherField] ?? null;
            if ($otherValue !== null && $otherValue !== '' && $otherValue !== []) {
                if ($this->isEmpty($value, $isFile, $field)) {
                    return ucfirst($this->getLabel($field)) . ' wajib diisi ketika ' . $this->getLabel($otherField) . ' diisi.';
                }
                break;
            }
        }
        return null;
    }

    private function ruleRequiredWithout(string $field, $value, array $params, bool $isFile): ?string {
        foreach ($params as $otherField) {
            $otherValue = $this->data[$otherField] ?? null;
            if ($otherValue === null || $otherValue === '' || $otherValue === []) {
                if ($this->isEmpty($value, $isFile, $field)) {
                    return ucfirst($this->getLabel($field)) . ' wajib diisi ketika ' . $this->getLabel($otherField) . ' kosong.';
                }
                break;
            }
        }
        return null;
    }

    private function ruleGt(string $field, $value, array $params, bool $isFile): ?string {
        $otherField = $params[0] ?? '';
        $otherValue = $this->data[$otherField] ?? 0;
        if (is_numeric($value) && is_numeric($otherValue) && (float)$value <= (float)$otherValue) {
            return ucfirst($this->getLabel($field)) . ' harus lebih besar dari ' . $this->getLabel($otherField) . '.';
        }
        return null;
    }

    private function ruleLt(string $field, $value, array $params, bool $isFile): ?string {
        $otherField = $params[0] ?? '';
        $otherValue = $this->data[$otherField] ?? 0;
        if (is_numeric($value) && is_numeric($otherValue) && (float)$value >= (float)$otherValue) {
            return ucfirst($this->getLabel($field)) . ' harus lebih kecil dari ' . $this->getLabel($otherField) . '.';
        }
        return null;
    }

    private function ruleGte(string $field, $value, array $params, bool $isFile): ?string {
        $otherField = $params[0] ?? '';
        $otherValue = $this->data[$otherField] ?? 0;
        if (is_numeric($value) && is_numeric($otherValue) && (float)$value < (float)$otherValue) {
            return ucfirst($this->getLabel($field)) . ' harus lebih besar atau sama dengan ' . $this->getLabel($otherField) . '.';
        }
        return null;
    }

    private function ruleLte(string $field, $value, array $params, bool $isFile): ?string {
        $otherField = $params[0] ?? '';
        $otherValue = $this->data[$otherField] ?? 0;
        if (is_numeric($value) && is_numeric($otherValue) && (float)$value > (float)$otherValue) {
            return ucfirst($this->getLabel($field)) . ' harus lebih kecil atau sama dengan ' . $this->getLabel($otherField) . '.';
        }
        return null;
    }

    private function ruleStartsWith(string $field, $value, array $params, bool $isFile): ?string {
        if ($value !== null) {
            foreach ($params as $prefix) {
                if (strpos((string)$value, $prefix) === 0) return null;
            }
            return ucfirst($this->getLabel($field)) . ' harus diawali dengan: ' . implode(', ', $params) . '.';
        }
        return null;
    }

    private function ruleEndsWith(string $field, $value, array $params, bool $isFile): ?string {
        if ($value !== null) {
            foreach ($params as $suffix) {
                if (substr((string)$value, -strlen($suffix)) === $suffix) return null;
            }
            return ucfirst($this->getLabel($field)) . ' harus diakhiri dengan: ' . implode(', ', $params) . '.';
        }
        return null;
    }
}
