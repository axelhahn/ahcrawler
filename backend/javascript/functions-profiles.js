/* ======================================================================
 * Axels CRAWLER
 * functions for page "profiles"
 ====================================================================== */

var sIdInput='profileimageinserter', 
    sIdOutput='profileimagedata', 
    sIdDeleteButton='profileimagedelete',
    sIdFileselect='profileimagefile'
    ;

/**
 * initialize div with contenteditable=true to fetch imagae data from pasted 
 * image; it adds an "on paste" event handler.
 * @param {string} sIdInput   id of input div
 * @param {string} sIdOutput  id of output field (<input type=hidden ..>)
 * @returns {undefined}
 */
function initInsertImage(){
    var oIn=document.getElementById(sIdInput);
    var oOut=document.getElementById(sIdOutput);
    var oDelButton=document.getElementById(sIdDeleteButton);
    var oFileselect=document.getElementById(sIdFileselect);

    if (oIn && oOut){
        
        // source: https://stackoverflow.com/questions/1391278/contenteditable-change-events
        $('body').on('focus', '[contenteditable]', function() {
            const $this = $(this);
            $this.data('before', $this.html());
        // }).on('blur keyup paste input', '[contenteditable]', function() {
        }).on('paste keyup', '[contenteditable]', function() {
            const $this = $(this);
            if ($this.data('before') !== $this.html()) {
                $this.data('before', $this.html());
                $this.trigger('change');
            }
        });

        $(oIn).change(function(){
            // get src attribute value from first image
            var data=$('#' + this.id + ' > img') ? $('#' + this.id + ' > img').attr("src") : '';
            if(data && oOut.value!==data){
                oOut.value=data;
                oIn.innerHTML='['+data.length+']<br><img src="'+data+'">';
                $(oDelButton).attr("disabled", 'disabled');
            }
        });
    }
    if(oDelButton && oOut){
        $(oDelButton).click(function(){
            oOut.value='DELETE';
            $(oIn).hide();
            $(document.getElementById('myimagediv')).css('opacity', 0.3);
            
            return false;
        });
    }
    $(oFileselect).change(function(){
        $(oIn).css('opacity', 0.3);
    });
    
}
// ----------------------------------------------------------------------
// init
// ----------------------------------------------------------------------
initInsertImage(); // page profiles
