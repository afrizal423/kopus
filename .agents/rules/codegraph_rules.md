# CodeGraph & Dual Code Search Rule

## Objective
Establish an accurate, zero-hallucination code search strategy by prioritizing CodeGraph when an indexed database exists, executing a hybrid dual search with native search tools, and falling back gracefully when CodeGraph is not present.

## Rules & Trigger Conditions

### 1. Database Presence Check
- Verify whether the `.codegraph/` directory contains an active database file (e.g., `codegraph.db`).

### 2. When `codegraph.db` is Present
- **Priority 1 (Native MCP Tool)**: Call MCP server `codegraph` using the `codegraph_explore` tool:
  ```json
  {
    "ServerName": "codegraph",
    "ToolName": "codegraph_explore",
    "Arguments": { "query": "<symbol, class, function, or query>" }
  }
  ```
  This immediately fetches callers, dependencies, blast radius, and line-numbered verbatim source code.
- **Priority 2 (CLI Fallback)**: If MCP is unreachable or offline, run via shell command:
  ```powershell
  cmd /c codegraph explore "<query>"
  ```
- **Hybrid / Dual Search Strategy**:
  - Run CodeGraph exploration alongside native tools (`grep_search`, `view_file`).
  - CodeGraph provides structural understanding and dependency trees, while native search/view tools verify active verbatim lines and surrounding file context.
  - This dual approach guarantees complete and accurate code comprehension before any refactoring or modification.

### 3. When `codegraph.db` is Absent
- Do not throw errors or fail execution.
- Immediately fallback to default native search mechanisms (`grep_search`, `list_dir`, `view_file`).
