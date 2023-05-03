<?php

declare(strict_types=1);
eval('declare(strict_types=1);namespace LuanaMonday {?>' . file_get_contents(__DIR__ . '/../libs/vendor/SymconModulHelper/DebugHelper.php') . '}');

    class MondayCloud extends IPSModule
    {
        use \LuanaMonday\DebugHelper;
        private $apiURL = 'https://api.monday.com/v2';

        public function Create()
        {
            //Never delete this line!
            parent::Create();
            $this->RegisterPropertyString('Token', '');
        }

        public function Destroy()
        {
            //Never delete this line!
            parent::Destroy();
        }

        public function ApplyChanges()
        {
            //Never delete this line!
            parent::ApplyChanges();
        }

        public function ForwardData($JSONString)
        {
            $this->SendDebug(__FUNCTION__ . ' :: JSON String', $JSONString, 0);
            $data = json_decode($JSONString, true);

            switch ($data['Buffer']['Command']) {
                case 'sendRequest':
                    $result = json_encode($this->sendRequest($data['Buffer']['Query'], $data['Buffer']['vars']));
                    break;
                default:
                $this->SendDebug(__FUNCTION__, ' :: Invalid Command: ' . $data['Buffer']['Command'], 0);
                break;
            }
            return json_encode($result);
        }

        private function sendRequest($query, $vars = [])
        {
            $token = $this->ReadPropertyString('Token');
            if ($token == '') {
                return;
            }

            $headers = [
                'Content-Type: application/json',
                'Authorization: ' . $token
            ];

            $this->SendDebug('Query', $query,0);
            $this->SendDebug('Variablen', $vars,0);

            $data = @file_get_contents($this->apiURL, false, stream_context_create([
                'http' => [
                    'method'  => 'POST',
                    'header'  => $headers,
                    'content' => json_encode(['query' => $query, 'variables' => $vars]),
                ]
            ]));

            if ($data != false) {
                $responseContent = json_decode($data, true);
                $this->SendDebug(__FUNCTION__ . ' result', $responseContent, 0);
            } else {
                $this->LogMessage($this->Translate('Error on sendRequest'),KL_ERROR);
                $responseContent = [];
            }


            return $responseContent;
        }
    }