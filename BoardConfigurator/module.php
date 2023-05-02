<?php

declare(strict_types=1);
eval('declare(strict_types=1);namespace LuanaMonday {?>' . file_get_contents(__DIR__ . '/../libs/vendor/SymconModulHelper/DebugHelper.php') . '}');

define('GUID_GROUP', '{2EA48728-FC44-B701-A7AB-F6FBDFCCA2E1}');
define('GUID_MONDAY_CLOUD', '{9DD94999-AC6B-10C6-E80B-445F637924E3}');

    class BoardConfigurator extends IPSModule
    {
        use \LuanaMonday\DebugHelper;

        public function Create()
        {
            //Never delete this line!
            parent::Create();
            $this->RequireParent('{9DD94999-AC6B-10C6-E80B-445F637924E3}');
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

        public function GetConfigurationForm()
        {
            $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
            $boards = $this->getAllBoards();

            IPS_LogMessage('test', print_r($boards, true));

            $Values = [];
            $parentID = 9000;

            foreach ($boards as $key => $board) {
                $Values[] = [
                    'id'                                  => intval($board['id']),
                    'parent'                              => 0,
                    'DisplayName'                         => $board['name'],
                    'Description'                         => $board['description'],
                    'State'                               => $board['state']
                ];
                foreach ($board['groups'] as $key => $group) {
                    $instanceID = $this->getInstanceID($board['id'], $group['id']);
                    $Values[] = [
                        'id'                                        => $parentID,
                        'groupId'                                   => $group['id'],
                        'parent'                                    => intval($board['id']),
                        'DisplayName'                               => $group['title'],
                        'Description'                               => '',
                        'State'                                     => '',
                        'instanceID'                                => $instanceID,
                        'create'                                    => [
                            'moduleID'      => GUID_GROUP,
                            'configuration' => [
                                'BoardID' => $board['id'],
                                'GroupID' => $group['id']
                            ]
                        ]
                    ];
                    $parentID++;
                }
            }
            $Form['actions'][0]['values'] = $Values;
            return json_encode($Form);
        }

        private function getInstanceID($boardID, $groupID)
        {
            $InstanceIDs = IPS_GetInstanceListByModuleID(GUID_GROUP);
            foreach ($InstanceIDs as $ID) {
                if ((IPS_GetProperty($ID, 'BoardID') == $boardID) && (IPS_GetProperty($ID, 'GroupID') == $groupID)) {
                    return $ID;
                }
            }
            return 0;
        }

        private function getAllBoards()
        {
            $Data = [];
            $Buffer = [];

            $query = '{
				boards(limit: 250) {
				  id
                  name
				  description
				  state
				  groups(){
					id,
					title
				  }
				}
			  }';

            $Data['DataID'] = '{5537F0FF-18D6-B6D7-715E-F69B1A6225DF}';
            $Buffer['Command'] = 'sendRequest';
            $Buffer['Query'] = $query;
            $Buffer['vars'] = [];
            $Data['Buffer'] = $Buffer;
            $Data = json_encode($Data);
            $result = json_decode($this->SendDataToParent($Data), true);
            if (!$result) {
                return [];
            }
            $result = json_decode($result, true);
            return $result['data']['boards'];
        }
    }