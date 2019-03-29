<article class="box">
    <div class="accordion">
        <input id="tab-aggregate-total" type="checkbox" name="tabs">
        <label for="tab-aggregate-total">トータルスコア</label>
        <div class="accordion-content">
            <div class="table_wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>&nbsp;</th>
                            <th>Battle Score</th>
                            <th>Over Damage</th>
                            <th>Technical Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($stat['difficulty'] as $key => $value)
                            <tr>
                                <td>{{$key}}</td>
                                <td>{{number_format($value["battle"])}}</td>
                                <td>{{number_format($value["overDamage"], 2)}}%</td>
                                <td>{{number_format($value["technical"])}}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td>Total</td>
                            <td>{{number_format($stat['difficulty']["Basic"]["battle"] + $stat['difficulty']["Advanced"]["battle"] + $stat['difficulty']["Expert"]["battle"] + $stat['difficulty']["Master"]["battle"] + $stat['difficulty']["Lunatic"]["battle"])}}</td>
                            <td>{{number_format($stat['difficulty']["Basic"]["overDamage"] + $stat['difficulty']["Advanced"]["overDamage"] + $stat['difficulty']["Expert"]["overDamage"] + $stat['difficulty']["Master"]["overDamage"] + $stat['difficulty']["Lunatic"]["overDamage"], 2)}}%</td>
                            <td>{{number_format($stat['difficulty']["Basic"]["technical"] + $stat['difficulty']["Advanced"]["technical"] + $stat['difficulty']["Expert"]["technical"] + $stat['difficulty']["Master"]["technical"] + $stat['difficulty']["Lunatic"]["technical"])}}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="accordion">
        <input id="tab-aggregate-def" type="checkbox" name="tabs">
        <label for="tab-aggregate-def">難易度別達成度</label>
        <div class="accordion-content">
            <div class="table_wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>&nbsp;</th>
                            <th>P</th>
                            <th>SSS+</th>
                            <th>SSS</th>
                            <th>SS</th>
                            <th>S</th>
                            <th>AAA</th>
                            <th>AA</th>
                            <th>A</th>
                            <th>under A</th>
                            <th>FB</th>
                            <th>FC</th>
                            <th>AB</th>
                            <th>極+</th>
                            <th>極</th>
                            <th>秀</th>
                            <th>優</th>
                            <th>良</th>
                            <th>可</th>
                            <th>不可</th>
                            <th>NoPlay</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($stat['difficulty'] as $key => $value)
                            @component('layouts/components/user/song_aggregate_record')
                                @slot('key')
                                    {{$key}}
                                @endslot
                                @slot('p')
                                    {{isset($value['P']) ? $value['P'] : 0}}
                                @endslot
                                @slot('ssss')
                                    {{isset($value['SSS+']) ? $value['SSS+'] : 0}}
                                @endslot
                                @slot('sss')
                                    {{isset($value['SSS']) ? $value['SSS'] : 0}}
                                @endslot
                                @slot('ss')
                                    {{isset($value['SS']) ? $value['SS'] : 0}}
                                @endslot
                                @slot('s')
                                    {{isset($value['S']) ? $value['S'] : 0}}
                                @endslot
                                @slot('aaa')
                                    {{isset($value['AAA']) ? $value['AAA'] : 0}}
                                @endslot
                                @slot('aa')
                                {{isset($value['AA']) ? $value['AA'] : 0}}
                                @endslot
                                @slot('a')
                                {{isset($value['A']) ? $value['A'] : 0}}
                                @endslot
                                @slot('b')
                                {{isset($value['B']) ? $value['B'] : 0}}
                                @endslot
                                @slot('np')
                                {{isset($value['NP']) ? $value['NP'] : 0}}
                                @endslot
                                @slot('fb')
                                {{isset($value['fb']) ? $value['fb'] : 0}}
                                @endslot
                                @slot('fc')
                                {{isset($value['fc']) ? $value['fc'] : 0}}
                                @endslot
                                @slot('ab')
                                {{isset($value['ab']) ? $value['ab'] : 0}}
                                @endslot
                                @slot('b0')
                                {{isset($value['極+']) ? $value['極+'] : 0}}
                                @endslot
                                @slot('b1')
                                {{isset($value['極']) ? $value['極'] : 0}}
                                @endslot
                                @slot('b2')
                                {{isset($value['秀']) ? $value['秀'] : 0}}
                                @endslot
                                @slot('b3')
                                {{isset($value['優']) ? $value['優'] : 0}}
                                @endslot
                                @slot('b4')
                                {{isset($value['良']) ? $value['良'] : 0}}
                                @endslot
                                @slot('b5')
                                {{isset($value['可']) ? $value['可'] : 0}}
                                @endslot
                                @slot('b6')
                                {{isset($value['不可']) ? $value['不可'] : 0}}
                                @endslot
                            @endcomponent    
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="accordion">
        <input id="tab-aggregate-lv" type="checkbox" name="tabs">
        <label for="tab-aggregate-lv">レベル別達成度</label>
        <div class="accordion-content">
            <div class="table_wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>&nbsp;</th>
                            <th>P</th>
                            <th>SSS+</th>
                            <th>SSS</th>
                            <th>SS</th>
                            <th>S</th>
                            <th>AAA</th>
                            <th>AA</th>
                            <th>A</th>
                            <th>under A</th>
                            <th>FB</th>
                            <th>FC</th>
                            <th>AB</th>
                            <th>極+</th>
                            <th>極</th>
                            <th>秀</th>
                            <th>優</th>
                            <th>良</th>
                            <th>可</th>
                            <th>不可</th>
                            <th>NoPlay</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($stat['level'] as $key => $value)
                            @component('layouts/components/user/song_aggregate_record')
                                @slot('key')
                                    {{$key}}
                                @endslot
                                @slot('p')
                                    {{isset($value['P']) ? $value['P'] : 0}}
                                @endslot
                                @slot('ssss')
                                    {{isset($value['SSS+']) ? $value['SSS+'] : 0}}
                                @endslot
                                @slot('sss')
                                    {{isset($value['SSS']) ? $value['SSS'] : 0}}
                                @endslot
                                @slot('ss')
                                    {{isset($value['SS']) ? $value['SS'] : 0}}
                                @endslot
                                @slot('s')
                                    {{isset($value['S']) ? $value['S'] : 0}}
                                @endslot
                                @slot('aaa')
                                    {{isset($value['AAA']) ? $value['AAA'] : 0}}
                                @endslot
                                @slot('aa')
                                {{isset($value['AA']) ? $value['AA'] : 0}}
                                @endslot
                                @slot('a')
                                {{isset($value['A']) ? $value['A'] : 0}}
                                @endslot
                                @slot('b')
                                {{isset($value['B']) ? $value['B'] : 0}}
                                @endslot
                                @slot('np')
                                {{isset($value['NP']) ? $value['NP'] : 0}}
                                @endslot
                                @slot('fb')
                                {{isset($value['fb']) ? $value['fb'] : 0}}
                                @endslot
                                @slot('fc')
                                {{isset($value['fc']) ? $value['fc'] : 0}}
                                @endslot
                                @slot('ab')
                                {{isset($value['ab']) ? $value['ab'] : 0}}
                                @endslot
                                @slot('b0')
                                {{isset($value['極+']) ? $value['極+'] : 0}}
                                @endslot
                                @slot('b1')
                                {{isset($value['極']) ? $value['極'] : 0}}
                                @endslot
                                @slot('b2')
                                {{isset($value['秀']) ? $value['秀'] : 0}}
                                @endslot
                                @slot('b3')
                                {{isset($value['優']) ? $value['優'] : 0}}
                                @endslot
                                @slot('b4')
                                {{isset($value['良']) ? $value['良'] : 0}}
                                @endslot
                                @slot('b5')
                                {{isset($value['可']) ? $value['可'] : 0}}
                                @endslot
                                @slot('b6')
                                {{isset($value['不可']) ? $value['不可'] : 0}}
                                @endslot
                            @endcomponent    
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="accordion">
        <input id="tab-aggregate-average" type="checkbox" name="tabs">
        <label for="tab-aggregate-average">レベル別平均テクニカルスコア</label>
        <div class="accordion-content">
            <div class="table_wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>&nbsp;</th>
                            <th>Basic</th>
                            <th>Advanced</th>
                            <th>Expert</th>
                            <th>Master</th>
                            <th>Lunatic</th>
                            <th>合計</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($stat['average'] as $key => $value)
                        <tr>
                            <td>{{$key}}</td>
                            <td>{{array_key_exists("Basic", $value) ? number_format(floor($value['Basic']['score'] / $value['Basic']['count'])) : "-"}}</td>
                            <td>{{array_key_exists("Advanced", $value) ? number_format(floor($value['Advanced']['score'] / $value['Advanced']['count'])) : "-"}}</td>
                            <td>{{array_key_exists("Expert", $value) ? number_format(floor($value['Expert']['score'] / $value['Expert']['count'])) : "-"}}</td>
                            <td>{{array_key_exists("Master", $value) ? number_format(floor($value['Master']['score'] / $value['Master']['count'])) : "-"}}</td>
                            <td>{{array_key_exists("Lunatic", $value) ? number_format(floor($value['Lunatic']['score'] / $value['Lunatic']['count'])) : "-"}}</td>
                            <td>{{array_key_exists("total", $value) ? number_format(floor($value['total']['score'] / $value['total']['count'])) : 0}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="accordion">
        <input id="tab-aggregate-average-exist" type="checkbox" name="tabs">
        <label for="tab-aggregate-average-exist">レベル別平均テクニカルスコア(未プレイを除く)</label>
        <div class="accordion-content">
            <div class="table_wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>&nbsp;</th>
                            <th>Basic</th>
                            <th>Advanced</th>
                            <th>Expert</th>
                            <th>Master</th>
                            <th>Lunatic</th>
                            <th>合計</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($stat['averageExist'] as $key => $value)
                        <tr>
                            <td>{{$key}}</td>
                            <td>{{array_key_exists("Basic", $value) ? number_format(floor($value['Basic']['score'] / $value['Basic']['count'])) : "-"}}</td>
                            <td>{{array_key_exists("Advanced", $value) ? number_format(floor($value['Advanced']['score'] / $value['Advanced']['count'])) : "-"}}</td>
                            <td>{{array_key_exists("Expert", $value) ? number_format(floor($value['Expert']['score'] / $value['Expert']['count'])) : "-"}}</td>
                            <td>{{array_key_exists("Master", $value) ? number_format(floor($value['Master']['score'] / $value['Master']['count'])) : "-"}}</td>
                            <td>{{array_key_exists("Lunatic", $value) ? number_format(floor($value['Lunatic']['score'] / $value['Lunatic']['count'])) : "-"}}</td>
                            <td>{{array_key_exists("total", $value) ? number_format(floor($value['total']['score'] / $value['total']['count'])) : 0}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</article>