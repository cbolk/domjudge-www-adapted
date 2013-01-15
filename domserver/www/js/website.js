//Initialize
$(document).ready(function(){
	//Examples
	var oSlider1 = $('#slider1');
	if(oSlider1.length > 0){
		oSlider1.tinycarousel();
	}
	
	var oSlider2 = $('#slider2');
	if(oSlider2.length > 0){
		oSlider2.tinycarousel({ display: 3});
	}
	
	var oSlider3 = $('#slider3');
	if(oSlider3.length > 0){
		oSlider3.tinycarousel({ pager: true, interval: true });
	}
	
	var oSlider4 = $('#slider4');
	if(oSlider4.length > 0){
		oSlider4.tinycarousel({ controls: false, pager: true, animation: false });
	}		
	
	var oSlider5 = $('#slider5');
	if(oSlider5.length > 0){
		oSlider5.tinycarousel({ axis: 'y'});
	}
			
	var oSlider6 = $('#slider6');
	if(oSlider6.length > 0 ){
		oSlider6.tinycarousel({ 
			'pager': true,
			'callback': function(oEl, iIndex){
				$.cookie('cookie-example', iIndex +1);
			}, 
			'start': ($.cookie('cookie-example') == null ? 0 : $.cookie('cookie-example')) 
		 });
	}	
	
	var oSlider7 = $('#slider7');
	if(oSlider7.length > 0){
		oSlider7.tinycarousel({ interval: true });
		
		$('#gotoslide4').click(function(){ 
			oSlider7.tinycarousel_move(4);
			return false;
		});
		$('#startslider').click(function(){ 
			oSlider7.tinycarousel_start();
			return false;
		});
		$('#stopslider').click(function(){ 
			oSlider7.tinycarousel_stop();
			return false;
		});		
	}
	
});

//Fake html5 in ie
document.createElement('header');
document.createElement('footer');
document.createElement('nav');
document.createElement('section');
document.createElement('article');
document.createElement('aside');