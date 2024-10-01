/* ======================================================================
 * Axels CRAWLER
 * initial functions for a page
 ====================================================================== */

/**
 * helper function for initDrawH3list: get id of a given headline object
 * or build one by its text
 * @param {object} o
 * @param {type} sPrefix
 * @returns {String|@var;sPrefix}
 */
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
    // var sMenuid = 'ul ul .pure-menu-item .pure-menu-link-active';
    var sMenuid = 'ul .pure-menu-link-active';
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
    if (i < 3) {
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
        $('<ul class="pure-menu-list" id="'+sMyId+'" style="display: none;">' + sHtml + '</ul>').insertAfter($(sMenuid).last());
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
}

/**
 * toggle extended options in settings and in profile
 * @returns {undefined}
 */
function initExtendedView(){
    var bShow=localStorage.getItem('crawler_showExtended');
    $('.btn-extend').hide();
    if(bShow==1){
        $('.btn-extend-on').show();
        $('.btn-extend-off').hide();
        $('.hintextended').hide();
        $('.extended').slideDown();
    } else {
        $('.extended').hide();
        $('.hintextended').show();
        $('.btn-extend-on').hide();
        $('.btn-extend-off').show();
    }
}
function toggleExtendedView(){
    var bShow=localStorage.getItem('crawler_showExtended');
    bShow=bShow/1 ? 0 : 1;
    localStorage.setItem('crawler_showExtended', bShow);
    initExtendedView();
}

function initToggleAreas(){
    $('.div-toggle-head').each(function () {
        
        var id = $(this).attr('id');
        var link = $(this.children[0]);
        var target = $(this.nextSibling);
        var varname='crawler_toggle-'+id+location.search;
        
        var bIsOpen=localStorage.getItem(varname);

        if(bIsOpen==0){
            $(link).removeClass('open');
            $(target).hide();
        } 
        if(bIsOpen==1){
            $(link).addClass('open');
            $(target).show();
        } 

        // add onclick event
        $(this).click(function () {
            return toggleAreas(this);
        });
    });
}
function toggleAreas(oDiv){

    var id = $(oDiv).attr('id');
    var varname='crawler_toggle-'+id+location.search;
    var link = $(oDiv.children[0]);
    var target = $(oDiv.nextSibling);

    var bIsOpen=$(link).hasClass('open');
    bIsOpen=bIsOpen/1 ? 0 : 1;
    if(bIsOpen==1){
        $(link).addClass('open');
        $(target).slideDown();
    } else {
        $(link).removeClass('open');
        $(target).slideUp();
    }
        
    localStorage.setItem(varname, bIsOpen);
    return false;
}

/**
 * write value of one input to another as base64 encoded string
 * @param {type} sIdInput
 * @param {type} sIdOutput
 * @returns {undefined}
 */
function initBase64Input(sIdInput, sIdOutput){
    var oIn=document.getElementById(sIdInput);
    var oOut=document.getElementById(sIdOutput);
    if (oIn && oOut){
        oIn=document.getElementById(sIdInput);
        oIn.onchange = function() {
          oOut.value=btoa(oIn.value);
          // console.log("IN: " + oIn.value + " | OUT: "+ oOut.value);
        };
    }
}

// ----------------------------------------------------------------------
// datatables
// ----------------------------------------------------------------------

function initDatatables(){
    var aDtSettings={
        // cookies
        'tblSavedCookies':{   'lengthMenu':[[50,-1]], 'bStateSave': true},
        
        // htmlchecks
        'tableCrawlerErrors':{ 'aaSorting':[[1,'asc']], 'bStateSave': true},
        'tableShortTitles':{   'aaSorting':[[1,'asc']], 'bStateSave': true},
        'tableShortDescr':{    'aaSorting':[[1,'asc']], 'bStateSave': true},
        'tableShortKeywords':{ 'aaSorting':[[1,'asc']], 'bStateSave': true},
        'tableLongLoad':{      'aaSorting':[[1,'desc']], 'bStateSave': true},
        'tableLargePages':{    'aaSorting':[[1,'desc']], 'bStateSave': true},
        
        // httpstatuscode
        'httpstatuscdodes':{   'lengthMenu':[[-1]], 'bStateSave': true},
        
        // langedit
        'tblLangtexts':{       'lengthMenu':[[-1]], 'bStateSave': true},
        
        // ressources output list
        // 'tableResData'

        // searchindexstatus
        // 'tableAlldata'

        // sslechecks
        // 'tableNonhttpsitems'
        
        '_default':{'lengthMenu':[[10,25,50,100,-1]]}
    };

    $('.datatable').each(function () {
        var aOptions=aDtSettings[this.id] ? aDtSettings[this.id] : aDtSettings['_default'];

        if(aOptions['lengthMenu']){
            if(!aOptions['lengthMenu'][1]){
                aOptions['lengthMenu'][1]=[];
                for (var i=0; i<aOptions['lengthMenu'][0].length; i++){
                    aOptions['lengthMenu'][1][i]=aOptions['lengthMenu'][0][i] === -1 ? '...' : aOptions['lengthMenu'][0][i];
                }
            }
        }
        
        if(this.id){
            $('#'+this.id).DataTable( aOptions );
        }
        
    });
    // $('$sSelector').DataTable( ". json_encode($aOptions)." );} );
}

