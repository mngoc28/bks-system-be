---
name: stack-brainstorm
description: >-
  Runs discovery for vague product ideas and writes clarified lead documents under docs/leads. Use when the
  user has rough concepts, incomplete requirements, or wants brainstorming before a PRD. Triggers on phrases
  like "thinking about", "might need", or unclear feature scope.
---

# CM Brainstorm

## Cursor adaptation

- **Single agent:** Execute steps in order in this chat. Do **not** use Claude Code Agent tool, TaskOutput, or background subagents.
- **Personas:** For business vs technical angles, read `.cursor/skills/stack-personas/business-analyst.md` and `.cursor/skills/stack-personas/technical-lead-architect.md` and follow those behaviors when drafting questions.

## Overview

Transforms vague ideas, rough concepts, and unstructured leads into clear, actionable requirements through collaborative analysis and targeted questioning. Applies business-analyst and technical-lead-architect **perspectives** (see persona files) to examine the lead from multiple angles, identify gaps, and formulate clarifying questions.

**Core principle:** Great requirements emerge from asking the right questions, not making assumptions. Collaborative exploration uncovers hidden constraints and opportunities.

## When to Use

Use when:
- User has a vague idea or rough concept they want to explore
- User provides incomplete or ambiguous information about a feature
- User says things like "I'm thinking about..." or "We might need..."
- User wants to clarify assumptions before committing to a solution
- Starting the discovery phase for a new initiative

Do NOT use when:
- User asks simple questions (just answer directly)
- User provides well-defined requirements (use `stack-analyze` skill instead)
- User requests code changes (this is discovery only)
- Requirements are already documented in a PRD

## Workflow

```mermaid
flowchart TB
    start([User provides vague idea/lead])
    capture[Capture initial information]
    draft[Draft BA + TLA question sets using persona files]
    questions[Compile clarifying questions]
    ask_user[Present questions to user]
    collect{Collect user responses}
    iterate{More clarification needed?}
    synthesize[Synthesize into clear requirements]
    write["Write lead_XXX.md"]
    done([Report completion])

    start --> capture
    capture --> draft
    draft --> questions
    questions --> ask_user
    ask_user --> collect
    collect --> iterate
    iterate -->|Yes| draft
    iterate -->|No| synthesize
    synthesize --> write
    write --> done
```

## Implementation

### Step 1: Capture Initial Information

Gather all the information the user has provided:

- What is the core idea or problem?
- Who are the stakeholders or users?
- What is the context or business domain?
- Any constraints, deadlines, or preferences mentioned?
- What is the desired outcome?

If the initial input is very sparse, ask the user for basic context before drafting questions.

### Step 2: Draft business and technical question sets (single pass or two mental passes)

1. Following `.cursor/skills/stack-personas/business-analyst.md`, list **5–10** concrete business questions for the lead (targets, value, metrics, constraints, workflows, competitors).
2. Following `.cursor/skills/stack-personas/technical-lead-architect.md`, list **5–10** concrete technical questions (systems, integrations, scale, security, data).

You may produce both lists in one reply; they replace parallel Claude Code subagents.

### Step 3: Compile clarifying questions

Combine the questions from both agents into a structured format:

| Category | Questions |
|----------|-----------|
| Business | [From business-analyst] |
| Technical | [From technical-lead-architect] |

Remove duplicate or redundant questions. Prioritize questions that address the biggest areas of uncertainty.

### Step 4: Present questions to the user

Present the highest-priority questions in the chat. If the Cursor **AskQuestion** UI is available, you may use it for multiple-choice style prompts; otherwise ask conversationally. For open-ended items, ask plain text and wait for answers.

### Step 5: Collect and Analyze Responses

Review the user's responses:

- Do they answer the questions adequately?
- Do they reveal new areas of uncertainty?
- Are there contradictions or ambiguities?

If responses are still vague or incomplete, iterate:
- Regenerate business and technical question sets using the persona files with the **new** context
- Generate follow-up questions
- Repeat until requirements are clear

### Step 6: Synthesize into Clear Requirements

Once you have sufficient information, synthesize the findings:

