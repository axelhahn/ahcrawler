/* ======================================================================
 * Axels CRAWLER
 * functions for page "ressourcedetail"
 ====================================================================== */
 
// on start: 
window.addEventListener('load', function() {

    // deselect OK status buttons
    $('a.http-code-ok').removeClass('text-on-markedelement http-code-2xx http-code-ok');

    // hide ok resources
    $('div.group-2xx-ok').hide();
});
