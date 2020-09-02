#!/usr/bin/env bash
curl -i -H "Accept:application/json" -H "Content-Type:application/json" -H "Authorization: Bearer 100-token" \
    -XPUT "http://priam.local/api/valute" \
    -d '{"id": "R01010", "date": "2020-04-15", "value": "87.1618"}'