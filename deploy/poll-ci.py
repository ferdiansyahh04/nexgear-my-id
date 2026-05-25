#!/usr/bin/env python3
"""Poll the latest GitHub Actions run for this repo and print status."""
import json
import sys
import time
import urllib.request

REPO = "ferdiansyahh04/nexgear-my-id"
URL = f"https://api.github.com/repos/{REPO}/actions/runs?per_page=1"


def fetch():
    with urllib.request.urlopen(URL, timeout=10) as r:
        return json.loads(r.read())


def main():
    poll = "--poll" in sys.argv
    while True:
        data = fetch()
        runs = data.get("workflow_runs", [])
        if not runs:
            print("No runs yet")
            return
        run = runs[0]
        print(
            f"Run #{run['run_number']:>4} | "
            f"status: {run['status']:<11} | "
            f"conclusion: {run['conclusion']} | "
            f"branch: {run['head_branch']} | "
            f"sha: {run['head_sha'][:7]} | "
            f"event: {run['event']}"
        )
        print(f"  url: {run['html_url']}")

        if not poll:
            return
        if run["status"] == "completed":
            print(f"\nFinal: {run['conclusion'].upper()}")
            return
        time.sleep(15)


if __name__ == "__main__":
    main()
