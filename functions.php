<?php
// functions.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('DATA_DIR', __DIR__ . '/data');
define('BOOKS_FILE', DATA_DIR . '/books.txt');
define('MEMBERS_FILE', DATA_DIR . '/members.txt');
define('LOANS_FILE', DATA_DIR . '/loans.txt');
define('RETURNS_FILE', DATA_DIR . '/returns.txt');

$BORROW_LIMIT = 3;    // batas pinjam per anggota
$BORROW_DAYS  = 7;    // lama pinjam default (hari)
$FINE_PER_DAY = 1000; // denda per hari (Rupiah)

// pastikan folder data ada
if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0755, true);

// baca file ke array of rows (each row => array of fields)
function readData($file) {
    $rows = [];
    if (!file_exists($file)) return $rows;
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $rows[] = explode('|', rtrim($line, "\n"));
    }
    return $rows;
}

// append satu baris (array fields) ke file
function appendRow($file, $row) {
    $fp = fopen($file, 'a');
    $safe = array_map(function($f){ return str_replace("|", "/", trim($f)); }, $row);
    fwrite($fp, implode('|', $safe) . PHP_EOL);
    fclose($fp);
}

// overwrite seluruh file dengan rows (array of arrays)
function writeTable($file, $rows) {
    $fp = fopen($file, 'w');
    foreach ($rows as $r) {
        $safe = array_map(function($f){ return str_replace("|", "/", trim($f)); }, $r);
        fwrite($fp, implode('|', $safe) . PHP_EOL);
    }
    fclose($fp);
}

// generate next ID based on prefix and file content (e.g. B-1, M-2)
function nextID($file, $prefix) {
    $rows = readData($file);
    $max = 0;
    foreach ($rows as $r) {
        if (isset($r[0])) {
            $parts = explode('-', $r[0]);
            $num = intval(end($parts));
            if ($num > $max) $max = $num;
        }
    }
    return $prefix . '-' . ($max + 1);
}

// escape html
function h($s) {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

// simple currency format
function rp($n) {
    return number_format((int)$n,0,',','.');
}

// validate email
function is_valid_email($e) {
    return filter_var($e, FILTER_VALIDATE_EMAIL) !== false;
}

// get loan enriched info (member/book)
function enrichLoan($loan) {
    $members = readData(MEMBERS_FILE);
    $books = readData(BOOKS_FILE);
    $member = null; $book = null;
    foreach ($members as $m) if ($m[0] === $loan[1]) { $member = $m; break; }
    foreach ($books   as $b) if ($b[0] === $loan[2]) { $book = $b; break; }
    return [$loan, $member, $book];
}
?>
