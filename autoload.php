<?php
/**
 * Autoloader Manual untuk PhpSpreadsheet
 * 
 * Letakkan file ini di root folder project Anda.
 * Pastikan folder `PhpSpreadsheet` ada di root folder (hasil ekstrak dari release GitHub).
 */

spl_autoload_register(function ($class) {
    // Hanya proses class yang dimulai dengan namespace PhpOffice\PhpSpreadsheet
    $prefix = 'PhpOffice\\PhpSpreadsheet\\';
    
    // Base directory untuk library
    $base_dir = __DIR__ . '/PhpSpreadsheet/';
    
    // Cek apakah class termasuk namespace yang kita tangani
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    // Ambil relative class name
    $relative_class = substr($class, $len);
    
    // Ganti namespace separator menjadi DIRECTORY_SEPARATOR
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    // Jika file ada, require
    if (file_exists($file)) {
        require $file;
    }
});
