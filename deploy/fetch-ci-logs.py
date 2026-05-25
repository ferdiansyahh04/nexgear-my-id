#!/usr/bin/env python3
"""Fetch failed jobs + step results from a GitHub Actions run."""
import json
import sys
import urllib.request

REPO = "ferdiansyahh04/nexgear-my-id"


def fetch(url):
    with urllib.request.urlopen(url, timeout=15) as r:
        return json.loads(r.read())


def main():
    if len(sys.argv) < 2:
        print("Usage: fetch-ci-logs.py <run_id>")
        sys.exit(1)
    run_id = sys.argv[1]
    jobs = fetch(f"https://api.github.com/repos/{REPO}/actions/runs/{run_id}/jobs")
    for job in jobs.get("jobs", []):
        print(f"=== Job: {job['name']} | conclusion: {job['conclusion']} ===")
        for step in job.get("steps", []):
            mark = "OK" if step["conclusion"] == "success" else (step["conclusion"] or "skip").upper()
            print(f"  [{mark}] {step['name']}")
        print()


if __name__ == "__main__":
    main()
