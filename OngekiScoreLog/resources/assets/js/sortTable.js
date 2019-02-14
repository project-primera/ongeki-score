var options = {
    valueNames: ['sort_title', 'sort_genre', 'sort_difficulty', 'sort_lv', 'sort_fb', 'sort_fc', 'sort_ab', 'sort_rank0', 'sort_rank1', 'sort_bs', 'sort_od', 'sort_ts', 'sort_nod', 'sort_nts', 'sort_rate', 'sort_update', 'sort_raw_battle_rank', 'sort_raw_technical_rank', 'sort_raw_lamp', 'sort_raw_difficulty']
};
var sortTable = new List('sort_table', options);

var filterList = [];
function AddFilterList(key, value){
    filterList.push({key: key, value: value});
}

function DeleteFilterList(key, value){
    index = filterList.findIndex(x => (x.key == key && x.value == value));
    if(index != -1){
        filterList.splice(index, 1);
    }
}

function SortTable(){
    var cnt = 0;
    var allCnt = 0;
    sortTable.filter(function(item) {
        ++allCnt;
        if(filterList.length == 0){
            return true;
        }

        var isVisible = false;
        $.each(filterList ,function(index,val){
            if(item.values()[val.key] == val.value){
                ++cnt;
                isVisible = true;
                return false;
            }
        });
        return isVisible;
    });
    if(filterList.length == 0){
        $('.filter_cases').html(allCnt);
    }else{
        $('.filter_cases').html(cnt);
    }
}

$('.filter_level_button').on('click',function(){
    var $text = $(this).text();
    if($(this).hasClass('is-info')){
        DeleteFilterList('sort_lv', $text);
        $(this).removeClass('is-info');

    } else {
        AddFilterList('sort_lv', $text);
        $(this).addClass('is-info');
    }
    SortTable();
});

$('.filter_difficulty_button').on('click',function(){
    var $text = $(this).text();
    if($(this).hasClass('is-info')){
        DeleteFilterList('sort_raw_difficulty', $text);
        $(this).removeClass('is-info');

    } else {
        AddFilterList('sort_raw_difficulty', $text);
        $(this).addClass('is-info');
    }
    console.log(filterList);
    SortTable();
});

$('.filter_battle_rank_button').on('click',function(){
    var $text = $(this).text();
    if($(this).hasClass('is-info')){
        DeleteFilterList('sort_raw_battle_rank', $text);
        $(this).removeClass('is-info');

    } else {
        AddFilterList('sort_raw_battle_rank', $text);
        $(this).addClass('is-info');
    }
    SortTable();
});

$('.filter_technical_rank_button').on('click',function(){
    var $text = $(this).text();
    if($(this).hasClass('is-info')){
        DeleteFilterList('sort_raw_technical_rank', $text);
        $(this).removeClass('is-info');

    } else {
        AddFilterList('sort_raw_technical_rank', $text);
        $(this).addClass('is-info');
    }
    SortTable();
});

class ClearLamp {
    constructor (){
        this.NoLamp = false;
        this.FB = false;
        this.FC = false;
        this.AB = false;
        this.state = "";
    }

    get() {
        if(this.FB && this.AB){
            this.state = "FB+FC+AB";
        }else if(this.FB && this.FC){
            this.state = "FB+FC";
        }else if(this.AB){
            this.state = "FC+AB";
        }else if(this.FC){
            this.state = "FC";
        }else if(this.FB){
            this.state = "FB";
        }else if(this.NoLamp){
            this.state = "-";
        }else{
            this.state = "";
            return false;
        }
        return this.state;
    }
}

clearLamp = new ClearLamp();

$('.filter_lamp_button').on('click',function(){
    var $text = $(this).text();

    if($text !== "NoLamp" && clearLamp.NoLamp){
        return;
    }

    if($(this).hasClass('is-info')){
        DeleteFilterList('sort_raw_lamp', clearLamp.get());
        clearLamp[$text] = false;
        $(this).removeClass('is-info');

    } else {
        DeleteFilterList('sort_raw_lamp', clearLamp.get());
        clearLamp[$text] = true;
        $(this).addClass('is-info');
    }

    if(!clearLamp.FC && clearLamp.AB){
        $('.filter_lamp_button.fc').addClass('is-info');
        clearLamp.FC = true;
    }else if(clearLamp.NoLamp || ($text === "NoLamp" && !clearLamp.NoLamp)){
        $('.filter_lamp_button.fb').removeClass('is-info');
        $('.filter_lamp_button.fc').removeClass('is-info');
        $('.filter_lamp_button.ab').removeClass('is-info');
        clearLamp.FB = false;
        clearLamp.FC = false;
        clearLamp.AB = false;
    }

    if(clearLamp.get() !== false){
        AddFilterList('sort_raw_lamp', clearLamp.get());
    }

    SortTable();
});

SortTable();