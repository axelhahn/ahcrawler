/* ======================================================================
 * Axels CRAWLER
 * initial functions for a page
 ====================================================================== */

function _getId(o, sPrefix=''){
    return o.id ? o.id : sPrefix + o.innerHTML.replace(/\W/g, '');
}

/**
 * scan h3 headers and draw on the top
 * @returns {undefined}
 */
function initDrawH3list() {
    var sHtml = '';
    // var sMenuid = '.sidebar-menu>li.active>span.submenu';
    var sMenuid = 'ul ul .pure-menu-item .pure-menu-link-active';
    var sH3id = false;
    var sMyId = 'ulh3list';
    var sActiveClass = 'pure-menu-link-active';

    // menu animation
    // $('#sbright').hide() && window.setTimeout("$('#sbright').slideDown(400)", 50);

    var i = 0;
    $("h3").each(function () {
        sH3id = _getId(this, 'h3');
        
        // if the headline has no id attribute yet then add one
        if (!this.id){
            this.id = sH3id;
        }
        if (this.id !== "h3menu") {
            i++;
            // sHtml += '<li class="pure-menu-item"><a href="#' + sH3id + '" class="scroll-link"><i class="fa fa-angle-right"></i> ' + this.innerHTML.replace(/(<([^>]+)>)/ig, "") + '</a></li>';
            sHtml += '<li class="pure-menu-item"><a href="#' 
                    + sH3id + '" class="scroll-link pure-menu-link'+(i===1 ? ' '+sActiveClass: '')+'">' 
                    + this.innerHTML.replace(/(<([^>]+)>)/ig, "") + '</a></li>';
        }

    });
    // console.log(sHtml);
    // console.log($(sMenuid));
    if (i < 2) {
        sHtml = '';
        // $(sMenuid).hide();
    } else {
    
        $(window).on('scroll', function() {
            $('h3').each(function() {
                if($(window).scrollTop() >= $(this).offset().top-400) {
                    // var id = $(this).attr('id');
                    sH3id = _getId(this, 'h3');
                    // console.log($('#'+sMyId+' a[href=#'+ sH3id +']'));
                    $('#'+sMyId+' a').removeClass(sActiveClass);
                    $('#'+sMyId+' a[href$='+ sH3id +']').addClass(sActiveClass);
                }
            });
        });    
        // $(sMenuid).append('<ul class="pure-menu-list" style="display: none;">' + sHtml + '</ul>');
        $('<ul class="pure-menu-list" id="'+sMyId+'" style="display: none;">' + sHtml + '</ul>').insertAfter($(sMenuid));
        $('#'+sMyId).slideDown(200);
    

    }
}

/**
 * initialize soft scrolling for links with css class "scroll-link"
 * @see http://css-tricks.com/snippets/jquery/smooth-scrolling/
 * @returns {undefined}
 */
function initSoftscroll() {
    $(function () {
        // $('a[href*=#]:not([href=#])').click(function() {
        $('a.scroll-link').click(function () {
            if (location.pathname.replace(/^\//, '') === this.pathname.replace(/^\//, '') && location.hostname === this.hostname) {
                var target = $(this.hash);
                target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
                if (target.length) {
                    $('html,body').animate({
                        scrollTop: target.offset().top - 130
                    }, 300);
                    return false;
                }
            }
        });
    });
}
function changeView(sClass2Hide, sIdToShow){
    
    $("."+sClass2Hide).hide();
    $("#"+sIdToShow).show();
    /*
    var oHide = document.querySelector("."+sClass2Hide); 
    var oShow = document.querySelector("#"+sIdToShow); 
    console.log('--- hide');
    console.log(oHide);
    console.log('--- show');
    console.log(oShow);
    if(oShow){
        if (oHide && oHide.length) {
            for (var i=0; i<oHide.length; i++){
                oHide[i].style.display="none";
            }
        }
        oShow.style.display="block";
    }
    */
}

// ----------------------------------------------------------------------
// modal dialog
// ----------------------------------------------------------------------


function modalDlg_setTitle(sTitle=''){
    document.getElementById('dialogtitle').innerHTML=sTitle ? sTitle : '&nbsp;';
}
function modalDlg_setContent(sHtml=''){
    document.getElementById('dialogcontent').innerHTML=sHtml;
}

function showModalUrl(sUrl, sTitle=''){
    showModal('<iframe src="'+sUrl+'" style="width: 100%; border: 0; height: 800px;"></iframe>', sTitle);
}
function showModal(sHtml='', sTitle=''){
    // dialogtitle
    modalDlg_setTitle(sTitle);
    modalDlg_setContent(sHtml)
    showModalWindow(true);
}
function showModalWindow(bVisible=1){
    var divOverlay=document.getElementById('overlay');
    var oBody=document.getElementById('content');
    var oNav=document.getElementById('navmain');
    if (bVisible){
        divOverlay.style.display='block';    
        oBody.style.filter="blur(0.2em)";
        oNav.style.filter="blur(0.2em)";
    } else {
        divOverlay.style.display='none';
        oBody.style.filter="";
        oNav.style.filter="";
    }
    return false;
}
function hideModal(){
    showModalWindow(false);
    return false;
}
/**
 * get css value by given property and selector
 * see https://stackoverflow.com/questions/16965515/how-to-get-a-style-attribute-from-a-css-class-by-javascript-jquery
 * 
 * @param {type} style
 * @param {type} selector
 * @param {type} sheet
 * @returns {.sheet@arr;cssRules.style}
 */
function getStyleRuleValue(style, selector, sheet) {
    var sheets = typeof sheet !== 'undefined' ? [sheet] : document.styleSheets;
    for (var i = 0, l = sheets.length; i < l; i++) {
        var sheet = sheets[i];
        try {
            if( !sheet.cssRules ) { continue; }
            for (var j = 0, k = sheet.cssRules.length; j < k; j++) {
                var rule = sheet.cssRules[j];
                if (rule.selectorText && rule.selectorText.split(',').indexOf(selector) !== -1) {
                    return rule.style[style];
                }
            }
        } catch (e) {
            if (e.name !== "SecurityError") {
               throw e;
            }
        }
    }
    return null;
}

/**
 * init page
 * @returns {undefined}
 */
function initPage() {
    initDrawH3list();
    initSoftscroll();
}


/**
 * handle tabs of a 2nd tab row
 * @param {type} id
 * @returns {Boolean}
 */
function showTab(id) {
    mydiv = '.subh2 ';
    $(mydiv + ' > h3').hide();
    $(mydiv + ' > .subh3').hide();
    $(mydiv + ' > ' + id).show();
    $(mydiv + ' > ' + id + ' + div.subh3').show();
    $(mydiv + ' li a').blur();
    return false;
}

