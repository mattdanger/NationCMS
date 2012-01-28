// -----------------------------------------------------------------------------------
//
// LightboxEx 2.02,
// this is a modified version of Lightbox 2.02 by Lokesh Dhakar - BIG credit to him for
// creating such good script and making it available to the public.
//
// Modified by Oleksander Havrylyuk, ahavrilu@yahoo.com, http://alexphotostudio.blogspot.com
// Sound bridge by Gustavo Ribeiro Amigo, Copyright (c) 2006

/*
Updates:
8/16/2006 - Fixed issue with PNG support in IE when play and close button graphics where not visible.

*/

// List of new features:
// 	- display set of images in slideshow mode
//	- stop/resume playing of slideshow
//	- play music during slideshow playback
//	- customize slideshow parameters
//	- PNG image support for Internet Explorer
//	- new graphics and look
//
// -----------------------------------------------------------------------------------
//	Lightbox v2.02 by Lokesh Dhakar - http://www.huddletogether.com
//	3/31/06
//
//	For more information on this script, visit:
//	http://huddletogether.com/projects/lightbox2/
//
//	Licensed under the Creative Commons Attribution 2.5 License - 
//	http://creativecommons.org/licenses/by/2.5/
//	
//	Credit also due to those who have helped, inspired, and made their code available to the public.
//	Including: Scott Upton(uptonic.com), Peter-Paul Koch(quirksmode.org), Thomas Fuchs(mir.aculo.us), 
//	and others.
// -----------------------------------------------------------------------------------

// -----------------------------------------------------------------------------------
//
//	Default Configuration Parameters
//  Changing those parameters will effect global behavior of LightboxEx
//
var resizeSpeed 				= 8;	// controls the speed of the image resizing (1=slowest and 10=fastest)
var borderSize 					= 10;	//if you adjust the padding in the CSS, you will need to update this variable

// ------- Slideshow options ---------------------------------------------------------

var slideShowWidth 				= 604;	// -1 - size slideshow window based on each image				
var slideShowHeight 			= 454;	// -1 - size slideshow window based on each image
var navigationBarWidth			= -1; 	// -1 - size navigation bar based on width of each image
var slideshow 					= 1;   	// 1 slideshow auto start. Set 0 if you want to disable starting slideshow  automaticaly
var foreverLoop 				= 0;	// Set 0 if want to stop on the last image or Set it to 1 for Infinite loop feature
var loopInterval 				= 4000;	// image swap interval in miliseconds
var loopMusic					= true; //loops music if it is shorter then slideshow

// ------- Images path -----------------------------------------------------------------
var homeURL 					= "http://nationskateboarding.com/"; //URL to your installation directory

var fileLoadingImage 			= "http://nationskateboarding.com/images/lightbox/loading.gif";		
var fileBottomNavCloseImage 	= homeURL + "images/lightbox/close.png";
var SlideShowStartImage 		= homeURL + "images/lightbox/play.png";
var SlideShowStopImage 			= homeURL + "images/lightbox/stop.png";
var MusicOnImage 				= homeURL + "images/lightbox/music_on.png";
var MusicOffImage 				= homeURL + "images/lightbox/music_off.png";
var replayImage					= homeURL + "images/lightbox/replay.png";
var blankSrc 					= homeURL + "images/lightbox/blank.gif";

var imageDataContainerOpacity   = 0.6;

var resize 						= 1;	// Set 0 to disable auto-resizing

var SoundBridgeSWF 				= homeURL + "js/SoundBridge.swf";

// ------- End of Configuration Parameters --------------------------------------------------------------------






















//
//	Global Variables
//

var imageArray = new Array;
var activeImage;

if(resizeSpeed > 10){ resizeSpeed = 10;}
if(resizeSpeed < 1){ resizeSpeed = 1;}
resizeDuration = (11 - resizeSpeed) * 0.15;

var so = null;
var objSlideShowImage;
var objLightboxImage;
var objImageDataContainer;
var objSpeakerImage;
var objBottomNavCloseImage;

var keyPressed = false;
var slideshowMusic = null;
var firstTime = 1;
var closeWindow = false;

var saveSlideshow;
var saveForeverLoop;
var saveLoopInterval;
var saveSlideShowWidth;
var saveSlideShowHeight;
var saveLoopMusic;
var saveNavigationBarWidth;

// -----------------------------------------------------------------------------------
var timeStart = 0;

//PNG image support
var isPNGSupported = !(/MSIE ((5\.5)|(6\.0))/.test(navigator.userAgent) && (navigator.platform == "Win32"));
var realSrc;

function propertyChanged() {
	
	if (isPNGSupported) return;

	var pName = event.propertyName;

	//check if it is the right property
	if (pName != "src") return;

	//if not set to blank
	if (!new RegExp(blankSrc).test(event.srcElement.src)) {
		fixPNGImage(event.srcElement);
	}
}

function fixPNGImage(element) {

	if (isPNGSupported) return;

	var src = element.src;

	// check for real change
	if (src == realSrc && /\.png$/i.test(src)) {
	  element.src = blankSrc;
	  return;
	}

	if ( ! new RegExp(blankSrc).test(src)) {
	  //save old src
	  realSrc = src;
	}

	// check for png
	if (/\.png$/i.test(realSrc)) {
	  element.src = blankSrc;
	  element.runtimeStyle.filter = "progid:DXImageTransform.Microsoft." +
					"AlphaImageLoader(src='" + src + "',sizingMethod='scale')";
	}
	else {
	  // remove filter
	  element.runtimeStyle.filter = "";
	}
}


// Opens a new slideshow window. You must have a <slideshow>.html file present, which contains links
// to you images. slideshow - is the name of your set in lightbox[slideshow] declaration. They must match.
//
function openSlideShow(slideshowName) {
  var wW = 800;
  var wH = 620;
  var X = (screen.width - wW) /2;
  var Y = (screen.height - wH)/3;

  var pageURL = homeURL + slideshowName + '.html';
  var slideshowWindow = window.open(pageURL, '_blank',
			 'width=' + wW + ',height=' + wH + 
			 ',top=' + Y + ',left=' + X + ',screenX=' + X + ',screenY=' + Y +
			 ',toolbar=0,scrollbars=0,resizable=0,location=0,status=0');

	if(slideshowWindow==null || typeof(slideshowWindow)=="undefined"){
		alert("Can't open a slideshow window.\nPlease, try again when the page gets reloaded.");
		location.reload();		
	}

  //if (!slideshowWindow.opener) slideshowWindow.opener = self;
}



