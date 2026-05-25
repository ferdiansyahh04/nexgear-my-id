#!/usr/bin/env python3
"""Download the raw log of a specific job/step from a GitHub run.
Usage:
  fetch-ci-step-log.py <run_id> [job_name_substring]
"""
import json
import sys
import urllib.request
import zipfile
import io
import os

REPO = "ferdiansyahh04/nexgear-my-id"


def fetch_json(url):
    with urllib.request.urlopen(url, timeout=15) as r:
        return json.loads(r.read())


def download_log(job_id):
    url = f"https://api.github.com/repos/{REPO}/actions/jobs/{job_id}/logs"
    try:
        with urllib.request.urlopen(url, timeout=20) as r:
            return r.read().decode("utf-8", errors="replace")
    except urllib.error.HTTPError as e:
        # Logs require auth for private repos, but public repos via redirect work
        if e.code == 302:
            return urllib.request.urlopen(e.headers["Location"]).read().decode("utf-8", errors="replace")
        return f"(could not fetch — {e})"


def main():
    if len(sys.argv) < 2:
        print("Usage: fetch-ci-step-log.py <run_id> [job_filter]")
        sys.exit(1)
    run_id = sys.argv[1]
    job_filter = sys.argv[2].lower() if len(sys.argv) > 2 else None

    jobs = fetch_json(f"https://api.github.com/repos/{REPO}/actions/runs/{run_id}/jobs")
    for job in jobs.get("jobs", []):
        if job_filter and job_filter not in job["name"].lower():
            continue
        if job["conclusion"] not in ("failure", "cancelled"):
            continue
        print(f"\n========== {job['name']} (id={job['id']}) ==========")
        log = download_log(job["id"])
        # Print last 80 lines
        lines = log.splitlines()
        for line in lines[-80:]:
            print(line)


if __name__ == "__main__":
    main()
