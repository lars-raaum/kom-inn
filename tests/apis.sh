#!/bin/bash

validate () {
  is=$2
  should_be=$3
  if [[ $is -eq $should_be ]]; then
    echo -e "\e[92mPassed.\t[$1]\t\e[0m"
  else
    echo -e "\e[31mFailed.\t[$1]\tShould be $should_be, is $is\e[0m"
    # TODO exit with error code so script could be used in build script?
  fi
}

# WEB API - Register
printf "\nWEB API - Register\n"
validate "405 NOT ALLOWED /register GET" $(curl -X GET "http://localhost:8001/register" -si | grep HTTP | awk '{print $2}') 405
validate "400 BAD REQUEST /register POST" $(curl -X POST "http://localhost:8001/register" -si | grep HTTP | awk '{print $2}') 400

# WEB API - Register a host
hostregjson='{"email": "john@example.com", "name": "John Smith", "type": "host"}'
registration=$(curl -H "Content-Type: application/json" -X POST -d "$hostregjson" "http://localhost:8001/register" -si)
registercode=$(echo $registration | grep HTTP | awk '{print $2}')
validate "200 OK /register HOST" $registercode 200
if [[ $registercode -ne 200 ]]; then
    echo "\n \e[31mERROR : Registration failed, aborting early and skipping rest of tests\e[0m\n\n"
    exit 10
fi
# Get the id of the registed HOST
hostjson=$(echo $registration | grep "{\"id\":" )
hostid=$(node -pe 'JSON.parse(process.argv[1]).id' $hostjson)

# WEB API - Register a guest
guestregjson='{"email": "anna@example.com", "name": "Anna Smith", "age": 35, "type": "guest"}'
guestregistration=$(curl -H "Content-Type: application/json" -X POST -d "$guestregjson" "http://localhost:8001/register" -si)
guestregistercode=$(echo $guestregistration | grep HTTP | awk '{print $2}')
validate "200 OK /register GUEST" $guestregistercode 200
if [[ $guestregistercode -ne 200 ]]; then
    echo "\n \e[31mERROR : Registration failed, aborting early and skipping rest of tests\e[0m\n\n"
    exit 10
fi
# Get the id of the registed HOST
guestjson=$(echo $guestregistration | grep "{\"id\":" )
guestid=$(node -pe 'JSON.parse(process.argv[1]).id' $guestjson)


# ADMIN API - People
printf "\nADMIN API - People\n"
validate "200 OK /person/${hostid}" $(curl -X GET "http://localhost:9001/person/${hostid}" -si | grep HTTP | awk '{print $2}') 200
validate "404 NOT FOUND /person/aa" $(curl -X GET "http://localhost:9001/person/aa" -si | grep HTTP | awk '{print $2}') 404
validate "200 OK /people" $(curl -X GET "http://localhost:9001/people" -si | grep HTTP | awk '{print $2}') 200

# ADMIN API - Update person's age
validate "Registered age" $(node -pe 'JSON.parse(process.argv[1]).age' $guestjson) 35
updatejson='{"age": 55}'
updateresponse=$(curl -H "Content-Type: application/json" -X POST -d "$updatejson" "http://localhost:9001/person/${guestid}" -si)
validate "200 OK POST /person/${guestid}" $(echo $updateresponse | grep HTTP | awk '{print $2}') 200
responsejson=$(echo $updateresponse | grep "{\"id\":" )
validate "Updated age" $(node -pe 'JSON.parse(process.argv[1]).age' $responsejson) 55

# ADMIN API - Guests
printf "\nADMIN API - Guest\n"
validate "200 OK /guest/${guestid}" $(curl -X GET "http://localhost:9001/guest/${guestid}" -si | grep HTTP | awk '{print $2}') 200
validate "404 NOT FOUND /guest/aa" $(curl -X GET "http://localhost:9001/guest/aa" -si | grep HTTP | awk '{print $2}') 404
validate "200 OK /guests" $(curl -X GET "http://localhost:9001/guests" -si | grep HTTP | awk '{print $2}') 200

# ADMIN API - Hosts
printf "\nADMIN API - Hosts\n"
validate "200 OK /host/${hostid}" $(curl -X GET "http://localhost:9001/host/${hostid}" -si | grep HTTP | awk '{print $2}') 200
validate "404 NOT FOUND /host/aa" $(curl -X GET "http://localhost:9001/host/aa" -si | grep HTTP | awk '{print $2}') 404
validate "200 OK /hosts" $(curl -X GET "http://localhost:9001/hosts" -si | grep HTTP | awk '{print $2}') 200

