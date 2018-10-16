<article class="box">
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
                    <th>NP</th>
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
                </tr>
            </thead>
            <tbody>
                @foreach ($stat as $key => $value)
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
</article>