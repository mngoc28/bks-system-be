# Performance Review Checklist

Reference this file during Step 4 of the review process when examining performance-sensitive code. Focus on changes that affect hot paths, large datasets, or user-facing latency.

## Database Queries

- [ ] **N+1 queries**: No queries inside loops that could be batched into a single query with JOIN or WHERE IN
- [ ] **Missing indexes**: WHERE, JOIN, ORDER BY, and GROUP BY columns have appropriate indexes
- [ ] **Full table scans**: Queries use indexed columns for filtering; no `SELECT *` on large tables
- [ ] **Excessive joins**: Queries don't join more tables than needed for the use case
- [ ] **Unbounded result sets**: Queries have LIMIT/OFFSET or cursor-based pagination
- [ ] **Connection management**: Database connections are released properly (not leaked in error paths)
- [ ] **Transaction scope**: Transactions are as short as possible — no long-running operations inside them

## Memory & Data Processing

- [ ] **Large collection loading**: Entire tables or result sets aren't loaded into memory when streaming/iterating would suffice
- [ ] **Redundant copies**: Data isn't copied unnecessarily (e.g., converting between array/list/collection types multiple times)
- [ ] **String concatenation in loops**: Heavy string building uses StringBuilder/buffer, not repeated concatenation
- [ ] **Resource cleanup**: File handles, connections, and streams are closed in finally blocks or via try-with-resources
- [ ] **Caching opportunities**: Expensive computations or frequently accessed data that rarely changes are cached with appropriate invalidation

## Algorithm & Complexity

- [ ] **Unnecessary nesting**: Nested loops where a hash map or set lookup would reduce O(n*m) to O(n+m)
- [ ] **Redundant computation**: Values computed multiple times that could be computed once and stored
- [ ] **Expensive operations in loops**: Regex compilation, date parsing, or format conversion inside loops moved outside
- [ ] **Lazy evaluation**: Large intermediate collections could use generators/iterators/lazy sequences instead of materializing everything

## Network & I/O

- [ ] **Serial API calls**: Independent API calls made sequentially that could be parallelized
- [ ] **Redundant requests**: Same data fetched multiple times in a single operation
- [ ] **Large payloads**: API responses include more data than needed (missing field selection/partial responses)
- [ ] **Missing compression**: Large responses not using gzip/deflate compression
- [ ] **Timeout configuration**: External calls have reasonable timeouts to prevent cascading failures

## Concurrency

- [ ] **Blocking in async contexts**: No blocking I/O or CPU-heavy work on async/event loop threads
- [ ] **Lock contention**: Shared resources use appropriate concurrency primitives; locks held for minimal time
- [ ] **Deadlock potential**: Multiple locks are always acquired in the same order across all code paths
- [ ] **Thread pool exhaustion**: No unbounded thread creation; thread pools are properly configured

## Frontend-Specific (when applicable)

- [ ] **Bundle size**: New dependencies significantly increase bundle size
- [ ] **Render performance**: List rendering uses virtualization for large lists; no unnecessary re-renders
- [ ] **Image optimization**: Images use appropriate formats, sizes, and lazy loading
- [ ] **Blocking resources**: Render-blocking CSS/JS that could be deferred or loaded asynchronously
