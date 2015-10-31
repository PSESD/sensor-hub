<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */
namespace canis\sensorHub\components\sensors;

use Yii;
use canis\broadcaster\eventTypes\EventType;

class SourceInstance extends Instance
{
	static public function collectEventTypes()
    {
    	$events = [];
        $events['source_created'] = [
            'name' => 'Sensor Source Created',
            'descriptorString' => 'Sensor source \'{{ source.name }}\' was created'
        ];
        $events['source_updated'] = [
            'name' => 'Sensor Source Updated',
            'descriptorString' => 'Sensor source \'{{ source.name }}\' was updated'
        ];
        $events['source_checked'] = [
            'name' => 'Sensor Source Checked',
            'descriptorString' => 'Sensor source \'{{ source.name }}\' was checked',
            'priority' => EventType::PRIORITY_LOW
        ];
    	return $events;
    }

    public function validateSetup($scenario = 'create')
    {
    	if (!parent::validateSetup($scenario)) {
    		return false;
    	}

    	$data = static::fetchData($this->attributes['url'], $this->attributes['apiKey']);
    	if (!$data || !empty($data['error'])) {
    		if (!empty($data['responseCode']) && $data['responseCode'] === 404) {
    			$this->setupErrors['url'] = 'Data source was not found at this URL';
    		} elseif (!empty($data['responseCode']) && $data['responseCode'] === 403) {
    			$this->setupErrors['apiKey'] = 'API Key was rejected';
    		} else {
    			$this->setupErrors['url'] = 'An unknown error occurred while validating the data source';
    		}
    		return;
    	}

    	
    }


    public function setupFields()
	{
		$fields = [];
		$fields['url'] = [
			'label' => 'Source URL',
			'type' => 'text',
			'required' => true,
			'full' => true,
			'on' => ['create', 'update']
		];
		$fields['apiKey'] = [
			'label' => 'API Key',
			'type' => 'text',
			'required' => true,
			'full' => true,
			'on' => ['create', 'update']
		];

		$intervals = [];
		$intervals['+1 minute'] = '1 Minute';
		$intervals['+5 minutes'] = '5 Minutes';
		$intervals['+30 minutes'] = '30 Minutes';
		$intervals['+1 hour'] = '1 Hour';
		$intervals['+3 hours'] = '3 Hours';
		$intervals['+6 hours'] = '6 Hours';
		$intervals['+12 hours'] = '12 Hours';
		$intervals['+1 day'] = '1 Day';
		$intervals['+1 week'] = '1 Week';
		$intervals['+1 month'] = '1 Month';

		$fields['checkInterval'] = [
			'label' => 'Check Interval',
			'type' => 'select',
			'default' => '+1 minute',
			'options' => $intervals,
			'required' => true,
			'full' => false,
			'on' => ['create', 'update']
		];
		return $fields;
	}

    public function getPackage()
    {
    	$package = [];

    	return $package;
    }
}
?>