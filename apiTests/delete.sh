#!/usr/bin/env bash
curl -i -H "Accept:application/json" -H "Content-Type:application/json" \
    -XDELETE "http://priam.local/api/valute" \
    -d '{"id": "R01010", "date": "2020-04-15"}'