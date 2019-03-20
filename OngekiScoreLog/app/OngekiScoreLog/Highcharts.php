<?php
namespace app\OngekiScoreLog;

class Highcharts
{
    private $id = "container";
    private $type = "line";
    private $zoomType = null;
    private $title = "";
    private $subTitle = "";

    // Axis
    private $xAxis = [];
    private $yAxis = [];

    // Data
    private $series = [];

    // Options
    private $isPlotOptionsDataLabelsEnabled = false;
    private $isPlotOptionsEnableMouseTracking = true;


    public function id(string $s)
    {
        $this->id = $s;
        return $this;
    }
    public function type(string $s)
    {
        $this->type = $s;
        return $this;
    }
    public function zoomType(string $s)
    {
        $this->zoomType = $s;
        return $this;
    }
    public function title(string $t)
    {
        $this->title = $t;
        return $this;
    }
    public function subTitle(string $t)
    {
        $this->subTitle = $t;
        return $this;
    }

    public function addXAxis(string $title, array $data = [], bool $isRight = false, int $width = null)
    {
        $c = new \stdClass();
        $c->title = $title;
        $c->categories = $data;
        $c->isRight = $isRight;
        $c->width = $width;
        $this->xAxis[] = $c;
        return $this;
    }
    public function addYAxis(string $title, array $data = [], bool $isRight = false, int $width = null)
    {
        $c = new \stdClass();
        $c->title = $title;
        $c->categories = $data;
        $c->isRight = $isRight;
        $c->width = $width;
        $this->yAxis[] = $c;
        return $this;
    }

    public function addSeries(string $name, array $a, int $axis = 0)
    {
        $c = new \stdClass();
        $c->name = $name;
        $c->data = $a;
        $c->axis = $axis;
        $this->series[] = $c;
        return $this;
    }
    public function isPlotOptionsDataLabelsEnabled(bool $b)
    {
        $this->isPlotOptionsDataLabelsEnabled = $b;
        return $this;
    }
    public function isPlotOptionsEnableMouseTracking(bool $b)
    {
        $this->isPlotOptionsEnableMouseTracking = $b;
        return $this;
    }

    public function __toString()
    {
        $str = "<script>Highcharts.chart('$this->id',{chart:{type:'$this->type',";
        if (!is_null($this->zoomType)) {
            $str .= "zoomType: '$this->zoomType',";
        }
        $str .= "},title:{text:'$this->title'},subtitle:{text: '$this->subTitle'},";

        $str .= "xAxis:[";
        foreach ($this->xAxis as $value) {
            $str .= "{title:{text:'$value->title',},categories:" . json_encode($value->categories) . ",opposite: " . var_export($value->isRight, true) . ",";
            if(!is_null($value->width)){
                $str .= "gridLineWidth:$value->width,";
            }
            $str .= "},";

        }
        $str .= "],";

        $str .= "yAxis:[";
        foreach ($this->yAxis as $value) {
            $str .= "{title:{text:'$value->title',},categories:" . json_encode($value->categories) . ",opposite: " . var_export($value->isRight, true) . ",";
            if(!is_null($value->width)){
                $str .= "gridLineWidth:$value->width,";
            }
            $str .= "},";
        }
        $str .= "],";

        $str .= "series:[";
        foreach ($this->series as $value) {
            $str .= "{name:'$value->name',data:" . json_encode($value->data) . ",yAxis:$value->axis,},";
        }
        $str .= "],";

        $str .= "plotOptions: {
            $this->type: {
                dataLabels: {
                    enabled:" . var_export($this->isPlotOptionsDataLabelsEnabled, true) . "
                },
                enableMouseTracking: " . var_export($this->isPlotOptionsEnableMouseTracking, true) . "
            }
        },});</script>";

        return $str;
    }
}

