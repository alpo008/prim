#!/usr/bin/env bash
curl -i -H "Accept:application/json" -H "Content-Type:application/json" \
    -XPATCH "http://priam.local/api/valute" \
    -d '{"id": "R01010", "date": "2020-04-25", "value": "73.5119"}'