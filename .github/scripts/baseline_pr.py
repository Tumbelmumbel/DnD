"""Baseline refresh: put the new test baseline into the branch "baseline-refresh" and open a pull request.

Called by the job "baseline-refresh" of deploy.yml. It runs no code of a pull request.
- The commit is created through the GitHub API, so GitHub signs it (author github-actions[bot]).
  The job test-scope (tests.yml) accepts it only for tests/baseline/baseline.sql.
- The branch "baseline-refresh" always starts from the deployed commit of main.
- It opens a pull request if none is open, and starts the workflow "Tests" for it.

Environment: GH_TOKEN, GITHUB_REPOSITORY, GITHUB_SHA, NEW_BASELINE (path of the new file).
"""
import base64
import json
import os
import subprocess

REPO = os.environ["GITHUB_REPOSITORY"]
SHA = os.environ["GITHUB_SHA"]
BRANCH = "baseline-refresh"
PATH = "tests/baseline/baseline.sql"
OWNER = REPO.split("/")[0]


def api(*args, data=None, check=True):
    cmd = ["gh", "api", *args] + (["--input", "-"] if data is not None else [])
    res = subprocess.run(cmd, input=json.dumps(data) if data is not None else None,
                         capture_output=True, text=True)
    if check and res.returncode != 0:
        raise SystemExit(f"GitHub API error ({' '.join(args[:2])}): {res.stderr.strip()[:300]}")
    return json.loads(res.stdout) if res.stdout.strip() else {}, res.returncode


content = open(os.environ["NEW_BASELINE"], "rb").read()
blob, _ = api(f"repos/{REPO}/git/blobs", data={"content": base64.b64encode(content).decode(), "encoding": "base64"})
base, _ = api(f"repos/{REPO}/git/commits/{SHA}")
tree, _ = api(f"repos/{REPO}/git/trees", data={
    "base_tree": base["tree"]["sha"],
    "tree": [{"path": PATH, "mode": "100644", "type": "blob", "sha": blob["sha"]}]})
commit, _ = api(f"repos/{REPO}/git/commits", data={
    "message": "Baseline refresh: test baseline from the live database\n\n"
               "Structure of all tables and the data of the game tables (allowlist in deploy.yml).\n"
               "No user data. Written by the workflow deploy.yml (job baseline-refresh).",
    "tree": tree["sha"], "parents": [SHA]})

_, missing = api(f"repos/{REPO}/git/ref/heads/{BRANCH}", check=False)
if missing:
    api(f"repos/{REPO}/git/refs", data={"ref": f"refs/heads/{BRANCH}", "sha": commit["sha"]})
else:
    api("-X", "PATCH", f"repos/{REPO}/git/refs/heads/{BRANCH}", data={"sha": commit["sha"], "force": True})
print("Branch", BRANCH, "->", commit["sha"])

pulls, _ = api(f"repos/{REPO}/pulls?state=open&head={OWNER}:{BRANCH}")
if pulls:
    number = pulls[0]["number"]
    print("Open pull request:", number)
else:
    body = (
        "## Requirement and behavior\n\n"
        "Automatic baseline refresh after the deploy of " + SHA[:7] + ". "
        "`tests/baseline/baseline.sql` gets the structure of all tables and the data of the game tables "
        "of the live database. It has no user data.\n\n"
        "## Changes\n\n- `tests/baseline/baseline.sql`: new state from the live database.\n\n"
        "## Reason\n\nThe automatic tests must use the same structure and game data as the live database.\n\n"
        "## Before you merge\n\nThe owner checks the diff. If GitHub holds the run of \"Tests\", "
        "the owner clicks \"Approve and run workflows\".\n\n"
        "## Test\n\n1. Action: Open the tab \"Checks\".\n   Expected result: `gate` is green.\n"
    )
    pr, code = api(f"repos/{REPO}/pulls", data={
        "title": "Baseline refresh from the live database", "head": BRANCH, "base": "main", "body": body},
        check=False)
    if code != 0:
        print("::warning::The branch baseline-refresh is ready, but the workflow cannot open a pull request. "
              "Open it by hand, or allow it: Settings -> Actions -> General -> "
              "'Allow GitHub Actions to create and approve pull requests'.")
        raise SystemExit(0)
    number = pr["number"]
    print("New pull request:", number)

subprocess.run(["gh", "workflow", "run", "tests.yml", "-R", REPO, "--ref", BRANCH, "-f", f"pr={number}"], check=True)
print("Started the workflow Tests for pull request", number)