| Aspect | Clarified Requirement |
|--------|----------------------|
| Problem Statement | [What problem are we solving] |
| Target Users | [Who will use this] |
| Business Value | [Why this matters] |
| Success Metrics | [How we measure success] |
| Technical Context | [Systems, platforms, constraints] |
| Key Features | [What needs to be built] |
| Out of Scope | [What we're not doing] |
| Assumptions | [What we're assuming] |
| Open Questions | [Still unresolved items] |

### Step 7: Write Lead Document

Create the lead file at `docs/leads/lead_[YYMMDD]_[topic].md`:

```markdown
# Lead: [Feature/Initiative Name]

## Document Information
- **Lead ID:** L[YYMMDD]-[topic]
- **Created:** [Date]
- **Status:** Clarified / Needs Further Discussion
- **Next Step:** [analyze / design / More questions needed]

## Original Input
[The user's initial vague idea or concept]

---

## Clarified Requirements

### Problem Statement
[Clear description of the problem to solve]

### Target Users
- Primary: [Who are the main users]
- Secondary: [Other stakeholders]

### Business Context
- **Business Value:** [Why this matters to the business]
- **Success Metrics:** [How we measure success]
- **Constraints:** [Budget, timeline, compliance, etc.]

### Technical Context
- **Systems Involved:** [What platforms/systems]
- **Integrations:** [What needs to connect to what]
- **Technical Constraints:** [Legacy systems, tech restrictions]

### Key Features
1. [Feature 1]
2. [Feature 2]
3. [Feature 3]

### Out of Scope
- [What we're explicitly not doing]
- [Future considerations]

---

## Clarification Q&A

### Business Questions
| Question | Answer |
|----------|--------|
| [Question from BA] | [User's answer] |
| [Question from BA] | [User's answer] |

### Technical Questions
| Question | Answer |
|----------|--------|
| [Question from TLA] | [User's answer] |
| [Question from TLA] | [User's answer] |

---

## Assumptions
- [Assumption 1]
- [Assumption 2]
- [Assumption 3]

## Open Questions
- [ ] [Still unresolved question 1]
- [ ] [Still unresolved question 2]

## Risks Identified
| Risk | Impact | Mitigation |
|------|--------|------------|
| [Risk] | [H/M/L] | [How to address] |

---

## Next Steps
- [ ] [Recommended next action, e.g., "Run stack-analyze to create PRD"]
- [ ] [Any follow-up discussions needed]

## Appendix

### Discovery Session Log
- **Round 1:** [Summary of first round of questions]
- **Round 2:** [Summary of follow-up questions if any]
```

## Example

**User input:**
> "I want to add SSO to our app"

**Action:**
1. Capture initial info: SSO feature, but unclear about provider, users, use case
2. Business perspective → questions about user types, login flows, compliance needs
3. Technical perspective → questions about current auth system, provider preferences, integration complexity
4. Present questions to user:
   - "Which SSO provider(s) do you need? (Okta, Azure AD, Google, etc.)"
   - "Who are the users needing SSO? (employees, customers, partners)"
   - "Do you need just authentication or also authorization/permissions?"
5. Collect responses and iterate if needed
6. Synthesize into `docs/leads/lead_260401_sso-integration.md`

**Result:**
The user now has a clear document that captures:
- Exactly which SSO provider(s) to integrate
- Who the target users are
- What the authentication flow should look like
- Technical constraints and requirements
- What's in and out of scope
- What the next steps should be

## Common Mistakes

| Mistake | Fix |
|---------|-----|
| Making assumptions instead of asking | Always ask questions rather than guess |
| Asking too many questions at once | Prioritize and batch questions into rounds |
| Accepting vague answers | Follow up with "can you give me a specific example?" |
| Skipping the synthesis step | Must transform Q&A into structured requirements |
| Creating docs/leads if missing | Create the directory if it doesn't exist |
| Rushing to solution | Stay in discovery mode until requirements are clear |
| Not documenting assumptions | Always list what you're assuming explicitly |
| Forgetting open questions | Track unresolved items for future discussion |

## Important Rules

1. **NO CODE CHANGES** - This is a discovery and clarification process only
2. **Always ask questions** - Never assume you understand a vague requirement
3. **Iterate as needed** - Some leads require multiple rounds of clarification
4. **Document everything** - Capture the original input and all Q&A for traceability
5. **Stay neutral** - Don't push toward a particular solution during brainstorming
6. **Be patient** - Users may need time to think through questions

## File Output

- **Location:** `docs/leads/lead_[YYMMDD]_[topic].md`
- **Naming format:**
  - `[YYMMDD]` = Date in YYMMDD format (e.g., 260401 for April 1, 2026)
  - `[topic]` = Kebab-case topic name (e.g., `okta-integration`, `user-reporting`)
  - Example: `lead_260401_okta-integration.md`
- **Create directory:** If `docs/leads/` doesn't exist, create it
- **Status field:** Indicate if lead is "Clarified" or "Needs Further Discussion"
