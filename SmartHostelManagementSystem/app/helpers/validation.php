<?php
/**
 * Validation helper functions
 */

function validateRequired($value) {
    return $value !== null && $value !== '' && (!is_string($value) || trim($value) !== '');
}

function validateEmail($value) {
    return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
}

function validateMinLength($value, $min) {
    return strlen($value) >= $min;
}

function validateMaxLength($value, $max) {
    return strlen($value) <= $max;
}

function validateNumeric($value) {
    if ($value === null || $value === '') {
        return true;
    }

    $normalized = preg_replace('/[^0-9.\-]/', '', (string) $value);
    return $normalized !== '' && is_numeric($normalized);
}

function validateInArray($value, $array) {
    return in_array($value, $array);
}

function validateConfirmed($value, $confirmation) {
    return $value === $confirmation;
}

function validateUnique($value, $table, $column) {
    $stmt = db()->prepare("SELECT COUNT(*) FROM $table WHERE $column = ?");
    $stmt->execute([$value]);
    return $stmt->fetchColumn() == 0;
}

function validateExists($value, $table, $column) {
    $stmt = db()->prepare("SELECT COUNT(*) FROM $table WHERE $column = ?");
    $stmt->execute([$value]);
    return $stmt->fetchColumn() > 0;
}

function validate($data, $rules) {
    $errors = [];
    
    foreach ($rules as $field => $ruleList) {
        $rules = explode('|', $ruleList);
        $value = $data[$field] ?? null;
        
        foreach ($rules as $rule) {
            $params = explode(':', $rule);
            $ruleName = $params[0];
            $ruleParam = $params[1] ?? null;
            
            switch ($ruleName) {
                case 'required':
                    if (!validateRequired($value)) {
                        $errors[$field][] = ucfirst($field) . ' is required';
                    }
                    break;
                case 'email':
                    if (!empty($value) && !validateEmail($value)) {
                        $errors[$field][] = 'Please enter a valid email address';
                    }
                    break;
                case 'min':
                    if (!empty($value) && !validateMinLength($value, $ruleParam)) {
                        $errors[$field][] = ucfirst($field) . " must be at least {$ruleParam} characters";
                    }
                    break;
                case 'max':
                    if (!empty($value) && !validateMaxLength($value, $ruleParam)) {
                        $errors[$field][] = ucfirst($field) . " must not exceed {$ruleParam} characters";
                    }
                    break;
                case 'numeric':
                    if (!empty($value) && !validateNumeric($value)) {
                        $errors[$field][] = ucfirst($field) . ' must be a number';
                    }
                    break;
                case 'confirmed':
                    $confirmation = $data[$field . '_confirmation'] ?? null;
                    if (!validateConfirmed($value, $confirmation)) {
                        $errors[$field][] = ucfirst($field) . ' confirmation does not match';
                    }
                    break;
                case 'unique':
                    if (!empty($value) && !validateUnique($value, $ruleParam, $field)) {
                        $errors[$field][] = ucfirst($field) . ' already exists';
                    }
                    break;
                case 'exists':
                    if (!empty($value) && !validateExists($value, $ruleParam, $field)) {
                        $errors[$field][] = ucfirst($field) . ' does not exist';
                    }
                    break;
                case 'in':
                    if (!empty($value) && !validateInArray($value, explode(',', $ruleParam))) {
                        $errors[$field][] = ucfirst($field) . ' has an invalid value';
                    }
                    break;
            }
        }
    }
    
    return $errors;
}
?>