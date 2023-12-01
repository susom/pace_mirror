# Pediatric Acute Care Data Mirror


#### This module was developed to mirror participant data from Rhapsode to REDCap

## Configuration
#### The project makes use of the following system settings:
 - `rhapsode-url` API endpoint corresponding to survey data in REDCap
 - `rhapsode-username` Rhapsode credentialed username
 - `rhapsode-password` Rhapsode credentialed password

Note: Current credentials only have access to surveys in Rhapsode created by **self** & user **meaneypa**

Cron is currently configured to run once a week and will overwrite previous data.

The module will mirror Rhapsode data into the following REDCap fields:

- `learning_progress`
- `refresher_progress`
- `latest_activity`
- `last_updated`

Example response payload:

```text
Learner,Initial Learning Progress,Refresher Progress,Latest Activity
Belina,28%,13%,2023-08-25
Edward,27%,4%,2023-08-09
Kasaba,67%,2%,2023-09-28
Martha,33%,0%,2023-09-15
```
