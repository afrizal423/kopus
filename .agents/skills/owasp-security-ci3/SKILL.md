---
name: owasp-security-ci3
description: >-
  Comprehensive OWASP Top 10 security guidelines and implementation workflows for CodeIgniter 3 APIs connecting to Oracle databases in an enterprise intranet environment (where HTTPS is not required). Use when developing, reviewing, refactoring, or auditing API endpoints, controllers, models, and database queries.
---

# OWASP Top 10 Security Checklist & Guidelines (CI3 + Oracle Intranet)

This skill provides step-by-step security workflows, code patterns, and verification checklists tailored for this CodeIgniter 3 repository interacting with Oracle 11g database instances in a corporate intranet environment.

---

## 1. Intranet Environment Context (HTTPS Exemption)

> [!IMPORTANT]
> **HTTPS is NOT required or enforced** in this application. It operates inside a closed corporate intranet / LAN where plain HTTP traffic is standard.
> - **Do NOT** enforce SSL/TLS redirects.
> - **Do NOT** add `Strict-Transport-Security` (HSTS) headers, as they can cause client browsers to reject plain HTTP connections.

---

## 2. Core OWASP Top 10 Workflows & Patterns

### A03:2021 – Injection Prevention (SQLi & XSS)

#### Rule: Zero String Interpolation in SQL
Never embed user-controlled input (`$inputan`, `$_GET`, `$_POST`) directly into raw SQL strings.

**Vulnerable Pattern (DO NOT USE):**
```php
// VULNERABLE TO SQL INJECTION
$sql = "SELECT * FROM CMMS_CM_SPK WHERE KODE_FA = '$inputan'";
$data = $db->query($sql)->result();
```

**Secure Pattern (Query Binding):**
```php
$sql = "
    SELECT * 
    FROM CMMS_CM_SPK 
    WHERE KODE_FA = ? 
       OR KODE_FA_LAMA = ? 
       OR NICKNAME = ?
";
$data = $db->query($sql, array($inputan, $inputan, $inputan))->result();
```

#### Input Sanitization & Validation Checklist
1. **XSS Cleaning**: Read input via `$this->input->get('param', TRUE)` or `$this->security->xss_clean($input)`.
2. **Null-byte Elimination**: `$input = trim(str_replace(chr(0), '', $input));`
3. **Length Constraints**: Reject inputs longer than expected (e.g. `strlen($input) > 60`) to prevent resource exhaustion and buffer attacks.
4. **Character Whitelist Regex**: Validate using regex appropriate for the domain entity:
   ```php
   // Example for machine codes, nicknames, alphanumeric IDs:
   if (!preg_match('/^[a-zA-Z0-9.\-_ \/]+$/', $input)) {
       $this->output->set_status_header(400);
       $this->output->set_output(json_encode(array(
           'status'  => false,
           'message' => 'Format parameter inputan tidak valid.'
       )));
       return;
   }
   ```

---

### A01:2021 – Broken Access Control & HTTP Method Enforcement

Every endpoint must validate that the HTTP request method matches its intended action:

```php
$method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';
if ($method !== 'GET') {
    $this->output->set_status_header(405);
    $this->output->set_header('Allow: GET');
    $this->output->set_content_type('application/json', 'utf-8');
    $this->output->set_output(json_encode(array(
        'status'  => false,
        'message' => 'Method Not Allowed. Hanya request GET yang diizinkan.'
    )));
    return;
}
```

---

### A05:2021 – Security Misconfiguration & Response Hardening

Always set hardened response headers at the start of output generation:

```php
$this->output->set_content_type('application/json', 'utf-8');
$this->output->set_header('X-Content-Type-Options: nosniff');
$this->output->set_header('X-Frame-Options: DENY');
$this->output->set_header('X-XSS-Protection: 1; mode=block');
$this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
$this->output->set_header('Pragma: no-cache');
```

---

### A04:2021 & A09:2021 – Resource Management & Error Logging

- **Connection Cleanup**: Always close Oracle database connections (`$db->close()`) to avoid exhausting Oracle connection pools (`ORA-12519` / TNS errors).
- **PHP 5.3+ Compatibility**:
  - Avoid inline array dereferencing on function calls:
    ```php
    // Bad in PHP 5.3:
    // $code = $db->error()['code'];

    // Good in PHP 5.3+:
    $err = $db->error();
    if (!empty($err['code'])) { ... }
    ```
  - Use `catch (Exception $e)` instead of `Throwable`.
- **Information Exposure**: Never return database error details or SQL statements in the JSON response:
  ```php
  catch (Exception $e) {
      if ($db) {
          $db->close();
      }
      log_message('error', 'Controller_Name Error: ' . $e->getMessage());
      $this->output->set_status_header(500);
      $this->output->set_output(json_encode(array(
          'status'  => false,
          'message' => 'Terjadi kesalahan internal pada server.'
      )));
  }
  ```

---

## 3. Security Verification Workflow

When developing or auditing an endpoint:
1. **Method Check**: Test with invalid HTTP method (`curl.exe -X POST ...`) -> Expect `405`.
2. **Missing Input**: Test without required parameters -> Expect `400`.
3. **SQL Injection Test**: Test with payload `' OR '1'='1` -> Expect `400` validation failure or safe parameter escaping.
4. **Header Check**: Verify `X-Content-Type-Options: nosniff` and `X-Frame-Options: DENY` are returned.
5. **No HSTS Header**: Verify `Strict-Transport-Security` is NOT present to prevent intranet HTTP breaking.
