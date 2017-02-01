<?php

class GridFieldSummaryRow implements GridField_HTMLProvider {
    
    protected $displayFields = array();

	public function __construct($fragment = 'footer') {
		$this->fragment = $fragment;
	}
	
	public function setDisplayFields($fields) {
	    if(!is_array($fields)) {
	        throw new InvalidArgumentException('
				Arguments passed to GridFieldSummaryRow::setDisplayFields() must be an array');
	    }
	    $this->displayFields = $fields;
	    return $this;
	}
	
	public function getDisplayFields($gridField) {
	    if(!$this->displayFields) {
	        return singleton($gridField->getModelClass())->summaryFields();
	    }
	    return $this->displayFields;
	}
	

	function getHTMLFragments($gridField) {

		Requirements::css("gridfield-summary-row/css/summary-row.css");

		$columns = $gridField->getColumns();
		$list = $gridField->getManipulatedList();

		$summary_values = new ArrayList();
		
		foreach($columns as $column) {
		    
		    if (!$this->displayFields)
		    {
    			$db = singleton($list->dataClass)->db();
    			if(singleton($list->dataClass)->hasField($column)){
    			    if($db[$column] == "Money") {
    			        $summary_value = $list->sum($column."Amount");
    			    } else {
    			        $summary_value = $list->sum($column);
    			    }
    	        }
    	        else
    	        {
    	        	$summary_value = "";
    	        }
		    }
		    else 
		    {
		        if (key_exists($column, $this->displayFields)) {
		            // calc sum
		            $summary_value = 0;
		            foreach ($list as $record) {
		                $field = $gridField->getDataFieldValue($record, $column);
		                if (is_a($field, 'DBField')) {
		                    $summary_value += floatval($field->getValue());
		                } else {
		                    $summary_value += floatval($field);
		                }
		            }
		            // format
		            $formatClass = $this->displayFields[$column];
		            $obj = $formatClass::create();
		            $obj->setValue($summary_value);
		            $summary_value = $obj->Nice();
		        } else {
		            $summary_value = "";
		        }
		    }

	        $summary_values->push(new ArrayData(array(
				"Value" => $summary_value
			)));
			
		}
		
		$data = new ArrayData(array(
			'SummaryValues' => $summary_values
		));

		return array(
			$this->fragment => $data->renderWith('GridFieldSummaryRow')
		);
	}
}