<?php

class GridFieldSummaryRow implements GridField_HTMLProvider
{

    public function __construct($fragment = 'footer')
    {
        $this->fragment = $fragment;
    }

    public function getHTMLFragments($gridField)
    {
        Requirements::css("gridfield-summary-row/css/summary-row.css");

        $columns = $gridField->getColumns();
        $list = $gridField->getList();

        $summary_values = new ArrayList();

        foreach ($columns as $column) {
            $db = singleton($list->dataClass)->db();

            if (singleton($list->dataClass)->hasField($column)) {
                if ($db[$column] == "Money") {
                    $summary_value = $list->sum($column."Amount");
                } else {
                    $summary_value = $list->sum($column);
                }
            } else {
                $summary_value = "";
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
