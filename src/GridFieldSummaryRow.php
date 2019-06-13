<?php

namespace SamTheJarvis\GridFieldSummaryRow;

use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use SilverStripe\View\Requirements;
use SilverStripe\ORM\FieldType\DBInt;
use SilverStripe\ORM\FieldType\DBText;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBFloat;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\FieldType\DBDecimal;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_HTMLProvider;

class GridFieldSummaryRow implements GridField_HTMLProvider
{
    use Configurable;

    /**
     * List of data types that will result in a sum.
     * 
     * *NOTE* This class will also check for instances of these classes 
     * 
     * @var array
     */
    private static $allowed_classes = [
        DBFloat::class,
        DBDecimal::class,
        DBInt::class,
        DBMoney::class
    ];

    public function __construct($fragment = 'footer') 
    {
        $this->fragment = $fragment;
    }

    /**
     * Is the provided field on our allowed list?
     * 
     * @return boolean
     */
    protected function isFieldAllowed(DBField $field)
    {
        foreach ($this->config()->allowed_classes as $class) {
            if (get_class($field) == $class || is_subclass_of($field, $class)) {
                return true;
            }
        }

        return false;
    }


    /**
     * Render this row
     * 
     * @param GridField $gridField The current gridfield
     * 
     * @return array
     */
    public function getHTMLFragments($gridField) 
    {
        Requirements::css("samthejarvis/gridfield-summary-row:client/css/summary-row.css");

        $columns = $gridField->getColumns();
        $list = $gridField->getList();
        $summary_values = ArrayList::create();
        $singleton = Injector::inst()->get($list->dataClass, true);
        $db = $singleton->config()->db;

        foreach ($columns as $column) {
            $field = $singleton->dbObject($column);
            $summary_value = "";

            if (empty($field) || !$this->isFieldAllowed($field)) {
                $obj = DBText::create("Summary");
            } else {
                $obj = clone $field;
                if ($db[$column] == "Money") {
                    $summary_value = $list->sum($column."Amount");
                } else {
                    $summary_value = $list->sum($column);
                }
            }

            $obj->setValue($summary_value);

            $summary_values->push(
                ArrayData::create(["Value" => $obj])
            );
        }

        $data = ArrayData::create(['SummaryValues' => $summary_values]);

        return [$this->fragment => $data->renderWith(__CLASS__)];
    }
}