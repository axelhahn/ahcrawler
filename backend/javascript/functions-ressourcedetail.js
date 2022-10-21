/* ======================================================================
 * Axels CRAWLER
 * functions for page "ressourcedetail"
 ====================================================================== */
 
// on start: 
window.addEventListener('load', function() {

    // deselect OK status buttons on linked resources
    $('#div-toggle-3 a.http-code-ok').removeClass('text-on-markedelement http-code-2xx http-code-ok');

    // hide ok items on linked resources
    $('#div-toggle-3 div.group-2xx-ok').hide();
});
