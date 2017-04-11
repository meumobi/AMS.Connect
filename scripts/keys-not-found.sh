#!/bin/sh
clear
echo "+-------------------------------------------------------------------------------------+"
echo "|   > keys-not-found                                                                  |"
echo "|   This script tail logs to catch keys not found messages                            |"
echo "|     * on exit, the script resumes on keys-not-found.log                             |"
echo "|     list of keys missing as reported on logs                                        |"
echo "+-------------------------------------------------------------------------------------+"

FILE=keys-not-found.log

heroku logs --tail | grep -i "Key Not Found" | awk -F ":" '{ print $8 $9}' | sed -e 's/{\"key\"//g'| awk '{$1=$1};1'| cut -f 1 -d, > $FILE
# to trim awk '{$1=$1};1'


#heroku logs --tail | grep "AdServing Key Not Found" | awk -F "\"" '{print $4}' > $FILE
sort -u -o $FILE $FILE
echo "Number of Keys not found:"
cat $FILE | wc -l