# Adding All Modified Files to a Pull Request

When you open a pull request, GitHub shows whatever commits you have pushed to the branch. To make sure *every* file you touched ends up in that PR, follow this workflow from inside your local clone of the repository:

## 1. Inspect your working tree
```bash
git status
```
This shows which files are modified, staged, or untracked.

If you see untracked files that belong in the change, add them explicitly:
```bash
git add <path/to/file>
```

## 2. Stage all changes you want in the PR
Use one of the following commands from the repository root:

- Stage every tracked and untracked change:
  ```bash
  git add -A
  ```
- Or, if you only want to stage tracked files:
  ```bash
  git add .
  ```

You can combine them—start with `git add .` for tracked updates, then run `git add -A` if you created or deleted files that also belong in the PR.

## 3. Verify what is staged
```bash
git status
```
The output should now list the staged files under “Changes to be committed.” If something is missing, go back and add it.

Optional but recommended: review the actual diffs before committing.
```bash
git diff --cached
```

## 4. Commit the staged snapshot
```bash
git commit -m "Describe your change"
```
Each commit becomes part of the PR. You can repeat the add/commit steps to build multiple commits if needed.

## 5. Push the branch
```bash
git push origin <your-branch-name>
```
Once pushed, GitHub updates the pull request automatically. You can confirm the correct files appear in the **Files changed** tab.

## 6. Double-check before merging
Before you merge the PR, verify the file list once more in the GitHub UI. If something is missing, return to your local repository, stage the file with `git add`, commit, and push again.

Following these steps ensures every relevant file is staged, committed, and uploaded to the PR without surprises.
