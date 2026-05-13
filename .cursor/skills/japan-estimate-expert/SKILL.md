---
name: japan-estimate-expert
description: >-
  Produces detailed software effort estimates aligned with Japanese-style practices (IPA/SEC benchmarking,
  Function Point based sizing, Basis of Estimate discipline) for both new development and maintenance projects.
  Use when the user asks for estimate reports, man-month breakdowns, cost/schedule projections, WBS effort,
  or wants analysis from source code, database, documents, and test scope.
---

# Japan Estimate Expert

## Purpose

Create practical, auditable estimate reports with two modes:
- **New Build:** system built from scratch
- **Maintenance:** existing system enhancement, feature update, and bug fix

Use objective sizing, explicit assumptions, and confidence ranges instead of gut-feel only.

## Estimation references to follow

Use these as baseline methods and vocabulary:
- **IPA/SEC style benchmarking:** correlate size/productivity/effort with historical data
- **IFPUG/NESMA Function Point concepts:** EI/EO/EQ/ILF/EIF for functional sizing
- **Enhancement sizing mindset:** separate Added/Changed/Deleted scope in maintenance
- **Basis of Estimate (BoE):** document assumptions, exclusions, risk, and confidence

If internet access is available, quickly verify updated benchmark ranges before finalizing.

## Trigger scenarios

Apply this skill when user asks for:
- Estimate report, man-month, delivery timeline, cost projection
- Analysis from source code/database/documents/test cases
- Feasibility study for new product build
- Maintenance estimate for feature update or bug-fix backlog
- Breakdown by role (BA/Dev/QA/DevOps) or phase (design/dev/test/release)

## Inputs checklist

Collect as many as possible before estimating:
- Scope docs: requirement docs, specs, tickets, acceptance criteria
- Codebase info: repository modules, language/framework, architecture style
- Database info: schema/table count, migration complexity, data volume
- Integration info: external APIs, batch jobs, auth, infra dependencies
- Quality scope: test strategy, testcase volume, automation target
- Delivery constraints: deadline, team size, skill level, environments
- Historical metrics: prior velocity, defect rate, rework rate, production incidents

If key inputs are missing, produce estimate with explicit assumption flags.

## Output format (required)

Always produce this structure:

```markdown
# Estimate Report: [Project/Feature Name]

## 1) Estimate type
- New Build | Maintenance

## 2) Scope summary
- Included:
- Excluded:
- Dependencies:

## 3) Sizing approach
- Primary sizing method:
- Supporting method:
- Size result:

## 4) Effort breakdown (man-day / man-month)
- By phase:
- By role:
- By module:

## 5) Schedule projection
- Earliest (optimistic):
- Most likely:
- Conservative:
- Critical path:

## 6) Cost projection
- Labor cost:
- Tool/infra cost:
- Contingency:
- Total:

## 7) Quality & test estimation
- Test design:
- Test execution:
- Automation:
- Defect fix reserve:

## 8) Risk and uncertainty
- Top risks:
- Assumptions:
- Confidence level:

## 9) Recommendation
- Suggested plan:
- Scope cut options:
- Next validation actions:
```

## Workflow

### Step 1: Classify project type

Choose one:
- **New Build**
- **Maintenance**

If mixed work, split estimate into two workstreams and total them later.

### Step 2: Build scope map

Create a module-level scope map:
- UI/API/Batch/Auth/Admin/Reporting
- Data model changes
- Integration endpoints
- Non-functional requirements (performance, security, audit, logging)

### Step 3: Size the work

Use one primary and one supporting method:
- **Primary (preferred):** Function-oriented sizing (FP style)
- **Supporting:** SLOC proxy, story/use-case complexity, historical velocity

For maintenance, explicitly classify:
- **Added**
- **Changed**
- **Deleted**

### Step 4: Convert size to effort

Use historical productivity first. If unavailable, use assumption ranges:
- `Effort = Size x ProductivityFactor x ComplexityMultiplier x RiskBuffer`

Typical multipliers to consider:
- Legacy complexity
- Domain complexity
- Team ramp-up
- Third-party uncertainty
- Compliance/security requirements

### Step 5: Build schedule and staffing

- Derive parallelizable streams and critical path
- Validate staff loading is realistic
- Provide 3-point scenario: optimistic / likely / conservative

### Step 6: Add QA and release effort

Do not treat testing as residual work. Estimate separately:
- Test analysis and testcase design
- Manual execution cycles
- Automation development and maintenance
- Defect triage and fix verification
- UAT and release support

### Step 7: Publish BoE-quality report

Every final estimate must include:
- Assumptions
- Exclusions
- Confidence level
- Change-impact rules (how estimate updates when scope changes)

## Mode A: New Build estimation guide

### Minimum breakdown

- Discovery/requirement clarification
- Architecture and database design
- Development by module
- Integration and data migration
- Testing (SIT/UAT/regression)
- Release, hypercare, documentation

### New build checkpoints

- Requirements stability score
- New technology adoption risk
- Unknown external integration risk
- Environment readiness (CI/CD, staging, monitoring)

### New build formula hint

Use phased calculation:
- `Build Effort = Core Development + Integration + QA + Release + Management + Contingency`

Keep contingency explicit, not hidden in each line item.

## Mode B: Maintenance estimation guide

### Required maintenance analysis

- Current build source structure and module ownership
- Language/framework/runtime versions and upgrade pressure
- Code health: coupling, technical debt, test coverage, flaky tests
- Database migration risk and backward compatibility
- Production issue trends (bug categories, recurrence)

### Maintenance work categories

- Feature update/enhancement
- Bug fix (simple/medium/complex/severity-based)
- Refactor/tech debt reduction
- Framework/library/security patching

### Maintenance effort model

Use category-based mapping with adjustment factors:

```text
Maintenance Effort
= (Enhancement Effort + Bug Fix Effort + Platform Update Effort)
  x Environment/Regression Factor
  x Risk Buffer
```

### Maintenance-specific reserves

- Regression retest reserve
- Hotfix interruption reserve
- Legacy investigation reserve
- Rollback and post-release monitoring reserve

## Guardrails

- Do not provide a single deterministic number only; always provide range
- Do not hide assumptions; list them explicitly
- Do not skip QA/doc/release effort
- Do not mix new build and maintenance without separate lines
- If requirements are unclear, pause and ask clarification before final commitment

## Quick clarification questions

Ask these when input quality is low:
- Which estimate type: New Build or Maintenance?
- Deadline fixed or flexible?
- Existing team size and skill level?
- Any mandatory tech stack or architecture constraints?
- Required test depth (smoke/full regression/performance/security)?
- Need estimate by phase, by role, or both?

## Deliverable quality checklist

- [ ] Type classified correctly (New Build/Maintenance)
- [ ] Scope in/out explicitly listed
- [ ] Sizing method and rationale explained
- [ ] Effort includes dev + QA + docs + release
- [ ] Timeline has 3-point scenario
- [ ] Risk and assumptions documented
- [ ] Confidence level stated
