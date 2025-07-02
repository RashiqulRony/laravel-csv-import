# Laravel CSV Import

[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

A Laravel package to **import large CSV files** efficiently using **chunked memory processing**, **on-the-fly UTF-8 conversion**, and **no temporary storage overhead**.

---

## 📦 Installation

Install via Composer:

```bash
composer require rashiqulrony/laravel-csv-import
```

If you're using Laravel <5.5 or your Laravel version doesn't support package auto-discovery, add the service provider manually:
```php
// config/app.php
'providers' => [
    Rashiqulrony\CSVImport\Providers\AppServiceProvider::class,
];
```

## ⚙️ Configuration
###### Publish the config file (optional):
```bash
php artisan vendor:publish --provider="Rashiqulrony\CSVImport\Providers\AppServiceProvider" --tag=config
```
This creates a file: config/csvimport.php
```php
return [
    'chunk_size' => 200, // Number of rows to return per batch
];
```
# 🚀 Usage
## ✅ Basic Example
```php
use Rashiqulrony\CSVImport;

public function upload(Request $request)
{
    $request->validate([
        'file' => 'required|file|mimes:csv',
    ]);

    $result = CSVImport::upload($request->file('file'));
    
    if (!empty($result) && is_array($result)) {
         foreach ($result as $key => $value) {
              foreach ($result[$key] as $data) {
                 // Process or save the row (e.g. DB::table(...)->insert($row))
              }
         }
    } else {
         return back()->with('error', 'Data not found or invalid file.');
    }
        
    return back()->with('success', 'CSV imported successfully!');
}
```
## 📥 Method: CSVImport::upload($file)
| Returns                                   | Description                                               |
| ----------------------------------------- | --------------------------------------------------------- |
| `array`                                   | On success: returns a chunk of rows (associative arrays). |
| `['status' => false, 'message' => '...']` | On failure.                                               |

## ✅ Features
* Detects and converts multiple encodings to UTF-8
* Skips invalid rows (missing fields, malformed headers)
* Efficient memory usage with chunking
* No need to manually store or manage temp files

## 🧪 Supported Encodings
* UTF-8
* UTF-16LE / UTF-16BE
* Windows-1252
* ISO-8859-1

## 📂 Directory Structure

```arduion
laravel-csv-import/
├── src/
│   ├── CSVImport.php
│   └── Providers/
│       └── AppServiceProvider.php
├── config/
│   └── csvimport.php
├── composer.json
└── README.md
```

## 📝 License
###### This package is open-sourced software licensed under the MIT license.

## 👤 Author
```
Rashiqul Rony
📧 rashiqulrony@gmail.com
🔗 github.com/rashiqulrony
```