function startSlideshow() {
	closeWindow = true;
	init();
	var anchors = document.getElementsByTagName('a');
	if (anchors.length != 0) {
		var anchor = anchors[0];
		//window.setTimeout(function(a) {myLightbox.start(a);}, 1000, anchor);				
		myLightbox.start(anchor);
	}
}


//
//	Additional methods for Element added by SU, Couloir
//	- further additions by Lokesh Dhakar (huddletogether.com)
//
Object.extend(Element, {
	getWidth: function(element) {
	   	element = $(element);
	   	return element.offsetWidth; 
	},
	setWidth: function(element,w) {
	   	element = $(element);
    	element.style.width = w +"px";
	},
	setHeight: function(element,h) {
   		element = $(element);
    	element.style.height = h +"px";
	},
	setTop: function(element,t) {
	   	element = $(element);
    	element.style.top = t +"px";
	},
	setSrc: function(element,src) {
    	element = $(element);
    	element.src = src; 
	},
	setHref: function(element,href) {
    	element = $(element);
    	element.href = href; 
	},
	setInnerHTML: function(element,content) {
		element = $(element);
		element.innerHTML = content;
	}
});

// -----------------------------------------------------------------------------------

// http://4umi.com/web/javascript/array.htm
//	Extending built-in Array object
//	- array.removeDuplicates()
//	- array.empty()
//
Array.prototype.removeDuplicates = function () {
	for(i = 1; i < this.length; i++){
		if(this[i][0] == this[i-1][0]){
			this.splice(i,1);
		}
	}
}

// -----------------------------------------------------------------------------------

Array.prototype.empty = function () {
	for(i = 0; i <= this.length; i++){
		this.shift();
	}
}
    Sound.trace = function(value, isJavascript) {
        // tracing disabled
    } 
    
// -----------------------------------------------------------------------------------

// Music Player Class
   function Player () {
      this.paused = true;
      this.stoped = true;

      this.options = new Object();
      this.options.swfLocation = SoundBridgeSWF;
      this.sound = new Sound(this.options);
		
      this.position = 0;
      this.frequency = 1000;
      this.isLoaded = false;
      this.duration = 0;
      this.bytesTotal = 0;
      this.callback = this.registerCallback();
   }
    
    
   Player.prototype.onTimerEvent = function() {
	  var isDurationOk = false
      if(!this.paused) {

          var position = this.sound.getPosition();
          if(!position) position = 0;
          if(position != this.position && position != 0) {
             this.onPlaying();
          } else {
             this.onBuffering();
          }
          this.position = position;          
          
          var duration = 0;                   
          duration = this.sound.getDuration();
          
          if(!duration) duration = 0;
          if(duration == this.duration && duration != 0) {
             isDurationOk = true;             
          }
          
          this.duration = duration;
          var progress = position/duration;
          if(isDurationOk) {
              this.setProgressBar(progress);
          }
          
          var isBytesTotalOk = false;
          
          var bytesTotal = this.sound.getBytesTotal();
          if(bytesTotal == this.bytesTotal) {
              isBytesTotalOk = true;    
          }
          this.bytesTotal = bytesTotal;
          
          if(isBytesTotalOk) {
              var loaded =  this.sound.getBytesLoaded()/bytesTotal;
              this.setLoadedBar(loaded);
          }
          
          if (progress == 1 && duration != 0 && position != 0) {
            this.onSoundComplete();
          }
          
      }
   }
      
   Player.prototype.registerCallback = function() {
      return setInterval(this.onTimerEvent.bind(this), this.frequency);
   }
      
   Player.prototype.clearCallback = function() {
   		clearInterval(this.callback);
   		this.callback = null;
	}
	
   Player.prototype.setProgressBar = function(progress) {
        if(!progress) progress = 0;        
   }
	
   Player.prototype.setLoadedBar = function(loaded) {
         if(!loaded) loaded = 0;
   }   
      
   Player.prototype.onPlaying = function() {   
   		//Element.show('caption');
   		//Element.setInnerHTML( 'caption', this.sound.getId3());
   }
   
   Player.prototype.onPause = function() {
   }   
   
   Player.prototype.onBuffering = function() {      
   }   

   Player.prototype.onSoundComplete = function() {
      if(!this.paused) {
      	if (loopMusic) {
        	this.onForward();
        }
      }
   }

   Player.prototype.onForward = function() {
         this.position = 0;
         this.duration = 0;
         this.sound.start(this.duration/1000, 1);
         this.sound.stop();
         this.loadTrack(this.track);
         this.stoped = true;
         this.setProgressBar(0);
         this.setLoadedBar(0);
         if(!this.paused) {            
            this.paused = true;
            this.play();
         }
   }   

   Player.prototype.fadeOut = function() {
	   	  for (var i=this.sound.getVolume()-1; i>=0; i--) {   	  	
	       	this.sound.setVolume(i);
	       	//pause(1);
	      }            	
   }   
   
   Player.prototype.fadeIn = function() {
	   	  for (var i=1; i <= 100; i++) {   	  	
	       	this.sound.setVolume(i);
	       	//pause(1);
	      }            	
   }
   
   Player.prototype.toggleVolume = function() {
   		if (this.paused) return;
   		var volume = this.sound.getVolume();
   		if (volume == 0) {
   			this.fadeIn();
   			//this.sound.setVolume(100);   			
   			objSpeakerImage.setAttribute('src', MusicOnImage);
   		}
   	  if (volume == 100) {
   	  	this.fadeOut();
   	  	//this.sound.setVolume(0);
   	  	objSpeakerImage.setAttribute('src', MusicOffImage);
   	  }
   }
   
   Player.prototype.play = function() {   
      if(this.paused) {
         this.paused = false;
         if(this.stoped) {
             this.sound.loadSound(this.track, true);
         }
         this.sound.start(this.position/1000, 1);         
         this.stoped = false;
      } else {
         this.position = this.sound.getPosition();
         this.sound.stop();         
         this.paused = true;
         this.onPause();
      }
   }

   Player.prototype.stop = function() {
       if (! this.paused) {
		   //fade out
		   for (var i=this.sound.getVolume()-1; i>=0; i--) {   	  	
		       	this.sound.setVolume(i);
		       	pause(1);
		   }            	       	
       }
       this.paused = true;
       this.stoped = true;
       this.position = 0;
       this.duration = 0;
              
       this.sound.start(this.duration/1000, 1);       
       this.sound.stop();         
   }   
   
  
   Player.prototype.loadTrack = function(track) {
      this.track = track;
   }        

