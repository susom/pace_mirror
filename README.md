# Pediatric Acute Care Data Mirror


#### This module was developed to mirror participant data from Rhapsode to REDCap

## Configuration
#### The project makes use of the following system settings:
 - `rhapsode-url` API endpoint corresponding to survey data in REDCap
 - `rhapsode-username` Rhapsode credentialed username
 - `rhapsode-password` Rhapsode credentialed password

Note: Current credentials only have access to surveys in Rhapsode created by **self** & user **meaneypa**

Cron is configured to run once a day and copy over data for individuals under the following conditions:
1. User in rhapsode payload matches consented user (surname + given name) exactly
2. If user has no weekly progress -- rhapsode data will be copied into baseline week 0 event on consent date
3. If user has weekly progress, rhapsode data will be copied into corresponding event every seven days starting from consent date

### Supported Fields
This module will allow you to mirror the following Rhapsode data into selected REDCap fields (designated in project settings):

- `Learning Progress`
- `Overall Refresher Progress`
- `Latest Activity`
---
**Update - Dec 2024**

Additional fields have been added:
- `Activity Last Week` - Encoded as Y/N
- `Automaticity Refresh`
- `Refresh Knowledge`

Along with the following fields (Average total points)
- `Difficulty Breathing`
- `Term Birth B`
- `Body Swelling`
- `Fever`
- `Term Birth A`
- `Diarrhea`

### Examples

Example response payload from Rhapsode API:

```text
Preset 3516
Learner,Adolfine - Difficulty Breathing (Average Total Points),Albert - Term Birth B (Average Total Points),Castory - Body Swelling (Average Total Points),Hanston - Fever (Average Total Points),Namala - Term Birth A (Average Total Points),Neema - Diarrhea (Average Total Points),— (Average Total Points)
Alex White,64%,,,57%,,,
Brent Zhang,,,,,,,
Gloria Jong,50%,0%,,,0%,58%,
```

```text
Preset 3515
Learner,Activity Last Week,Learn,Automaticity Refresh,Refresh Knowledge,Overall Refresh Progress
Alex White,no,52% (16h 39m),0% (),0% (),0%
Brent Zhang,no,46% (7h 43m),0% (),1% (15m),1%
Gloria Jong,no,54% (13h 21m),61% (1h 26m),22% (4h 6m),42%
```
