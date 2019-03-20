<?php
namespace app\OngekiScoreLog;

class Highcharts{
    private $id = "container";
    private $type = "line";
    private $zoomType = null;
    private $title = "";
    private $subTitle = "";
    private $xAxisTitle = "";
    private $yAxisTitle = "";
    private $xAxisCategories = [];
    private $yAxisCategories = [];
    private $series = [];

    // options
    private $isPlotOptionsDataLabelsEnabled = false;
    private $isPlotOptionsEnableMouseTracking = true;

    public function id(string $s){
        $this->id = $s;
        return $this;
    }
    public function type(string $s){
        $this->type = $s;
        return $this;
    }
    public function zoomType(string $s){
        $this->zoomType = $s;
        return $this;
    }
    public function title(string $t){
        $this->title = $t;
        return $this;
    }
    public function subTitle(string $t){
        $this->subTitle = $t;
        return $this;
    }
    public function xAxisTitle(string $t){
        $this->xAxisTitle = $t;
        return $this;
    }
    public function yAxisTitle(string $t){
        $this->yAxisTitle = $t;
        return $this;
    }
    public function xAxisCategories(array $a){
        $this->xAxisCategories = $a;
        return $this;
    }
    public function yAxisCategories(array $a){
        $this->yAxisCategories = $a;
        return $this;
    }
    public function addSeries(string $name, array $a){
        $c = new \stdClass();
        $c->name = $name;
        $c->data = $a;
        $this->series[] = $c;
        return $this;
    }
    public function isPlotOptionsDataLabelsEnabled(bool $b){
        $this->isPlotOptionsDataLabelsEnabled = $b;
        return $this;
    }
    public function isPlotOptionsEnableMouseTracking(bool $b){
        $this->isPlotOptionsEnableMouseTracking = $b;
        return $this;
    }

    public function __toString()
    {
        $str = "<script>Highcharts.chart('$this->id',{chart:{type:'$this->type',";

        if(!is_null($this->zoomType)){
            $str .= "zoomType: '$this->zoomType',";
        }
            
        $str .= "},title:{text:'$this->title'},subtitle:{text: '$this->subTitle'},xAxis:{title:{text:'$this->xAxisTitle',},categories:" . json_encode($this->xAxisCategories) . "},yAxis:{title:{text:'$this->yAxisTitle',},categories:" . json_encode($this->yAxisCategories) . "},series:[";

        foreach ($this->series as $value) {
            $str .= "{name:'$value->name',data:" . json_encode($value->data) . "},";
        }
            
        $str .= "],
                    plotOptions: {
                        $this->type: {
                            dataLabels: {
                                enabled:" . var_export($this->isPlotOptionsDataLabelsEnabled, TRUE) . "
                            },
                            enableMouseTracking: " . var_export($this->isPlotOptionsEnableMouseTracking, TRUE) . "
                        }
                    },
                });
            </script>
        ";

        return $str;
    }
}