var player;

// -----------------------------------------------------------------------------------
//
//	Lightbox Class Declaration
//	- initialize()
//	- start()
//	- changeImage()
//	- resizeImageContainer()
//	- showImage()
//	- updateDetails()
//	- updateNav()
//	- enableKeyboardNav()
//	- disableKeyboardNav()
//	- keyboardNavAction()
//	- preloadNeighborImages()
//	- end()
//
//	Structuring of code inspired by Scott Upton (http://www.uptonic.com/)
//
var Lightbox = Class.create();

Lightbox.prototype = {
	
	// initialize()
	// Constructor runs on completion of the DOM loading. Loops through anchor tags looking for 
	// 'lightbox' references and applies onclick events to appropriate links. The 2nd section of
	// the function inserts html at the bottom of the page which is used to display the shadow 
	// overlay and the image container.
	//
	initialize: function() {	
		this.dimmingOut = false;
		
		if (!document.getElementsByTagName){ return; }
		var anchors = document.getElementsByTagName('a');

		// loop through all anchor tags
		for (var i=0; i<anchors.length; i++){
			var anchor = anchors[i];
			
			var relAttribute = String(anchor.getAttribute('rel'));
			
			// use the string.match() method to catch 'lightbox' references in the rel attribute
			if (anchor.getAttribute('href') && (relAttribute.toLowerCase().match('lightbox'))){
				anchor.onclick = function () {myLightbox.start(this); return false;}
			}
		}

		// The rest of this code inserts html at the bottom of the page that looks similar to this:
		//
		//	<div id="overlay"></div>
		//	<div id="lightbox">
		//		<div id="outerImageContainer">
		//			<div id="imageContainer">
		//				<img id="lightboxImage">
		//				<div style="" id="hoverNav">
		//					<a href="#" id="prevLink"></a>
		//					<a href="#" id="nextLink"></a>
		//				</div>
		//				<div id="loading">
		//					<a href="#" id="loadingLink">
		//						<img src="images/loading.gif">
		//					</a>
		//				</div>
		//			</div>
		//		</div>
		//		<div id="imageDataContainer">
		//			<div id="imageData">
		//				<div id="imageDetails">
		//					<span id="caption"></span>
		//					<span id="numberDisplay"></span>
		//				</div>
		//				<div id="bottomNav">
		//					<a href="#" id="bottomNavClose">
		//						<img src="images/close.gif">
		//					</a>
		//				</div>
		//			</div>
		//		</div>
		//	</div>


		var objBody = document.getElementsByTagName("body").item(0);
		
		var objOverlay = document.createElement("div");
		objOverlay.setAttribute('id','overlay');
		objOverlay.style.display = 'none';
		objOverlay.onclick = function() { 			
			if (!closeWindow) {
				 myLightbox.end();	 
			}
			return false; 
		}
		objBody.appendChild(objOverlay);
		

		var objLightbox = document.createElement("div");
		objLightbox.setAttribute('id','lightbox');
		objLightbox.style.display = 'none';
		objBody.appendChild(objLightbox);

		var objOuterImageContainer = document.createElement("div");
		objOuterImageContainer.setAttribute('id','outerImageContainer');
		objLightbox.appendChild(objOuterImageContainer);

		var objImageContainer = document.createElement("div");
		objImageContainer.setAttribute('id','imageContainer');
		objOuterImageContainer.appendChild(objImageContainer);

		objLightboxImage = document.createElement("img");
		objLightboxImage.setAttribute('id','lightboxImage');
		objLightboxImage.setAttribute('width',''); //needed for proper resizing
		objLightboxImage.setAttribute('height',''); //needed for proper resizing
		objImageContainer.appendChild(objLightboxImage);
	
		var objHoverNav = document.createElement("div");
		objHoverNav.setAttribute('id','hoverNav');
		objImageContainer.appendChild(objHoverNav);
	
		var objPrevLink = document.createElement("a");
		objPrevLink.setAttribute('id','prevLink');
		objPrevLink.setAttribute('href','#');
		objPrevLink.setAttribute('onFocus', 'if (this.blur) this.blur()');
		objHoverNav.appendChild(objPrevLink);
		
		var objNextLink = document.createElement("a");
		objNextLink.setAttribute('id','nextLink');
		objNextLink.setAttribute('href','#');
		objNextLink.setAttribute('onFocus', 'if (this.blur) this.blur()');
		objHoverNav.appendChild(objNextLink);

		//Loading 
		var objLoading = document.createElement("div");
		objLoading.setAttribute('id','loading');
		objImageContainer.appendChild(objLoading);
	
		var objLoadingLink = document.createElement("a");
		objLoadingLink.setAttribute('id','loadingLink');
		objLoadingLink.setAttribute('href','#');
		objLoadingLink.setAttribute('onFocus', 'if (this.blur) this.blur()');
		objLoadingLink.onclick = function() { 
			myLightbox.end(); 
			if (closeWindow) window.close(); 
			return false; 
		}
		objLoading.appendChild(objLoadingLink);
	
		var objLoadingImage = document.createElement("img");
		objLoadingImage.setAttribute('src', fileLoadingImage);
		objLoadingLink.appendChild(objLoadingImage);	
		
		//Replay
		var objReplay = document.createElement("div");
		objReplay.setAttribute('id','replay');
		objImageContainer.appendChild(objReplay);
	
		var objReplayLink = document.createElement("a");
		objReplayLink.setAttribute('id','replayLink');
		objReplayLink.setAttribute('href','#');
		objReplayLink.setAttribute('onFocus', 'if (this.blur) this.blur()');
		objReplayLink.onclick = function() { myLightbox.toggleSlideShow(); return false; }	
		objReplay.appendChild(objReplayLink);
	
		var objReplayImage = document.createElement("img");
		objReplayImage.onpropertychange = function() { propertyChanged(); return false; };
		objReplayImage.setAttribute('src', replayImage);
		objReplayLink.appendChild(objReplayImage);	
		objReplayImage.setAttribute('src', replayImage);
		Element.hide('replay');
		
		//Spacer
		var objSpacer = document.createElement("div");
		objSpacer.setAttribute('id', 'spacer');
		objSpacer.className = 'spacer';
		objLightbox.appendChild(objSpacer);

		//Bottom bar - ImageDataContainer
		objImageDataContainer = document.createElement("div");
		objImageDataContainer.setAttribute('id','imageDataContainer');
		objImageDataContainer.className = 'clearfix';
			
 		objImageDataContainer.onmouseover = function(ev) {
 			if (ev == undefined) {
 				ev=event;
 			}
			if (checkMouseEnter(this, ev)) {
 				myLightbox.lightUpNavigationBar();
 			}
			
			return false;
		}
		objImageDataContainer.onmouseout = function(ev) {		
 			if (ev == undefined) {
 				ev=event;
 			}			
			if (checkMouseLeave(this, ev)) {
				myLightbox.dimDownNavigationBar();
			}
						
			return false;
		}
		
		objLightbox.appendChild(objImageDataContainer);

		var objImageData = document.createElement("div");
		objImageData.setAttribute('id','imageData');
		objImageDataContainer.appendChild(objImageData);
	
		var objImageDetails = document.createElement("div");
		objImageDetails.setAttribute('id','imageDetails');
		objImageData.appendChild(objImageDetails);
	
		var objCaption = document.createElement("span");
		objCaption.setAttribute('id','caption');
		objImageDetails.appendChild(objCaption);
	
		var objNumberDisplay = document.createElement("span");
		objNumberDisplay.setAttribute('id','numberDisplay');
		objImageDetails.appendChild(objNumberDisplay);

		//Bottom Navigation -------------------------------------------------------------	
		var objBottomNav = document.createElement("div");
		objBottomNav.setAttribute('id','bottomNav');
		objImageData.appendChild(objBottomNav);

		//Close link
		var objBottomNavCloseLink = document.createElement("a");
		objBottomNavCloseLink.setAttribute('id','bottomNavClose');
		objBottomNavCloseLink.setAttribute('href','#');
		objBottomNavCloseLink.setAttribute('onFocus', 'if (this.blur) this.blur()');
		objBottomNavCloseLink.onclick = function() { 
			myLightbox.end(); 
			if (closeWindow) window.close();
			return false; 
		}
		objBottomNav.appendChild(objBottomNavCloseLink);

		//Close image
		objBottomNavCloseImage = document.createElement("img");
		objBottomNavCloseImage.setAttribute('id', 'closeButton');
		objBottomNavCloseImage.setAttribute('alt', "Close");
		objBottomNavCloseImage.onpropertychange = function() { propertyChanged(); return false; };
		objBottomNavCloseImage.setAttribute('src', fileBottomNavCloseImage);
		objBottomNavCloseLink.appendChild(objBottomNavCloseImage);
        //objBottomNavCloseImage.setAttribute('src', fileBottomNavCloseImage);

		//Slideshow link
 		var objSlideShowLink = document.createElement("a");
		objSlideShowLink.setAttribute('id','slideshowLink');
		objSlideShowLink.setAttribute('href','#');
		objSlideShowLink.setAttribute('onFocus', 'if (this.blur) this.blur()');
		objSlideShowLink.onclick = function() { myLightbox.toggleSlideShow(); return false; }
		objBottomNav.appendChild(objSlideShowLink);

		//Slidehow Image
		objSlideShowImage = document.createElement("img");
		objSlideShowImage.setAttribute('id', 'playButton');
		objSlideShowImage.setAttribute('alt', "Start/Stop");
		objSlideShowImage.setAttribute('src', SlideShowStartImage);
		objSlideShowImage.onpropertychange = function() { propertyChanged(); return false; };
		objSlideShowLink.appendChild(objSlideShowImage);
		//objSlideShowImage.setAttribute('src', SlideShowStartImage);

		//Speaker link
		var objSpeakerLink = document.createElement("a");
		objSpeakerLink.setAttribute('id','speakerLink');
		objSpeakerLink.setAttribute('href','#');
		objSpeakerLink.setAttribute('onFocus', 'if (this.blur) this.blur()');
		objSpeakerLink.onclick = function() { player.toggleVolume(); return false; }
		objBottomNav.appendChild(objSpeakerLink);
			
		//Speaker Image
		objSpeakerImage = document.createElement("img");
		objSpeakerImage.setAttribute('id', 'speaker');		
		objSpeakerImage.setAttribute('alt', "Music On/Off");			
		objSpeakerImage.setAttribute('src', MusicOffImage);	
		objSpeakerImage.onpropertychange = function() { propertyChanged(); return false; };
		objSpeakerLink.appendChild(objSpeakerImage);		
			
		//Music player
		var objFlashPlayer = document.createElement("div");
		objFlashPlayer.setAttribute('id','__sound_flash__');
		objOverlay.appendChild(objFlashPlayer);
			

	},
	
	lightUpNavigationBar: function() {
		if (!this.dimmingOut) {
			  new Effect.Parallel(
				[ 
				new Effect.Appear('imageDataContainer', 
				{ duration: 0.25, from: imageDataContainerOpacity, to: 1.0 }) 
				], { duration: 0.25 } );
		} else {
			this.dimmingOut = false;
		}
	},
	
	dimDownNavigationBar: function() {					
		this.dimmingOut = true;
		setTimeout(function() {				
			if (this.dimmingOut) { 
				//this.dimmingOut = false; 
			
				  new Effect.Parallel(
					[ new Effect.Appear('imageDataContainer', 
						{ duration: 0.25, from: 1.0, to: imageDataContainerOpacity,
							afterFinish: function(){ myLightbox.dimmingOut = false; } 
						}) 
					], { duration: 0.25 } );
			}	
		}.bind(this), 
			2000 //<-- dimming delay
		);			 				
	},
	
	//
	//	start()
	//	Display overlay and lightbox. If image is part of a set, add siblings to imageArray.
	//
	start: function(imageLink) {	
		player = new Player();
		
		slideshowMusic = null;
		firstTime = 1;
		
		saveSlideshow = slideshow;
		saveForeverLoop = foreverLoop;
		saveLoopInterval = loopInterval;

		saveSlideShowWidth = slideShowWidth;
		saveSlideShowHeight = slideShowHeight;
		
		saveLoopMusic = loopMusic;
		saveNavigationBarWidth = navigationBarWidth;

		hideSelectBoxes();

		// stretch overlay to fill page and fade in
		var arrayPageSize = getPageSize();
		Element.setHeight('overlay', arrayPageSize[1]);
		new Effect.Appear('overlay', { duration: 0.2, from: 0.0, to: 0.8 });

		imageArray = [];
		imageNum = 0;		

		if (!document.getElementsByTagName){ return; }
		var anchors = document.getElementsByTagName('a');

		// if image is NOT part of a set..
		if((imageLink.getAttribute('rel') == 'lightbox')){
			// add single image to imageArray
			imageArray.push(new Array(imageLink.getAttribute('href'), imageLink.getAttribute('title')));			
		} else {
		// if image is part of a set..

			// loop through anchors, find other images in set, and add them to imageArray
			for (var i=0; i<anchors.length; i++){
				var anchor = anchors[i];
				if (anchor.getAttribute('href') && (anchor.getAttribute('rel') == imageLink.getAttribute('rel'))){
					imageArray.push(new Array(anchor.getAttribute('href'), anchor.getAttribute('title')));
					
					if (imageArray.length == 1) {
					  slideshowMusic = anchor.getAttribute('music');
					  if (slideshowMusic == null) {						
						  //Element.hide('speakerLink');
					  } else { 
						  //Element.show('speakerLink');							
						  player.loadTrack(slideshowMusic);															
					  }

					  var startSlideshow = anchor.getAttribute('startslideshow');
					  if (startSlideshow != null) {
						if (startSlideshow == "false") slideshow = 0;
					  }					

					  var forever = anchor.getAttribute('forever');
					  if (forever != null) {
						if (forever == "true") foreverLoop = 1; else foreverLoop = 0;
					  }					
					  
					  var foreverMusic = anchor.getAttribute('loopMusic');
					  if (foreverMusic != null) {
						if (foreverMusic == "true") loopMusic = true; else loopMusic = false;
					  }					
					  
					  if (foreverLoop == 1) { loopMusic = true;  }
					  
					  var slideDuration = anchor.getAttribute('slideDuration');
					  if (slideDuration != null) {
						loopInterval = slideDuration * 1000;
					  }					
					  var width = anchor.getAttribute('slideshowwidth');
					  if (width != null) {
						slideShowWidth = width *1;
					  }
					  var height = anchor.getAttribute('slideshowheight');
					  if (height != null) {
						slideShowHeight = height *1;
					  }
					  
					  var barWidth = anchor.getAttribute('navbarWidth');
					  if (barWidth != null) {
						navigationBarWidth = barWidth *1;
					  }					  
					}
					
				}
			}

			imageArray.removeDuplicates();
			while(imageArray[imageNum][0] != imageLink.getAttribute('href')) { imageNum++;}	
		}

		this.changeImageByTimer(imageNum);			
	},
	
	showLightBox: function() {
		    // calculate top offset for the lightbox and display 
	        var arrayPageSize = getPageSize();
		    var arrayPageScroll = getPageScroll();
		    var lightboxTop = arrayPageScroll[1] + (arrayPageSize[3] / 15);
						
		    Element.setTop('lightbox', lightboxTop+10);
		    Element.show('lightbox');			
	},

	// changeImageByTimer()
	// changes image using timer, which prevents the loading gif from spinning 
	// until the entire page is loaded
    	changeImageByTimer: function(imageNum) {
    			activeImage = imageNum;
    			this.imageTimer = setTimeout(function() {
    			this.showLightBox();
    			this.changeImage(activeImage);
    		}.bind(this), 10);
   	 },
    
	//
	//	changeImage()
	//	Hide most elements and preload image in preparation for resizing image container.
	//
	changeImage: function(imageNum) {	
		
		activeImage = imageNum;	// update global var

		// hide elements during transition
		Element.show('loading');
		Element.hide('replay');
		Element.hide('lightboxImage');
		Element.hide('hoverNav');
		Element.hide('prevLink');
		Element.hide('nextLink');
		

		if (firstTime == 1) {		
	  	  Element.hide('imageDataContainer');		  
		  Element.hide('bottomNav');
		  Element.hide('numberDisplay');
		  Element.hide('speakerLink');
		  Element.hide('slideshowLink');		
		}
			
		imgPreloader = new Image();
		
		// once image is preloaded, resize image container
		imgPreloader.onload=function(){
			Element.setSrc('lightboxImage', imageArray[activeImage][0]);

			objLightboxImage.setAttribute('width', imgPreloader.width);
			objLightboxImage.setAttribute('height', imgPreloader.height);

			if ((imageArray.length > 1) && (slideShowWidth != -1 || slideShowHeight != -1)) {
				//<---
				if (slideShowWidth == -1 && slideShowHeight != -1) {
					myLightbox.resizeImageContainer(imgPreloader.width, slideShowHeight);
				} else {
				if (slideShowHeight == -1 && slideShowWidth != -1) {
					myLightbox.resizeImageContainer(slideShowWidth, imgPreloader.height);
				} else {
				//<----
						//------------
					   if (	(slideShowWidth >= imgPreloader.width) &&				
					        (slideShowHeight >= imgPreloader.height)
					      ) 
					    {			  							
							myLightbox.resizeImageContainer(slideShowWidth, slideShowHeight);
						} else {
							myLightbox.resizeImageAndContainer(imgPreloader.width, imgPreloader.height);
						}			  
						//------------
				}//else
				}//else
			} else {
			  myLightbox.resizeImageAndContainer(imgPreloader.width, imgPreloader.height);
			}
		}
		imgPreloader.src = imageArray[activeImage][0];
	},

	resizeImageAndContainer: function(imgWidth, imgHeight) {		
		if(resize == 1) {//resize mod by magarnicle
			useableWidth = 0.9; // 90% of the window
			useableHeight = 0.8; // 80% of the window
			
			var arrayPageSize = getPageSize();
			
			windowWidth = arrayPageSize[2];
			windowHeight = arrayPageSize[3];
			
			var w = windowWidth * useableWidth;
			var h = windowHeight * useableHeight;
			
			var d = Element.getHeight('spacer');
			//adjust the height of the window to fit the navigation bar in
			if (d) {  
				//w = w - (d+d);
				h = h - (d+d);
			}
			
			scaleX = 1; scaleY = 1;
			
			if ( imgWidth > w ) scaleX = (w) / imgWidth;
			if ( imgHeight > h ) scaleY = (h) / imgHeight;
			
			scale = Math.min( scaleX, scaleY );
			
			imgWidth *= scale;
			imgHeight *= scale;

			objLightboxImage.setAttribute('width', imgWidth);
			objLightboxImage.setAttribute('height', imgHeight);
		}
		this.resizeImageContainer(imgWidth, imgHeight);
	},

	//
	//	resizeImageContainer()
	//
	resizeImageContainer: function( imgWidth, imgHeight) {
		imgWidth = imgWidth + 1;
		// get current height and width
		this.wCur = Element.getWidth('outerImageContainer');
		this.hCur = Element.getHeight('outerImageContainer');

		// scalars based on change from old to new
		this.xScale = ((imgWidth  + (borderSize * 2)) / this.wCur) * 100;
		this.yScale = ((imgHeight  + (borderSize * 2)) / this.hCur) * 100;

		// calculate size difference between new and old image, and resize if necessary
		wDiff = (this.wCur - borderSize * 2) - imgWidth;
		hDiff = (this.hCur - borderSize * 2) - imgHeight;

		this.slideDownImageDataContainer = true;

		if(!( hDiff == 0)){ new Effect.Scale('outerImageContainer', this.yScale, {scaleX: false, duration: resizeDuration, queue: 'front'}); }
		if(!( wDiff == 0)){ 
			if (navigationBarWidth == -1) { Element.hide('imageDataContainer');	}
			new Effect.Scale('outerImageContainer', this.xScale, 
				{scaleY: false, delay: resizeDuration, duration: resizeDuration }); 
		} else {
		  	this.slideDownImageDataContainer = false;
		}

		// if new and old image are same size and no scaling transition is necessary, 
		// do a quick pause to prevent image flicker.
		if((hDiff == 0) && (wDiff == 0)){
			if (navigator.appVersion.indexOf("MSIE")!=-1){ pause(250); } else { pause(100);} 
		}

		Element.setHeight('prevLink', imgHeight);
		Element.setHeight('nextLink', imgHeight);		
		if (navigationBarWidth == -1) {
			Element.setWidth( 'imageDataContainer', imgWidth + (borderSize * 2));
		} else {
			Element.setWidth( 'imageDataContainer', navigationBarWidth + (borderSize * 2));	
			this.slideDownImageDataContainer = false;		
		}

		this.showImage();
	},


	//
	//	showImage()
	//	Display image and begin preloading neighbors.
	//
	showImage: function(){
		Element.hide('loading');
		new Effect.Appear('lightboxImage', { duration: 0.5, queue: 'end', afterFinish: function(){ myLightbox.updateDetails(); } });
		this.preloadNeighborImages();
	},

	//
	//	updateDetails()
	//	Display caption, image number, and bottom nav.
	//
	updateDetails: function() {
		Element.show('bottomNav');
		
		if (firstTime == 1) {
			objSpeakerImage.setAttribute('src', MusicOffImage);
			objSlideShowImage.setAttribute('src', SlideShowStartImage);
			objBottomNavCloseImage.setAttribute('src', fileBottomNavCloseImage);
		}
				
		Element.show('caption');		
		if (imageArray[activeImage][1] != '' && imageArray[activeImage][1] != null) {
			Element.setInnerHTML( 'caption', imageArray[activeImage][1]);
		} else {
			Element.setInnerHTML( 'caption', "&nbsp;");
		}
				
		// if image is part of set display 'Image x of x' 
		if(imageArray.length > 1){
			Element.show('numberDisplay');
			Element.setInnerHTML( 'numberDisplay', "" + eval(activeImage + 1) + " of " + imageArray.length);
		}

		if (firstTime == 1 || this.slideDownImageDataContainer) {
		  new Effect.Parallel(
			[ new Effect.SlideDown( 'imageDataContainer', { sync: true, duration: resizeDuration + 0.25, from: 0.0, to: 1 }), 
			  new Effect.Appear('imageDataContainer', { sync: true, duration: 1.0, from: 0.0, to: imageDataContainerOpacity }) ], 
		 	{ duration: 0.65, afterFinish: function() { myLightbox.updateNav();} } 
		  );                   
		} else {		  
//		  new Effect.Parallel(
//			[ new Effect.Appear('imageDataContainer', { sync: true, duration: 1.0 }) ], 
//		 	{ duration: 0.65, afterFinish: function() { myLightbox.updateNav();} } 
//		  );
		  myLightbox.updateNav();
		}


			if (imageArray.length > 1) {                           
			   //Element.show('speakerLink');
			   Element.show('slideshowLink');
			}else {
			   //Element.hide('speakerLink');
			   Element.hide('slideshowLink');
			}

   		if (slideshow == 1 && imageArray.length > 1) {
				this.startSlideShow();
			} 

	},

	//
	//	updateNav()
	//	Display appropriate previous and next hover navigation.
	//
	updateNav: function() {

		Element.show('hoverNav');				

		// if not first image in set, display prev image button
		if(activeImage != 0){
			Element.show('prevLink');
			document.getElementById('prevLink').onclick = function() {
				if (slideshow == 1) keyPressed = true;
				myLightbox.changeImage(activeImage - 1); return false;
			}
		}

		// if not last image in set, display next image button
		if(activeImage != (imageArray.length - 1)){
			Element.show('nextLink');
			document.getElementById('nextLink').onclick = function() {
				if (slideshow == 1) keyPressed = true;
				myLightbox.changeImage(activeImage + 1); return false;
			}
		}
		
		this.enableKeyboardNav();

		if (firstTime == 1) {
		  firstTime = 0;
		  //if (imageArray.length > 1 && slideshow == 1) 
		  this.showSpeaker();
		  if (slideshow == 1) this.playMusic(); 
		}
	},

	//
	//	enableKeyboardNav()
	//
	enableKeyboardNav: function() {
		document.onkeydown = this.keyboardAction; 
	},

	//
	//	disableKeyboardNav()
	//
	disableKeyboardNav: function() {
		document.onkeydown = '';
	},

	//
	//	keyboardAction()
	//
	keyboardAction: function(e) {
		if (e == null) { // ie
			keycode = event.keyCode;
		} else { // mozilla
			keycode = e.which;
		}

		key = String.fromCharCode(keycode).toLowerCase();

		if((key == 'x') || (key == 'o') || (key == 'c')){	// close lightbox
			myLightbox.end();
		} else if((keycode == 188) || (key == 'p')){	// display previous image
			if(activeImage != 0){
				if (slideshow == 1) keyPressed = true;
				myLightbox.disableKeyboardNav();							
				myLightbox.changeImage(activeImage - 1);
			}
		} else if((keycode == 190) || (key == 'n')){	// display next image
			if(activeImage != (imageArray.length - 1)){
				if (slideshow == 1) keyPressed = true;
				myLightbox.disableKeyboardNav();				
				myLightbox.changeImage(activeImage + 1);
			}
		}


	},

	//
	//	preloadNeighborImages()
	//	Preload previous and next images.
	//
	preloadNeighborImages: function(){

		if((imageArray.length - 1) > activeImage){
			preloadNextImage = new Image();
			preloadNextImage.src = imageArray[activeImage + 1][0];
		}
		if(activeImage > 0){
			preloadPrevImage = new Image();
			preloadPrevImage.src = imageArray[activeImage - 1][0];
		}
	
	},

	//showSpeaker
	showSpeaker: function() {
	   if (slideshowMusic != null) {
	      Element.show('speakerLink');	      
	   } else {
		  Element.hide('speakerLink');
		}
	},
	
	//playMusic
	playMusic: function() {
	   if (slideshowMusic != null) {
	      objSpeakerImage.setAttribute('src', MusicOnImage);
	      player.play();
	   }
	},

	//stopMusic
	stopMusic: function() {
	  if (slideshowMusic != null) {
        objSpeakerImage.setAttribute('src', MusicOffImage);
        if (player.paused) {
       	  player.stop();
        } else {
       		player.play();
        }
      }
	},

	//	Slideshow Functions
	//
	//	toggleSlideShow()
	//	startSlideShow()
	//	stopSlideShow()
	
	//	toggleSlideShow()
	toggleSlideShow: function() {
		if(slideshow == 1) this.stopSlideShow();
		else {
		   this.playMusic();		   
		   if(activeImage == (imageArray.length-1)) {
			slideshow = 1;
			this.changeImage(0);			
		   } else {
		   	this.startSlideShow();
		   }
		}
	},
	
	//	startSlideShow()
	startSlideShow: function() {
		slideshow = 1;				
		objSlideShowImage.setAttribute('src', SlideShowStopImage);
		this.slideShowTimer = setTimeout(function() {
			if (keyPressed) {
 				keyPressed = false;
				return;
			}
			if(activeImage < (imageArray.length-1)) {
				this.changeImage(activeImage + 1);
			}
			else {
				if(foreverLoop) { 
					this.changeImage(0);
				}
				else {
					//this.end();
					//if (closeWindow) window.close();
					
					//this.stopSlideShow();
					
					//---
					slideshow = 0;
					
					if(this.slideShowTimer) {
						clearTimeout(this.slideShowTimer);
						this.slideShowTimer = null;			
					}
					//---
					
							
					player.clearCallback();
		
					this.disableKeyboardNav();					
					Element.hide('hoverNav');
					Element.hide('prevLink');
					Element.hide('nextLink');
					
					Element.setInnerHTML( 'numberDisplay', '');
					
					this.fadeoutTimer = setInterval(function() {
						player.sound.setVolume(player.sound.getVolume()-1);
					}.bind(this), 30);
					
					new Effect.Appear('lightboxImage', { duration: 3, from: 1, to: 0, 
										afterFinish: function(){ 											
											new Effect.Appear('replay', { duration: 0.2, from: 0, to: 1}); 
											objSlideShowImage.setAttribute('src', SlideShowStartImage);
											clearInterval(myLightbox.fadeoutTimer);
											player.paused = true; //this will cause music to stop
											myLightbox.stopMusic();
											
											}}	);
				}
	     }	
		}.bind(this), loopInterval);
	},

	//	stopSlideShow()
	stopSlideShow: function() {
		slideshow = 0;
		objSlideShowImage.setAttribute('src', SlideShowStartImage);
		 
		this.stopMusic();		
		if(this.slideShowTimer) {
			clearTimeout(this.slideShowTimer);
			this.slideShowTimer = null;			
		}
	},

	//
	//	end()
	//
	end: function() {		
		player.paused = true; //this will cause music to stop
		
		this.stopSlideShow();
		
		player.clearCallback();
		clearInterval(myLightbox.fadeoutTimer);
		
		this.disableKeyboardNav();
		
		Element.hide('bottomNav');
		Element.hide('lightbox');
		
		new Effect.Fade('overlay', { duration: 0.2});
		showSelectBoxes();

		slideshow = saveSlideshow;
		foreverLoop = saveForeverLoop;
		loopInterval = saveLoopInterval;

		slideShowWidth = saveSlideShowWidth;
		slideShowHeight = saveSlideShowHeight;
		navigationBarWidth = saveNavigationBarWidth;
		
		loopMusic = saveLoopMusic;
	}
}

