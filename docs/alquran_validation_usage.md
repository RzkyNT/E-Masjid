# Al-Quran Validation System Usage

## Overview

The Al-Quran validation system provides comprehensive input validation and parameter handling for the Al-Quran feature. It includes validation for surat numbers, ayat numbers, page numbers, juz numbers, and tema IDs with proper error messages and boundary checking.

## Components

### 1. AlQuranValidator Class (`includes/alquran_validation.php`)

Main validation class with static methods for validating different parameter types.

#### Methods:

- `validateSurat($surat)` - Validates surat number (1-114)
- `validateAyat($ayat, $surat)` - Validates ayat number with optional surat context
- `validatePage($page)` - Validates page number (1-604)
- `validateJuz($juz)` - Validates juz number (1-30)
- `validateTema($tema_id)` - Validates tema ID (1-1121)
- `validatePanjang($panjang)` - Validates length parameter (1-30)
- `validateAyatRange($start, $end, $surat)` - Validates ayat range
- `validateParameters($mode, $params)` - Validates complete parameter set
- `sanitizeNumericInput($input)` - Sanitizes numeric input
- `sanitizeStringInput($input)` - Sanitizes string input

### 2. AlQuranParameterHandler Class (`includes/alquran_parameter_handler.php`)

Handles parameter processing and validation for API endpoints.

#### Methods:

- `processGetParameters($get_params)` - Process and validate $_GET parameters
- `generateErrorResponse($errors, $action)` - Generate standardized error responses
- `getParameterHelp($action)` - Get help information for parameters

## Usage Examples

### Basic Validation

```php
require_once 'includes/alquran_validation.php';

// Validate surat number
$result = AlQuranValidator::validateSurat(2);
if ($result['valid']) {
    echo "Surat is valid: " . $result['surat_name'];
} else {
    echo "Error: " . $result['message'];
}

// Validate ayat with surat context
$result = AlQuranValidator::validateAyat(255, 2); // Ayat Kursi
if ($result['valid']) {
    echo "Ayat is valid";
} else {
    echo "Error: " . $result['message'];
}
```

### API Parameter Processing

```php
require_once 'includes/alquran_parameter_handler.php';

// Process $_GET parameters
$param_result = AlQuranParameterHandler::processGetParameters($_GET);

if (!$param_result['valid']) {
    // Return error response
    http_response_code(400);
    echo json_encode(
        AlQuranParameterHandler::generateErrorResponse(
            $param_result['errors'], 
            $param_result['action']
        )
    );
    exit;
}

// Use validated parameters
$action = $param_result['action'];
$params = $param_result['params'];
```

### Complete Parameter Validation

```php
// Validate parameters for surat mode
$validation = AlQuranValidator::validateParameters('surat', [
    'surat' => 2,
    'ayat' => 1,
    'panjang' => 5
]);

if ($validation['valid']) {
    $validated_params = $validation['validated_params'];
    // Use validated parameters safely
} else {
    foreach ($validation['errors'] as $error) {
        echo "Error: " . $error . "\n";
    }
}
```

## Validation Rules

### Surat Numbers
- Range: 1-114
- Must be numeric
- Returns surat name and ayat count

### Ayat Numbers
- Range: 1 to maximum ayat in surat
- Must be numeric
- Context validation with surat number

### Page Numbers
- Range: 1-604 (mushaf pages)
- Must be numeric

### Juz Numbers
- Range: 1-30
- Must be numeric

### Tema IDs
- Range: 1-1121
- Must be numeric

### Ayat Ranges
- Start must be ≤ end
- Both must be valid ayat numbers
- Maximum range size: 30 ayat

## Error Handling

All validation functions return structured results:

```php
[
    'valid' => true/false,
    'value' => sanitized_value, // if valid
    'message' => 'error_message', // if invalid
    'code' => 'ERROR_CODE' // if invalid
]
```

Error codes include:
- `INVALID_TYPE` - Input is not the expected type
- `OUT_OF_RANGE` - Value is outside valid range
- `EXCEEDS_SURAT_LIMIT` - Ayat number exceeds surat's ayat count
- `INVALID_RANGE` - Invalid range specification
- `RANGE_TOO_LARGE` - Range exceeds maximum size

## Integration with Existing Code

The validation system is integrated with:

1. **AlQuranAPI class** - Uses validation for all API methods
2. **API endpoint** (`api/alquran.php`) - Uses parameter handler for request processing
3. **Helper functions** - Provides backward compatibility functions

## Helper Functions

For convenience, helper functions are available:

```php
// Quick validation functions
$result = validateAlQuranSurat(2);
$result = validateAlQuranAyat(255, 2);
$result = validateAlQuranPage(1);
$result = validateAlQuranJuz(1);
$result = validateAlQuranTema(1);

// Parameter processing
$result = validateAlQuranParameters('surat', $params);
$sanitized = sanitizeAlQuranInput($input);
```

## Requirements Compliance

This validation system fulfills the following requirements:

- **1.4**: Surat number validation (1-114)
- **1.5**: Ayat number validation with surat context
- **2.2**: Page number validation (1-604)
- **3.3**: Juz number validation (1-30)
- **4.3**: Tema ID validation (1-1121)
- **7.1**: Comprehensive input validation with specific error messages
- **7.3**: Parameter sanitization and boundary checking