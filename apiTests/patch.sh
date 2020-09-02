#!/usr/bin/env bash
curl -i -H "Accept:application/json" -H "Content-Type:application/json" -H "Authorization: Bearer 100-token" \
    -XPATCH "http://alpo.pw/api/valute" \
    -d '{"id": "R01010", "date": "2020-04-25", "value": "73.5119"}'