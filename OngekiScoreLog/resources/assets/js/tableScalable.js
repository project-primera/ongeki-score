$('.table_scale_change').on('click',function(){
    var scale = $(this).text();
    console.log(scale);
    $('.scalable').css('zoom', scale);
});