/* ======================================================================
 * Axels CRAWLER
 * functions for page "httpheaderchecks"
 ====================================================================== */

 // id of the http header table
var sTableId="httpheader-table";

// default css classes of the buttons in the filter bar
var sBtnCss="pure-button button-filter";

// ----------------------------------------------------------------------
//  FUNCTIONS
// ----------------------------------------------------------------------

/**
 * reset css classes all filter buttons
 */
function resetFilterBtn(){
    $('.filterbar a').each(function() {
        $(this).attr('class', sBtnCss);
    });
}

/**
 * filter table by current tag
 * @param {string} sTag  class name of the tag to filter
 */
function filterTable(sTag) {
    var oTable = document.getElementById(sTableId);
    var oRows = oTable.getElementsByTagName("tr");
    for (var i = 0; i < oRows.length; i++) {
        var oRow = oRows[i];
        var trClasses=oRow.getAttribute("class");

        if (trClasses){
            if (trClasses.indexOf(sTag)<0) {
                oRow.style.display = "none";
            } else {
                oRow.style.display = "";
            }
        }
    }
}


// ----------------------------------------------------------------------
// init
// ----------------------------------------------------------------------

// on start: 
window.addEventListener('load', function() {

    $('.filterbar a').each(function() {
        $(this).click(function() {
            var sTag=this.dataset.tagname;

            var bDoEnable=!$(this).hasClass(sTag);
            resetFilterBtn();
            filterTable('');

            if(bDoEnable){
                filterTable(sTag);
                $(this).addClass(sTag);
                $(this).addClass("active");
            }

            return false;
        })
    });

});

// ----------------------------------------------------------------------