# ADMIN API - Create Match
printf "\nADMIN API - Create Match\n"
validate "405 NOT ALLOWED /match GET" $(curl -X GET "http://localhost:9001/match" -si | grep HTTP | awk '{print $2}') 405
validate "400 BAD REQUEST /match POST" $(curl -X POST "http://localhost:9001/match" -si | grep HTTP | awk '{print $2}') 400
match_post_json="{\"guest_id\": ${guestid}, \"host_id\": ${hostid}, \"comment\": \"Automated api test\"}"
matchresponse=$(curl -H "Content-Type: application/json" -X POST -d "$match_post_json" "http://localhost:9001/match" -si)
matchresponsejson=$(echo $matchresponse | grep "{\"id\":" )
matchid=$(node -pe 'JSON.parse(process.argv[1]).id' $matchresponsejson)

# ADMIN API - Matches
printf "\nADMIN API - Matches\n"
validate "200 OK /match/${matchid}" $(curl -X GET "http://localhost:9001/match/${matchid}" -si | grep HTTP | awk '{print $2}') 200
validate "404 NOT FOUND /match/aa" $(curl -X GET "http://localhost:9001/match/aa" -si | grep HTTP | awk '{print $2}') 404
validate "200 OK /matches" $(curl -X GET "http://localhost:9001/matches" -si | grep HTTP | awk '{print $2}') 200
match_post_json="{\"comment\": \"Automated api test - Updated\"}"
matchupdateresponse=$(curl -H "Content-Type: application/json" -X POST -d "$match_post_json" "http://localhost:9001/match/${matchid}" -si)
matchupdateresponsejson=$(echo $matchupdateresponse | grep "{\"id\":" )
validate "Comment updated on match" $(node -pe 'JSON.parse(process.argv[1]).comment' $matchupdateresponsejson) "Automated api test - Updated"

# PUBLIC API - Reactivate user
printf "\nPUBLIC API - Reactivate\n"
validate "405 NOT ALLOWED /reactivate GET" $(curl -X GET "http://localhost:8001/reactivate" -si | grep HTTP | awk '{print $2}') 405
validate "400 BAD REQUEST /reactivate POST" $(curl -X POST "http://localhost:8001/reactivate" -si | grep HTTP | awk '{print $2}') 400

reactivate_json="{\"id\": ${hostid}, \"code\": \"NOTCODE\"}"
reactivate_response=$(curl -H "Content-Type: application/json" -X POST -d "$reactivate_json" "http://localhost:8001/reactivate" -si)
validate "404 NOT FOUND /reactivate POST" $(echo $reactivate_response | grep HTTP | awk '{print $2}') 404

code='21e8712e64284677eb65550cddb8756d584cfe45' # autogenerate in case salt is changed?
reactivate_json="{\"id\": ${matchid}, \"code\": \"${code}\"}"
reactivate_response=$(curl -H "Content-Type: application/json" -X POST -d "$reactivate_json" "http://localhost:8001/reactivate" -si)
validate "200 OK /reactivate POST" $(echo $reactivate_response | grep HTTP | awk '{print $2}') 200
# todo check status of person?

# PUBLIC API - Feedback
printf "\nPUBLIC API - Feedback\n"
validate "405 NOT ALLOWED /feedback GET" $(curl -X GET "http://localhost:8001/feedback" -si | grep HTTP | awk '{print $2}') 405
validate "400 BAD REQUEST /feedback POST" $(curl -X POST "http://localhost:8001/feedback" -si | grep HTTP | awk '{print $2}') 400

# ADMIN API - Delete match
printf "\nADMIN API - Deleting Match\n"
validate "200 OK DELETE /match/${matchid}" $(curl -X DELETE "http://localhost:9001/match/${matchid}" -si | grep HTTP | awk '{print $2}') 200

# ADMIN API - Delete registered test persons
printf "\nADMIN API - Deleting People\n"
validate "200 OK DELETE /person/${hostid}" $(curl -X DELETE "http://localhost:9001/person/${hostid}" -si | grep HTTP | awk '{print $2}') 200
validate "200 OK DELETE /person/${guestid}" $(curl -X DELETE "http://localhost:9001/person/${guestid}" -si | grep HTTP | awk '{print $2}') 200

