var options = {
    valueNames: ['sort_title', 'sort_genre', 'sort_difficulty', 'sort_lv', 'sort_lamp', 'sort_fb', 'sort_fc', 'sort_ab', 'sort_rank0', 'sort_rank1', 'sort_bs', 'sort_od', 'sort_topod', 'sort_ts', 'sort_nod', 'sort_nts', 'sort_rate', 'sort_update', 'sort_raw_battle_rank', 'sort_raw_technical_rank', 'sort_raw_lamp', 'sort_raw_difficulty', 'sort_key1', 'sort_key2', 'sort_key3']
};
var sortTable = new List('sort_table', options);

var filters = new Map([
    ["sort_lv", new Set()],
    ["sort_raw_difficulty", new Set()],
    ["sort_raw_battle_rank", new Set()],
    ["sort_raw_technical_rank", new Set()],
]);

var filterClearLamp = new Set();

function SortTable(){
    var cnt = 0;
    sortTable.filter(function(item) {
        for (const [key, filter] of filters) {
            if (filter.size > 0) {
                if (!filter.has(item.values()[key])) {
                    return false;
                }
            }
        }

        if (filterClearLamp.size > 0) {
            const lamps = new Set(item.values()["sort_raw_lamp"].split('+'));
            for (const filter of filterClearLamp) {
                if (!lamps.has(filter)) {
                    return false;
                }
            }
        }

        ++cnt;
        return true;
    });

    $('.filter_cases').html(cnt);
}

$('.filter_level_button').on('click',function(){
    var $text = $(this).text();
    var filter = filters.get('sort_lv');
    if($(this).hasClass('is-info')){
        filter.delete($text);
        $(this).removeClass('is-info');

    } else {
        filter.add($text);
        $(this).addClass('is-info');
    }
    SortTable();
});

$('.filter_difficulty_button').on('click',function(){
    var $text = $(this).text();
    var filter = filters.get('sort_raw_difficulty');
    if($(this).hasClass('is-info')){
        filter.delete($text);
        $(this).removeClass('is-info');

    } else {
        filter.add($text);
        $(this).addClass('is-info');
    }
    SortTable();
});

$('.filter_battle_rank_button').on('click',function(){
    var $text = $(this).text();
    var filter = filters.get('sort_raw_battle_rank');
    if($(this).hasClass('is-info')){
        filter.delete($text);
        $(this).removeClass('is-info');

    } else {
        filter.add($text);
        $(this).addClass('is-info');
    }
    SortTable();
});

$('.filter_technical_rank_button').on('click',function(){
    var $text = $(this).text();
    var filter = filters.get('sort_raw_technical_rank');
    if($(this).hasClass('is-info')){
        filter.delete($text);
        $(this).removeClass('is-info');

    } else {
        filter.add($text);
        $(this).addClass('is-info');
    }
    SortTable();
});

var noLamp = false;

$('.filter_lamp_button').on('click',function(){
    var $text = $(this).text();

    if ($text === 'NoLamp') {
        $text = '-';
    }

    if ($text !== '-' && noLamp) {
        $('.filter_lamp_button.nolamp').removeClass('is-info');
        noLamp = false;
        filterClearLamp.delete('-');

    } else if ($text === '-' && !noLamp) {
        $('.filter_lamp_button.fb').removeClass('is-info');
        $('.filter_lamp_button.fc').removeClass('is-info');
        $('.filter_lamp_button.ab').removeClass('is-info');
        noLamp = true;
        filterClearLamp.clear();
    }

    if($(this).hasClass('is-info')){
        filterClearLamp.delete($text);
        $(this).removeClass('is-info');

    } else {
        filterClearLamp.add($text);
        $(this).addClass('is-info');
    }

    SortTable();
});

SortTable();