// -----------------------------------------------------------------------------------

//this is three functions to support mouseover and mouseout events.
//It allows checking if mouse is inside the block ignoring children's blocks.
function containsDOM (container, containee) {
  var isParent = false;
  do {
    if ((isParent = container == containee))
      break;
    containee = containee.parentNode;
  }
  while (containee != null);
  return isParent;
}

function checkMouseEnter (element, evt) {
  if (element.contains && evt.fromElement) {
    return !element.contains(evt.fromElement);
  }
  else if (evt.relatedTarget) {
    return !containsDOM(element, evt.relatedTarget);
  }
}

function checkMouseLeave (element, evt) {
  if (element.contains && evt.toElement) {
    return !element.contains(evt.toElement);
  }
  else if (evt.relatedTarget) {
    return !containsDOM(element, evt.relatedTarget);
  }
}

//
// getPageScroll()
// Returns array with x,y page scroll values.
// Core code from - quirksmode.org
//
function getPageScroll(){

	var yScroll;

	if (self.pageYOffset) {
		yScroll = self.pageYOffset;
	} else if (document.documentElement && document.documentElement.scrollTop){	 // Explorer 6 Strict
		yScroll = document.documentElement.scrollTop;
	} else if (document.body) {// all other Explorers
		yScroll = document.body.scrollTop;
	}

	arrayPageScroll = new Array('',yScroll) 
	return arrayPageScroll;
}

