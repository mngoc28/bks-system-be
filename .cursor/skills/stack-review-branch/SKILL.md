---
name: stack-review-branch
description: >
  Comprehensive code review skill that compares the current git branch against a user-specified
  target branch and produces a structured review report. Covers security, correctness, performance,
  error handling, maintainability, testing, and cross-file impact. Use this skill whenever the user
  asks to review code changes, review a branch, check a PR, review a pull request, audit code
  changes, or wants feedback on their diffs between branches. Also trigger on phrases like "code
  review", "review my changes", "check the diff", "what's wrong with my code", "review this branch",
  "review against main/develop/master", or when the user mentions wanting feedback on code they've
  written or changed. This skill is generic and works across all programming languages and project types.
---

## Cursor execution

- This skill replaces Claude Code–specific tooling: use **git** and file tools in Cursor; there is no separate review subagent.
- Use references as review checklists:
  - `.cursor/references/security-checklist.md`
  - `.cursor/references/performance-checklist.md`
  - `.cursor/references/testing-patterns.md`
- Language: write review reports in Vietnamese.
- Pipeline role: this is the quality/release gate after `stack-task` and before stakeholder release reporting.
- Repository memory: update `docs/memory/index.md` and `docs/memory/decisions.md` with key review outcomes.
- Mandatory memory gate: review cannot be finalized until required memory files are updated.
- DB reference rule: when changes touch schema/query/data mapping, read `docs/databases_docs/db_overview_etc_core_schema.md` (and **Nhật ký thay đổi** for recency) before concluding review.

**One-line prompt add-on (optional):**
> "Khi review, áp dụng checklist trong `.cursor/references/security-checklist.md` + `performance-checklist.md` + `testing-patterns.md`."

# Branch Code Review

You are conducting a thorough, professional code review. Your job is to find real issues that matter — bugs that could break production, security holes that could be exploited, design problems that will make the code hard to maintain — not to nitpick style or impose personal preferences.

## Input

The user specifies a **target branch** to compare against (e.g., `main`, `develop`, `master`). The current branch contains the changes to review.

If no target branch is given, ask the user which branch to compare against before proceeding.

When available, also read:
- Related SRS (`docs/SRC/srs_*.md`)
- Related plan (`docs/plans/plan_*.md`)
- Related test-case doc (`docs/test-cases/testcase_*.md`)
- Canonical DB doc: `docs/databases_docs/db_overview_etc_core_schema.md` (other files in folder optional)
to validate requirement coverage before release.

## Review Process

Follow these steps in order. Each step builds on the previous one, so don't skip ahead.

### Step 1: Gather Context

Run these git commands to understand the scope of changes:

```bash
# List all changed files with status (added/modified/removed/renamed)
git diff --name-status <target>...HEAD

# Get the full diff with context (3 lines around each change)
git diff <target>...HEAD

# Get commit history between branches
git log --oneline <target>...HEAD

# Get diffstat for a quick size overview
git diff --stat <target>...HEAD
```

This gives you the big picture: how many files, how large the changes are, and what the commit narrative looks like.

### Step 2: Categorize the Changes

Group the changed files by concern:

| Category | Examples |
|---|---|
| **Core logic** | Business rules, algorithms, data processing |
| **API/Interface** | REST endpoints, GraphQL resolvers, public methods |
| **Data layer** | Database queries, migrations, ORM models |
| **Configuration** | Config files, DI containers, environment variables |
| **Tests** | Unit tests, integration tests, fixtures |
| **Infrastructure** | CI/CD, Docker, deployment scripts |
| **Frontend** | UI components, styles, templates |
| **Documentation** | README, API docs, comments |

Categorization helps you allocate review effort proportionally. Core logic and API changes deserve deeper scrutiny than documentation updates.

### Step 3: First Pass — Understand Intent

Before looking for problems, understand what each change is trying to accomplish. Read the diff for each file and ask:

1. What is this change supposed to do?
2. What problem is it solving?
3. How does it fit into the existing codebase?

If you can't understand the intent from the diff alone, read the surrounding context in the full file. Misunderstanding the intent leads to false positives in your review.

### Step 4: Deep Review — Systematic Analysis

Now review each changed file systematically. For each file, check the categories below. Not every category applies to every file — use your judgment about what's relevant.

Read `references/security-checklist.md` for security-specific review criteria and `references/performance-checklist.md` for performance review criteria.

#### Correctness & Logic
- Does the code actually do what it claims to do?
- Are there off-by-one errors, inverted conditions, or wrong operators?
- Are all code paths reachable and do they return correct results?
- Are null/undefined/empty cases handled where they can occur?
- Are there race conditions in concurrent code?
- Does the change preserve existing invariants that aren't explicitly modified?
- Are there implicit type coercions that could produce unexpected results?

#### Security
- Is all external input validated and sanitized?
- Are there SQL injection, XSS, or command injection vectors?
- Are authentication and authorization checks present where needed?
- Is sensitive data (passwords, tokens, PII) handled safely — not logged, not exposed in URLs, not stored in plaintext?
- Are there information leakage risks in error messages or API responses?
- Are file operations safe from path traversal?

