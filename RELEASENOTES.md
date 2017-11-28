# Release Notes

## Update Release Notes
### Get Resume of changes commits
Update these notes using: git log --pretty=format:'%s' --no-merges v1.0.0..HEAD

#### Commit Release Notes
Use Resume of Changes from previous command on commit message

1. $ git add RELEASENOTES.md 
2. $ git commit 

### Tag and Push Release

1. $ git tag v1.0.0
2. $ git push origin v1.0.0

<a name="v1.0.0"></a>
# v1.0.0
ENHANCEMENT: add option --update-correlationtable to providers:perform
FEATURE: Closes #83, Migrate Correlation Table to Googlesheet
FEAT: Closes #81, Publish log on Slack
FEAT: Allow Logs in a Slack Channel
FEATURE: Closes #71, add fields 'net revenu' and 'marge' on AMS.Format filled by csv from mailbox