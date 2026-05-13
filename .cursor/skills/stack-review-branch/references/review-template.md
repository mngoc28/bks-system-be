# Review Report Template

Use this template for Step 6 of the review process. Fill in each section; omit sections that have no findings (don't leave empty headings).

---

## Code Review: `<branch-name>` → `<target-branch>`

**Review Date**: <date>
**Commits Reviewed**: <number> commits (<first-hash>..<last-hash>)
**Files Changed**: <number> files (<lines added> additions, <lines deleted> deletions)

### Summary

<1-3 sentences describing what the branch changes and whether the overall change looks sound. This gives the reader a quick orientation before diving into findings.>

### Severity Breakdown

| Severity | Count |
|---|---|
| CRITICAL | 0 |
| HIGH | 0 |
| MEDIUM | 0 |
| LOW | 0 |
| INFO | 0 |

### Findings

For each finding, use this structure:

---

#### [SEVERITY] <short title>

**File**: `<file-path>:<line-range>`
**Category**: <Correctness | Security | Performance | Error Handling | API Design | Data Integrity | Maintainability | Testing | Cross-File Impact>

<Description of the issue. Include the specific code snippet that's problematic and explain why it's an issue. Show the concrete impact — what happens if this isn't fixed?>

**Suggestion**:
```
// Show the recommended fix as a code snippet
```

<If relevant, note any alternatives considered and why this approach is recommended.>

---

### Positive Observations

<Acknowledge things the code does well. Good practices, clean abstractions, thorough tests, well-named variables. This isn't filler — it helps the author know what to keep doing and makes the review feel balanced.>

### Questions for the Author

<Open questions that aren't necessarily problems but need clarification. Things like: "What happens when X?" or "Is there a reason this uses approach Y instead of Z?">

---
