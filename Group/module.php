<?php

declare(strict_types=1);
    class Group extends IPSModule
    {
        public function Create()
        {
            //Never delete this line!
            parent::Create();
            $this->ConnectParent('{9DD94999-AC6B-10C6-E80B-445F637924E3}');

            $this->RegisterPropertyString('BoardID', '');
            $this->RegisterPropertyString('GroupID', '');
        }

        public function Destroy()
        {
            //Never delete this line!
            parent::Destroy();
        }

        public function GetConfigurationForm()
        {
            $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        }

        public function ApplyChanges()
        {
            //Never delete this line!
            parent::ApplyChanges();
        }

        public function getActionForm(string $actionGUID)
        {
            $Form = [];

            if ($actionGUID == '{13847E74-A394-47C3-83F0-53885CA54FFE}') {
                $items = $this->getGroupItems();

                $options = [];
                $values = [];
                foreach ($items as $item) {
                    $options[] = [
                        'value'       => $item['id'],
                        'caption'     => $item['name'],
                    ];
                }

                $Form[] = [
                    'type'    => 'Select',
                    'name'    => 'element',
                    'caption' => $this->Translate('Element'),
                    'options' => $options
                ];

            }

            $columns = $this->getColumns();
            $this->SendDebug('columns', json_encode($columns), 0);
            //IPS_LogMessage('columns', print_r($columns, true));
            foreach ($columns as $column) {
                switch ($column['type']) {
                        case 'name':
                            $Form[] = [
                                'type'    => 'ValidationTextBox',
                                'name'    => strval($column['id']),
                                'caption' => $column['title'],
                            ];
                            break;
                        case 'multiple-person':

                            $users = $this->getUsers();
                            $this->SendDebug('users', json_encode($users), 0);

                            $options = [];
                            $values = [];
                            foreach ($users as $user) {
                                $values[] = [
                                    'id'   => $user['id'],
                                    'name' => $user['name'],
                                ];

                                $options[] = [
                                    'value'       => $user['id'],
                                    'caption'     => $user['name'],
                                ];
                            }

                            $Form[] = [
                                'type'    => 'Select',
                                'name'    => strval($column['id']),
                                'caption' => $column['title'],
                                'options' => $options
                            ];

                            $Form1[] = [
                                'type'    => 'List',
                                'name'    => strval($column['id']),
                                'caption' => $column['title'],
                                'add'     => false,
                                'columns' => [
                                    [
                                        'caption' => 'ID',
                                        'name'    => 'id',
                                        'width'   => '150px'

                                    ],
                                    [
                                        'caption' => 'Name',
                                        'name'    => 'name',
                                        'width'   => 'auto'
                                    ]
                                ],
                                'values' => $values
                            ];
                            break;
                        case 'color':
                            $Values = json_decode($column['settings_str'], true);
                            $options = [];
                            foreach ($Values['labels'] as $Value) {
                                $options[] = [
                                    'caption' => $Value,
                                    'value'   => strval($Value)
                                ];
                            }
                            $Form[] = [
                                'type'    => 'Select',
                                'name'    => strval($column['id']),
                                'caption' => $column['title'],
                                'options' => $options
                            ];
                            break;
                        case 'date':
                            $Form[] = [
                                'type'    => 'SelectDate',
                                'name'    => strval($column['id']),
                                'caption' => $column['title'],
                            ];
                            break;
                        case 'text2':
                            $Form[] = [
                                'type'    => 'ValidationTextBox',
                                'name'    => 'txt.' . strval($column['id']),
                                'caption' => $column['title'],
                            ];
                            break;
                        case 'text':
                        case 'numeric':
                            if ($column['type'] == 'text') {
                                $type = 'ValidationTextBox';
                            }
                            if ($column['type'] == 'numeric') {
                                $type = 'NumberSpinner';
                            }
                            $FieldName = strval($column['id']);
                            $FieldNameVariable = 'var' . strval($column['id']);
                            $DynamicName = 'DYNAMIC' . strval($column['id']);
                            $Form[] = [
                                'type'    => 'Select',
                                'name'    => $DynamicName = 'DYNAMIC' . strval($column['id']),
                                'caption' => $this->Translate('Source of') . ' ' . $column['title'],
                                'value'   => true,
                                'options' => [
                                    [
                                        'value'   => false,
                                        'caption' => 'Constant Value',
                                    ],
                                    [
                                        'value'   => true,
                                        'caption' => 'Other Variable',
                                    ],
                                ],
                                'onChange' => [
                                    'IPS_UpdateFormField(\'' . $FieldName . '\',\'visible\',!$' . $DynamicName . ',$id);',
                                    'IPS_UpdateFormField(\'' . $FieldNameVariable . '\',\'visible\',$' . $DynamicName . ',$id);',
                                ]
                            ];
                            $Form[] = [
                                'type'    => $type,
                                'name'    => $FieldName,
                                'caption' => $column['title'],
                                'visible' => false,
                            ];
                            $Form[] = [
                                'type'    => 'SelectVariable',
                                'name'    => $FieldNameVariable,
                                'caption' => $column['title'],
                                'visible' => true,
                            ];
                            break;
                        default:
                            # code...
                            break;
                    }
            }
            //IPS_LogMessage('form', print_r($Form, true));
            return $Form;
        }

        public function addEditValue($IPS)
        {
            $columns = $this->getColumns();

            //print_r($columns);
            $keys = array_keys($IPS);
            print_r($_IPS);

            $variables = [];
            $columnVals = [];

            foreach ($columns as $key => $column) {
                if (in_array($column['id'], $keys)) {
                    switch ($column['type']) {
                        case 'name':
                            $variables['myItemName'] = $IPS[$column['id']];
                            $columnVals['name'] = $IPS[$column['id']];
                            break;
                        case 'multiple-person':
                            $columnVals[$column['id']] = strval($IPS[$column['id']]);
                            break;
                        case 'color':
                            $columnVals[$column['id']] = ['label'=> $IPS[$column['id']]];
                            break;
                        case 'date':

                            $datum = json_decode($IPS[$column['id']], true);
                            if (($datum['year'] == 0) || ($datum['month'] == 0) || ($datum['day'] == 0)) {
                                $datum = date('Y-m-d', time());
                            } else {
                                $datum = date('Y-m-d', strtotime($datum['year'] . '-' . $datum['month'] . '-' . $datum['day']));
                            }

                            $columnVals[$column['id']] = $datum;
                            break;
                        case 'text':
                        case 'numeric':
                            $FieldName = strval($column['id']);
                            $FieldNameVariable = 'var' . strval($column['id']);
                            $DynamicName = 'DYNAMIC' . strval($column['id']);

                            if ($_IPS[$DynamicName]) {
                                if ($_IPS[$FieldNameVariable] > 1) {
                                    $columnVals[$column['id']] = GetValue($_IPS[$FieldNameVariable]);
                                    echo GetValue($_IPS[$FieldNameVariable]);
                                } else {
                                    $columnVals[$column['id']] = '';
                                }
                            } else {
                                $columnVals[$column['id']] = $IPS[$column['id']];
                            }
                            break;
                        default:
                            # code...
                            break;
                    }
                }
            }


            if (!array_key_exists('element', $IPS)) {
                $query = 'mutation ($myItemName: String!, $columnVals: JSON!) { create_item (board_id:' . $this->ReadPropertyString('BoardID') . ', group_id:' . $this->ReadPropertyString('GroupID') . ' , item_name:$myItemName, column_values:$columnVals) { id } }';
            } else {
                $query = '
                mutation 
                    ($columnVals: JSON!) { 
                    change_multiple_column_values(
                        board_id: ' . $this->ReadPropertyString('BoardID') . '
                        item_id: '.$IPS['element'].'
                        column_values: $columnVals
                    )
                    { id }
                }';
            }


            
            $variables['columnVals'] = json_encode($columnVals);
            $this->sendQuery($query, $variables);
        }

        private function sendQuery(string $query, array $vars = [])
        {
            $Data = [];
            $Buffer = [];
            $Data['DataID'] = '{5537F0FF-18D6-B6D7-715E-F69B1A6225DF}';
            $Buffer['Command'] = 'sendRequest';
            $Buffer['Query'] = $query;
            $Buffer['vars'] = $vars;
            $Data['Buffer'] = $Buffer;
            $Data = json_encode($Data);
            $result = json_decode($this->SendDataToParent($Data), true);
            if (!$result) {
                return [];
            }
            $result = json_decode($result, true);
            return $result;
        }

        private function getColumns()
        {
            $query = '{
					boards(ids: ' . $this->ReadPropertyString('BoardID') . ') {
						columns {
							id
							title
							settings_str
							type
						  }
						}
					  }
					  ';
            $result = $this->sendQuery($query);
            return $result['data']['boards'][0]['columns'];
        }

        private function getGroupItems()
        {
            $query = '{
					boards(ids: ' . $this->ReadPropertyString('BoardID') . ') {
						groups(ids: "' . $this->ReadPropertyString('GroupID') . '") {
							items {
                                id
                                name
                            }
						  }
						}
					  }
					  ';
            $result = $this->sendQuery($query);
            IPS_LogMessage('result', print_r($result, true));
            return $result['data']['boards'][0]['groups'][0]['items'];
        }

        private function getUsers()
        {
            $query = '{
                users {
                  id
                  name
                }
              }
              ';
            $result = $this->sendQuery($query);
            //IPS_LogMessage('Users', print_r($result['data']['users'], true));
            return $result['data']['users'];
        }
    }