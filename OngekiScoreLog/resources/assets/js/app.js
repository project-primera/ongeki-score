
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

// require('./bootstrap');

// window.Vue = require('vue');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

// Vue.component('example-component', require('./components/ExampleComponent.vue'));
/*
const app = new Vue({
    el: '#app'
});
*/

var options = {
    valueNames: ['sort_title', 'sort_genre', 'sort_difficulty', 'sort_lv', 'sort_fb', 'sort_fc', 'sort_ab', 'sort_rank0', 'sort_rank1', 'sort_bs', 'sort_od', 'sort_ts', 'sort_update', 'sort_raw_battle_rank', 'sort_raw_technical_rank', 'sort_raw_lamp']
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
    console.log(filterList);
    var cnt = 0;
    sortTable.filter(function(item) {
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
    console.log(cnt + "件表示");
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

$('.filter_lamp_button').on('click',function(){
    var $text = $(this).text();
    if($(this).hasClass('is-info')){
        DeleteFilterList('sort_raw_lamp', $text);
        $(this).removeClass('is-info');

    } else {
        AddFilterList('sort_raw_lamp', $text);
        $(this).addClass('is-info');
    }
    SortTable();
});



/*
$('.filter_level_button').on('click',function(){
    var $text = $(this).text();
    if($(this).hasClass('is-info')){
        sortTable.filter();
        $(this).removeClass('is-info');

    } else {
        sortTable.filter(function(item) {
        return (item.values().sort_lv == $text);
      });
      $(this).addClass('is-info');
    }
});
*/