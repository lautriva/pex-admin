$(function () 
{
    $('.switch').click(function () {
        $(this).children(':checkbox').click();
    });
});