#### Error Handling & Reliability
- Are errors caught and handled at the appropriate level?
- Are error messages informative without exposing internal details?
- Is cleanup performed in error paths (closing connections, releasing resources)?
- Are there swallowed exceptions (empty catch blocks)?
- Do retry mechanisms have sensible limits and backoff?

#### Performance
- Are there N+1 query patterns (querying in a loop)?
- Are large data structures loaded into memory when streaming would work?
- Are there unnecessary synchronous operations that could be async?
- Are database queries using appropriate indexes?
- Are there tight loops doing expensive work that could be precomputed or cached?

#### API Design & Contracts
- Do new endpoints follow the project's existing conventions?
- Are request/response schemas well-defined and backward-compatible?
- Are HTTP methods used correctly (GET for reads, POST/PUT for writes)?
- Are there breaking changes to existing interfaces?
- Are rate limiting and pagination considered where appropriate?

#### Data Integrity
- Are database transactions used where multiple related writes must be atomic?
- Are foreign key constraints respected?
- Is data migration reversible or at least safe for rollback?
- Are there migration steps that could fail partway and leave data in an inconsistent state?

#### Maintainability & Readability
- Can a new team member understand the code without tribal knowledge?
- Is the level of abstraction appropriate — not too high (over-engineered) or too low (spaghetti)?
- Are magic numbers/strings replaced with named constants?
- Is the code DRY without being prematurely abstracted?
- Do names (variables, functions, classes) clearly communicate intent?

#### Testing
- Are there tests for the new behavior?
- Do tests cover the important edge cases, not just the happy path?
- Are tests independent and deterministic (no flaky tests)?
- Do mocks match the real contracts of what they're replacing?

#### Cross-File Impact
- Does removing or renaming a function break callers in other files?
- Do changes to a data model propagate to all layers that use it?
- Does a new dependency in one module create circular dependencies?
- Does a configuration change affect other environments or services?

### Step 5: Classify Findings

For each issue found, assign a severity:

| Level | Meaning | Action Required |
|---|---|---|
| **CRITICAL** | Will cause bugs, data loss, or security breaches in production | Must fix before merge |
| **HIGH** | Likely to cause problems under real usage conditions | Should fix before merge |
| **MEDIUM** | Could cause issues in edge cases or makes maintenance harder | Worth addressing soon |
| **LOW** | Minor improvement, style preference, or nice-to-have | Optional, use judgment |
| **INFO** | Observation, question, or suggestion for consideration | No action required |

Be honest about severity. A missing edge-case check that would only trigger in an impossible configuration is LOW, not HIGH. A SQL injection vector is always CRITICAL.

### Step 6: Generate Report

First, gather the information needed for the output file path:

```bash
# Get the source branch name
git rev-parse --abbrev-ref HEAD

# Get a timestamp
date +%Y%m%d-%H%M%S
```

Then construct the output path. **Sanitize branch names** by replacing `/` with `-` (e.g. `feature/auth` becomes `feature-auth`, `origin/main` becomes `origin-main`) so the filename stays flat:

```
docs/code-review/<timestamp>_<source_branch>_<target_branch>.md
```

For example: `docs/code-review/20260407-143025_feature-login_main.md`

Create the directory and write the report:

```bash
mkdir -p docs/code-review
```

Write the full review report to the constructed path using the template in `references/review-template.md`.

After writing the file, print a brief summary to the conversation that includes:
- The file path where the report was saved
- The severity breakdown (counts per level)
- The top 1-2 most important findings

Also add a release recommendation block:
- `GO`: no blocking findings
- `CONDITIONAL GO`: only medium/low findings with mitigation plan
- `NO-GO`: any critical/high unresolved finding

If recommendation is `GO` or `CONDITIONAL GO`, include handoff notes for `report-writer` to create customer-facing release summary.

Before closing the review, update:
- `docs/memory/index.md` with review artifact path and decision summary
- `docs/memory/decisions.md` if release decision/risk posture changed

## Important Principles

**Be proportional.** A 2-line bugfix in a config file does not need the same depth of review as a 500-line new API endpoint. Scale your effort to the risk and complexity of the change.

**Avoid false positives.** Every finding should be a genuine issue. If you're not sure something is a problem, mark it as INFO and phrase it as a question rather than declaring it a defect. False positives erode trust and waste time.

**Explain why, not just what.** "This is vulnerable to SQL injection" is less useful than "This concatenates user input directly into a SQL query string (`"SELECT * FROM users WHERE id = " + userId`), which allows an attacker to inject arbitrary SQL via the `userId` parameter." The second version teaches; the first just declares.

**Respect existing patterns.** Don't flag something as an issue if it follows the project's established conventions, even if those conventions aren't your preference. Consistency within a project matters more than theoretical perfection.

**Focus on impact.** Prioritize findings that affect users, data, or system stability over internal code aesthetics. A missing test for a critical payment flow matters more than a variable name you'd choose differently.
