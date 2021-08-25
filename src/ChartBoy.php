<?php
namespace ChartBoy;

function linkStyle(string $location)
{
    // This function soley links to a charts.css file,
    // whether it be from a pre-done CDN, or a local file.
    // 'shorthands' for jsdelivr and unpkg are included
    // for simple deployment if that's what you would do anyways.
    $link;
    switch ($location) {
        case "jsdelivr":
            $link = "https://cdn.jsdelivr.net/npm/charts.css/dist/charts.min.css";
            break;
        case "unpkg":
            $link = "https://unpkg.com/charts.css/dist/charts.min.css";
            break;
        default:
            $link = $location;
    }
    return "<link rel='stylesheet' href='{$link}'>";
}

class ChartBoy
{
    /* Variables */
    public $type;
    // Types: bar, column, area, line
    // TODO: radial, pie, radar, polar
    public $data; // Array of data in the ["label" => numericValue] where "label" is optional.
    public $caption; // The 'caption' of the chart. Optional.
    private $min;
    private $max; // The min/max of the chart is calculated automatically.
    private $startPoint;

    // charts.css variables
    protected $setting;

    public function __construct(array $data, string $type = "bar", $caption = null)
    {
        $this->data = $data;
        $this->setType($type);
        $this->setCaption($caption);

        $this->min = min($data);
        $this->max = max($data);

        // charts.css settings
        $this->setting = [
            // A sea of falses
            "show-heading" => false,
            "multiple" => false,
            "hide-data" => false,
            "show-data-on-hover" => false,
            "reverse" => false,
            "show-labels" => false,
            "labels-align-start" => false,
            "labels-align-center" => false,
            "labels-align-end" => false,
            "show-primary-axis" => false,
            "show-n-secondary-axes" => false,
            "show-data-axes" => false,
            "data-spacing-n" => false,
            "datasets-spacing-n" => false,
            "reverse-data" => false,
            "reverse-datasets" => false,
            "stacked" => false,
        ];
    }

    // Logic

    private function makeScale($n)
    {
        // Calculate a percentage on a scale of 100.
        // This is to hide exact values from the public,
        // in case such information is sensitive.
        return round(($n / $this->max), 3);
    }

    /* Setters */

    public function setCaption($caption, $display = true)
    {
        if (!isset($caption)) {return;}
        $this->caption = $caption;
        // If someone sets the caption, assume they want it to be seen
        $this->setting["show-heading"] = $display;
    }

    public function setType($type)
    {
        $this->type = $type;
        // Special case handling
        switch ($type) {
            case "bar":
            case "column":
                $this->startPoint = false;
                break;
            case "area":
            case "line":
                $this->startPoint = true;
                $this->setting["datasets-spacing-n"] = false;
                $this->setting["reverse-datasets"] = false;
                $this->setting["stacked"] = false;
                break;
        }
    }

    // charts.css
    public function hideData($bool = true)
    {$this->hideData = $bool;}

    public function inputData($array)
    {
        // Takes in a new input data array and updates calculations regarding it.
        $this->data = $array;
        $this->min = min($array);
        $this->max = max($array);
    }

    // Getters

    public function renderChart()
    {
        print("\n\t<table class='charts-css {$this->type}'>");
        if (!empty($this->caption)) {print("\n\t<caption>{$this->caption}</caption>");}
        print("\n\t<tbody>\n");
        foreach ($this->data as $item => $value) {
            // Loop through each data item

            $current = $this->makeScale($value);
            $next = $this->makeScale(next($this->data));

            // Does this chart require a starting point?
            if ($this->startPoint === true) {
                $start = "--start:{$current};";
                $size = "--size:{$next}";
                if ($item === array_key_last($this->data)) {break;}
            } else {
                $start = null;
                $size = "--size:{$current}";
            }

            print("\t\t<tr><td style='{$start}{$size}'><span class='data'>{$item}</span></td></tr>\n");
        }
        print("\t</tbody>\n\t");
        print("\t</table>\n\t");
    }
}