// ----------------------------------------------------------------------
// select project - aka nav 2
// ----------------------------------------------------------------------

// source: http://blog.crondesign.com/2010/05/javascript-auto-expand-forms-select-box.html
function toggleSelectBox(selbox){ 
 if(selbox.size>1){//HIDE:
  selbox.size=1;
  selbox.style.position='relative';
 }else{//SHOW:
  selbox.size = selbox.options.length;
  selbox.style.position='absolute';
  // selbox.style.height='auto';
 }
  selbox.style.height='auto';
}

function initSelectProject(){
    var sId='selectProject';
    
    $('#'+sId+' select').change(function(){
        location.href=this.value;
    });
    /*
     * TODO
     * 
    $('#'+sId+' select').mouseover(function(){
        toggleSelectBox(this);
    });
    $('#'+sId+' select').mouseout(function(){
        toggleSelectBox(this);
    });
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
    modalDlg_setContent(sHtml);
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

// ----------------------------------------------------------------------
// css rules
// ----------------------------------------------------------------------

/**
 * get css value by given property and selector
 * see used code base at https://stackoverflow.com/questions/16965515/how-to-get-a-style-attribute-from-a-css-class-by-javascript-jquery
 * 
 * @param {type} style
 * @param {type} selector
 * @param {type} sheet
 * @returns {.sheet@arr;cssRules.style}
 */
function getStyleRuleValue(style, selector, sheet) {
    var oReturn=null;
    var sheets = typeof sheet !== 'undefined' ? [sheet] : document.styleSheets;
    for (var i = 0, l = sheets.length; i < l; i++) {
        var sheet = sheets[i];
        try {
            if( !sheet.cssRules ) { continue; }
            for (var j = 0, k = sheet.cssRules.length; j < k; j++) {
                var rule = sheet.cssRules[j];
                if (rule.selectorText && rule.selectorText.split(',').indexOf(selector) !== -1) {
                    oReturn=rule.style[style];
                }
            }
        } catch (e) {
            if (e.name !== "SecurityError") {
               throw e;
            }
        }
    }
    return oReturn;
}

// ----------------------------------------------------------------------
// status display of running crawler
// ----------------------------------------------------------------------

var indexer_was_started=false;
/**
 * 
 * @param {type} sUrl
 * @returns {undefined}
 */
function updateStatus(sUrl){
    var oDiv=document.getElementById('divStatus');
    fetch(sUrl, {})
        .then(res => res.text())
        .then(data => {
            oDiv.innerHTML=data;
            oDiv.style.display=data ? 'block' : 'none';
            if(oDiv.innerHTML){
                indexer_was_started=true;
                $('.actions-crawler .running').show();
                $('.actions-crawler .stopped').hide();
            } else {
                $('.actions-crawler .running').hide();
                $('.actions-crawler .stopped').show();
                if(indexer_was_started){
                    location.reload();
                    indexer_was_started=false;
                }
            }
            var iTimer=data ? 1 : 5;
            window.setTimeout('updateStatus("'+sUrl+'")', 1000*iTimer);
        });
        
}

/**
 * prepare textarea: add double click event that adds the placeholder 
 * value if the value is empty (= to copy the default value and make it
 * editable)
 * @returns {undefined}
 */
function initTextareas(){
    $('.pure-control-group textarea').dblclick(function(){
        if ($(this).attr('placeholder') && $(this).val()==='' ){
            $(this).val($(this).attr('placeholder'));
        }
    }); 
}

// ----------------------------------------------------------------------
// init
// ----------------------------------------------------------------------

window.addEventListener('load', function() {

    initDrawH3list();
    initSoftscroll();
    initExtendedView();
    initToggleAreas();
    initDatatables();
    initSelectProject();
    
    initBase64Input('e_url', 'urlbase64');

    initTextareas();

    // detect public frontend or backend
    var sMyPath=document.location.pathname.replace(/(.*\/)[a-z0-0\.]*$/, '$1');
    if(sMyPath.indexOf('/backend/')>=0 && sMyPath.indexOf('/backend/') > sMyPath.length-10){
        // console.log('OK - backend');
        var sUrl=document.location.href.replace(/\?.*$/, '').replace(/(.*\/)[a-z0-0\.]*$/, '$1');
        sUrl+='/get.php?action=getstatus';
        updateStatus(sUrl);
    } else {
        // console.log('NO - backend');
    }
});
