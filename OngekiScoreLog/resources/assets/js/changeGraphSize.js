let hideAxis = false;

if($(window).width() <= 769){
    hideAxis = true;
}

function process(){
    if(hideAxis){
        $('#graph').css('display','none');
        $('#sp-graph').css('display','block');
    }else{
        $('#graph').css('display','block');
        $('#sp-graph').css('display','none');
    }
}

$('.change-graph-size').on('click', function(){
    hideAxis = !hideAxis;
    process();
});

process();