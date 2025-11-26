# Exported Changed Files

This directory holds optional deployment bundles for the refreshed CallRecord UI. To keep the repository free of binary blobs, the pre-built `changed_files.zip` archive was removed. You can generate the package locally whenever you need to redeploy the styling updates.

## Where to find this folder

| Environment | Path |
|-------------|------|
| **Git working copy** | `<repo-root>/CallRecord/artifacts/` |
| **Deployed web root** | `<document-root>/CallRecord/artifacts/` |

The folder is intentionally committed empty (apart from this README) so you can add your own archives without creating merge conflicts.

## Creating a fresh archive

Run the following from the repository root to package the modernised assets. Adjust the destination path if you prefer another location.

```bash
zip -r CallRecord/artifacts/changed_files.zip \
  CallRecord/css/record_style.css \
  CallRecord/includes/footer.php \
  CallRecord/includes/functions.php \
  CallRecord/includes/header.php \
  CallRecord/index.php
```

On Windows you can select the same files in File Explorer, right-click, and choose **Send to → Compressed (zipped) folder**, then move the archive into `CallRecord\artifacts`.

## Deploying the files without a zip

Because the repository already tracks each updated source file, you can also deploy by copying them directly:

1. Fetch the latest commits (`git pull`).
2. Copy the files listed above from your working copy into the target server.
3. Clear your browser cache or append a cache-busting query string when testing the new styles.

Use whichever approach fits your deployment workflow; keeping the binary bundle out of version control ensures the repo remains lean and easy to review.
