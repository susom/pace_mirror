<?php
/**
 *
 * CSIRO Open Source Software Licence Agreement (variation of the BSD / MIT License)
 * Copyright (c) 2018, Commonwealth Scientific and Industrial Research Organisation (CSIRO) ABN 41 687 119 230.
 * All rights reserved. CSIRO is willing to grant you a licence to this SimpleOntologyExternalModule on the following terms, except where otherwise indicated for third party material.
 * Redistribution and use of this software in source and binary forms, with or without modification, are permitted provided that the following conditions are met:
 * Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
 * Neither the name of CSIRO nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission of CSIRO.
 * EXCEPT AS EXPRESSLY STATED IN THIS AGREEMENT AND TO THE FULL EXTENT PERMITTED BY APPLICABLE LAW, THE SOFTWARE IS PROVIDED "AS-IS". CSIRO MAKES NO REPRESENTATIONS, WARRANTIES OR CONDITIONS OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO ANY REPRESENTATIONS, WARRANTIES OR CONDITIONS REGARDING THE CONTENTS OR ACCURACY OF THE SOFTWARE, OR OF TITLE, MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, NON-INFRINGEMENT, THE ABSENCE OF LATENT OR OTHER DEFECTS, OR THE PRESENCE OR ABSENCE OF ERRORS, WHETHER OR NOT DISCOVERABLE.
 * TO THE FULL EXTENT PERMITTED BY APPLICABLE LAW, IN NO EVENT SHALL CSIRO BE LIABLE ON ANY LEGAL THEORY (INCLUDING, WITHOUT LIMITATION, IN AN ACTION FOR BREACH OF CONTRACT, NEGLIGENCE OR OTHERWISE) FOR ANY CLAIM, LOSS, DAMAGES OR OTHER LIABILITY HOWSOEVER INCURRED.  WITHOUT LIMITING THE SCOPE OF THE PREVIOUS SENTENCE THE EXCLUSION OF LIABILITY SHALL INCLUDE: LOSS OF PRODUCTION OR OPERATION TIME, LOSS, DAMAGE OR CORRUPTION OF DATA OR RECORDS; OR LOSS OF ANTICIPATED SAVINGS, OPPORTUNITY, REVENUE, PROFIT OR GOODWILL, OR OTHER ECONOMIC LOSS; OR ANY SPECIAL, INCIDENTAL, INDIRECT, CONSEQUENTIAL, PUNITIVE OR EXEMPLARY DAMAGES, ARISING OUT OF OR IN CONNECTION WITH THIS AGREEMENT, ACCESS OF THE SOFTWARE OR ANY OTHER DEALINGS WITH THE SOFTWARE, EVEN IF CSIRO HAS BEEN ADVISED OF THE POSSIBILITY OF SUCH CLAIM, LOSS, DAMAGES OR OTHER LIABILITY.
 * APPLICABLE LEGISLATION SUCH AS THE AUSTRALIAN CONSUMER LAW MAY APPLY REPRESENTATIONS, WARRANTIES, OR CONDITIONS, OR IMPOSES OBLIGATIONS OR LIABILITY ON CSIRO THAT CANNOT BE EXCLUDED, RESTRICTED OR MODIFIED TO THE FULL EXTENT SET OUT IN THE EXPRESS TERMS OF THIS CLAUSE ABOVE "CONSUMER GUARANTEES".  TO THE EXTENT THAT SUCH CONSUMER GUARANTEES CONTINUE TO APPLY, THEN TO THE FULL EXTENT PERMITTED BY THE APPLICABLE LEGISLATION, THE LIABILITY OF CSIRO UNDER THE RELEVANT CONSUMER GUARANTEE IS LIMITED (WHERE PERMITTED AT CSIRO'S OPTION) TO ONE OF FOLLOWING REMEDIES OR SUBSTANTIALLY EQUIVALENT REMEDIES:
 * (a)               THE REPLACEMENT OF THE SOFTWARE, THE SUPPLY OF EQUIVALENT SOFTWARE, OR SUPPLYING RELEVANT SERVICES AGAIN;
 * (b)               THE REPAIR OF THE SOFTWARE;
 * (c)               THE PAYMENT OF THE COST OF REPLACING THE SOFTWARE, OF ACQUIRING EQUIVALENT SOFTWARE, HAVING THE RELEVANT SERVICES SUPPLIED AGAIN, OR HAVING THE SOFTWARE REPAIRED.
 * IN THIS CLAUSE, CSIRO INCLUDES ANY THIRD PARTY AUTHOR OR OWNER OF ANY PART OF THE SOFTWARE OR MATERIAL DISTRIBUTED WITH IT.  CSIRO MAY ENFORCE ANY RIGHTS ON BEHALF OF THE RELEVANT THIRD PARTY.
 * Third Party Components
 * The following third party components are distributed with the Software.  You agree to comply with the licence terms for these components as part of accessing the Software.  Other third party software may also be identified in separate files distributed with the Software.
 *
 *
 *
 */

namespace Stanford\PACE;
include "emLoggerTrait.php";

use ExternalModules\AbstractExternalModule;
use Exception;
use REDCap;
use GuzzleHttp\Exception\GuzzleException;

require_once "classes/Client.php";

class PACE extends AbstractExternalModule
{
    use emLoggerTrait;

    public function __construct()
    {
        parent::__construct();
    }