// -----------------------------------------------------------------------------------

//
// getPageSize()
// Returns array with page width, height and window width, height
// Core code from - quirksmode.org
// Edit for Firefox by pHaez
//
function getPageSize(){
	
	var xScroll, yScroll;
	
	if (window.innerHeight && window.scrollMaxY) {	
		xScroll = document.body.scrollWidth;
		yScroll = window.innerHeight + window.scrollMaxY;
	} else if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
		xScroll = document.body.scrollWidth;
		yScroll = document.body.scrollHeight;
	} else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
		xScroll = document.body.offsetWidth;
		yScroll = document.body.offsetHeight;
	}
	
	var windowWidth, windowHeight;
	if (self.innerHeight) {	// all except Explorer
		windowWidth = self.innerWidth;
		windowHeight = self.innerHeight;
	} else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
		windowWidth = document.documentElement.clientWidth;
		windowHeight = document.documentElement.clientHeight;
	} else if (document.body) { // other Explorers
		windowWidth = document.body.clientWidth;
		windowHeight = document.body.clientHeight;
	}	
	
	// for small pages with total height less then height of the viewport
	if(yScroll < windowHeight){
		pageHeight = windowHeight;
	} else { 
		pageHeight = yScroll;
	}

	// for small pages with total width less then width of the viewport
	if(xScroll < windowWidth){	
		pageWidth = windowWidth;
	} else {
		pageWidth = xScroll;
	}


	arrayPageSize = new Array(pageWidth,pageHeight,windowWidth,windowHeight) 
	return arrayPageSize;
}

