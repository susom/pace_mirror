# Pediatric Acute Care Data Mirror


#### This module was developed to mirror participant data from Rhapsode to REDCap

## Configuration
#### The project makes use of the following system settings:
 - `rhapsode-url` API endpoint corresponding to survey data in REDCap
 - `rhapsode-username` Rhapsode credentialed username
 - `rhapsode-password` Rhapsode credentialed password

Note: Current credentials only have access to surveys in Rhapsode created by **self** & user **meaneypa**

Cron is currently configured to run once a week and will overwrite previous data.

This module will allow you to mirror the following Rhapsode data into selected REDCap fields (designated in project settings):

- `Learning Progress`
- `Refresher Progress`
- `Latest Activity`

The following fields are automatically updated per record
- `Last Updated`

Example response payload from Rhapsode API:

```text
Learner,Initial Learning Progress,Refresher Progress,Latest Activity
Belina,28%,13%,2023-08-25
Edward,27%,4%,2023-08-09
Kasaba,67%,2%,2023-09-28
Martha,33%,0%,2023-09-15
```

