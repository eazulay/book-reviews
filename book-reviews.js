jQuery(document).ready(function( $ ) {
	$('.bkrv-excerpt-more').click(function( e ){
        const popId = e.target.dataset.popId;
        $('#'+popId).show(400);
    });
    $('.bkrv-close-popup').click(function( e ){
        $(e.target.parentNode.parentNode).hide(400);
    });
    $('.bkrv-pop-outer').click(function( e ){
        if (e.target !== this) // Don't capture click on children (bkrv-pop-inner)
            return;
        $(e.target.parentNode).hide(400);
    });
});