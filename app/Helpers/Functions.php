<?php

function lockTable($table) {
    \DB::raw('LOCK TABLES ' . $table . ' WRITE');
}

function unlockTable() {
    \DB::raw('UNLOCK TABLES');
}

function trimSpaces($name) {
    return trim(preg_replace('/\s\s+/', ' ', str_replace("\n", ' ', $name)));
}

function slugify($str, $delimiter = '-') {
    return strtolower(trim(preg_replace('/[\s-]+/', $delimiter, preg_replace('/[^A-Za-z0-9-]+/', $delimiter, preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $str))))), $delimiter));
}

function guidv4() {
    if (function_exists('com_create_guid') === true) {
        return trim(com_create_guid(), '{}');
    }

    $data = openssl_random_pseudo_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function generateRandomToken() {
    $bytes = openssl_random_pseudo_bytes(16);
    return bin2hex($bytes);
}

function getQueryLimit($limit = null) {
    if (!(int)$limit) {
        return config('grabmaid.query.limit.min');
    }

    if ((int)$limit > config('grabmaid.query.limit.max')) {
        return config('grabmaid.query.limit.max');
    }

    return (int)$limit;
}

function getQueryOffset($offset = null) {
    if (!(int)$offset) {
        return 0;
    }

    return (int)$offset;
}

function getGender($g) {
    $g = strtolower($g);

    if ($g === 'male') {
        return 1;
    }

    if ($g === 'female') {
        return 2;
    }

    return null;
}

function generateOrderCode(int $number) {
    $currentBookingNumber = dechex($number);
    $hexLength = strlen((string)$currentBookingNumber);
    $zeros = '';

    for($i = 8; $i > $hexLength; $i--) {
        $zeros .= '0';
    }

    return $zeros . $currentBookingNumber;
}

function getPrefix($refNo) {
    $prefix = '';

    for ($i = 0; $i < strlen($refNo); $i++) {
        $char = $refNo[$i];

        if (is_numeric($char)) {
            break;
        }

        $prefix .= $char;
    }

    return $prefix;
}

function getRefNo($lastRefNo, $prefix) {
    if ($lastRefNo === 0) {
        $number = $lastRefNo + 1;
    } else {
        $thisPrefix = getPrefix($lastRefNo);
        if ($thisPrefix !== $prefix) {
            $thisPrefix = $prefix;
        }
        $number = hexdec(substr($lastRefNo, strlen($thisPrefix))) + 1;
    }
    return $prefix . generateOrderCode($number);
}

function getBookingNumber($lastBookingNumber, $prefix = 'GMB') {
    return getRefNo($lastBookingNumber, $prefix);
}

function getTopupNumber($lastTopupNumber, $prefix = 'GMT') {
    return getRefNo($lastTopupNumber, $prefix);
}

function generateVerificationCode() {
    $length = 4;
    $code = '';

    for($i = 0; $i < $length; $i++) {
        $code .= rand(0, 9);
    }

    return $code;
}

function iPay88_signature($source) {
	return hash('sha256', $source);
}
