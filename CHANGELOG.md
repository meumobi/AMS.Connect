<a name="v1.2.2"></a>
# [v1.2.2](https://github.com/meumobi/infomobi/compare/v1.2.1...v1.2.2)
* UPGRADE: Closes #106, Update adtech key for concat("Emplacement", "ID du flight", "Bannière ID")
* UPGRADE: Closes #107, adtech: for 'campagne' field replace 'flight description' by '(Master) Campaign'
* FIX: remove firebase DatabaseUri from pushToFirebase to switch INT, PROD accounts by setting config FIREBASE_CONFIG_PATH

<a name="v1.2.1"></a>
# v1.2.1
* ENAHNCE: Closes #100, improve logs when raise ErrorException: array_combine()
* ENHANCE: add rubicon stub
* ENHANCE: Closes #101, improve logs when ErrorException:: array_reduce() in RubiconPresenter
* ENHANCE: Closes #103, accept 'text' param as date for GET requests to /providers/perform/(providerName)
* ENHANCE: increase number fo decimals on revenue to prevent equal 0 when impressions is greater than 0
* ENHANCE: reduce log level of some messages
* FIX: Closes #99, remove controller url from logs to prevent loop when LOG_CHANNEL=slack
* FIX: none rubicon raws imported

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