    function redcap_module_link_check_display($project_id, $link)
    {
        //Add manual parameter to link display to triage when hitting mirrorRhapsode.php
        if (isset($link) && array_key_exists('url', $link) && str_contains($link['url'], 'pace_data_mirror&page=cron%2FmirrorRhapsode')) {
            $link['url'] = $link['url'] . '&manual=1';
        }
        return $link;
    }


    /**
     * Function called by cron to inject PID
     * @return void
     * @throws GuzzleException
     */
    public function callCron()
    {
        try {
            $client = new Client(null, null);
            $projects = $this->framework->getProjectsWithModuleEnabled();
            $url = $this->getUrl("cron/mirrorRhapsode.php", true);

            $options = [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ];

            foreach ($projects as $pid) {
                $client->createRequest("GET", ($url . "&pid=$pid"), $options);
            }
        } catch (\Exception $e) {
            $this->emError($e);
            \REDCap::logEvent("Cron failed: $e");
        }

    }

    /**
     * @param $current_event
     * @param $ending_event
     */
    public function updateCurrentEvent($current_event, $ending_event): void
    {
        if ($current_event === $ending_event) {
            $this->setProjectSetting("current_event", "Done");
        } else { //Increment event name by 1
            $expl = explode("_", $current_event);
            $expl[1] = strval(intval($expl[1]) + 1);
            $fin = implode("_", $expl);
            $this->setProjectSetting("current_event", $fin);
        }
    }

    /**
     * @param string $type
     * @return void
     * @throws GuzzleException
     */
    public function mirrorRhapsode($type): void
    {
        $project_settings = $this->getProjectSettings();
        if($project_settings["current_event"] !== "Done") {

            // Grab Rhapsode API data and format
            $data = $this->fetch();
            $formatted = $this->formatResponse($data);

            try {
                // Grab event name
                $events = REDCap::getEventNames(TRUE);

                if (empty($project_settings["current_event"])) {
                    $event_name = $events[$project_settings["start_event"]];
                } else {
                    $event_name = $project_settings["current_event"];
                }

                // Get all user records
                $params = array(
                    "return_format" => "json",
                    "fields" => array("screen_surname", "screen_firstname", "participant_id"),
                    "redcap_event_name" => "enrollment_arm_1",
                );

                $json = json_decode(REDCap::getData($params), true);

                $upload = [];

                foreach ($json as $user) {
                    $full = strtolower($user['screen_firstname'] . " " . $user['screen_surname']);

                    // If user exists in Rhapsode data
                    if (array_key_exists($full, $formatted)) {
                        // Build payload for data upload
                        $currentDate = date('Y-m-d');
                        $upload[] = array(
                            "participant_id" => $user['participant_id'],
                            $project_settings["rhapsode_learning_progress"] => str_replace("%", '', $formatted[$full][0]),
                            $project_settings["rhapsode_refresher_progress"] => str_replace("%", '', $formatted[$full][1]),
                            $project_settings["rhapsode_latest_activity"] => $formatted[$full][2],
                            "redcap_event_name" => $event_name
                        );
                    }
                }

                $response = REDCap::saveData('json', json_encode($upload), 'overwrite');

                if (!empty($response['errors'])) {
                    throw new Exception("Could not update record with " . json_encode($response['errors']));
                } else { //Success, update current event
                    if($type !== "manual")
                        $this->updateCurrentEvent($event_name, $events[$project_settings["end_event"]]);
                }


            } catch (\Exception $e) {
                $this->emError($e);
                \REDCap::logEvent("Error: $e");
            }
        } else {
            if($type === "manual")
                \REDCap::logEvent("Manual data pull failed: Rhapsode pull requested with a done designation in project settings");
            else
                \REDCap::logEvent("Cron failed: Rhapsode pull requested with a done designation in project settings");
        }

    }

    /**
     * @return void
     */
    public function logManualTrigger()
    {
        \REDCap::logEvent("Manual refresh triggered");
    }

    /**
     * @return void
     */
    public function logCronTrigger(){
        \REDCap::logEvent("Cron started");
    }


    /**
     *
     * @return ?string
     * @throws GuzzleException
     */
    public function fetch(): ?string
    {
        try {
            $user = $this->getSystemSetting('rhapsode-username');
            $pass = $this->getSystemSetting('rhapsode-password');
            $url = $this->getProjectSetting('rhapsode-url');

            if (empty($user) || empty($pass) || empty($url))
                throw new \Exception("Empty system and / or project settings");

            $client = new Client($user, $pass);
            return $client->createRequest('GET', $url);

        } catch (\Exception $e) {
            $this->emError($e);
            return null;
        }
    }

    /**
     * Return an associative array of names with each of the following values:
     * Initial Learning Progress, Refresher Progress, Latest Activity
     * @param string $data
     * @return array
     */
    public function formatResponse(string $data): array
    {
        $lines = explode("\n", $data);
        $data = [];

        // Iterate through each line (skipping the first line with headers) and explode it into an array of values
        for ($i = 1; $i < count($lines); $i++) {
            $values = explode(',', $lines[$i]);
            $name = explode(' ', trim($values[0]));

            if (($key = array_search("", $name)) !== false) {
                unset($name[$key]);
                $values[0] = implode(" ", $name);
            }


            // Use the first value (name of the user) as the index in the associative array
            $username = strtolower($values[0]);
            str_replace(" ", "", $username);

            // Assign the rest of the values to the user in the associative array
            $data[$username] = array_slice($values, 1);
        }

        return $data;
    }
}
