# Security Review Checklist

Reference this file during Step 4 of the review process when examining security-relevant changes. Not every item applies to every codebase — focus on what's relevant to the language, framework, and change type.

## Input Handling

- [ ] All external input is validated (HTTP params, headers, body, file uploads, environment variables, database reads from untrusted sources)
- [ ] Input validation uses allowlists (expected format/length/range) rather than denylists
- [ ] File uploads check type by content (magic bytes), not just extension
- [ ] Deserialized data from untrusted sources is validated after deserialization
- [ ] JSON/XML parsing has depth/size limits to prevent denial-of-service

## Injection Prevention

- [ ] SQL queries use parameterized statements or ORM abstractions — no string concatenation of user input
- [ ] HTML output encodes user-supplied data to prevent XSS (context-aware encoding: body, attribute, JavaScript, URL)
- [ ] Shell commands use safe APIs (execvp with args array) — no shell interpolation of user input
- [ ] LDAP queries escape special characters
- [ ] Template engines don't evaluate user-supplied content as code
- [ ] XPath/XQuery queries use parameterized forms

## Authentication & Authorization

- [ ] Passwords are hashed with modern algorithms (bcrypt, scrypt, Argon2) — never stored in plaintext or reversible encryption
- [ ] Authentication checks are not bypassed in any code path
- [ ] Authorization checks enforce least privilege — users can only access their own resources
- [ ] Session tokens are: (a) cryptographically random, (b) transmitted over HTTPS only, (c) invalidated on logout, (d) have reasonable expiration
- [ ] API keys and secrets are loaded from environment variables or secret managers — never hardcoded or committed
- [ ] Multi-tenant isolation is enforced at the data access layer, not just the UI layer

## Data Protection

- [ ] Sensitive data (PII, credentials, tokens) is not logged
- [ ] Error messages don't leak internal details (stack traces, SQL queries, file paths) to external callers
- [ ] HTTP responses don't expose sensitive headers (server version, internal IPs)
- [ ] Data at rest is encrypted where required (database fields, file storage)
- [ ] Data in transit uses TLS — no plaintext HTTP for sensitive operations
- [ ] CORS headers restrict origins appropriately (not `*` for authenticated endpoints)

## Cryptography

- [ ] Cryptographic operations use well-vetted libraries — no custom crypto
- [ ] Random number generation uses cryptographically secure APIs (not `Math.random()`, `rand()`, etc.)
- [ ] Key sizes meet current recommendations (AES-256, RSA-2048+, ECDSA P-256+)
- [ ] TLS configuration avoids deprecated protocols (SSLv3, TLS 1.0/1.1) and weak cipher suites
- [ ] IVs/nonces are generated correctly and never reused

## Common Vulnerability Patterns

- [ ] **Mass assignment**: ORM/model binding doesn't allow setting fields the user shouldn't control (e.g., `isAdmin`)
- [ ] **Insecure direct object references**: Access to resources by ID is authorized — user can't guess another user's resource ID
- [ ] **Open redirects**: Redirect URLs are validated against an allowlist, not just checked for format
- [ ] **SSRF**: URLs provided by users are validated; internal network addresses are blocked
- [ ] **XML External Entities**: XML parsers disable external entity processing
- [ ] **Race conditions on state changes**: Double-submit or TOCTOU issues on financial/data operations use proper locking or idempotency keys
- [ ] **Path traversal**: File paths constructed from user input are normalized and validated against a base directory

## Dependency Security

- [ ] No known-vulnerable dependencies (check for pinned versions, lock files, vulnerability advisories)
- [ ] No use of `eval()`, `Function()`, or equivalent dynamic code execution on user input
- [ ] Third-party packages are from trusted registries and properly pinned
