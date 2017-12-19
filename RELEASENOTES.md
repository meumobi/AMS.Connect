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

<a name="v1.2.0"></a>
# v1.2.0
* ENHANCE: remove obsolet adserving pid lock
* FEATURE: Closes #82, Commands online to perform providers
* FIX: Closes #97, missing 'revenu net' field on premium raws
* FIX: Closes #96, revenu net is not computed from unplugged records of the day

<a name="v1.1.0"></a>
# v1.1.0
* ENHANCE: add github issue template
* ENHANCE: Closes #84, Improve logs messages shared on Slack
* ENHANCE: for consistency replace ND by NA when Not Applicable, use Unknown when missing
* ENHANCE: refactor criteo service - allow custom configs for Criteo hb and Criteo wf
* ENHANCE: rename LOG_CHANNEL console instead of local
* ENHANCE: set 'NA' instead of 'Unknown when field is Not Applicable'
* ENHANCE: Closes #85, LOG_LEVEL can be set on .env
* FEATURE: Closes #87, Criteo Provider Splited into criteohb (header-bidding) and criteowf
* FIX: Closes #77, Sublime API Excepction is catched and logged
* FIX: Closes #88, add position default value on AMS response when key not found on TDC
* FIX: Closes #92, prevent exception if admargin/unplugged or adtech email missing
* FIX: Closes #93, adtech Mapping Error Undefined index: cpm
* FIX: Closes #94, catch error when adtech csv contains errors
* FIX: Closes #95, add margin field on unplugged raws
* FIX: Get latest Admargin email
* FIX: Remove old reference to cpm field raising Mapping exception
* FIX: remove upper cases on 'impressions envoyees' label when unknown
* FIX: update criteo inventaire partenaire from config.php

<a name="v1.0.0"></a>
# v1.0.0
* ENHANCE: add option --update-correlationtable to providers:perform
* FEATURE: Closes #83, Migrate Correlation Table to Googlesheet
* FEATURE: Closes #81, Publish log on Slack
* FEATURE: Allow Logs in a Slack Channel
* FEATURE: Closes #71, add fields 'net revenu' and 'marge' on AMS.Format
