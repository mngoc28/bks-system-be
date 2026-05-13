# Testing Patterns Reference (Laravel Modules + Blade)

Quick reference for testing in this repository. Focus on requirement-centric verification for Laravel HTTP flows, services, and Blade UI behavior.

## Scope

- Backend/framework: Laravel
- Architecture: Laravel Modules
- UI rendering: Blade templates/components
- Default test runner: `php artisan test` (PHPUnit)

## Table of Contents

- [Test Pyramid for This Repo](#test-pyramid-for-this-repo)
- [Test Structure (Arrange-Act-Assert)](#test-structure-arrange-act-assert)
- [Naming Conventions](#naming-conventions)
- [Feature/API Test Patterns](#featureapi-test-patterns)
- [Module Service Unit Test Patterns](#module-service-unit-test-patterns)
- [Database and Migration Checks](#database-and-migration-checks)
- [Useful Commands](#useful-commands)
- [Anti-Patterns](#anti-patterns)

## Test Pyramid for This Repo

- Prefer **Feature tests** (`tests/Feature`) for routes/controllers/middleware/validation/authorization.
- Use **Unit tests** (`tests/Unit`) for pure service logic and data transforms.
- Keep tests mapped to requirement IDs/acceptance criteria from SRS/plan.

## Test Structure (Arrange-Act-Assert)

```php
public function test_member_creation_requires_required_fields(): void
{
    // Arrange
    $payload = [];

    // Act
    $response = $this->postJson('/api/members', $payload);

    // Assert
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['member_code', 'member_name']);
}
```

## Naming Conventions

- Method name should describe behavior and condition:
  - `test_guest_is_redirected_to_login_on_protected_page`
  - `test_store_returns_422_when_member_code_is_missing`
  - `test_admin_can_update_fuel_member`

## Feature/API Test Patterns

### Authentication and authorization

```php
public function test_guest_is_redirected_to_login(): void
{
    $this->get('/dashboard')->assertRedirect('/login');
}
```

```php
public function test_non_admin_cannot_access_admin_route(): void
{
    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($user)
        ->get('/admin/users')
        ->assertForbidden();
}
```

### Validation

```php
public function test_store_returns_422_for_invalid_payload(): void
{
    $this->postJson('/api/fuel-members', ['member_code' => ''])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['member_code']);
}
```

### Success response contract

```php
public function test_store_returns_201_and_resource_shape(): void
{
    $payload = ['member_code' => 'M001', 'member_name' => 'Test Member'];

    $this->postJson('/api/fuel-members', $payload)
        ->assertCreated()
        ->assertJsonPath('data.member_code', 'M001');
}
```

## Module Service Unit Test Patterns

Use Unit tests for business logic in module services where HTTP layer is not needed.

```php
public function test_due_date_is_calculated_from_closing_rule(): void
{
    $service = new BillingRuleService();

    $dueDate = $service->calculateDueDate('2026-05-31', 10);

    $this->assertSame('2026-06-10', $dueDate->format('Y-m-d'));
}
```

## Database and Migration Checks

- Use factories/seeders for deterministic setup when needed.
- Validate DB side effects with `assertDatabaseHas` / `assertDatabaseMissing`.
- If schema/mapping changes, update `docs/databases_docs/db_overview_etc_core_schema.md` and append **Nhật ký thay đổi**.

```php
$this->assertDatabaseHas('m_kumiaiin', [
    'member_code' => 'M001',
]);
```

## Useful Commands

```bash
# Run all tests
php artisan test

# Run a single file
php artisan test tests/Feature/Auth/LoginTest.php

# Run by filter
php artisan test --filter=member_creation

# Alternative runner
vendor/bin/phpunit
```

## Anti-Patterns

| Anti-Pattern | Problem | Better Approach |
|---|---|---|
| Testing implementation details | Breaks on refactor | Test request/response/business outcome |
| No auth/permission cases | Misses critical regressions | Add guest + unauthorized + authorized scenarios |
| Over-mocking domain logic | False confidence | Mock external boundaries only |
| Shared mutable fixture state | Flaky tests | Isolated setup per test |
| Missing DB assertions | Side effects unverified | Assert database state explicitly |
| Skipping failing tests | Hides defects | Fix or remove invalid tests with reason |