// -----------------------------------------------------------------------------------

//
// getKey(key)
// Gets keycode. If 'x' is pressed then it hides the lightbox.
//
function getKey(e){
	if (e == null) { // ie
		keycode = event.keyCode;
	} else { // mozilla
		keycode = e.which;
	}
	key = String.fromCharCode(keycode).toLowerCase();
	
	if(key == 'x'){
	}
}

// -----------------------------------------------------------------------------------

//
// listenKey()
//
function listenKey () {	document.onkeypress = getKey; }
	
// ---------------------------------------------------

function showSelectBoxes(){
	selects = document.getElementsByTagName("select");
	for (i = 0; i != selects.length; i++) {
		selects[i].style.visibility = "visible";
	}
}

// ---------------------------------------------------

function hideSelectBoxes(){
	selects = document.getElementsByTagName("select");
	for (i = 0; i != selects.length; i++) {
		selects[i].style.visibility = "hidden";
	}
}

// ---------------------------------------------------

//
// pause(numberMillis)
// Pauses code execution for specified time. Uses busy code, not good.
// Code from http://www.faqts.com/knowledge_base/view.phtml/aid/1602
//
function pause(numberMillis) {
	var now = new Date();
	var exitTime = now.getTime() + numberMillis;
	while (true) {
		now = new Date();
		if (now.getTime() > exitTime)
			return;
	}
}

// ---------------------------------------------------



function initLightbox() { myLightbox = new Lightbox();}
//Event.observe(window, 'load', initLightbox, false);



//the code below suppose to help starting slideshow before a page is totaly loaded
function init() {
    // quit if this function has already been called
    if (arguments.callee.done) return;

    // flag this function so we don't do the same thing twice
    arguments.callee.done = true;

    // kill the timer
    if (_timer) {
        clearInterval(_timer);
        _timer = null;
    }

    // do onload stuff
    initLightbox();

};

 

/* for Mozilla */

if (document.addEventListener) {
    document.addEventListener("DOMContentLoaded", init, false);

}

 

/* for Internet Explorer */
/*@cc_on @*/
/*@if (@_win32)
    document.write("<script id=__ie_onload defer src=javascript:void(0)></script>");
    var script = document.getElementById("__ie_onload");
    script.onreadystatechange = function() {
        if (this.readyState == "complete") {
            init(); // call the onload handler
        }
    };
/*@end @*/

 

/* for Safari */
if (/WebKit/i.test(navigator.userAgent)) { // sniff
    var _timer = setInterval(function() {
        if (/loaded|complete/.test(document.readyState)) {
            init(); // call the onload handler
        }
    }, 10);
}

 

/* for other browsers */
window.onload = init;

